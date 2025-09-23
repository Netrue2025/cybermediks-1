<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $status = match (strtolower((string)$request->get('status'))) {
            'active' => 'active',
            'expired' => 'expired',
            'refill requested', 'refill_requested' => 'refill_requested',
            default => null
        };

        $prescriptions = Prescription::query()
            ->with(['doctor:id,first_name,last_name', 'items'])
            ->forPatient($user->id)
            ->statusIs($status)
            ->search($request->get('q'))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('patient.prescriptions._list', compact('prescriptions'))->render();
        }

        return view('patient.prescriptions.index', compact('prescriptions'));
    }

    public function list(Request $r, Prescription $rx)
    {
        // Ensure this prescription belongs to the current patient
        abort_unless($rx->patient_id === Auth::id(), 403);

        $q = trim((string)$r->query('q', ''));
        $filter = $r->query('filter'); // '', '24_7', 'delivery' (if you track it)

        $pharmacies = User::query()
            ->where('role', 'pharmacy')
            ->with(['pharmacyProfile' => function ($p) {
                $p->select('id', 'user_id', 'hours', 'is_24_7', 'delivery_radius_km');
            }])
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($filter === '24_7', fn($w) => $w->whereHas('pharmacyProfile', fn($p) => $p->where('is_24_7', true)))
            // ->when($filter === 'delivery', fn($w)=>$w->whereHas('pharmacyProfile', fn($p)=>$p->whereNotNull('delivery_radius_km'))) // if you want
            ->orderBy('first_name')
            ->take(50)
            ->get();

        return view('patient.prescriptions._pharmacy_list', compact('pharmacies'))->render();
    }

    public function assign(Request $r, Prescription $rx)
    {
        abort_unless($rx->patient_id === Auth::id(), 403);
        $r->validate([
            'pharmacy_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        // Can only assign if not already picked/cancelled
        if (in_array(($rx->dispense_status ?? 'pending'), ['picked', 'cancelled'], true)) {
            return response()->json(['message' => 'This prescription can no longer be assigned.'], 422);
        }

        // Ensure target user is a pharmacy
        $pharmacy = User::where('id', $r->pharmacy_id)->where('role', 'pharmacy')->first();
        if (!$pharmacy) {
            return response()->json(['message' => 'Invalid pharmacy.'], 422);
        }

        $rx->pharmacy_id = $pharmacy->id;
        $rx->save();

        return response()->json(['status' => 'ok', 'message' => 'Pharmacy assigned.']);
    }

    public function confirm(Request $r, Prescription $rx)
    {
        abort_unless($rx->patient_id === Auth::id(), 403);

        if (($rx->dispense_status ?? 'pending') !== 'price_assigned') {
            return response()->json(['message' => 'No price to confirm'], 422);
        }
        if (is_null($rx->total_amount)) {
            return response()->json(['message' => 'Invalid price'], 422);
        }

        $rx->dispense_status = 'price_confirmed';
        $rx->save();

        return response()->json(['status' => 'ok', 'message' => 'Price confirmed']);
    }
}
