@extends('layouts.hospital')
@section('title', 'Doctors')

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

        .doc-title {
            color: #cfd6e6
        }

        .mini-help {
            font-size: .78rem;
            color: #9aa3b2
        }

        .right-card .item {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 12px;
            background: #0f1a2e
        }

        .cred-status {
            font-size: .78rem
        }

        .cred-status.pending {
            color: #eab308
        }

        .cred-status.verified {
            color: #22c55e
        }

        .cred-status.rejected {
            color: #ef4444
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
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Doctor name or title">
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

    <div class="row g-3">
        {{-- LEFT: Doctors table --}}
        <div class="col-lg-8">
            <div class="cardx">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                        <thead>
                            <tr class="section-subtle">
                                <th style="width:36px;"></th>
                                <th>Name</th>
                                <th>Title</th>
                                <th>Available</th>
                                <th>Fee</th>
                                <th>Avg (min)</th>
                                <th class="text-end" style="width:220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctors as $d)
                                @php
                                    $p = $d->doctorProfile;
                                    $initials =
                                        strtoupper(substr($d->first_name, 0, 1)) .
                                        strtoupper(substr($d->last_name, 0, 1));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="avatar-mini">{{ $initials }}</div>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $d->first_name }} {{ $d->last_name }}
                                        <div class="mini-help">#D{{ str_pad($d->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    </td>
                                    <td class="doc-title">{{ $p->title ?? '—' }}</td>
                                    <td>
                                        <span
                                            class="badge-soft {{ $p?->is_available ?? false ? 'badge-on' : 'badge-off' }}">
                                            <i
                                                class="fa-solid {{ $p?->is_available ?? false ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                                            {{ $p?->is_available ?? false ? 'Available' : 'Unavailable' }}
                                        </span>
                                    </td>
                                    <td><span class="pill-money">${{ number_format($p->consult_fee ?? 0, 2) }}</span></td>
                                    <td>{{ $p->avg_duration ?? '—' }}</td>
                                    <td class="text-end row-actions">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-outline-light btn-sm" data-view-creds
                                                data-doctor="{{ $d->id }}">
                                                <i class="fa-regular fa-folder-open me-1"></i> Credentials
                                            </button>

                                            <button type="button" class="btn btn-outline-light btn-sm" data-edit-doctor
                                                data-doctor-id="{{ $d->id }}"
                                                data-first-name="{{ $d->first_name }}"
                                                data-last-name="{{ $d->last_name }}" data-title="{{ $p->title ?? '' }}"
                                                data-fee="{{ $p->consult_fee ?? 0 }}"
                                                data-avg="{{ $p->avg_duration ?? 15 }}"
                                                data-meeting="{{ $p->meeting_link ?? '' }}"
                                                data-specialties='@json($d->specialties->pluck('id'))'>
                                                <i class="fa-regular fa-pen-to-square me-1"></i> Edit
                                            </button>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center section-subtle py-4">No doctors found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">{{ $doctors->withQueryString()->onEachSide(1)->links() }}</div>
            </div>
        </div>

        {{-- RIGHT: Recent Credentials --}}
        <div class="col-lg-4">
            <div class="cardx right-card">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-badge-check"></i>
                    <div class="fw-bold">Recent Credentials</div>
                </div>
                <div class="d-flex flex-column gap-2">
                    @forelse($credentials as $c)
                        <div class="item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">
                                    {{ $c->doctor?->first_name }} {{ $c->doctor?->last_name }}
                                </div>
                                <div class="section-subtle small">
                                    {{ $c->type ?? 'Document' }} • {{ $c->created_at->diffForHumans() }}
                                </div>
                                <div class="cred-status {{ $c->status ?? 'pending' }}">
                                    Status: {{ ucfirst($c->status ?? 'pending') }}
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-light btn-sm" data-view-creds
                                    data-doctor="{{ $c->doctor_id }}">
                                    View
                                </button>
                                {{-- <form method="POST"
                                    action="{{ route('admin.doctors.approveCredential', $c->doctor_id) }}">
                                    @csrf
                                    <input type="hidden" name="credential_id" value="{{ $c->id }}">
                                    <button class="btn btn-success btn-sm">Approve</button>
                                </form> --}}
                            </div>
                        </div>
                    @empty
                        <div class="section-subtle">No credentials</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Doctor Modal --}}
    <div class="modal fade" id="editDoctorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content" style="background:var(--card);color:var(--text);border:1px solid var(--border)">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-regular fa-pen-to-square me-1"></i> Edit Doctor</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form id="editDoctorForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="doc_id" id="ed_doc_id">

                        <div class="mb-2">
                            <label class="form-label small">Name</label>
                            <div class="form-control" id="ed_name" disabled></div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small">Title</label>
                            <input type="text" class="form-control" name="title" id="ed_title"
                                placeholder="e.g., Consultant Cardiologist">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small">Meeting Link</label>
                            <input type="url" class="form-control" name="meeting_link" id="ed_meeting"
                                placeholder="https://...">
                            <div class="mini-help">Optional: When set, <em>link_updated_at</em> will be refreshed.</div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">Consultation Fee ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="consult_fee" id="ed_fee">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Avg Duration (min)</label>
                                <input type="number" min="5" max="240" class="form-control"
                                    name="avg_duration" id="ed_avg">
                            </div>
                        </div>

                        <div class="mt-2">
                            <label class="form-label small">Specialties</label>
                            <select class="form-select" name="specialty_ids[]" id="ed_specialties" multiple>
                                @foreach ($allSpecialties as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            <div class="mini-help">Hold <kbd>Ctrl</kbd>/<kbd>Cmd</kbd> to select multiple.</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-outline-light" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-gradient" id="btnSaveEd" type="submit">
                            <i class="fa-regular fa-floppy-disk me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Credentials Modal --}}
    <div class="modal fade" id="credModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card);color:var(--text);border:1px solid var(--border)">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-solid fa-badge-check me-1"></i> Doctor Credentials</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="credModalBody">
                    {{-- Loaded via AJAX --}}
                    <div class="text-center section-subtle">Loading…</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            // When "Credentials" clicked, fetch and show
            $(document).on('click', '[data-view-creds]', function() {
                const doctorId = $(this).data('doctor');
                const $body = $('#credModalBody');
                $body.html('<div class="text-center section-subtle">Loading…</div>');
                new bootstrap.Modal(document.getElementById('credModal')).show();

                $.get(`{{ url('/hospital/doctors') }}/${doctorId}/credentials`, function(html) {
                    $body.html(html);
                }).fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to load credentials';
                    $body.html(`<div class="text-center text-danger">${msg}</div>`);
                });
            });
        })();
    </script>

    <script>
(function () {
    // CSRF for AJAX
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}
    });

    // Open Edit modal + prefill
    $(document).on('click', '[data-edit-doctor]', function () {
        const $btn   = $(this);
        const id     = $btn.data('doctor-id');
        const first  = $btn.data('first-name') || '';
        const last   = $btn.data('last-name')  || '';
        const title  = $btn.data('title')      || '';
        const fee    = $btn.data('fee')        || 0;
        const avg    = $btn.data('avg')        || 15;
        const meet   = $btn.data('meeting')    || '';
        const specs  = ($btn.data('specialties') || []);

        $('#ed_doc_id').val(id);
        $('#ed_name').text(`${first} ${last}`);
        $('#ed_title').val(title);
        $('#ed_fee').val(fee);
        $('#ed_avg').val(avg);
        $('#ed_meeting').val(meet);

        // Preselect specialties
        const $sel = $('#ed_specialties');
        $sel.val([]); // reset
        specs.forEach(v => {
            $sel.find(`option[value="${v}"]`).prop('selected', true);
        });

        new bootstrap.Modal(document.getElementById('editDoctorModal')).show();
    });

    // Submit edit
    $('#editDoctorForm').on('submit', function (e) {
        e.preventDefault();
        const $btn   = $('#btnSaveEd').prop('disabled', true);
        const docId  = $('#ed_doc_id').val();
        const data   = $(this).serialize();

        $.post(`{{ route('hospital.doctors.profile.update', ':id') }}`.replace(':id', docId), data)
            .done(res => {
                // Update the row UI (title, fee, avg)
                const $rowBtn = $(`[data-edit-doctor][data-doctor-id="${docId}"]`);
                const $tr     = $rowBtn.closest('tr');
                $tr.find('.doc-title').text(res.profile.title ?? '—');
                $tr.find('td:nth-child(5) .pill-money').text(`$${Number(res.profile.consult_fee ?? 0).toFixed(2)}`);
                $tr.find('td:nth-child(6)').text(res.profile.avg_duration ?? '—');

                // Also refresh the data-* cache on the Edit button
                $rowBtn.data('title', res.profile.title || '');
                $rowBtn.data('fee', res.profile.consult_fee || 0);
                $rowBtn.data('avg', res.profile.avg_duration || 15);
                $rowBtn.data('meeting', res.profile.meeting_link || '');

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('editDoctorModal')).hide();

                // Small toast/flash
                showToast('Profile updated', 'success'); // implement showToast or replace with your notifier
            })
            .fail(xhr => {
                let msg = 'Unable to update profile';
                if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                showToast(msg, 'error');
            })
            .always(() => $btn.prop('disabled', false));
    });

    // Minimal toast helper (optional)
    window.showToast = function (text, type='info') {
        // plug into your Toastify or Bootstrap alert; for quick demo:
        console.log(`[${type}] ${text}`);
    };
})();
</script>
@endpush
