<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;


class OrderFactory
{
    public static function ensureOrderFromPrescription(Prescription $rx, ?int $pharmacyId = null): Order
    {
        return DB::transaction(function () use ($rx, $pharmacyId) {
            $order = $rx->order()->first();
            if (!$order) {
                $order = Order::create([
                    'prescription_id' => $rx->id,
                    'patient_id'      => $rx->patient_id,
                    'pharmacy_id'     => $pharmacyId,
                    'status'          => 'pending',
                    'currency'        => 'USD',
                ]);

                // copy Rx items -> order_items
                foreach ($rx->items as $pi) {
                    OrderItem::create([
                        'order_id'             => $order->id,
                        'prescription_item_id' => $pi->id,
                        'drug'       => $pi->drug,
                        'dose'       => $pi->dose,
                        'frequency'  => $pi->frequency,
                        'days'       => $pi->days,
                        'quantity'   => $pi->quantity ?? 1,
                        'directions' => $pi->directions,
                        'status'     => 'pending',
                    ]);
                }
            } elseif ($pharmacyId && $order->pharmacy_id !== $pharmacyId) {
                $order->update(['pharmacy_id' => $pharmacyId, 'status' => 'pending']);
            }

            return $order->fresh(['items','pharmacy']);
        });
    }
}
