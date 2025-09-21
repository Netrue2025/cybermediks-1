<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyPrescriptionController extends Controller
{
    protected function baseQuery()
    {
        return Prescription::query()
            ->with([
                'patient:id,first_name,last_name,email',
                'doctor:id,first_name,last_name',
                'items:id,prescription_id,drug,dose,frequency,days,quantity',
            ]);
    }

    protected function ensureOwned(Prescription $rx): void
    {
        abort_unless($rx->pharmacy_id === Auth::id(), 403);
    }

    public function index(Request $request)
    {
        $pharmacyId = Auth::id();

        $q       = trim((string)$request->get('q'));
        $status  = (string)$request->get('status'); // '', pending, ready, picked, cancelled
        $dateFrom = $request->date('from');
        $dateTo  = $request->date('to');

        $rx = $this->baseQuery()
            ->where('pharmacy_id', $pharmacyId);

        if ($q !== '') {
            $rx->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                    ->orWhereHas('patient', fn($p) => $p->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('doctor', fn($d) => $d->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%"))
                    ->orWhereHas('items', fn($i) => $i->where('drug', 'like', "%{$q}%"));
            });
        }

        if (in_array($status, ['pending', 'ready', 'picked', 'cancelled'])) {
            $rx->where('dispense_status', $status);
        }

        if ($dateFrom) $rx->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $rx->whereDate('created_at', '<=', $dateTo);

        $prescriptions = $rx->orderByDesc('created_at')->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return view('pharmacy.prescriptions._list', compact('prescriptions'))->render();
        }

        return view('pharmacy.prescriptions.index', compact('prescriptions', 'q', 'status', 'dateFrom', 'dateTo'));
    }

    public function show(Prescription $rx)
    {
        $this->ensureOwned($rx->loadMissing('patient', 'doctor', 'items'));
        return view('pharmacy.prescriptions.show', compact('rx'));
    }

    public function updateStatus(Request $request, Prescription $rx)
    {
        $this->ensureOwned($rx);
        $data = $request->validate([
            'status' => 'required|in:pending,ready,picked,cancelled',
        ]);

        // simple guardrails for forward-only flow
        $current = $rx->dispense_status;
        $next    = $data['status'];

        $allowed = [
            'pending'  => ['ready', 'cancelled'],
            'ready'    => ['picked', 'cancelled', 'pending'],
            'picked'   => [],             // terminal
            'cancelled' => [],             // terminal
        ];
        abort_unless(in_array($next, $allowed[$current] ?? []), 422, 'Invalid status transition');

        $rx->update(['dispense_status' => $next]);

        return response()->json(['status' => 'success', 'message' => "Status updated to {$next}."]);
    }

    public function updateAmount(Request $request, Prescription $rx)
    {
        $this->ensureOwned($rx);
        $data = $request->validate([
            'total_amount' => 'nullable|numeric|min:0|max:9999999.99',
        ]);
        $rx->update(['total_amount' => $data['total_amount']]);
        return response()->json(['status' => 'success', 'message' => 'Amount saved.']);
    }

    // optional: claim unassigned (if you want to support this)
    public function claim(Prescription $rx)
    {
        abort_if($rx->pharmacy_id && $rx->pharmacy_id !== Auth::id(), 403);
        $rx->update(['pharmacy_id' => Auth::id(), 'dispense_status' => $rx->dispense_status ?? 'pending']);
        return response()->json(['status' => 'success', 'message' => 'Prescription claimed.']);
    }
}
