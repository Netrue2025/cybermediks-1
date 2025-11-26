@extends('layouts.admin')

@section('title', 'Withdrawals Management')

@section('content')
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .form-control {
            background-color: #0f172a;
            border: 1px solid #334155;
            color: #f8fafc;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-select {
            background-color: #0f172a;
            border: 1px solid #334155;
            color: #f8fafc;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .table {
            background-color: #1e293b;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .table th {
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
            color: #94a3b8;
            font-weight: 500;
            padding: 12px 16px;
            text-align: left;
        }

        .table td {
            border-bottom: 1px solid #334155;
            padding: 16px;
            vertical-align: top;
        }

        .table tbody tr:hover {
            background-color: rgba(51, 65, 85, 0.3);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid;
        }

        .badge-pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.2);
        }

        .badge-approved {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-color: rgba(59, 130, 246, 0.2);
        }

        .badge-paid {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-color: rgba(34, 197, 94, 0.2);
        }

        .badge-rejected,
        .badge-failed {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.2);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-success {
            background-color: #22c55e;
            color: white;
            border-color: #22c55e;
        }

        .btn-success:hover {
            background-color: #16a34a;
            border-color: #16a34a;
        }

        .btn-outline {
            background-color: transparent;
            color: #f8fafc;
            border-color: #334155;
        }

        .btn-outline:hover {
            background-color: rgba(51, 65, 85, 0.5);
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        .text-muted {
            color: #94a3b8;
        }

        .text-mono {
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
        }

        .search-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            width: 16px;
            height: 16px;
        }

        .search-input {
            padding-left: 40px;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #1e293b;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            border: 1px solid #334155;
            border-radius: 6px;
            z-index: 1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-item {
            color: #94a3b8;
            padding: 8px 12px;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }

        .dropdown-item:hover {
            background-color: rgba(51, 65, 85, 0.5);
            color: #f8fafc;
        }
    </style>
    <style>
        /* Override table background to match page bg (#0f172a) */
        .table {
            background-color: #0f172a !important;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #0f172a !important;
            border-bottom: 1px solid #334155;
            color: #94a3b8;
        }

        .table tbody tr {
            background-color: #0f172a !important;
        }

        .table td {
            background-color: transparent !important;
            /* inherit the row bg */
            border-bottom: 1px solid #334155;
            color: #94a3b8;
        }

        /* Hover state—slightly brighter than #0f172a */
        .table.table-hover tbody tr:hover {
            background-color: #111c34 !important;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4" style="color: #f8fafc;">Withdrawals Management</h1>

                <!-- Filters Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.withdrawals.index') }}">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="search-wrapper">
                                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <input type="text" name="search" class="form-control search-input"
                                            placeholder="Search name, email, or reference..."
                                            value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select name="status" class="form-select">
                                        <option value="">All statuses</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                            Approved</option>
                                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid
                                        </option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                            Rejected</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <svg width="16" height="16" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" style="margin-right: 4px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Search
                                </button>
                                <a href="{{ route('admin.withdrawals.index') }}"
                                    class="btn btn-outline btn-sm ms-2">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Withdrawals Table -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Payout Details</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $withdrawal)
                                    <tr>
                                        <td>
                                            <span class="text-mono fw-medium">{{ $withdrawal->reference }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-medium">{{ $withdrawal->user->first_name }}
                                                    {{ $withdrawal->user->last_name }}</div>
                                                <div class="small">{{ $withdrawal->user->email }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">₦{{ number_format($withdrawal->amount, 2) }}
                                                {{ $withdrawal->currency }}</span>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div class="fw-medium">{{ $withdrawal->bank_name }}</div>
                                                <div class="">Acct: {{ $withdrawal->account_number }}</div>
                                                @if ($withdrawal->routing_number)
                                                    <div class="">Routing: {{ $withdrawal->routing_number }}
                                                    </div>
                                                @endif
                                                @if ($withdrawal->swift_code)
                                                    <div>SWIFT: {{ $withdrawal->swift_code }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="badge badge-{{ $withdrawal->status }}">
                                                    {{ ucfirst($withdrawal->status) }}
                                                </span>
                                                <div class="small mt-1">
                                                    {{ $withdrawal->created_at->format('M j, Y g:i A') }}</div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                @if ($withdrawal->status === 'pending')
                                                    <form method="POST"
                                                        action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <svg width="16" height="16" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24"
                                                                style="margin-right: 4px;">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            Approve & Payout
                                                        </button>
                                                    </form>
                                                    <form method="POST"
                                                        action="{{ route('admin.withdrawals.reject', $withdrawal->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline btn-sm">
                                                            <svg width="16" height="16" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24"
                                                                style="margin-right: 4px;">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                            Reject
                                                        </button>
                                                    </form>
                                                @elseif($withdrawal->status === 'approved')
                                                    <form method="POST"
                                                        action="{{ route('admin.withdrawals.payout', $withdrawal->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <svg width="16" height="16" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24"
                                                                style="margin-right: 4px;">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                                                </path>
                                                            </svg>
                                                            Payout
                                                        </button>
                                                    </form>
                                                    <form method="POST"
                                                        action="{{ route('admin.withdrawals.reject', $withdrawal->id) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline btn-sm">
                                                            <svg width="16" height="16" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24"
                                                                style="margin-right: 4px;">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                            Reject
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- <div class="dropdown">
                                                        <button class="btn btn-outline btn-sm" style="padding: 4px 8px;">
                                                            <svg width="16" height="16" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                        <div class="dropdown-content">
                                                            <a href="{{ route('admin.withdrawals.show', $withdrawal->id) }}"
                                                                class="dropdown-item">View Details</a>
                                                            <a href="{{ route('admin.withdrawals.receipt', $withdrawal->id) }}"
                                                                class="dropdown-item">Download Receipt</a>
                                                        </div>
                                                    </div> --}}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            No withdrawals found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($withdrawals->hasPages())
                        <div class="card-footer d-flex justify-content-between align-items-center"
                            style="background-color: #1e293b; border-top: 1px solid #334155;">
                            <div class="small">
                                Showing {{ $withdrawals->firstItem() }} to {{ $withdrawals->lastItem() }} of
                                {{ $withdrawals->total() }} withdrawals
                            </div>
                            <div>
                                {{ $withdrawals->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
