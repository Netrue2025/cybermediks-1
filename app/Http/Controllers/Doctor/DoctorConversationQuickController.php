<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
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
        return response()->json(['status' => 'success', 'message' => 'Request accepted ✅']);
    }

    public function close(Conversation $conversation)
    {
        $this->ensureOwned($conversation);
        // allow closing from pending or active
        if (!in_array($conversation->status, ['pending', 'active'])) {
            return response()->json(['status' => 'error', 'message' => 'Already closed'], 422);
        }

        // charge patient for consultation
        $patient = $conversation->patient;
        $doctor = $conversation->doctor;
        $doctorProfile = $doctor->doctorProfile;
        $fee = $doctorProfile?->consult_fee;
        if ($fee && $patient) {
            // Charge the patient
            $patient->wallet_balance -= $fee;
            $patient->save();

            // Pay the doctor
            $doctor->wallet_balance += $fee;
            $doctor->save();

            // Log the transaction (pseudo-code, implement as needed)
            WalletTransaction::create([
                'user_id' => $patient->id,
                'amount' => -$fee,
                'currency' => 'USD',
                'type' => 'debit',
                'reference' => uniqid('txn_'),
                'purpose' => "Consultation fee for conversation ID {$conversation->id}",
            ]);

            WalletTransaction::create([
                'user_id' => $doctor->id,
                'amount' => $fee,
                'currency' => 'USD',
                'type' => 'credit',
                'reference' => uniqid('txn_'),
                'purpose' => "Consultation fee received for conversation ID {$conversation->id}",
            ]);
        }

        $conversation->update(['status' => 'closed']);
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
