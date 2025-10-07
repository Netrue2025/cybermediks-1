@extends('layouts.doctor')
@section('title', 'Dashboard')

@push('styles')
    <style>
        :root {
            --bg: #0f172a;
            --panel: #0f1628;
            --card: #101a2e;
            --border: #27344e;
            --text: #e5e7eb;
            --muted: #9aa3b2;
            --chip: #1e293b;
            --chipBorder: #2a3854;
            --accent1: #8758e8;
            --accent2: #e0568a;
            --success: #22c55e;
        }

        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .cardx-soft {
            background: #0f1a2e;
        }

        /* slightly brighter inner cards */
        .metric {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .pill {
            width: 44px;
            height: 44px;
            background: #0b1222;
            border: 1px solid var(--border);
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Section header (thin underline like screenshot) */
        .sec-head {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 10px;
        }

        .sec-head i {
            opacity: .9;
        }

        .sec-wrap {
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #0f1a2e;
            padding: 14px;
        }

        .subtle {
            color: var(--muted);
        }

        /* Empty states */
        .empty {
            color: #9aa3b2;
            text-align: center;
            padding: 32px 8px;
        }

        .empty .ico {
            font-size: 28px;
            opacity: .7;
            margin-bottom: 8px;
        }

        /* Quick actions row */
        .qa-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            background: #0f1a2e;
            height: 100%;
        }

        .qa-title {
            font-weight: 700;
        }

        .qa-note {
            color: #a1aec3;
            font-size: .92rem;
        }

        /* Toggle row inside Profile & Settings */
        .ps-row {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            background: #0e182b;
        }

        /* Credential management */
        .cred-card {
            background: #121a2c;
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .btn-ghost {
            background: #0e162b;
            border: 1px solid #283652;
            color: #e5e7eb;
        }

        .btn-ghost:hover {
            background: #1a2845;
            color: #fff;
        }

        .btn-gradient {
            background-image: linear-gradient(90deg, var(--accent1), var(--accent2));
            border: 0;
            color: #fff;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            background: #0b1222;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6b7280;
            box-shadow: none;
        }

        .link-card {
            position: relative;
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .link-card:hover {
            filter: brightness(1.06);
        }

        .link-card .stretched-link {
            position: static;
        }

        /* keep a11y while we control position */
        .cursor-pointer {
            cursor: pointer;
        }

        .pill-ghost {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0b1222;
            border: 1px solid var(--border);
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .85rem;
            background: #0e162b;
        }

        .badge-on {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43;
        }

        .badge-off {
            background: rgba(239, 68, 68, .08);
            border-color: #6f2b2b;
        }

        .input-icon {
            position: relative
        }

        .input-icon-prefix {
            position: absolute;
            left: .6rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2
        }

        .input-icon-suffix {
            position: absolute;
            right: .6rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2
        }

        .input-icon .form-control {
            padding-left: 1.6rem
        }

        .chips-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            min-height: 38px;
            padding: .25rem;
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 8px
        }

        .chip2 {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #13203a;
            border: 1px solid #2a3854;
            border-radius: 999px;
            padding: .25rem .55rem;
            color: #cfe0ff;
            font-size: .85rem
        }

        .chip2 .x {
            opacity: .7;
            cursor: pointer
        }

        .chip2 .x:hover {
            opacity: 1
        }

        .spec-results {
            position: relative;
            margin-top: .25rem
        }

        .spec-results .menu {
            position: absolute;
            z-index: 10;
            width: 100%;
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 8px;
            max-height: 220px;
            overflow: auto;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .35)
        }

        .spec-results .item {
            padding: .45rem .6rem;
            border-bottom: 1px solid var(--border);
            cursor: pointer
        }

        .spec-results .item:hover {
            background: #111f37
        }
    </style>
@endpush

@section('content')
    {{-- METRICS --}}
    <div class="row g-3">
        {{-- <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Pending Requests</div>
                        <div class="metric">{{ $pendingRequestsCount }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-users fs-5" style="color:#efed86;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Active Consultations</div>
                        <div class="metric">{{ $activeConsultationsCount }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-comments fs-5" style="color:#86bcef;"></i></div>
                </div>
            </div>
        </div> --}}

        <div class="col-lg-6">
            <div class="cardx" style="cursor: pointer;"
                onclick="window.location.href='{{ route('doctor.prescriptions.index', ['from' => today()->toDateString(), 'to' => today()->toDateString()]) }}'">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Prescriptions Today</div>
                        <div class="metric">{{ $prescriptionsToday }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-file-prescription fs-5" style="color:#86efac;"></i></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6" style="cursor: pointer;" onclick="window.location.href='{{ route('doctor.wallet.index') }}'">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Total Earnings</div>
                        <div class="metric">$ {{ number_format($earnings, 2, '.', ',') }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-dollar-sign fs-5" style="color:#86bcef;"></i></div>
                </div>
            </div>
        </div>

    </div>



    {{-- VIDEO CALL QUEUE --}}
    <div class="cardx mt-3">
        <div class="sec-head cursor-pointer" onclick="window.location.href='{{ route('doctor.queue') }}'">
            <i class="fa-regular fa-folder-open"></i>
            <span>Video Call Queue</span>
            @isset($videoQueueCount)
                <span class="badge bg-secondary ms-2">{{ $videoQueueCount }}</span>
            @endisset
        </div>



        @if ($videoQueueCount > 0)
            <div class="d-flex flex-column gap-2">
                @foreach ($videoQueue as $appt)
                    <div class="ps-row d-flex justify-content-between align-items-center">
                        <div class="me-2">
                            <div class="fw-semibold">
                                {{ $appt->patient?->first_name }} {{ $appt->patient?->last_name }}
                                <span class="badge bg-info ms-2">Video</span>
                            </div>
                            <div class="subtle small">Reason: {{ $appt->reason }}</div>
                            <div class="subtle small">Scheduled at:
                                {{ $appt->scheduled_at ? $appt->scheduled_at->format('M d, Y h:i A') : 'As soon as possible' }}
                            </div>
                            @if (!empty($appt->meeting_link))
                                <div class="subtle small mt-1">
                                    Meeting: <a class="link-light text-decoration-none" href="{{ $appt->meeting_link }}"
                                        target="_blank">
                                        Open link
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex flex-column align-items-end gap-2" style="min-width:260px;">
                            @if ($appt->status === 'pending')
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success btn-sm" data-accept-appt="{{ $appt->id }}">
                                        <i class="fa-solid fa-check me-1"></i> Accept
                                    </button>
                                    <button class="btn btn-outline-light btn-sm" data-reject-appt="{{ $appt->id }}">
                                        <i class="fa-solid fa-xmark me-1"></i> Reject
                                    </button>
                                </div>
                            @elseif (in_array($appt->status, ['accepted', 'scheduled']))

                                <button class="btn btn-outline-light btn-sm" data-reject-appt="{{ $appt->id }}">
                                    <i class="fa-solid fa-xmark me-1"></i> Reject
                                </button>

                                @if (!$appt->prescription_issued)
                                    <button class="btn btn-gradient" id="btnPrescription"
                                        data-patientid="{{ $appt->patient?->id }}"
                                        data-patientname="{{ $appt->patient?->first_name . ' ' . $appt->patient?->last_name }}"
                                        data-appointmentid="{{ $appt->id }}">Add Prescription</button>
                                @else
                                    @if (!empty($appt->meeting_link))
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-success btn-sm"
                                                data-completed-appt="{{ $appt->id }}">
                                                <i class="fa-solid fa-check me-1"></i> Mark Completed
                                            </button>
                                        </div>
                                    @endif
                                @endif


                                <div class="subtle small">The patient will automatically see this link</div>
                            @else
                                <span class="subtle small text-uppercase">{{ ucfirst($appt->status) }}</span>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty">
                <div class="ico"><i class="fa-solid fa-users"></i></div>
                <div>No patients in the video call queue.</div>
            </div>
        @endif

        
    </div>

    {{-- PRESCRIPTION MODAL --}}
    <div class="modal fade" id="prescriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:#121a2c;border:1px solid var(--border);border-radius:18px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Add Prescrition</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rxForm" class="cardx">
                        @csrf
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-file-medical"></i>
                            <h5 class="m-0">New e-Prescription</h5>
                        </div>
                        <div class="text-secondary mb-3">Select patient and add medications.</div>

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">Patient</label>
                                <input type="text" id="patientId" name="patient_id" hidden value="">
                                <input type="text" id="appointmentId" name="appointment_id" hidden value="">
                                <h3 id="patientDetails"></h3>

                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Encounter Type</label>
                                <select class="form-select disabled" name="encounter" required>
                                    <option value="video" selected>Video</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Refills</label>
                                <input type="number" min="0" value="0" class="form-control"
                                    name="refills">
                            </div>
                        </div>

                        <hr class="my-4" style="border-color:var(--border);opacity:.6">

                        <div id="rxItems" class="d-flex flex-column gap-2">
                            <div class="rx-item">
                                <div class="row g-2">
                                    <div class="col-lg-4">
                                        <label class="form-label">Drug</label>
                                        <input class="form-control" name="items[0][drug]" placeholder="Amoxicillin 500mg"
                                            required>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Dose</label>
                                        <input class="form-control" name="items[0][dose]" placeholder="1 tab">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Freq.</label>
                                        <input class="form-control" name="items[0][freq]" placeholder="2×/day">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Days</label>
                                        <input class="form-control" type="number" name="items[0][days]"
                                            placeholder="7">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Qty</label>
                                        <input class="form-control" type="number" name="items[0][quantity]"
                                            placeholder="14">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-outline-light btn-sm" id="addItem"><i
                                    class="fa-solid fa-plus me-1"></i>Add Item</button>
                            <button class="btn btn-gradient ms-auto" id="btnIssue"><span class="btn-text">Issue
                                    Prescription</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- PENDING REQUESTS + ACTIVE CONSULTATIONS --}}
    <div class="row g-3 mt-1">

        {{-- Pending Patient Requests --}}
        <div class="col-lg-6">
            <div class="cardx h-100">
                <div class="sec-head d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-user-group"></i> Pending Patient Requests</span>
                    <a href="{{ route('doctor.patients', ['tab' => 'pending']) }}"
                        class="text-decoration-none subtle small">
                        View all <i class="fa-solid fa-arrow-right-long ms-1"></i>
                    </a>
                </div>

                @if ($pendingConvs->isEmpty())
                    <a href="{{ route('doctor.patients', ['tab' => 'pending']) }}" class="sec-wrap link-card">
                        <div class="empty">
                            <div class="ico"><i class="fa-solid fa-user-group"></i></div>
                            <div>No new patient requests</div>
                        </div>
                        <span class="stretched-link"></span>
                    </a>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach ($pendingConvs as $c)
                            <div class="ps-row d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $c->patient?->first_name }} {{ $c->patient?->last_name }}
                                    </div>
                                    <div class="subtle small">Requested {{ $c->created_at?->diffForHumans() }}</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-success btn-sm" data-accept="{{ $c->id }}">
                                        <i class="fa-solid fa-check me-1"></i> Accept
                                    </button>
                                    <button class="btn btn-danger btn-sm" data-reject="{{ $c->id }}">
                                        <i class="fa-solid fa-xmark me-1"></i> Reject
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        <a href="{{ route('doctor.patients', ['tab' => 'pending']) }}" class="btn btn-ghost w-100">
                            See all pending ({{ $pendingRequestsCount }})
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Active Consultations --}}
        <div class="col-lg-6">
            <div class="cardx h-100">
                <div class="sec-head d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-check-double" style="color:#22c55e;"></i> Active Consultations</span>
                    <a href="{{ route('doctor.messenger', ['filter' => 'active']) }}"
                        class="text-decoration-none subtle small">
                        View all <i class="fa-solid fa-arrow-right-long ms-1"></i>
                    </a>
                </div>

                @if ($activeConvs->isEmpty())
                    <a href="{{ route('doctor.messenger', ['filter' => 'active']) }}" class="sec-wrap link-card">
                        <div class="empty">
                            <div class="ico"><i class="fa-regular fa-message"></i></div>
                            <div>No active consultations<br>
                                <span class="subtle">Accept a patient request to begin.</span>
                            </div>
                        </div>
                        <span class="stretched-link"></span>
                    </a>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach ($activeConvs as $c)
                            <div class="ps-row d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $c->patient?->first_name }} {{ $c->patient?->last_name }}
                                    </div>
                                    <div class="subtle small">Active since {{ $c->updated_at?->diffForHumans() }}</div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('doctor.messenger', ['conversation' => $c->id, 'filter' => 'active']) }}"
                                        class="btn btn-gradient btn-sm">Open Chat</a>
                                    <button class="btn btn-outline-light btn-sm" data-close="{{ $c->id }}">
                                        <i class="fa-solid fa-xmark me-1"></i> Close
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        <a href="{{ route('doctor.messenger', ['filter' => 'active']) }}" class="btn btn-ghost w-100">
                            See all active ({{ $activeConsultationsCount }})
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>


@endsection

@push('scripts')
    <script>
        (function() {


            // Accept (pending -> active)
            $(document).on('click', '[data-accept]', function() {
                const id = $(this).data('accept');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('doctor.conversations.accept', ['conversation' => '__ID__']) }}`.replace(
                        '__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Accepted');
                        location.reload(); // simplest; or remove row & update counts dynamically
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed to accept');
                    })
                    .always(() => unlockBtn($btn));
            });

            // Reject (pending -> rejected)
            $(document).on('click', '[data-reject]', function() {
                const id = $(this).data('reject');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('doctor.conversations.reject', ['conversation' => '__ID__']) }}`.replace(
                        '__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Rejected');
                        location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed to reject');
                    })
                    .always(() => unlockBtn($btn));
            });

            // Close (pending/active -> closed)
            $(document).on('click', '[data-close]', function() {
                const id = $(this).data('close');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('doctor.conversations.close', ['conversation' => '__ID__']) }}`.replace(
                        '__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Closed');
                        location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed to close');
                    })
                    .always(() => unlockBtn($btn));
            });

            // Accept video call
            $(document).on('click', '[data-accept-appt]', function() {
                const id = $(this).data('accept-appt');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('doctor.queue.accept', '__ID__') }}`.replace('__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Accepted');
                        location.reload();
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to accept');
                    })
                    .always(() => unlockBtn($btn));
            });

            // mark completed
            $(document).on('click', '[data-completed-appt]', function() {
                const id = $(this).data('completed-appt');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('doctor.queue.completed', '__ID__') }}`.replace('__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Accepted');
                        location.reload();
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to accept');
                    })
                    .always(() => unlockBtn($btn));
            });

            // Reject video call
            $(document).on('click', '[data-reject-appt]', function() {
                const id = $(this).data('reject-appt');
                const $btn = $(this);
                if (!confirm('Reject this request?')) return;
                lockBtn($btn);
                $.post(`{{ route('doctor.queue.reject', '__ID__') }}`.replace('__ID__', id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Rejected');
                        location.reload();
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to reject');
                    })
                    .always(() => unlockBtn($btn));
            });


            let rxIndex = 1;



            $('#addItem').on('click', function() {
                const i = rxIndex++;
                $('#rxItems').append(`
                <div class="rx-item">
                    <div class="row g-2">
                    <div class="col-lg-4"><label class="form-label">Drug</label><input class="form-control" name="items[${i}][drug]" placeholder="e.g., Amoxicillin 500mg" required></div>
                    <div class="col-lg-2"><label class="form-label">Dose</label><input class="form-control" name="items[${i}][dose]" placeholder="1 tab"></div>
                    <div class="col-lg-2"><label class="form-label">Freq.</label><input class="form-control" name="items[${i}][freq]" placeholder="2×/day"></div>
                    <div class="col-lg-2"><label class="form-label">Days</label><input class="form-control" type="number" name="items[${i}][days]" placeholder="7"></div>
                    <div class="col-lg-2"><label class="form-label">Qty</label><input class="form-control" type="number" name="items[${i}][quantity]" placeholder="14"></div>
                    </div>
                </div>`);
            });

            $('#rxForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#btnIssue');
                lockBtn($btn);

                $.ajax({
                    url: `{{ route('doctor.prescriptions.store') }}`,
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': `{{ csrf_token() }}`
                    },
                    success: function(res) {
                        flash('success', res.message || 'Prescription issued');
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        } else {
                            // fallback: clear the form for another entry
                            $('#rxForm')[0].reset();
                            $('#rxItems').html($('#rxItems .rx-item').first()); // keep first row
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to issue prescription';
                        flash('danger', msg);
                        // Optional: show validation errors
                        if (xhr.responseJSON?.errors) {
                            console.warn(xhr.responseJSON.errors);
                        }
                    },
                    complete: function() {
                        unlockBtn($btn);
                    }
                });
            });

            $('#btnPrescription').on('click', function() {
                let appointmentId = $(this).data('appointmentid');
                let patientId = $(this).data('patientid');
                let patientName = $(this).data('patientname');
                let patientDetails = patientName + ' (ID: ' + patientId + ')';
                $("#patientId").val(patientId)
                $("#appointmentId").val(appointmentId)
                $("#patientDetails").text(patientDetails);
                new bootstrap.Modal(document.getElementById('prescriptionModal')).show();
            });

        })();
    </script>
@endpush
