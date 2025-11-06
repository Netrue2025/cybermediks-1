{{-- expects: $order, $available, $unavailable, $items_total, $delivery_fee, $distance_km --}}
<div class="mb-2 fw-semibold">Available items</div>

@if ($available->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-borderless table-darkish align-middle mb-3">
            <thead>
                <tr>
                    <th>Drug</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($available as $a)
                    <tr>
                        <td>{{ $a['drug'] ?? '—' }}</td>
                        <td class="text-end">@money($a['unit_price'] ?? 0)</td>
                        <td class="text-end">@money($a['line_total'] ?? null ?: $a['unit_price'] ?? 0)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-warning mb-3">No matching items found in this pharmacy inventory.</div>
@endif

@if ($unavailable->isNotEmpty())
    <div class="mb-2 fw-semibold">Unavailable here</div>
    <ul class="mb-0 small">
        @foreach ($unavailable as $u)
            <li>
                {{ $u['drug'] ?? '—' }}
                @if (!empty($u['reason']))
                    — <span class="section-subtle">{{ $u['reason'] }}</span>
                @endif
            </li>
        @endforeach
    </ul>
@endif

<hr class="my-3" />

<div class="d-flex justify-content-between align-items-center">
    <div class="small section-subtle">
        Items: @money($items_total)
        · Delivery: @money($delivery_fee)
        @if ($distance_km)
            ({{ number_format($distance_km, 1) }} km @ @money(100)/km)
        @endif
    </div>
    <div class="fs-5 fw-bold" id="quoteTotal">@money($items_total)</div>
</div>

<div class="text-end mt-3">
    <button class="btn btn-success btn-sm" id="btnConfirmQuotedItems" {{ $items_total <= 0 ? 'disabled' : '' }}>
        Confirm Items (@money($items_total))
    </button>
</div>
