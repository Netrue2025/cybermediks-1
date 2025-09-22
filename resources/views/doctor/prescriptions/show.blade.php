@extends('layouts.doctor')
@section('title', 'Rx ' . $rx->code)

@push('styles')
    <style>
        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .block {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem;
            background: #0e162b
        }

        .badge-ready {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .08)
        }

        .badge-picked {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .16)
        }

        .badge-pending {
            border-color: #334155
        }

        .badge-cancelled {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .08)
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="cardx">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold">Rx {{ $rx->code }}</div>
                        <div class="subtle small">
                            {{ $rx->created_at?->format('M d, Y · g:ia') }}
                            • Patient: {{ $rx->patient?->first_name }} {{ $rx->patient?->last_name }}
                        </div>
                    </div>
                    <div>
                        @php
                            $s = $rx->dispense_status ?? 'pending';
                            $cls =
                                [
                                    'pending' => 'badge-pending',
                                    'ready' => 'badge-ready',
                                    'picked' => 'badge-picked',
                                    'cancelled' => 'badge-cancelled',
                                ][$s] ?? 'badge-pending';
                        @endphp
                        <span class="badge-soft {{ $cls }}">{{ ucfirst($s) }}</span>
                    </div>
                </div>

                <hr class="my-3" style="border-color:var(--border);opacity:.6">

                <div class="d-flex flex-column gap-2">
                    @forelse($rx->items as $it)
                        <div class="block">
                            <div class="fw-semibold">{{ $it->medicine ?? ($it->drug ?? 'Item') }}</div>
                            <div class="subtle small">
                                @php
                                    $dose = $it->dosage ?? $it->dose;
                                    $freq = $it->frequency;
                                    $dur = $it->duration ?? $it->days;
                                    $qty = $it->qty;
                                    $txt = [];
                                    if ($dose) {
                                        $txt[] = "Dose: $dose";
                                    }
                                    if ($freq) {
                                        $txt[] = $freq;
                                    }
                                    if ($dur) {
                                        $txt[] = is_numeric($dur) ? "{$dur} days" : $dur;
                                    }
                                    if ($qty) {
                                        $txt[] = "Qty: $qty";
                                    }
                                @endphp
                                {{ implode(' • ', $txt) }}
                            </div>
                            @if ($it->directions ?? null)
                                <div class="subtle small mt-1">Directions: {{ $it->directions }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="subtle">No items listed.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="cardx">
                <div class="fw-bold mb-2">Summary</div>
                <div class="subtle small mb-2">Total Amount:
                    <strong>
                        {{ is_null($rx->total_amount) ? '—' : '$' . number_format((float) $rx->total_amount, 2, '.', ',') }}
                    </strong>
                </div>
                @if ($rx->notes)
                    <div class="subtle small">Notes:</div>
                    <div class="block mt-1">{{ $rx->notes }}</div>
                @endif
                <a href="{{ route('doctor.prescriptions.index') }}" class="btn btn-ghost w-100 mt-3">
                    <i class="fa-solid fa-arrow-left-long me-1"></i> Back to list
                </a>
            </div>
        </div>
    </div>
@endsection
