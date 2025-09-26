@extends('layouts.dispatcher')
@section('title', 'Dashboard')

@push('styles')
    <style>
        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .85rem;
            background: #0e162b
        }

        .badge-note {
            background: #1f2a44;
            border-color: #2b3b5d
        }

        .badge-ok {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43
        }

        .note-alert {
            background: #2a1f00;
            border: 1px solid #5b4200;
            color: #ffd78a;
            border-radius: 10px
        }
    </style>
@endpush

@section('content')
    {{-- METRICS --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Pending deliveries</div>
                        <div class="metric">{{ $pendingCount }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-file-prescription fs-5" style="color:#efed86;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Active deliveries</div>
                        <div class="metric">{{ $activeCount }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-truck fs-5" style="color:#86bcef;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Revenue (Today)</div>
                        <div class="metric">${{ number_format($revenueToday, 2, '.', ',') }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-dollar-sign fs-5" style="color:#86efac;"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- PANELS --}}
    <div class="row g-3 mt-1">
        {{-- Pending --}}
        <div class="col-lg-6">
            <div class="cardx h-100">
                <div class="sec-head"><i class="fa-regular fa-circle-question"></i> <span>Pending delivery</span></div>

                @if ($pending->isEmpty())
                    <div class="empty">
                        <div class="ico"><i class="fa-solid fa-box-open"></i></div>
                        <div>No pending deliveries</div>
                    </div>
                @else
                    <div class="note-alert py-2 px-3 mb-2 small">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Make sure to <strong>negotiate delivery details</strong> (fee, time window, address confirmation)
                        with the patient.
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @foreach ($pending as $rx)
                            @php
                                $pickup = $rx->pharmacy?->address ?? ($rx->pharmacy?->pharmacyProfile?->hours ?? '—');
                                $delivery = $rx->patient?->address ?? '—';
                                $phone = $rx->patient?->phone ?? '';
                                $st = $rx->dispense_status ?? 'ready';
                                $canSetFee = in_array($st, ['ready', 'dispatcher_price_set'], true);
                            @endphp

                            <div class="ps-row d-flex justify-content-between align-items-start">
                                <div class="pe-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="fw-semibold">Rx {{ $rx->code }}</div>
                                        <span class="badge-soft">
                                            <i class="fa-regular fa-clock me-1"></i>{{ $rx->created_at->diffForHumans() }}
                                        </span>
                                        @if (!is_null($rx->total_amount))
                                            <span class="badge-soft">
                                                <i
                                                    class="fa-solid fa-dollar-sign me-1"></i>{{ number_format($rx->total_amount, 2, '.', ',') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="subtle small mt-1">
                                        {{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}
                                        • Status: {{ ucwords(str_replace('_', ' ', $st)) }}
                                    </div>

                                    <div class="subtle small mt-2">
                                        <div><i class="fa-solid fa-store me-1"></i><strong>Pickup:</strong>
                                            {{ $pickup }}</div>
                                        <div><i class="fa-solid fa-location-dot me-1"></i><strong>Delivery:</strong>
                                            {{ $delivery }}</div>
                                        <div>
                                            <i class="fa-solid fa-phone me-1"></i><strong>Call:</strong>
                                            @if ($phone)
                                                <a class="link-light text-decoration-none"
                                                    href="tel:{{ $phone }}">{{ $phone }}</a>
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column align-items-end gap-2">
                                    @if ($canSetFee)
                                        <div class="input-group input-group-sm" style="width:220px;">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" min="0" class="form-control"
                                                value="{{ $rx->dispatcher_price }}" id="dspFee{{ $rx->id }}"
                                                placeholder="Delivery fee">
                                            <button class="btn btn-outline-light" data-dsp-set="{{ $rx->id }}">
                                                Set Fee
                                            </button>
                                        </div>
                                        <div class="text-end subtle small">
                                            {{ $st === 'dispatcher_price_set' ? 'Waiting for patient to confirm delivery fee' : 'Please call patient to negotiate price' }}
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2">
                                        @if ($phone)
                                            <a class="btn btn-outline-light btn-sm" href="tel:{{ $phone }}">
                                                <i class="fa-solid fa-phone me-1"></i> Call
                                            </a>
                                        @endif

                                        {{-- Accept (attach dispatcher_id). Disabled if already assigned elsewhere --}}
                                        <button class="btn btn-success btn-sm" data-accept="{{ $rx->id }}"
                                            {{ $rx->dispatcher_id ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-boxes-packing me-1"></i>
                                            {{ $rx->dispatcher_id ? 'Accepted' : 'Accept' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if ($pending->hasPages())
                            <div class="mt-2">{{ $pending->withQueryString()->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Active --}}
        <div class="col-lg-6">
            <div class="cardx h-100">
                <div class="sec-head"><i class="fa-solid fa-truck"></i> <span>Active Deliveries</span></div>

                @if ($active->isEmpty())
                    <div class="empty">
                        <div class="ico"><i class="fa-solid fa-truck"></i></div>
                        <div>No active deliveries</div>
                        <div class="subtle small">Accept deliveries to see them here</div>
                    </div>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach ($active as $rx)
                            @php
                                $pickup = $rx->pharmacy?->address ?? ($rx->pharmacy?->pharmacyProfile?->hours ?? '—');
                                $delivery = $rx->patient?->address ?? '—';
                                $st = $rx->dispense_status ?? 'ready';
                                $canSetFee = in_array($st, ['ready', 'dispatcher_price_set'], true);
                            @endphp
                            <div class="ps-row d-flex justify-content-between align-items-start">
                                <div class="pe-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="fw-semibold">Rx {{ $rx->code }}</div>
                                        <span class="badge-soft">
                                            <i class="fa-regular fa-clock me-1"></i>{{ $rx->created_at->diffForHumans() }}
                                        </span>
                                        <span
                                            class="badge-soft {{ $st === 'dispatcher_price_confirm' ? 'badge-ok' : 'badge-note' }}">
                                            {{ ucwords(str_replace('_', ' ', $st)) }}
                                        </span>
                                        @if (!is_null($rx->dispatcher_price))
                                            <span class="badge-soft">
                                                <i
                                                    class="fa-solid fa-dollar-sign me-1"></i>{{ number_format($rx->dispatcher_price, 2, '.', ',') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="subtle small mt-2">
                                        <div><i class="fa-solid fa-store me-1"></i><strong>Pickup:</strong>
                                            {{ $pickup }}</div>
                                        <div><i class="fa-solid fa-location-dot me-1"></i><strong>Delivery:</strong>
                                            {{ $delivery }}</div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column align-items-end gap-2">
                                    {{-- You can keep tweaking fee until patient confirms --}}
                                    @if ($canSetFee)
                                        <div class="input-group input-group-sm" style="width:220px;">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" min="0" class="form-control"
                                                value="{{ $rx->dispatcher_price }}" id="dspFee{{ $rx->id }}"
                                                placeholder="Delivery fee">
                                            <button class="btn btn-outline-light" data-dsp-set="{{ $rx->id }}">
                                                Set Fee
                                            </button>
                                        </div>
                                        <div class="text-end subtle small">
                                            {{ $st === 'dispatcher_price_set' ? 'Waiting for patient to confirm delivery fee' : 'Propose a delivery fee to the patient' }}
                                        </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2">
                                        {{-- Only show "Mark Delivered" when picked --}}
                                        @if ($st === 'picked')
                                            <button class="btn btn-success btn-sm" data-delivered="{{ $rx->id }}">
                                                <i class="fa-solid fa-clipboard-check me-1"></i> Mark Delivered
                                            </button>
                                        @endif

                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endforeach

                        @if ($active->hasPages())
                            <div class="mt-2">{{ $active->withQueryString()->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            // Accept
            $(document).on('click', '[data-accept]', function() {
                const id = $(this).data('accept');
                const $btn = $(this);
                if ($btn.is(':disabled')) return;
                lockBtn($btn);
                $.post(`{{ route('dispatcher.prescriptions.accept', '__ID__') }}`.replace('__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Accepted');
                        location.reload();
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });

            // Set delivery fee
            $(document).on('click', '[data-dsp-set]', function() {
                const id = $(this).data('dsp-set');
                const $btn = $(this);
                const val = parseFloat($('#dspFee' + id).val());
                if (isNaN(val) || val < 0) {
                    flash('danger', 'Enter a valid amount');
                    return;
                }
                lockBtn($btn);
                $.post(`{{ route('dispatcher.setDeliveryFee', ':id') }}`.replace(':id', id), {
                        _token: `{{ csrf_token() }}`,
                        dispatcher_price: val
                    })
                    .done(res => {
                        flash('success', res.message || 'Fee set');
                        location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });

            // Mark Delivered
            $(document).on('click', '[data-delivered]', function() {
                const id = $(this).data('delivered');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('dispatcher.prescriptions.deliver', '__ID__') }}`.replace('__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Marked delivered');
                        location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed to mark delivered');
                    })
                    .always(() => unlockBtn($btn));
            });

        })();
    </script>
@endpush
