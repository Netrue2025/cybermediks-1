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
                    <input id="pSearch" class="form-control" placeholder="Search by name, email, or ID..."
                        value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-lg-3">
                <select id="pFilter" class="form-select">
                    @php $curr = strtolower(request('filter','')); @endphp
                    <option value="" {{ $curr === '' ? 'selected' : '' }}>All</option>
                    <option value="recent" {{ $curr === 'recent' ? 'selected' : '' }}>Recent</option>
                    <option value="with active rx" {{ $curr === 'with active rx' ? 'selected' : '' }}>With active Rx</option>
                    <option value="follow-ups" {{ $curr === 'follow-ups' ? 'selected' : '' }}>Follow-ups</option>
                </select>
            </div>
        </div>
    </div>

    <div id="patientsList">
        @include('doctor.patients._list', ['patients' => $patients])
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const $list = $('#patientsList');
            let t = null;

            function fetchList() {
                $.get(`{{ route('doctor.patients') }}`, {
                    q: $('#pSearch').val(),
                    filter: $('#pFilter').val()
                }, function(html) {
                    $list.html(html);
                });
            }

            $('#pSearch').on('input', function() {
                clearTimeout(t);
                t = setTimeout(fetchList, 300);
            });
            $('#pFilter').on('change', fetchList);

            // Actions (wire up routes you have)
            $(document).on('click', '[data-history]', function() {
                const id = $(this).data('history');
                // e.g. go to a patient history page
                window.location.href = `/doctor/patient/${id}/history`;
            });
            $(document).on('click', '[data-consult]', function() {
                const id = $(this).data('consult');
                // e.g. open messenger with that patient
                window.location.href = `/doctor/messenger?patient_id=${id}`;
            });
        })();
    </script>
@endpush
