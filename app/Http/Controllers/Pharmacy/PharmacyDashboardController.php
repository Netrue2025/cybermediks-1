<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyDashboardController extends Controller
{
    public function index(Request $request)
    {
        $pharmacyId = Auth::id();

        // Metrics
        $pendingCount = Prescription::where('pharmacy_id', $pharmacyId)
            ->where('dispense_status', 'pending')->count();

        $readyCount = Prescription::where('pharmacy_id', $pharmacyId)
            ->where('dispense_status', 'ready')->count();

        $revenueToday = WalletTransaction::where('user_id', $pharmacyId)
            ->where('type', 'credit')
            ->where('purpose', 'pharmacy_sale')
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        // Lists (latest 8)
        $pending = Prescription::with(['patient:id,first_name,last_name', 'doctor:id,first_name,last_name'])
            ->where('pharmacy_id', $pharmacyId)
            ->where('dispense_status', 'pending')
            ->latest()->take(8)->get();

        $dispensed = Prescription::with(['patient:id,first_name,last_name'])
            ->where('pharmacy_id', $pharmacyId)
            ->whereIn('dispense_status', ['ready', 'picked'])
            ->latest()->take(8)->get();

        return view('pharmacy.dashboard', compact(
            'pendingCount',
            'readyCount',
            'revenueToday',
            'pending',
            'dispensed'
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
