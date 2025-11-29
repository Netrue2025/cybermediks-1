<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DoctorQueueController extends Controller
{
    // Accept a pending video appointment
    public function accept(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);

        if ($appointment->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be accepted'], 422);
        }

        $doctorProfile = DoctorProfile::where('doctor_id', Auth::id())->first();

        if (!$doctorProfile) {
            return response()->json(['message' => 'Please setup doctor profile before accepting appointment'], 422);
        }

        if (!$doctorProfile->meeting_link || $doctorProfile->meeting_link == '') {
            return response()->json(['message' => 'Please add meeting link in doctor profile before accepting appointment'], 422);
        }

        // check if link is still valid

        if (!$doctorProfile?->link_updated_at || !$doctorProfile->link_updated_at->isToday()) {
            return response()->json([
                'message' => 'Meeting link expired, please update it in Doctors Profile before accepting appointment'
            ], 422);
        }

        $appointment->meeting_link = $doctorProfile->meeting_link;

        $appointment->status = 'accepted';
        $appointment->save();

        return response()->json(['ok' => true, 'message' => 'Request accepted']);
    }

    // Reject a pending video appointment
    public function reject(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);

        if (!in_array($appointment->status, ['pending', 'accepted'])) {
            return response()->json(['message' => 'Only pending requests can be rejected'], 422);
        }

        $appointment->status = 'rejected';
        $appointment->save();

        return response()->json(['ok' => true, 'message' => 'Request rejected']);
    }

    public function completed(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);

        if ($appointment->status !== 'accepted') {
            return response()->json(['message' => 'Only accepted requests can be completed'], 422);
        }

        // check if prescription was issued
        $required = $request->boolean('prescription_is_required') ?? false;
        if (!$appointment->prescription_issued && !$required)
        {
            return response()->json(['status' => 'error', 'message' => 'You need to issue a prescrition before completing this appointment'], 422);
        }

        $appointment->status = 'completed';
        $appointment->save();
        
        return response()->json(['ok' => true, 'message' => 'Request completed']);
    }

    // Save (or update) the meeting link after acceptance
    public function saveMeetingLink(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);

        $data = $request->validate([
            'meeting_link' => ['required', 'string', 'max:2048'],
        ]);

        if (! in_array($appointment->status, ['accepted', 'scheduled'])) {
            return response()->json(['message' => 'Set meeting link only after accepting the request'], 422);
        }

        // Assuming your column is `meeting_link` (rename if different)
        $appointment->meeting_link = $data['meeting_link'];
        $appointment->save();

        return response()->json(['ok' => true, 'message' => 'Meeting link saved']);
    }

    private function authorizeAppointment(Appointment $appointment): void
    {
        if ((int)$appointment->doctor_id !== (int)Auth::id()) {
            abort(403, 'Unauthorized');
        }
        if ($appointment->type !== 'video') {
            abort(422, 'Not a video appointment');
        }
    }
}
