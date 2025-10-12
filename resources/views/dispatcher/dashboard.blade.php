@extends('layouts.dispatcher')
@section('title', 'Dashboard')

@push('styles')
    <style>
        /* ===== THEME PRIMITIVES (match patient theme for consistency) ===== */
        :root {
            --bg: #0f172a;
            --panel: #0f1628;
            --card: #101a2e;
            --border: #27344e;
            --text: #e5e7eb;
            --muted: #9aa3b2;
            --accent1: #8758e8;
            --accent2: #e0568a;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --focus: rgba(135, 88, 232, .55);
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .cardx {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
        }

        .metric {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .subtle {
            color: var(--muted);
        }

        .pill {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: .4rem .6rem;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--accent1), var(--accent2));
            border: 0;
            color: #fff;
            font-weight: 700;
        }

        .btn-outline-light {
            border-color: #3a4a69;
            color: #dbe3f7;
        }

        .btn-outline-light:hover {
            background: #1a2845;
            color: #fff;
        }

        .btn-success {
            background: var(--success);
            border-color: var(--success);
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .85rem;
            background: #0e162b;
        }

        .badge-note {
            background: #1f2a44;
            border-color: #2b3b5d;
        }

        .badge-ok {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43;
        }

        .badge-warn {
            background: rgba(245, 158, 11, .12);
            border-color: #7a5205;
        }

        .note-alert {
            background: #2a1f00;
            border: 1px solid #5b4200;
            color: #ffd78a;
            border-radius: 10px;
        }

        .ps-row {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
        }

        .ps-row:focus-within {
            outline: 2px solid var(--focus);
        }

        .input-group-text {
            background: #0b1222;
            color: var(--text);
            border-color: var(--border);
        }

        .form-control {
            background: #0b1222;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .form-control:focus {
            border-color: var(--accent1);
            box-shadow: 0 0 0 .18rem var(--focus);
        }

        .empty {
            padding: 24px;
            text-align: center;
            color: var(--muted);
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        /* Skeleton shimmer */
        .skeleton {
            position: relative;
            overflow: hidden;
            background: #14223e;
            border-radius: 8px;
            min-height: 12px;
        }

        .skeleton::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .08), transparent);
            transform: translateX(-100%);
            animation: shimmer 1.4s infinite;
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
@endpush

@section('content')
    {{-- ===== METRICS ===== --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="cardx h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Pending deliveries</div>
                        <div class="metric">{{ $pendingCount }}</div>
                    </div>
                    <div class="pill" aria-hidden="true"><i class="fa-solid fa-file-prescription fs-5"
                            style="color:#efed86;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Active deliveries</div>
                        <div class="metric">{{ $activeCount }}</div>
                    </div>
                    <div class="pill" aria-hidden="true"><i class="fa-solid fa-truck fs-5" style="color:#86bcef;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Revenue (Today)</div>
                        <div class="metric">${{ number_format($revenueToday, 2, '.', ',') }}</div>
                    </div>
                    <div class="pill" aria-hidden="true"><i class="fa-solid fa-dollar-sign fs-5"
                            style="color:#86efac;"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PENDING ===== --}}
    <div class="row g-3 mt-1">
        @if ($pendingOrders->isEmpty())
            <div class="col-12">
                <div class="empty"><i class="fa-regular fa-face-smile me-1"></i> No pending deliveries right now.</div>
            </div>
        @else
            <div class="col-12">
                <div class="note-alert py-2 px-3 mb-2 small">
                    <i class="fa-solid fa-triangle-exclamation me-1" aria-hidden="true"></i>
                    Make sure to <strong>negotiate delivery details</strong> (time window, address confirmation) with
                    the patient.
                </div>
            </div>
            <div class="col-12 d-flex flex-column gap-2">
                @foreach ($pendingOrders as $order)
                    @php
                        $rx = $order->prescription;
                        $pickup = $rx?->pharmacy?->address ?? '—';
                        $delivery = $rx?->patient?->address ?? '—';
                        $phone = $rx?->patient?->phone ?? '';
                        $st = $order->status ?? 'ready';
                        $canSetFee = in_array($st, ['ready', 'dispatcher_price_set'], true);
                    @endphp
                    <article class="ps-row d-flex justify-content-between align-items-start"
                        aria-label="Pending delivery Rx {{ $rx?->code }}">
                        <div class="pe-2">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <div class="fw-semibold">Rx {{ $rx?->code }}</div>
                                <span class="badge-soft"><i
                                        class="fa-regular fa-clock me-1"></i>{{ $order->created_at->diffForHumans() }}</span>
                                @if (!is_null($order->items_subtotal))
                                    <span class="badge-soft"><i
                                            class="fa-solid fa-dollar-sign me-1"></i>{{ number_format($order->items_subtotal, 2, '.', ',') }}</span>
                                @endif
                                <span class="badge-soft badge-warn">{{ ucwords(str_replace('_', ' ', $st)) }}</span>
                            </div>
                            <div class="subtle small mt-1">{{ $rx?->patient?->first_name }} {{ $rx?->patient?->last_name }}
                                • Status: {{ ucwords(str_replace('_', ' ', $st)) }}</div>
                            <div class="subtle small mt-2">
                                <div><i class="fa-solid fa-store me-1"></i><strong>Pickup:</strong> {{ $pickup }}
                                </div>
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
                            <span>Delivery Fee: <b>${{ $order->dispatcher_price }}</b></span>
                            {{-- @if ($canSetFee)
                                <label for="dspFee{{ $order->id }}" class="visually-hidden">Delivery fee</label>
                                <div class="input-group input-group-sm" style="width:220px;">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        value="{{ $order->dispatcher_price }}" id="dspFee{{ $order->id }}"
                                        placeholder="Delivery fee" autocomplete="off">
                                    <button class="btn btn-outline-light" data-dsp-set="{{ $order->id }}">Set
                                        Fee</button>
                                </div>
                                <div class="text-end subtle small">
                                    {{ $st === 'dispatcher_price_set' ? 'Waiting for patient to confirm delivery fee' : 'Please call patient to negotiate price' }}
                                </div>
                            @endif --}}
                            <div class="d-flex flex-wrap gap-2">
                                @if ($phone)
                                    <a class="btn btn-outline-light btn-sm" href="tel:{{ $phone }}"><i
                                            class="fa-solid fa-phone me-1"></i> Call</a>
                                @endif
                                <button class="btn btn-success btn-sm" data-accept="{{ $order->id }}"
                                    {{ $order->dispatcher_id ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-boxes-packing me-1"></i>
                                    {{ $order->dispatcher_id ? 'Accepted' : 'Accept' }}
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
                @if ($pendingOrders->hasPages())
                    <div class="mt-2">{{ $pendingOrders->withQueryString()->links() }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- ===== ACTIVE ===== --}}
    <div class="row g-3 mt-1">
        @if ($activeOrders->isEmpty())
            <div class="col-12">
                <div class="empty"><i class="fa-regular fa-circle-check me-1"></i> No active deliveries.</div>
            </div>
        @else
            <div class="col-12 d-flex flex-column gap-2">
                @foreach ($activeOrders as $order)
                    @php
                        $rx = $order->prescription;
                        $pickup = $rx?->pharmacy?->address ?? '—';
                        $delivery = $rx?->patient?->address ?? '—';
                        $st = $order->status ?? 'ready';
                        $canSetFee = in_array($st, ['ready', 'dispatcher_price_set'], true);
                    @endphp
                    <article class="ps-row d-flex justify-content-between align-items-start"
                        aria-label="Active delivery Rx {{ $rx?->code }}">
                        <div class="pe-2">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <div class="fw-semibold">Rx {{ $rx?->code }}</div>
                                <span class="badge-soft"><i
                                        class="fa-regular fa-clock me-1"></i>{{ $order->created_at->diffForHumans() }}</span>
                                <span
                                    class="badge-soft {{ $st === 'dispatcher_price_confirm' ? 'badge-ok' : 'badge-note' }}">{{ ucwords(str_replace('_', ' ', $st)) }}</span>
                                @if (!is_null($order->dispatcher_price))
                                    <span class="badge-soft"><i
                                            class="fa-solid fa-dollar-sign me-1"></i>{{ number_format($order->dispatcher_price, 2, '.', ',') }}</span>
                                @endif
                            </div>
                            <div class="subtle small mt-2">
                                <div><i class="fa-solid fa-store me-1"></i><strong>Pickup:</strong> {{ $pickup }}
                                </div>
                                <div><i class="fa-solid fa-location-dot me-1"></i><strong>Delivery:</strong>
                                    {{ $delivery }}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-2">
                            <span>Delivery Fee: ${{ $order->dispatcher_price }}</span>
                            {{-- @if ($canSetFee)
                                <label for="dspFee{{ $order->id }}" class="visually-hidden">Delivery fee</label>
                                <div class="input-group input-group-sm" style="width:220px;">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        value="{{ $order->dispatcher_price }}" id="dspFee{{ $order->id }}"
                                        placeholder="Delivery fee" autocomplete="off">
                                    <button class="btn btn-outline-light" data-dsp-set="{{ $order->id }}">Set
                                        Fee</button>
                                </div>
                                <div class="text-end subtle small">
                                    {{ $st === 'dispatcher_price_set' ? 'Waiting for patient to confirm delivery fee' : 'Propose a delivery fee to the patient' }}
                                </div>
                            @endif --}}
                            <div class="d-flex flex-wrap gap-2">
                                @if ($st === 'picked')
                                    <button class="btn btn-success btn-sm" data-delivered="{{ $order->id }}"><i
                                            class="fa-solid fa-clipboard-check me-1"></i> Mark Delivered</button>
                                @endif
                            </div>
                        </div>
                    </article>
                    <hr class="my-2" style="border-color: var(--border); opacity:.4;">
                @endforeach
                @if ($activeOrders->hasPages())
                    <div class="mt-2">{{ $activeOrders->withQueryString()->links() }}</div>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            // ===== Helpers (idempotent) =====
            window.lockBtn = window.lockBtn || function($btn) {
                if (!$btn || !$btn.length) return;
                $btn.data('ohtml', $btn.html());
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            };
            window.unlockBtn = window.unlockBtn || function($btn) {
                if (!$btn || !$btn.length) return;
                $btn.prop('disabled', false).html($btn.data('ohtml') || '');
            };
            window.flash = window.flash || function(type, msg) {
                const wrap = document.getElementById('flashWrap') || (function() {
                    const d = document.createElement('div');
                    d.id = 'flashWrap';
                    d.style.position = 'fixed';
                    d.style.top = '12px';
                    d.style.right = '12px';
                    d.style.zIndex = '1060';
                    document.body.appendChild(d);
                    return d;
                })();
                const el = document.createElement('div');
                el.className = 'alert alert-' + (type || 'info');
                el.style.background = '#0f1a2e';
                el.style.border = '1px solid var(--border)';
                el.style.color = 'var(--text)';
                el.style.marginTop = '8px';
                el.textContent = msg || '';
                wrap.appendChild(el);
                setTimeout(() => {
                    el.remove();
                }, 3500);
            };

            // Accept order
            $(document).on('click', '[data-accept]', function() {
                const id = $(this).data('accept');
                const $btn = $(this);
                if ($btn.is(':disabled')) return;
                lockBtn($btn);
                $.post(`{{ route('dispatcher.orders.accept', '__ID__') }}`.replace('__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Accepted');
                        window.location.reload();
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });

            // Set delivery fee
            // $(document).on('click', '[data-dsp-set]', function() {
            //     const id = $(this).data('dsp-set');
            //     const $btn = $(this);
            //     const raw = $('#dspFee' + id).val();
            //     const val = parseFloat(raw);
            //     if (isNaN(val) || val < 0) {
            //         flash('danger', 'Enter a valid amount');
            //         return;
            //     }
            //     lockBtn($btn);
            //     $.post(`{{ route('dispatcher.orders.setDeliveryFee', ':id') }}`.replace(':id', id), {
            //             _token: `{{ csrf_token() }}`,
            //             dispatcher_price: val
            //         })
            //         .done(res => {
            //             flash('success', res.message || 'Fee set');
            //             window.location.reload();
            //         })
            //         .fail(err => {
            //             flash('danger', err.responseJSON?.message || 'Failed');
            //         })
            //         .always(() => unlockBtn($btn));
            // });

            // Mark Delivered
            $(document).on('click', '[data-delivered]', function() {
                const id = $(this).data('delivered');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('dispatcher.orders.deliver', '__ID__') }}`.replace('__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Marked delivered');
                        window.location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed to mark delivered');
                    })
                    .always(() => unlockBtn($btn));
            });
        })();
    </script>
@endpush
