@extends('layouts.pharmacy')
@section('title', 'Dispensed Orders')

@push('styles')
    <style>
        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px
        }

        .rx-row {
            display: grid;
            grid-template-columns: 140px 1.2fr 1fr 140px 180px;
            gap: 10px;
            align-items: center;
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px
        }

        .rx-head {
            background: #101a2e;
            font-weight: 700;
            color: #b8c2d6
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .2rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .8rem;
            background: #0e162b
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
            filter: brightness(1.08)
        }

        @media(max-width:1100px) {
            .rx-row {
                grid-template-columns: 130px 1fr .9fr 120px 1fr
            }
        }

        @media(max-width:820px) {
            .rx-head {
                display: none
            }

            .rx-row {
                grid-template-columns: 1fr;
                gap: 8px
            }
        }
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <h5 class="m-0">Dispensed Orders</h5>
                </div>
                <div class="subtle">All prescriptions marked as <strong>Picked</strong></div>
            </div>
            <div class="text-end">
                <div class="subtle small">Count</div>
                <div class="fw-bold">{{ $count }}</div>
                <div class="subtle small mt-1">Total Amount</div>
                <div class="fw-bold">${{ number_format($totalAmount, 2, '.', ',') }}</div>
            </div>
        </div>

        <form id="rxFilter" class="row g-2 mt-2">
            <div class="col-lg-5">
                <input class="form-control" name="q" value="{{ $q ?? '' }}"
                    placeholder="Search code, patient, medicine…">
            </div>
            <div class="col-lg-3"><input type="date" class="form-control" name="from" value="{{ $from }}">
            </div>
            <div class="col-lg-3"><input type="date" class="form-control" name="to" value="{{ $to }}">
            </div>
            <div class="col-lg-1 d-grid"><button class="btn btn-gradient">Go</button></div>
        </form>
    </div>

    <div id="rxList">
        @include('pharmacy.dispensed._list', ['prescriptions' => $prescriptions])
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            $('#rxFilter').on('submit', function(e) {
                e.preventDefault();
                $.get(`{{ route('pharmacy.dispensed.index') }}`, $(this).serialize(), function(html) {
                    $('#rxList').html(html);
                });
            });

            // Undo to Ready
            $(document).on('click', '[data-undo]', function() {
                const id = $(this).data('undo'),
                    $btn = $(this);
                if (!confirm('Revert this order to Ready?')) return;
                lockBtn($btn);
                $.post(`{{ route('pharmacy.dispensed.undo', ['rx' => '__ID__']) }}`.replace('__ID__', id), {
                    _token: `{{ csrf_token() }}`
                }).done(res => {
                    flash('success', res.message || 'Reverted');
                    $('#rxFilter').trigger('submit');
                }).fail(xhr => {
                    flash('danger', xhr.responseJSON?.message || 'Failed to revert');
                }).always(() => unlockBtn($btn));
            });
        })();
    </script>
@endpush
