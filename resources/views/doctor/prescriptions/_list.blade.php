@php
    function statusBadge($s)
    {
        $cls = match ($s) {
            'ready' => 'badge-ready',
            'picked' => 'badge-picked',
            'cancelled' => 'badge-cancelled',
            default => 'badge-pending',
        };
        return "<span class='badge-soft {$cls}'>" . ucfirst($s ?? 'pending') . '</span>';
    }
@endphp

@if ($prescriptions->isEmpty())
    <div class="text-center subtle py-4">No prescriptions found.</div>
@else
    <div class="rx-table">
        <div class="rx-head">
            <div>Rx</div>
            <div>Patient</div>
            <div>Items</div>
            <div>Amount</div>
            <div>Status / Open</div>
        </div>

        @foreach ($prescriptions as $rx)
            @php
                // support either 'medicine' or 'drug' column names
                $names = $rx->items
                    ?->map(function ($i) {
                        return $i->medicine ?? ($i->drug ?? null);
                    })
                    ->filter()
                    ->values();
                $itemsPreview = $names->take(3)->implode(', ');
                if ($names->count() > 3) {
                    $itemsPreview .= '…';
                }

                $amount = is_null($rx->total_amount)
                    ? '—'
                    : '$' . number_format((float) $rx->total_amount, 2, '.', ',');
            @endphp

            <div class="rx-rowx">
                <div class="rx-col">
                    <div class="fw-semibold">#{{ $rx->code }}</div>
                    <div class="rx-cell-sub">{{ $rx->created_at?->format('M d, Y · g:ia') }}</div>
                </div>

                <div class="rx-col">
                    <div class="fw-semibold">{{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}</div>
                    <div class="rx-cell-sub">{{ $rx->patient?->email }}</div>
                </div>

                <div class="rx-col">
                    <div class="rx-items">{{ $itemsPreview ?: '—' }}</div>
                </div>

                <div class="rx-col">{{ $amount }}</div>

                <div class="rx-col d-flex align-items-center gap-2">
                    {!! statusBadge($rx->dispense_status) !!}
                    <a class="btn-ico" title="Open" href="{{ route('doctor.prescriptions.show', $rx) }}">
                        <i class="fa-regular fa-folder-open"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    @if ($prescriptions->hasPages())
        <div class="mt-3">{!! $prescriptions->links() !!}</div>
    @endif
@endif
