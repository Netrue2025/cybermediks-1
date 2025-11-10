@extends('layouts.admin')
@section('title', 'Disputes')

@push('styles')
    <style>
        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-striped-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            --bs-table-hover-color: var(--text);
            color: var(--text);
        }

        table th,
        table td {
            color: white !important;
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
    </style>
@endpush

@section('content')
    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="cardx mb-3 filter-card">
        <form class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small section-subtle mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#0b1222;border-color:var(--border); color:white;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input class="form-control" name="q" value="{{ $q ?? '' }}"
                        placeholder="Reason, patient, or doctor">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small section-subtle mb-1">Status</label>
                <select class="form-select" name="status">
                    @php $st = $status ?? 'open'; @endphp
                    <option value="all" @selected($st === 'all')>All</option>
                    <option value="open" @selected($st === 'open')>Open</option>
                    <option value="admin_review" @selected($st === 'admin_review')>Admin review</option>
                    <option value="resolved" @selected($st === 'resolved')>Resolved</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small section-subtle mb-1">From</label>
                <input type="date" class="form-control" name="from" value="{{ $from }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small section-subtle mb-1">To</label>
                <input type="date" class="form-control" name="to" value="{{ $to }}">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small section-subtle mb-1 d-none d-md-block">&nbsp;</label>
                <button class="btn btn-gradient w-100"><i class="fa-solid fa-sliders me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <div class="cardx">
        <div class="table-responsive">
            <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                <thead>
                    <tr>
                        <th>Appointment</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Held</th>
                        <th class="text-end" style="width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $d)
                        @php
                            $ap = $d->appointment;
                            $badge =
                                $d->status === 'resolved'
                                    ? 'badge-resolved'
                                    : ($d->status === 'admin_review'
                                        ? 'badge-review'
                                        : 'badge-open');

                            // Prefer a passed-in map $holdByAppt; fallback to $ap->hold if relation exists
                            /** @var \App\Models\WalletHold|null $h */
                            $h = isset($holdByAppt) ? $holdByAppt[$ap->id] ?? null : $ap->hold ?? null;
                            $held = $h ? number_format((float) $h->amount, 2) : '—';
                            $hstat = $h->status ?? '—';
                            $hBadge = match ($hstat) {
                                'pending' => 'badge-open',
                                'released_to_patient' => 'badge-resolved',
                                'released_to_doctor' => 'badge-resolved',
                                'partial' => 'badge-review',
                                default => 'badge-open',
                            };
                        @endphp
                        <tr>
                            <td>
                                #A{{ str_pad($ap->id, 4, '0', STR_PAD_LEFT) }}
                                <div class="section-subtle small">
                                    {{ optional($ap->scheduled_at)->format('M d, Y · g:ia') }}
                                </div>
                            </td>
                            <td>{{ $ap->patient?->first_name }} {{ $ap->patient?->last_name }}</td>
                            <td>{{ $ap->doctor?->first_name }} {{ $ap->doctor?->last_name }}</td>
                            <td class="section-subtle small">{{ \Illuminate\Support\Str::limit($d->reason, 80) }}</td>
                            <td>
                                <span
                                    class="badge-soft {{ $badge }}">{{ ucwords(str_replace('_', ' ', $d->status)) }}</span>
                            </td>
                            <td>
                                <div> @money($held)</div>
                                <div class="section-subtle small">
                                    <span
                                        class="badge-soft {{ $hBadge }}">{{ str_replace('_', ' ', ucfirst($hstat)) }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('admin.disputes.show', $d) }}" class="btn btn-outline-light btn-sm">
                                        <i class="fa-regular fa-eye me-1"></i> View
                                    </a>
                                    @if ($d->status !== 'resolved' && ($h->status ?? null) === 'pending')
                                        <button type="button" class="btn btn-warning btn-sm js-open-resolve"
                                            data-id="{{ $d->id }}" data-appointment="{{ $ap->id }}"
                                            data-patient="{{ $ap->patient?->first_name }} {{ $ap->patient?->last_name }}"
                                            data-doctor="{{ $ap->doctor?->first_name }} {{ $ap->doctor?->last_name }}">
                                            Resolve
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center section-subtle py-4">No disputes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $disputes->onEachSide(1)->links() }}</div>
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
            let resolveId = null;

            $(document).on('click', '.js-open-resolve', function() {
                resolveId = $(this).data('id');
                const ap = $(this).data('appointment');
                const pt = $(this).data('patient');
                const dr = $(this).data('doctor');

                $('#resolveContext').text(
                    `Dispute for Appointment #A${String(ap).padStart(4,'0')} | Patient: ${pt} | Doctor: ${dr}`
                );

                const action = `{{ url('/admin/disputes') }}/${resolveId}/resolve`;
                $('#resolveForm').attr('action', action);

                bootstrap.Modal.getOrCreateInstance(document.getElementById('resolveModal')).show();
            });
        })();
    </script>
@endpush
