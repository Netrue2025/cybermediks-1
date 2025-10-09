<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyDashboardController extends Controller
{
    public function index(Request $request)
    {
        $pharmacyId = Auth::id();

        // Metrics (Orders-based)
        $actionableCount = Order::where('pharmacy_id', $pharmacyId)
            ->whereIn('status', ['quoted', 'patient_confirmed'])
            ->count();

        $readyCount = Order::where('pharmacy_id', $pharmacyId)
            ->where('status', 'ready')
            ->count();

        // Revenue today (credits tied to orders)
        // We log purpose like: "Payment received for order #O{ID}" and meta['order_id'] in the OrderController
        $revenueToday = WalletTransaction::where('user_id', $pharmacyId)
            ->where('type', 'credit')
            ->whereDate('created_at', now()->toDateString())
            ->where(function ($q) {
                $q->where('purpose', 'like', 'Payment received for order%')
                    ->orWhereNotNull('meta->order_id'); // JSON meta fallback
            })
            ->sum('amount');

        // Lists (latest 8)
        $pendingOrders = Order::with([
            'prescription:id,code,patient_id,doctor_id',
            'prescription.patient:id,first_name,last_name',
            'prescription.doctor:id,first_name,last_name',
        ])
            ->where('pharmacy_id', $pharmacyId)
            ->whereIn('status', ['quoted', 'patient_confirmed'])
            ->latest()
            ->take(8)
            ->get();

        $dispensedOrders = Order::with([
            'prescription:id,code,patient_id',
            'prescription.patient:id,first_name,last_name',
        ])
            ->where('pharmacy_id', $pharmacyId)
            ->whereIn('status', ['ready', 'dispatcher_price_set', 'dispatcher_price_confirm', 'picked'])
            ->latest()
            ->take(8)
            ->get();

        // NOTE: the dashboard.blade.php you updated should expect $pendingOrders and $dispensedOrders now.
        return view('pharmacy.dashboard', compact(
            'actionableCount',
            'readyCount',
            'revenueToday',
            'pendingOrders',
            'dispensedOrders'
        ));
    }

    public function markReady(Prescription $rx)
    {
        $this->authorizeRx($rx);
        if ($rx->dispense_status === 'pending') {
            $rx->update(['dispense_status' => 'ready']);
        }
        return response()->json(['status' => 'ok', 'message' => 'Marked ready for pickup']);
    }

    public function markPicked(Prescription $rx)
    {
        $this->authorizeRx($rx);
        if (in_array($rx->dispense_status, ['pending', 'ready'])) {
            $rx->update(['dispense_status' => 'picked']);
        }
        return response()->json(['status' => 'ok', 'message' => 'Marked picked up']);
    }

    protected function authorizeRx(Prescription $rx): void
    {
        abort_unless($rx->pharmacy_id === Auth::id(), 403);
    }
}
