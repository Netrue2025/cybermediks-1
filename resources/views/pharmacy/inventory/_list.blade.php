@if ($items->isEmpty())
    <div class="text-center subtle py-4">No items found.</div>
@else
    <div class="d-flex flex-column gap-2">
        <div class="inv-row inv-head">
            <div>Name / SKU</div>
            <div>Unit</div>
            <div>Price</div>
            <div>Qty</div>
            <div>Reorder</div>
            <div>Actions</div>
        </div>
        @foreach ($items as $it)
            <div class="inv-row">
                <div>
                    <div class="fw-semibold">{{ $it->name }}</div>
                    <div class="subtle small">{{ $it->sku ?: '—' }}</div>
                </div>
                <div>{{ $it->unit ?: '—' }}</div>
                <div>
                    <div class="input-icon">
                        <span class="input-icon-prefix">$</span>
                        <input data-price-edit="{{ $it->id }}" type="number" step="0.01" min="0"
                            class="form-control" value="{{ $it->price }}">
                    </div>
                </div>
                <div>
                    <span id="qty-{{ $it->id }}" class="fw-semibold">{{ $it->qty }}</span>
                    <span id="low-{{ $it->id }}" class="badge-soft {{ $it->low_stock ? 'badge-low' : '' }}">
                        {{ $it->low_stock ? 'Low' : 'OK' }} </span>
                    <div class="mt-1 d-flex gap-1">
                        <button class="btn-ico" data-adjust data-id="{{ $it->id }}" data-adjust="-1"
                            title="-1"><i class="fa-solid fa-minus"></i></button>
                        <button class="btn-ico" data-adjust data-id="{{ $it->id }}" data-adjust="+1"
                            title="+1"><i class="fa-solid fa-plus"></i></button>
                        <button class="btn-ico" data-adjust data-id="{{ $it->id }}" data-adjust="10"
                            title="+10"><i class="fa-solid fa-plus"></i>10</button>
                    </div>
                </div>
                <div>
                    <input data-reorder-edit="{{ $it->id }}" type="number" min="0" class="form-control"
                        value="{{ $it->reorder_level }}">
                </div>
                <div class="d-flex gap-1">
                    <a class="btn-ico" title="Edit" onclick="alert('Use inline fields above');"><i
                            class="fa-regular fa-pen-to-square"></i></a>
                    <button class="btn-ico" title="Delete" data-del="{{ $it->id }}"><i
                            class="fa-regular fa-trash-can"></i></button>
                </div>
            </div>
        @endforeach
    </div>

    @if ($items->hasPages())
        <div class="mt-3">{!! $items->links() !!}</div>
    @endif
@endif
