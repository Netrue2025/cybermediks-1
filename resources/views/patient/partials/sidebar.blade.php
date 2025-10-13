<aside class="sidebar">
    {{-- Top brand / back --}}
    <div class="sb-head d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" class="sb-back d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/logo.webp') }}" width="40" alt="">
            <span class="brand-gradient fw-bold">{{ strtoupper(config('app.name', 'CYBERMEDIKS')) }}</span>
        </a>
    </div>

    {{-- User --}}
    <center>
        <div class="sb-user align-items-center gap-3">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) . strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
            </div>
            <div>
                <div class="fw-semibold text-white">{{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}
                </div>
                <div class="sb-role">Patient</div>
            </div>
        </div>
    </center>

    {{-- Menu (scrollable) --}}
    <div class="sb-scroll">
        <nav class="menu">
            <div class="section">Main</div>

            <a class="item {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}"
                href="{{ route('patient.dashboard') }}">
                <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
            </a>

            <a class="item {{ request()->routeIs('patient.store') ? 'active' : '' }}"
                href="{{ route('patient.store') }}">
                <i class="fa-solid fa-store"></i><span>Online Store</span>
            </a>

            <a class="item {{ request()->routeIs('patient.prescriptions.index') ? 'active' : '' }}"
                href="{{ route('patient.prescriptions.index') }}">
                <i class="fa-solid fa-file-prescription"></i><span>My Prescriptions</span>
            </a>

            {{-- LABWORK (new) --}}
            <div class="section">Labwork</div>

            <a class="item {{ request()->routeIs('patient.labworks.create') ? 'active' : '' }}"
                href="{{ route('patient.labworks.create') }}">
                <i class="fa-solid fa-flask-vial"></i><span>Request Labwork</span>
            </a>

            <a class="item {{ request()->routeIs('patient.labworks.index') ? 'active' : '' }}"
                href="{{ route('patient.labworks.index') }}">
                <i class="fa-solid fa-list-check"></i><span>My Labwork Requests</span>
            </a>

            {{-- Optional: browsing/selecting providers (only include if you’ve wired the route) --}}
            {{-- @if (Route::has('patient.labworks.providers'))
                <a class="item {{ request()->routeIs('patient.labworks.providers') ? 'active' : '' }}"
                    href="{{ route('patient.labworks.providers') }}">
                    <i class="fa-solid fa-hospital-user"></i><span>Lab Providers</span>
                </a>
            @endif --}}
            {{-- /LABWORK --}}

            <a class="item {{ request()->routeIs('patient.appointments.index') ? 'active' : '' }}"
                href="{{ route('patient.appointments.index') }}">
                <i class="fa-solid fa-clock-rotate-left"></i><span>Appointment History</span>
            </a>

            <a class="item {{ request()->routeIs('patient.wallet.index') ? 'active' : '' }}"
                href="{{ route('patient.wallet.index') }}">
                <i class="fa-solid fa-wallet"></i><span>My Wallet</span>
            </a>

            <a class="item {{ request()->routeIs('patient.pharmacies') ? 'active' : '' }}"
                href="{{ route('patient.pharmacies') }}">
                <i class="fa-solid fa-location-dot"></i><span>Nearby Pharmacies</span>
            </a>

            <a class="item {{ request()->routeIs('patient.messages') ? 'active' : '' }}"
                href="{{ route('patient.messages') }}">
                <i class="fa-solid fa-message"></i><span>Chat</span>
            </a>
        </nav>
    </div>


    {{-- Bottom actions --}}
    <div class="sb-bottom">
        <a class="item {{ request()->routeIs('patient.profile') ? 'active' : '' }} mb-4"
            href="{{ route('patient.profile') }}">
            <i class="fa-solid fa-gear"></i><span>Profile Settings</span>
        </a>

        <form action="{{ route('logout') }}" method="POST" class="mb-2">
            @csrf
            <button class="item btn-as-link text-end w-100 logout">
                <i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span>
            </button>
        </form>
    </div>
</aside>
