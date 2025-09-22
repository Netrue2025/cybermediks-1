@php
    use Illuminate\Support\Str;
@endphp
<div class="d-flex flex-column gap-2">
    @forelse ($patients as $p)
        @php
            $name = trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? ''));
            $initials =
                Str::upper(Str::substr($p->first_name ?? '', 0, 1) . Str::substr($p->last_name ?? '', 0, 1)) ?: 'PT';
            $visitsLabel = ($p->visits_count ?? 0) . ' ' . Str::plural('visit', (int) ($p->visits_count ?? 0));
        @endphp
        <div class="patient-row">
            <div class="avatar">{{ $initials }}</div>
            <div class="flex-grow-1">
                <div class="fw-semibold">{{ $name !== '' ? $name : 'Patient #' . $p->id }}</div>
                <div class="text-secondary small">{{ $p->email }}</div>
                <div class="mt-1 d-flex gap-2 align-items-center">
                    <span class="chip">{{ $visitsLabel }}</span>
                    @if ($p->has_active_rx)
                        <span class="chip">Active Rx</span>
                    @endif
                    @if ($p->has_followup)
                        <span class="chip">Follow-up</span>
                    @endif
                    @if (!empty($p->last_visit_at))
                        <span class="text-secondary small">Last:
                            {{ \Carbon\Carbon::parse($p->last_visit_at)->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm" data-history="{{ $p->id }}">
                    <i class="fa-regular fa-file-lines me-1"></i> History
                </button>
            </div>
        </div>
    @empty
        <div class="text-center text-secondary py-4">No patients found.</div>
    @endforelse
</div>

@if ($patients->hasPages())
    <div class="mt-3">{!! $patients->links() !!}</div>
@endif
