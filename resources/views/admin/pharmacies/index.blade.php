@extends('layouts.admin')
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
                <a href="{{ route('admin.pharmacies.index') }}" class="reset-link d-inline-block">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset
                </a>
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
                        <th>24/7</th>
                        <th>Radius (km)</th>
                        <th class="text-end" style="width:260px;">Actions</th>
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
                                <span class="badge-soft {{ $prof?->is_24_7 ?? false ? 'badge-on' : 'badge-off' }}">
                                    <i
                                        class="fa-solid {{ $prof?->is_24_7 ?? false ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                                    {{ $prof?->is_24_7 ?? false ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>{{ $prof->delivery_radius_km ?? '—' }}</td>
                            <td class="text-end row-actions">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-light btn-sm" data-view-profile
                                        data-pharmacy="{{ $p->id }}">
                                        <i class="fa-regular fa-eye me-1"></i> View Profile
                                    </button>

                                    {{-- Toggle 24/7 --}}
                                    <form method="POST" action="{{ route('admin.pharmacies.toggle24', $p->id) }}">
                                        @csrf
                                        <button class="btn btn-outline-light btn-sm">
                                            <i class="fa-solid fa-power-off me-1"></i> Toggle 24/7
                                        </button>
                                    </form>

                                    {{-- Quick radius (modal handles the actual update; this is optional shortcut) --}}
                                    <button type="button" class="btn btn-outline-light btn-sm" data-view-profile
                                        data-pharmacy="{{ $p->id }}">
                                        <i class="fa-solid fa-ruler-combined me-1"></i> Radius
                                    </button>
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

    {{-- Profile Modal --}}
    <div class="modal fade" id="pharmProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card);color:var(--text);border:1px solid var(--border)">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="fa-solid fa-prescription-bottle-medical me-1"></i> Pharmacy Profile
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="pharmProfileBody">
                    <div class="text-center section-subtle">Loading…</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            // Open profile modal
            $(document).on('click', '[data-view-profile]', function() {
                const id = $(this).data('pharmacy');
                const $body = $('#pharmProfileBody');
                $body.html('<div class="text-center section-subtle">Loading…</div>');
                new bootstrap.Modal(document.getElementById('pharmProfileModal')).show();

                $.get(`{{ url('/admin/pharmacies') }}/${id}/profile`, function(html) {
                    $body.html(html);
                }).fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to load profile';
                    $body.html(`<div class="text-center text-danger">${msg}</div>`);
                });
            });

            // Handle quick radius update inside the modal (delegated)
            $(document).on('submit', '#radiusForm', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $form.find('button[type="submit"]');
                $btn.prop('disabled', true);
                $.post($form.attr('action'), $form.serialize())
                    .done(res => {
                        // Re-render profile modal content for freshness
                        const id = $form.data('pharmacy-id');
                        $.get(`{{ url('/admin/pharmacies') }}/${id}/profile`, function(html) {
                            $('#pharmProfileBody').html(html);
                        });
                    })
                    .fail(xhr => {
                        alert(xhr.responseJSON?.message || 'Update failed');
                    })
                    .always(() => $btn.prop('disabled', false));
            });
        })();
    </script>
@endpush
