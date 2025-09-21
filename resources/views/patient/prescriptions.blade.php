@extends('layouts.patient')
@section('title', 'My Prescriptions')

@push('styles')
    <style>
        .rx-row {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
        }

        .rx-badge {
            background: #10203a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .2rem .5rem;
            color: #cfe0ff;
        }

        .rx-status {
            background: var(--chip);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .2rem .6rem;
        }
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-file-prescription"></i>
            <h5 class="m-0">My Prescriptions</h5>
        </div>
        <div class="section-subtle">View, refill, and manage your prescriptions</div>

        <div class="row g-2">
            <div class="col-lg-6">
                <input id="rxSearch" class="form-control" placeholder="Search by drug or doctor...">
            </div>
            <div class="col-lg-3">
                <select id="rxStatus" class="form-select">
                    <option value="">All Statuses</option>
                    <option>Active</option>
                    <option>Expired</option>
                    <option>Refill requested</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-3">
        @foreach ([['Amoxicillin 500mg', 'Dr. Don Joe', 'Active', '#123456'], ['Ibuprofen 200mg', 'Dr. Kuyik Swiss', 'Expired', '#123457']] as $rx)
            <div class="rx-row">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <div class="fw-semibold">{{ $rx[0] }}</div>
                        <div class="section-subtle small">Prescribed by {{ $rx[1] }}</div>
                        <div class="mt-2 d-flex gap-2">
                            <span class="rx-badge">Rx {{ $rx[3] }}</span>
                            <span class="rx-status">{{ $rx[2] }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-light btn-sm">View</button>
                        <button class="btn btn-gradient btn-sm">Refill</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $('#rxSearch, #rxStatus').on('input change', function() {
            // TODO: ajax filter
        });
    </script>
@endpush
