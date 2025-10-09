@php
    use Carbon\Carbon;

    if (!function_exists('ap_status_badge')) {
        function ap_status_badge(?string $s): string
        {
            $s = $s ?: 'scheduled';
            $cls = match ($s) {
                'scheduled', 'accepted' => 'badge-ready',
                'completed' => 'badge-picked',
                'cancelled', 'rejected' => 'badge-cancelled',
                'no_show' => 'badge-pending',
                default => 'badge-pending',
            };
            return "<span class='badge-soft {$cls}'>" . ucwords(str_replace('_', ' ', $s)) . '</span>';
        }
    }
@endphp

<div class="d-flex flex-column gap-3">
    @forelse ($appointments as $a)
        @php
            $when = $a->scheduled_at ? Carbon::parse($a->scheduled_at)->format('M d, Y · g:ia') : 'Appointment';
            $type = str_replace('_', ' ', $a->type ?? 'consult');
            $status = $a->status ?? 'scheduled';
            // If you used the controller I shared earlier, video calls may store $a->meeting_link
            $meeting = $a->meeting_link ?? null;
        @endphp

        <div class="ap-row">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                {{-- LEFT: meta --}}
                <div class="me-2">
                    <div class="ap-when">{{ $when }}</div>
                    <div class="section-subtle">
                        with <strong>{{ $a->doctor?->full_name }}</strong>
                        @if ($a->title)
                            — {{ $a->title }}
                        @endif
                    </div>

                    <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                        <span class="ap-kind">{{ $type }}</span>
                        <span>{!! ap_status_badge($status) !!}</span>

                        {{-- Meeting link (video only) --}}
                        @if ($a->type === 'video' && $meeting && $a->status == 'accepted')
                            <a class="btn btn-outline-light btn-sm" href="{{ $meeting }}" target="_blank"
                                rel="noopener">
                                <i class="fa-solid fa-video me-1"></i> Open Meeting
                            </a>
                        @endif
                    </div>
                </div>

                {{-- RIGHT: actions --}}
                <div class="d-flex flex-wrap gap-2">
                    @if (!empty($a->notes))
                        <button class="btn btn-outline-light btn-sm" data-ap-notes="{{ $a->notes }}">
                            View Notes
                        </button>
                    @endif

                    {{-- Show Dispute if not already disputed --}}
                    @if (($a->status ?? '') !== 'disputed' && !$a->dispute)
                        <button class="btn btn-warning btn-sm js-open-dispute" data-appt-id="{{ $a->id }}"
                            data-appt-when="{{ $when }}" data-doctor="{{ $a->doctor?->full_name }}">
                            Dispute
                        </button>
                    @endif

                    <button class="btn btn-gradient btn-sm" data-book-again data-doctor-id="{{ $a->doctor_id }}">
                        Book Again
                    </button>
                </div>

            </div>
        </div>
    @empty
        <div class="section-subtle">No appointments found.</div>
    @endforelse
</div>

@if ($appointments->hasPages())
    <div class="mt-3">
        {!! $appointments->links() !!}
    </div>
@endif
