@extends('layouts.patient')
@section('title', 'Dashboard')

@push('styles')
    <style>
        /* Theme (keeps consistent with your auth + layout) */
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

        /* Cards / sections */
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

        /* Section header */
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

        /* Inputs */
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

        /* Search with icon */
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

        /* Toggle label */
        .switch-label {
            color: var(--muted);
            margin-left: .35rem;
        }

        /* Specialty tiles */
        .spec-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 16px;
        }

        @media (max-width: 1200px) {
            .spec-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 576px) {
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

        /* Doctor rows */
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
                        <div class="mt-1">No upcoming appointments.</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:44px;height:44px;background:#0b1222;border:1px solid var(--border);">
                        <i class="fa-regular fa-calendar-days fs-5" style="color:#cbd5e1;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cardx h-100">
                <div class="section-subtle">Active Prescriptions</div>
                <div class="metric">0</div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cardx h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="section-subtle">Nearby Pharmacies</div>
                        <div class="metric">1</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width:44px;height:44px;background:#0b1222;border:1px solid var(--border);">
                        <i class="fa-solid fa-location-dot fs-5" style="color:#86efac;"></i>
                    </div>
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
                    <input class="form-control" placeholder="Search doctors by name..." id="doctorSearch">
                </div>
            </div>
            <div class="col-lg-2">
                <select class="form-select" id="specialtySelect">
                    <option>All Specialties</option>
                    <option>Cardiology</option>
                    <option>Dermatology</option>
                    <option>Neurology</option>
                    <option>Orthopedics</option>
                    <option>Pediatrics</option>
                    <option>Psychiatry</option>
                    <option>General Medicine</option>
                </select>
            </div>
            <div class="col-lg-2 d-flex align-items-center justify-content-lg-end mt-2 mt-lg-0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="onlyAvailable">
                </div>
                <label class="switch-label" for="onlyAvailable">Show available only</label>
            </div>
        </div>

        {{-- Specialties --}}
        <div class="spec-grid mt-3">
            <div class="spec-tile" data-spec="Cardiology">
                <i class="fa-regular fa-heart icon" style="color:#f472b6;"></i><span>Cardiology</span>
            </div>
            <div class="spec-tile" data-spec="Dermatology">
                <i class="fa-regular fa-sun icon" style="color:#fbbf24;"></i><span>Dermatology</span>
            </div>
            <div class="spec-tile" data-spec="Neurology">
                <i class="fa-solid fa-brain icon" style="color:#a78bfa;"></i><span>Neurology</span>
            </div>
            <div class="spec-tile" data-spec="Orthopedics">
                <i class="fa-solid fa-bone icon" style="color:#93c5fd;"></i><span>Orthopedics</span>
            </div>
            <div class="spec-tile" data-spec="Pediatrics">
                <i class="fa-solid fa-baby icon" style="color:#fb7185;"></i><span>Pediatrics</span>
            </div>
            <div class="spec-tile" data-spec="Psychiatry">
                <i class="fa-solid fa-user icon" style="color:#a0aec0;"></i><span>Psychiatry</span>
            </div>
            <div class="spec-tile" data-spec="General">
                <i class="fa-solid fa-stethoscope icon" style="color:#60a5fa;"></i><span>General Medicine</span>
            </div>
        </div>
    </div>

    {{-- Doctor list --}}
    <div class="mt-3 d-flex flex-column gap-3">
        @foreach ([['initials' => 'DJ', 'name' => 'Don Joe', 'status' => 'Offline'], ['initials' => 'KS', 'name' => 'Kuyik Swiss', 'status' => 'Offline']] as $doc)
            <div class="doctor-row">
                <div class="avatar-sm">{{ $doc['initials'] }}</div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $doc['name'] }}</div>
                    <span class="chip">
                        <span class="me-1"
                            style="display:inline-block;width:8px;height:8px;background:#64748b;border-radius:50%;"></span>
                        {{ $doc['status'] }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light"><i class="fa-regular fa-message me-1"></i> Chat</button>
                    <button class="btn btn-success"><i class="fa-solid fa-video me-1"></i> Video Call</button>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        // Specialty tile selection (visual only for now)
        $('.spec-tile').on('click', function() {
            $('.spec-tile').removeClass('active');
            $(this).addClass('active');
            // TODO: trigger filter by $(this).data('spec')
        });
    </script>
@endpush
