<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
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
        $conversation->update(['status' => 'closed']);
        return response()->json(['status' => 'success', 'message' => 'Conversation closed ✅']);
    }
}
