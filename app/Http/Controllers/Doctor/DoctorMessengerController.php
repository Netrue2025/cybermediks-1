<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorMessengerController extends Controller
{
    public function index(Request $request)
    {
        $docId = Auth::id();

        // Create or open conversation via ?patient_id=...
        $openConversationId = null;
        if ($pid = $request->integer('patient_id')) {
            $conv = Conversation::firstOrCreate(
                ['doctor_id' => $docId, 'patient_id' => $pid],
                ['appointment_id' => null]
            );
            $openConversationId = $conv->id;
        } else {
            // pick most recent by last message or created_at
            $openConversationId = Conversation::where('doctor_id', $docId)
                ->orderByDesc(DB::raw('COALESCE(created_at)'))
                ->value('id');
        }

        $conversations = Conversation::query()
            ->where('doctor_id', $docId)
            ->with(['patient:id,first_name,last_name'])
            ->withCount(['messages'])
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1);
            }]) // for preview
            ->orderByDesc(DB::raw('COALESCE(created_at)'))
            ->paginate(30);

        $active = $openConversationId
            ? Conversation::where('doctor_id', $docId)
            ->with(['patient:id,first_name,last_name'])
            ->find($openConversationId)
            : null;

        // First page render with list + (optionally) active thread
        return view('doctor.messenger.index', compact('conversations', 'active'));
    }

    public function show(Conversation $conversation)
    {
        // authz: doctor must own it
        abort_unless($conversation->doctor_id === Auth::id(), 403);

        $messages = Message::with('sender:id,first_name,last_name')
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->take(200) // cap
            ->get();

        // mark unread (patient→doctor) as read
        Message::where('conversation_id', $conversation->id)
            ->whereNull('read_at')
            ->where('sender_id', '<>', Auth::id())
            ->update(['read_at' => now()]);

        return view('doctor.messenger._thread', compact('conversation', 'messages'))->render();
    }

    public function send(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->doctor_id === Auth::id(), 403);

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        if ($conversation->status !== 'active') {
            return response()->json(['status' => 'error', 'message' => 'Conversation is not active'], 422);
        }

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => Auth::id(),
            'body'            => $data['body'],
            'attachments'     => null,
        ]);

        // Return a minimal HTML bubble to append
        return response()->json([
            'status' => 'ok',
            'html'   => view('doctor.messenger._bubble_me', ['m' => $msg])->render(),
        ]);
    }
}
