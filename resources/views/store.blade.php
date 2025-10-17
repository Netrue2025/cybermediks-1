@extends('layouts.app')
@section('title', 'Online Store')

@push('styles')
    <style>
        :root {
            --bg: #0b1222;
            --card: #0e1629;
            --panel: #0c1426;
            --border: #202a3d;
            --muted: #9aa3b2;
            --text: #e5eaf3;
            --accent: #6ee7b7;
        }

        .store-wrap {
            display: grid;
            gap: 12px
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 4;
            background: linear-gradient(180deg, rgba(11, 18, 34, .9), rgba(11, 18, 34, .7));
            backdrop-filter: blur(8px);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
        }

        .toolbar .form-control,
        .toolbar .form-select {
            background: var(--panel);
            border: 1px solid var(--border);
            color: var(--text);
        }

        .toolbar .form-control::placeholder {
            color: #7d8796
        }

        .filter-pill {
            background: #0f172a;
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 999px;
            padding: .35rem .8rem;
            font-size: .85rem;
            cursor: pointer;
        }

        .filter-pill.active {
            outline: 1px solid #2c3f64;
            background: #101a2e
        }

        .prod-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s;
        }

        .prod-card:hover {
            transform: translateY(-2px);
            border-color: #2a3a56;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .25)
        }

        .prod-media {
            position: relative;
            background: #0b1222;
            aspect-ratio: 4/3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .prod-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .badge-top {
            position: absolute;
            left: 10px;
            top: 10px;
            background: rgba(0, 0, 0, .45);
            color: #dbe8ff;
            border: 1px solid rgba(255, 255, 255, .15);
            padding: .25rem .5rem;
            border-radius: 999px;
            font-size: .75rem;
        }

        .fav-btn {
            position: absolute;
            right: 10px;
            top: 10px;
            border: none;
            background: rgba(0, 0, 0, .35);
            color: #fff;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: grid;
            place-items: center;
        }

        .prod-body {
            padding: 12px
        }

        .prod-name {
            font-weight: 700;
            line-height: 1.25
        }

        .prod-desc {
            color: var(--muted);
            font-size: .9rem;
            min-height: 2.4em
        }

        .price {
            font-weight: 800;
            font-size: 1.05rem
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--border);
            color: #e7eefc;
        }

        .btn-ghost:hover {
            border-color: #314363;
            background: #101a2e
        }

        .btn-gradient {
            background: linear-gradient(90deg, #1f9d6b, #2dbfa0);
            border: 0
        }

        /* Empty / skeleton */
        .empty {
            border: 1px dashed var(--border);
            border-radius: 14px;
            padding: 2rem;
            text-align: center;
            color: var(--muted);
            background: #0c1426;
        }

        .skeleton {
            background: linear-gradient(90deg, #0f172a 0%, #111b30 50%, #0f172a 100%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: 0 0
            }

            100% {
                background-position: -200% 0
            }
        }

        /* Pagination tune */
        .pagination .page-link {
            background: #0f172a;
            border-color: var(--border);
            color: #cfe0ff
        }

        .pagination .page-item.active .page-link {
            background: #1b2a4a;
            border-color: #2f3d58
        }
    </style>
@endpush

@section('content')
    <div class="store-wrap">

        <br><br><br>

        {{-- Sticky toolbar: Search + Sort (+ optional category pills) --}}
        <div class="toolbar">
            <form class="row g-2 align-items-center" method="GET">
                <div class="col-12 col-md-7">
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute"
                            style="left:.75rem;top:50%;transform:translateY(-50%);color:#9aa3b2"></i>
                        <input id="storeSearch" name="q" value="{{ $q ?? '' }}" style="padding-left:2.5rem"
                            class="form-control" placeholder="Search products...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" name="sort" onchange="this.form.submit()">
                        @php $sort = request('sort') @endphp
                        <option value="">Sort: Featured</option>
                        <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low → High</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High → Low</option>
                        <option value="new" {{ $sort === 'new' ? 'selected' : '' }}>Newest</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex justify-content-end">
                    <button class="btn btn-ghost w-100" type="submit">
                        <i class="fa-solid fa-sliders me-1"></i> Apply
                    </button>
                </div>
            </form>

            @isset($categories)
                <div class="mt-2 d-flex flex-wrap gap-2">
                    @foreach ($categories as $cat)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $cat->slug]) }}"
                            class="filter-pill {{ request('category') === $cat->slug ? 'active' : '' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            @endisset
        </div>

        {{-- Product grid --}}
        <div class="row g-3">
            @forelse ($products as $p)
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <div class="prod-card d-flex flex-column">
                        <div class="prod-media">
                            @if (!empty($p->image))
                                <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" loading="lazy">
                            @else
                                <i class="fa-solid fa-prescription-bottle-medical fs-2" style="color:#9aa3b2"></i>
                            @endif

                            {{-- Optional top badges --}}
                            @if (!empty($p->is_featured))
                                <span class="badge-top"><i class="fa-solid fa-star me-1"></i> Featured</span>
                            @endif
                            <button class="fav-btn" type="button" title="Save">
                                <i class="fa-regular fa-bookmark"></i>
                            </button>
                        </div>

                        <div class="prod-body d-flex flex-column gap-2">
                            <div class="prod-name">{{ $p->name }}</div>
                            <div class="prod-desc">{{ \Illuminate\Support\Str::limit($p->description, 90) }}</div>

                            <div class="d-flex align-items-center justify-content-between mt-auto">
                                <div class="price">
                                    ₦ {{ number_format((float) $p->price, 2) }}
                                </div>

                                @if ($p->link)
                                    <a class="btn btn-sm btn-gradient" target="_blank" href="{{ $p->link }}">
                                        View Product
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-ghost" disabled>No link</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty">
                        <div class="mb-2"><i class="fa-regular fa-face-frown fs-3"></i></div>
                        <div class="fw-semibold">No products found</div>
                        <div class="small">Try a different search or clear filters.</div>
                        <div class="mt-2">
                            <a class="btn btn-ghost btn-sm" href="{{ url()->current() }}">Reset</a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $products->onEachSide(1)->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Debounced search submit (GET)
        (function() {
            const input = document.getElementById('storeSearch');
            if (!input) return;
            let t = null;
            input.addEventListener('input', function() {
                clearTimeout(t);
                t = setTimeout(() => {
                    input.form && input.form.submit();
                }, 350);
            });
        })();

        // Optional: optimistic “save” click animation
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.fav-btn');
            if (!btn) return;
            btn.classList.add('skeleton');
            setTimeout(() => btn.classList.remove('skeleton'), 500);
            // TODO: Ajax call to save product
        }, {
            passive: true
        });
    </script>
@endpush
