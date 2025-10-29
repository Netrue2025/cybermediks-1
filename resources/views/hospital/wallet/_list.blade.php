@php use Carbon\Carbon; @endphp
<div class="d-flex flex-column gap-2">
    @forelse ($transactions as $t)
        <div class="tx-row d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="flex-grow-1 min-width-0">
                <div class="fw-semibold text-truncate">
                    {{ $t->purpose ? \Illuminate\Support\Str::title(str_replace('_', ' ', $t->purpose)) : 'Transaction' }}
                </div>

                {{-- Doctor chip --}}
                @if ($t->relationLoaded('user') && $t->user && $t->user->id !== auth()->user()->id)
                    @php
                        // Prefer first/last; fallback to name/email
                        $docName = trim(($t->user->first_name ?? '') . ' ' . ($t->user->last_name ?? ''));
                        if ($docName === '') {
                            $docName = $t->user->name ?? 'Unknown Doctor';
                        }
                    @endphp
                    <div class="mt-1">
                        <span class="badge-soft" title="Doctor">
                            <i class="fa-solid fa-user-doctor"></i>
                            {{ $docName }}
                            @if (!empty($t->user->email))
                                <span class="section-subtle ms-2">{{ $t->user->email }}</span>
                            @endif
                        </span>
                    </div>
                @endif

                <div class="section-subtle small mt-1">{{ Carbon::parse($t->created_at)->format('M d, Y') }}</div>

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
                @if ($transactions->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $transactions->previousPageUrl() }}" rel="prev">&laquo;</a>
                    </li>
                @endif

                @foreach ($transactions->links()->elements[0] as $page => $url)
                    @if ($page == $transactions->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

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
