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
                <div class="fw-semibold text-white">
                    {{ auth()->user()->first_name . ' ' . auth()->user()->last_name }}
                </div>
                <div class="sb-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>
    </center>

    {{-- Menu (scrollable) --}}
    <div class="sb-scroll">
        <nav class="menu">
            <div class="section">Overview</div>
            <a class="item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
            </a>

            <div class="section">People</div>
            <a class="item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                href="{{ route('admin.users.index') }}">
                <i class="fa-solid fa-users"></i><span>Patients</span>
            </a>
            <a class="item {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}"
                href="{{ route('admin.doctors.index') }}">
                <i class="fa-solid fa-user-doctor"></i><span>Doctors</span>
            </a>
            <a class="item {{ request()->routeIs('admin.pharmacies.*') ? 'active' : '' }}"
                href="{{ route('admin.pharmacies.index') }}">
                <i class="fa-solid fa-prescription-bottle-medical"></i><span>Pharmacies</span>
            </a>
            <a class="item {{ request()->routeIs('admin.dispatchers.*') ? 'active' : '' }}"
                href="{{ route('admin.dispatchers.index') }}">
                <i class="fa-solid fa-truck"></i><span>Dispatchers</span>
            </a>

            <div class="section">Operations</div>
            <a class="item {{ request()->routeIs('admin.prescriptions.*') ? 'active' : '' }}"
                href="{{ route('admin.prescriptions.index') }}">
                <i class="fa-solid fa-file-prescription"></i><span>Prescriptions</span>
            </a>
            <a class="item {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}"
                href="{{ route('admin.appointments.index') }}">
                <i class="fa-solid fa-calendar-check"></i><span>Appointments</span>
            </a>
            <a class="item {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}"
                href="{{ route('admin.transactions.index') }}">
                <i class="fa-solid fa-wallet"></i><span>Transactions</span>
            </a>

            <div class="section">Configuration</div>
            <a class="item {{ request()->routeIs('admin.specialties.*') ? 'active' : '' }}"
                href="{{ route('admin.specialties.index') }}">
                <i class="fa-solid fa-list-check"></i><span>Specialties</span>
            </a>
            <a class="item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}"
                href="{{ route('admin.admins.index') }}">
                <i class="fa-solid fa-user-shield"></i><span>Admins</span>
            </a>
        </nav>
    </div>

    {{-- Bottom actions --}}
    <div class="sb-bottom">
        <form action="{{ route('logout') }}" method="POST" class="mb-2">
            @csrf
            <button class="item btn-as-link text-end w-100 logout">
                <i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span>
            </button>
        </form>
    </div>
</aside>
