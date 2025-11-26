@extends('layouts.patient')
@section('title', 'My Prescriptions')

@push('styles')
    <style>
        .rx-row {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
        }

        .rx-badge {
            background: #10203a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .2rem .5rem;
            color: #cfe0ff;
        }

        .rx-status {
            background: var(--chip);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .2rem .6rem;
            text-transform: capitalize;
        }


        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .18rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem;
            background: #0e162b
        }

        .badge-pending {
            border-color: #334155
        }

        .badge-ready {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .08)
        }

        .badge-picked {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .16)
        }

        .badge-cancelled {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .08)
        }

        .price-pill {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .18rem .55rem;
            font-weight: 600
        }
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-prescription"></i>
                <h5 class="m-0">My Prescriptions</h5>
            </div>

            <button id="btnRxRefresh" type="button" class="btn btn-outline-light btn-sm">
                <span id="rxRefreshSpin" class="spinner-border spinner-border-sm me-1 d-none" role="status"
                    aria-hidden="true"></span>
                Refresh
            </button>
        </div>

        <div class="section-subtle mb-3">View, refill, and manage your prescriptions</div>
        <div class="row g-2">
            <div class="col-lg-6">
                <input id="rxSearch" class="form-control" placeholder="Search by drug, doctor, or code..."
                    value="{{ request('q') }}">
            </div>
            <div class="col-lg-3">
                <select id="rxStatus" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="refill_requested" {{ request('status') === 'refill_requested' ? 'selected' : '' }}>Refill
                        requested</option>
                </select>
            </div>
        </div>
    </div>

    <div id="rxList">
        @include('patient.prescriptions._list', ['prescriptions' => $prescriptions])
    </div>

    <!-- View dialog -->
    <div class="modal fade" id="rxViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card); color:var(--text);">
                <div class="modal-header">
                    <h6 class="modal-title">Prescription</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="rxViewBody" class="small"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Choose Pharmacy Modal -->
    <div class="modal fade" id="pharmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card); color:var(--text);">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-solid fa-store me-1"></i> Choose a Pharmacy</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-8">
                            <input id="pharmSearch" class="form-control" placeholder="Search pharmacy by name…">
                        </div>
                        <div class="col-md-4">
                            <select id="pharmFilter" class="form-select">
                                <option value="">All</option>
                                <option value="24_7">Open 24/7</option>
                                <option value="delivery">Has delivery</option> {{-- optional, if you track this --}}
                            </select>
                        </div>
                    </div>

                    <div id="pharmList" class="d-flex flex-column gap-2">
                        <div class="text-center text-secondary py-3">Searching…</div>
                    </div>

                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quote Review Modal -->
    <div class="modal fade" id="quoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card); color:var(--text);">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Quote Review</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="quoteBody">
                        <div class="text-center text-secondary py-3">Loading…</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto section-subtle">
                        <span id="quoteTotal" class="price-pill">₦0.00</span>
                    </div>
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="btnConfirmQuotedItems" disabled>Confirm
                        Items</button>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        (function() {
            const $list = $('#rxList');
            let currentOrderIdForQuote = null;
            let currentRxIdForQuote = null;
            let t = null;

            function fetchList() {
                const q = $('#rxSearch').val();
                const status = $('#rxStatus').val();
                $.get(`{{ route('patient.prescriptions.index') }}`, {
                    q,
                    status
                }, function(html) {
                    $list.html(html);
                });
            }

            $('#rxSearch').on('input', function() {
                clearTimeout(t);
                t = setTimeout(fetchList, 300);
            });
            $('#rxStatus').on('change', fetchList);

            // View: read JSON payload embedded in row
            $(document).on('click', '[data-rx-view]', function() {
                const payload = $(this).data('rx-view'); // stringified JSON
                const rx = typeof payload === 'string' ? JSON.parse(payload) : payload;

                let itemsHtml = rx.items.map(i => {
                    const bought = i.status === 'purchased';
                    const badge = i.status ?
                        `<span class="badge-soft ms-1">${i.status.replaceAll('_',' ')}</span>` : '';
                    const price = (i.line_total ?? i.unit_price) ?
                        `<span class="price-pill ms-2">₦${Number(i.line_total ?? i.unit_price).toFixed(2)}</span>` :
                        '';
                    return `<li class="${bought ? 'opacity-50' : ''}">
                ${i.drug}${i.dose?` • ${i.dose}`:''}${i.frequency?` • ${i.frequency}`:''}${i.days?` • ${i.days}`:''}${i.directions?` — ${i.directions}`:''}
                ${badge}${price}
                </li>`;
                }).join('');

                let html = `
                    <div class="mb-2">
                        <span class="rx-badge">Rx ${rx.code}</span>
                        <span class="rx-status ms-2">${(rx.dispense || rx.status || '').toString().replaceAll('_',' ')}</span>
                    </div>
                    <div class="mb-2"><strong>Doctor:</strong> ${rx.doctor}</div>
                    ${rx.notes ? `<div class="mb-3"><strong>Notes:</strong> ${rx.notes}</div>` : ``}
                    <div class="mb-2"><strong>Items</strong></div>
                    <ul class="mb-0">${itemsHtml}</ul>
                `;
                $('#rxViewBody').html(html);
                new bootstrap.Modal(document.getElementById('rxViewModal')).show();
            });


            // Refill click (placeholder: route to your refill flow)
            $(document).on('click', '[data-rx-refill]', function() {
                const id = $(this).data('rx-refill');
                window.location.href = `/patient/prescriptions/${id}/refill`; // implement when ready
            });


            let currentRxId = null;
            const $pharmModal = $('#pharmModal');
            const $pharmList = $('#pharmList');
            const $pharmSearch = $('#pharmSearch');
            const $pharmFilter = $('#pharmFilter');
            let searchTimer = null;

            // Open modal and load pharmacies
            $(document).on('click', '[data-rx-buy]', function() {
                currentRxId = $(this).data('rx-buy');
                if (!currentRxId) return;
                $pharmModal.modal('show');
                loadPharmacies();
            });

            $pharmSearch.on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadPharmacies, 300);
            });
            $pharmFilter.on('change', loadPharmacies);

            function loadPharmacies() {
                const q = $pharmSearch.val() || '';
                const filter = $pharmFilter.val() || '';
                $pharmList.html(`<div class="text-center text-secondary py-3">Loading…</div>`);
                $.get(`{{ route('patient.prescriptions.pharmacies', ['rx' => '__ID__']) }}`.replace('__ID__',
                        currentRxId), {
                        q,
                        filter
                    })
                    .done(html => $pharmList.html(html))
                    .fail(() => $pharmList.html(
                        `<div class="text-center text-danger py-3">Failed to load pharmacies</div>`));
            }

            // Select pharmacy -> assign to prescription
            $(document).on('click', '[data-pharm-select]', function() {
                const pharmId = $(this).data('pharm-select');
                const $btn = $(this);
                if (!pharmId || !currentRxId) return;

                lockBtn($btn);
                $.post(`{{ route('patient.prescriptions.assignPharmacy', ['rx' => '__ID__']) }}`.replace(
                        '__ID__', currentRxId), {
                        _token: `{{ csrf_token() }}`,
                        pharmacy_id: pharmId
                    })
                    .done(res => {
                        // Always close the picker
                        $pharmModal.modal('hide');

                        // If server returned a quote, show it immediately
                        if (res && res.quote) {
                            currentOrderIdForQuote = res.order_id;
                            currentRxIdForQuote =
                                currentRxId; // <- you already have currentRxId in your page
                            $.get(`{{ route('patient.orders.quoteFragment', ['order' => '__OID__']) }}`
                                    .replace('__OID__', res.order_id))
                                .done(function(html) {
                                    $('#quoteBody').html(html);
                                    bootstrap.Modal.getOrCreateInstance(document.getElementById(
                                        'quoteModal')).show();
                                })
                                .fail(function() {
                                    flash('danger', 'Failed to load quote');
                                });
                        } else {
                            flash('success', res.message || 'Pharmacy selected');
                            try {
                                (typeof fetchList === 'function') && fetchList();
                            } catch (e) {}
                        }
                    })

                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to assign pharmacy');
                    })
                    .always(() => unlockBtn($btn));
            });

            $(document).on('click', '[data-dsp-confirm-order]', function() {
                const orderId = $(this).data('dsp-confirm-order');
                const $btn = $(this);
                lockBtn($btn);

                $.post(
                        `{{ route('patient.orders.confirmDeliveryFee', ['order' => '__OID__']) }}`.replace(
                            '__OID__', orderId), {
                            _token: `{{ csrf_token() }}`
                        }
                    )
                    .done(res => {
                        flash('success', res.message || 'Delivery fee confirmed');
                        location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });






            // Confirm the quoted items for this order
            $(document)
                .off('click.quote', '#btnConfirmQuotedItems')
                .on('click.quote', '#btnConfirmQuotedItems', function() {
                    const $btn = $(this);
                    if (!currentRxIdForQuote) return; // we confirm by PRESCRIPTION

                    lockBtn($btn);
                    $.post(
                            `{{ route('patient.prescriptions.confirmPrice', ['rx' => '__RX__']) }}`
                            .replace('__RX__', currentRxIdForQuote), {
                                _token: `{{ csrf_token() }}`
                            }
                        )
                        .done(res => {
                            flash('success', res.message || 'Items confirmed');
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('quoteModal')).hide();
                            try {
                                (typeof fetchList === 'function') && fetchList();
                            } catch (e) {}
                        })
                        .fail(xhr => {
                            flash('danger', xhr.responseJSON?.message || 'Failed to confirm');
                        })
                        .always(() => unlockBtn($btn));
                });



            // tiny HTML escaper for safe rendering
            function escapeHtml(s) {
                return String(s || '')
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            }

        })();
    </script>
@endpush

@push('scripts')
    <script>
        (function() {
            const $list = $('#rxList');
            let currentOrderIdForQuote = null;
            let currentRxIdForQuote = null;
            let t = null;
            const $btnRefresh = $('#btnRxRefresh');
            const $spin = $('#rxRefreshSpin');

            function fetchList(pageUrl = null) {
                const q = $('#rxSearch').val() || '';
                const status = $('#rxStatus').val() || '';
                const url = pageUrl || `{{ route('patient.prescriptions.index') }}`;

                return $.get(url, {
                    q,
                    status
                }).done(function(html) {
                    $list.html(html);
                });
            }

            function refreshRxList(pageUrl = null) {
                $btnRefresh.prop('disabled', true);
                $spin.removeClass('d-none');
                return fetchList(pageUrl).always(function() {
                    $btnRefresh.prop('disabled', false);
                    $spin.addClass('d-none');
                });
            }

            $('#rxSearch').on('input', function() {
                clearTimeout(t);
                t = setTimeout(() => refreshRxList(), 300);
            });
            $('#rxStatus').on('change', refreshRxList());

            $btnRefresh.on('click', function() {
                refreshRxList();
            });

            // NEW: AJAX pagination inside #rxList
            $(document).on('click', '#rxList .pagination a', function(e) {
                e.preventDefault();
                const url = this.href;
                if (!url) return;
                refreshRxList(url);
            });


            // View: read JSON payload embedded in row
            $(document).on('click', '[data-rx-view]', function() {
                const payload = $(this).data('rx-view'); // stringified JSON
                const rx = typeof payload === 'string' ? JSON.parse(payload) : payload;

                let itemsHtml = rx.items.map(i => {
                    const bought = i.status === 'purchased';
                    const badge = i.status ?
                        `<span class="badge-soft ms-1">${i.status.replaceAll('_',' ')}</span>` : '';
                    const price = (i.line_total ?? i.unit_price) ?
                        `<span class="price-pill ms-2">₦${Number(i.line_total ?? i.unit_price).toFixed(2)}</span>` :
                        '';
                    return `<li class="${bought ? 'opacity-50' : ''}">
                ${i.drug}${i.dose?` • ${i.dose}`:''}${i.frequency?` • ${i.frequency}`:''}${i.days?` • ${i.days}`:''}${i.directions?` — ${i.directions}`:''}
                ${badge}${price}
                </li>`;
                }).join('');

                let html = `
                    <div class="mb-2">
                        <span class="rx-badge">Rx ${rx.code}</span>
                        <span class="rx-status ms-2">${(rx.dispense || rx.status || '').toString().replaceAll('_',' ')}</span>
                    </div>
                    <div class="mb-2"><strong>Doctor:</strong> ${rx.doctor}</div>
                    ${rx.notes ? `<div class="mb-3"><strong>Notes:</strong> ${rx.notes}</div>` : ``}
                    <div class="mb-2"><strong>Items</strong></div>
                    <ul class="mb-0">${itemsHtml}</ul>
                `;
                $('#rxViewBody').html(html);
                new bootstrap.Modal(document.getElementById('rxViewModal')).show();
            });


            // Refill click (placeholder: route to your refill flow)
            $(document).on('click', '[data-rx-refill]', function() {
                const id = $(this).data('rx-refill');
                window.location.href = `/patient/prescriptions/${id}/refill`; // implement when ready
            });


            let currentRxId = null;
            const $pharmModal = $('#pharmModal');
            const $pharmList = $('#pharmList');
            const $pharmSearch = $('#pharmSearch');
            const $pharmFilter = $('#pharmFilter');
            let searchTimer = null;

            // Open modal and load pharmacies
            $(document).on('click', '[data-rx-buy]', function() {
                currentRxId = $(this).data('rx-buy');
                if (!currentRxId) return;
                $pharmModal.modal('show');
                loadPharmacies();
            });

            $pharmSearch.on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadPharmacies, 300);
            });
            $pharmFilter.on('change', loadPharmacies);

            function loadPharmacies() {
                const q = $pharmSearch.val() || '';
                const filter = $pharmFilter.val() || '';
                $pharmList.html(`<div class="text-center text-secondary py-3">Loading…</div>`);
                $.get(`{{ route('patient.prescriptions.pharmacies', ['rx' => '__ID__']) }}`.replace('__ID__',
                        currentRxId), {
                        q,
                        filter
                    })
                    .done(html => $pharmList.html(html))
                    .fail(() => $pharmList.html(
                        `<div class="text-center text-danger py-3">Failed to load pharmacies</div>`));
            }

            // Select pharmacy -> assign to prescription
            $(document).on('click', '[data-pharm-select]', function() {
                const pharmId = $(this).data('pharm-select');
                const $btn = $(this);
                if (!pharmId || !currentRxId) return;

                lockBtn($btn);
                $.post(`{{ route('patient.prescriptions.assignPharmacy', ['rx' => '__ID__']) }}`.replace(
                        '__ID__', currentRxId), {
                        _token: `{{ csrf_token() }}`,
                        pharmacy_id: pharmId
                    })
                    .done(res => {
                        // Always close the picker
                        $pharmModal.modal('hide');

                        // If server returned a quote, show it immediately
                        if (res && res.quote) {
                            currentOrderIdForQuote = res.order_id;
                            currentRxIdForQuote =
                                currentRxId; // <- you already have currentRxId in your page
                            $.get(`{{ route('patient.orders.quoteFragment', ['order' => '__OID__']) }}`
                                    .replace('__OID__', res.order_id))
                                .done(function(html) {
                                    $('#quoteBody').html(html);
                                    bootstrap.Modal.getOrCreateInstance(document.getElementById(
                                        'quoteModal')).show();
                                })
                                .fail(function() {
                                    flash('danger', 'Failed to load quote');
                                });
                        } else {
                            flash('success', res.message || 'Pharmacy selected');
                            try {
                                (typeof fetchList === 'function') && fetchList();
                            } catch (e) {}
                        }
                    })

                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to assign pharmacy');
                    })
                    .always(() => unlockBtn($btn));
            });

            $(document).on('click', '[data-dsp-confirm-order]', function() {
                const orderId = $(this).data('dsp-confirm-order');
                const $btn = $(this);
                lockBtn($btn);

                $.post(
                        `{{ route('patient.orders.confirmDeliveryFee', ['order' => '__OID__']) }}`.replace(
                            '__OID__', orderId), {
                            _token: `{{ csrf_token() }}`
                        }
                    )
                    .done(res => {
                        flash('success', res.message || 'Delivery fee confirmed');
                        location.reload();
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });






            // Confirm the quoted items for this order
            $(document)
                .off('click.quote', '#btnConfirmQuotedItems')
                .on('click.quote', '#btnConfirmQuotedItems', function() {
                    const $btn = $(this);
                    if (!currentRxIdForQuote) return; // we confirm by PRESCRIPTION

                    lockBtn($btn);
                    $.post(
                            `{{ route('patient.prescriptions.confirmPrice', ['rx' => '__RX__']) }}`
                            .replace('__RX__', currentRxIdForQuote), {
                                _token: `{{ csrf_token() }}`
                            }
                        )
                        .done(res => {
                            flash('success', res.message || 'Items confirmed');
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('quoteModal')).hide();
                            try {
                                (typeof fetchList === 'function') && fetchList();
                            } catch (e) {}
                        })
                        .fail(xhr => {
                            flash('danger', xhr.responseJSON?.message || 'Failed to confirm');
                        })
                        .always(() => unlockBtn($btn));
                });



            // tiny HTML escaper for safe rendering
            function escapeHtml(s) {
                return String(s || '')
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            }

            // --- Config ---
            const POLL_MS = 3000; // how often to check
            const IDLE_GRACE_MS = 1200; // “user not typing” window

            // --- State ---
            let inFlight = false;
            let nextTimer = null;
            let lastUserActivityAt = Date.now();
            let lastStartedAt = 0;
            let lastFinishedAt = 0;

            // If your previous code defined these, we reuse them:
            //   const $btnRefresh = $('#btnRxRefresh');
            //   const $spin = $('#rxRefreshSpin');

            // Ensure refreshRxList returns a jqXHR/Promise and toggles the button/spinner.
            // If you copied the earlier snippet, it already does.
            function startAutoPolling() {
                // guard: don’t create multiple loops
                if (nextTimer) return;
                scheduleNext();
            }

            function stopAutoPolling() {
                if (nextTimer) {
                    clearTimeout(nextTimer);
                    nextTimer = null;
                }
            }

            function scheduleNext() {
                stopAutoPolling();
                nextTimer = setTimeout(tick, POLL_MS);
            }

            function idleEnough() {
                return (Date.now() - lastUserActivityAt) >= IDLE_GRACE_MS;
            }

            function tick() {
                // Skip if tab not visible or user busy or request running
                if (document.hidden || !idleEnough() || inFlight) {
                    return scheduleNext();
                }

                inFlight = true;
                lastStartedAt = Date.now();

                // Kick the refresh (reuse your function)
                // If you don’t want the top-right button spinner during auto, you can
                // add a flag to refreshRxList; but it’s fine to show a tiny spinner.
                refreshRxList()
                    .always(function() {
                        inFlight = false;
                        lastFinishedAt = Date.now();
                        scheduleNext();
                    });
            }

            // --- Wire user activity (pause while typing/selecting) ---
            function bumpIdle() {
                lastUserActivityAt = Date.now();
            }

            $(document).on('input', '#rxSearch', bumpIdle);
            $(document).on('change', '#rxStatus', bumpIdle);

            // If you have more interactive inputs inside the list, track them too:
            $(document).on('input change', '#rxList :input', bumpIdle);

            // Pause when tab hidden; resume when visible
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    stopAutoPolling();
                } else {
                    // user came back—reset idle and schedule soon
                    bumpIdle();
                    scheduleNext();
                }
            });

            // If your manual Refresh button exists, make sure it doesn’t break the loop
            $(document).on('click', '#btnRxRefresh', function() {
                bumpIdle(); // treat as user activity
                // When manual refresh finishes, the loop will scheduleNext() again automatically
            });

            // Kick it off
            startAutoPolling();
        })();
    </script>
@endpush
