<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorDashboardController extends Controller
{
    public function index()
    {
        $docId = Auth::id();
        $now   = now();

        // === Metric definitions (adjust if your schema differs) ===
        // Pending Requests: upcoming appointments not yet started (scheduled in the future).
        $pendingRequests = Appointment::where('doctor_id', $docId)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', $now)
            ->count();

        // Active Consultations: appointments happening "now" (scheduled within the last 60m and not completed/cancelled).
        $activeConsultations = Appointment::where('doctor_id', $docId)
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$now->copy()->subHour(), $now])
            ->count();

        // Prescriptions Today: prescriptions issued today by this doctor.
        $prescriptionsToday = Prescription::where('doctor_id', $docId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        // Earnings (credits) – same logic you referenced earlier
        $earnings = WalletTransaction::where('user_id', $docId)
            ->where('type', 'credit')
            ->whereIn('purpose', ['consultation_payout', 'prescription_payout'])
            ->sum('amount');

        // Optional: “Video Call Queue” – upcoming video consults starting soon (next 15 min)
        $videoQueueCount = Appointment::where('doctor_id', $docId)
            ->where('type', 'video')
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$now, $now->copy()->addMinutes(15)])
            ->count();

        return view('doctor.dashboard', compact(
            'pendingRequests',
            'activeConsultations',
            'prescriptionsToday',
            'earnings',
            'videoQueueCount'
        ));
    }
}
