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

                {{-- ... inside the action buttons area ... --}}
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light btn-sm"
                        data-rx-view='@json($viewPayload)'>View</button>

                    @php
                        $canBuy = !in_array($rx->dispense_status ?? 'pending', ['picked', 'cancelled'], true);
                        $pharmacyName = $rx->pharmacy?->first_name
                            ? $rx->pharmacy->first_name . ' ' . $rx->pharmacy->last_name
                            : $rx->pharmacy?->name;
                    @endphp

                    @if ($canBuy)
                        <button class="btn btn-success btn-sm" data-rx-buy="{{ $rx->id }}">
                            <i class="fa-solid fa-cart-shopping me-1"></i>
                            {{ $rx->pharmacy_id ? 'Change Pharmacy' : 'Buy' }}
                        </button>
                    @else
                        <button class="btn btn-outline-light btn-sm" disabled>Buy</button>
                    @endif

                    {{-- Patient price confirmation (pharmacy) --}}
                    @if (($rx->dispense_status ?? 'pending') === 'price_assigned')
                        <button class="btn btn-success btn-sm" data-pharm-confirm="{{ $rx->id }}">
                            Confirm Price ({{ $rx->total_amount ? '$' . number_format($rx->total_amount, 2) : '—' }})
                        </button>
                    @endif

                    {{-- NEW: Patient confirms dispatcher fee --}}
                    @if (($rx->dispense_status ?? 'pending') === 'dispatcher_price_set')
                        <button class="btn btn-success btn-sm" data-dsp-confirm="{{ $rx->id }}">
                            Confirm Delivery Fee
                            ({{ $rx->dispatcher_price ? '$' . number_format($rx->dispatcher_price, 2) : '—' }})
                        </button>
                    @endif
                </div>

                {{-- Delivery chip if confirmed --}}
                @if (($rx->dispense_status ?? 'pending') === 'dispatcher_price_confirmed')
                    <div class="mt-2">
                        <span class="badge-soft">
                            <i class="fa-solid fa-truck me-1"></i>
                            Delivery fee confirmed:
                            {{ $rx->dispatcher_price ? '$' . number_format($rx->dispatcher_price, 2) : '—' }}
                        </span>
                    </div>
                @endif


                @if ($rx->pharmacy_id)
                    <div class="mt-2">
                        <span class="badge-soft">
                            <i class="fa-solid fa-store me-1"></i>
                            {{ $pharmacyName ?? 'Selected pharmacy' }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="section-subtle">No prescriptions found.</div>
    @endforelse
</div>

@if ($prescriptions->hasPages())
    <div class="mt-3">{!! $prescriptions->links() !!}</div>
@endif
