<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientMessageController extends Controller
{
    public function index(Request $r)
    {
        return view('patient.messages');
    }

    // Left pane: list conversations for this patient
    public function conversations(Request $r)
    {
        $patientId = $r->user()->id;

        $convos = Conversation::with(['doctor:id,first_name,last_name'])
            ->where('patient_id', $patientId)
            ->latest('updated_at')
            ->get(['id', 'doctor_id', 'updated_at']);

        $data = $convos->map(function ($c) {
            return [
                'id'       => $c->id,
                'doctor'   => ['id' => $c->doctor->id, 'name' => $c->doctor->first_name . ' ' . $c->doctor->last_name],
                'initials' => strtoupper(substr($c->doctor->first_name, 0, 1)) . strtoupper(substr($c->doctor->last_name, 0, 1)),
                'updated_at' => $c->updated_at->diffForHumans(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    // Right pane: load thread
    public function show(Request $r, Conversation $conversation)
    {
        $this->authorizeConversation($r, $conversation);

        $messages = $conversation->messages()
            ->with('sender:id,first_name,last_name')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'sender_id', 'body', 'attachments', 'created_at']);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'doctor' => ['id' => $conversation->doctor_id, 'name' => $conversation->doctor->first_name . ' ' . $conversation->doctor->last_name],
            ],
            'messages' => $messages->map(function ($m) {
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'sender_name' => $m->sender->first_name . ' ' . $m->sender->last_name,
                    'body' => $m->body,
                    'created_at' => $m->created_at->format('M j, g:i A'),
                ];
            }),
        ]);
    }

    // Send message
    public function send(Request $r, Conversation $conversation)
    {
        $this->authorizeConversation($r, $conversation);

        $data = $r->validate(['body' => ['required', 'string', 'max:4000']]);

        $msg = $conversation->messages()->create([
            'sender_id' => $r->user()->id,
            'body'      => $data['body'],
        ]);

        $conversation->touch(); // bump updated_at

        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $r->user()->name,
                'body' => $msg->body,
                'created_at' => $msg->created_at->format('M j, g:i A'),
            ]
        ]);
    }

    // Start a conversation with a doctor (used by “Chat” button from finder)
    public function start(Request $r)
    {
        $data = $r->validate([
            'doctor_id' => ['required', Rule::exists('users', 'id')->where('role', 'doctor')],
        ]);

        $conversation = Conversation::firstOrCreate(
            ['patient_id' => $r->user()->id, 'doctor_id' => $data['doctor_id'], 'appointment_id' => null],
            []
        );

        return response()->json(['redirect' => route('patient.messages') . '?c=' . $conversation->id]);
    }

    protected function authorizeConversation(Request $r, Conversation $conversation)
    {
        abort_unless($conversation->patient_id === $r->user()->id, 403);
    }
}
