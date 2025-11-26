@extends('layouts.admin')
@section('title', 'Revenue by Country')

@push('styles')
    <style>
        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            color: var(--text);
        }

        .badge-soft {
            display: inline-flex;
            gap: .35rem;
            padding: .18rem .55rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #0e162b;
            font-size: .8rem;
        }

        .amt {
            font-weight: 700;
        }

        .amt-pos {
            color: #22c55e
        }

        .amt-neg {
            color: #f87171
        }

        tr th,
        tr td {
            color: white !important;
        }
    </style>
@endpush

@section('content')
    {{-- Filters --}}
    <div class="cardx mb-3">
        <form class="row g-2 align-items-end" method="get">
            <div class="col-md-2">
                <label class="form-label small section-subtle">Role</label>
                <select name="role" class="form-select">
                    <option value="">All roles</option>
                    @foreach (['patient', 'doctor', 'pharmacy', 'dispatcher', 'labtech'] as $rname)
                        <option value="{{ $rname }}" @selected(request('role') === $rname)>{{ ucfirst($rname) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small section-subtle">Type</label>
                <select name="type" class="form-select">
                    <option value="">Credit + Debit</option>
                    <option value="credit" @selected(request('type') === 'credit')>Credit only</option>
                    <option value="debit" @selected(request('type') === 'debit')>Debit only</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small section-subtle">From</label>
                <input type="date" class="form-control" name="from" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small section-subtle">To</label>
                <input type="date" class="form-control" name="to" value="{{ request('to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small section-subtle">Country (name or ISO2)</label>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="e.g. Nigeria or NG">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-gradient"><i class="fa-solid fa-sliders me-1"></i> Go</button>
            </div>
        </form>
    </div>

    {{-- Totals summary --}}
    <div class="cardx mb-3">
        <div class="d-flex flex-wrap gap-4">
            <div>
                <div class="section-subtle small">Total Credits</div>
                <div class="amt amt-pos">₦{{ number_format($totals->credits ?? 0, 2) }}</div>
            </div>
            <div>
                <div class="section-subtle small">Total Debits</div>
                <div class="amt amt-neg">-₦{{ number_format($totals->debits ?? 0, 2) }}</div>
            </div>
            <div>
                <div class="section-subtle small">Net</div>
                @php $net = (float)($totals->net ?? 0); @endphp
                <div class="amt {{ $net >= 0 ? 'amt-pos' : 'amt-neg' }}">
                    {{ $net >= 0 ? '' : '-' }}₦{{ number_format(abs($net), 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="cardx">
        <div class="table-responsive">
            <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                <thead>
                    <tr class="section-subtle">
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'country_asc' ? 'country_desc' : 'country_asc']) }}"
                                class="link-light text-decoration-none">Country</a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'role_asc' ? 'role_desc' : 'role_asc']) }}"
                                class="link-light text-decoration-none">Role</a>
                        </th>
                        <th class="text-end">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'credits_desc']) }}"
                                class="link-light text-decoration-none">Credits</a>
                        </th>
                        <th class="text-end">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'debits_desc']) }}"
                                class="link-light text-decoration-none">Debits</a>
                        </th>
                        <th class="text-end">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => request('sort') === 'net_asc' ? 'net_desc' : 'net_asc']) }}"
                                class="link-light text-decoration-none">Net</a>
                        </th>
                        <th class="text-end">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'count_desc']) }}"
                                class="link-light text-decoration-none">Tx Count</a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'last_desc']) }}"
                                class="link-light text-decoration-none">Last Tx</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $net = (float) $row->net;
                            $netCls = $net >= 0 ? 'amt amt-pos' : 'amt amt-neg';
                            $iso = strtoupper($row->iso2 ?? '--');
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $row->country }}</div>
                                <div class="section-subtle small">{{ $iso }}</div>
                            </td>
                            <td><span class="badge-soft">{{ ucfirst($row->role) }}</span></td>
                            <td class="text-end amt amt-pos">₦{{ number_format($row->credits, 2) }}</td>
                            <td class="text-end amt amt-neg">-₦{{ number_format($row->debits, 2) }}</td>
                            <td class="text-end {{ $netCls }}">
                                {{ $net >= 0 ? '' : '-' }}₦{{ number_format(abs($net), 2) }}</td>
                            <td class="text-end">{{ number_format($row->tx_count) }}</td>
                            <td class="section-subtle small">{{ \Carbon\Carbon::parse($row->last_tx_at)->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center section-subtle py-4">No data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination (onEachSide=1 style) --}}
        @php
            $paginator = $rows->withQueryString();
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();
            $start = max(1, $current - 1);
            $end = min($last, $current + 1);
            $pages = $paginator->getUrlRange($start, $end);
        @endphp

        @if ($paginator->hasPages())
            <div class="mt-3 d-flex justify-content-center">
                <nav>
                    <ul class="pagination">
                        @if ($paginator->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}"
                                    rel="prev">&laquo;</a></li>
                        @endif

                        @if ($start > 1)
                            <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                            @if ($start > 2)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                        @endif

                        @foreach ($pages as $page => $url)
                            @if ($page == $current)
                                <li class="page-item active" aria-current="page"><span
                                        class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        @if ($end < $last)
                            @if ($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            @endif
                            <li class="page-item"><a class="page-link"
                                    href="{{ $paginator->url($last) }}">{{ $last }}</a></li>
                        @endif

                        @if ($paginator->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}"
                                    rel="next">&raquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif
    </div>
@endsection
