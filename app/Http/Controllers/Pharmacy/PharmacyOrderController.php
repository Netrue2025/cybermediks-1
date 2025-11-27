<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyOrderController extends Controller
{
    protected function baseQuery()
    {
        return Order::query()
            ->with([
                'prescription:id,code,patient_id,doctor_id,dispatcher_id,dispatcher_price',
                'prescription.patient:id,first_name,last_name,email,phone',
                'prescription.doctor:id,first_name,last_name',
                'items:id,order_id,prescription_item_id,drug,quantity,unit_price,line_total,status',
                'dispatcher:id,first_name,last_name,phone',
            ])
            ->where('pharmacy_id', Auth::id());
    }

    public function index(Request $request)
    {
        $q        = trim((string)$request->get('q'));
        $status   = (string)$request->get('status'); // pending, quoted, patient_confirmed, pharmacy_accepted, ready, dispatcher_price_set, dispatcher_price_confirm, picked, delivered, rejected
        $dateFrom = $request->date('from');
        $dateTo   = $request->date('to');

        $orders = $this->baseQuery();

        if ($q !== '') {
            $orders->where(function ($w) use ($q) {
                $w->whereHas('prescription', fn($p) => $p->where('code', 'like', "%{$q}%"))
                    ->orWhereHas('prescription.patient', fn($p) =>
                    $p->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('prescription.doctor', fn($d) =>
                    $d->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%"))
                    ->orWhereHas('items', fn($i) => $i->where('drug', 'like', "%{$q}%"));
            });
        }

        if (in_array($status, [
            'pending',
            'quoted',
            'patient_confirmed',
            'pharmacy_accepted',
            'ready',
            'dispatcher_price_set',
            'dispatcher_price_confirm',
            'picked',
            'delivered',
            'rejected'
        ], true)) {
            $orders->where('status', $status);
        }

        if ($dateFrom) $orders->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $orders->whereDate('created_at', '<=', $dateTo);

        $orders = $orders->orderByDesc('created_at')->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return view('pharmacy.orders._list', compact('orders'))->render();
        }

        return view('pharmacy.orders.index', compact('orders', 'q', 'status', 'dateFrom', 'dateTo'));
    }

    public function show(Order $order)
    {
        abort_unless($order->pharmacy_id === Auth::id(), 403);
        $order->loadMissing('prescription.patient', 'prescription.doctor', 'items', 'dispatcher');
        return view('pharmacy.orders.show', compact('order'));
    }

    /**
     * Pharmacy actions state machine
     *
     * Allowed transitions (pharmacy side):
     * - pending         -> quoted (AI does this)  [pharmacy does not set]
     * - quoted          -> pharmacy_accepted | rejected
     * - patient_confirmed -> ready | rejected
     * - ready           -> dispatcher_price_set (dispatcher) [pharmacy does not set] | rejected
     * - dispatcher_price_confirm -> picked | rejected
     * - picked          -> delivered (dispatcher) [pharmacy does not set]
     */
    public function updateStatus(Request $r, Order $order)
    {
        abort_unless($order->pharmacy_id === Auth::id(), 403);

        $r->validate([
            'status' => 'required|string|in:pharmacy_accepted,rejected,ready,picked'
        ]);

        $from = $order->status;
        $to   = (string)$r->input('status');

        $allowed = match ($from) {
            'quoted'            => ['pharmacy_accepted', 'rejected'],
            'patient_confirmed' => ['ready', 'rejected'],
            'dispatcher_price_confirm' => ['picked', 'rejected'],
            default             => [],
        };

        if (!in_array($to, $allowed, true)) {
            return response()->json(['message' => "Transition $from → $to not allowed"], 422);
        }

        // Guards
        if ($to === 'ready' && empty($order->items_subtotal)) {
            return response()->json(['message' => 'No quoted/confirmed items total.'], 422);
        }

        // Wallet move on "picked" (handoff)
        if ($to === 'picked') {
            $rx = $order->prescription;
            if ($from !== 'dispatcher_price_confirm') {
                return response()->json(['message' => 'Can only mark picked after delivery fee is confirmed'], 422);
            }
            if (is_null($rx?->dispatcher_id)) {
                return response()->json(['message' => 'Attach a dispatcher before marking picked'], 422);
            }

            $patient = $rx->patient;
            $pharm   = $order->pharmacy; // via belongsTo(User, 'pharmacy_id') on Order model
            $fee     = (float)($order->items_subtotal ?? 0);

            DB::transaction(function () use ($patient, $pharm, $fee, $order) {
                if ($fee > 0 && $patient && $pharm) {
                    // Debit patient
                    $patient->wallet_balance = (float)$patient->wallet_balance - $fee;
                    $patient->save();

                    WalletTransaction::create([
                        'user_id'   => $patient->id,
                        'type'      => 'debit',
                        'amount'    => $fee,
                        'currency'  => 'NGN',
                        'purpose'   => "Payment for order #O{$order->id}",
                        'reference' => uniqid('ord_pay_'),
                        'status'    => 'success',
                        'meta'      => ['order_id' => $order->id],
                    ]);

                    // Credit pharmacy
                    $pharm->wallet_balance = (float)$pharm->wallet_balance + $fee;
                    $pharm->save();

                    WalletTransaction::create([
                        'user_id'   => $pharm->id,
                        'type'      => 'credit',
                        'amount'    => $fee,
                        'currency'  => 'NGN',
                        'purpose'   => "Payment received for order #O{$order->id}",
                        'reference' => uniqid('ord_rec_'),
                        'status'    => 'success',
                        'meta'      => ['order_id' => $order->id],
                    ]);
                }
            });
        }

        $order->update(['status' => $to]);

        return response()->json(['status' => 'ok', 'message' => 'Status updated']);
    }

    public function markPicked(Order $order)
    {
        // Auth: ensure this order belongs to current pharmacy
        $pharmacyIdFromOrder = $order->pharmacy_id ?? $order->prescription?->pharmacy_id;
        abort_unless($pharmacyIdFromOrder === Auth::id(), 403);

        // Must have patient confirmed items and (if present) confirmed delivery fee
        // Typical path: quoted → patient_confirmed → pharmacy_accepted → ready → dispatcher_price_set → dispatcher_price_confirm → picked
        $allowedBeforePick = ['dispatcher_price_confirm', 'ready', 'pharmacy_accepted']; // allow ready if no delivery flow
        if (!in_array($order->status, $allowedBeforePick, true)) {
            return response()->json(['message' => "Order not ready to be picked (status: {$order->status})"], 422);
        }

        // If there is a delivery fee quoted, ensure a dispatcher is assigned and fee is confirmed
        if (!is_null($order->dispatcher_price)) {
            if ($order->status !== 'dispatcher_price_confirm') {
                return response()->json(['message' => 'Delivery fee must be confirmed before pickup'], 422);
            }
            if (is_null($order->dispatcher_id)) {
                return response()->json(['message' => 'Assign a dispatcher before pickup'], 422);
            }
        }

        // Must have an items subtotal to charge
        $itemsTotal = (float) ($order->items_subtotal ?? 0);
        if ($itemsTotal <= 0) {
            return response()->json(['message' => 'No billable items to charge'], 422);
        }

        // Who pays/gets paid
        $patient   = $order->prescription?->patient;
        $pharmacy  = $order->prescription?->pharmacy;

        if (!$patient || !$pharmacy) {
            return response()->json(['message' => 'Order parties missing'], 422);
        }

        // Idempotency: don’t double-charge if we already posted a tx for this order’s items
        $reference = 'ORD-' . $order->id . '-ITEMS';
        $alreadyCharged = WalletTransaction::where('reference', $reference)->exists();

        DB::transaction(function () use ($order, $patient, $pharmacy, $itemsTotal, $reference, $alreadyCharged) {
            if (!$alreadyCharged) {
                // 1) Patient → debit
                $patient->wallet_balance -= $itemsTotal;
                $patient->save();

                WalletTransaction::create([
                    'user_id'   => $patient->id,
                    'type'      => 'debit',
                    'amount'    => $itemsTotal,
                    'currency'  => 'NGN',
                    'purpose'   => 'pharmacy_sale',
                    'reference' => $reference,
                    'meta'      => ['order_id' => $order->id],
                    'status'    => 'successful',
                ]);

                // 2) Pharmacy → credit
                $pharmacy->wallet_balance += $itemsTotal;
                $pharmacy->save();

                WalletTransaction::create([
                    'user_id'   => $pharmacy->id,
                    'type'      => 'credit',
                    'amount'    => $itemsTotal,
                    'currency'  => 'NGN',
                    'purpose'   => 'pharmacy_sale',
                    'reference' => $reference,
                    'meta'      => ['order_id' => $order->id],
                    'status'    => 'successful',
                ]);
            }

            // 3) Move order to picked
            $order->status = 'picked';
            if ($order->isFillable('picked_at')) {
                $order->picked_at = now();
            }
            $order->save();

            // 4) Legacy sync (optional)
            if ($order->prescription) {
                $order->prescription->update(['dispense_status' => 'picked']);
            }
        });

        return response()->json(['status' => 'ok', 'message' => 'Marked picked up']);
    }
}
