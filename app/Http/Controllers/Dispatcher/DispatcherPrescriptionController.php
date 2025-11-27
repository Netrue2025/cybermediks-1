<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispatcherPrescriptionController extends Controller
{
    public function accept(Request $r, Prescription $rx)
    {
        // Only READY and unassigned can be accepted
        if ($rx->dispense_status !== 'ready') {
            return response()->json(['message' => 'Only ready prescriptions can be accepted'], 422);
        }
        if (!is_null($rx->dispatcher_id)) {
            return response()->json(['message' => 'Already assigned'], 422);
        }

        $rx->dispatcher_id = Auth::id();
        $rx->save();

        return response()->json(['status' => 'ok', 'message' => 'Delivery accepted']);
    }

    public function setDeliveryFee(Request $r, Prescription $rx)
    {
        // Must be ready & accepted by this dispatcher (or not assigned → assign to me)
        if ($rx->dispense_status !== 'ready' && $rx->dispense_status !== 'dispatcher_price_set') {
            return response()->json(['message' => 'You can set delivery fee only when prescription is ready'], 422);
        }

        // If a dispatcher is required, ensure it’s me (or set me if not set yet)
        if ($rx->dispatcher_id && $rx->dispatcher_id !== Auth::id()) {
            return response()->json(['message' => 'This delivery is assigned to another dispatcher'], 403);
        }
        if (!$rx->dispatcher_id) {
            $rx->dispatcher_id = Auth::id();
        }

        $data = $r->validate([
            'dispatcher_price' => 'required|numeric|min:0',
        ]);

        if ($rx->dispense_status !== 'ready' && $rx->dispense_status !== 'dispatcher_price_set') {
            return response()->json(['message' => 'Fee can be set only when Rx is ready'], 422);
        }


        $rx->dispatcher_price = $data['dispatcher_price'];
        $rx->dispense_status  = 'dispatcher_price_set';
        $rx->save();

        return response()->json(['status' => 'ok', 'message' => 'Delivery fee set. Awaiting patient confirmation.']);
    }

    // Dispatcher marks delivered (after pharmacy already marked picked)
    public function markDelivered(Prescription $rx)
    {
        if ($rx->dispense_status !== 'picked') {
            return response()->json(['message' => 'Can only deliver after it is picked'], 422);
        }
        if ($rx->dispatcher_id !== Auth::id()) {
            return response()->json(['message' => 'Not your delivery'], 403);
        }

        $rx->dispense_status = 'delivered';
        $rx->save();

        // charge patient and pay dispatcher
        $patient = $rx->patient;
        $dispatcher = $rx->dispatcher;
        $fee = $rx->dispatcher_price;

        $patient->wallet_balance -= $fee;
        $patient->save();


        $dispatcher->wallet_balance += $fee;
        $dispatcher->save();

        WalletTransaction::create([
            'user_id' => $patient->id,
            'amount' => -$fee,
            'currency' => 'NGN',
            'type' => 'debit',
            'reference' => uniqid('txn_'),
            'purpose' => "Payment for prescription delivery with ID {$rx->id}",
        ]);

        WalletTransaction::create([
            'user_id' => $dispatcher->id,
            'amount' => $fee,
            'currency' => 'NGN',
            'type' => 'credit',
            'reference' => uniqid('txn_'),
            'purpose' => "Payment received prescription delivery with ID {$rx->id}",
        ]);

        return response()->json(['status' => 'ok', 'message' => 'Delivered']);
    }
}
