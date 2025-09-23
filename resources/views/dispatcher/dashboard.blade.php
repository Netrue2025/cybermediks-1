@extends('layouts.dispatcher')
@section('title', 'Dashboard')

@push('styles')
    <style>
        :root {
            --bg: #0f172a;
            --panel: #0f1628;
            --card: #101a2e;
            --border: #27344e;
            --text: #e5e7eb;
            --muted: #9aa3b2;
            --chip: #1e293b;
            --chipBorder: #2a3854;
            --accent1: #8758e8;
            --accent2: #e0568a;
            --success: #22c55e;
        }

        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .cardx-soft {
            background: #0f1a2e;
        }

        /* slightly brighter inner cards */
        .metric {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .pill {
            width: 44px;
            height: 44px;
            background: #0b1222;
            border: 1px solid var(--border);
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Section header (thin underline like screenshot) */
        .sec-head {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 10px;
        }

        .sec-head i {
            opacity: .9;
        }

        .sec-wrap {
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #0f1a2e;
            padding: 14px;
        }

        .subtle {
            color: var(--muted);
        }

        /* Empty states */
        .empty {
            color: #9aa3b2;
            text-align: center;
            padding: 32px 8px;
        }

        .empty .ico {
            font-size: 28px;
            opacity: .7;
            margin-bottom: 8px;
        }

        /* Quick actions row */
        .qa-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            background: #0f1a2e;
            height: 100%;
        }

        .qa-title {
            font-weight: 700;
        }

        .qa-note {
            color: #a1aec3;
            font-size: .92rem;
        }

        /* Toggle row inside Profile & Settings */
        .ps-row {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            background: #0e182b;
        }

        /* Credential management */
        .cred-card {
            background: #121a2c;
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .btn-ghost {
            background: #0e162b;
            border: 1px solid #283652;
            color: #e5e7eb;
        }

        .btn-ghost:hover {
            background: #1a2845;
            color: #fff;
        }

        .btn-gradient {
            background-image: linear-gradient(90deg, var(--accent1), var(--accent2));
            border: 0;
            color: #fff;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            background: #0b1222;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6b7280;
            box-shadow: none;
        }

        .link-card {
            position: relative;
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .link-card:hover {
            filter: brightness(1.06);
        }

        .link-card .stretched-link {
            position: static;
        }

        /* keep a11y while we control position */
        .cursor-pointer {
            cursor: pointer;
        }

        .pill-ghost {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0b1222;
            border: 1px solid var(--border);
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

        .badge-on {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43;
        }

        .badge-off {
            background: rgba(239, 68, 68, .08);
            border-color: #6f2b2b;
        }

        .input-icon {
            position: relative
        }

        .input-icon-prefix {
            position: absolute;
            left: .6rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2
        }

        .input-icon-suffix {
            position: absolute;
            right: .6rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2
        }

        .input-icon .form-control {
            padding-left: 1.6rem
        }

        .chips-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            min-height: 38px;
            padding: .25rem;
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 8px
        }

        .chip2 {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #13203a;
            border: 1px solid #2a3854;
            border-radius: 999px;
            padding: .25rem .55rem;
            color: #cfe0ff;
            font-size: .85rem
        }

        .chip2 .x {
            opacity: .7;
            cursor: pointer
        }

        .chip2 .x:hover {
            opacity: 1
        }

        .spec-results {
            position: relative;
            margin-top: .25rem
        }

        .spec-results .menu {
            position: absolute;
            z-index: 10;
            width: 100%;
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 8px;
            max-height: 220px;
            overflow: auto;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .35)
        }

        .spec-results .item {
            padding: .45rem .6rem;
            border-bottom: 1px solid var(--border);
            cursor: pointer
        }

        .spec-results .item:hover {
            background: #111f37
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
                    <div class="pill"><i class="fa-solid fa-bag-shopping fs-5" style="color:#86bcef;"></i></div>
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
                    <div class="d-flex flex-column gap-2">
                        @foreach ($pending as $rx)
                        <i class="text-danger">!!Make sure to negotiate delivery details with the patient.!!</i>
                            @php
                                $pickup = $rx->pharmacy?->address ?? $rx->pharmacy?->pharmacyProfile?->hours; // fallback text if no address
                                $delivery = $rx->patient?->address;
                            @endphp
                            <div class="ps-row d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Rx {{ $rx->code }}</div>
                                    <div class="subtle small">
                                        {{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}
                                        • {{ $rx->created_at->diffForHumans() }}
                                        @if ($rx->total_amount)
                                            • ${{ number_format($rx->total_amount, 2, '.', ',') }}
                                        @endif
                                    </div>
                                    <p class="subtle small">Call: {{ $rx->patient?->phone ?? '—' }}</p>
                                    <div class="subtle small">
                                        <i class="fa-solid fa-store me-1"></i> Pickup: {{ $pickup ?? '—' }}<br>
                                        <i class="fa-solid fa-location-dot me-1"></i> Delivery: {{ $delivery ?? '—' }}
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-dark btn-sm">
                                        <a href="tel:{{ $rx->patient?->phone }}" style="text-decoration: none;"><i class="fa-solid fa-phone me-1"></i> Call</a>
                                    </button>

                                    <button class="btn btn-success btn-sm" data-accept="{{ $rx->id }}">
                                        <i class="fa-solid fa-boxes-packing me-1"></i> Accept
                                    </button>


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

        {{-- Ready/Picked --}}
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
                                $pickup = $rx->pharmacy?->address ?? $rx->pharmacy?->pharmacyProfile?->hours;
                                $delivery = $rx->patient?->address;
                            @endphp
                            <div class="ps-row d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Rx {{ $rx->code }}</div>
                                    <div class="subtle small">
                                        {{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}
                                        • Status: {{ ucfirst($rx->dispense_status) }}
                                        @if ($rx->total_amount)
                                            • ${{ number_format($rx->total_amount, 2, '.', ',') }}
                                        @endif
                                    </div>
                                    <div class="subtle small mt-1">
                                        <i class="fa-solid fa-store me-1"></i> Pickup: {{ $pickup ?? '—' }}<br>
                                        <i class="fa-solid fa-location-dot me-1"></i> Delivery: {{ $delivery ?? '—' }}
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    {{-- Dispatcher typically won’t mark picked; pharmacy will after handoff --}}
                                    <a href="{{ route('pharmacy.prescriptions.show', $rx) }}"
                                        class="btn btn-outline-light btn-sm">View</a>
                                </div>
                            </div>
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
            $(document).on('click', '[data-accept]', function() {
                const id = $(this).data('accept');
                const $btn = $(this);
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
        })();
    </script>
@endpush
