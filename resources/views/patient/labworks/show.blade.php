@extends('layouts.patient')
@section('title', 'Labwork ' . $lab->code)

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="cardx">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fw-bold">#{{ $lab->code }} — {{ $lab->lab_type }}</div>
                        <div class="text-secondary small">
                            Status: {{ ucwords(str_replace('_', ' ', $lab->status)) }}
                            @if ($lab->scheduled_at)
                                • Scheduled: {{ $lab->scheduled_at->format('M d, Y g:ia') }}
                            @endif
                            @if ($lab->price)
                                • Price: ${{ number_format($lab->price, 2) }}
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                <div class="small text-secondary">
                    <div><strong>Collection:</strong> {{ $lab->collection_method === 'home' ? 'Home' : 'In Lab' }}</div>
                    @if ($lab->address)
                        <div><strong>Address:</strong> {{ $lab->address }}</div>
                    @endif
                    @if ($lab->preferred_at)
                        <div><strong>Preferred:</strong> {{ $lab->preferred_at->format('M d, Y g:ia') }}</div>
                    @endif
                    @if ($lab->notes)
                        <div class="mt-2">{{ $lab->notes }}</div>
                    @endif
                </div>

                <hr>

                <div>
                    <strong>Provider:</strong>
                    @if ($lab->labtech)
                        {{ trim($lab->labtech->first_name . ' ' . $lab->labtech->last_name) ?: ($lab->labtech->name ?: $lab->labtech->email) }}
                    @else
                        —
                    @endif
                </div>

                @if ($lab->rejection_reason && $lab->status === 'rejected')
                    <div class="alert alert-warning mt-2 small">
                        Rejection reason: {{ $lab->rejection_reason }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="cardx">
                <div class="fw-bold mb-2">Actions</div>

                @if ($lab->canPatientCancel())
                    <form method="POST" action="{{ route('patient.labworks.cancel', $lab) }}" class="d-grid mb-2">
                        @csrf
                        <button class="btn btn-outline-danger">Cancel Request</button>
                    </form>
                @endif

                @if ($lab->results_path)
                    <a class="btn btn-success w-100" href="{{ route('patient.labworks.download', $lab) }}">
                        <i class="fa-regular fa-file-arrow-down me-1"></i> Download Results
                    </a>
                    @if ($lab->results_notes)
                        <div class="small text-secondary mt-2">{{ $lab->results_notes }}</div>
                    @endif
                @else
                    <div class="small text-secondary">No results uploaded yet.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
