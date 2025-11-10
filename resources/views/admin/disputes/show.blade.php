@extends('layouts.admin')
@section('title', 'Dispute Details')

@push('styles')
    <style>
        .kv {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: .4rem .8rem
        }

        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-striped-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            --bs-table-hover-color: var(--text);
            color: var(--text);
        }

        .badge-soft {
            display: inline-flex;
            gap: .35rem;
            align-items: center;
            padding: .18rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #0e162b;
            font-size: .8rem
        }

        .badge-open {
            color: #fbbf24;
            border-color: #7a5a1b;
            background: rgba(245, 158, 11, .08)
        }

        .badge-review {
            color: #93c5fd;
            border-color: #375a9e;
            background: rgba(59, 130, 246, .08)
        }

        .badge-resolved {
            color: #86efac;
            border-color: #1a6b3a;
            background: rgba(34, 197, 94, .08)
        }

        table th,
        table td {
            color: white !important;
        }
    </style>
@endpush

@section('content')
    @php
        $ap = $dispute->appointment;
        $statusBadge = match ($dispute->status) {
            'resolved' => 'badge-resolved',
            'admin_review' => 'badge-review',
            default => 'badge-open',
        };

        // Prefer $hold from controller; fallback to relation if you add one
        /** @var \App\Models\WalletHold|null $holdVar */
        $holdVar = $hold ?? ($ap->hold ?? null);
        $heldAmount = $holdVar ? number_format((float) $holdVar->amount, 2) : '0.00';
        $holdStatus = $holdVar->status ?? '—';
        $holdBadge = match ($holdStatus) {
            'pending' => 'badge-open',
            'released_to_patient' => 'badge-resolved',
            'released_to_doctor' => 'badge-resolved',
            'partial' => 'badge-review',
            default => 'badge-open',
        };
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">Dispute #D{{ str_pad($dispute->id, 4, '0', STR_PAD_LEFT) }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.disputes.index') }}" class="btn btn-outline-light">
                <i class="fa-solid fa-arrow-left-long me-1"></i> Back
            </a>
            @if ($dispute->status !== 'resolved' && ($holdVar->status ?? null) === 'pending')
                <button type="button" class="btn btn-warning js-open-resolve" data-id="{{ $dispute->id }}"
                    data-appointment="{{ $ap->id }}"
                    data-patient="{{ $ap->patient?->first_name }} {{ $ap->patient?->last_name }}"
                    data-doctor="{{ $ap->doctor?->first_name }} {{ $ap->doctor?->last_name }}">
                    <i class="fa-solid fa-scale-balanced me-1"></i> Resolve
                </button>
            @endif
        </div>
    </div>

    <div class="row g-3">
        {{-- Left: Dispute details --}}
        <div class="col-lg-7">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="section-subtle small">Status</div>
                        <span
                            class="badge-soft {{ $statusBadge }}">{{ ucwords(str_replace('_', ' ', $dispute->status)) }}</span>
                    </div>
                    <div class="text-end section-subtle small">
                        Opened: {{ $dispute->created_at->format('M d, Y · g:ia') }}<br>
                        @if ($dispute->updated_at)
                            Updated: {{ $dispute->updated_at->format('M d, Y · g:ia') }}
                        @endif
                    </div>
                </div>

                <div class="mt-2">
                    <div class="section-subtle small">Reason</div>
                    <div class="p-2" style="background:#0f1a2e;border:1px solid var(--border);border-radius:8px;">
                        {{ $dispute->reason }}
                    </div>
                </div>

                @if (!empty($dispute->admin_notes))
                    <div class="mt-3">
                        <div class="section-subtle small">Admin Notes</div>
                        <div class="p-2"
                            style="background:#0f1a2e;border:1px solid var(--border);border-radius:8px;white-space:pre-line;">
                            {{ $dispute->admin_notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Financials + meta --}}
        <div class="col-lg-5">
            <div class="cardx">
                <h6 class="mb-2">Financials</h6>
                <div class="kv">
                    <div class="section-subtle small">Held Amount</div>
                    <div>@money($heldAmount)</div>

                    <div class="section-subtle small">Hold Status</div>
                    <div><span
                            class="badge-soft {{ $holdBadge }}">{{ str_replace('_', ' ', ucfirst($holdStatus)) }}</span>
                    </div>
                </div>
                @if (empty($holdVar))
                    <div class="alert alert-warning mt-2 mb-0">
                        No active hold found for this dispute. Please verify payment state.
                    </div>
                @endif
            </div>

            <div class="cardx mt-3">
                <h6 class="mb-2">Appointment</h6>
                <div class="kv">
                    <div class="section-subtle small">ID</div>
                    <div>#A{{ str_pad($ap->id, 4, '0', STR_PAD_LEFT) }}</div>

                    <div class="section-subtle small">Scheduled</div>
                    <div>{{ optional($ap->scheduled_at)->format('M d, Y · g:ia') ?? '—' }}</div>

                    <div class="section-subtle small">Type</div>
                    <div>{{ ucwords(str_replace('_', ' ', $ap->type ?? 'consult')) }}</div>

                    <div class="section-subtle small">Status</div>
                    <div>{{ ucwords(str_replace('_', ' ', $ap->status ?? 'scheduled')) }}</div>
                </div>
            </div>

            <div class="cardx mt-3">
                <h6 class="mb-2">Parties</h6>
                <div class="kv">
                    <div class="section-subtle small">Patient</div>
                    <div>{{ $ap->patient?->first_name }} {{ $ap->patient?->last_name }} (U{{ $ap->patient_id }})</div>

                    <div class="section-subtle small">Doctor</div>
                    <div>{{ $ap->doctor?->first_name }} {{ $ap->doctor?->last_name }} (U{{ $ap->doctor_id }})</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resolve Modal --}}
    <div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:var(--card);color:var(--text);border:1px solid var(--border)">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-solid fa-scale-balanced me-2"></i> Resolve Dispute</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="resolveForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div id="resolveContext" class="section-subtle small mb-2"></div>

                        <label class="form-label">Decision</label>
                        <select name="decision" id="decision" class="form-select" required>
                            <option value="refund">Refund to patient (100%)</option>
                            <option value="release">Release to doctor (100%)</option>
                            <option value="partial">Partial (50/50 split)</option>
                        </select>

                        <div class="mt-2">
                            <label class="form-label">Admin Notes (optional)</label>
                            <textarea name="admin_notes" class="form-control" rows="3" placeholder="Notes for audit trail"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-warning" type="submit">Resolve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            $(document).on('click', '.js-open-resolve', function() {
                const id = $(this).data('id');
                const ap = $(this).data('appointment');
                const pt = $(this).data('patient');
                const dr = $(this).data('doctor');

                $('#resolveContext').text(
                    `Dispute #D${String(id).padStart(4,'0')} • Appointment #A${String(ap).padStart(4,'0')} • Patient: ${pt} • Doctor: ${dr}`
                );

                const action = `{{ url('/admin/disputes') }}/${id}/resolve`;
                $('#resolveForm').attr('action', action);

                bootstrap.Modal.getOrCreateInstance(document.getElementById('resolveModal')).show();
            });
        })();
    </script>
@endpush
