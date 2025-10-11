@php use Carbon\Carbon; @endphp
<div class="d-flex flex-column gap-2">
    @forelse ($transactions as $t)
        <div class="tx-row d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="flex-grow-1 min-width-0">
                <div class="fw-semibold text-truncate">
                    {{ $t->purpose ? \Illuminate\Support\Str::title(str_replace('_', ' ', $t->purpose)) : 'Transaction' }}
                </div>
                <div class="section-subtle small">{{ Carbon::parse($t->created_at)->format('M d, Y') }}</div>
                @if ($t->reference)
                    <div class="section-subtle small text-truncate">Ref: {{ $t->reference }}</div>
                @endif
            </div>
            <div class="{{ $t->is_credit ? 'amount-pos' : 'amount-neg' }} flex-shrink-0 text-end">
                {{ $t->currency === 'USD' ? '$' : $t->currency . ' ' }}
                {{ number_format((float) $t->amount, 2, '.', ',') }}
            </div>
            <div class="flex-shrink-0">
                <span
                    class="badge {{ $t->status === 'successful' ? 'bg-success' : ($t->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                    {{ ucfirst($t->status) }}
                </span>
            </div>
        </div>
    @empty
        <div class="section-subtle">No transactions yet.</div>
    @endforelse
</div>

@if ($transactions->hasPages())
    <div class="mt-3 d-flex justify-content-center">
        <nav>
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($transactions->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $transactions->previousPageUrl() }}" rel="prev">&laquo;</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($transactions->links()->elements[0] as $page => $url)
                    @if ($page == $transactions->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($transactions->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $transactions->nextPageUrl() }}" rel="next">&raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                @endif
            </ul>
        </nav>
    </div>
@endif
