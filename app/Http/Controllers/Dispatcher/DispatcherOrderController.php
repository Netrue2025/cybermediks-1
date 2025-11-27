<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispatcherOrderController extends Controller
{
    public function accept(Request $r, Order $order)
    {
        if ($order->status !== 'ready') {
            return response()->json(['message' => 'Only ready orders can be accepted'], 422);
        }
        if (!is_null($order->dispatcher_id)) {
            return response()->json(['message' => 'Already assigned'], 422);
        }

        $order->update(['dispatcher_id' => Auth::id(), 'status' => 'dispatcher_price_confirm']);

        return response()->json(['status' => 'ok', 'message' => 'Delivery accepted']);
    }

    // public function setDeliveryFee(Request $r, Order $order)
    // {
    //     if (!in_array($order->status, ['ready', 'dispatcher_price_set'], true)) {
    //         return response()->json(['message' => 'You can set delivery fee only when order is ready'], 422);
    //     }

    //     if ($order->dispatcher_id && $order->dispatcher_id !== Auth::id()) {
    //         return response()->json(['message' => 'This delivery is assigned to another dispatcher'], 403);
    //     }
    //     if (!$order->dispatcher_id) {
    //         $order->dispatcher_id = Auth::id();
    //     }

    //     $data = $r->validate([
    //         'dispatcher_price' => 'required|numeric|min:0',
    //     ]);

    //     $order->dispatcher_price = $data['dispatcher_price'];
    //     $order->status           = 'dispatcher_price_set';
    //     $order->save();

    //     return response()->json(['status' => 'ok', 'message' => 'Delivery fee set. Awaiting patient confirmation.']);
    // }

    public function markDelivered(Order $order)
    {
        if ($order->status !== 'picked') {
            return response()->json(['message' => 'Can only deliver after it is picked'], 422);
        }
        if ($order->dispatcher_id !== Auth::id()) {
            return response()->json(['message' => 'Not your delivery'], 403);
        }

        $order->update(['status' => 'delivered']);

        // charge patient and pay dispatcher
        $patient    = $order->patient;     // Order::patient() relationship needed
        $dispatcher = $order->dispatcher;  // Order::dispatcher() relationship already discussed
        $fee        = (float) $order->dispatcher_price;

        if ($fee > 0 && $patient && $dispatcher) {
            $patient->wallet_balance   -= $fee;
            $dispatcher->wallet_balance += $fee;
            $patient->save();
            $dispatcher->save();

            WalletTransaction::create([
                'user_id'  => $patient->id,
                'type'     => 'debit',
                'amount'   => $fee,
                'currency' => 'NGN',
                'purpose'  => 'dispatcher_fee',
                'reference' => 'ORD-' . $order->id,
                'meta'     => ['order_id' => $order->id],
            ]);

            WalletTransaction::create([
                'user_id'  => $dispatcher->id,
                'type'     => 'credit',
                'amount'   => $fee,
                'currency' => 'NGN',
                'purpose'  => 'dispatcher_fee',
                'reference' => 'ORD-' . $order->id,
                'meta'     => ['order_id' => $order->id],
            ]);
        }

        return response()->json(['status' => 'ok', 'message' => 'Delivered']);
    }
}
