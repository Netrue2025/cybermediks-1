@extends('layouts.pharmacy')
@section('title', 'Receipt ' . $rx->code)

@push('styles')
    <style>
        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px
        }

        .line {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed var(--border);
            padding: .35rem 0
        }
    </style>
@endpush

@section('content')
    <div class="cardx">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <div class="fw-bold">Receipt · Rx {{ $rx->code }}</div>
                <div class="subtle small">
                    {{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }} •
                    {{ $rx->updated_at?->format('M d, Y · g:ia') }}
                </div>
            </div>
            <a href="#" onclick="window.print()" class="btn btn-ghost"><i class="fa-solid fa-print me-1"></i> Print</a>
        </div>

        <div class="mb-2">
            @foreach ($rx->items as $it)
                @php
                    $name = $it->medicine ?? ($it->drug ?? 'Item');
                    $qty = $it->qty ?? 1;
                @endphp
                <div class="line"><span>{{ $name }}</span><span>x{{ $qty }}</span></div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end">
            <div class="fw-bold">Total:
                {{ is_null($rx->total_amount) ? '—' : '$' . number_format((float) $rx->total_amount, 2, '.', ',') }}
            </div>
        </div>
    </div>
@endsection
