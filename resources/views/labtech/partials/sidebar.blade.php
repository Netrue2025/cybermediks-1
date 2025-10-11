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
                <div class="sb-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>
    </center>

    {{-- Menu (scrollable) --}}
    <div class="sb-scroll">
        <nav class="menu">
            {{-- Dashboard --}}
            <a class="item {{ request()->routeIs('labtech.dashboard') ? 'active' : '' }}"
                href="{{ route('labtech.dashboard') }}">
                <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
            </a>

            {{-- Labwork Requests (index list) --}}
            <a class="item {{ request()->routeIs('labtech.labworks.index') ? 'active' : '' }}"
                href="{{ route('labtech.labworks.index') }}">
                <i class="fa-solid fa-flask-vial"></i><span>Labwork Requests</span>
            </a>

            {{-- (Optional) Appointments/Schedule if you add it later --}}
            {{-- 
            <a class="item {{ request()->routeIs('labtech.schedule*') ? 'active' : '' }}"
               href="{{ route('labtech.schedule') }}">
                <i class="fa-solid fa-calendar-check"></i><span>Schedule</span>
            </a>
            --}}

            {{-- Wallet --}}
            <a class="item {{ request()->routeIs('labtech.wallet.index') ? 'active' : '' }}"
                href="{{ route('labtech.wallet.index') }}">
                <i class="fa-solid fa-wallet"></i><span>My Wallet</span>
            </a>
        </nav>
    </div>

    {{-- Bottom actions --}}
    <div class="sb-bottom">
        <a class="item {{ request()->routeIs('labtech.profile') ? 'active' : '' }} mb-4"
            href="{{ route('labtech.profile') }}">
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
