@extends('layouts.patient')
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
    </style>
@endpush

@section('content')
    <div class="row g-3">
        {{-- Profile info --}}
        <div class="col-12 col-lg-8">
            <div class="section-card" id="profileCard">
                <button type="button" class="btn btn-sm btn-outline-light edit-btn" id="editProfileBtn">
                    <i class="fa-solid fa-pen me-1"></i> Edit
                </button>

                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-user"></i>
                    <h5 class="m-0">Profile Information</h5>
                </div>
                <div class="label-sub mb-3">Update your personal details</div>

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
                                    <a class="btn btn-outline-light" href="{{ route('verify.show') }}">Verify</a>
                                @endif
                            </div>
                            <div class="mt-1">
                                @if (auth()->user()->email_verified_at)
                                    <span class="status-chip verified"><i class="fa-solid fa-circle-check me-1"></i>
                                        Verified</span>
                                @else
                                    <span class="status-chip unverified"><i class="fa-regular fa-circle me-1"></i> Not
                                        verified</span>
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
                            <select class="form-select" name="gender" disabled>
                                <option value="">Select</option>
                                @php $g = auth()->user()->gender ?? ''; @endphp
                                <option value="male" {{ $g === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $g === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $g === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="dob"
                                value="{{ old('dob', optional(auth()->user()->dob ?? null)->format('Y-m-d')) }}" disabled>
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

        {{-- Sidebar column: wallet + password --}}
        <div class="col-12 col-lg-4">
            {{-- Wallet --}}
            <div class="section-card mb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="label-sub">Wallet Balance</div>
                        <div class="display-6 fw-bold">$ {{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</div>
                    </div>
                    <i class="fa-solid fa-wallet fs-3" style="color:#cbd5e1;"></i>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('patient.wallet.index') }}" class="btn btn-outline-light">View Transactions</a>
                </div>
            </div>

            {{-- Change Password --}}
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
                        <input type="password" class="form-control" name="password_confirmation" required disabled>
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
@endsection

@push('scripts')
    <script>
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
            const first = $('[name="first_name"]').val().trim();
            const last = $('[name="last_name"]').val().trim();
            data.push({
                name: 'name',
                value: (first + ' ' + last).trim()
            });

            $.post(`{{ route('patient.profile.update') }}`, $.param(data))
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
            $.post(`{{ route('patient.profile.password') }}`, $(this).serialize())
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
    </script>
@endpush
