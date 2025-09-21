<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — @yield('title', '') </title>

    {{-- Bootstrap 5 + FA + jQuery (CDNs) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

    <style>
        .btn-loading .spinner-border {
            width: 1rem;
            height: 1rem;
        }

        .btn-loading .btn-text {
            visibility: hidden;
        }

        .app-navbar {
            background: #0f172a;
            /* dark */
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .app-navbar .nav-link {
            color: #e5e7eb;
            font-weight: 500;
        }

        .app-navbar .nav-link:hover {
            color: #fff;
        }

        .brand-gradient {
            background: linear-gradient(90deg, #8758e8, #e0568a);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-gradient {
            background: linear-gradient(90deg, #8758e8, #e0568a);
            color: #fff !important;
            border: none;
        }

        .btn-gradient:hover {
            opacity: .9;
        }

        /* keeps button width stable */
    </style>
    @stack('styles')
</head>

<body>
    <nav class="navbar navbar-expand-lg app-navbar">
        <div class="container">
            <a class="navbar-brand brand-gradient fw-bold" href="{{ url('/') }}">
                {{ config('app.name') }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    @auth
                    @php
                        $user = auth()->user();
                        $role = $user->role;
                        $dashboardRoute = '';

                        switch ($role) {
                            case 'patient':
                                $dashboardRoute = route('patient.dashboard');
                                break;

                            case 'pharmacist':
                                $dashboardRoute = route('pharmacist.dashboard');
                                break;
                            
                            case 'doctor':
                                $dashboardRoute = route('doctor.dashboard');
                                break;

                            case 'dispatcher':
                                $dashboardRoute = route('dispatcher.dashboard');
                                break;
                            
                            default:
                                $dashboardRoute = '#';
                                break;
                        }
                    @endphp
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $dashboardRoute }}">
                                <i class="fa-solid fa-house me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-gradient">Logout</button>
                            </form>
                        </li>
                    @endauth
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login.show') }}">
                                <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-sm btn-gradient" href="{{ route('register.show') }}">
                                <i class="fa-solid fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>


    <main class="container py-4">
        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>
