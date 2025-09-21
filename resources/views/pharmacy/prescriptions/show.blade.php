@extends('layouts.pharmacy')
@section('title', 'Prescription ' . $rx->code)

@push('styles')
    <style>
        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .block {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem;
            background: #0e162b
        }

        .badge-ready {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .08)
        }

        .badge-picked {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .16)
        }

        .badge-pending {
            border-color: #334155
        }

        .badge-cancelled {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .08)
        }

        .rx-item {
            background: #0c1529;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="cardx">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold">Rx {{ $rx->code }}</div>
                        <div class="subtle small">
                            {{ $rx->created_at->format('M d, Y · g:ia') }}
                            • Doctor: {{ $rx->doctor?->first_name }} {{ $rx->doctor?->last_name }}
                            • Patient: {{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}
                        </div>
                    </div>
                    <div>
                        <span class="badge-soft badge-{{ $rx->dispense_status }}">{{ ucfirst($rx->dispense_status) }}</span>
                    </div>
                </div>

                <hr class="my-3" style="border-color:var(--border);opacity:.6">

                <div class="d-flex flex-column gap-2">
                    @forelse($rx->items as $it)
                        <div class="rx-item">
                            <div class="fw-semibold">{{ $it->drug }}</div>
                            <div class="subtle small">
                                @if ($it->dose)
                                    Dose: {{ $it->dose }} •
                                @endif
                                @if ($it->frequency)
                                    {{ $it->frequency }} •
                                @endif
                                @if ($it->days)
                                    {{ $it->days }} •
                                @endif
                                @if ($it->quantity)
                                    Qty: {{ $it->quantity }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="subtle">No items listed.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="cardx">
                <div class="fw-bold mb-2">Fulfillment</div>

                <div class="mb-2">
                    <label class="form-label small subtle">Total Amount</label>
                    <div class="input-icon">
                        <span class="input-icon-prefix">$</span>
                        <input class="form-control" id="totalAmount" type="number" step="0.01" min="0"
                            value="{{ $rx->total_amount }}">
                    </div>
                    <button class="btn btn-outline-light btn-sm mt-2" id="btnSaveAmount">Save Amount</button>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    @if ($rx->dispense_status === 'pending')
                        <button class="btn btn-success" data-status="ready">Mark Ready</button>
                        <button class="btn btn-outline-light" data-status="cancelled">Cancel</button>
                    @elseif($rx->dispense_status === 'ready')
                        <button class="btn btn-outline-light" data-status="pending">Back to Pending</button>
                        <button class="btn btn-success" data-status="picked">Mark Picked</button>
                        <button class="btn btn-outline-light" data-status="cancelled">Cancel</button>
                    @else
                        <div class="subtle">No further actions.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const id = `{{ $rx->id }}`;

            $('#btnSaveAmount').on('click', function() {
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('pharmacy.prescriptions.amount', $rx) }}`, {
                        _token: `{{ csrf_token() }}`,
                        total_amount: $('#totalAmount').val()
                    })
                    .done(res => flash('success', res.message || 'Amount saved'))
                    .fail(err => flash('danger', err.responseJSON?.message || 'Failed'))
                    .always(() => unlockBtn($btn));
            });

            $('[data-status]').on('click', function() {
                const to = $(this).data('status');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('pharmacy.prescriptions.status', $rx) }}`, {
                        _token: `{{ csrf_token() }}`,
                        status: to
                    })
                    .done(res => {
                        flash('success', res.message || 'Updated');
                        location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });

        })();
    </script>
@endpush
