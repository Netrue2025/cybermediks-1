@extends('layouts.patient')
@section('title', 'Online Store')

@push('styles')
    <style>
        .prod-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .prod-img {
            background: #0b1222;
            aspect-ratio: 4/3;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9aa3b2;
        }

        .price {
            font-weight: 800;
        }

        .filter-pill {
            background: #0f172a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .3rem .7rem;
        }

    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-store"></i>
            <h5 class="m-0">Online Store</h5>
        </div>
        <i class="section-subtle">Order medications and essentials</i>

        <div class="row g-2 align-items-center mt-2">
            <div class="col-lg-6">
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute"
                        style="left:.75rem;top:50%;transform:translateY(-50%);color:#9aa3b2"></i>
                    <input style="padding-left: 2.5rem;" id="storeSearch" class="form-control" placeholder="Search products...">
                </div>
            </div>
            <div class="col-lg-3">
                <select id="storeCat" class="form-select">
                    <option value="">All Categories</option>
                    <option>OTC</option>
                    <option>Prescription</option>
                    <option>Devices</option>
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2 justify-content-lg-end">
                <span class="filter-pill">In stock</span>
                <span class="filter-pill">Fast delivery</span>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @foreach (range(1, 8) as $i)
            <div class="col-sm-6 col-lg-3">
                <div class="prod-card h-100 d-flex flex-column">
                    <div class="prod-img">
                        <i class="fa-solid fa-prescription-bottle-medical"></i>
                    </div>
                    <div class="p-3 d-flex flex-column gap-2">
                        <div class="fw-semibold">Product {{ $i }}</div>
                        <div class="section-subtle small">Short description goes here.</div>
                        <div class="d-flex align-items-center justify-content-between mt-auto">
                            <div class="price">$ {{ number_format(9.99 + $i, 2) }}</div>
                            <button class="btn btn-sm btn-gradient">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        $('#storeSearch, #storeCat').on('input change', function() {
            // TODO: ajax filter
        });
    </script>
@endpush
