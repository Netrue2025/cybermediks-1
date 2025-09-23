@extends('layouts.admin')
@section('title', 'Appointments')

@push('styles')
    <style>
        .section-subtle {
            color: var(--muted)
        }

        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-striped-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            --bs-table-hover-color: var(--text);
            color: var(--text)
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

        .badge-scheduled {
            border-color: #334155
        }

        .badge-completed {
            border-color: #1f6f43;
            background: rgba(34, 197, 94, .12)
        }

        .badge-cancelled {
            border-color: #6f2b2b;
            background: rgba(239, 68, 68, .10)
        }

        .badge-no_show {
            border-color: #6b4ae0;
            background: rgba(135, 88, 232, .10)
        }

        .chip {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .18rem .55rem;
            color: #cfe0ff;
            font-size: .8rem
        }

        .input-icon {
            position: relative
        }

        .input-icon .prefix {
            position: absolute;
            left: .6rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2
        }

        .input-icon input {
            padding-left: 2rem
        }
    </style>
@endpush

@section('content')
    {{-- Filters --}}
    <div class="cardx mb-3">
        <form class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small section-subtle mb-1">Date</label>
                <input class="form-control" type="date" name="date" value="{{ $date }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small section-subtle mb-1">Status</label>
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach (['scheduled', 'completed', 'cancelled', 'no_show'] as $st)
                        <option value="{{ $st }}" @selected($status === $st)>
                            {{ ucwords(str_replace('_', ' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small section-subtle mb-1">Search</label>
                <div class="input-icon">
                    <span class="prefix"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="q" value="{{ $q }}"
                        placeholder="Title, doctor, or patient">
                </div>
            </div>

            <div class="col-md-2">
                <button class="btn btn-gradient w-100">
                    <i class="fa-solid fa-sliders me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="cardx">
        <div class="table-responsive">
            <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                <thead>
                    <tr class="section-subtle">
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Title</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appts as $a)
                        @php
                            $st = $a->status ?? 'scheduled';
                            $badge =
                                [
                                    'scheduled' => 'badge-scheduled',
                                    'completed' => 'badge-completed',
                                    'cancelled' => 'badge-cancelled',
                                    'no_show' => 'badge-no_show',
                                ][$st] ?? 'badge-scheduled';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($a->scheduled_at)->format('M d, Y · g:ia') }}</div>
                                <div class="section-subtle small">
                                    {{ \Carbon\Carbon::parse($a->scheduled_at)->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $a->patient?->first_name }} {{ $a->patient?->last_name }}</div>
                                <div class="section-subtle small">{{ $a->patient?->email }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">Dr. {{ $a->doctor?->first_name }} {{ $a->doctor?->last_name }}
                                </div>
                                <div class="section-subtle small">{{ $a->doctor?->email }}</div>
                            </td>
                            <td><span class="chip">{{ ucwords(str_replace('_', ' ', $a->type)) }}</span></td>
                            <td><span
                                    class="badge-soft {{ $badge }}">{{ ucwords(str_replace('_', ' ', $st)) }}</span>
                            </td>
                            <td>{{ $a->title ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center section-subtle py-4">No appointments</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">{{ $appts->withQueryString()->onEachSide(1)->links() }}</div>
    </div>
@endsection
