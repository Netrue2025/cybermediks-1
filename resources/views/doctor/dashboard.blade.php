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

                {{-- Header --}}
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="pill-ghost"><i class="fa-solid fa-id-card-clip"></i></div>
                        <div>
                            <div class="fw-bold">Doctor Profile & Settings</div>
                            <div class="subtle small">Quick tweaks to your public profile & availability</div>
                        </div>
                    </div>
                </div>

                {{-- Availability --}}
                <div class="ps-row d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-soft {{ $profile?->is_available ?? false ? 'badge-on' : 'badge-off' }}"
                            id="availBadge">
                            <i
                                class="fa-solid {{ $profile?->is_available ?? false ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                            <span class="availableText">{{ $profile?->is_available ? 'Available' : 'Unavailable' }}</span>
                        </span>
                        <span class="subtle small">Patients can only book you when you’re available.</span>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="availSwitch"
                            {{ $profile?->is_available ?? false ? 'checked' : '' }}>
                    </div>
                </div>

                {{-- Quick edit row --}}
                <div class="ps-row mb-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small subtle mb-1">Title</label>
                            <input id="qTitle" class="form-control" placeholder="e.g., Consultant Cardiologist"
                                value="{{ $profile?->title }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small subtle mb-1">Consult Fee</label>
                            <div class="input-icon">
                                <span class="input-icon-prefix">$</span>
                                <input id="qFee" type="number" min="0" step="0.01"
                                    class="form-control pe-4" value="{{ $profile?->consult_fee }}">
                                <span class="input-icon-suffix subtle">USD</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small subtle mb-1">Avg Duration</label>
                            <div class="input-icon">
                                <input id="qDuration" type="number" min="5" step="5"
                                    class="form-control pe-5" value="{{ $profile?->avg_duration ?? 15 }}">
                                <span class="input-icon-suffix subtle">min</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Specialties --}}
                <div class="ps-row">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small subtle m-0">Specialties</label>
                        <span class="subtle small" id="specCount"></span>
                    </div>

                    <select id="qSpecialties" class="form-select d-none" multiple>
                        @foreach ($allSpecialties as $sp)
                            <option value="{{ $sp->id }}"
                                {{ in_array($sp->id, $selectedSpecialtyIds ?? []) ? 'selected' : '' }}>
                                {{ $sp->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Chip viewer (clickable to remove) --}}
                    <div id="specChips" class="chips-wrap"></div>

                    {{-- Simple picker row --}}
                    <div class="mt-2 d-flex gap-2">
                        <input id="specSearch" class="form-control" placeholder="Search specialties…">
                        <button type="button" class="btn btn-ghost" id="btnSpecAdd">
                            <i class="fa-solid fa-plus me-1"></i>Add
                        </button>
                    </div>

                    {{-- Results dropdown --}}
                    <div id="specResults" class="spec-results d-none"></div>

                    <div class="subtle small mt-2">
                        Tip: You can add multiple specialties. Click a chip to remove it.
                    </div>
                </div>

                <button class="btn btn-gradient w-100 mt-3" id="btnQuickSave">Save</button>
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
                <div class="subtle small mb-2">Uploaded Credentials</div>
                <div id="credList">
                    {!! view('doctor.credentials._list', [
                        'docs' => \App\Models\DoctorCredential::where('doctor_id', auth()->id())->orderByDesc('created_at')->get(),
                    ])->render() !!}
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            // Availability toggle
            $('#availSwitch').on('change', function() {
                const on = $(this).is(':checked');
                $('#availBadge')
                    .toggleClass('badge-on', on)
                    .toggleClass('badge-off', !on)
                    .html(`<i class="fa-solid ${on?'fa-circle-check':'fa-circle-xmark'} me-1"></i>
             <span class="availableText">${on?'Available':'Unavailable'}</span>`);
                $.post(`{{ route('doctor.profile.availability') }}`, {
                        _token: `{{ csrf_token() }}`,
                        is_available: on ? 1 : 0
                    })
                    .done(res => {
                        $('.availableText').text(on ? 'Available' : 'Unavailable');
                        flash('success', res.message || (on ? 'You are now available.' :
                            'You are set to unavailable.'));
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed to update availability');
                        // revert UI if server failed
                        $(this).prop('checked', !on);
                    });
            });

            // Quick save for title/fee/duration
            $('#btnQuickSave').on('click', function() {
                const $btn = $(this);
                lockBtn($btn);

                const spec = $('#qSpecialties').val() || []; // array of IDs (strings)

                $.post(`{{ route('doctor.profile.quick') }}`, {
                        _token: `{{ csrf_token() }}`,
                        title: $('#qTitle').val(),
                        consult_fee: $('#qFee').val(),
                        avg_duration: $('#qDuration').val(),
                        'specialty_ids': spec
                    })
                    .done(res => {
                        flash('success', res.message || 'Profile updated');
                    })
                    .fail(err => {
                        const msg = err.responseJSON?.message || 'Failed to update profile';
                        flash('danger', msg);
                        if (err.responseJSON?.errors) console.warn(err.responseJSON.errors);
                    })
                    .always(() => unlockBtn($btn));
            });

            // ---- Specialty chip picker (no external libs) ----
            const $select = $('#qSpecialties'); // hidden native multiple
            const $chips = $('#specChips');
            const $search = $('#specSearch');
            const $results = $('#specResults');

            function renderChips() {
                const vals = $select.val() || [];
                const map = {};
                $select.find('option').each(function() {
                    map[$(this).val()] = $(this).text();
                });
                $chips.empty();
                vals.forEach(v => {
                    $chips.append(
                        `<span class="chip2" data-id="${v}">
                            ${map[v] || 'Specialty'}
                            <i class="fa-solid fa-xmark x"></i>
                        </span>`
                    );
                });
                $('#specCount').text(vals.length ? `${vals.length} selected` : '');
            }

            // remove on chip click
            $chips.on('click', '.chip2 .x', function() {
                const id = $(this).closest('.chip2').data('id').toString();
                const vals = ($select.val() || []).filter(v => v.toString() !== id);
                $select.val(vals);
                renderChips();
            });

            // lightweight search over all options
            function searchOptions(q) {
                q = (q || '').toLowerCase();
                const items = [];
                $select.find('option').each(function() {
                    const id = $(this).val(),
                        text = $(this).text();
                    if (!q || text.toLowerCase().includes(q)) {
                        items.push({
                            id,
                            text
                        });
                    }
                });
                return items.slice(0, 50);
            }

            function showResults(list) {
                if (!list.length) {
                    $results.addClass('d-none').empty();
                    return;
                }
                const html = `<div class="menu">` + list.map(it =>
                    `<div class="item" data-id="${it.id}">${it.text}</div>`
                ).join('') + `</div>`;
                $results.removeClass('d-none').html(html);
            }

            $search.on('input', function() {
                showResults(searchOptions($(this).val()));
            });

            // pick from dropdown
            $results.on('click', '.item', function() {
                const id = $(this).data('id').toString();
                const cur = $select.val() || [];
                if (!cur.includes(id)) {
                    cur.push(id);
                    $select.val(cur);
                    renderChips();
                }
                $results.addClass('d-none').empty();
                $search.val('');
            });

            // also allow Add button to take the top result
            $('#btnSpecAdd').on('click', function() {
                const list = searchOptions($search.val());
                if (!list.length) return;
                const id = list[0].id.toString();
                const cur = $select.val() || [];
                if (!cur.includes(id)) {
                    cur.push(id);
                    $select.val(cur);
                    renderChips();
                }
                $results.addClass('d-none').empty();
                $search.val('');
            });

            // initial render
            renderChips();


            function refreshCredList() {
                $.get(`{{ route('doctor.credentials.fragment') }}`, function(html) {
                    $('#credList').html(html);
                });
            }

            $('#btnUpload').on('click', function() {
                const $btn = $(this);
                lockBtn($btn);

                const type = $('#credType').val();
                const fileInput = $('#credFile')[0];
                const file = fileInput?.files?.[0];

                if (!file || !type || type === 'Select credential type') {
                    flash('danger', 'Please choose a credential type and file.');
                    return unlockBtn($btn);
                }

                const fd = new FormData();
                fd.append('type', type);
                fd.append('file', file);
                fd.append('_token', `{{ csrf_token() }}`);

                $.ajax({
                    url: `{{ route('doctor.credentials.store') }}`,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        flash('success', res.message || 'Credential uploaded');
                        // reset inputs
                        $('#credType').prop('selectedIndex', 0);
                        $('#credFile').val('');
                        refreshCredList();
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Upload failed';
                        flash('danger', msg);
                        // Optional: display validation errors
                        if (xhr.responseJSON?.errors) console.warn(xhr.responseJSON.errors);
                    },
                    complete: function() {
                        unlockBtn($btn);
                    }
                });
            });

            $(document).on('click', '[data-cred-del]', function() {
                const id = $(this).data('cred-del');
                if (!confirm('Delete this credential?')) return;

                $.ajax({
                    url: `{{ url('/doctor/credentials') }}/${id}`,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: `{{ csrf_token() }}`
                    },
                    success: function(res) {
                        flash('success', res.message || 'Removed');
                        refreshCredList();
                    },
                    error: function(xhr) {
                        flash('danger', xhr.responseJSON?.message || 'Delete failed');
                    }
                });
            });
        })();
    </script>
@endpush
