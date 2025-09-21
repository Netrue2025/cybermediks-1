@extends('layouts.doctor')
@section('title', 'Video Call Queue')

@push('styles')
    <style>
        .cardx {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .empty {
            color: #9aa3b2;
            text-align: center;
            padding: 32px 8px;
        }
    </style>
@endpush

@section('content')
    <div class="cardx">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-regular fa-folder-open"></i>
            <h5 class="m-0">Video Call Queue</h5>
        </div>
        <div class="empty">
            <div class="mb-2"><i class="fa-solid fa-users fs-4"></i></div>
            No patients in the video call queue.
        </div>
    </div>
@endsection
