@extends('layouts.patient')
@section('title', 'Appointment History')

@push('styles')
    <style>
        .ap-row {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
        }

        .ap-when {
            font-weight: 700;
        }

        .ap-kind {
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
            <i class="fa-solid fa-clock-rotate-left"></i>
            <h5 class="m-0">Appointment History</h5>
        </div>
        <div class="section-subtle">Past and upcoming visits</div>

        <div class="row g-2">
            <div class="col-lg-4"><input id="apSearch" class="form-control" placeholder="Search doctor or note..."></div>
            <div class="col-lg-3">
                <select id="apType" class="form-select">
                    <option value="">All Types</option>
                    <option>Video</option>
                    <option>Chat</option>
                    <option>In-person</option>
                </select>
            </div>
            <div class="col-lg-3">
                <input id="apDate" type="date" class="form-control">
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-3">
        @foreach ([['2025-03-03 10:30', 'Video', 'Dr. Don Joe', 'Follow-up on lab results'], ['2025-02-15 14:00', 'Chat', 'Dr. Kuyik Swiss', 'Headache consultation']] as $a)
            <div class="ap-row">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <div class="ap-when">{{ \Carbon\Carbon::parse($a[0])->format('M d, Y · g:ia') }}</div>
                        <div class="section-subtle">with <strong>{{ $a[2] }}</strong> — {{ $a[3] }}</div>
                        <div class="mt-2"><span class="ap-kind">{{ $a[1] }}</span></div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-light btn-sm">View Notes</button>
                        <button class="btn btn-gradient btn-sm">Book Again</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $('#apSearch, #apType, #apDate').on('input change', function() {
            // TODO: ajax filter
        });
    </script>
@endpush
