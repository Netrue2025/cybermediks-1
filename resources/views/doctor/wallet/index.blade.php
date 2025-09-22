@extends('layouts.doctor')
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
                <div class="display-6 fw-bold" id="balanceAmount">
                    $ {{ number_format($balance, 2, '.', ',') }}
                </div>
                <div class="d-grid gap-2 mt-5">
                    <button class="btn btn-gradient" id="btnWithdraw" >Withdraw</button>
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
                    @include('doctor.wallet._list', ['transactions' => $transactions])
                </div>
            </div>
        </div>
    </div>


    {{-- Withdraw Prompt (simple) --}}
    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:#121a2c;border:1px solid var(--border);border-radius:18px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Withdraw</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Amount (USD)</label>
                    <input id="wdAmount" type="number" min="5" step="0.01" class="form-control"
                        placeholder="20.00">
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-gradient" id="btnDoWithdraw">Request Withdrawal</button>
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
                $.get(`{{ route('doctor.wallet.index') }}`, {}, function(html) {
                    const $html = $('<div>').html(html);
                    const $new = $html.find('#txList').html();
                    $txList.html($new);
                    // Also refresh balance
                    const newBalance = $html.find('.display-6.fw-bold').text();
                    $('.display-6.fw-bold').text(newBalance);
                });
            }


            $('#btnWithdraw').on('click', function() {
                new bootstrap.Modal(document.getElementById('withdrawModal')).show();
            });

            $('#btnDoWithdraw').on('click', function() {
                const $btn = $(this);
                lockBtn($btn);
                const amt = parseFloat($('#wdAmount').val());
                if (isNaN(amt) || amt < 5) {
                    flash('danger', 'Enter at least $5');
                    return unlockBtn($btn);
                }
                $.post(`{{ route('doctor.wallet.withdraw') }}`, {
                        _token: `{{ csrf_token() }}`,
                        amount: amt,
                        currency: 'USD'
                    })
                    .done(res => {
                        flash('success', res.message || 'Withdrawal requested');
                        $('#withdrawModal').modal('hide');
                        refreshList();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Withdrawal failed');
                    })
                    .always(() => unlockBtn($btn));
            });
        })();
    </script>
@endpush
