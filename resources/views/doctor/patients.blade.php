@extends('layouts.doctor')
@section('title', 'My Patients')

@push('styles')
    <style>
        .cardx {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .patient-row {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #14203a;
            color: #cfe0ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .chip {
            background: var(--chip);
            border: 1px solid var(--chipBorder);
            border-radius: 999px;
            padding: .2rem .55rem;
            color: #b8c2d6;
            font-size: .85rem;
        }

        .search-wrap {
            position: relative
        }

        .search-wrap .icon {
            position: absolute;
            left: .75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2;
        }

        .search-wrap input {
            padding-left: 2.3rem;
        }
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-users"></i>
            <h5 class="m-0">My Patients</h5>
        </div>
        <div class="text-secondary mt-1">Search, view history, and start a consultation.</div>

        <div class="row g-2 mt-2">
            <div class="col-lg-6">
                <div class="search-wrap">
                    <i class="fa-solid fa-magnifying-glass icon"></i>
                    <input id="pSearch" class="form-control" placeholder="Search by name, email, or ID...">
                </div>
            </div>
            <div class="col-lg-3">
                <select id="pFilter" class="form-select">
                    <option value="">All</option>
                    <option>Recent</option>
                    <option>With active Rx</option>
                    <option>Follow-ups</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column gap-2">
        @foreach ([['EM', 'Ebuka Mbanusi', 'ebuka@example.com', '3 visits'], ['DJ', 'Don Joe', 'don@example.com', '1 visit']] as $p)
            <div class="patient-row">
                <div class="avatar">{{ $p[0] }}</div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $p[1] }}</div>
                    <div class="text-secondary small">{{ $p[2] }}</div>
                    <span class="chip mt-1 d-inline-block">{{ $p[3] }}</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-light btn-sm"><i class="fa-regular fa-file-lines me-1"></i>
                        History</a>
                    <a href="#" class="btn btn-gradient btn-sm">Start Consult</a>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $('#pSearch,#pFilter').on('input change', function() {
            // TODO: AJAX filter
        });
    </script>
@endpush
