@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    <div class="row g-3">
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Total Users</div>
                <div class="metric">{{ $totalUsers }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Doctors</div>
                <div class="metric">{{ $doctors }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Pharmacies</div>
                <div class="metric">{{ $pharmacies }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Dispatchers</div>
                <div class="metric">{{ $dispatchers }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Rx Total</div>
                <div class="metric">{{ $rxTotal }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Rx Pending</div>
                <div class="metric">{{ $rxPending }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Rx Ready</div>
                <div class="metric">{{ $rxReady }}</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="cardx">
                <div class="subtle">Rx Picked</div>
                <div class="metric">{{ $rxPicked }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-4">
            <div class="cardx">
                <div class="subtle">Appointments Today</div>
                <div class="metric">{{ $apptsToday }}</div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="cardx">
                <div class="subtle">Revenue (Today)</div>
                <div class="metric">${{ number_format($revenueToday, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-3"><a class="cardx d-block text-decoration-none" href="{{ route('admin.users.index') }}">Manage
                Users →</a></div>
        <div class="col-lg-3"><a class="cardx d-block text-decoration-none"
                href="{{ route('admin.prescriptions.index') }}">Review Prescriptions →</a></div>
        <div class="col-lg-3"><a class="cardx d-block text-decoration-none"
                href="{{ route('admin.appointments.index') }}">Review Appointments →</a></div>
        <div class="col-lg-3"><a class="cardx d-block text-decoration-none"
                href="{{ route('admin.transactions.index') }}">View Transactions →</a></div>
    </div>
@endsection
