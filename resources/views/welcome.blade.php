@extends('layouts.app')
@section('title', 'Welcome')

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
            --accent1: #8758e8;
            --accent2: #e0568a;
            --success: #22c55e;
        }

        body {
            background: var(--bg) !important;
            color: var(--text);
        }

        /* HERO */
        .hero {
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--border);
            background:
                radial-gradient(1200px 600px at -10% -20%, rgba(135, 88, 232, .12), transparent 60%),
                radial-gradient(1000px 600px at 110% 10%, rgba(224, 86, 138, .10), transparent 60%);
        }

        .brand-gradient {
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            letter-spacing: .4px;
        }

        .hero h1 {
            font-weight: 800;
            line-height: 1.12;
        }

        .lead-sub {
            color: var(--muted);
        }

        .btn-gradient {
            background-image: linear-gradient(90deg, var(--accent1), var(--accent2));
            border: 0;
            color: #fff;
            font-weight: 600;
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

        /* STATS STRIP */
        .stats {
            background: #0f1628;
            border: 1px solid var(--border);
            border-radius: 16px;
        }

        .stat {
            text-align: center;
            padding: 14px 8px;
        }

        .stat .num {
            font-size: 1.6rem;
            font-weight: 800;
        }

        .stat .lbl {
            color: var(--muted);
            font-size: .95rem;
        }

        /* SECTION */
        .section {
            padding: 56px 0;
        }

        .sec-head {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: .5rem;
        }

        .sec-sub {
            color: var(--muted);
            max-width: 720px;
        }

        /* ROLE CARDS */
        .role-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            height: 100%;
        }

        .role-card .ico-pill {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            background: #0b1222;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .role-card ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        .role-card li {
            color: #cbd5e1;
            margin: .25rem 0;
        }

        .sep-dash {
            height: 1px;
            background: var(--border);
            margin: 12px 0;
            opacity: .6;
        }

        /* HOW IT WORKS */
        .step {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            height: 100%;
        }

        .step .badge-num {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: #0b1222;
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: .4rem;
        }

        /* SHOWCASE / MOCKS */
        .mock {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px;
            min-height: 220px;
        }

        .mock .placeholder {
            height: 160px;
            border-radius: 12px;
            background:
                linear-gradient(90deg, rgba(135, 88, 232, .15), rgba(224, 86, 138, .15));
            border: 1px dashed #314160;
        }

        /* FAQ */
        .accordion-item {
            background: #0f1a2e;
            border: 1px solid var(--border);
        }

        .accordion-button {
            background: #0f1a2e;
            color: #e5e7eb;
        }

        .accordion-button:focus {
            box-shadow: none;
        }

        .accordion-button:not(.collapsed) {
            color: #fff;
            background: #12203a;
        }

        /* FOOTER */
        .footer {
            border-top: 1px solid var(--border);
            color: var(--muted);
        }

        .footer a {
            color: #bcd1ff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .chip {
            background: var(--chip);
            border: 1px solid var(--border);
            color: #cfe0ff;
            border-radius: 999px;
            padding: .25rem .6rem;
        }
    </style>
@endpush

@section('content')
    {{-- HERO --}}
    <section class="hero py-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <div class="chip mb-3">End-to-end Telehealth Platform</div>
                    <h1 class="mb-3">
                        <span class="brand-gradient">{{ strtoupper(config('app.name', 'CYBERMEDIKS')) }}</span><br>
                        Care that connects Patients, Doctors, Pharmacies & Dispatch
                    </h1>
                    <p class="lead-sub mb-4">
                        Secure consultations, e-prescriptions, doorstep delivery, and smart workflows—built for speed and
                        trust.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('register.show') }}" class="btn btn-gradient">
                            <span class="btn-text"><i class="fa-solid fa-user-plus me-2"></i>Get Started </span>
                        </a>

                    </div>

                    <div class="row g-3 mt-4 stats">
                        <div class="col-4 stat">
                            <div class="num">HIPAA-style</div>
                            <div class="lbl">Security mindset</div>
                        </div>
                        <div class="col-4 stat">
                            <div class="num">24/7</div>
                            <div class="lbl">Support readiness</div>
                        </div>
                        <div class="col-4 stat">
                            <div class="num">4 Roles</div>
                            <div class="lbl">Unified platform</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mock">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-semibold">Live preview</div>
                            <span class="chip">Dark UI</span>
                        </div>
                        <div class="placeholder mt-3"></div>
                        <div class="text-white small mt-2">Modern, responsive components with Bootstrap 5 + Font Awesome.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ROLES --}}
    <section class="section">
        <div class="container">
            <div class="sec-head">
                <i class="fa-solid fa-layer-group"></i>
                <h3 class="m-0">Built for every role</h3>
            </div>
            <p class="sec-sub">From onboarding to delivery, each dashboard is tailored to the job-to-be-done—no extra
                clicks, just outcomes.</p>

            <div class="row g-3 mt-2">
                {{-- Patient --}}
                <div class="col-md-6 col-xl-4">
                    <div class="role-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ico-pill"><i class="fa-solid fa-user"></i></div>
                            <h5 class="m-0">Patients</h5>
                        </div>
                        <div class="sep-dash"></div>
                        <ul>
                            <li>Search & chat/video with doctors</li>
                            <li>View prescriptions & history</li>
                            <li>Wallet & secure payments</li>
                            <li>Pharmacy delivery tracking</li>
                        </ul>
                        <div class="d-grid mt-3">
                            <a href="{{ route('register.show') }}" class="btn btn-gradient"><span
                                    class="btn-text">Create free account</span></a>
                        </div>
                    </div>
                </div>

                {{-- Doctor --}}
                <div class="col-md-6 col-xl-4">
                    <div class="role-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ico-pill"><i class="fa-solid fa-user-doctor"></i></div>
                            <h5 class="m-0">Doctors</h5>
                        </div>
                        <div class="sep-dash"></div>
                        <ul>
                            <li>Smart queue for video & chat</li>
                            <li>Credential management & KYC</li>
                            <li>E-prescriptions with refills</li>
                            <li>Schedule & availability control</li>
                        </ul>
                        <div class="d-grid mt-3">
                            <a href="{{ route('doctor.dashboard') }}" class="btn btn-ghost">Go to Doctor Portal</a>
                        </div>
                    </div>
                </div>

                {{-- Pharmacy --}}
                <div class="col-md-6 col-xl-4">
                    <div class="role-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ico-pill"><i class="fa-solid fa-store"></i></div>
                            <h5 class="m-0">Pharmacies</h5>
                        </div>
                        <div class="sep-dash"></div>
                        <ul>
                            <li>Receive e-prescriptions instantly</li>
                            <li>Stock, pricing & fulfillment</li>
                            <li>Real-time dispatch handoff</li>
                            <li>Settlement & reconciliation</li>
                        </ul>
                        <div class="d-grid mt-3">
                            <a href="#" class="btn btn-ghost">Find Nearby Pharmacies</a>
                        </div>
                    </div>
                </div>

                {{-- Dispatch --}}
                <div class="col-md-6 col-xl-4">
                    <div class="role-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ico-pill"><i class="fa-solid fa-motorcycle"></i></div>
                            <h5 class="m-0">Dispatch</h5>
                        </div>
                        <div class="sep-dash"></div>
                        <ul>
                            <li>Pickup & route optimization</li>
                            <li>Status updates to patients</li>
                            <li>Proof of delivery & notes</li>
                            <li>Wallet payouts</li>
                        </ul>
                        <div class="d-grid mt-3">
                            <a href="#" class="btn btn-ghost">Dispatch Portal</a>
                        </div>
                    </div>
                </div>

                {{-- Admin --}}
                <div class="col-md-6 col-xl-4">
                    <div class="role-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ico-pill"><i class="fa-solid fa-shield-halved"></i></div>
                            <h5 class="m-0">Admin</h5>
                        </div>
                        <div class="sep-dash"></div>
                        <ul>
                            <li>User verification & QA</li>
                            <li>Disputes & compliance</li>
                            <li>Pricing & marketplace rules</li>
                            <li>Analytics & reporting</li>
                        </ul>
                        <div class="d-grid mt-3">
                            <a href="#" class="btn btn-ghost">Admin Console</a>
                        </div>
                    </div>
                </div>

                {{-- Security (extra card for trust) --}}
                <div class="col-md-6 col-xl-4">
                    <div class="role-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="ico-pill"><i class="fa-solid fa-lock"></i></div>
                            <h5 class="m-0">Security & Compliance</h5>
                        </div>
                        <div class="sep-dash"></div>
                        <ul>
                            <li>Role-based access & audit trails</li>
                            <li>Code-based verification flows</li>
                            <li>Encrypted transit & rest (config)</li>
                            <li>Least-privilege dashboards</li>
                        </ul>
                        <div class="d-grid mt-3">
                            <a href="#faq" class="btn btn-ghost">Learn more</a>
                        </div>
                    </div>
                </div>
            </div> <!-- /row -->
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="section">
        <div class="container">
            <div class="sec-head">
                <i class="fa-solid fa-route"></i>
                <h3 class="m-0">How it works</h3>
            </div>
            <p class="sec-sub">Simple steps—from discovery to delivery.</p>

            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="step">
                        <span class="badge-num">1</span> <strong>Create account</strong>
                        <div class="text-white small mt-2">Sign up and verify email with secure code flow.</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step">
                        <span class="badge-num">2</span> <strong>Consult a doctor</strong>
                        <div class="text-white small mt-2">Search, filter by specialty, and chat or start a video call.
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step">
                        <span class="badge-num">3</span> <strong>Receive e-Rx</strong>
                        <div class="text-white small mt-2">Prescriptions sync to your profile and preferred pharmacy.</div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step">
                        <span class="badge-num">4</span> <strong>Doorstep delivery</strong>
                        <div class="text-white small mt-2">Dispatch updates in real-time till completion.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SHOWCASE --}}
    <section class="section">
        <div class="container">
            <div class="sec-head">
                <i class="fa-regular fa-window-maximize"></i>
                <h3 class="m-0">Modern, fast, familiar</h3>
            </div>
            <p class="sec-sub">Bootstrap, Font Awesome, and crisp UX—no heavy frontend build step required.</p>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="mock">
                        <div class="fw-semibold"><i class="fa-solid fa-gauge-high me-2"></i>Patient Dashboard</div>
                        <div class="placeholder mt-3"></div>
                        <div class="text-white small mt-2">Metrics, find doctors, and real-time availability.</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mock">
                        <div class="fw-semibold"><i class="fa-solid fa-user-doctor me-2"></i>Doctor Dashboard</div>
                        <div class="placeholder mt-3"></div>
                        <div class="text-white small mt-2">Queues, quick actions, and credential management.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="section" id="faq">
        <div class="container">
            <div class="sec-head">
                <i class="fa-regular fa-circle-question"></i>
                <h3 class="m-0">FAQ</h3>
            </div>

            <div class="accordion" id="faqAcc">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="f1">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#c1">
                            How do I verify my email and reset password?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                        <div class="accordion-body">
                            We send a 6-digit code via email. Codes are cached server-side with expiry. You can request a
                            new code anytime.
                        </div>
                    </div>
                </div>

                <div class="accordion-item mt-2">
                    <h2 class="accordion-header" id="f2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#c2">
                            Can I choose my doctor by specialty and availability?
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                        <div class="accordion-body">
                            Yes. Filter by specialty, search by name/title, and toggle “available only” for real-time
                            matches.
                        </div>
                    </div>
                </div>

                <div class="accordion-item mt-2">
                    <h2 class="accordion-header" id="f3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#c3">
                            Do you support pharmacies and dispatch in one flow?
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
                        <div class="accordion-body">
                            Absolutely. e-Rx is sent to a pharmacy (user role), then dispatch is notified for pickup and
                            delivery updates.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="footer py-4">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>© {{ date('Y') }} {{ config('app.name', 'CYBERMEDIKS') }}. All rights reserved.</div>
            <div class="d-flex gap-3">
                <a href="{{ route('login.show') }}"> Login</a>
                <a href="{{ route('register.show') }}">Get Started</a>
            </div>
        </div>
    </footer>
@endsection
