<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\DoctorProfile;
use App\Models\DoctorSpecialty;
use App\Models\Prescription;
use App\Models\Specialty;
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


        $pendingRequestsCount = Conversation::where('doctor_id', auth()->id())
            ->where('status', 'pending') // add this column if you don't have it yet
            ->count();


        // Active Consultations: appointments happening "now" (scheduled within the last 60m and not completed/cancelled).
        $activeConsultationsCount = Conversation::where('doctor_id', auth()->id())
            ->where('status', 'active')
            ->count();


        // Prescriptions Today: prescriptions issued today by this doctor.
        $prescriptionsToday = Prescription::where('doctor_id', $docId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        // Earnings (credits) – same logic you referenced earlier
        $earnings = Auth::user()->wallet_balance;

        // Optional: “Video Call Queue” – upcoming video consults starting soon (next 15 min)
        $videoQueueCount = Appointment::where('doctor_id', $docId)
            ->where('type', 'video')
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$now, $now->copy()->addMinutes(15)])
            ->count();


        $pendingConvs = Conversation::with(['patient:id,first_name,last_name'])
            ->where('doctor_id', auth()->id())
            ->where('status', 'pending')
            ->latest()->take(6)->get();

        $activeConvs = Conversation::with(['patient:id,first_name,last_name'])
            ->where('doctor_id', auth()->id())
            ->where('status', 'active')
            ->latest()->take(6)->get();

        $profile = DoctorProfile::firstOrCreate(['doctor_id' => Auth::id()]);
        $allSpecialties = Specialty::orderBy('name')->get(['id', 'name']);
        $selectedSpecialtyIds = DoctorSpecialty::where('doctor_id', Auth::id())->pluck('specialty_id')->all();

        return view('doctor.dashboard', compact(
            'pendingRequests',
            'activeConsultationsCount',
            'pendingRequestsCount',
            'pendingConvs',
            'activeConvs',
            'prescriptionsToday',
            'earnings',
            'videoQueueCount',
            'profile',
            'allSpecialties',
            'selectedSpecialtyIds'
        ));
    }
}
