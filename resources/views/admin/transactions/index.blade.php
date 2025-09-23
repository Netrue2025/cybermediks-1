@extends('layouts.admin')
@section('title', 'Transactions')

@push('styles')
    <style>
        .section-subtle {
            color: var(--muted)
        }

        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-striped-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            --bs-table-hover-color: var(--text);
            color: var(--text)
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

        .badge-credit {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .12)
        }

        .badge-debit {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .10)
        }

        .amt {
            font-weight: 700
        }

        .amt-pos {
            color: #22c55e
        }

        .amt-neg {
            color: #f87171
        }

        .input-icon {
            position: relative
        }

        .input-icon .prefix {
            position: absolute;
            left: .6rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2
        }

        .input-icon input {
            padding-left: 2rem
        }
    </style>
@endpush

@section('content')
    {{-- Filters --}}
    <div class="cardx mb-3">
        <form class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small section-subtle mb-1">Type</label>
                <select class="form-select" name="type">
                    <option value="">All types</option>
                    <option value="credit" @selected($type === 'credit')>Credit</option>
                    <option value="debit" @selected($type === 'debit')>Debit</option>
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label small section-subtle mb-1">Search</label>
                <div class="input-icon">
                    <span class="prefix"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="q" value="{{ $q }}"
                        placeholder="Reference, purpose, or user">
                </div>
            </div>

            <div class="col-md-2">
                <button class="btn btn-gradient w-100">
                    <i class="fa-solid fa-sliders me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="cardx">
        <div class="table-responsive">
            <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                <thead>
                    <tr class="section-subtle">
                        <th>Date</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Purpose</th>
                        <th>Amount</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tx as $t)
                        @php
                            $isCredit = strtolower($t->type) === 'credit';
                            $badgeCls = $isCredit ? 'badge-credit' : 'badge-debit';
                            $sign = $isCredit ? '+' : '−';
                            $amtCls = $isCredit ? 'amt amt-pos' : 'amt amt-neg';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $t->created_at->format('M d, Y · g:ia') }}</div>
                                <div class="section-subtle small">{{ $t->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    {{ $t->user?->first_name }} {{ $t->user?->last_name }}
                                </div>
                                <div class="section-subtle small">{{ $t->user?->email }}</div>
                            </td>
                            <td>
                                <span class="badge-soft {{ $badgeCls }}">
                                    <i class="fa-solid {{ $isCredit ? 'fa-circle-arrow-down' : 'fa-circle-arrow-up' }}"></i>
                                    {{ ucfirst($t->type) }}
                                </span>
                            </td>
                            <td>{{ $t->purpose ?: '—' }}</td>
                            <td class="{{ $amtCls }}">{{ $sign }}${{ number_format($t->amount, 2) }}</td>
                            <td><code>{{ $t->reference ?: '—' }}</code></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center section-subtle py-4">No transactions</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $tx->withQueryString()->onEachSide(1)->links() }}</div>
    </div>
@endsection
