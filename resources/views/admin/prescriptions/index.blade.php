@extends('layouts.admin')
@section('title', 'Prescriptions')

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

        .badge-pending {
            border-color: #334155
        }

        .badge-ready {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .08)
        }

        .badge-picked {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .16)
        }

        .badge-cancelled {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .08)
        }

        .badge-price_assigned {
            border-color: #6b4ae0;
            background: rgba(135, 88, 232, .10)
        }

        .badge-price_confirmed {
            border-color: #2a7a55;
            background: rgba(34, 197, 94, .10)
        }

        .chip {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .18rem .55rem;
            color: #cfe0ff;
            font-size: .8rem
        }

        .input-slim .form-select,
        .input-slim .form-control {
            background: #0b1222;
            color: var(--text);
            border: 1px solid var(--border)
        }

        .row-actions .btn {
            --bs-btn-padding-y: .25rem;
            --bs-btn-padding-x: .5rem;
            --bs-btn-font-size: .8rem
        }

        .rx-code {
            font-weight: 700
        }

        .cell-sub {
            color: var(--muted);
            font-size: .85rem
        }

        table th, table td {
            color: white !important;
        }
    </style>
@endpush

@section('content')
    {{-- Filters --}}
    <div class="cardx mb-3">
        <form class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small section-subtle mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#0b1222;border-color:var(--border)">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input class="form-control" name="q" value="{{ $q }}"
                        placeholder="Code, patient, doctor">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small section-subtle mb-1">Status</label>
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'price_assigned', 'price_confirmed', 'ready', 'picked', 'cancelled'] as $st)
                        <option value="{{ $st }}" @selected($status === $st)>
                            {{ ucwords(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
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
                        <th>Rx</th>
                        <th>Patient / Doctor</th>
                        <th>Pharmacy / Dispatcher</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end" style="width:280px;">Assign</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rx as $r)
                        @php
                            $itemsPreview = $r->items?->pluck('drug')->take(3)->implode(', ');
                            if (($r->items?->count() ?? 0) > 3) {
                                $itemsPreview .= '…';
                            }
                            $disp = $r->dispense_status ?? 'pending';
                            $badgeMap = [
                                'pending' => 'badge-pending',
                                'price_assigned' => 'badge-price_assigned',
                                'price_confirmed' => 'badge-price_confirmed',
                                'ready' => 'badge-ready',
                                'picked' => 'badge-picked',
                                'cancelled' => 'badge-cancelled',
                            ];
                            $cls = $badgeMap[$disp] ?? 'badge-pending';

                            // payload for Quick View
                            $viewPayload = [
                                'code' => $r->code,
                                'created_at' => optional($r->created_at)->format('M d, Y · g:ia'),
                                'patient' => trim(
                                    ($r->patient->first_name ?? '') . ' ' . ($r->patient->last_name ?? ''),
                                ),
                                'doctor' => trim(($r->doctor->first_name ?? '') . ' ' . ($r->doctor->last_name ?? '')),
                                'pharmacy' => trim(
                                    ($r->pharmacy->first_name ?? '') . ' ' . ($r->pharmacy->last_name ?? ''),
                                ),
                                'dispatcher' => trim(
                                    ($r->dispatcher->first_name ?? '') . ' ' . ($r->dispatcher->last_name ?? ''),
                                ),
                                'dispense_status' => $disp,
                                'amount' => $r->total_amount,
                                'notes' => $r->notes,
                                'items' =>
                                    $r->items
                                        ?->map(
                                            fn($i) => [
                                                'drug' => $i->drug,
                                                'dose' => $i->dose,
                                                'frequency' => $i->frequency,
                                                'days' => $i->days,
                                                'qty' => $i->quantity,
                                                'directions' => $i->directions,
                                            ],
                                        )
                                        ->values() ?? [],
                            ];
                        @endphp
                        <tr>
                            {{-- Rx --}}
                            <td>
                                <div class="rx-code">#{{ $r->code }}</div>
                                <div class="cell-sub">{{ optional($r->created_at)->format('M d, Y · g:ia') }}</div>
                                <button type="button" class="btn btn-outline-light btn-sm mt-1"
                                    data-rx-view='@json($viewPayload)'>
                                    <i class="fa-regular fa-eye me-1"></i> View
                                </button>
                            </td>

                            {{-- Patient / Doctor --}}
                            <td>
                                <div class="fw-semibold">
                                    {{ $r->patient?->first_name }} {{ $r->patient?->last_name }}
                                </div>
                                <div class="cell-sub">Dr. {{ $r->doctor?->first_name }} {{ $r->doctor?->last_name }}</div>
                            </td>

                            {{-- Pharmacy / Dispatcher --}}
                            <td>
                                <div class="fw-semibold">
                                    {{ $r->pharmacy?->first_name ? $r->pharmacy?->first_name . ' ' . $r->pharmacy?->last_name : '—' }}
                                </div>
                                <div class="cell-sub">
                                    {{ $r->dispatcher?->first_name ? 'Disp: ' . $r->dispatcher?->first_name . ' ' . $r->dispatcher?->last_name : 'Disp: —' }}
                                </div>
                            </td>

                            {{-- Items --}}
                            <td>
                                <span class="chip">{{ $itemsPreview ?: '—' }}</span>
                            </td>

                            {{-- Amount --}}
                            <td>
                                {{ is_null($r->total_amount) ? '—' : '$' . number_format($r->total_amount, 2, '.', ',') }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge-soft {{ $cls }}">
                                    {{ ucwords(str_replace('_', ' ', $disp)) }}
                                </span>
                            </td>

                            {{-- Assign --}}
                            <td class="text-end">
                                <div class="d-flex flex-column flex-lg-row gap-2 justify-content-end input-slim">
                                    <form method="POST" action="{{ route('admin.prescriptions.reassignPharmacy', $r) }}"
                                        class="d-flex gap-2 align-items-stretch">
                                        @csrf
                                        <select class="form-select form-select-sm" name="pharmacy_id"
                                            style="min-width:180px">
                                            <option value="">Pharmacy…</option>
                                            @foreach ($pharmacies as $p)
                                                <option value="{{ $p->id }}">{{ $p->first_name }}
                                                    {{ $p->last_name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-ghost btn-sm">Assign</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.prescriptions.assignDispatcher', $r) }}"
                                        class="d-flex gap-2 align-items-stretch">
                                        @csrf
                                        <select class="form-select form-select-sm" name="dispatcher_id"
                                            style="min-width:180px">
                                            <option value="">Dispatcher…</option>
                                            @foreach ($dispatchers as $d)
                                                <option value="{{ $d->id }}">{{ $d->first_name }}
                                                    {{ $d->last_name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-ghost btn-sm">Assign</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center section-subtle py-4">No prescriptions</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $rx->withQueryString()->onEachSide(1)->links() }}</div>
    </div>

    {{-- Quick View Modal --}}
    <div class="modal fade" id="rxViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card);color:var(--text);border:1px solid var(--border)">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-solid fa-file-prescription me-1"></i> Prescription</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="rxViewBody">
                    <div class="text-center section-subtle">Loading…</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            // Quick View
            $(document).on('click', '[data-rx-view]', function() {
                const payload = $(this).data('rx-view');
                const rx = typeof payload === 'string' ? JSON.parse(payload) : payload;

                const money = (v) => v == null ? '—' : '$' + Number(v).toFixed(2);
                const itemsHtml = (rx.items || []).map(i => {
                    const bits = [i.drug, i.dose, i.frequency, i.days ? (i.days + ' days') : null, i
                            .qty ? ('Qty: ' + i.qty) : null
                        ]
                        .filter(Boolean).join(' • ');
                    const dir = i.directions ? `<div class="cell-sub">${i.directions}</div>` : '';
                    return `<li class="mb-1"><strong>${bits}</strong>${dir}</li>`;
                }).join('');

                const html = `
      <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
        <div>
          <div class="fw-semibold">Rx #${rx.code}</div>
          <div class="section-subtle small">${rx.created_at ?? ''}</div>
        </div>
        <div><span class="badge-soft">${(rx.dispense_status||'pending').replaceAll('_',' ')}</span></div>
      </div>

      <div class="row g-3 mb-2">
        <div class="col-md-4"><div class="section-subtle small">Patient</div><div class="fw-semibold">${rx.patient||'—'}</div></div>
        <div class="col-md-4"><div class="section-subtle small">Doctor</div><div class="fw-semibold">${rx.doctor||'—'}</div></div>
        <div class="col-md-4"><div class="section-subtle small">Pharmacy / Dispatcher</div>
          <div class="fw-semibold">${rx.pharmacy||'—'}</div>
          <div class="cell-sub">${rx.dispatcher?('Disp: '+rx.dispatcher):''}</div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-8">
          <div class="section-subtle small mb-1">Items</div>
          <ul class="mb-0">${itemsHtml || '<li>—</li>'}</ul>
        </div>
        <div class="col-md-4">
          <div class="section-subtle small mb-1">Amount</div>
          <div class="fw-bold">${money(rx.amount)}</div>
          ${rx.notes ? `<div class="section-subtle small mt-3"><strong>Notes</strong><div>${rx.notes}</div></div>`:''}
        </div>
      </div>
    `;

                $('#rxViewBody').html(html);
                new bootstrap.Modal(document.getElementById('rxViewModal')).show();
            });
        })();
    </script>
@endpush
