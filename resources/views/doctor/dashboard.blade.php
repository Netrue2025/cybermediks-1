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

        <div class="col-lg-6" style="cursor: pointer;">
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
    <div class="cardx mt-3" id="videoCallQueue">
        @include('doctor.partials._video_call_queue', [
            'videoQueue' => $videoQueue,
            'videoQueueCount' => $videoQueueCount,
        ])

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
                                <input type="number" min="0" value="0" class="form-control" name="refills">
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
                                        <input class="form-control" type="number" name="items[0][days]" placeholder="7">
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

    <div class="modal fade" id="closeChatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background:#0f1628;border:1px solid #27344e;border-radius:18px;color:#e5e7eb;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Close Appointment</h5>
                    <button type="button" id="closeModalBtn" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Hidden input ensures 0 is sent when unchecked -->
                    <input type="hidden" name="prescription_is_required" value="0">

                    <div class="form-check form-switch d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="prescription_is_required"
                            name="prescription_is_required" value="1">
                        <label class="form-check-label" for="prescription_is_required">
                            Prescription not required
                        </label>
                    </div>

                    <p class="mt-3 mb-0" style="font-size:.9rem;color:#9aa3b2;">
                        Toggle this if you can close the appointment without issuing a prescription.
                    </p>

                    <div class="mt-4 d-flex gap-2">
                        <button type="button" class="btn btn-outline-light flex-fill" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" data-completed-appt="" class="btn flex-fill" id="endChat"
                            style="background:linear-gradient(135deg,#3b82f6,#06b6d4);color:#fff;border:0;">
                            Close Appointment
                        </button>
                    </div>
                </div>

                <div class="modal-footer border-0 d-flex justify-content-between">
                    <button class="btn btn-success" id="newPrescription" type="button">
                        Add Prescription
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- PENDING REQUESTS + ACTIVE CONSULTATIONS --}}
    <div class="row g-3 mt-1">

        {{-- Pending Patient Requests --}}
        <div class="col-lg-6" id="pendingRequestSection">
            @include('doctor.partials._pending_request', [
                'pendingConvs' => $pendingConvs,
                'pendingRequestsCount' => $pendingRequestsCount,
            ])

        </div>

        {{-- Active Consultations --}}
        <div class="col-lg-6" id="activeRequestSection">
            @include('doctor.partials._active_request', [
                'activeConvs' => $activeConvs,
                'activeConsultationsCount' => $activeConsultationsCount,
            ])
        </div>

    </div>


@endsection

@push('scripts')
    <script>
        (function() {


            function replaceIfChanged($container, html) {
                if ($container.length && $container.html().trim() !== String(html).trim()) {
                    $container.html(html);
                }
            }

            function autoLoader() {
                const url = `{{ route('doctor.dashboard') }}`;
                return $.get(url).done(function(res) {
                    if (res.videoCallQueue) replaceIfChanged($('#videoCallQueue'), res.videoCallQueue);
                    if (res.pendingRequest) replaceIfChanged($('#pendingRequestSection'), res.pendingRequest);
                    if (res.activeRequest) replaceIfChanged($('#activeRequestSection'), res.activeRequest);
                });
            }


            setInterval(() => {
                autoLoader()
            }, 5000);

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
                        autoLoader()
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
                        autoLoader()
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
                        autoLoader()
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
                        autoLoader()
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to accept');
                    })
                    .always(() => unlockBtn($btn));
            });


            $("#newPrescription").on('click', function() {
                $("#closeModalBtn").trigger('click');
                $(".btnPrescription").trigger('click');
            })

            // mark completed
            $('#endChat').on('click', function() {
                const is_required = $('#prescription_is_required').prop('checked') ? 1 : 0;
                const id = $(this).data('completed-appt');
                const $btn = $(this);

                //if (is_required === 0) {
                //   flash('danger', 'Prescription is required');
                // return;
                //}

                lockBtn($btn);
                $.post(`{{ route('doctor.queue.completed', '__ID__') }}`.replace('__ID__', id), {
                        _token: `{{ csrf_token() }}`,
                        prescription_is_required: is_required
                    })
                    .done(res => {
                        flash('success', res.message || 'Accepted');
                        autoLoader();
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to accept');
                    })
                    .always(() => unlockBtn($btn));
            });

            // mark completed
            $(document).on('click', '[data-completed-appt]', function() {
                const id = $(this).data('completed-appt');
                const $endBtn = $('#endChat');

                // Set consistently with .data()
                $endBtn.data('completed-appt', id); // or use .data('close', id) if your other code reads that

                const modalEl = document.getElementById('closeChatModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
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
                        autoLoader();
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
                            autoLoader()
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

            $(document).off('click', '.btnPrescription').on('click', '.btnPrescription', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const appointmentId = $btn.data('appointmentId') ?? $btn.data('appointmentid');
                const patientId = $btn.data('patientId') ?? $btn.data('patientid');
                const patientName = $btn.data('patientName') ?? $btn.data('patientname');

                $('#patientId').val(patientId);
                $('#appointmentId').val(appointmentId);
                $('#patientDetails').text(`${patientName} (ID: ${patientId})`);

                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('prescriptionModal'));
                modal.show();
            });

            let pollId = setInterval(autoLoader, 5000);

            function pausePolling() {
                if (pollId) {
                    clearInterval(pollId);
                    pollId = null;
                }
            }

            function resumePolling() {
                if (!pollId) {
                    pollId = setInterval(autoLoader, 5000);
                }
            }

            // Pause on any modal show; resume when last modal closes
            document.addEventListener('show.bs.modal', pausePolling);
            document.addEventListener('hidden.bs.modal', function() {
                // Only resume if no modals are visible
                if ($('.modal.show').length === 0) resumePolling();
            });


        })();
    </script>
@endpush
