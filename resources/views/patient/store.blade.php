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

        /* Uniform image area */
        .prod-img {
            background: #0b1222;
            height: 180px;
            /* fixed, consistent height */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9aa3b2;
        }

        .prod-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
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

        {{-- Search (GET, debounced submit) --}}
        <form class="row g-2 align-items-center mt-2" method="GET">
            <div class="col-lg-6">
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute"
                        style="left:.75rem;top:50%;transform:translateY(-50%);color:#9aa3b2"></i>
                    <input id="storeSearch" name="q" value="{{ $q ?? '' }}" style="padding-left:2.5rem"
                        class="form-control" placeholder="Search products...">
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3">
        @forelse ($products as $p)
            <div class="col-sm-6 col-lg-3">
                <div class="prod-card h-100 d-flex flex-column">
                    <div class="prod-img">
                        @if (!empty($p->image))
                            <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}">
                        @else
                            <i class="fa-solid fa-prescription-bottle-medical fs-3"></i>
                        @endif
                    </div>
                    <div class="p-3 d-flex flex-column gap-2">
                        <div class="fw-semibold">{{ $p->name }}</div>
                        <div class="section-subtle small">{{ \Illuminate\Support\Str::limit($p->description, 100) }}</div>
                        <div class="d-flex align-items-center justify-content-between mt-auto">
                            <div class="price">@money($p->price)</div>
                            @if ($p->link)
                                <a class="btn btn-sm btn-gradient" target="_blank" href="{{ $p->link }}">View
                                    Product</a>
                            @else
                                <span class="text-muted small">No link</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center section-subtle py-4">No products found</div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $products->onEachSide(1)->links() }}
    </div>
@endsection

@push('scripts')
    <script>
        // Debounced auto-submit for the GET search form
        (function() {
            const input = document.getElementById('storeSearch');
            if (!input) return;
            let t = null;
            input.addEventListener('input', function() {
                clearTimeout(t);
                t = setTimeout(() => {
                    // submit the closest form (the GET search form)
                    input.form && input.form.submit();
                }, 300);
            });
        })();
    </script>
@endpush
