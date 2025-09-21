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
                <select class="form-select" name="patient_id" required>
                    <option value="">Choose patient…</option>
                    <option value="1">Ebuka Mbanusi (#P001)</option>
                    <option value="2">Don Joe (#P002)</option>
                </select>
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
                        <input class="form-control" name="items[0][days]" placeholder="7">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Qty</label>
                        <input class="form-control" name="items[0][qty]" placeholder="14">
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
          <div class="col-lg-4"><label class="form-label">Drug</label><input class="form-control" name="items[${i}][drug]" required></div>
          <div class="col-lg-2"><label class="form-label">Dose</label><input class="form-control" name="items[${i}][dose]"></div>
          <div class="col-lg-2"><label class="form-label">Freq.</label><input class="form-control" name="items[${i}][freq]"></div>
          <div class="col-lg-2"><label class="form-label">Days</label><input class="form-control" name="items[${i}][days]"></div>
          <div class="col-lg-2"><label class="form-label">Qty</label><input class="form-control" name="items[${i}][qty]"></div>
        </div>
      </div>`);
        });

        $('#rxForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#btnIssue');
            lockBtn($btn);
            // TODO: AJAX post to store endpoint
            setTimeout(() => {
                flash('success', 'Prescription issued (demo)');
                unlockBtn($btn);
            }, 800);
        });
    </script>
@endpush
