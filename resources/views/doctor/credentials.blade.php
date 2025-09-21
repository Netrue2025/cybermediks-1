@extends('layouts.doctor')
@section('title', 'Credential Management')

@push('styles')
    <style>
        .cardx {
            background: #121a2c;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .btn-gradient {
            background-image: linear-gradient(90deg, var(--accent1), var(--accent2));
            border: 0;
            color: #fff;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="cardx">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-badge-check"></i>
            <h5 class="m-0">Credential Management</h5>
        </div>
        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label">Credential Type</label>
                <select id="credType" class="form-select">
                    <option selected>Select credential type</option>
                    <option>Medical License</option>
                    <option>Board Certification</option>
                    <option>ID / Passport</option>
                </select>
            </div>
            <div class="col-lg-6">
                <label class="form-label">Document</label>
                <input id="credFile" type="file" class="form-control">
            </div>
            <div class="col-12 d-grid">
                <button class="btn btn-success" id="btnUpload"><span class="btn-text">Upload Credential</span></button>
            </div>
        </div>

        <hr class="my-4" style="border-color:var(--border);opacity:.6">

        <div class="text-secondary small mb-2">Uploaded Credentials</div>
        <div id="credList" class="text-secondary">No credentials uploaded yet.</div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#btnUpload').on('click', function() {
            const $btn = $(this);
            lockBtn($btn);
            const type = $('#credType').val();
            const file = $('#credFile')[0].files[0];
            if (!file || !type || type === 'Select credential type') {
                flash('danger', 'Choose type and file');
                return unlockBtn($btn);
            }
            // TODO: AJAX upload
            setTimeout(() => {
                $('#credList').html(
                    '<div class="d-flex justify-content-between border rounded p-2" style="border-color:var(--border)"><span>' +
                    type + ' — ' + file.name +
                    '</span><span class="badge bg-success">Pending review</span></div>');
                flash('success', 'Uploaded (demo)');
                unlockBtn($btn);
            }, 700);
        });
    </script>
@endpush
