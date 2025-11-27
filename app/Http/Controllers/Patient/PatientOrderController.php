<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Services\OrderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientOrderController extends Controller
{
    public function assignPharmacy(Prescription $rx, Request $r)
    {
        $r->validate(['pharmacy_id' => 'required|exists:users,id']);
   
        $patient = $r->user();
        $patientBalance = (float)($patient->wallet_balance ?? 0);

        $order = OrderFactory::ensureOrderFromPrescription($rx, (int)$r->pharmacy_id);

        return response()->json(['ok' => true, 'message' => 'Pharmacy selected', 'order_id' => $order->id]);
    }

    public function confirmItems(Prescription $rx)
    {
        $order = $rx->order()->with('items')->firstOrFail();
        if ($order->status !== 'quoted') {
            return response()->json(['message' => 'Not ready for confirmation'], 422);
        }

        // Check patient wallet balance
        $patient = auth()->user();
        $patientBalance = (float)($patient->wallet_balance ?? 0);
        $orderTotal = (float)($order->items_subtotal ?? 0) + (float)($order->dispatcher_price ?? 0);
        
        if ($orderTotal > 0 && $patientBalance < $orderTotal) {
            return response()->json([
                'ok' => false,
                'error' => 'insufficient_balance',
                'message' => 'Insufficient wallet balance. Please fund your wallet or choose payment on delivery.',
                'required_amount' => $orderTotal,
                'current_balance' => $patientBalance,
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->items()->where('status', 'quoted')->update(['status' => 'patient_confirmed']);
            $order->update(['status' => 'patient_confirmed']);
        });

        return back()->with('ok', 'Items confirmed.');
    }
}
