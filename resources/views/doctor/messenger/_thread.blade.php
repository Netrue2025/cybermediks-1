@php
    // If controller passed messages, use them; else fetch here to keep it safe on first render.
    if (!isset($messages) || count($messages) === 0) {
        $messages = \App\Models\Message::with('sender:id,first_name,last_name')
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->take(200)
            ->get();
    }
    $me = auth()->id();
@endphp

@forelse($messages as $m)
    @if ($m->sender_id === $me)
        {!! view('doctor.messenger._bubble_me', ['m' => $m])->render() !!}
    @else
        <div class="bubble them-bubble">
            <div class="small-muted">{{ $m->sender?->first_name }} {{ $m->sender?->last_name }}</div>
            <div>{{ $m->body }}</div>
            <div class="small-muted mt-1">{{ $m->created_at->format('M d, g:ia') }}</div>
        </div>
    @endif
@empty
    <div class="small-muted">No messages yet. Say hello 👋</div>
@endforelse
