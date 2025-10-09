{{-- resources/views/pharmacy/inventory.blade.php --}}
@extends('layouts.pharmacy')
@section('title', 'Inventory')

@push('styles')
    <style>
        .table-scroll {
            max-height: 420px;
            overflow: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .csv-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .csv-table th,
        .csv-table td {
            padding: .45rem .6rem;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .csv-table th {
            position: sticky;
            top: 0;
            background: #0f1a2e;
            z-index: 1;
        }

        .muted {
            color: #9aa3b2;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .18rem .55rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #0e162b;
            font-size: .85rem;
        }
    </style>
@endpush

@section('content')
    <div class="cardx">
        <h5 class="mb-2">Upload Inventory (CSV)</h5>
        <form method="POST" action="{{ route('pharmacy.inventory.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">CSV File</label>
                    <input type="file" name="inventory" class="form-control" accept=".csv,text/csv" required>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>

        @if (session('ok'))
            <div class="alert alert-success mt-2">{{ session('ok') }}</div>
        @endif
        @error('inventory')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
    </div>

    {{-- Current inventory preview --}}
    <div class="cardx mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="m-0">Current Inventory</h5>
            <div class="d-flex align-items-center gap-2">
                @if (!empty($fileMeta))
                    <span class="pill">
                        <i class="fa-regular fa-file-lines"></i>
                        {{ number_format($fileMeta['size'] / 1024, 1) }} KB
                    </span>
                    @if ($fileMeta['updated_at'])
                        <span class="pill">
                            <i class="fa-regular fa-clock"></i>
                            Updated {{ $fileMeta['updated_at']->diffForHumans() }}
                        </span>
                    @endif
                    <a href="{{ route('pharmacy.inventory.download') }}" class="btn btn-outline-light btn-sm">
                        <i class="fa-solid fa-download me-1"></i> Download CSV
                    </a>
                @endif
            </div>
        </div>

        @if (empty($fileMeta))
            <div class="muted">No inventory uploaded yet.</div>
        @else
            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <input id="invSearch" class="form-control" placeholder="Search rows (client-side)">
                </div>
                <div class="col-md-6 text-end muted">
                    Showing first <strong>{{ count($rows) }}</strong> rows
                    @if (count($rows) === 200)
                        (preview truncated)
                    @endif
                </div>
            </div>

            <div class="table-scroll">
                <table class="csv-table">
                    @if (!empty($headers))
                        <thead>
                            <tr>
                                @foreach ($headers as $h)
                                    <th>{{ $h === '' ? '—' : $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    @endif
                    <tbody id="invBody">
                        @forelse($rows as $r)
                            <tr>
                                @foreach ($r as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td class="muted">No data rows</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const $search = document.getElementById('invSearch');
            const $tbody = document.getElementById('invBody');
            if (!$search || !$tbody) return;

            $search.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                for (const tr of $tbody.querySelectorAll('tr')) {
                    const text = tr.innerText.toLowerCase();
                    tr.style.display = q === '' || text.includes(q) ? '' : 'none';
                }
            });
        })();
    </script>
@endpush
