<div class="cardx h-100">
    <div class="sec-head d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-user-group"></i> Pending Patient Requests</span>
        <a href="{{ route('doctor.patients', ['tab' => 'pending']) }}" class="text-decoration-none subtle small">
            View all <i class="fa-solid fa-arrow-right-long ms-1"></i>
        </a>
    </div>

    @if ($pendingConvs->isEmpty())
        <a href="{{ route('doctor.patients', ['tab' => 'pending']) }}" class="sec-wrap link-card">
            <div class="empty">
                <div class="ico"><i class="fa-solid fa-user-group"></i></div>
                <div>No new patient requests</div>
            </div>
            <span class="stretched-link"></span>
        </a>
    @else
        <div class="d-flex flex-column gap-2">
            @foreach ($pendingConvs as $c)
                <div class="ps-row d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">{{ $c->patient?->first_name }} {{ $c->patient?->last_name }}
                        </div>
                        <div class="subtle small">Requested {{ $c->created_at?->diffForHumans() }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" data-accept="{{ $c->id }}">
                            <i class="fa-solid fa-check me-1"></i> Accept
                        </button>
                        <button class="btn btn-danger btn-sm" data-reject="{{ $c->id }}">
                            <i class="fa-solid fa-xmark me-1"></i> Reject
                        </button>
                    </div>
                </div>
            @endforeach

            <a href="{{ route('doctor.patients', ['tab' => 'pending']) }}" class="btn btn-ghost w-100">
                See all pending ({{ $pendingRequestsCount }})
            </a>
        </div>
    @endif
</div>
