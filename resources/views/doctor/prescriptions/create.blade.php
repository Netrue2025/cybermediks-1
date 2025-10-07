@extends('layouts.doctor')
@section('title', 'Create Prescription')

@push('styles')
    <style>
        .cardx {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .rx-item {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
        }
    </style>
@endpush

@section('content')
    <form id="rxForm" class="cardx">
        @csrf
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-file-medical"></i>
            <h5 class="m-0">New e-Prescription</h5>
        </div>
        <div class="text-secondary mb-3">Select patient and add medications.</div>

        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label">Patient</label>
                <input type="text" name="patient_id" hidden value="{{ $patient->id }}">
                <h3>{{ $patient->first_name }} {{ $patient->last_name }} (ID: {{ $patient->id }})</h3>

            </div>
            <div class="col-lg-3">
                <label class="form-label">Encounter Type</label>
                <select class="form-select" name="encounter" required>
                    <option value="video">Video</option>
                    <option value="chat">Chat</option>
                    <option value="in_person">In-person</option>
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Refills</label>
                <input type="number" min="0" value="0" class="form-control" name="refills">
            </div>
        </div>

        <hr class="my-4" style="border-color:var(--border);opacity:.6">

        <div id="rxItems" class="d-flex flex-column gap-2">
            <div class="rx-item">
                <div class="row g-2">
                    <div class="col-lg-4">
                        <label class="form-label">Drug</label>
                        <input class="form-control" name="items[0][drug]" placeholder="Amoxicillin 500mg" required>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Dose</label>
                        <input class="form-control" name="items[0][dose]" placeholder="1 tab">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Freq.</label>
                        <input class="form-control" name="items[0][freq]" placeholder="2×/day">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Days</label>
                        <input class="form-control" type="number" name="items[0][days]" placeholder="7">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Qty</label>
                        <input class="form-control" type="number" name="items[0][quantity]" placeholder="14">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-outline-light btn-sm" id="addItem"><i
                    class="fa-solid fa-plus me-1"></i>Add Item</button>
            <button class="btn btn-gradient ms-auto" id="btnIssue"><span class="btn-text">Issue
                    Prescription</span></button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        let rxIndex = 1;

        $('#addItem').on('click', function() {
            const i = rxIndex++;
            $('#rxItems').append(`
            <div class="rx-item">
                <div class="row g-2">
                <div class="col-lg-4"><label class="form-label">Drug</label><input class="form-control" name="items[${i}][drug]" placeholder="e.g., Amoxicillin 500mg" required></div>
                <div class="col-lg-2"><label class="form-label">Dose</label><input class="form-control" name="items[${i}][dose]" placeholder="1 tab"></div>
                <div class="col-lg-2"><label class="form-label">Freq.</label><input class="form-control" name="items[${i}][freq]" placeholder="2×/day"></div>
                <div class="col-lg-2"><label class="form-label">Days</label><input class="form-control" type="number" name="items[${i}][days]" placeholder="7"></div>
                <div class="col-lg-2"><label class="form-label">Qty</label><input class="form-control" type="number" name="items[${i}][quantity]" placeholder="14"></div>
                </div>
            </div>`);
        });

        $('#rxForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#btnIssue');
            lockBtn($btn);

            $.ajax({
                url: `{{ route('doctor.prescriptions.store') }}`,
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': `{{ csrf_token() }}`
                },
                success: function(res) {
                    flash('success', res.message || 'Prescription issued');
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    } else {
                        // fallback: clear the form for another entry
                        $('#rxForm')[0].reset();
                        $('#rxItems').html($('#rxItems .rx-item').first()); // keep first row
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to issue prescription';
                    flash('danger', msg);
                    // Optional: show validation errors
                    if (xhr.responseJSON?.errors) {
                        console.warn(xhr.responseJSON.errors);
                    }
                },
                complete: function() {
                    unlockBtn($btn);
                }
            });
        });
    </script>
@endpush
