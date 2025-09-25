@extends('layouts.dispatcher')
@section('title', 'Deliveries')

@push('styles')
    <style>
        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .55rem;
            border-radius: 999px;
            font-size: .85rem;
            border: 1px solid var(--border);
            background: #0e162b;
        }

        .badge-pending {
            border-color: #334155
        }

        .badge-ready {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .08)
        }

        .badge-picked {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .16)
        }

        .badge-cancelled {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .08)
        }

        .badge-note {
            background: #1f2a44;
            border-color: #2b3b5d
        }

        .rx-row {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
        }

        .rx-sub {
            color: #9aa3b2;
            font-size: .9rem
        }
    </style>
@endpush

@php
    function d_status_badge($s)
    {
        $s = $s ?: 'picked';
        $cls = match ($s) {
            'delivered' => 'badge-picked',
            'picked' => 'badge-picked',
            'cancelled' => 'badge-cancelled',
            default => 'badge-note',
        };
        return "<span class='badge-soft {$cls}'>" . ucwords(str_replace('_', ' ', $s)) . '</span>';
    }
@endphp

@section('content')

    {{-- METRICS --}}
    <div class="row g-3 mb-1">
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Picked</div>
                <div class="metric">{{ $countPicked }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Delivered</div>
                <div class="metric">{{ $countDelivered }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Cancelled</div>
                <div class="metric">{{ $countCancelled }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Total Dispatch Fees</div>
                <div class="metric">${{ number_format($sumFees, 2, '.', ',') }}</div>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="cardx mb-3">
        <form class="row g-2">
            <div class="col-md-4">
                <input class="form-control" name="q" value="{{ $q }}"
                    placeholder="Search code/patient/pharmacy/drug">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All past statuses</option>
                    @foreach (['picked', 'delivered', 'cancelled'] as $st)
                        <option value="{{ $st }}" @selected($status === $st)>{{ ucwords($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input class="form-control" type="date" name="from" value="{{ $from }}">
            </div>
            <div class="col-md-2"><input class="form-control" type="date" name="to" value="{{ $to }}">
            </div>
            <div class="col-md-1"><button class="btn btn-gradient w-100">Filter</button></div>
        </form>
    </div>

    {{-- LIST --}}
    <div class="cardx">
        @forelse ($deliveries as $rx)
            @php
                $itemText = $rx->items?->pluck('drug')->take(3)->implode(', ');
                if ($rx->items && $rx->items->count() > 3) {
                    $itemText .= '…';
                }
            @endphp
            <div class="rx-row mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="pe-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="fw-semibold">Rx {{ $rx->code }}</div>
                            {!! d_status_badge($rx->dispense_status) !!}
                            @if (!is_null($rx->dispatcher_price))
                                <span class="badge-soft">
                                    <i
                                        class="fa-solid fa-dollar-sign me-1"></i>{{ number_format($rx->dispatcher_price, 2, '.', ',') }}
                                </span>
                            @endif
                        </div>
                        <div class="rx-sub mt-1">
                            Patient: {{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}
                            • Pharmacy: {{ $rx->pharmacy?->first_name }} {{ $rx->pharmacy?->last_name }}
                            • {{ $rx->updated_at->format('M d, Y · g:ia') }}
                        </div>
                        @if ($itemText)
                            <div class="rx-sub mt-1">Items: {{ $itemText }}</div>
                        @endif
                    </div>
                    <div class="text-end">
                        @if (!is_null($rx->total_amount))
                            <div class="fw-semibold">${{ number_format($rx->total_amount, 2, '.', ',') }}</div>
                        @else
                            <div class="rx-sub">—</div>
                        @endif
                        <div class="rx-sub">Dispatch Fee:
                            {{ is_null($rx->dispatcher_price) ? '—' : '$' . number_format($rx->dispatcher_price, 2, '.', ',') }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center subtle py-4">No deliveries found.</div>
        @endforelse

        <div class="mt-2">{{ $deliveries->links() }}</div>
    </div>
@endsection
