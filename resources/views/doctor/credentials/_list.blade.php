@php
    function humanSize($bytes)
    {
        $u = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($u) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return number_format($bytes, $i ? 1 : 0) . ' ' . $u[$i];
    }
@endphp

<div class="d-flex flex-column gap-2">
    @forelse($docs as $d)
        <div class="d-flex justify-content-between align-items-center ps-row">
            <div>
                <div class="fw-semibold">{{ $d->type }}</div>
                <div class="subtle small">
                    {{ $d->file_name }} • {{ humanSize($d->size) }} • Uploaded {{ $d->created_at?->diffForHumans() ?? 'N/A' }}
                </div>
                <br>
                @php
                    $color = match ($d->status) {
                        'rejected' => 'red',
                        'pending' => 'yellow',
                        default => 'green',
                    };
                @endphp

                <span>Status: <span style="color:{{ $color }}">{{ ucfirst($d->status) }}</span></span>
                <br>
                @if ($d->status === 'rejected')
                    <span>Reason: <span>{{ $d->review_notes }}</span></span>
                @endif

            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('doctor.credentials.download', $d) }}" class="btn btn-outline-light btn-sm">Download</a>
                <button class="btn btn-outline-light btn-sm" data-cred-del="{{ $d->id }}">Delete</button>
            </div>
        </div>
        <hr>
    @empty
        <div class="empty py-3">No credentials uploaded yet.</div>
    @endforelse
</div>
