<div class="bubble me-bubble">
    <div>{{ $m->body }}</div>
    <div class="small-muted mt-1">{{ \Carbon\Carbon::parse($m->created_at)->format('M d, g:ia') }}</div>
</div>
