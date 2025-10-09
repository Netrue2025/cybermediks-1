@php
    function orderBadge($s)
    {
        $s = $s ?: 'pending';
        $cls = match ($s) {
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
        return "<span class='badge-soft {$cls}'>" . ucwords(str_replace('_', ' ', $s)) . '</span>';
    }
@endphp

@if ($orders->isEmpty())
    <div class="text-center subtle py-4">No orders found.</div>
@else
    <div class="order-table">
        <div class="order-head">
            <div>Rx</div>
            <div>Patient / Doctor</div>
            <div>Items</div>
            <div>Subtotal</div>
            <div>Status</div>
            <div>Actions</div>
        </div>

        @foreach ($orders as $order)
            @php
                $rx = $order->prescription;
                $itemText = $order->items?->pluck('drug')->take(3)->implode(', ');
                if ($order->items && $order->items->count() > 3) {
                    $itemText .= '…';
                }
            @endphp
            <div class="order-row">
                <div class="col">
                    <div class="fw-semibold">#{{ $rx?->code }}</div>
                    <div class="cell-sub">{{ $order->created_at->format('M d, Y · g:ia') }}</div>
                </div>
                <div class="col">
                    <div class="fw-semibold">{{ $rx?->patient?->first_name }} {{ $rx?->patient?->last_name }}</div>
                    <div class="cell-sub">Dr. {{ $rx?->doctor?->first_name }} {{ $rx?->doctor?->last_name }}</div>
                </div>
                <div class="col">
                    <div class="items-ellipsis">{{ $itemText ?: '—' }}</div>
                </div>
                <div class="col">
                    <strong>${{ number_format((float) ($order->items_subtotal ?? 0), 2, '.', ',') }}</strong>
                    @if ($order->status === 'quoted')
                        <div class="cell-sub mt-1">Waiting for your decision</div>
                    @elseif ($order->status === 'patient_confirmed')
                        <div class="cell-sub mt-1">Patient confirmed</div>
                    @endif
                </div>
                <div class="col">{!! orderBadge($order->status) !!}</div>
                <div class="col d-flex align-items-center gap-2 flex-wrap">
                    <a class="btn-ico" title="Open" href="{{ route('pharmacy.orders.show', $order) }}"><i
                            class="fa-regular fa-folder-open"></i></a>

                    @if ($order->status === 'quoted')
                        <button class="btn btn-success btn-sm" data-status="pharmacy_accepted"
                            data-id="{{ $order->id }}">Accept</button>
                        <button class="btn btn-outline-light btn-sm" data-status="rejected"
                            data-id="{{ $order->id }}">Reject</button>
                    @elseif ($order->status === 'patient_confirmed')
                        <button class="btn btn-success btn-sm" data-status="ready" data-id="{{ $order->id }}"><i
                                class="fa-solid fa-boxes-packing me-1"></i> Ready</button>
                        <button class="btn btn-outline-light btn-sm" data-status="rejected"
                            data-id="{{ $order->id }}">Reject</button>
                    @elseif ($order->status === 'dispatcher_price_confirm')
                        <button class="btn btn-outline-light btn-sm" data-status="picked"
                            data-id="{{ $order->id }}">
                            <i class="fa-solid fa-person-walking-dashed-line-arrow-right me-1"></i> Mark
                            Picked
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if ($orders->hasPages())
        <div class="mt-3">{{ $orders->onEachSide(1)->links() }}</div>
    @endif
@endif
