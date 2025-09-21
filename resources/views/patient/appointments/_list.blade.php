@php use Carbon\Carbon; @endphp

<div class="d-flex flex-column gap-3">
    @forelse ($appointments as $a)
        <div class="ap-row">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="ap-when">{{ Carbon::parse($a->scheduled_at)->format('M d, Y · g:ia') }}</div>
                    <div class="section-subtle">
                        with <strong>{{ $a->doctor?->full_name }}</strong>
                        @if ($a->title)
                            — {{ $a->title }}
                        @endif
                    </div>
                    <div class="mt-2"><span class="ap-kind">{{ str_replace('_', ' ', $a->type) }}</span></div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light btn-sm" data-ap-notes="{{ $a->notes }}">View Notes</button>
                    <button class="btn btn-gradient btn-sm" data-book-again data-doctor-id="{{ $a->doctor_id }}">Book
                        Again</button>
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
