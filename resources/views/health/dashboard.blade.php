@extends('layouts.health')
@section('title', 'Dashboard')

@push('styles')
    <style>
        :root {
            --card: #0f172a;
            --panel: #101a2e;
            --border: #27344e;
            --text: #e5e7eb;
            --muted: #9aa3b2;
            --accent: #3b82f6;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        /* ========== Badges ========== */
        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .28rem .6rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .85rem;
            background: #0e162b;
            color: var(--text)
        }

        .badge-note {
            background: #1f2a44;
            border-color: #2b3b5d
        }

        .badge-ok {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43
        }

        .note-alert {
            background: #2a1f00;
            border: 1px solid #5b4200;
            color: #ffd78a;
            border-radius: 10px;
            padding: .65rem .8rem
        }

        /* ========== Cards / Metrics ========== */
        .cardx {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem 1.1rem;
            color: var(--text)
        }

        .cardx:hover {
            border-color: #35517c
        }

        .subtle {
            color: var(--muted);
            font-size: .9rem
        }

        .metric {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: .2px
        }

        .pill {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f162a;
            border: 1px solid var(--border)
        }

        /* ========== Panels / List Items ========== */
        .right-card {
            padding: 1rem 1.1rem
        }

        .section-subtle {
            color: var(--muted)
        }

        .item {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: .8rem 1rem
        }

        .item:hover {
            border-color: #35517c
        }

        .item .fw-semibold {
            font-weight: 600
        }

        /* Status chip */
        .cred-status {
            margin-top: .35rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem
        }

        .cred-status.pending {
            background: rgba(245, 158, 11, .1);
            border-color: #7a5a1b;
            color: #fbbf24
        }

        .cred-status.approved {
            background: rgba(34, 197, 94, .1);
            border-color: #1a6b3a;
            color: #86efac
        }

        .cred-status.rejected {
            background: rgba(239, 68, 68, .1);
            border-color: #7a1f2a;
            color: #fca5a5
        }

        /* ========== Buttons (keep Bootstrap core, just subtle tune) ========== */
        .btn-outline-light {
            border-color: #3b4a69;
            color: #dce3ef
        }

        .btn-outline-light:hover {
            background: #1a2540;
            border-color: #4a5d85
        }

        .btn-success.btn-sm {
            padding: .35rem .6rem;
            border-radius: .55rem
        }

        /* ========== Modal ========== */
        .modal-themed .modal-content {
            background: var(--card) !important;
            color: var(--text);
            border: 1px solid var(--border)
        }

        .modal-themed .modal-header {
            border-bottom: 1px solid var(--border)
        }

        .btn-close-white {
            filter: invert(1);
            opacity: .75
        }

        .btn-close-white:hover {
            opacity: 1
        }

        /* ========== Utilities ========== */
        .row.g-3>[class*="col-"] {
            min-width: 0
        }
    </style>
@endpush


@section('content')
    {{-- METRICS --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Pending credentials</div>
                        <div class="metric">{{ $pending ?? 0 }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-file fs-5" style="color:#efed86;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Approved credentials</div>
                        <div class="metric">{{ $approved ?? 0 }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-folder fs-5" style="color:#86bcef;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Total Doctors</div>
                        <div class="metric">{{ $total ?? 0 }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-user-doctor fs-5" style="color:#86efac;"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- PANELS --}}
    <div class="row g-3 mt-1">
        {{-- Pending --}}
        <div class="col-lg">
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
                                <form method="POST"
                                    action="{{ route('health.doctors.approveCredential', $c->doctor_id) }}">
                                    @csrf
                                    <input type="hidden" name="credential_id" value="{{ $c->id }}">
                                    <button class="btn btn-success btn-sm">Approve</button>
                                </form>

                                <form method="POST"
                                    action="{{ route('health.doctors.rejectCredential', $c->doctor_id) }}">
                                    @csrf
                                    <input type="hidden" name="credential_id" value="{{ $c->id }}">
                                    <button type="button" class="btn btn-danger btn-sm js-reject">Reject</button>
                                </form>

                            </div>
                        </div>
                    @empty
                        <div class="section-subtle">No credentials</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-themed"
                style="background:var(--card);color:var(--text);border:1px solid var(--border)">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Reject Credential</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Reason for rejection <span class="text-danger">*</span></label>
                        <textarea id="rejectReason" class="form-control" rows="3"
                            placeholder="e.g., Document is blurry / invalid / mismatched name"></textarea>
                        <div class="invalid-feedback">Please provide a reason.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" id="confirmRejectBtn">Reject</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Credentials Modal --}}
    <div class="modal fade" id="credModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-themed" style="background:var(--card);border:1px solid var(--border)">
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
            let rejectFormRef = null;

            // When "Credentials" clicked, fetch and show
            $(document).on('click', '[data-view-creds]', function() {
                const doctorId = $(this).data('doctor');
                const $body = $('#credModalBody');
                $body.html('<div class="text-center section-subtle">Loading…</div>');
                new bootstrap.Modal(document.getElementById('credModal')).show();

                $.get(`{{ url('/health/doctors') }}/${doctorId}/credentials`, function(html) {
                    $body.html(html);
                }).fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to load credentials';
                    $body.html(`<div class="text-center text-danger">${msg}</div>`);
                });
            });

            $(document).on('click', 'form[action*="reject"] button.btn-danger', function(e) {
                e.preventDefault();
                rejectFormRef = this.form || $(this).closest('form')[0];
                $('#rejectReason').val('').removeClass('is-invalid');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectReasonModal')).show();
            });



            $(document).on('click', '.js-reject', function(e) {
                e.preventDefault();
                rejectFormRef = this.form; // the form that owns this button
                $('#rejectReason').val('').removeClass('is-invalid');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectReasonModal')).show();
            });

            $('#confirmRejectBtn').on('click', function() {
                if (!rejectFormRef) return;
                const reason = $('#rejectReason').val().trim();
                if (!reason) {
                    $('#rejectReason').addClass('is-invalid').trigger('focus');
                    return;
                }
                $('<input>', {
                    type: 'hidden',
                    name: 'reason',
                    value: reason
                }).appendTo(rejectFormRef);
                rejectFormRef.submit();
            });

            $('#rejectReason').on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') $('#confirmRejectBtn').click();
            });

        })();
    </script>
@endpush
