<div class="cardx h-100">
    <div class="sec-head d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-check-double" style="color:#22c55e;"></i> Active Consultations</span>
        <a href="{{ route('doctor.messenger', ['filter' => 'active']) }}" class="text-decoration-none subtle small">
            View all <i class="fa-solid fa-arrow-right-long ms-1"></i>
        </a>
    </div>

    @if ($activeConvs->isEmpty())
        <a href="{{ route('doctor.messenger', ['filter' => 'active']) }}" class="sec-wrap link-card">
            <div class="empty">
                <div class="ico"><i class="fa-regular fa-message"></i></div>
                <div>No active consultations<br>
                    <span class="subtle">Accept a patient request to begin.</span>
                </div>
            </div>
            <span class="stretched-link"></span>
        </a>
    @else
        <div class="d-flex flex-column gap-2">
            @foreach ($activeConvs as $c)
                <div class="ps-row d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">{{ $c->patient?->first_name }} {{ $c->patient?->last_name }}
                        </div>
                        <div class="subtle small">Active since {{ $c->updated_at?->diffForHumans() }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('doctor.messenger', ['conversation' => $c->id, 'filter' => 'active']) }}"
                            class="btn btn-gradient btn-sm">Open Chat</a>
                        {{-- <button class="btn btn-outline-light btn-sm" data-close="{{ $c->id }}">
                                        <i class="fa-solid fa-xmark me-1"></i> Close
                                    </button> --}}
                    </div>
                </div>
            @endforeach

            <a href="{{ route('doctor.messenger', ['filter' => 'active']) }}" class="btn btn-ghost w-100">
                See all active ({{ $activeConsultationsCount }})
            </a>
        </div>
    @endif
</div>
