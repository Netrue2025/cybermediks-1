@extends('layouts.doctor')
@section('title', 'Profile Settings')

@push('styles')
    <style>
        .section-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            position: relative;
        }

        .label-sub {
            color: var(--muted);
            font-size: .9rem;
        }

        .status-chip {
            background: var(--chip);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .2rem .6rem;
        }

        .verified {
            color: #22c55e;
        }

        .unverified {
            color: #f59e0b;
        }

        .edit-btn {
            position: absolute;
            top: 16px;
            right: 16px;
            font-size: .85rem;
        }

        /* Override Bootstrap disabled input styles */
        .form-control:disabled,
        .form-select:disabled {
            background-color: var(--field) !important;
            /* same as your normal input bg */
            border-color: var(--field-border) !important;
            color: var(--text) !important;
            /* keep text readable */
            opacity: 0.6;
            /* optional: subtle dimming */
            cursor: not-allowed;
            /* keep the disabled cursor */
        }

        /* pill tabs */
        .tabs-pill .nav-link {
            border: 1px solid var(--border);
            background: #0e162b;
            color: #cfe0ff;
            border-radius: 999px;
            padding: .45rem .9rem;
        }

        .tabs-pill .nav-link.active {
            background: #13203a;
            border-color: #2a3854;
            color: #fff;
        }

        .tab-pane .section-card,
        .tab-pane .cardx,
        .tab-pane .cred-card {
            background: transparent;
            border: 0;
            padding: 0;
        }

        .tab-pane .ps-row {
            margin-bottom: .75rem;
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
    {{-- LEFT COLUMN with Tabs --}}
    <div class="col-12 col-lg-12">
        <div class="cardx">

            {{-- Tabs header --}}
            <div class="d-flex justify-content-center">
                <ul class="nav nav-tabs tabs-pill border-0" role="tablist" style="gap:.4rem;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-profile" data-bs-toggle="tab" data-bs-target="#pane-profile"
                            type="button" role="tab">
                            <i class="fa-solid fa-user me-1"></i> Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-doctor" data-bs-toggle="tab" data-bs-target="#pane-doctor"
                            type="button" role="tab">
                            <i class="fa-solid fa-id-card-clip me-1"></i> Doctor Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-creds" data-bs-toggle="tab" data-bs-target="#pane-creds"
                            type="button" role="tab">
                            <i class="fa-solid fa-badge-check me-1"></i> Credentials
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-billing" data-bs-toggle="tab" data-bs-target="#pane-billing"
                            type="button" role="tab">
                            <i class="fa-solid fa-wallet me-1"></i> Billing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-security" data-bs-toggle="tab" data-bs-target="#pane-security"
                            type="button" role="tab">
                            <i class="fa-solid fa-shield-halved me-1"></i> Security
                        </button>
                    </li>

                </ul>
            </div>

            <div class="tab-content pt-3">

                {{-- TAB 1: Profile --}}
                <div class="tab-pane fade show active" id="pane-profile" role="tabpanel" aria-labelledby="tab-profile">
                    <div class="section-card" id="profileCard">
                        <button type="button" class="btn btn-sm btn-outline-light edit-btn" id="editProfileBtn">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </button>

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-user"></i>
                            <h5 class="m-0">Profile Information</h5>
                        </div>
                        <div class="label-sub mb-3">Update your personal details</div>

                        {{-- original form preserved --}}
                        <form id="profileForm" autocomplete="off">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input class="form-control" name="first_name"
                                        value="{{ old('first_name', auth()->user()->first_name) }}" required disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input class="form-control" name="last_name"
                                        value="{{ old('last_name', auth()->user()->last_name) }}" disabled>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <input class="form-control" value="{{ auth()->user()->email }}" disabled>
                                        @if (!auth()->user()->email_verified_at)
                                            <a class="btn btn-outline-light"
                                                href="{{ route('verify.show') }}">Verify</a>
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        @if (auth()->user()->email_verified_at)
                                            <span class="status-chip verified"><i class="fa-solid fa-circle-check me-1"></i>
                                                Verified</span>
                                        @else
                                            <span class="status-chip unverified"><i class="fa-regular fa-circle me-1"></i>
                                                Not verified</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input class="form-control" name="phone"
                                        value="{{ old('phone', auth()->user()->phone ?? '') }}" disabled>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    @php $g = auth()->user()->gender ?? ''; @endphp
                                    <select class="form-select" name="gender" disabled>
                                        <option value="">Select</option>
                                        <option value="male" {{ $g === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $g === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ $g === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob"
                                        value="{{ old('dob', optional(auth()->user()->dob ?? null)->format('Y-m-d')) }}"
                                        disabled>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Country</label>
                                    <input class="form-control" name="country"
                                        value="{{ old('country', auth()->user()->country ?? '') }}" disabled>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input class="form-control" name="address"
                                        value="{{ old('address', auth()->user()->address ?? '') }}" disabled>
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button class="btn btn-gradient" id="btnSaveProfile" disabled>
                                        <span class="btn-text">Save changes</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- TAB 2: Doctor Profile --}}
                <div class="tab-pane fade" id="pane-doctor" role="tabpanel" aria-labelledby="tab-doctor">
                    @push('styles')
                        <style>
                            .hdr-bar {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                gap: 10px;
                                background: #0e162b;
                                border: 1px solid var(--border);
                                border-radius: 12px;
                                padding: 12px 14px;
                                margin-bottom: 12px
                            }

                            .hdr-title {
                                display: flex;
                                align-items: center;
                                gap: .6rem
                            }

                            .hdr-title .pill-ghost {
                                width: 38px;
                                height: 38px;
                                border-radius: 999px;
                                background: #0b1222;
                                border: 1px solid var(--border);
                                display: flex;
                                align-items: center;
                                justify-content: center
                            }

                            .tile {
                                background: #0f1a2e;
                                border: 1px solid var(--border);
                                border-radius: 12px;
                                padding: 14px
                            }

                            .tile+.tile {
                                margin-top: 12px
                            }

                            .tile-head {
                                font-weight: 700;
                                margin-bottom: 8px;
                                display: flex;
                                align-items: center;
                                gap: .5rem
                            }

                            .mini {
                                color: #9aa3b2;
                                font-size: .9rem
                            }

                            .badge-soft {
                                display: inline-flex;
                                align-items: center;
                                gap: .35rem;
                                padding: .22rem .6rem;
                                border-radius: 999px;
                                border: 1px solid var(--border);
                                background: #0e162b;
                                font-size: .85rem;
                                color: #cfe0ff
                            }

                            .badge-on {
                                background: rgba(34, 197, 94, .10);
                                border-color: #1f6f43
                            }

                            .badge-off {
                                background: rgba(239, 68, 68, .10);
                                border-color: #6f2b2b
                            }

                            .input-icon {
                                position: relative
                            }

                            .input-icon .input-icon-prefix {
                                position: absolute;
                                left: .6rem;
                                top: 50%;
                                transform: translateY(-50%);
                                color: #9aa3b2
                            }

                            .input-icon .input-icon-suffix {
                                position: absolute;
                                right: .6rem;
                                top: 50%;
                                transform: translateY(-50%);
                                color: #9aa3b2
                            }

                            .input-icon .form-control {
                                padding-left: 1.6rem;
                                padding-right: 2.2rem
                            }

                            /* specialties */
                            .chips-wrap {
                                display: flex;
                                flex-wrap: wrap;
                                gap: .4rem;
                                min-height: 38px;
                                padding: .35rem;
                                background: #0d162a;
                                border: 1px solid var(--border);
                                border-radius: 10px
                            }

                            .chip2 {
                                display: inline-flex;
                                align-items: center;
                                gap: .4rem;
                                background: #13203a;
                                border: 1px solid #2a3854;
                                border-radius: 999px;
                                padding: .28rem .6rem;
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
                                border-radius: 10px;
                                max-height: 240px;
                                overflow: auto;
                                box-shadow: 0 8px 24px rgba(0, 0, 0, .35)
                            }

                            .spec-results .item {
                                padding: .5rem .6rem;
                                border-bottom: 1px solid var(--border);
                                cursor: pointer
                            }

                            .spec-results .item:hover {
                                background: #111f37
                            }

                            /* sticky save bar */
                            .savebar {
                                position: sticky;
                                bottom: 0;
                                margin-top: 12px;
                                background: linear-gradient(180deg, rgba(15, 22, 40, 0) 0%, #0f1628 35%);
                                padding-top: 10px
                            }

                            .savebar .inner {
                                background: #0f1a2e;
                                border: 1px solid var(--border);
                                border-radius: 12px;
                                padding: 10px;
                                display: flex;
                                justify-content: flex-end
                            }
                        </style>
                    @endpush

                    <!-- Header -->
                    <div class="hdr-bar">
                        <div class="hdr-title">
                            <div class="pill-ghost"><i class="fa-solid fa-id-card-clip"></i></div>
                            <div>
                                <h5 class="fw-bold m-0">Doctor Profile</h5>
                                <div class="mini">Manage your public profile, specialties, and availability</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- LEFT: Availability & Quick Edit --}}
                        <div class="col-md-6 d-flex flex-column">
                            <div class="tile">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span
                                            class="badge-soft {{ $profile?->is_available ?? false ? 'badge-on' : 'badge-off' }}"
                                            id="availBadge">
                                            <i
                                                class="fa-solid {{ $profile?->is_available ?? false ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                                            <span
                                                class="availableText">{{ $profile?->is_available ? 'Available' : 'Unavailable' }}</span>
                                        </span>
                                        <div class="mini mt-1">Patients can only book you when available.</div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="availSwitch"
                                            {{ $profile?->is_available ?? false ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <div class="tile">
                                <div class="tile-head"><i class="fa-solid fa-sliders"></i> Quick Details</div>
                                <div class="mb-3">
                                    <label class="form-label small subtle mb-1">Title</label>
                                    <input id="qTitle" class="form-control"
                                        placeholder="e.g., Consultant Cardiologist" value="{{ $profile?->title }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small subtle mb-1">Consult Fee</label>
                                    <div class="input-icon">
                                        <span class="input-icon-prefix">$</span>
                                        <input id="qFee" type="number" min="0" step="0.01"
                                            class="form-control" value="{{ $profile?->consult_fee }}">
                                        <span class="input-icon-suffix subtle">USD</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small subtle mb-1">Avg Duration</label>
                                    <div class="input-icon">
                                        <input id="qDuration" type="number" min="5" step="5"
                                            class="form-control" value="{{ $profile?->avg_duration ?? 15 }}">
                                        <span class="input-icon-suffix subtle">min</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small subtle mb-1">Meeting Link</label>
                                    <div class="input-icon">
                                        <input id="meeting_link" type="url" 
                                            class="form-control" value="{{ $profile?->meeting_link}}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT: Specialties --}}
                        <div class="col-md-6 d-flex flex-column">
                            <div class="tile h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="tile-head m-0"><i class="fa-solid fa-stethoscope"></i> Specialties</div>
                                    <span class="mini" id="specCount"></span>
                                </div>

                                <select id="qSpecialties" class="form-select d-none" multiple>
                                    @foreach ($allSpecialties as $sp)
                                        <option value="{{ $sp->id }}"
                                            {{ in_array($sp->id, $selectedSpecialtyIds ?? []) ? 'selected' : '' }}>
                                            {{ $sp->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <div id="specChips" class="chips-wrap mb-2"></div>

                                <div class="d-flex gap-2 mb-2">
                                    <input id="specSearch" class="form-control" placeholder="Search specialties…">
                                    <button type="button" class="btn btn-ghost" id="btnSpecAdd">
                                        <i class="fa-solid fa-plus me-1"></i>Add
                                    </button>
                                </div>

                                <div id="specResults" class="spec-results d-none"></div>

                                <div class="mini mt-auto">
                                    Tip: Add multiple specialties. Click a chip to remove it.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sticky Save -->
                    <div class="savebar">
                        <div class="inner">
                            <button class="btn btn-gradient px-4" id="btnQuickSave">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                {{-- TAB 3: Credentials --}}
                <div class="tab-pane fade" id="pane-creds" role="tabpanel" aria-labelledby="tab-creds">
                    <div class="cred-card p-3 p-md-4">
                        <div class="sec-head"><i class="fa-solid fa-badge-check"></i> <span>Credential Management</span>
                        </div>

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
                                <span class="btn-text">Upload Credential <i
                                        class="fa-solid fa-rotate-right ms-1"></i></span>
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

                {{-- TAB 4: Billing --}}
                <div class="tab-pane fade" id="pane-billing" role="tabpanel" aria-labelledby="tab-billing">
                    <div class="section-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="label-sub">Wallet Balance</div>
                                <div class="display-6 fw-bold">$
                                    {{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</div>
                            </div>
                            <i class="fa-solid fa-wallet fs-3" style="color:#cbd5e1;"></i>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <a href="{{ route('doctor.wallet.index') }}" class="btn btn-gradient">Withdraw Funds</a>
                            <a href="{{ route('doctor.wallet.index') }}" class="btn btn-outline-light">View
                                Transactions</a>
                        </div>
                    </div>
                </div>

                {{-- TAB 5: Security --}}
                <div class="tab-pane fade" id="pane-security" role="tabpanel" aria-labelledby="tab-security">
                    <div class="section-card" id="passwordCard">
                        <button type="button" class="btn btn-sm btn-outline-light edit-btn" id="editPassBtn">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </button>

                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-key"></i>
                            <h5 class="m-0">Change Password</h5>
                        </div>
                        <div class="label-sub mb-3">Keep your account secure</div>

                        <form id="passwordForm" autocomplete="off">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="password" required disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="password_confirmation" required
                                    disabled>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-gradient" id="btnChangePass" disabled>
                                    <span class="btn-text">Update Password</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>


            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
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

        // Toggle editable for profile
        $('#editProfileBtn').on('click', function() {
            const $form = $('#profileForm');
            const isDisabled = $form.find('input, select, button').prop('disabled');
            $form.find('input, select, button').prop('disabled', !isDisabled);
            if (isDisabled) {
                $(this).html('<i class="fa-solid fa-xmark me-1"></i> Cancel');
            } else {
                $(this).html('<i class="fa-solid fa-pen me-1"></i> Edit');
            }
        });

        // Toggle editable for password
        $('#editPassBtn').on('click', function() {
            const $form = $('#passwordForm');
            const isDisabled = $form.find('input, button').prop('disabled');
            $form.find('input, button').prop('disabled', !isDisabled);
            if (isDisabled) {
                $(this).html('<i class="fa-solid fa-xmark me-1"></i> Cancel');
            } else {
                $(this).html('<i class="fa-solid fa-pen me-1"></i> Edit');
            }
        });

        // Save profile
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#btnSaveProfile');
            lockBtn($btn);

            const data = $(this).serializeArray();

            $.post(`{{ route('doctor.profile.update') }}`, $.param(data))
                .done(res => flash('success', res.message || 'Profile updated'))
                .fail(xhr => {
                    let msg = xhr.responseJSON?.message || 'Update failed';
                    if (xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    flash('danger', msg);
                })
                .always(() => unlockBtn($btn));
        });

        // Change password
        $('#passwordForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#btnChangePass');
            lockBtn($btn);
            $.post(`{{ route('doctor.profile.password') }}`, $(this).serialize())
                .done(res => {
                    flash('success', res.message || 'Password updated');
                    this.reset();
                })
                .fail(xhr => {
                    let msg = xhr.responseJSON?.message || 'Password change failed';
                    if (xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    flash('danger', msg);
                })
                .always(() => unlockBtn($btn));
        });



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
                    meeting_link: $('#meeting_link').val(),
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
    </script>
@endpush
