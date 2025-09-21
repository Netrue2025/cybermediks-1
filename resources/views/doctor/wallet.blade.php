@extends('layouts.patient')
@section('title', 'My Wallet')

@push('styles')
    <style>
        .balance-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
        }

        .tx-row {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
        }

        .amount-pos {
            color: #22c55e;
            font-weight: 700;
        }

        .amount-neg {
            color: #f87171;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="balance-card h-100">
                <div class="section-subtle">Wallet Balance</div>
                <div class="display-6 fw-bold">$ 128.40</div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addFundsModal">Add Funds</button>
                    <button class="btn btn-outline-light">Withdraw</button>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="cardx h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-wallet"></i>
                    <h5 class="m-0">Transactions</h5>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach ([['2025-03-04', 'Consultation payment', '-35.00'], ['2025-03-01', 'Wallet top-up', '+100.00'], ['2025-02-23', 'Prescription purchase', '-12.60']] as $t)
                        <div class="tx-row d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">{{ $t[1] }}</div>
                                <div class="section-subtle small">{{ \Carbon\Carbon::parse($t[0])->format('M d, Y') }}</div>
                            </div>
                            <div class="{{ str_starts_with($t[2], '+') ? 'amount-pos' : 'amount-neg' }}">$
                                {{ ltrim($t[2], '+-') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Add Funds Modal --}}
    <div class="modal fade" id="addFundsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:#121a2c;border:1px solid var(--border);border-radius:18px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Add Funds</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Amount (USD)</label>
                    <input id="addAmount" type="number" min="5" step="0.01" class="form-control"
                        placeholder="50.00">
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-gradient" id="btnAddFunds"><span class="btn-text">Add</span></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#btnAddFunds').on('click', function() {
            const $btn = $(this);
            lockBtn($btn);
            const amt = parseFloat($('#addAmount').val());
            if (isNaN(amt) || amt < 5) {
                flash('danger', 'Enter at least $5');
                return unlockBtn($btn);
            }
            // TODO: AJAX to your payment endpoint
            setTimeout(() => {
                flash('success', 'Funds added (demo)');
                unlockBtn($btn);
                $('#addFundsModal').modal('hide');
            }, 800);
        });
    </script>
@endpush
