@extends('layouts.pharmacy')
@section('title', 'Order #O' . $order->id)

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
            gap: .35rem;
            padding: .25rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem;
            background: #0e162b
        }

        .b-good {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .08)
        }

        .b-warn {
            border-color: #7a5a1b;
            background: rgba(245, 158, 11, .08)
        }

        .b-bad {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .08)
        }

        .b-pending {
            border-color: #334155
        }

        .order-item {
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
                        <div class="fw-bold">Rx {{ $order->prescription?->code }}</div>
                        <div class="subtle small">
                            {{ $order->created_at->format('M d, Y · g:ia') }}
                            • Doctor: {{ $order->prescription?->doctor?->first_name }}
                            {{ $order->prescription?->doctor?->last_name }}
                            • Patient: {{ $order->prescription?->patient?->first_name }}
                            {{ $order->prescription?->patient?->last_name }}
                        </div>
                    </div>
                    <div>
                        @php
                            $st = $order->status ?? 'pending';
                            $cls = match ($st) {
                                'quoted', 'dispatcher_price_set' => 'b-warn',
                                'patient_confirmed',
                                'pharmacy_accepted',
                                'ready',
                                'dispatcher_price_confirm',
                                'picked',
                                'delivered'
                                    => 'b-good',
                                'rejected' => 'b-bad',
                                default => 'b-pending',
                            };
                        @endphp
                        <span class="badge-soft {{ $cls }}">{{ ucwords(str_replace('_', ' ', $st)) }}</span>
                    </div>
                </div>

                <hr class="my-3" style="border-color:var(--border);opacity:.6">

                <div class="d-flex flex-column gap-2">
                    @forelse($order->items as $it)
                        <div class="order-item">
                            <div class="fw-semibold">{{ $it->drug }}</div>
                            <div class="subtle small">
                                Qty: {{ $it->quantity ?? 1 }}
                                @if (!is_null($it->unit_price))
                                    • Unit: ₦{{ number_format($it->unit_price, 2, '.', ',') }}
                                @endif
                                @if (!is_null($it->line_total))
                                    • Line: ₦{{ number_format($it->line_total, 2, '.', ',') }}
                                @endif
                                @if ($it->status)
                                    • {{ ucwords(str_replace('_', ' ', $it->status)) }}
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
                    <div class="subtle small">Items Subtotal</div>
                    <div class="fs-5 fw-bold">₦{{ number_format((float) ($order->items_subtotal ?? 0), 2, '.', ',') }}</div>
                    @if ($order->status === 'quoted')
                        <div class="hint mt-1">Waiting for your decision (accept / reject).</div>
                    @elseif ($order->status === 'patient_confirmed')
                        <div class="hint mt-1">Patient confirmed — you can mark Ready.</div>
                    @endif
                </div>

                {{-- Dispatcher context (if using prescription fields for now) --}}
                @if ($order->prescription?->dispatcher_id && $order->dispatcher)
                    <div class="hint mb-2">
                        <i class="fa-solid fa-motorcycle me-1"></i>
                        Dispatcher: {{ $order->dispatcher->first_name }} {{ $order->dispatcher->last_name }}
                        @if ($order->dispatcher->phone)
                            • <a class="link-light text-decoration-none"
                                href="tel:{{ $order->dispatcher->phone }}">{{ $order->dispatcher->phone }}</a>
                        @endif
                        @if (!is_null($order->prescription->dispatcher_price))
                            • Delivery fee: ₦{{ number_format($order->prescription->dispatcher_price, 2, '.', ',') }}
                        @endif
                    </div>
                @endif

                <div class="mt-3 d-flex flex-wrap gap-2">
                    @if ($st === 'quoted')
                        <button class="btn btn-success" data-status="pharmacy_accepted"><i
                                class="fa-solid fa-check me-1"></i> Accept</button>
                        <button class="btn btn-outline-light" data-status="rejected">Reject</button>
                    @elseif ($st === 'patient_confirmed')
                        <button class="btn btn-success" data-status="ready"><i class="fa-solid fa-boxes-packing me-1"></i>
                            Mark Ready</button>
                        <button class="btn btn-outline-light" data-status="rejected">Reject</button>
                    @elseif ($st === 'dispatcher_price_confirm')
                        <button class="btn btn-success" data-status="picked"><i class="fa-solid fa-box-check me-1"></i> Mark
                            Picked</button>
                    @elseif (in_array($st, ['picked', 'delivered', 'rejected']))
                        <div class="hint">No further actions.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const id = `{{ $order->id }}`;

            $('[data-status]').on('click', function() {
                const to = $(this).data('status');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('pharmacy.orders.status', $order) }}`, {
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
