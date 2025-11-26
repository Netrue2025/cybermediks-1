@extends('layouts.pharmacy')
@section('title', 'Reports')

@push('styles')
    <style>
        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px
        }

        .kpi {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px
        }

        .kpi .val {
            font-size: 1.6rem;
            font-weight: 800
        }

        .row-gap {
            row-gap: 12px
        }

        .tag {
            display: inline-flex;
            gap: .35rem;
            align-items: center;
            border: 1px solid var(--border);
            background: #0e162b;
            border-radius: 999px;
            padding: .2rem .55rem;
            font-size: .8rem
        }

        .tablex {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px
        }

        .tablex tr {
            background: #0f1a2e;
            border: 1px solid var(--border)
        }

        .tablex td,
        .tablex th {
            padding: 10px 12px
        }

        .tablex th {
            background: #101a2e;
            color: #b8c2d6
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .18rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem;
            background: #0e162b
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
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-chart-line"></i>
            <h5 class="m-0">Reports</h5>
        </div>
        <form class="row g-2" method="get">
            <div class="col-lg-3"><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
            <div class="col-lg-3"><input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-lg-3 d-grid"><button class="btn btn-gradient">Apply</button></div>
        </form>
    </div>

    <div class="row row-gap">
        <div class="col-lg-3">
            <div class="kpi">
                <div class="subtle">Revenue</div>
                <div class="val">₦{{ number_format($revenue, 2, '.', ',') }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="kpi">
                <div class="subtle">Filled (Ready + Picked)</div>
                <div class="val">{{ $filled }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="kpi">
                <div class="subtle">Picked Up</div>
                <div class="val">{{ $countPicked }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="kpi">
                <div class="subtle">Pending</div>
                <div class="val">{{ $countPending }}</div>
            </div>
        </div>
    </div>

    <div class="row row-gap mt-1">
        <div class="col-lg-6">
            <div class="cardx h-100">
                <div class="fw-semibold mb-2">Fulfillment mix</div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge-soft badge-ready">Ready: {{ $countReady }}</span>
                    <span class="badge-soft badge-picked">Picked: {{ $countPicked }}</span>
                    <span class="badge-soft badge-pending">Pending: {{ $countPending }}</span>
                    <span class="badge-soft badge-cancelled">Cancelled: {{ $countCanceled }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="cardx h-100">
                <div class="fw-semibold mb-2">Low stock (top 10)</div>
                @if ($lowStock->isEmpty())
                    <div class="subtle">No low stock items.</div>
                @else
                    <table class="tablex">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Qty</th>
                                <th>Reorder at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowStock as $it)
                                <tr>
                                    <td>{{ $it->name }}</td>
                                    <td>{{ $it->qty }}</td>
                                    <td>{{ $it->reorder_level }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="cardx mt-2">
        <div class="fw-semibold mb-2">Top medicines</div>
        @if ($topMeds->isEmpty())
            <div class="subtle">No data.</div>
        @else
            <table class="tablex">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topMeds as $m)
                        <tr>
                            <td>{{ $m->name ?: '—' }}</td>
                            <td>{{ $m->cnt }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
