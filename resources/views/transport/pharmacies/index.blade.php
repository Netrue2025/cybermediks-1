@extends('layouts.transport')
@section('title', 'Pharmacies')

@push('styles')
    <style>
        .section-subtle {
            color: var(--muted)
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

        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-striped-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            --bs-table-hover-color: var(--text);
            color: var(--text)
        }

        .avatar-mini {
            width: 36px;
            height: 36px;
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
            --bs-btn-font-size: .8rem
        }

        .filter-card .form-control,
        .filter-card .form-select {
            background: #0b1222;
            color: var(--text);
            border: 1px solid var(--border)
        }

        table th,
        table td {
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
                    <span class="input-group-text" style="background:#0b1222;border-color:var(--border)">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input class="form-control" name="q" value="{{ $q }}"
                        placeholder="Pharmacy name or email">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small section-subtle mb-1">&nbsp;</label>
                <button class="btn btn-gradient w-100">
                    <i class="fa-solid fa-sliders me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <div class="cardx">
        <div class="table-responsive">
            <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                <thead>
                    <tr class="section-subtle">
                        <th style="width:36px;"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>License</th>
                        <th>Uploaded License</th>
                        <th>Status</th>
                        <th>24/7</th>
                        <th>Radius (km)</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pharmacies as $p)
                        @php
                            $prof = $p->pharmacyProfile;
                            $initials =
                                strtoupper(substr($p->first_name, 0, 1)) . strtoupper(substr($p->last_name, 0, 1));
                        @endphp
                        <tr>
                            <td>
                                <div class="avatar-mini">{{ $initials }}</div>
                            </td>
                            <td class="fw-semibold">
                                {{ $p->first_name }} {{ $p->last_name }}
                                <div class="section-subtle mini">#P{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td>{{ $p->email }}</td>
                            <td>{{ $prof->license_no ?? '—' }}</td>
                            <td>

                                <span
                                    class="badge-soft {{ $prof?->operating_license ?? false ? 'badge-on' : 'badge-off' }}">
                                    <i
                                        class="fa-solid {{ $prof?->operating_license ?? false ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                                    {{ $prof?->operating_license ?? false ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $status = $prof->status ?? 'pending'; // fallback
                                    $badgeClass = match ($status) {
                                        'approved' => 'badge-on', // green (define in CSS)
                                        'rejected' => 'badge-off', // red (define in CSS)
                                        default => '', // pending = neutral
                                    };
                                    $iconClass = match ($status) {
                                        'approved' => 'fa-circle-check',
                                        'rejected' => 'fa-circle-xmark',
                                        default => 'fa-hourglass-half',
                                    };
                                @endphp

                                <span class="badge-soft {{ $badgeClass }}">
                                    <i class="fa-solid {{ $iconClass }} me-1"></i>
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge-soft {{ $prof?->is_24_7 ?? false ? 'badge-on' : 'badge-off' }}">
                                    <i
                                        class="fa-solid {{ $prof?->is_24_7 ?? false ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                                    {{ $prof?->is_24_7 ?? false ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>{{ $prof->delivery_radius_km ?? '—' }}</td>
                            <td class="text-end row-actions">
                                <div class="btn-group">
                                    <a class="btn btn-outline-light btn-sm"
                                        href="{{ Storage::disk('public')->url($prof->operating_license) }}"
                                        target="_blank">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View License
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center section-subtle py-4">No pharmacies found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $pharmacies->withQueryString()->onEachSide(1)->links() }}</div>
    </div>

@endsection

