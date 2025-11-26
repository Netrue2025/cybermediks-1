@extends('layouts.patient')
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
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
        }

        .metric {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: .25rem;
        }

        .section-subtle {
            color: var(--muted);
            margin-bottom: 1rem;
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

        .search-wrap {
            position: relative;
        }

        .search-wrap .icon {
            position: absolute;
            left: .75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2;
        }

        .search-wrap input {
            padding-left: 2.3rem;
        }

        .switch-label {
            color: var(--muted);
            margin-left: .35rem;
        }

        .spec-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 16px;
        }

        @media (max-width:1200px) {
            .spec-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width:576px) {
            .spec-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .spec-tile {
            border-radius: 14px;
            padding: 18px 12px;
            text-align: center;
            cursor: pointer;
            transition: all .15s ease;
            border: 1px solid transparent;
            background: #0f1a2e;
        }

        .spec-tile .icon {
            font-size: 22px;
            margin-bottom: 10px;
            display: block;
        }

        .spec-tile span {
            color: #cdd6e4;
            font-weight: 600;
        }

        .spec-tile:hover {
            background: #111f37;
            border-color: #344767;
        }

        .spec-tile.active {
            outline: 2px solid rgba(135, 88, 232, .45);
        }

        .doctor-row {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .avatar-sm {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #14203a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cfe0ff;
            font-weight: 700;
        }

        .chip {
            background: var(--chip);
            border: 1px solid var(--chipBorder);
            border-radius: 999px;
            padding: .25rem .55rem;
            color: #b8c2d6;
            font-size: .85rem;
        }

        .btn-outline-light {
            border-color: #3a4a69;
            color: #dbe3f7;
        }

        .btn-outline-light:hover {
            background: #1a2845;
            color: #fff;
        }

        .btn-success {
            background: var(--success);
            border-color: var(--success);
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
            color: #cfe0ff
        }

        .badge-on {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43
        }

        .badge-off {
            background: rgba(148, 163, 184, .18);
            border-color: #334155
        }

        .price-pill {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .25rem .65rem;
            font-weight: 700
        }

        .spec-chip {
            background: #13203a;
            border: 1px solid #2a3854;
            border-radius: 999px;
            padding: .22rem .6rem;
            color: #cfe0ff;
            font-size: .85rem
        }

        .slot-pill {
            background: #0e162b;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .25rem .6rem
        }

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
    {{-- Metrics --}}
    <div class="row g-3">
        <div class="col-lg-4" style="cursor:pointer;" onclick="window.location ='{{ route('patient.appointments.index') }}'">
            <div class="cardx h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="section-subtle">Pending Appointment</div>
                        <div class="metric">{{ $pendingAppointments }}</div>
                    </div>
                    <i class="fa-regular fa-calendar-days fs-2" style="color:#cbd5e1;"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4" style="cursor:pointer;"
            onclick="window.location ='{{ route('patient.prescriptions.index') }}'">
            <div class="cardx h-100">
                <div class="section-subtle">Active Prescriptions</div>
                <div class="metric">{{ $activeRxCount }}</div>
            </div>
        </div>

        <div class="col-lg-4" style="cursor:pointer;" onclick="window.location ='{{ route('patient.pharmacies') }}'">
            <div class="cardx h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="section-subtle">Nearby Pharmacies</div>
                        <div class="metric">{{ $nearbyCount }}</div>
                    </div>
                    <i class="fa-solid fa-location-dot fs-2" style="color:#86efac;"></i>
                </div>
            </div>
        </div>
    </div>



    <br><br>


    <div class="cardx mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-prescription"></i>
                <h5 class="m-0">My Prescriptions</h5>
            </div>

            <button id="btnRxRefresh" type="button" class="btn btn-outline-light btn-sm" style="display: none;">
                <span id="rxRefreshSpin" class="spinner-border spinner-border-sm me-1 d-none" role="status"
                    aria-hidden="true"></span>
                Refresh
            </button>
            <button class="btn btn-sm btn-gradient" type="button" data-bs-toggle="collapse"
                data-bs-target="#rxListCollapse" aria-expanded="true" aria-controls="rxListCollapse">
                Collapse &UpArrow;
            </button>
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

    <div class="collapse show" id="rxListCollapse">
        <div id="rxList">
            @include('patient.prescriptions._list', [
                'prescriptions' => $prescriptions,
                'acceptedAppt' => $acceptedAppt,
                'meet_remaining' => $meet_remaining,
                'meet_end_epoch' => $meet_end_epoch,
                'meet_now_epoch' => $meet_now_epoch,
            ])
        </div>
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

    <!-- Choose Pharmacy Modal -->
    <div class="modal fade" id="pharmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card); color:var(--text);">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-solid fa-store me-1"></i> Choose a Pharmacy</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-8">
                            <input id="pharmSearch" class="form-control" placeholder="Search pharmacy by name…">
                        </div>
                        <div class="col-md-4">
                            <select id="pharmFilter" class="form-select">
                                <option value="">All</option>
                                <option value="24_7">Open 24/7</option>
                                <option value="delivery">Has delivery</option> {{-- optional, if you track this --}}
                            </select>
                        </div>
                    </div>

                    <div id="pharmList" class="d-flex flex-column gap-2">
                        <div class="text-center text-secondary py-3">Searching…</div>
                    </div>

                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quote Review Modal -->
    <div class="modal fade" id="quoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card); color:var(--text);">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Quote Review</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="quoteBody">
                        <div class="text-center text-secondary py-3">Loading…</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto section-subtle">
                        <span id="quoteTotal" class="price-pill">₦0.00</span>
                    </div>
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="btnConfirmQuotedItems" disabled>Confirm
                        Items</button>
                </div>
            </div>
        </div>
    </div>









    <br><br>









    {{-- Find Doctors --}}
    <div class="cardx mt-3">
        <div class="section-title">
            <i class="fa-solid fa-magnifying-glass"></i>
            <h5 class="m-0">Find Doctors</h5>

        </div>
        <button class="btn btn-sm btn-gradient" style="float: right !important;" type="button"
            data-bs-toggle="collapse" data-bs-target="#doctorsSection" aria-expanded="true"
            aria-controls="doctorsSection">
            Collapse &UpArrow;
        </button>

        <div class="collapse show" id="doctorsSection">
            <div class="section-subtle">Search and start a consultation with available doctors </div>


            <div class="row g-2 align-items-center">
                <div class="col-lg-8">
                    <div class="search-wrap">
                        <i class="fa-solid fa-magnifying-glass icon"></i>
                        <input class="form-control" placeholder="Search doctors by name or title..." id="doctorSearch">
                    </div>

                </div>
                <div class="col-lg-2">
                    <select class="form-select" id="specialtySelect">
                        <option value="">All Specialties</option>
                        @foreach ($specialties as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-center justify-content-lg-end mt-2 mt-lg-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="onlyAvailable">
                    </div>
                    <label class="switch-label" for="onlyAvailable">Show available only</label>
                </div>
            </div>

            {{-- Specialties (chips) --}}
            <div class="spec-grid mt-3" id="specGrid">
                <div class="spec-tile active" data-spec="">
                    <i class="fa-regular fa-circle icon" style="color:#9aa3b2;"></i><span>All</span>
                </div>
                @foreach ($specialties as $s)
                    <div class="spec-tile" data-spec="{{ $s->id }}">
                        <i class="fa-solid {{ $s->icon }} icon"
                            style="color:{{ $s->color }};"></i><span>{{ $s->name }}</span>
                    </div>
                @endforeach
            </div>


        </div>
    </div>

    {{-- Doctor Quick View Modal --}}
    <div class="modal fade" id="doctorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background:#121a2c;border:1px solid var(--border);border-radius:18px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span id="docName">Doctor</span>
                        <small class="text-secondary d-block" id="docTitle"></small>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2 mb-2" id="docSpecs"></div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span id="docStatus" class="badge-soft badge-pending">Offline</span>
                        <span id="docFee" class="price-pill">₦0.00</span>
                        <span id="docDuration" class="badge-soft">—</span>
                    </div>

                    <div id="docBio" class="mb-3 text-secondary"></div>

                    <div>
                        <div class="fw-semibold mb-1">Next available slots</div>
                        <div id="docSlots" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>

                <div class="modal-footer border-0 d-flex justify-content-between">
                    <div class="text-secondary small">Tip: select a slot on the booking page.</div>
                    <div class="d-flex gap-2">
                        <a id="docBookBtn" href="#" class="btn btn-success"><i class="fa-solid fa-file me-1"></i>
                            Book Appointment</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Doctor list --}}
    <div class="mt-3 d-flex flex-column gap-3" id="doctorsList"></div>
    <div class="d-grid mt-3">
        <button class="btn btn-outline-light d-none" id="btnMore"><span class="btn-text">Load more</span></button>
    </div>




@endsection

@push('scripts')
    <script>
        function formatMoney(n) {
            if (n === null || n === undefined || n === '') return '—';
            const v = parseFloat(n);
            if (isNaN(v)) return '—';
            return '₦' + v.toFixed(2);
        }

        function openDoctorModal(id) {
            const $m = $('#doctorModal');
            // reset skeleton
            $m.find('#docName').text('Loading…');
            $m.find('#docTitle').text('');
            $m.find('#docSpecs').empty();
            $m.find('#docStatus').removeClass('badge-on badge-off').addClass('badge-off').text('Offline');
            $m.find('#docFee').text('—');
            $m.find('#docDuration').text('—');
            $m.find('#docBio').text('');
            $m.find('#docSlots').html('<span class="text-secondary">Loading…</span>');
            $m.find('#docBookBtn').attr('href', '#');

            $m.modal('show');

            $.get(`{{ route('patient.doctors.show', ['doctor' => '__ID__']) }}`.replace('__ID__', id))
                .done(function(d) {
                    const name = `${d.first_name} ${d.last_name}`;
                    $('#docName').text(name);
                    $('#docTitle').text(d.title || '');

                    // specialties
                    const $specs = $('#docSpecs').empty();
                    (d.specialties || []).forEach(s => $specs.append(`<span class="spec-chip">${s}</span>`));

                    // status, fee, duration
                    $('#docStatus')
                        .toggleClass('badge-on', !!d.available)
                        .toggleClass('badge-off', !d.available)
                        .text(d.available ? 'Online' : 'Offline');

                    $('#docFee').text(formatMoney(d.consult_fee));
                    $('#docDuration').text(d.avg_duration ? `${d.avg_duration} min` : '—');

                    // bio
                    $('#docBio').text(d.bio || '');

                    // slots
                    const $slots = $('#docSlots').empty();
                    if (d.next_slots && d.next_slots.length) {
                        d.next_slots.forEach(s => $slots.append(`<span class="slot-pill">${s.human}</span>`));
                    } else {
                        $slots.html(`<span class="text-secondary">No upcoming slots</span>`);
                    }

                    // actions
                    const pickedDate = (typeof $ !== 'undefined' && $('#doctorDate').length) ? ($('#doctorDate')
                        .val() || '') : '';
                    const bookUrl = pickedDate ?
                        `${d.appointment_url}${d.appointment_url.includes('?')?'&':'?'}date=${encodeURIComponent(pickedDate)}` :
                        d.appointment_url;
                    $('#docBookBtn').attr('href', bookUrl);
                })
                .fail(function() {
                    flash('danger', 'Failed to load doctor details');
                });
        }

        // delegate: “View” buttons or any clickable element with data-doc-view
        $(document).on('click', '[data-doc-view]', function(e) {
            e.preventDefault();
            const id = $(this).data('doc-view');
            openDoctorModal(id);
        });

        // (optional) make avatar/name clickable:
        $(document).on('click', '.doctor-row .avatar-sm, .doctor-row .fw-semibold', function() {
            const id = $(this).closest('.doctor-row').data('doc-id');
            if (id) openDoctorModal(id);
        });

        // expose globally if you want to call from inline onclick
        window.openDoctorModal = openDoctorModal;

        // --- Simple debounce without lodash
        function debounce(fn, wait) {
            let t;
            return function() {
                const ctx = this,
                    args = arguments;
                clearTimeout(t);
                t = setTimeout(() => fn.apply(ctx, args), wait);
            };
        }

        let nextUrl = null;
        const $list = $('#doctorsList');
        const $btnMore = $('#btnMore');

        function rowHtml(d) {
            const dot = d.available ? '#22c55e' : '#64748b';
            const specs = (d.specialties || []).join(', ');
            const next = d.next_slot_human ?
                `<span class="badge" style="border:1px solid var(--border); background:transparent; color:#cfe0ff;">Next: ${d.next_slot_human}</span>` :
                `<span class="text-secondary small">No upcoming slots</span>`;

            const bookDisabledClass = d.has_availability ? '' : 'disabled';
            const pickedDate = (typeof $ !== 'undefined' && $('#doctorDate').length) ? ($('#doctorDate').val() || '') : '';
            const appointmentUrl = pickedDate ?
                `${d.appointment_url}${d.appointment_url.includes('?') ? '&' : '?'}date=${encodeURIComponent(pickedDate)}` :
                d.appointment_url;

            return `
        <div class="doctor-row" data-doc-id="${d.id}">
            <div class="avatar-sm">${d.initials}</div>
            <div class="flex-grow-1">
                <div class="fw-semibold">${d.first_name} ${d.last_name}</div>
                <span class="chip text-success">
                    <span class="me-1" style="display:inline-block;width:8px;height:8px;background:${dot};border-radius:50%;"></span>
                    <b>Hospital : ${d.hospital ?? 'Not Under any hospital'}</b>
                </span>
                <span class="chip">
                    <span class="me-1" style="display:inline-block;width:8px;height:8px;background:${dot};border-radius:50%;"></span>
                    ${d.available ? 'Online' : 'Offline'}
                </span>
                ${specs ? `<span class="chip ms-2">${specs}</span>` : ''}
                <div class="mt-2">${next}</div>
                <div class="mt-2">Charges: ${d.charges}</div>
                <div class="mt-2">Duration: ${d.duration} mins</div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-gradient" data-doc-view="${d.id}">
                    <i class="fa-regular fa-eye me-1"></i> View
                </button>
                <a href="${appointmentUrl}" class="btn btn-success ${bookDisabledClass}">
                    <i class="fa-solid fa-file me-1"></i> Book Appointment
                </a>
            </div>
        </div>`;
        }

        function renderDoctors(res, replace = true) {
            nextUrl = res.next_page_url;
            if (replace) $list.empty();
            if (res.data.length === 0 && replace) {
                $list.html(`<div class="text-center text-secondary py-4">No doctors found.</div>`);
            } else {
                res.data.forEach(d => $list.append(rowHtml(d)));
            }
            $btnMore.toggleClass('d-none', !nextUrl);
        }

        function fetchDoctors(replace = true) {
            const q = $('#doctorSearch').val() || '';
            const specialty_id = $('#specialtySelect').val() || ($('#specGrid .spec-tile.active').data('spec') || '');
            const available = $('#onlyAvailable').is(':checked') ? 1 : 0;
            const date = $('#doctorDate').val() || '';

            $.get(`{{ route('patient.doctors.index') }}`, {
                    q,
                    specialty_id,
                    available,
                    date
                })
                .done(res => renderDoctors(res, replace))
                .fail(() => flash('danger', 'Failed to load doctors'));
        }

        // Events
        $('#doctorSearch').on('input', debounce(() => fetchDoctors(true), 300));
        $('#specialtySelect').on('change', () => fetchDoctors(true));
        $('#onlyAvailable').on('change', () => fetchDoctors(true));

        $(document).on('click', '#specGrid .spec-tile', function() {
            $('#specGrid .spec-tile').removeClass('active');
            $(this).addClass('active');
            const id = $(this).data('spec') || '';
            $('#specialtySelect').val(id);
            fetchDoctors(true);
        });

        $btnMore.on('click', function() {
            if (!nextUrl) return;
            const $btn = $(this);
            lockBtn($btn);
            $.get(nextUrl)
                .done(res => renderDoctors(res, false))
                .always(() => unlockBtn($btn));
        });

        // Initial load
        fetchDoctors(true);

        // Optional: capture geolocation once to improve Nearby Pharmacies metric next time
        @if (($nearbyCount ?? 0) === 0)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(pos => {
                    $.post(`{{ route('patient.location.update') }}`, {
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    });
                });
            }
        @endif

        // (REMOVED the old "auto-open if modal exists" here – handled in Rx block after AJAX)

        // Copy link
        const copyBtn = document.getElementById('apCopyLink');
        const linkInput = document.getElementById('apModalLink');
        const copyNote = document.getElementById('apCopyNote');
        if (copyBtn && linkInput) {
            copyBtn.addEventListener('click', function() {
                const val = linkInput.value || '';
                if (!val) return;
                navigator.clipboard.writeText(val).then(() => {
                    copyNote.style.display = 'block';
                    setTimeout(() => (copyNote.style.display = 'none'), 1200);
                });
            });
        }
    </script>

    <script>
        // Remove any old direct bindings to avoid duplicates
        $(document).off('click', '#endAppointment');

        // Delegated handler: works even when the button is injected via AJAX
        $(document).on('click', '#endAppointment', function() {
            const $btn = $(this);
            const id = $btn.data('apt-id');
            if (!id) return;

            if (typeof lockBtn === 'function') lockBtn($btn);

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                '{{ csrf_token() }}';

            $.post(`{{ url('patient/appointments/close') }}/${id}`, {
                    _token: token
                })
                .done(res => {
                    if (typeof flash === 'function') flash('success', res.message || 'Appointment closed');
                    const modalEl = document.getElementById('apAcceptedModal');
                    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    try {
                        typeof fetchList === 'function' && fetchList();
                    } catch (_) {}
                })
                .fail(xhr => {
                    const msg = xhr?.responseJSON?.message || 'Failed to close appointment';
                    if (typeof flash === 'function') flash('danger', msg);
                })
                .always(() => {
                    if (typeof unlockBtn === 'function') unlockBtn($btn);
                });
        });
    </script>
@endpush



@push('scripts')
    <script>
        (function() {
            const $list = $('#rxList');
            let currentOrderIdForQuote = null;
            let currentRxIdForQuote = null;
            let t = null;
            const $btnRefresh = $('#btnRxRefresh');
            const $spin = $('#rxRefreshSpin');

            // === SMART REFRESH STATE ===
            let lastRxEtag = null; // for HTTP 304
            let lastRxSig = null; // for DOM hash fallback

            // === MODAL STATE ===
            let rxModalOpen = false; // whether the Accepted modal is currently open
            let lastShownApptId = null; // remember which appointment we already auto-opened

            // Tiny fast hash (djb2 variant)
            function djb2Hash(str) {
                let hash = 5381,
                    i = str.length;
                while (i) hash = ((hash << 5) + hash) ^ str.charCodeAt(--i);
                return (hash >>> 0).toString(36);
            }

            function getCurrentModalApptId() {
                const endBtn = document.querySelector('#apAcceptedModal #endAppointment');
                const id = endBtn ? Number(endBtn.getAttribute('data-apt-id')) : null;
                return Number.isFinite(id) ? id : null;
            }

            function maybeAutoOpenModal() {
                const modalEl = document.getElementById('apAcceptedModal');
                if (!modalEl || rxModalOpen) return;
                const apptId = getCurrentModalApptId();
                if (apptId && apptId !== lastShownApptId) {
                    const bs = bootstrap.Modal.getOrCreateInstance(modalEl);
                    bs.show();
                    // lastShownApptId will be set in shown handler for consistency
                }
            }

            // Only render when HTML differs. Also, never replace DOM while modal is open.
            function renderIfChanged(html) {
                if (rxModalOpen) return false;
                const incomingSig = djb2Hash(html);
                if (incomingSig === lastRxSig) return false;
                lastRxSig = incomingSig;
                $list.html(html);

                // After fresh HTML lands, if a modal exists, auto-open it (once per appt)
                maybeAutoOpenModal();
                return true;
            }

            // --- FETCH + REFRESH with ETag support ---
            function fetchList(pageUrl = null) {
                const q = $('#rxSearch').val() || '';
                const status = $('#rxStatus').val() || '';
                const base = pageUrl || `{{ route('patient.prescriptions.index') }}`;
                const url = base + (base.includes('?') ? '&' : '?') + 'from=dashboard';

                return $.ajax({
                    url,
                    method: 'GET',
                    data: {
                        q,
                        status
                    },
                    headers: lastRxEtag ? {
                        'If-None-Match': lastRxEtag
                    } : {},
                    success: function(html, _textStatus, jqXHR) {
                        const etag = jqXHR.getResponseHeader('ETag');
                        if (etag) lastRxEtag = etag;
                        if (html) renderIfChanged(html);
                    },
                    statusCode: {
                        304: function() {
                            /* Not Modified — no DOM changes */ }
                    }
                });
            }

            function refreshRxList(pageUrl = null) {
                $btnRefresh.prop('disabled', true);
                $spin.removeClass('d-none');
                return fetchList(pageUrl).always(function() {
                    $btnRefresh.prop('disabled', false);
                    $spin.addClass('d-none');

                    // Safety after every refresh: if a modal exists but isn't shown yet, open it
                    maybeAutoOpenModal();

                    // If no modal is visible but a backdrop remains, clean it
                    if (!$('.modal.show').length && $('.modal-backdrop').length) {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                    }
                });
            }

            // Seed initial signature to avoid first-poll thrash; also auto-open on initial page load if present
            (function seedInitialSig() {
                lastRxSig = djb2Hash($list.html() || '');
                // Initial check for a server-rendered modal at first page render:
                maybeAutoOpenModal();
            })();

            // Search + filters (fixed: no immediate invocation)
            $('#rxSearch').on('input', function() {
                clearTimeout(t);
                t = setTimeout(() => refreshRxList(), 300);
            });
            $('#rxStatus').on('change', refreshRxList);

            $btnRefresh.on('click', function() {
                refreshRxList();
            });

            // AJAX pagination inside #rxList (preserves smart refresh + ETag)
            $(document).on('click', '#rxList .pagination a', function(e) {
                e.preventDefault();
                const url = this.href;
                if (!url) return;
                refreshRxList(url);
            });

            // ====== View modal (unchanged) ======
            $(document).on('click', '[data-rx-view]', function() {
                const payload = $(this).data('rx-view');
                const rx = typeof payload === 'string' ? JSON.parse(payload) : payload;

                let itemsHtml = rx.items.map(i => {
                    const bought = i.status === 'purchased';
                    const badge = i.status ?
                        `<span class="badge-soft ms-1">${i.status.replaceAll('_',' ')}</span>` : '';
                    const price = (i.line_total ?? i.unit_price) ?
                        `<span class="price-pill ms-2">₦${Number(i.line_total ?? i.unit_price).toFixed(2)}</span>` :
                        '';
                    return `<li class="${bought ? 'opacity-50' : ''}">
                ${i.drug}${i.dose?` • ${i.dose}`:''}${i.frequency?` • ${i.frequency}`:''}${i.days?` • ${i.days}`:''}${i.directions?` — ${i.directions}`:''}
                ${badge}${price}
            </li>`;
                }).join('');

                let html = `
            <div class="mb-2">
                <span class="rx-badge">Rx ${rx.code}</span>
                <span class="rx-status ms-2">${(rx.dispense || rx.status || '').toString().replaceAll('_',' ')}</span>
            </div>
            <div class="mb-2"><strong>Doctor:</strong> ${rx.doctor}</div>
            ${rx.notes ? `<div class="mb-3"><strong>Notes:</strong> ${rx.notes}</div>` : ``}
            <div class="mb-2"><strong>Items</strong></div>
            <ul class="mb-0">${itemsHtml}</ul>
        `;
                $('#rxViewBody').html(html);
                new bootstrap.Modal(document.getElementById('rxViewModal')).show();
            });

            // ====== Buy flow (unchanged) ======
            let currentRxId = null;
            const $pharmModal = $('#pharmModal');
            const $pharmList = $('#pharmList');
            const $pharmSearch = $('#pharmSearch');
            const $pharmFilter = $('#pharmFilter');
            let searchTimer = null;

            $(document).on('click', '[data-rx-buy]', function() {
                currentRxId = $(this).data('rx-buy');
                if (!currentRxId) return;
                $pharmModal.modal('show');
                loadPharmacies();
            });

            $pharmSearch.on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadPharmacies, 300);
            });
            $pharmFilter.on('change', loadPharmacies);

            function loadPharmacies() {
                const q = $pharmSearch.val() || '';
                const filter = $pharmFilter.val() || '';
                $pharmList.html(`<div class="text-center text-secondary py-3">Loading…</div>`);
                $.get(`{{ route('patient.prescriptions.pharmacies', ['rx' => '__ID__']) }}`.replace('__ID__',
                        currentRxId), {
                        q,
                        filter
                    })
                    .done(html => $pharmList.html(html))
                    .fail(() => $pharmList.html(
                        `<div class="text-center text-danger py-3">Failed to load pharmacies</div>`));
            }

            $(document).on('click', '[data-pharm-select]', function() {
                const pharmId = $(this).data('pharm-select');
                const $btn = $(this);
                if (!pharmId || !currentRxId) return;

                lockBtn($btn);
                $.post(`{{ route('patient.prescriptions.assignPharmacy', ['rx' => '__ID__']) }}`.replace(
                        '__ID__', currentRxId), {
                        _token: `{{ csrf_token() }}`,
                        pharmacy_id: pharmId
                    })
                    .done(res => {
                        $pharmModal.modal('hide');

                        if (res && res.quote) {
                            currentOrderIdForQuote = res.order_id;
                            currentRxIdForQuote = currentRxId;
                            renderQuoteModal(res.order_id, res.quote);
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('quoteModal'))
                            .show();
                        } else {
                            if (typeof flash === 'function') flash('success', res.message ||
                                'Pharmacy selected');
                            try {
                                (typeof fetchList === 'function') && fetchList();
                            } catch (e) {}
                        }
                    })
                    .fail(xhr => {
                        if (typeof flash === 'function') flash('danger', xhr.responseJSON?.message ||
                            'Failed to assign pharmacy');
                    })
                    .always(() => unlockBtn($btn));
            });

            $(document).on('click', '[data-dsp-confirm-order]', function() {
                const orderId = $(this).data('dsp-confirm-order');
                const $btn = $(this);
                lockBtn($btn);

                $.post(`{{ route('patient.orders.confirmDeliveryFee', ['order' => '__OID__']) }}`.replace(
                        '__OID__', orderId), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        if (typeof flash === 'function') flash('success', res.message ||
                            'Delivery fee confirmed');
                        location.reload();
                    })
                    .fail(err => {
                        if (typeof flash === 'function') flash('danger', err.responseJSON?.message ||
                            'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });

            function renderQuoteModal(orderId, q) {
                currentOrderIdForQuote = orderId;

                const available = (q.available || []).map(a => {
                    const unit = Number(a.unit_price || 0);
                    const line = Number(a.line_total || unit);
                    return `
            <tr>
                <td>${escapeHtml(a.drug || '')}</td>
                <td class="text-end">₦${unit.toFixed(2)}</td>
                <td class="text-end">₦${line.toFixed(2)}</td>
            </tr>`;
                }).join('');

                const unavailable = (q.unavailable || []).map(u => `
            <li>${escapeHtml(u.drug || '')}${u.reason ? ` — <span class="section-subtle">${escapeHtml(u.reason)}</span>` : ''}</li>
        `).join('');

                const table = available ?
                    `
                <div class="mb-2 fw-semibold">Available items</div>
                <div class="table-responsive">
                    <table class="table table-borderless table-darkish align-middle mb-3">
                        <thead>
                        <tr>
                            <th>Drug</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                        </thead>
                        <tbody>${available}</tbody>
                    </table>
                </div>
            ` :
                    `<div class="text-warning mb-2">No matching items found in this pharmacy inventory.</div>`;

                const unvBlock = unavailable ?
                    `
                <div class="mb-2 fw-semibold">Unavailable here</div>
                <ul class="mb-0 small">${unavailable}</ul>
            ` :
                    '';

                $('#quoteBody').html(`${table}${unvBlock}`);
                const total = Number(q.items_total || 0);
                $('#quoteTotal').text(`₦${total.toFixed(2)}`);
                $('#btnConfirmQuotedItems').prop('disabled', total <= 0);
                $('#quoteBody').append(`
            <div class="small section-subtle">
                Items: ₦${Number(q.items_total).toFixed(2)} ·
                Delivery: ₦${Number(q.delivery_fee).toFixed(2)} (${q.distance_km} km @ ₦100/km)
            </div>
        `);
            }

            $(document)
                .off('click.quote', '#btnConfirmQuotedItems')
                .on('click.quote', '#btnConfirmQuotedItems', function() {
                    const $btn = $(this);
                    if (!currentRxIdForQuote) return;

                    lockBtn($btn);
                    $.post(`{{ route('patient.prescriptions.confirmPrice', ['rx' => '__RX__']) }}`.replace(
                            '__RX__', currentRxIdForQuote), {
                            _token: `{{ csrf_token() }}`
                        })
                        .done(res => {
                            if (typeof flash === 'function') flash('success', res.message || 'Items confirmed');
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('quoteModal')).hide();
                            try {
                                (typeof fetchList === 'function') && fetchList();
                            } catch (e) {}
                        })
                        .fail(xhr => {
                            if (typeof flash === 'function') flash('danger', xhr.responseJSON?.message ||
                                'Failed to confirm');
                        })
                        .always(() => unlockBtn($btn));
                });

            function escapeHtml(s) {
                return String(s || '')
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            }

            // --- Auto-poll loop (modal-aware) ---
            const POLL_MS = 3000;
            const IDLE_GRACE_MS = 1200;

            let inFlight = false;
            let nextTimer = null;
            let lastUserActivityAt = Date.now();

            function startAutoPolling() {
                if (nextTimer) return;
                scheduleNext();
            }

            function stopAutoPolling() {
                if (nextTimer) {
                    clearTimeout(nextTimer);
                    nextTimer = null;
                }
            }

            function scheduleNext() {
                stopAutoPolling();
                nextTimer = setTimeout(tick, POLL_MS);
            }

            function idleEnough() {
                return (Date.now() - lastUserActivityAt) >= IDLE_GRACE_MS;
            }

            function tick() {
                if (document.hidden || !idleEnough() || inFlight || rxModalOpen) {
                    return scheduleNext();
                }
                inFlight = true;
                refreshRxList().always(() => {
                    inFlight = false;
                    scheduleNext();
                });
            }

            function bumpIdle() {
                lastUserActivityAt = Date.now();
            }

            $(document).on('input', '#rxSearch', bumpIdle);
            $(document).on('change', '#rxStatus', bumpIdle);
            $(document).on('input change', '#rxList :input', bumpIdle);

            // Pause polling while modal is shown (prevents DOM replacement under it)
            $(document)
                .off('shown.bs.modal.apAccepted hidden.bs.modal.apAccepted')
                .on('shown.bs.modal.apAccepted', '#apAcceptedModal', function() {
                    rxModalOpen = true;
                    const apptId = getCurrentModalApptId();
                    if (apptId) lastShownApptId = apptId;
                    // (Re)init countdown if needed
                    if (typeof initMeetingCountdown === 'function') {
                        try {
                            initMeetingCountdown();
                        } catch (_) {}
                    }
                    stopAutoPolling();
                })
                .on('hidden.bs.modal.apAccepted', '#apAcceptedModal', function() {
                    rxModalOpen = false;
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                    }, 50);
                    scheduleNext(); // resume polling soon
                });

            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    stopAutoPolling();
                } else {
                    bumpIdle();
                    scheduleNext();
                }
            });

            $(document).on('click', '#btnRxRefresh', function() {
                bumpIdle();
            });

            // Kick it off
            startAutoPolling();
        })();
    </script>

    <script>
        (function() {
            function initMeetingCountdown() {
                const meta = document.getElementById('meetingCountdownMeta');
                const label = document.getElementById('meetingCountdown');
                const join = document.getElementById('apJoinNow');
                const endBtn = document.getElementById('endAppointment');

                if (!meta || !label) return;

                const endEpoch = Number(meta.getAttribute('data-end-epoch')) || 0;
                const nowEpoch = Number(meta.getAttribute('data-now-epoch')) || 0;
                if (!endEpoch || !nowEpoch) return;

                const clientNow = Math.floor(Date.now() / 1000);
                const skew = nowEpoch - clientNow;

                function endUI() {
                    label.textContent = '00:00';
                    if (join) {
                        join.classList.add('disabled');
                        join.setAttribute('aria-disabled', 'true');
                        join.setAttribute('tabindex', '-1');
                        join.textContent = 'Meeting ended';
                        join.removeAttribute('href');
                    }
                    if (endBtn) {
                        endBtn.disabled = false;
                        endBtn.classList.remove('disabled');
                    }
                }

                function fmt(sec) {
                    const m = Math.floor(sec / 60),
                        s = sec % 60;
                    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                }

                function tick() {
                    const now = Math.floor(Date.now() / 1000) + skew;
                    const remaining = Math.max(0, endEpoch - now);
                    label.textContent = fmt(remaining);
                    if (remaining <= 0) {
                        endUI();
                        clearInterval(timer);
                    }
                }

                tick();
                const timer = setInterval(tick, 1000);

                const modal = document.getElementById('apAcceptedModal');
                modal && modal.addEventListener('hidden.bs.modal', () => clearInterval(timer), {
                    once: true
                });
            }

            // Make the init function available globally (used in modal shown handler)
            window.initMeetingCountdown = initMeetingCountdown;

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMeetingCountdown);
            } else {
                initMeetingCountdown();
            }

            const modal = document.getElementById('apAcceptedModal');
            modal && modal.addEventListener('shown.bs.modal', initMeetingCountdown);
        })();
    </script>
@endpush
