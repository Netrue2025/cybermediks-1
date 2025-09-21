@php
    function statusBadge($s)
    {
        $cls = match ($s) {
            'pending' => 'badge-pending',
            'ready' => 'badge-ready',
            'picked' => 'badge-picked',
            'cancelled' => 'badge-cancelled',
            default => 'badge-pending',
        };
        return "<span class='badge-soft {$cls}'>" . ucfirst($s ?? 'pending') . '</span>';
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
                <div class="rx-col">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-icon" style="width:160px">
                            <span class="input-icon-prefix">$</span>
                            <input type="number" step="0.01" min="0" class="form-control"
                                data-amount-input="{{ $rx->id }}" value="{{ $rx->total_amount }}">
                        </div>
                        <button class="btn btn-outline-light btn-sm" data-save-amount
                            data-id="{{ $rx->id }}">Save</button>
                    </div>
                </div>

                {{-- Status --}}
                <div class="rx-col">{!! statusBadge($rx->dispense_status) !!}</div>

                {{-- Actions --}}
                <div class="rx-col d-flex align-items-center gap-2 flex-wrap">
                    <a class="btn-ico" title="Open" href="{{ route('pharmacy.prescriptions.show', $rx) }}">
                        <i class="fa-regular fa-folder-open"></i>
                    </a>

                    @if ($rx->dispense_status === 'pending')
                        <button class="btn btn-success btn-sm" data-status data-id="{{ $rx->id }}"
                            data-status="ready">
                            <i class="fa-solid fa-boxes-packing me-1"></i> Ready
                        </button>
                        <button class="btn btn-outline-light btn-sm" data-status data-id="{{ $rx->id }}"
                            data-status="cancelled">
                            Cancel
                        </button>
                    @elseif($rx->dispense_status === 'ready')
                        <button class="btn btn-outline-light btn-sm" data-status data-id="{{ $rx->id }}"
                            data-status="pending">
                            Back
                        </button>
                        <button class="btn btn-success btn-sm" data-status data-id="{{ $rx->id }}"
                            data-status="picked">
                            Picked
                        </button>
                        <button class="btn btn-outline-light btn-sm" data-status data-id="{{ $rx->id }}"
                            data-status="cancelled">
                            Cancel
                        </button>
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
