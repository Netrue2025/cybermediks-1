@extends('layouts.app')
@section('title', 'Verify Email')

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
        }

        .subtle {
            color: var(--muted);
        }

        .form-control {
            background: var(--field);
            border-color: var(--field-border);
            color: var(--text);
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
    </style>
@endpush

@section('content')
    <div class="auth-wrap">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="auth-card p-4 p-md-5 shadow-sm">
                        <div class="text-center mb-4">
                            <h2 class="brand-gradient mb-1">CYBERMEDIKS</h2>
                            <h4 class="text-white mb-1">Verify your email</h4>
                            <div class="subtle">We sent a 6-digit code to <strong>{{ auth()->user()->email }}</strong></div>
                        </div>

                        <form id="verifyForm">
                            <div class="mb-3">
                                <input type="text" name="code" class="form-control" placeholder="6-digit Code"
                                    maxlength="6" required>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-gradient py-2" type="submit">
                                    <span class="btn-text">Verify&nbsp;<i class="fa-solid fa-circle-check"></i></span>
                                </button>
                            </div>
                        </form>

                        <hr class="border-secondary">
                        <div class="text-center">
                            <button id="resendBtn" class="btn btn-outline-light btn-sm">
                                <i class="fa-solid fa-paper-plane me-1"></i> Resend code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $('#verifyForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type=submit]');
                lockBtn($btn);
                $.post(`{{ route('verify') }}`, $(this).serialize())
                    .done(res => {
                        flash('success', res.message || 'Verified');
                        window.location = res.redirect;
                    })
                    .fail(xhr => flash('danger', xhr.responseJSON?.message || 'Invalid code'))
                    .always(() => unlockBtn($btn));
            });

            $('#resendBtn').on('click', function() {
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('verify.send') }}`)
                    .done(res => flash('success', res.message || 'Code sent'))
                    .fail(() => flash('danger', 'Failed to send code'))
                    .always(() => unlockBtn($btn));
            });
        </script>
    @endpush
@endsection
