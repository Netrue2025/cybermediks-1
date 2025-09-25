@php use Carbon\Carbon; @endphp
<div class="d-flex flex-column gap-2">
    @forelse ($transactions as $t)
        <div class="tx-row d-flex justify-content-between">
            <div>
                <div class="fw-semibold">
                    {{ $t->purpose ? \Illuminate\Support\Str::title(str_replace('_', ' ', $t->purpose)) : 'Transaction' }}
                </div>
                <div class="section-subtle small">{{ Carbon::parse($t->created_at)->format('M d, Y') }}</div>
                @if ($t->reference)
                    <div class="section-subtle small">Ref: {{ $t->reference }}</div>
                @endif
            </div>
            <div class="{{ $t->is_credit ? 'amount-pos' : 'amount-neg' }}">
                {{ $t->currency === 'USD' ? '$' : $t->currency . ' ' }}
                {{ number_format((float) $t->amount, 2, '.', ',') }}
            </div>
            <div>
                <span class="badge {{ $t->status === 'completed' ? 'bg-success' : ($t->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                    {{ ucfirst($t->status) }}
                </span>
            </div>
        </div>
    @empty
        <div class="section-subtle">No transactions yet.</div>
    @endforelse
</div>

@if ($transactions->hasPages())
    <div class="mt-3">
        {!! $transactions->links() !!}
    </div>
@endif
