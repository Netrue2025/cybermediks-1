@extends('layouts.hospital')
@section('title', 'Dashboard')

@push('styles')
    <style>
        :root {
            --card: #0f172a;
            --panel: #101a2e;
            --border: #27344e;
            --text: #e5e7eb;
            --muted: #9aa3b2;
            --accent: #3b82f6;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        /* ========== Badges ========== */
        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .28rem .6rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .85rem;
            background: #0e162b;
            color: var(--text)
        }

        .badge-note {
            background: #1f2a44;
            border-color: #2b3b5d
        }

        .badge-ok {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43
        }

        .note-alert {
            background: #2a1f00;
            border: 1px solid #5b4200;
            color: #ffd78a;
            border-radius: 10px;
            padding: .65rem .8rem
        }

        /* ========== Cards / Metrics ========== */
        .cardx {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem 1.1rem;
            color: var(--text)
        }

        .cardx:hover {
            border-color: #35517c
        }

        .subtle {
            color: var(--muted);
            font-size: .9rem
        }

        .metric {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: .2px
        }

        .pill {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f162a;
            border: 1px solid var(--border)
        }

        /* ========== Panels / List Items ========== */
        .right-card {
            padding: 1rem 1.1rem
        }

        .section-subtle {
            color: var(--muted)
        }

        .item {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: .8rem 1rem
        }

        .item:hover {
            border-color: #35517c
        }

        .item .fw-semibold {
            font-weight: 600
        }

        /* Status chip */
        .cred-status {
            margin-top: .35rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem
        }

        .cred-status.pending {
            background: rgba(245, 158, 11, .1);
            border-color: #7a5a1b;
            color: #fbbf24
        }

        .cred-status.approved {
            background: rgba(34, 197, 94, .1);
            border-color: #1a6b3a;
            color: #86efac
        }

        .cred-status.rejected {
            background: rgba(239, 68, 68, .1);
            border-color: #7a1f2a;
            color: #fca5a5
        }

        /* ========== Buttons (keep Bootstrap core, just subtle tune) ========== */
        .btn-outline-light {
            border-color: #3b4a69;
            color: #dce3ef
        }

        .btn-outline-light:hover {
            background: #1a2540;
            border-color: #4a5d85
        }

        .btn-success.btn-sm {
            padding: .35rem .6rem;
            border-radius: .55rem
        }

        /* ========== Modal ========== */
        .modal-themed .modal-content {
            background: var(--card) !important;
            color: var(--text);
            border: 1px solid var(--border)
        }

        .modal-themed .modal-header {
            border-bottom: 1px solid var(--border)
        }

        .btn-close-white {
            filter: invert(1);
            opacity: .75
        }

        .btn-close-white:hover {
            opacity: 1
        }

        /* ========== Utilities ========== */
        .row.g-3>[class*="col-"] {
            min-width: 0
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
    {{-- METRICS --}}
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Pending credentials</div>
                        <div class="metric">{{ $pending ?? 0 }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-file fs-5" style="color:#efed86;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Approved credentials</div>
                        <div class="metric">{{ $approved ?? 0 }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-folder fs-5" style="color:#86bcef;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="subtle">Total Doctors</div>
                        <div class="metric">{{ $total ?? 0 }}</div>
                    </div>
                    <div class="pill"><i class="fa-solid fa-user-doctor fs-5" style="color:#86efac;"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- PANELS --}}
    <div class="row g-3 mt-1">
        {{-- Pending --}}
        <div class="col-lg">
            <div class="cardx right-card">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-badge-check"></i>
                    <div class="fw-bold">Add Doctors</div>
                </div>
                <div class="d-flex flex-column gap-2">
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
                                <select name="country_id" class="form-select" required>
                                    <option value="" disabled selected>Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- <div class="col-12 input-with-icon">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                                <i class="fa-regular fa-eye toggle-password" data-target="password"></i>
                            </div>
                            <div class="col-12">
                                <input type="password" name="password_confirmation" class="form-control"
                                    placeholder="Confirm Password" required>
                            </div> --}}
                        </div>

                        <div class="d-grid mt-4">
                            <button class="btn btn-gradient py-2" type="submit">
                                <span class="btn-text">Add Doctor&nbsp; <i class="fa-solid fa-arrow-right"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>



@endsection

@push('scripts')
    <script>
        (function() {

            // When "Credentials" clicked, fetch and show
            $(document).on('click', '[data-view-creds]', function() {
                const doctorId = $(this).data('doctor');
                const $body = $('#credModalBody');
                $body.html('<div class="text-center section-subtle">Loading…</div>');
                new bootstrap.Modal(document.getElementById('credModal')).show();

                $.get(`{{ url('/health/doctors') }}/${doctorId}/credentials`, function(html) {
                    $body.html(html);
                }).fail(function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to load credentials';
                    $body.html(`<div class="text-center text-danger">${msg}</div>`);
                });
            });

            // $(document).on('click', '.toggle-password', function() {
            //     const $pwd = $('input[name="password"]');
            //     if ($pwd.attr('type') === 'password') {
            //         $pwd.attr('type', 'text');
            //         $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            //     } else {
            //         $pwd.attr('type', 'password');
            //         $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            //     }
            // });


            $('#regForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type=submit]');
                lockBtn($btn);

                const data = {
                    first_name: $('input[name="first_name"]').val().trim(),
                    last_name: $('input[name="last_name"]').val().trim(),
                    email: $('input[name="email"]').val().trim(),
                    country_id: $('select[name="country_id"]').val().trim(),
                    // password: $('input[name="password"]').val(),
                    // password_confirmation: $('input[name="password_confirmation"]').val(),
                };

                $.post(`{{ route('hospital.doctors.create') }}`, data)
                    .done(res => {
                        flash('success', res.message || 'Registered');
                        window.location.reload();
                    })
                    .fail(xhr => {
                        let msg = 'Registration failed';
                        if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat()
                            .join(
                                '<br>');
                        else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                        flash('danger', msg);
                    })
                    .always(() => unlockBtn($btn));
            });

        })();
    </script>
@endpush
