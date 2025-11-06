@php
    // Badge mapper for commerce/dispatch pipeline (order-first, legacy-safe)
    function rx_dispense_badge($status)
    {
        $s = $status ?: 'pending';
        $cls = match ($s) {
            'quoted', 'patient_confirmed', 'pharmacy_accepted', 'ready', 'dispensing' => 'badge-ready',
            'dispatcher_price_set', 'dispatcher_price_confirm' => 'badge-ready',
            'picked', 'dispatched', 'delivered' => 'badge-picked',
            'cancelled', 'refunded', 'rejected' => 'badge-cancelled',
            default => 'badge-pending',
        };
        return "<span class='badge-soft {$cls}'>" . ucwords(str_replace('_', ' ', $s)) . '</span>';
    }
@endphp

<div class="d-flex flex-column gap-3">
    @forelse ($prescriptions as $rx)
        @php
            $doctorName =
                $rx->doctor?->full_name ?? trim($rx->doctor?->first_name . ' ' . $rx->doctor?->last_name) ?: 'Doctor';

            // Order-first commerce data
            $order = $rx->order; // may be null early in the flow
            $orderStatus = $order?->status; // pending|quoted|patient_confirmed|pharmacy_accepted|ready|...
            $itemsSubtotal = $order?->items_subtotal; // NGN base (assumed)
            $deliveryFee = $order?->dispatcher_price ?? $rx->dispatcher_price; // NGN base (assumed)

            // If both are known, sum; else keep known portion
            $grandTotalCalc =
                !is_null($itemsSubtotal) && !is_null($deliveryFee) ? $itemsSubtotal + $deliveryFee : $itemsSubtotal;

            // Legacy fallbacks
            $legacyDispense = $rx->dispense_status ?? 'pending';
            $legacyAmount = $rx->total_amount; // NGN base (assumed)

            // Preferred numeric amount used for all displays
            $amount = $grandTotalCalc ?? $legacyAmount;

            // Badge source priority: order.status > legacy dispense_status
            $badgeSource = $orderStatus ?? $legacyDispense;

            // Build RxItem.id => OrderItem map (to dim purchased items, show line pricing)
            $orderItemsByRxItemId = $order ? $order->items->keyBy('prescription_item_id') : collect();

            $viewPayload = [
                'id' => $rx->id,
                'code' => $rx->code,
                'status' => $rx->status, // clinical/legacy
                'dispense' => $badgeSource, // commerce badge source
                'amount' => $amount, // keep numeric
                'doctor' => $doctorName,
                'notes' => $rx->notes,
                'items' => $rx->items
                    ->map(function ($i) use ($orderItemsByRxItemId) {
                        $oi = $orderItemsByRxItemId->get($i->id);
                        return [
                            'drug' => $i->drug,
                            'dose' => $i->dose,
                            'frequency' => $i->frequency,
                            'days' => $i->days,
                            'directions' => $i->directions,
                            'status' => $oi?->status, // purchased / quoted / ...
                            'unit_price' => $oi?->unit_price, // NGN base
                            'line_total' => $oi?->line_total, // NGN base
                        ];
                    })
                    ->values(),
            ];

            // Buttons state:
            $commerceState = $orderStatus ?? ($legacyDispense ?? 'pending');
            $disallowChange = in_array(
                $commerceState,
                [
                    'ready',
                    'dispensing',
                    'pharmacy_accepted',
                    'picked',
                    'dispatched',
                    'delivered',
                    'refunded',
                    'cancelled',
                    'rejected',
                    'dispatcher_price_set',
                    'dispatcher_price_confirm',
                ],
                true,
            );
            $canBuy = !$disallowChange;

            $pharmacyName = $rx->pharmacy?->first_name
                ? $rx->pharmacy->first_name . ' ' . $rx->pharmacy->last_name
                : $rx->pharmacy?->name;

            $buyLabel = $rx->pharmacy_id
                ? ($commerceState === 'cancelled' || $commerceState === 'rejected'
                    ? 'Choose New Pharmacy'
                    : 'Change Pharmacy')
                : 'Buy';
        @endphp

        <div class="rx-row">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">
                        {{ $rx->items->take(1)->pluck('drug')->first() ?? 'Prescription' }}
                        @if ($rx->items->count() > 1)
                            <span class="section-subtle">+{{ $rx->items->count() - 1 }} more</span>
                        @endif
                    </div>
                    <div class="section-subtle small">
                        Prescribed by {{ $doctorName }} • {{ $rx->created_at?->format('M d, Y · g:ia') }}
                    </div>

                    <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                        <span class="rx-badge">Rx {{ $rx->code }}</span>
                        <span>{!! rx_dispense_badge($badgeSource) !!}</span>

                        {{-- Items subtotal chip (when we have a quote) --}}
                        @if (!is_null($itemsSubtotal))
                            <span class="badge-soft">
                                <i class="fa-solid fa-prescription-bottle-medical me-1"></i>
                                Items: @money($itemsSubtotal)
                            </span>
                        @endif

                        {{-- Delivery fee chip (when proposed/confirmed) --}}
                        @if (!is_null($deliveryFee))
                            <span class="badge-soft">
                                <i class="fa-solid fa-truck me-1"></i>
                                Delivery: @money($deliveryFee)
                            </span>
                        @endif

                        {{-- Grand total pill (items + delivery when both known, else legacy/partial; else legacy total) --}}
                        <span class="price-pill">
                            {{ is_null($amount) ? '—' : money_display($amount) }}
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-column align-items-end gap-2">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-light btn-sm" data-rx-view='@json($viewPayload)'>
                            View
                        </button>

                        @if ($canBuy)
                            <button class="btn btn-success btn-sm" data-rx-buy="{{ $rx->id }}">
                                <i class="fa-solid fa-cart-shopping me-1"></i>{{ $buyLabel }}
                            </button>
                        @else
                            <button class="btn btn-outline-light btn-sm" disabled>Buy</button>
                        @endif
                    </div>

                    {{-- Legacy confirm price (kept for backward compatibility) --}}
                    @if (($rx->dispense_status ?? 'pending') === 'price_assigned')
                        <button class="btn btn-success btn-sm" data-id="{{ $rx->id }}" id="btnConfirmPrice">
                            Confirm Price ({{ $rx->total_amount ? money_display($rx->total_amount) : '—' }})
                        </button>
                    @endif

                    {{-- NEW: Confirm Items (order quote) --}}
                    @if ($order && $order->status === 'quoted')
                        <form method="POST" action="{{ route('patient.prescriptions.confirmPrice', $rx) }}">
                            @csrf
                            <button class="btn btn-success btn-sm">
                                Confirm Items
                                ({{ $order->items_subtotal ? money_display($order->items_subtotal) : '—' }})
                            </button>
                        </form>
                    @endif

                    {{-- Confirm Dispatcher Delivery Fee (uses ORDER now) --}}
                    @if (($order && $order->status === 'dispatcher_price_set') || $rx->dispense_status === 'dispatcher_price_set')
                        <button class="btn btn-success btn-sm" data-dsp-confirm-order="{{ $order->id }}">
                            Confirm Delivery Fee ({{ !is_null($deliveryFee) ? money_display($deliveryFee) : '—' }})
                        </button>
                    @endif

                    {{-- Selected pharmacy chip --}}
                    @if ($rx->pharmacy_id)
                        <div class="mt-1">
                            <span class="badge-soft">
                                <i class="fa-solid fa-store me-1"></i>
                                {{ $pharmacyName ?? 'Selected pharmacy' }}
                            </span>
                        </div>
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

@if (!empty($acceptedAppt) && ($meet_remaining ?? 0) > 60)
    <!-- Accepted Appointment Modal -->
    <div class="modal fade" id="apAcceptedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:#121a2c;border:1px solid var(--border);border-radius:18px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-video me-2"></i>Appointment Accepted
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-2">
                        <div class="subtle small">Doctor</div>
                        <div id="apModalDoctor" class="fw-semibold">
                            {{ $acceptedAppt->doctor?->full_name ?? ($acceptedAppt->doctor?->name ?? '—') }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="subtle small">Scheduled time</div>
                        <div id="apModalWhen" class="fw-semibold">
                            {{ optional($acceptedAppt->scheduled_at)->format('M d, Y · g:ia') ?? '—' }}
                        </div>
                    </div>
                    <h3 class="mb-2">
                        Time left: <span id="meetingCountdown">--:--</span>
                    </h3>

                    <div id="meetingCountdownMeta" data-end-epoch="{{ (int) ($meet_end_epoch ?? 0) }}"
                        data-now-epoch="{{ (int) ($meet_now_epoch ?? 0) }}"></div>

                    <div class="alert alert-info mt-3 mb-0 small"
                        style="background:#0f1a2e;border:1px solid var(--border);color:#cfe0ff;">
                        Make sure you’re ready a few minutes early. Test your mic/camera before joining.
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <a id="apJoinNow" href="{{ $acceptedAppt->meeting_link }}" target="_blank" rel="noopener"
                        class="btn btn-gradient">
                        <i class="fa-solid fa-video me-1"></i> Join meeting
                    </a>
                    <button style="display: none" id="closeModal" data-bs-dismiss="modal"></button>
                    <button class="btn btn-outline-light" id="endAppointment" data-apt-id="{{ $acceptedAppt->id }}">
                        End Appointment
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
