@extends('layouts.admin')
@section('title', 'Products')

@push('styles')
    <style>
        .section-subtle {
            color: var(--muted)
        }

        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-striped-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            --bs-table-hover-color: var(--text);
            color: var(--text);
        }

        .avatar-mini {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #14203a;
            color: #cfe0ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .85rem
        }

        .row-actions .btn {
            --bs-btn-padding-y: .25rem;
            --bs-btn-padding-x: .5rem;
            --bs-btn-font-size: .8rem
        }

        .pill-money {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .18rem .55rem;
            font-weight: 600
        }

        table th,
        table td {
            color: white !important
        }

        .img-thumb {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid var(--border);
            background: #0b1222
        }
    </style>
@endpush

@section('content')
    {{-- Flash --}}
    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Filters --}}
    <div class="cardx mb-3 filter-card">
        <form class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label small section-subtle mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#0b1222;border-color:var(--border); color:white;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input class="form-control" name="q" value="{{ $q ?? '' }}"
                        placeholder="Search name, description, link">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small section-subtle mb-1">&nbsp;</label>
                <button class="btn btn-gradient w-100">
                    <i class="fa-solid fa-sliders me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2 text-md-end">
                <label class="form-label small section-subtle mb-1 d-none d-md-block">&nbsp;</label>
                <a href="{{ route('admin.products.index') }}" class="reset-link d-inline-block">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="row g-3">
        {{-- LEFT: Products table --}}
        <div class="col-lg-8">
            <div class="cardx">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                        <thead>
                            <tr class="section-subtle">
                                <th style="width:36px;"></th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Link</th>
                                <th style="width:220px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $p)
                                @php
                                    $initials = strtoupper(substr($p->name, 0, 1));
                                @endphp
                                <tr>
                                    <td>
                                        @if ($p->image)
                                            <img class="img-thumb" src="{{ asset('storage/' . $p->image) }}" alt="img">
                                        @else
                                            <div class="avatar-mini">{{ $initials }}</div>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $p->name }}
                                        <div class="section-subtle small">
                                            {{ \Illuminate\Support\Str::limit($p->description, 60) }}</div>
                                    </td>
                                    <td><span class="pill-money">${{ number_format($p->price, 2) }}</span></td>
                                    <td>
                                        @if ($p->link)
                                            <a href="{{ $p->link }}" target="_blank"
                                                class="text-decoration-underline">Open</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end row-actions">
                                        <div class="btn-group">
                                            {{-- View (opens link if present) --}}
                                            @if ($p->link)
                                                <a href="{{ $p->link }}" target="_blank"
                                                    class="btn btn-outline-light btn-sm">
                                                    <i class="fa-regular fa-eye"></i>
                                                </a>
                                            @endif

                                            {{-- Edit: reload page with ?edit=ID --}}
                                            <a href="{{ route('admin.products.index', array_merge(request()->query(), ['edit' => $p->id])) }}#productForm"
                                                class="btn btn-outline-light btn-sm">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}"
                                                onsubmit="return confirm('Delete this product?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-light btn-sm">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center section-subtle py-4">No products found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">{{ $products->links() }}</div>
            </div>
        </div>

        {{-- RIGHT: Create / Edit form --}}
        <div class="col-lg-4">
            <div class="cardx">
                @php
                    $isEdit = isset($editing) && $editing;
                @endphp

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">{{ $isEdit ? 'Edit Product' : 'Create Product' }}</h5>
                    @if ($isEdit)
                        <a href="{{ route('admin.products.index', request()->except('edit')) }}"
                            class="btn btn-sm btn-outline-light">
                            Cancel
                        </a>
                    @endif
                </div>

                <form id="productForm" method="POST"
                    action="{{ $isEdit ? route('admin.products.update', $editing) : route('admin.products.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $editing->name ?? '') }}" required maxlength="190">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                            placeholder="Optional">{{ old('description', $editing->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Price (USD)</label>
                        <input name="price" type="number" step="0.01" min="0"
                            class="form-control @error('price') is-invalid @enderror"
                            value="{{ old('price', $editing->price ?? '0.00') }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Link</label>
                        <input name="link" type="url" class="form-control @error('link') is-invalid @enderror"
                            placeholder="https://example.com/product" required value="{{ old('link', $editing->link ?? '') }}">
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Image {{ $isEdit ? '(upload to replace)' : '' }}</label>
                        <input name="image" type="file" accept="image/*"
                            class="form-control @error('image') is-invalid @enderror">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if ($isEdit && $editing->image)
                            <div class="mt-2">
                                <img class="img-thumb" src="{{ asset('storage/' . $editing->image) }}"
                                    alt="current image">
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary w-100">
                            {{ $isEdit ? 'Update Product' : 'Create Product' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
