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
        <div class="avatar">{{ strtoupper(substr(auth()->user()->first_name, 0, 1)) . strtoupper(substr(auth()->user()->last_name, 0, 1)) }}</div>
        <div>
            <div class="fw-semibold text-white">{{ auth()->user()->first_name. ' '. auth()->user()->last_name }}</div>
            <div class="sb-role">Pharmacy</div>
        </div>
    </div>
   </center>

    {{-- Menu (scrollable) --}}
    <div class="sb-scroll">
        <nav class="menu">
            <div class="section">Main</div>

            <a class="item {{ request()->routeIs('pharmacy.dashboard') ? 'active' : '' }}"
                href="{{ route('pharmacy.dashboard') }}">
                <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
            </a>

            <a class="item {{ request()->routeIs('pharmacy.prescriptions.index') ? 'active' : '' }}" href="{{ route('pharmacy.prescriptions.index') }}">
                <i class="fa-solid fa-file-prescription"></i><span>Pending Prescriptions</span>
            </a>

            <a class="item {{ request()->routeIs('pharmacy.dispensed.index') ? 'active' : '' }}" href="{{ route('pharmacy.dispensed.index') }}">
                <i class="fa-solid fa-clock-rotate-left"></i><span>Dispensed Orders</span>
            </a>

        </nav>
    </div>

    {{-- Bottom actions --}}
    <div class="sb-bottom">
        <a class="item {{ request()->routeIs('pharmacy.profile') ? 'active' : '' }} mb-4" href="{{ route('pharmacy.profile') }}">
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
