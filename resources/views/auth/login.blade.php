@extends('layouts.app')
@section('title', 'Login')

@push('styles')
    <style>
        :root {
            --bg: #0f172a;
            --card: #ffffff0d;
            --field: #0b1222;
            --field-border: #2b3a56;
            --field-focus: #6b7280;
            --text: #e5e7eb;
            --muted: #9aa3b2;
            --accent1: #8758e8;
            --accent2: #e0568a;
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

        .form-control:focus {
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

        .link-lightish {
            color: #d1d5db;
        }

        .link-lightish:hover {
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="auth-wrap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-xl-5">
                    <div class="auth-card p-4 p-md-5 shadow-sm">
                        <div class="text-center mb-4">
                            <h2 class="brand-gradient mb-1">CYBERMEDIKS</h2>
                            <h4 class="text-white mb-1">Welcome back</h4>
                            <div class="subtle">Sign in to continue</div>
                        </div>

                        <form id="loginForm" autocomplete="off">
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email Address"
                                    required autocomplete="email">
                            </div>
                            <div class="mb-2 input-with-icon">
                                <input type="password" name="password" class="form-control" placeholder="Password" required
                                    autocomplete="current-password">
                                <i class="fa-regular fa-eye toggle-password"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberCheck" name="remember">
                                    <label class="form-check-label subtle" for="rememberCheck">Remember me</label>
                                </div>
                                <a class="link-lightish text-decoration-none"
                                    href="{{ route('forgot.show') }}">Forgot password?</a>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-gradient py-2" type="submit">
                                    <span class="btn-text">Sign in&nbsp;<i class="fa-solid fa-arrow-right"></i></span>
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4 subtle">
                            New here? <a href="{{ route('register.show') }}" class="text-decoration-none">Create an
                                account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // show / hide password
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

            // submit login via AJAX with spinner + disable
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type=submit]');
                lockBtn($btn);

                $.post(`{{ route('login') }}`, $(this).serialize())
                    .done(res => {
                        window.location = res.redirect;
                    })
                    .fail(xhr => {
                        const msg = xhr.responseJSON?.message || 'Login failed';
                        flash('danger', msg);
                    })
                    .always(() => unlockBtn($btn));
            });
        </script>
    @endpush
@endsection
