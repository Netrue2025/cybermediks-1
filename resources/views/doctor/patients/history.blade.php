@extends('layouts.doctor')
@section('title', 'Patient History')

@push('styles')
    <style>
        .cardx {
            background: #0f1a2e;
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

        .chip {
            background: var(--chip);
            border: 1px solid var(--chipBorder);
            border-radius: 999px;
            padding: .2rem .55rem;
            color: #b8c2d6;
            font-size: .85rem;
        }

        .avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #14203a;
            color: #cfe0ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
        }

        .metric {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
        }

        .subtle {
            color: #9aa3b2;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .5rem;
            font-weight: 700;
        }

        .badge-soft {
            background: #0b1222;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .2rem .5rem;
        }

        .rx-item {
            background: #0c1529;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px;
        }
    </style>
@endpush

@section('content')
    @php
        use Illuminate\Support\Str;
        $name = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
        $initials =
            Str::upper(Str::substr($patient->first_name ?? '', 0, 1) . Str::substr($patient->last_name ?? '', 0, 1)) ?:
            'PT';
    @endphp

    <div class="row g-3">
        {{-- LEFT: Patient summary --}}
        <div class="col-lg-4">
            <div class="cardx">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar">{{ $initials }}</div>
                    <div>
                        <div class="fw-bold">{{ $name !== '' ? $name : 'Patient #' . $patient->id }}</div>
                        @if ($patient->email)
                            <div class="subtle small">{{ $patient->email }}</div>
                        @endif
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            <span class="chip">ID: {{ $patient->id }}</span>
                            @if ($stats->last_visit_at ?? null)
                                <span class="chip">Last:
                                    {{ \Carbon\Carbon::parse($stats->last_visit_at)->diffForHumans() }}</span>
                            @endif
                            @if ($activeRxCount > 0)
                                <span class="chip">Active Rx: {{ $activeRxCount }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <hr class="my-3" style="border-color:var(--border);opacity:.6">

                <div class="row text-center g-2">
                    <div class="col-4">
                        <div class="subtle small">Visits</div>
                        <div class="metric">{{ (int) ($stats->total_visits ?? 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="subtle small">Upcoming</div>
                        <div class="metric">{{ (int) ($stats->upcoming_count ?? 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="subtle small">Active Rx</div>
                        <div class="metric">{{ $activeRxCount }}</div>
                    </div>
                </div>

                <div class="mt-3 d-grid gap-2">
                    <a class="btn btn-gradient" href="{{ url('/doctor/messenger?patient_id=' . $patient->id) }}">
                        Start Consult
                    </a>
                    <a class="btn btn-outline-light"
                        href="{{ route('doctor.prescriptions.create') }}?patient_id={{ $patient->id }}">
                        New Prescription
                    </a>
                </div>
            </div>
        </div>

        {{-- RIGHT: Tabs: Appointments + Prescriptions --}}
        <div class="col-lg-8">
            <div class="cardx">
                <ul class="nav nav-tabs border-0" id="histTabs" role="tablist" style="gap:.25rem;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ap-tab" data-bs-toggle="tab" data-bs-target="#ap"
                            type="button" role="tab">Appointments</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rx-tab" data-bs-toggle="tab" data-bs-target="#rx" type="button"
                            role="tab">Prescriptions</button>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    {{-- Appointments --}}
                    <div class="tab-pane fade show active" id="ap" role="tabpanel" aria-labelledby="ap-tab">
                        <div class="d-flex flex-column gap-2">
                            @forelse($appointments as $a)
                                <div class="block d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold">
                                            {{ \Carbon\Carbon::parse($a->scheduled_at)->format('D, M d · g:ia') }}
                                        </div>
                                        <div class="subtle small">
                                            Type: {{ str_replace('_', ' ', $a->type) }}
                                            @if ($a->title)
                                                • {{ $a->title }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge-soft">{{ ucfirst(str_replace('_', ' ', $a->status)) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center subtle py-4">No appointments found.</div>
                            @endforelse
                        </div>

                        @if ($appointments->hasPages())
                            <div class="mt-3">{!! $appointments->onEachSide(0)->links() !!}</div>
                        @endif
                    </div>

                    {{-- Prescriptions --}}
                    <div class="tab-pane fade" id="rx" role="tabpanel" aria-labelledby="rx-tab">
                        <div class="d-flex flex-column gap-2">
                            @forelse($prescriptions as $rx)
                                <div class="block">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold">Rx {{ $rx->code }}</div>
                                            <div class="subtle small">
                                                {{ \Carbon\Carbon::parse($rx->created_at)->format('M d, Y · g:ia') }}
                                                • {{ ucfirst(str_replace('_', ' ', $rx->status)) }}
                                                • {{ str_replace('_', ' ', $rx->encounter ?? 'consult') }}
                                                @if (($rx->refills ?? 0) > 0)
                                                    • Refills: {{ $rx->refills }}
                                                @endif
                                            </div>
                                        </div>
                                        
                                    </div>

                                    {{-- items --}}
                                    @if ($rx->items->count())
                                        <div class="mt-2 d-flex flex-column gap-1">
                                            @foreach ($rx->items as $it)
                                                <div class="rx-item small">
                                                    <strong>{{ $it->medicine }}</strong>
                                                    @if ($it->dosage)
                                                        • {{ $it->dosage }}
                                                    @endif
                                                    @if ($it->frequency)
                                                        • {{ $it->frequency }}
                                                    @endif
                                                    @if ($it->duration)
                                                        • {{ $it->duration }}
                                                    @endif
                                                    @if ($it->qty)
                                                        • Qty: {{ $it->qty }}
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center subtle py-4">No prescriptions yet.</div>
                            @endforelse
                        </div>

                        @if ($prescriptions->hasPages())
                            <div class="mt-3">{!! $prescriptions->onEachSide(0)->links() !!}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
