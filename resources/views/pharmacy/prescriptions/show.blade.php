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

        .badge-delivered {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .22)
        }

        .rx-item {
            background: #0c1529;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
        }

        .hint {
            color: #9aa3b2;
            font-size: .85rem
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
                        @php $st = $rx->dispense_status ?? 'pending'; @endphp
                        <span class="badge-soft badge-{{ $st }}">{{ ucwords(str_replace('_', ' ', $st)) }}</span>
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

                {{-- Amount (editable only when pending) --}}
                @php $st = $rx->dispense_status ?? 'pending'; @endphp
                <div class="mb-2">
                    <label class="form-label small subtle">Total Amount</label>
                    <div class="input-icon">
                        <span class="input-icon-prefix">$</span>
                        <input class="form-control" id="totalAmount" type="number" step="0.01" min="0"
                            value="{{ $rx->total_amount }}" {{ $st === 'pending' ? '' : 'disabled' }}>
                    </div>
                    <button class="btn btn-outline-light btn-sm mt-2" id="btnSaveAmount"
                        {{ $st === 'pending' ? '' : 'disabled' }}>
                        Save Amount
                    </button>
                    @if ($st === 'price_assigned')
                        <div class="hint mt-1">Waiting for patient to confirm price.</div>
                    @endif
                </div>

                {{-- Dispatcher context --}}
                @if ($rx->dispatcher_id && $rx->dispatcher)
                    <div class="hint mb-2">
                        <i class="fa-solid fa-motorcycle me-1"></i>
                        Dispatcher: {{ $rx->dispatcher->first_name }} {{ $rx->dispatcher->last_name }}
                        @if ($rx->dispatcher->phone)
                            • <a class="link-light text-decoration-none"
                                href="tel:{{ $rx->dispatcher->phone }}">{{ $rx->dispatcher->phone }}</a>
                        @endif
                        @if (!is_null($rx->dispatcher_price))
                            • Delivery fee: ${{ number_format($rx->dispatcher_price, 2, '.', ',') }}
                        @endif
                    </div>
                @endif

                {{-- Actions by status --}}
                <div class="mt-3 d-flex flex-wrap gap-2">
                    @if ($st === 'pending')
                        {{-- set amount (Save) then wait for patient (price_assigned) --}}
                        <button class="btn btn-outline-light" data-status="cancelled">Cancel</button>
                    @elseif ($st === 'price_assigned')
                        {{-- patient must confirm --}}
                        <button class="btn btn-outline-light" data-status="cancelled">Cancel</button>
                    @elseif ($st === 'price_confirmed')
                        {{-- pharmacy can make it ready --}}
                        <button class="btn btn-success" data-status="ready">
                            <i class="fa-solid fa-boxes-packing me-1"></i> Mark Ready
                        </button>
                        <button class="btn btn-outline-light" data-status="cancelled">Cancel</button>
                    @elseif ($st === 'ready')
                        <div class="hint">Waiting for dispatcher to propose delivery fee.</div>
                        <button class="btn btn-outline-light" data-status="cancelled">Cancel</button>
                    @elseif ($st === 'dispatcher_price_set')
                        <div class="hint">Delivery fee proposed; waiting for patient confirmation.</div>
                        <button class="btn btn-outline-light" data-status="cancelled">Cancel</button>
                    @elseif ($st === 'dispatcher_price_confirm')
                        @php $hasDispatcher = !is_null($rx->dispatcher_id); @endphp
                        @if ($hasDispatcher)
                            <button class="btn btn-success" data-status="picked">
                                <i class="fa-solid fa-box-check me-1"></i> Mark Picked
                            </button>
                        @else
                            <button class="btn btn-success" disabled title="Assign a dispatcher first">
                                <i class="fa-solid fa-box-check me-1"></i> Mark Picked
                            </button>
                            <div class="w-100 hint">Attach a dispatcher before marking picked.</div>
                        @endif
                        <button class="btn btn-outline-light" data-status="cancelled">Cancel</button>
                    @elseif ($st === 'picked')
                        <div class="hint">With dispatcher — awaiting delivery.</div>
                    @elseif ($st === 'delivered')
                        <div class="hint">Delivered — no further actions.</div>
                    @elseif ($st === 'cancelled')
                        <div class="hint">Cancelled — no further actions.</div>
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
                if ($btn.is(':disabled')) return;
                lockBtn($btn);
                $.post(`{{ route('pharmacy.prescriptions.amount', $rx) }}`, {
                        _token: `{{ csrf_token() }}`,
                        amount: $('#totalAmount').val()
                    })
                    .done(res => {
                        // After saving amount while status=pending, backend should move to price_assigned.
                        flash('success', res.message || 'Amount saved (awaiting patient confirmation)');
                        location.reload();
                    })
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
