@extends('layouts.admin')
@section('title', 'Patients')

@push('styles')
    <style>
        .section-subtle {
            color: var(--muted)
        }

        .chip {
            background: var(--chip);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .18rem .55rem;
            font-size: .8rem;
            color: #c9d1e1;
            white-space: nowrap;
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

        .badge-on {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43
        }

        .badge-off {
            background: rgba(239, 68, 68, .08);
            border-color: #6f2b2b
        }

        .pill-money {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .18rem .55rem;
            font-weight: 600
        }

        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-striped-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            --bs-table-hover-color: var(--text);
            color: var(--text);
        }

        .avatar-mini {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #14203a;
            color: #cfe0ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .85rem
        }

        .row-actions .btn {
            --bs-btn-padding-y: .25rem;
            --bs-btn-padding-x: .5rem;
            --bs-btn-font-size: .8rem;
        }

        .filter-card .form-control,
        .filter-card .form-select {
            background: #0b1222;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .reset-link {
            color: #a9b4c8;
            text-decoration: none
        }

        .reset-link:hover {
            color: #fff
        }

        table th {
            color: white !important;
        }
    </style>
@endpush

@section('content')
    {{-- Filters --}}
    <div class="cardx mb-3 filter-card">
        <form class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small section-subtle mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#0b1222;border-color:var(--border); color:white;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input class="form-control" name="q" value="{{ $q }}"
                        placeholder="Search first/last name or email">
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label small section-subtle mb-1">Country</label>
                <select class="form-select" name="country">
                    <option value="">All countries</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c }}" @selected(strtoupper($country ?? '') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small section-subtle mb-1">&nbsp;</label>
                <button class="btn btn-gradient w-100">
                    <i class="fa-solid fa-sliders me-1"></i> Filter
                </button>
            </div>

            <div class="col-md-1 text-md-end">
                <label class="form-label small section-subtle mb-1 d-none d-md-block">&nbsp;</label>
                <a href="{{ route('admin.users.index') }}" class="reset-link d-inline-block">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset
                </a>
            </div>
        </form>

    </div>

    {{-- Table --}}
    <div class="cardx">
        <div class="table-responsive">
            <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                <thead>
                    <tr class="section-subtle" style="color: white !important">
                        <th style="width:34px;"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Country</th>
                        <th>Role</th>
                        <th>Email Verified</th>
                        <th>Wallet</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        @php
                            $initials =
                                strtoupper(substr($u->first_name, 0, 1)) . strtoupper(substr($u->last_name, 0, 1));
                            $roleChipIcon =
                                [
                                    'admin' => 'fa-user-shield',
                                    'patient' => 'fa-user',
                                    'doctor' => 'fa-user-doctor',
                                    'pharmacy' => 'fa-prescription-bottle-medical',
                                    'dispatcher' => 'fa-truck',
                                ][$u->role] ?? 'fa-user';
                        @endphp
                        <tr>
                            <td>
                                <div class="avatar-mini">{{ $initials }}</div>
                            </td>
                            <td class="fw-semibold">
                                {{ $u->first_name }} {{ $u->last_name }}
                                <div class="section-subtle small">#U{{ str_pad($u->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->phone ?? '-' }}</td>
                            <td>{{ ucfirst($u->gender ?? '-') }}</td>
                            <td>{{ strtoupper($u->country ?? '-') }}</td>
                            <td>
                                <span class="chip">
                                    <i class="fa-solid {{ $roleChipIcon }} me-1"></i>{{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td>
                                @if ($u->hasVerifiedEmail())
                                    <span class="badge-soft badge-on">
                                        <i class="fa-solid fa-check"></i> Verified
                                    </span>
                                @else
                                    <span class="badge-soft badge-off">
                                        <i class="fa-solid fa-xmark"></i> Unverified
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="pill-money">${{ number_format($u->wallet_balance ?? 0, 2) }}</span>
                            </td>
                            <td class="text-end row-actions">
                                <div class="btn-group">
                                    {{-- <a href="{{ route('admin.users.show', $u) }}" class="btn btn-outline-light btn-sm">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline-light btn-sm">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a> --}}
                                    {{-- Optional: quick toggle active via POST if you have the route
                                <form action="{{ route('admin.users.toggle', $u) }}" method="POST" onsubmit="return confirm('Toggle active status?')">
                                    @csrf
                                    <button class="btn btn-outline-light btn-sm">
                                        <i class="fa-solid fa-power-off"></i>
                                    </button>
                                </form>
                                --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center section-subtle py-4">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $users->withQueryString()->onEachSide(1)->links() }}
        </div>
    </div>
@endsection
