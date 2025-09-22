@extends('layouts.pharmacy')
@section('title', 'Inventory')

@push('styles')
    <style>
        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px
        }

        .inv-row {
            display: grid;
            grid-template-columns: 1.4fr .8fr .6fr .6fr .8fr .8fr;
            gap: 10px;
            align-items: center;
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px
        }

        .inv-head {
            background: #101a2e;
            font-weight: 700;
            color: #b8c2d6
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem;
            background: #0e162b
        }

        .badge-low {
            background: rgba(245, 158, 11, .08);
            border-color: #856013
        }

        .btn-ico {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            background: #0d162a;
            color: #e5e7eb
        }

        .btn-ico:hover {
            filter: brightness(1.1)
        }

        @media(max-width: 1100px) {
            .inv-row {
                grid-template-columns: 1.4fr .8fr .6fr .6fr 1fr
            }
        }

        @media(max-width: 820px) {
            .inv-head {
                display: none
            }

            .inv-row {
                grid-template-columns: 1fr;
                gap: 8px
            }
        }
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <h5 class="m-0">Inventory</h5>
                </div>
                <div class="subtle">Manage stock, prices and reorder levels</div>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#itemModal">
                <i class="fa-solid fa-plus me-1"></i> New Item
            </button>
        </div>

        <form id="invFilter" class="row g-2 mt-2">
            <div class="col-lg-6"><input class="form-control" name="q" value="{{ $q ?? '' }}"
                    placeholder="Search name, SKU, unit…"></div>
            <div class="col-lg-3">
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" id="onlyLow" name="low" value="1"
                        {{ $low ?? false ? 'checked' : '' }}>
                    <label for="onlyLow" class="form-check-label">Only low stock</label>
                </div>
            </div>
            <div class="col-lg-3 d-grid">
                <button class="btn btn-gradient">Apply</button>
            </div>
        </form>
    </div>

    <div id="invList">@include('pharmacy.inventory._list', ['items' => $items])</div>

    {{-- New Item Modal --}}
    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:#121a2c;border:1px solid var(--border);border-radius:18px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Add Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="itemForm">
                        @csrf
                        <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control"
                                required></div>
                        <div class="mb-2"><label class="form-label">SKU</label><input name="sku" class="form-control">
                        </div>
                        <div class="mb-2"><label class="form-label">Unit (e.g., 500mg, 10 tabs)</label><input
                                name="unit" class="form-control"></div>
                        <div class="row g-2">
                            <div class="col"><label class="form-label">Price (USD)</label><input name="price"
                                    type="number" min="0" step="0.01" class="form-control" required></div>
                            <div class="col"><label class="form-label">Quantity</label><input name="qty"
                                    type="number" min="0" class="form-control" required></div>
                        </div>
                        <div class="mt-2"><label class="form-label">Reorder at</label><input name="reorder_level"
                                type="number" min="0" class="form-control"></div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-gradient" id="btnCreateItem"><span class="btn-text">Save Item</span></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const $list = $('#invList');

            $('#invFilter').on('submit', function(e) {
                e.preventDefault();
                $.get(`{{ route('pharmacy.inventory.index') }}`, $(this).serialize(), html => $list.html(html));
            });

            $('#btnCreateItem').on('click', function() {
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('pharmacy.inventory.store') }}`, $('#itemForm').serialize())
                    .done(res => {
                        flash('success', res.message || 'Item added');
                        $('#itemModal').modal('hide');
                        $('#invFilter').trigger('submit');
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });

            // inline price update
            $(document).on('change', '[data-price-edit]', function() {
                const id = $(this).data('price-edit'),
                    price = $(this).val();
                $.ajax({
                        url: `{{ url('/pharmacy/inventory') }}/${id}`,
                        method: 'PATCH',
                        data: {
                            _token: `{{ csrf_token() }}`,
                            price
                        }
                    })
                    .done(() => flash('success', 'Price updated'))
                    .fail(() => flash('danger', 'Update failed'));
            });

            // inline reorder update
            $(document).on('change', '[data-reorder-edit]', function() {
                const id = $(this).data('reorder-edit'),
                    reorder_level = $(this).val();
                $.ajax({
                        url: `{{ url('/pharmacy/inventory') }}/${id}`,
                        method: 'PATCH',
                        data: {
                            _token: `{{ csrf_token() }}`,
                            reorder_level
                        }
                    })
                    .done(() => flash('success', 'Reorder level updated'))
                    .fail(() => flash('danger', 'Update failed'));
            });

            // stock adjust
            $(document).on('click', '[data-adjust]', function() {
                const id = $(this).data('id');
                const delta = parseInt($(this).data('adjust'), 10);
                $.post(`{{ url('/pharmacy/inventory') }}/${id}/adjust`, {
                        _token: `{{ csrf_token() }}`,
                        delta
                    })
                    .done(res => {
                        flash('success', 'Stock adjusted');
                        // refresh row qty
                        $(`#qty-${id}`).text(res.qty);
                        $(`#low-${id}`).toggleClass('badge-low', res.low);
                    })
                    .fail(() => flash('danger', 'Adjust failed'));
            });

            // delete
            $(document).on('click', '[data-del]', function() {
                if (!confirm('Delete this item?')) return;
                const id = $(this).data('del');
                $.ajax({
                        url: `{{ url('/pharmacy/inventory') }}/${id}`,
                        method: 'DELETE',
                        data: {
                            _token: `{{ csrf_token() }}`
                        }
                    })
                    .done(() => {
                        flash('success', 'Deleted');
                        $('#invFilter').trigger('submit');
                    })
                    .fail(() => flash('danger', 'Delete failed'));
            });
        })();
    </script>
@endpush
