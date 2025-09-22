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
    </style>
@endpush

@section('content')
    {{-- Metrics --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="cardx h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="section-subtle">Next Appointment</div>
                        <div class="mt-2">
                            @if ($nextAppt)
                                {{ $nextAppt->scheduled_at->format('D, M j · g:i A') }}
                            @else
                                No upcoming appointments.
                            @endif
                        </div>
                    </div>
                    <i class="fa-regular fa-calendar-days fs-2" style="color:#cbd5e1;"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cardx h-100">
                <div class="section-subtle">Active Prescriptions</div>
                <div class="metric">{{ $activeRxCount }}</div>
            </div>
        </div>

        <div class="col-lg-4">
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

    {{-- Find Doctors --}}
    <div class="cardx mt-3">
        <div class="section-title">
            <i class="fa-solid fa-magnifying-glass"></i>
            <h5 class="m-0">Find Doctors</h5>
        </div>
        <div class="section-subtle">Search and start a consultation with available doctors</div>

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
                        <span id="docFee" class="price-pill">$0.00</span>
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
                        <a id="docChatBtn" href="#" class="btn btn-outline-light"><i
                                class="fa-regular fa-message me-1"></i> Chat</a>
                        <a id="docBookBtn" href="#" class="btn btn-success"><i class="fa-solid fa-video me-1"></i>
                            Book Video</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
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
        </style>
    @endpush


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
            return '$' + v.toFixed(2);
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
            $m.find('#docChatBtn,#docBookBtn').attr('href', '#');

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
                    // forward currently picked date if present
                    const pickedDate = (typeof $ !== 'undefined' && $('#doctorDate').length) ? ($('#doctorDate')
                        .val() || '') : '';
                    const bookUrl = pickedDate ?
                        `${d.appointment_url}${d.appointment_url.includes('?')?'&':'?'}date=${encodeURIComponent(pickedDate)}` :
                        d.appointment_url;
                    $('#docBookBtn').attr('href', bookUrl);
                    $('#docChatBtn').attr('href', d.chat_url);
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
                `<span class="badge" style="border:1px solid var(--border); background:transparent; color:#cfe0ff;">
                    Next: ${d.next_slot_human}
                </span>` :
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
                <span class="chip">
                <span class="me-1" style="display:inline-block;width:8px;height:8px;background:${dot};border-radius:50%;"></span>
                ${d.available ? 'Online' : 'Offline'}
                </span>
                ${specs ? `<span class="chip ms-2">${specs}</span>` : ''}
                <div class="mt-2">${next}</div>
                <div class="mt-2">Charges: $${d.charges}</div>
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
            const date = $('#doctorDate').val() || ''; // NEW

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
            // sync dropdown with chip (and clear dropdown when "All")
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
    </script>
@endpush
