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
            text-transform: capitalize;
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
            <div class="col-lg-4">
                <input id="apSearch" class="form-control" placeholder="Search doctor or note..." value="{{ request('q') }}">
            </div>
            <div class="col-lg-3">
                <select id="apType" class="form-select">
                    <option value="">All Types</option>
                    <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Video</option>
                    <option value="chat" {{ request('type') === 'chat' ? 'selected' : '' }}>Chat</option>
                    <option value="in_person" {{ request('type') === 'in_person' ? 'selected' : '' }}>In-person</option>
                </select>
            </div>
            <div class="col-lg-3">
                <input id="apDate" type="date" class="form-control" value="{{ request('date') }}">
            </div>
        </div>
    </div>

    <div id="apList">
        @include('patient.appointments._list', ['appointments' => $appointments])
    </div>

    <!-- View Notes Modal -->
    <div class="modal fade" id="apNotesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:var(--card); color:var(--text);">
                <div class="modal-header">
                    <h6 class="modal-title">Visit Notes</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="apNotesBody" class="section-subtle"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const $list = $('#apList');
            let t = null;

            function fetchList() {
                const q = $('#apSearch').val();
                const type = $('#apType').val();
                const date = $('#apDate').val();
                $.get(`{{ route('patient.appointments.index') }}`, {
                    q,
                    type,
                    date
                }, function(html) {
                    $list.html(html);
                });
            }

            $('#apSearch').on('input', function() {
                clearTimeout(t);
                t = setTimeout(fetchList, 300); // debounce
            });
            $('#apType, #apDate').on('change', fetchList);

            // View notes (reads from data attr rendered in row)
            $(document).on('click', '[data-ap-notes]', function() {
                const notes = $(this).data('ap-notes') || 'No notes added.';
                $('#apNotesBody').text(notes);
                const modal = new bootstrap.Modal(document.getElementById('apNotesModal'));
                modal.show();
            });

            // Book again (you can adjust route)
            $(document).on('click', '[data-book-again]', function() {
                const doctorId = $(this).data('doctor-id');
                window.location.href = `/patient/appointments/create?doctor_id=${doctorId}`;
            });
        })();
    </script>
@endpush
