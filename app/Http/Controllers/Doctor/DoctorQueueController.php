<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorQueueController extends Controller
{
    // Accept a pending video appointment
    public function accept(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);

        if ($appointment->status !== 'pending') {
            return response()->json(['message' => 'Only pending requests can be accepted'], 422);
        }

        $appointment->status = 'accepted'; // you can use 'scheduled' if that’s your canonical status
        $appointment->save();

        return response()->json(['ok' => true, 'message' => 'Request accepted']);
    }

    // Reject a pending video appointment
    public function reject(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment);

        if ($appointment->status !== 'pending') {
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
