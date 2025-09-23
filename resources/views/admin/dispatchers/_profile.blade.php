@php
    /** @var \App\Models\User $dispatcher */
    $initials = strtoupper(substr($dispatcher->first_name, 0, 1)) . strtoupper(substr($dispatcher->last_name, 0, 1));
@endphp

<div class="d-flex align-items-center gap-2 mb-3">
    <div class="avatar-mini" style="width:42px;height:42px">{{ $initials }}</div>
    <div>
        <div class="fw-semibold">{{ $dispatcher->first_name }} {{ $dispatcher->last_name }}</div>
        <div class="section-subtle small">{{ $dispatcher->email }}</div>
        @if ($dispatcher->phone)
            <div class="section-subtle small">{{ $dispatcher->phone }}</div>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="cardx">
            

            <div class="section-subtle small">Joined</div>
            <div class="mb-2">{{ $dispatcher->created_at?->format('M d, Y') ?? '—' }}</div>

            <div class="section-subtle small">Last update</div>
            <div>{{ $dispatcher->updated_at?->diffForHumans() ?? '—' }}</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="cardx">
            <div class="fw-semibold mb-2">Recent Activity</div>
            {{-- If you track dispatcher metrics, you can surface them here. --}}
            <div class="d-flex justify-content-between">
                <div>
                    <div class="section-subtle small">Pending</div>
                    <div class="fw-bold">{{ $stats['pending'] ?? 0 }}</div>
                </div>
                <div>
                    <div class="section-subtle small">Active</div>
                    <div class="fw-bold">{{ $stats['active'] ?? 0 }}</div>
                </div>
                <div>
                    <div class="section-subtle small">Completed</div>
                    <div class="fw-bold">{{ $stats['completed'] ?? 0 }}</div>
                </div>
            </div>

        </div>
    </div>
</div>
