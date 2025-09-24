@php
    function statusBadge($s)
    {
        $s = $s ?: 'pending';
        $cls = match ($s) {
            'price_assigned' => 'badge-pending',
            'price_confirmed' => 'badge-ready',
            'ready' => 'badge-ready',
            'dispatcher_price_set' => 'badge-pending',
            'dispatcher_price_confirm' => 'badge-ready',
            'picked' => 'badge-picked',
            'delivered' => 'badge-picked',
            'cancelled' => 'badge-cancelled',
            default => 'badge-pending',
        };
        return "<span class='badge-soft {$cls}'>" . ucwords(str_replace('_', ' ', $s)) . '</span>';
    }
@endphp


@if ($prescriptions->isEmpty())
    <div class="text-center subtle py-4">No prescriptions found.</div>
@else
    <div class="rx-table">

        {{-- header --}}
        <div class="rx-head">
            <div>Rx</div>
            <div>Patient / Doctor</div>
            <div>Items</div>
            <div>Amount</div>
            <div>Status</div>
            <div>Actions</div>
        </div>

        {{-- rows --}}
        @foreach ($prescriptions as $rx)
            @php
                $itemText = $rx->items?->pluck('drug')->take(3)->implode(', ');
                if ($rx->items && $rx->items->count() > 3) {
                    $itemText .= '…';
                }
            @endphp
            <div class="rx-rowx">
                {{-- Rx --}}
                <div class="rx-col">
                    <div class="fw-semibold">#{{ $rx->code }}</div>
                    <div class="rx-cell-sub">{{ $rx->created_at->format('M d, Y · g:ia') }}</div>
                </div>

                {{-- Patient / Doctor --}}
                <div class="rx-col">
                    <div class="fw-semibold">
                        {{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}
                    </div>
                    <div class="rx-cell-sub">Dr. {{ $rx->doctor?->first_name }} {{ $rx->doctor?->last_name }}</div>
                </div>

                {{-- Items --}}
                <div class="rx-col">
                    <div class="rx-items">
                        {{ $itemText ?: '—' }}
                    </div>
                </div>

                {{-- Amount --}}
                @php
                    $canEditAmount = ($rx->dispense_status ?? 'pending') === 'pending';
                @endphp
                <div class="rx-col">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-icon" style="width:160px">
                            <span class="input-icon-prefix">$</span>
                            <input type="number" step="0.01" min="0" class="form-control"
                                data-amount-input="{{ $rx->id }}" value="{{ $rx->total_amount }}"
                                {{ $canEditAmount ? '' : 'disabled' }}>
                        </div>

                        @if ($canEditAmount)
                            <button class="btn btn-outline-light btn-sm" data-save-amount
                                data-id="{{ $rx->id }}">
                                Save
                            </button>
                        @else
                            <button class="btn btn-outline-light btn-sm" disabled>Save</button>
                        @endif
                    </div>

                    @if (($rx->dispense_status ?? 'pending') === 'price_assigned')
                        <div class="rx-cell-sub mt-1">Awaiting patient confirmation</div>
                    @endif
                </div>

                {{-- Status --}}
                <div class="rx-col">
                    {!! statusBadge($rx->dispense_status) !!}
                    @if ($rx->dispense_status === 'ready' && is_null($rx->dispatcher_id))
                        <div class="rx-cell-sub mt-1 text-warning">Dispatcher not assigned</div>
                    @endif
                    @if ($rx->dispatcher_id && $rx->dispatcher)
                        <div class="rx-cell-sub mt-1">
                            <i class="fa-solid fa-motorcycle me-1"></i>
                            Dispatcher: {{ $rx->dispatcher->first_name }} {{ $rx->dispatcher->last_name }}
                            @if ($rx->dispatcher->phone)
                                • <a class="link-light text-decoration-none"
                                    href="tel:{{ $rx->dispatcher->phone }}">{{ $rx->dispatcher->phone }}</a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="rx-col d-flex align-items-center gap-2 flex-wrap">
                    <a class="btn-ico" title="Open" href="{{ route('pharmacy.prescriptions.show', $rx) }}">
                        <i class="fa-regular fa-folder-open"></i>
                    </a>

                    @php $st = $rx->dispense_status ?? 'pending'; @endphp

                    @if ($st === 'pending')
                        {{-- enter price via Save button you already have, or cancel --}}
                        <button class="btn btn-outline-light btn-sm" data-id="{{ $rx->id }}"
                            data-status="cancelled">Cancel</button>
                    @elseif ($st === 'price_assigned')
                        {{-- waiting for patient confirmation; allow cancel --}}
                        <button class="btn btn-outline-light btn-sm" data-id="{{ $rx->id }}"
                            data-status="cancelled">Cancel</button>
                    @elseif ($st === 'price_confirmed')
                        {{-- pharmacy can mark ready or cancel --}}
                        <button class="btn btn-success btn-sm" data-status="ready" data-id="{{ $rx->id }}">
                            <i class="fa-solid fa-boxes-packing me-1"></i> Ready
                        </button>
                        <button class="btn btn-outline-light btn-sm" data-status="cancelled"
                            data-id="{{ $rx->id }}">Cancel</button>
                    @elseif ($st === 'ready')
                        {{-- dispatcher will set fee next; no picking yet --}}
                        <span class="rx-cell-sub">Waiting for dispatcher delivery quote</span>
                        <button class="btn btn-outline-light btn-sm" data-status="cancelled"
                            data-id="{{ $rx->id }}">Cancel</button>
                    @elseif ($st === 'dispatcher_price_set')
                        <span class="rx-cell-sub">Waiting for patient to confirm delivery fee</span>
                        <button class="btn btn-outline-light btn-sm" data-status="cancelled"
                            data-id="{{ $rx->id }}">Cancel</button>
                    @elseif ($st === 'dispatcher_price_confirm')
                        {{-- now pharmacy can mark picked (handoff) IF dispatcher assigned --}}
                        @php $hasDispatcher = !is_null($rx->dispatcher_id); @endphp
                        @if ($hasDispatcher)
                            <button class="btn btn-success btn-sm" data-status="picked" data-id="{{ $rx->id }}">
                                <i class="fa-solid fa-box-check me-1"></i> Mark Picked
                            </button>
                        @else
                            <button class="btn btn-success btn-sm" disabled
                                title="Assign a dispatcher before marking picked">
                                <i class="fa-solid fa-box-check me-1"></i> Mark Picked
                            </button>
                            <div class="rx-cell-sub w-100">Attach a dispatcher first.</div>
                        @endif
                        <button class="btn btn-outline-light btn-sm" data-status="cancelled"
                            data-id="{{ $rx->id }}">Cancel</button>
                    @elseif ($st === 'picked')
                        <span class="rx-cell-sub">With dispatcher — awaiting delivery</span>
                    @elseif ($st === 'delivered')
                        <span class="rx-cell-sub">Delivered</span>
                    @elseif ($st === 'cancelled')
                        <span class="rx-cell-sub">No actions</span>
                    @else
                        <span class="rx-cell-sub">No actions</span>
                    @endif
                </div>


            </div>
        @endforeach
    </div>

    @if ($prescriptions->hasPages())
        <div class="mt-3">{!! $prescriptions->links() !!}</div>
    @endif
@endif
