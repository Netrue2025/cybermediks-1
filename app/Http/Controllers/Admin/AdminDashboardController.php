<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers   = User::count();
        $doctors      = User::where('role', 'doctor')->count();
        $pharmacies   = User::where('role', 'pharmacy')->count();
        $dispatchers  = User::where('role', 'dispatcher')->count();

        $rxTotal      = Prescription::count();
        $rxPending    = Prescription::where('dispense_status', 'pending')->count();
        $rxReady      = Prescription::where('dispense_status', 'ready')->count();
        $rxPicked     = Prescription::where('dispense_status', 'picked')->count();

        $apptsToday   = Appointment::whereDate('scheduled_at', now()->toDateString())->count();
        $revenueToday = WalletTransaction::whereDate('created_at', now()->toDateString())
            ->where('type', 'credit')->sum('amount');

        return view('admin.dashboard', compact(
            'totalUsers',
            'doctors',
            'pharmacies',
            'dispatchers',
            'rxTotal',
            'rxPending',
            'rxReady',
            'rxPicked',
            'apptsToday',
            'revenueToday'
        ));
    }
}
