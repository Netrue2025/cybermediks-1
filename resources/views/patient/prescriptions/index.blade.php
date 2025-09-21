@extends('layouts.patient')
@section('title', 'My Prescriptions')

@push('styles')
    <style>
        .rx-row {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
        }

        .rx-badge {
            background: #10203a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .2rem .5rem;
            color: #cfe0ff;
        }

        .rx-status {
            background: var(--chip);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .2rem .6rem;
            text-transform: capitalize;
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

        .price-pill {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .18rem .55rem;
            font-weight: 600
        }
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-file-prescription"></i>
            <h5 class="m-0">My Prescriptions</h5>
        </div>
        <div class="section-subtle mb-3">View, refill, and manage your prescriptions</div>

        <div class="row g-2">
            <div class="col-lg-6">
                <input id="rxSearch" class="form-control" placeholder="Search by drug, doctor, or code..."
                    value="{{ request('q') }}">
            </div>
            <div class="col-lg-3">
                <select id="rxStatus" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="refill_requested" {{ request('status') === 'refill_requested' ? 'selected' : '' }}>Refill
                        requested</option>
                </select>
            </div>
        </div>
    </div>

    <div id="rxList">
        @include('patient.prescriptions._list', ['prescriptions' => $prescriptions])
    </div>

    <!-- View dialog -->
    <div class="modal fade" id="rxViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card); color:var(--text);">
                <div class="modal-header">
                    <h6 class="modal-title">Prescription</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="rxViewBody" class="small"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const $list = $('#rxList');
            let t = null;

            function fetchList() {
                const q = $('#rxSearch').val();
                const status = $('#rxStatus').val();
                $.get(`{{ route('patient.prescriptions.index') }}`, {
                    q,
                    status
                }, function(html) {
                    $list.html(html);
                });
            }

            $('#rxSearch').on('input', function() {
                clearTimeout(t);
                t = setTimeout(fetchList, 300);
            });
            $('#rxStatus').on('change', fetchList);

            // View: read JSON payload embedded in row
            $(document).on('click', '[data-rx-view]', function() {
                const payload = $(this).data('rx-view'); // stringified JSON
                const rx = typeof payload === 'string' ? JSON.parse(payload) : payload;

                let html = `
            <div class="mb-2"><span class="rx-badge">Rx ${rx.code}</span>
            <span class="rx-status ms-2">${rx.status.replace('_',' ')}</span></div>
            <div class="mb-2"><strong>Doctor:</strong> ${rx.doctor}</div>
            ${rx.notes ? `<div class="mb-3"><strong>Notes:</strong> ${rx.notes}</div>` : ``}
            <div class="mb-2"><strong>Items</strong></div>
            <ul class="mb-0">
                ${rx.items.map(i => `<li>${i.drug}${i.dose?` • ${i.dose}`:''}${i.frequency?` • ${i.frequency}`:''}${i.days?` • ${i.days}`:''}${i.directions?` — ${i.directions}`:''}</li>`).join('')}
            </ul>
        `;
                $('#rxViewBody').html(html);
                new bootstrap.Modal(document.getElementById('rxViewModal')).show();
            });

            // Refill click (placeholder: route to your refill flow)
            $(document).on('click', '[data-rx-refill]', function() {
                const id = $(this).data('rx-refill');
                window.location.href = `/patient/prescriptions/${id}/refill`; // implement when ready
            });
        })();
    </script>
@endpush
