@extends('layouts.admin')
@section('title', 'Dispatchers')

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
        table th, table td {
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
                <a href="{{ route('admin.dispatchers.index') }}" class="reset-link d-inline-block">
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
                        <th>Phone</th>
                        <th class="text-end" style="width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dispatchers as $d)
                        @php
                            $initials =
                                strtoupper(substr($d->first_name, 0, 1)) . strtoupper(substr($d->last_name, 0, 1));
                        @endphp
                        <tr>
                            <td>
                                <div class="avatar-mini">{{ $initials }}</div>
                            </td>
                            <td class="fw-semibold">
                                {{ $d->first_name }} {{ $d->last_name }}
                                <div class="section-subtle mini">#D{{ str_pad($d->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td>{{ $d->email }}</td>
                            <td>{{ $d->phone ?? '—' }}</td>
                            <td class="text-end row-actions">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-light btn-sm" data-view-profile
                                        data-dispatcher="{{ $d->id }}">
                                        <i class="fa-regular fa-eye me-1"></i> View Profile
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center section-subtle py-4">No dispatchers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $dispatchers->withQueryString()->onEachSide(1)->links() }}</div>
    </div>

    {{-- Profile Modal --}}
    <div class="modal fade" id="dispProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card);color:var(--text);border:1px solid var(--border)">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="fa-solid fa-person-biking me-1"></i> Dispatcher Profile
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="dispProfileBody">
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
                const id = $(this).data('dispatcher');
                const $body = $('#dispProfileBody');
                $body.html('<div class="text-center section-subtle">Loading…</div>');
                new bootstrap.Modal(document.getElementById('dispProfileModal')).show();

                $.get(`{{ url('/admin/dispatchers') }}/${id}/profile`, function(html) {
                    $body.html(html);
                }).fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to load profile';
                    $body.html(`<div class="text-center text-danger">${msg}</div>`);
                });
            });
        })();
    </script>
@endpush
