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
    </style>
@endpush

@section('content')
    {{-- METRICS --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Pending Requests</div>
                        <div class="metric">{{ $pendingRequests }}</div>
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
                        <div class="metric">{{ $activeConsultations }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-comments fs-5" style="color:#86bcef;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Prescriptions Today</div>
                        <div class="metric">{{ $prescriptionsToday }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-file-prescription fs-5" style="color:#86efac;"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="cardx mt-3 cardx-soft">
        <div class="d-flex align-items-center justify-content-between">
            <div class="subtle">Total Earnings</div>
            <div class="fw-bold">$ {{ number_format($earnings, 2, '.', ',') }}</div>
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


        <a href="{{ route('doctor.queue') }}" class="sec-wrap link-card">
            <div class="empty">
                <div class="ico"><i class="fa-solid fa-users"></i></div>
                <div>No patients in the video call queue.</div>
            </div>
            <span class="stretched-link"></span>
        </a>
    </div>


    {{-- PENDING REQUESTS + ACTIVE CONSULTATIONS --}}
    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="cardx h-100">
                <div class="sec-head"><i class="fa-solid fa-user-group"></i> <span>Pending Patient Requests</span></div>
                <a href="{{ route('doctor.patients') }}" class="sec-wrap link-card">
                    <div class="empty">
                        <div class="ico"><i class="fa-solid fa-user-group"></i></div>
                        <div>No new patient requests</div>
                    </div>
                    <span class="stretched-link"></span>
                </a>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="cardx h-100">
                <div class="sec-head"><i class="fa-solid fa-check-double" style="color:#22c55e;"></i> <span>Active
                        Consultations</span></div>
                <a href="{{ route('doctor.messenger') }}" class="sec-wrap link-card">
                    <div class="empty">
                        <div class="ico"><i class="fa-regular fa-message"></i></div>
                        <div>No active consultations<br><span class="subtle">Accept a patient request to begin.</span></div>
                    </div>
                    <span class="stretched-link"></span>
                </a>
            </div>
        </div>
    </div>


    {{-- QUICK ACTIONS --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <a href="{{ route('doctor.prescriptions.create') }}"
                class="qa-card link-card d-flex gap-3 text-decoration-none">
                <div class="pill" style="width:38px;height:38px;"><i class="fa-solid fa-file-medical"></i></div>
                <div>
                    <div class="qa-title">New Prescription</div>
                    <div class="qa-note">Issue a new e-prescription for a patient.</div>
                </div>
                <span class="stretched-link"></span>
            </a>
        </div>

        <div class="col-lg-4">
            <a href="{{ route('doctor.schedule') }}" class="qa-card link-card d-flex gap-3 text-decoration-none">
                <div class="pill" style="width:38px;height:38px;"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <div class="qa-title">Manage Schedule</div>
                    <div class="qa-note">Set your availability and manage appointments.</div>
                </div>
                <span class="stretched-link"></span>
            </a>
        </div>

        <div class="col-lg-4">
            <a href="{{ route('doctor.patients') }}" class="qa-card link-card d-flex gap-3 text-decoration-none">
                <div class="pill" style="width:38px;height:38px;"><i class="fa-solid fa-user-doctor"></i></div>
                <div>
                    <div class="qa-title">View Patients</div>
                    <div class="qa-note">Access records of your patient history.</div>
                </div>
                <span class="stretched-link"></span>
            </a>
        </div>

        <div class="col-lg-4">
            <a href="{{ route('doctor.messenger') }}" class="qa-card link-card d-flex gap-3 text-decoration-none">
                <div class="pill" style="width:38px;height:38px;"><i class="fa-regular fa-comment-dots"></i></div>
                <div>
                    <div class="qa-title">Open Messenger</div>
                    <div class="qa-note">Continue conversations with your patients.</div>
                </div>
                <span class="stretched-link"></span>
            </a>
        </div>
    </div>


    {{-- PROFILE & SETTINGS + CREDENTIAL MANAGEMENT --}}
    <div class="row g-3 mt-1">
        <div class="col-lg-7">
            <div class="cardx h-100">
                <div class="sec-head d-flex justify-content-between">
                    <span><i class="fa-solid fa-id-card-clip"></i> Profile &amp; Settings</span>
                    <a href="{{ route('doctor.profile') }}" class="text-decoration-none subtle">
                        <i class="fa-regular fa-pen-to-square me-1"></i>Edit
                    </a>

                </div>

                <div class="ps-row d-flex align-items-center justify-content-between">
                    <div class="subtle">Unavailable</div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="availSwitch">
                    </div>
                </div>

                <a href="{{ route('doctor.profile') }}" class="btn btn-ghost w-100 mt-3 text-decoration-none">
                    <i class="fa-regular fa-pen-to-square me-1"></i> Edit Profile &amp; Specialties
                </a>

            </div>
        </div>

        <div class="col-lg-5">
            <div class="cred-card p-3 p-md-4">
                <div class="sec-head"><i class="fa-solid fa-badge-check"></i> <span>Credential Management</span></div>

                <div class="mb-3">
                    <label class="form-label small subtle">Credential Type</label>
                    <select class="form-select" id="credType">
                        <option selected>Select credential type</option>
                        <option>Medical License</option>
                        <option>Board Certification</option>
                        <option>ID / Passport</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small subtle">Document</label>
                    <input type="file" class="form-control" id="credFile">
                </div>

                <div class="d-grid">
                    <button class="btn btn-success" id="btnUpload">
                        <span class="btn-text">Upload Credential <i class="fa-solid fa-rotate-right ms-1"></i></span>
                    </button>
                </div>

                <hr class="my-4" style="border-color:var(--border); opacity:.6;">
                <div class="subtle small">Uploaded Credentials</div>
                <div class="empty py-3">No credentials uploaded yet.</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Availability toggle (demo)
        $('#availSwitch').on('change', function() {
            const on = $(this).is(':checked');
            flash('success', on ? 'You are now available.' : 'You are set to unavailable.');
            // TODO: $.post(...) to save status
        });

        // Credential upload (demo)
        $('#btnUpload').on('click', function() {
            const $btn = $(this);
            lockBtn($btn);
            const type = $('#credType').val();
            const file = $('#credFile')[0].files[0];
            if (!file || !type || type === 'Select credential type') {
                flash('danger', 'Please choose a credential type and file.');
                unlockBtn($btn);
                return;
            }
            // TODO: AJAX upload to server with FormData
            setTimeout(() => {
                flash('success', 'Credential uploaded (demo)');
                unlockBtn($btn);
            }, 900);
        });
    </script>
@endpush
