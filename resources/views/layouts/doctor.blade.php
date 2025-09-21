<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', 'Doctor') </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0f172a;
            --panel: #111827;
            --muted: #9aa3b2;
            --text: #e5e7eb;
            --border: #25324a;
            --card: #0f1a2e;
            --chip: #1e293b;
            --accent1: #8758e8;
            --accent2: #e0568a;
            --success: #22c55e;
        }

        .form-control,
        .form-select {
            background-color: var(--bg) !important;
            /* same as your normal input bg */
            border-color: var(--border) !important;
            color: var(--text) !important;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .brand-gradient {
            background: linear-gradient(90deg, var(--accent1), var(--accent2));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: #0b1222;
            border-right: 1px solid var(--border);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: auto;
        }

        .sidebar .logo {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: 18px 16px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar .logo .name {
            font-weight: 800;
            letter-spacing: .6px;
        }

        .sidebar .user {
            padding: 16px;
            border-bottom: 1px solid var(--border);
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #17223a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .menu {
            padding: 8px;
        }

        .menu .item a {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: 10px 12px;
            color: #c9d1e1;
            text-decoration: none;
            border-radius: 10px;
        }

        .menu .item a:hover {
            background: #131f34;
            color: #fff;
        }

        .menu .item.active>a {
            background: linear-gradient(90deg, #412e83, transparent 70%);
            color: #fff;
        }

        .menu .section {
            padding: 12px 12px 4px;
            color: var(--muted);
            font-size: .84rem;
            text-transform: uppercase;
        }

        .logout {
            color: #f43f5e !important;
        }

        .content {
            min-height: 100vh;
            background: radial-gradient(1200px 600px at -10% -20%, rgba(135, 88, 232, .08), transparent 50%),
                radial-gradient(1000px 600px at 110% 10%, rgba(224, 86, 138, .06), transparent 50%);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background: #0f1628;
        }

        .btn-gradient {
            background-image: linear-gradient(90deg, var(--accent1), var(--accent2));
            color: #fff;
            border: 0;
        }

        .cardx {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
        }

        .metric {
            font-size: 2rem;
            font-weight: 800;
        }

        .chip {
            background: var(--chip);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .25rem .6rem;
            color: #b8c2d6;
        }

        .doctor-row {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            background: #0d162a;
        }

        .avatar-sm {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #14203a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .btn-outline-light {
            border-color: #3a4a69;
            color: #dbe3f7;
        }

        .btn-outline-light:hover {
            background: #1a2845;
            color: #fff;
        }

        /* Sidebar container */
        .sidebar {
            background: #0b1222;
            border-right: 1px solid #25324a;
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            width: 260px;
            overflow: hidden;
        }

        /* Header */
        .sb-head {
            padding: 14px 16px;
            border-bottom: 1px solid #25324a;
        }

        .sb-back {
            color: #dbe3f7;
        }

        .sb-back i {
            font-size: .9rem;
            color: #9aa3b2;
        }

        .sb-back:hover {
            color: #fff;
        }

        .brand-gradient {
            background: linear-gradient(90deg, #8758e8, #e0568a);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: .6px;
        }

        /* User block */
        .sb-user {
            padding: 16px;
            border-bottom: 1px solid #25324a;
        }

        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #17223a;
            color: #cfe0ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .sb-role {
            color: #9aa3b2;
            font-size: .85rem;
        }

        /* Scrollable middle */
        .sb-scroll {
            overflow: auto;
            padding: 8px 8px 12px;
            flex: 1;
        }

        .sb-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .sb-scroll::-webkit-scrollbar-thumb {
            background: #1b2741;
            border-radius: 8px;
        }

        /* Sections + items */
        .menu .section {
            color: #9aa3b2;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 8px 8px 4px;
        }

        .item {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: #c9d1e1;
            text-decoration: none;
            padding: 10px 12px;
            margin: 4px 4px;
            border-radius: 12px;
            transition: background .15s ease, color .15s ease;
        }

        .item i {
            width: 18px;
            text-align: center;
        }

        .item:hover {
            background: #131f34;
            color: #fff;
        }

        /* Active pill matches screenshot (purple left, soft) */
        .item.active {
            background: linear-gradient(90deg, rgba(92, 51, 180, .55), rgba(92, 51, 180, .18) 70%);
            color: #fff;
            border: 1px solid rgba(135, 88, 232, .35);
        }

        /* Bottom action block fixed */
        .sb-bottom {
            border-top: 1px solid #25324a;
            padding: 10px 8px 14px;
        }

        .btn-as-link {
            background: transparent;
            border: 0;
            padding: 0;
            line-height: 1;
            color: inherit;
        }

        .logout {
            color: #f43f5e !important;
        }

        .logout:hover {
            background: #2a1020;
        }


        /* mobile */
        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                left: -270px;
                width: 260px;
                z-index: 1040;
                height: 100vh;
                transition: left .2s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .content {
                padding-left: 0 !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    <div class="layout">
        {{-- Sidebar --}}
        @include('doctor.partials.sidebar')

        {{-- Main content --}}
        <section class="content">
            <div class="topbar">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-light d-lg-none" id="btnSidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <span class="fw-semibold">Doctor</span>
                </div>
            </div>

            <div class="container-fluid py-4">
                @yield('content')
            </div>
        </section>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        $('#btnSidebar').on('click', () => $('.sidebar').toggleClass('show'));
    </script>
    @include('doctor.partials.location-modal')
    @stack('scripts')
</body>

</html>
