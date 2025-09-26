<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CyberMediks</title>

    <!-- Bootstrap 5 + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --accent1: #8758e8;
            --accent2: #e0568a;
            --muted: #9aa3b2;
        }

        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: #0f172a;
            color: #fff;
        }

        /* NAV */
        .navbar {
            background: rgba(15, 23, 42, 0.35) !important;
            /* dark but see-through */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .navbar-brand {
            font-weight: 700;
            color: #fff;
        }

        .navbar-brand:hover {
            color: #fff;
        }

        .nav-link {
            color: #e5e7eb !important;
            position: relative;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #fff !important;
        }

        /* gradient underline on active */
        .nav-link.active::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -4px;
            width: 100%;
            height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
        }

        .btn-gradient {
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
            color: #fff !important;
            border: none;
        }

        .btn-gradient:hover {
            opacity: .92;
        }

        /* HERO */
        .hero-wrap {
            position: relative;
            width: 100%;
            min-height: 90vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(9, 15, 29, .75) 0%, rgba(9, 15, 29, .55) 40%, rgba(9, 15, 29, .85) 100%),
                url('/images/doctor.webp') center/cover no-repeat;
        }

        .hero-inner {
            position: relative;
            width: 100%;
            padding: 120px 0 80px;
            text-align: center;
        }

        h1 {
            font-weight: 900;
            line-height: 1.08;
            font-size: clamp(2rem, 4.5vw, 3.5rem);
        }

        .brand-gradient {
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .lead-sub {
            color: #d1d5db;
            font-size: 1.1rem;
        }

        .btn-ghost {
            background: rgba(14, 22, 43, .8);
            border: 1px solid #283652;
            color: #e5e7eb;
        }

        .btn-ghost:hover {
            background: #1a2845;
            color: #fff;
        }
    </style>
</head>

<body>
    <!-- NAV -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">CYBERMEDIKS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- left brand -->
                <a class="navbar-brand me-auto" style="visibility:hidden" href="#">CYBERMEDIKS</a>

                <!-- center links -->
                <ul class="navbar-nav mx-auto gap-4">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Store</a></li>
                </ul>

                <!-- right buttons -->
                <ul class="navbar-nav ms-auto gap-3">
                    <li class="nav-item"><a class="nav-link" href="#"><i
                                class="fa-solid fa-cart-shopping"></i></a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-dark" href="{{ route('login.show') }}">Login</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-success" href="{{ route('register.show') }}">Sign Up</a></li>
                </ul>
            </div>

        </div>
    </nav>

    <!-- HERO -->
    <section class="hero hero-wrap">
        <div class="hero-bg"></div>
        <div class="hero-inner container">
            <h1 class="mb-3">
                <span class="brand-gradient">Your Health, Reimagined.</span>
            </h1>
            <p class="lead-sub mb-4">Instant access to certified doctors, online prescriptions, and medication delivery.
                All from the comfort of your home.</p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('register.show') }}" class="btn btn-lg btn-gradient"><i
                        class="fa-solid fa-arrow-right-to-bracket me-2"></i>Get Started</a>
                <a href="{{ route('login.show') }}" class="btn btn-lg btn-ghost">Sign In</a>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
