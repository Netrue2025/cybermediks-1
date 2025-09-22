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
            <div class="cardx" style="cursor: pointer;" onclick="window.location.href='{{ route('doctor.prescriptions.index', ['from' => today()->toDateString(), 'to' => today()->toDateString()]) }}'">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Prescriptions Today</div>
                        <div class="metric">{{ $prescriptionsToday }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-file-prescription fs-5" style="color:#86efac;"></i></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
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
                                    <button class="btn btn-outline-light btn-sm" data-close="{{ $c->id }}">
                                        <i class="fa-solid fa-xmark me-1"></i> Close
                                    </button>
                                    <a href="{{ route('doctor.messenger', ['conversation' => $c->id, 'filter' => 'pending']) }}"
                                        class="btn btn-ghost btn-sm">Open</a>
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


    {{-- QUICK ACTIONS --}}
    {{-- <div class="row g-3">
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
    </div> --}}


    {{-- PROFILE & SETTINGS + CREDENTIAL MANAGEMENT --}}

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

        })();
    </script>
@endpush
