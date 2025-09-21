@php
    function rx_dispense_badge($s)
    {
        $s = $s ?: 'pending';
        $cls = match ($s) {
            'ready' => 'badge-ready',
            'picked' => 'badge-picked',
            'cancelled' => 'badge-cancelled',
            default => 'badge-pending',
        };
        return "<span class='badge-soft {$cls}'>" . ucfirst($s) . '</span>';
    }
@endphp

<div class="d-flex flex-column gap-3">
    @forelse ($prescriptions as $rx)
        @php
            $doctorName =
                $rx->doctor?->full_name ?? trim($rx->doctor?->first_name . ' ' . $rx->doctor?->last_name) ?: 'Doctor';
            $itemsPreview = $rx->items->take(1)->pluck('drug')->first();

            // New fields (null-safe)
            $dispense = $rx->dispense_status ?? 'pending';
            $amount = $rx->total_amount;

            // Pretty amount or em dash
            $amountDisplay = is_null($amount) ? '—' : '$' . number_format((float) $amount, 2, '.', ',');

            // Backward-compat: keep your original status in payload if you still use it elsewhere
            $viewPayload = [
                'id' => $rx->id,
                'code' => $rx->code,
                'status' => $rx->status, // legacy/clinical status if any
                'dispense' => $dispense, // NEW: fulfillment status
                'amount' => $amount, // NEW
                'doctor' => $doctorName,
                'notes' => $rx->notes,
                'items' => $rx->items
                    ->map(
                        fn($i) => [
                            'drug' => $i->drug,
                            'dose' => $i->dose,
                            'frequency' => $i->frequency,
                            'days' => $i->days,
                            'directions' => $i->directions,
                        ],
                    )
                    ->values(),
            ];
        @endphp

        <div class="rx-row">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">
                        {{ $itemsPreview ?? 'Prescription' }}
                        @if ($rx->items->count() > 1)
                            <span class="section-subtle">+{{ $rx->items->count() - 1 }} more</span>
                        @endif
                    </div>
                    <div class="section-subtle small">
                        Prescribed by {{ $doctorName }} • {{ $rx->created_at?->format('M d, Y · g:ia') }}
                    </div>

                    <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                        <span class="rx-badge">Rx {{ $rx->code }}</span>

                        {{-- NEW: fulfillment badge --}}
                        <span>{!! rx_dispense_badge($dispense) !!}</span>

                        {{-- NEW: amount pill --}}
                        <span class="price-pill">{{ $amountDisplay }}</span>

                        {{-- (Optional) show your legacy $rx->status as a subtle chip if you still want it --}}
                        {{-- <span class="rx-status">{{ str_replace('_',' ',$rx->status) }}</span> --}}
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light btn-sm" data-rx-view='@json($viewPayload)'>
                        View
                    </button>

                    {{-- Refill only when it makes sense (example rule: not cancelled) --}}
                    @if ($dispense !== 'cancelled')
                        <button class="btn btn-gradient btn-sm" data-rx-refill="{{ $rx->id }}">Refill</button>
                    @else
                        <button class="btn btn-outline-light btn-sm" disabled>Refill</button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="section-subtle">No prescriptions found.</div>
    @endforelse
</div>

@if ($prescriptions->hasPages())
    <div class="mt-3">{!! $prescriptions->links() !!}</div>
@endif
