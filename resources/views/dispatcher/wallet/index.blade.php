@extends('layouts.dispatcher')
@section('title', 'My Wallet')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        
        .select2-container--default .select2-selection--single {
            background-color: var(--bg) !important;
            border-color: var(--border) !important;
            color: var(--text) !important;
            height: 38px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--text) !important;
            line-height: 38px;
        }
        
        .select2-dropdown {
            background-color: var(--bg) !important;
            border-color: var(--border) !important;
        }
        
        .select2-container--default .select2-results__option {
            background-color: var(--bg) !important;
            color: var(--text) !important;
        }
        
        .select2-container--default .select2-results__option--highlighted {
            background-color: var(--card) !important;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="balance-card h-100">
                <div class="section-subtle">Wallet Balance</div>
                <div class="display-6 fw-bold" id="balanceAmount">
                    @money($balance)
                </div>
                <div class="d-grid gap-2 mt-5">
                    <button class="btn btn-gradient" id="btnWithdraw">Withdraw</button>
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
                    @include('dispatcher.wallet._list', ['transactions' => $transactions])
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
                    <div class="mb-3">
                        <label class="form-label">Amount (NGN)</label>
                        <input id="wdAmount" type="number" min="5" step="0.01" class="form-control"
                            placeholder="20.00">
                        <span>Fee: <b id="feeDisplay">0.00</b></span>
                        <br>
                        <span>You will receive: <b id="receiveAmount" data-fee="{{ $fee }}">0.00</b></span>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <div class="mb-2">
                                <input type="text" id="wdBankSearch" class="form-control" placeholder="🔍 Type to search banks..." autocomplete="off">
                            </div>
                            <select id="wdBankName" class="form-select" required>
                                <option value="">Select a bank...</option>
                            </select>
                            <input type="hidden" id="wdBankCode">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input id="wdAccountNumber" type="text" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input id="wdAccountName" type="text" class="form-control" disabled required>
                            <small class="text-muted">Account name will be automatically verified</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-gradient" id="btnDoWithdraw">Request Withdrawal</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function() {
            const $txList = $('#txList');
            let banks = [];
            let verifyTimeout = null;

            function refreshList() {
                $.get(`{{ route('dispatcher.wallet.index') }}`, {}, function(html) {
                    const $html = $('<div>').html(html);
                    const $new = $html.find('#txList').html();
                    $txList.html($new);
                    const newBalance = $html.find('.display-6.fw-bold').text();
                    $('.display-6.fw-bold').text(newBalance);
                });
            }

            function loadBanks() {
                $.get('/api/flutterwave/banks?country=NG')
                    .done(function(res) {
                        if (res.status === 'success' && res.data) {
                            banks = res.data;
                            populateBankSelect();
                            
                            // Search input handler
                            $('#wdBankSearch').on('input', function() {
                                const searchTerm = $(this).val().toLowerCase().trim();
                                populateBankSelect(searchTerm);
                            });
                        }
                    })
                    .fail(function() {
                        flash('danger', 'Failed to load banks. Please refresh the page.');
                    });
            }
            
            function populateBankSelect(searchTerm = '') {
                const $select = $('#wdBankName');
                const currentValue = $select.val();
                $select.empty().append('<option value="">Select a bank...</option>');
                
                let filteredBanks = banks;
                if (searchTerm) {
                    filteredBanks = banks.filter(function(bank) {
                        return bank.name.toLowerCase().includes(searchTerm);
                    });
                }
                
                filteredBanks.forEach(function(bank) {
                    $select.append(`<option value="${bank.name}" data-code="${bank.code}">${bank.name}</option>`);
                });
                
                // Restore previous selection if it still exists
                if (currentValue) {
                    $select.val(currentValue).trigger('change');
                }
                
                // Reinitialize Select2
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    placeholder: searchTerm ? 'Filtered results...' : 'Select a bank...',
                    allowClear: true,
                    width: '100%',
                    minimumInputLength: 0,
                    language: {
                        noResults: function() {
                            return searchTerm ? "No banks found matching your search" : "No banks available";
                        },
                        searching: function() {
                            return "Searching...";
                        }
                    }
                });
            }

            $('#wdBankName').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const bankCode = selectedOption.data('code');
                // Ensure bank code is stored as string (preserves leading zeros like "044")
                $('#wdBankCode').val(bankCode ? String(bankCode) : '');
                
                if ($('#wdAccountNumber').val()) {
                    verifyAccount();
                }
            });

            function verifyAccount() {
                const accountNumber = $('#wdAccountNumber').val();
                let bankCode = $('#wdBankCode').val();
                
                if (!accountNumber || !bankCode) {
                    $('#wdAccountName').val('').prop('disabled', true);
                    return;
                }
                
                // Ensure bank code is numeric (validate but keep as string to preserve leading zeros)
                bankCode = String(bankCode).trim();
                if (!/^\d+$/.test(bankCode)) {
                    $('#wdAccountName').val('').prop('disabled', true);
                    flash('danger', 'Invalid bank code');
                    return;
                }
                
                if (verifyTimeout) {
                    clearTimeout(verifyTimeout);
                }
                
                verifyTimeout = setTimeout(function() {
                    if (accountNumber.length >= 10) {
                        $('#wdAccountName').prop('disabled', false).val('Verifying...');
                        
                        $.post('/api/flutterwave/verify-account', {
                            account_number: accountNumber,
                            bank_code: bankCode
                        })
                        .done(function(res) {
                            if (res.status === 'success' && res.data) {
                                $('#wdAccountName').val(res.data.account_name).prop('disabled', true);
                            } else {
                                $('#wdAccountName').val('').prop('disabled', true);
                                flash('danger', res.message || 'Failed to verify account');
                            }
                        })
                        .fail(function(err) {
                            $('#wdAccountName').val('').prop('disabled', true);
                            flash('danger', err.responseJSON?.message || 'Failed to verify account');
                        });
                    } else {
                        $('#wdAccountName').val('').prop('disabled', true);
                    }
                }, 800);
            }

            $('#wdAccountNumber').on('input', function() {
                verifyAccount();
            });

            $("#wdAmount").on('input', function() {
                const fee = parseFloat($("#receiveAmount").data('fee')) || 0;
                const amount = parseFloat($(this).val());

                if (isNaN(amount)) {
                    $("#receiveAmount").text('0.00');
                    return;
                }

                const receive = amount * fee;
                $("#feeDisplay").text(receive.toFixed(2));
                $("#receiveAmount").text((amount - receive).toFixed(2));
            });

            $('#btnWithdraw').on('click', function() {
                loadBanks();
                new bootstrap.Modal(document.getElementById('withdrawModal')).show();
            });

            $('#withdrawModal').on('hidden.bs.modal', function() {
                $('#wdAmount, #wdAccountNumber, #wdBankSearch').val('');
                $('#wdAccountName').val('').prop('disabled', true);
                $('#wdBankName').val(null).trigger('change');
                $('#wdBankCode').val('');
                // Reset bank list to show all banks
                if (banks.length > 0) {
                    populateBankSelect();
                }
            });

            $('#btnDoWithdraw').on('click', function() {
                const $btn = $(this);
                lockBtn($btn);
                
                const bankName = $('#wdBankName').val();
                const bankCode = $('#wdBankCode').val();
                const accountNumber = $('#wdAccountNumber').val();
                const accountName = $('#wdAccountName').val();
                
                if (!bankName || !bankCode) {
                    flash('danger', 'Please select a bank');
                    return unlockBtn($btn);
                }
                
                if (!accountNumber || accountNumber.length < 10) {
                    flash('danger', 'Please enter a valid account number');
                    return unlockBtn($btn);
                }
                
                if (!accountName) {
                    flash('danger', 'Please wait for account verification to complete');
                    return unlockBtn($btn);
                }
                
                const payload = {
                    _token: `{{ csrf_token() }}`,
                    amount: parseFloat($('#wdAmount').val()),
                    currency: 'NGN',
                    bank_name: bankName,
                    bank_code: bankCode,
                    account_number: accountNumber,
                    account_name: accountName,
                };
                
                if (isNaN(payload.amount) || payload.amount < 5) {
                    flash('danger', 'Enter at least ₦5');
                    return unlockBtn($btn);
                }
                
                $.post(`{{ route('wallet.withdraw') }}`, payload)
                    .done(res => {
                        flash('success', res.message || 'Withdrawal requested');
                        $('#withdrawModal').modal('hide');
                        location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Withdrawal failed');
                    })
                    .always(() => unlockBtn($btn));
            });

        })();
    </script>
@endpush
