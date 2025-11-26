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
                <div class="display-6 fw-bold">
                    @money($balance)
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addFundsModal">Add Funds</button>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="cardx h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-wallet"></i>
                    <h5 class="m-0">Transactions</h5>
                </div>
                <div id="txList">
                    @include('patient.wallet._list', ['transactions' => $transactions])
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
                    <label class="form-label">Amount (NGN)</label>
                    <input id="addAmount" type="number" min="5" step="0.01" class="form-control"
                        placeholder="1000.00">
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
        (function() {
            const $txList = $('#txList');

            function refreshList() {
                $.get(`{{ route('patient.wallet.index') }}`, {}, function(html) {
                    const $html = $('<div>').html(html);
                    const $new = $html.find('#txList').html();
                    $txList.html($new);
                    // Also refresh balance
                    const newBalance = $html.find('.display-6.fw-bold').text();
                    $('.display-6.fw-bold').text(newBalance);
                });
            }

            $('#btnAddFunds').on('click', function() {
                const $btn = $(this);
                lockBtn($btn);

                const amt = parseFloat($('#addAmount').val());
                if (isNaN(amt) || amt < 5) {
                    flash('danger', 'Enter at least ₦5');
                    return unlockBtn($btn);
                }

                $.ajax({
                        url: `{{ route('patient.wallet.pay') }}`,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            _token: `{{ csrf_token() }}`,
                            amount: amt,
                        }
                    })
                    .done(res => {
                        if (res?.ok && res.url) {
                            // You can either replace the page or open a new tab:
                            window.location.href = res.url; // or: window.open(res.url, '_blank');
                        } else {
                            flash('danger', res?.message || 'Failed to initialize payment');
                        }
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to initialize payment');
                    })
                    .always(() => unlockBtn($btn));
            });




        })();
    </script>
@endpush
