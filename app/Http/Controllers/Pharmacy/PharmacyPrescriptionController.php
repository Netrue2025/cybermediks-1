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

    public function updateStatus(Request $r, Prescription $rx)
    {
        if ($rx->pharmacy_id && $rx->pharmacy_id !== Auth::id()) {
            return response()->json(['message' => 'Not your prescription'], 403);
        }

        $r->validate(['status' => 'required|string|in:pending,price_assigned,price_confirmed,ready,picked,cancelled']);

        $from = $rx->dispense_status ?? 'pending';
        $to   = (string) $r->input('status'); // <-- cast to plain string

        $allowed = match ($from) {
            'pending'         => ['cancelled'],            // price is set via saveAmount()
            'price_assigned'  => ['cancelled'],            // waiting for patient
            'price_confirmed' => ['ready', 'cancelled'],   // now you can prepare
            'ready'           => ['picked', 'cancelled'],  // handover or cancel
            'picked'          => [],                       // terminal
            'cancelled'       => [],                       // terminal
            default           => [],
        };

        if (!in_array($to, $allowed, true)) {
            return response()->json(['message' => "Transition $from → $to not allowed"], 422);
        }

        if ($to === 'ready' && is_null($rx->total_amount)) {
            return response()->json(['message' => 'Set and confirm price before marking ready'], 422);
        }

        $rx->dispense_status = $to;
        $rx->save();

        return response()->json(['status' => 'ok', 'message' => 'Status updated']);
    }


    public function updateAmount(Request $r, Prescription $rx)
    {
        // auth: ensure this Rx belongs to this pharmacy, or pharmacy is assigned, or allow any pharmacy? Choose policy.
        // Example: allow if pharmacy_id matches current user (or is null):
        if ($rx->pharmacy_id && $rx->pharmacy_id !== Auth::id()) {
            return response()->json(['message' => 'Not your prescription'], 403);
        }

        $r->validate(['amount' => 'required|numeric|min:0']);

        if (($rx->dispense_status ?? 'pending') !== 'pending') {
            return response()->json(['message' => 'Price can only be set while pending'], 422);
        }

        $rx->total_amount   = $r->amount;
        $rx->pharmacy_id    = $rx->pharmacy_id ?: Auth::id(); // capture ownership on first pricing
        $rx->dispense_status = 'price_assigned';
        $rx->save();

        return response()->json(['status' => 'ok', 'message' => 'Price assigned, awaiting patient confirmation']);
    }

    // public function updateAmount(Request $request, Prescription $rx)
    // {
    //     $this->ensureOwned($rx);
    //     $data = $request->validate([
    //         'total_amount' => 'nullable|numeric|min:0|max:9999999.99',
    //     ]);
    //     $rx->update(['total_amount' => $data['total_amount']]);
    //     return response()->json(['status' => 'success', 'message' => 'Amount saved.']);
    // }

    // optional: claim unassigned (if you want to support this)
    public function claim(Prescription $rx)
    {
        abort_if($rx->pharmacy_id && $rx->pharmacy_id !== Auth::id(), 403);
        $rx->update(['pharmacy_id' => Auth::id(), 'dispense_status' => $rx->dispense_status ?? 'pending']);
        return response()->json(['status' => 'success', 'message' => 'Prescription claimed.']);
    }
}
