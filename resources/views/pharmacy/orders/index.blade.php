@extends('layouts.pharmacy')
@section('title', 'Orders')

@push('styles')
    <style>
        /* reuse your previous styles, names updated from rx->order */
        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .order-table {
            display: grid;
            gap: 8px
        }

        .order-head,
        .order-row {
            display: grid;
            grid-template-columns: 140px 1.2fr 1fr 160px 160px 190px;
            align-items: center;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #0f1a2e
        }

        .order-head {
            background: #101a2e;
            font-weight: 700;
            color: #b8c2d6
        }

        .cell-sub {
            color: #9aa3b2;
            font-size: .85rem
        }

        .items-ellipsis {
            color: #b8c2d6;
            font-size: .9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .btn-ico {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            background: #0d162a;
            color: #e5e7eb
        }

        .btn-ico:hover {
            filter: brightness(1.1)
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

        .b-pending {
            border-color: #334155
        }

        .b-good {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .08)
        }

        .b-warn {
            border-color: #7a5a1b;
            background: rgba(245, 158, 11, .08)
        }

        .b-bad {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .08)
        }

        @media(max-width:820px) {
            .order-head {
                display: none
            }

            .order-row {
                grid-template-columns: 1fr;
                gap: 8px
            }

            .order-row .col {
                display: flex;
                justify-content: space-between;
                gap: 12px
            }

            .order-row .lbl {
                color: #9aa3b2
            }
        }
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-file-prescription"></i>
            <h5 class="m-0">Orders</h5>
        </div>
        <div class="subtle mb-2">Search and manage prescription orders for this pharmacy</div>

        <form id="orderFilter" class="row g-2">
            <div class="col-lg-4">
                <input class="form-control" name="q" value="{{ $q ?? '' }}"
                    placeholder="Search code, patient, doctor, medicine…">
            </div>
            <div class="col-lg-3">
                @php $s = $status ?? ''; @endphp
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'quoted', 'patient_confirmed', 'pharmacy_accepted', 'ready', 'dispatcher_price_set', 'dispatcher_price_confirm', 'picked', 'delivered', 'rejected'] as $opt)
                        <option value="{{ $opt }}" @selected($s === $opt)>
                            {{ ucwords(str_replace('_', ' ', $opt)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2"><input type="date" class="form-control" name="from" value="{{ $dateFrom ?? '' }}">
            </div>
            <div class="col-lg-2"><input type="date" class="form-control" name="to" value="{{ $dateTo ?? '' }}">
            </div>
            <div class="col-lg-1 d-grid"><button class="btn btn-gradient">Go</button></div>
        </form>
    </div>

    <div id="orderList">
        @include('pharmacy.orders._list', ['orders' => $orders])
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            $('#orderFilter input, #orderFilter select').on('change', () => $('#orderFilter').trigger('submit'));
            $('#orderFilter').on('submit', function(e) {
                e.preventDefault();
                $.get(`{{ route('pharmacy.orders.index') }}`, $(this).serialize(), html => $('#orderList').html(
                    html));
            });

            // actions (delegated)
            $(document).on('click', '[data-status]', function() {
                const id = $(this).data('id');
                const to = $(this).data('status');
                if (to === 'picked') {
                    return;
                }
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ url('/pharmacy/orders') }}/${id}/status`, {
                        _token: `{{ csrf_token() }}`,
                        status: to
                    })
                    .done(res => {
                        flash('success', res.message || 'Updated');
                        location.reload();
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Update failed');
                    })
                    .always(() => unlockBtn($btn));
            });

            $(document).on('click', '[data-status="picked"]', function() {
                const id = $(this).data('id');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('pharmacy.orders.markPicked', ['order' => '__OID__']) }}`.replace('__OID__',
                        id), {
                        _token: `{{ csrf_token() }}`
                    })
                    .done(res => {
                        flash('success', res.message || 'Marked picked');
                        location.reload();
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed');
                    })
                    .always(() => unlockBtn($btn));
            });
        })();
    </script>
@endpush
