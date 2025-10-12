@extends('layouts.app')
@section('title', 'Register')

@push('styles')
    <style>
        :root {
            --bg: #0f172a;
            /* page background */
            --card: #ffffff0d;
            /* panel */
            --field: #0b1222;
            /* input bg */
            --field-border: #2b3a56;
            --field-focus: #6b7280;
            --text: #e5e7eb;
            --muted: #9aa3b2;
            --accent1: #8758e8;
            /* gradient left */
            --accent2: #e0568a;
            /* gradient right */
        }

        body {
            background: var(--bg) !important;
        }

        .auth-wrap {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
        }

        .auth-card {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 18px;
        }

        .brand-gradient {
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            letter-spacing: .8px;
        }

        .subtle {
            color: var(--muted);
        }

        .form-control,
        .form-select {
            background: var(--field);
            border-color: var(--field-border);
            color: var(--text);
        }

        .form-control::placeholder {
            color: #7a8395;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--field-focus);
            box-shadow: none;
        }

        .btn-gradient {
            background-image: linear-gradient(90deg, var(--accent1), var(--accent2));
            border: 0;
            color: #fff;
            font-weight: 600;
        }

        .btn-gradient:disabled {
            opacity: .85;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon .toggle-password {
            position: absolute;
            right: .75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2;
            cursor: pointer;
        }
    </style>
@endpush

@section('content')
    <div class="auth-wrap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="auth-card p-4 p-md-5 shadow-sm">
                        <div class="text-center mb-4">
                            <h2 class="brand-gradient mb-1">CYBERMEDIKS</h2>
                            <h4 class="text-white mb-1">Create an Account</h4>
                        </div>

                        <form id="regForm" autocomplete="off">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" name="first_name" class="form-control" placeholder="First Name"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="last_name" class="form-control" placeholder="Last Name"
                                        required>
                                </div>
                                <div class="col-12">
                                    <input type="email" name="email" class="form-control" placeholder="Email Address"
                                        required>
                                </div>

                                <div class="col-12">
                                    <select name="role" class="form-select" required>
                                        <option value="" disabled selected>Select Role</option>
                                        <option value="patient">Patient</option>
                                        <option value="doctor">Doctor</option>
                                        <option value="pharmacy">Pharmacy</option>
                                        <option value="dispatcher">Dispatch Rider</option>
                                        <option value="labtech">Laboratory Technician</option>
                                        <option value="health">Department of Health</option>
                                        <option value="transport">Department of Pharmacy</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <select name="country_id" class="form-select" required>
                                        <option value="" disabled selected>Select Country</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 input-with-icon">
                                    <input type="password" name="password" class="form-control" placeholder="Password"
                                        required>
                                    <i class="fa-regular fa-eye toggle-password" data-target="password"></i>
                                </div>
                                <div class="col-12">
                                    <input type="password" name="password_confirmation" class="form-control"
                                        placeholder="Confirm Password" required>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button class="btn btn-gradient py-2" type="submit">
                                    <span class="btn-text">Next&nbsp; <i class="fa-solid fa-arrow-right"></i></span>
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4 subtle">
                            Already have an account? <a href="{{ route('login.show') }}"
                                class="text-decoration-none">Sign in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Show/Hide password
            $(document).on('click', '.toggle-password', function() {
                const $pwd = $('input[name="password"]');
                if ($pwd.attr('type') === 'password') {
                    $pwd.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $pwd.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Submit (concat first+last -> name)
            $('#regForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type=submit]');
                lockBtn($btn);

                const data = {
                    first_name: $('input[name="first_name"]').val().trim(),
                    last_name: $('input[name="last_name"]').val().trim(),
                    email: $('input[name="email"]').val().trim(),
                    country_id: $('select[name="country_id"]').val().trim(),
                    password: $('input[name="password"]').val(),
                    password_confirmation: $('input[name="password_confirmation"]').val(),
                    role: $('select[name="role"]').val()
                };
                data.name = (data.first_name + ' ' + data.last_name).trim(); // for your controller

                $.post(`{{ route('register') }}`, data)
                    .done(res => {
                        flash('success', res.message || 'Registered');
                        window.location = res.redirect;
                    })
                    .fail(xhr => {
                        let msg = 'Registration failed';
                        if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(
                            '<br>');
                        else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                        flash('danger', msg);
                    })
                    .always(() => unlockBtn($btn));
            });
        </script>
    @endpush
@endsection
