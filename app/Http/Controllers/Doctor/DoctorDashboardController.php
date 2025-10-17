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

        $videoQueue = Appointment::where('doctor_id', $docId)
            ->where('type', 'video')
            ->whereIn('status', ['pending', 'accepted'])
            ->get();

        $videoQueueCount = $videoQueue->count();


        $pendingConvs = Conversation::with(['patient:id,first_name,last_name'])
            ->where('doctor_id', auth()->id())
            ->where('status', 'pending')
            ->latest()->take(6)->get();

        $activeConvs = Conversation::with(['patient:id,first_name,last_name'])
            ->where('doctor_id', auth()->id())
            ->where('status', 'active')
            ->latest()->take(6)->get();

        if (request()->ajax()) {
            $videoCallQueue = view('doctor.partials._video_call_queue', ['videoQueue' => $videoQueue, 'videoQueueCount' => $videoQueueCount])->render();
            $pendingRequest = view('doctor.partials._pending_request', ['pendingConvs' => $pendingConvs, 'pendingRequestsCount' => $pendingRequestsCount])->render();
            $activeRequest = view('doctor.partials._active_request', ['activeConvs' => $activeConvs, 'activeConsultationsCount' => $activeConsultationsCount])->render();


            return response()->json(['videoCallQueue' => $videoCallQueue, 'pendingRequest' => $pendingRequest, 'activeRequest' => $activeRequest]);
        }

        return view('doctor.dashboard', compact(
            'activeConsultationsCount',
            'pendingRequestsCount',
            'pendingConvs',
            'activeConvs',
            'prescriptionsToday',
            'earnings',
            'videoQueue',
            'videoQueueCount',
        ));
    }
}
