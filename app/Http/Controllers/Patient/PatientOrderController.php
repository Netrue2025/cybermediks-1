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
        $order = OrderFactory::ensureOrderFromPrescription($rx, (int)$r->pharmacy_id);

        return response()->json(['ok' => true, 'message' => 'Pharmacy selected', 'order_id' => $order->id]);
    }

    public function confirmItems(Prescription $rx)
    {
        $order = $rx->order()->with('items')->firstOrFail();
        if ($order->status !== 'quoted') {
            return response()->json(['message'=>'Not ready for confirmation'], 422);
        }

        DB::transaction(function () use ($order) {
            $order->items()->where('status','quoted')->update(['status'=>'patient_confirmed']);
            $order->update(['status' => 'patient_confirmed']);
        });

        return back()->with('ok', 'Items confirmed.');
    }
    
}
