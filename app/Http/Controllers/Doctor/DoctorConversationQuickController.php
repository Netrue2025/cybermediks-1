<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Conversation;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorConversationQuickController extends Controller
{
    protected function ensureOwned(Conversation $c): void
    {
        abort_unless($c->doctor_id === Auth::id(), 403);
    }

    public function accept(Conversation $conversation)
    {
        $this->ensureOwned($conversation);
        // only allow accept from pending
        if ($conversation->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Not in pending state'], 422);
        }
        $conversation->update(['status' => 'active']);
        $appointment = Appointment::where('id', $conversation->appointment_id)->first();
        if ($appointment)
        {
            $appointment->update(['status' => 'accepted']);
        }
        return response()->json(['status' => 'success', 'message' => 'Request accepted ✅']);
    }

    public function reject(Conversation $conversation)
    {
        $this->ensureOwned($conversation);
        // allow closing from pending or active
        if (!in_array($conversation->status, ['pending'])) {
            return response()->json(['status' => 'error', 'message' => 'Already closed'], 422);
        }

        $conversation->update(['status' => 'rejected']);
        $appointment = Appointment::where('id', $conversation->appointment_id)->first();
        if ($appointment)
        {
            $appointment->update(['status' => 'rejected']);
        }
        return response()->json(['status' => 'success', 'message' => 'Request rejected ✅']);
    }

    public function close(Request $request, Conversation $conversation)
    {
        $this->ensureOwned($conversation);
        // allow closing from pending or active
        if (!in_array($conversation->status, ['pending', 'active'])) {
            return response()->json(['status' => 'error', 'message' => 'Already closed'], 422);
        }

        // check if prescription was issued
        $appointment = Appointment::where('id', $conversation->appointment_id)->where('doctor_id', Auth::id())->first();
        $required = $request->boolean('prescription_is_required') ?? false;

        if (!$appointment)
        {
            return response()->json(['status' => 'error', 'message' => 'Appointment not found'], 422);
        }

        if (!$appointment->prescription_issued && !$required)
        {
            return response()->json(['status' => 'error', 'message' => 'You must issue prescription before closing chat'], 422);
        }

        $conversation->update(['status' => 'closed']);
        if ($appointment)
        {
            $appointment->update(['status' => 'completed']);
        }
        return response()->json(['status' => 'success', 'message' => 'Conversation closed ✅']);
    }

    public function reopen(Conversation $conversation)
    {
        $this->ensureOwned($conversation);
        // allow reopening from closed
        if ($conversation->status !== 'closed') {
            return response()->json(['status' => 'error', 'message' => 'Not closed'], 422);
        }
        $conversation->update(['status' => 'active']);
        return response()->json(['status' => 'success', 'message' => 'Conversation reopened ✅']);
    }
}
