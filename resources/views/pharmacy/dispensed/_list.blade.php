@if ($prescriptions->isEmpty())
    <div class="text-center subtle py-4">No dispensed orders found.</div>
@else
    <div class="d-flex flex-column gap-2">
        <div class="rx-row rx-head">
            <div>Rx</div>
            <div>Patient / Doctor</div>
            <div>Items</div>
            <div>Amount</div>
            <div>Picked / Actions</div>
        </div>

        @foreach ($prescriptions as $rx)
            @php
                $items = $rx->items?->map(fn($i) => $i->medicine ?? ($i->drug ?? null))->filter();
                $preview = $items->take(3)->implode(', ');
                if ($items->count() > 3) {
                    $preview .= '…';
                }
                $amount = is_null($rx->total_amount)
                    ? '—'
                    : '$' . number_format((float) $rx->total_amount, 2, '.', ',');
                // If you have a picked_at column, use it; else updated_at is when status changed
                $pickedAt =
                    method_exists($rx, 'getAttribute') && $rx->getAttribute('picked_at')
                        ? $rx->picked_at->format('M d, Y · g:ia')
                        : $rx->updated_at?->format('M d, Y · g:ia');
            @endphp
            <div class="rx-row">
                <div>
                    <div class="fw-semibold">#{{ $rx->code }}</div>
                    <div class="subtle small">{{ $rx->created_at?->format('M d, Y') }}</div>
                </div>
                <div>
                    <div class="fw-semibold">{{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}</div>
                    <div class="subtle small">Doctor: {{ $rx->doctor?->first_name }} {{ $rx->doctor?->last_name }}</div>
                </div>
                <div class="subtle">{{ $preview ?: '—' }}</div>
                <div class="fw-semibold">{{ $amount }}</div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-soft"><i class="fa-solid fa-circle-check" style="color:#22c55e"></i>
                        {{ $pickedAt }}</span>
                    <a class="btn-ico" title="Receipt" href="{{ route('pharmacy.dispensed.receipt', $rx) }}"><i
                            class="fa-regular fa-file-lines"></i></a>
                    <button class="btn-ico" title="Undo to Ready" data-undo="{{ $rx->id }}"><i
                            class="fa-solid fa-rotate-left"></i></button>
                </div>
            </div>
        @endforeach
    </div>

    @if ($prescriptions->hasPages())
        <div class="mt-3">{!! $prescriptions->links() !!}</div>
    @endif
@endif
