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

        .nav-tabs .nav-link {
            border: 1px solid var(--border);
            background: #0e162b;
            color: #cfe0ff;
            border-radius: 999px;
            padding: .4rem .9rem;
        }

        .nav-tabs .nav-link.active {
            background: #13203a;
            border-color: #2a3854;
            color: #fff;
        }

        /* Rows / blocks */
        .blk {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px
        }

        .blk+.blk {
            margin-top: 10px
        }

        .blk-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px
        }

        .blk-title {
            font-weight: 700
        }

        .blk-meta {
            color: #9aa3b2;
            font-size: .9rem
        }

        .blk-subtle {
            color: #9aa3b2;
            font-size: .85rem
        }

        /* Pills / badges */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: #0e162b;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: .22rem .55rem;
            font-size: .85rem;
            color: #cfe0ff
        }

        .pill-kind {
            background: #13203a;
            border-color: #2a3854
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .22rem .6rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #0e162b;
            font-size: .85rem;
            color: #cfe0ff
        }

        .b-scheduled {
            background: rgba(56, 189, 248, .10);
            border-color: #1f4a6f
        }

        .b-completed {
            background: rgba(34, 197, 94, .10);
            border-color: #1f6f43
        }

        .b-cancelled {
            background: rgba(239, 68, 68, .10);
            border-color: #6f2b2b
        }

        .b-pending {
            background: rgba(148, 163, 184, .12);
            border-color: #334155
        }

        .b-ready {
            background: rgba(34, 197, 94, .10);
            border-color: #1f6f43
        }

        .b-picked {
            background: rgba(34, 197, 94, .16);
            border-color: #1f6f43
        }

        .b-expired {
            background: rgba(245, 158, 11, .10);
            border-color: #7a5217
        }

        /* List of Rx items */
        .rx-items {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 8px
        }

        .rx-chip {
            display: inline-flex;
            flex-wrap: wrap;
            gap: .35rem .5rem;
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: .45rem .55rem;
            font-size: .85rem
        }

        .rx-chip strong {
            color: #fff
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
                <ul class="nav nav-tabs border-0" id="histTabs" role="tablist" style="gap:.4rem;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ap-tab" data-bs-toggle="tab" data-bs-target="#ap"
                            type="button" role="tab">
                            <i class="fa-solid fa-calendar-day me-1"></i> Appointments
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rx-tab" data-bs-toggle="tab" data-bs-target="#rx" type="button"
                            role="tab">
                            <i class="fa-solid fa-prescription-bottle-medical me-1"></i> Prescriptions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="lab-tab" data-bs-toggle="tab" data-bs-target="#lab" type="button"
                            role="tab">
                            <i class="fa-solid fa-flask-vial me-1"></i> Labworks
                        </button>
                    </li>

                </ul>

                <div class="tab-content pt-3">

                    {{-- Appointments --}}
                    <div class="tab-pane fade show active" id="ap" role="tabpanel" aria-labelledby="ap-tab">
                        <div class="d-flex flex-column">
                            @forelse($appointments as $a)
                                @php
                                    $apDate = \Carbon\Carbon::parse($a->scheduled_at)->format('D, M d · g:ia');
                                    $apType = str_replace('_', ' ', $a->type);
                                    $apStat = strtolower($a->status ?? 'scheduled');
                                    $apCls = match ($apStat) {
                                        'scheduled' => 'b-scheduled',
                                        'completed' => 'b-completed',
                                        'cancelled' => 'b-cancelled',
                                        default => 'b-pending',
                                    };
                                @endphp

                                <div class="blk">
                                    <div class="blk-head">
                                        <div>
                                            <div class="blk-title">
                                                <i class="fa-regular fa-calendar me-1"></i>{{ $apDate }}
                                            </div>
                                            <div class="blk-meta mt-1">
                                                <span class="pill pill-kind"><i
                                                        class="fa-solid fa-video me-1"></i>{{ ucfirst($apType) }}</span>
                                                @if ($a->title)
                                                    <span class="pill ms-1">{{ $a->title }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge-soft {{ $apCls }}">{{ ucfirst($apStat) }}</span>
                                        </div>
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
                        <div class="d-flex flex-column">
                            @forelse($prescriptions as $rx)
                                @php
                                    $created = \Carbon\Carbon::parse($rx->created_at)->format('M d, Y · g:ia');
                                    $rxStat = strtolower($rx->status ?? 'pending'); // clinical status (e.g., active/expired)
                                    $rxDisp = strtolower($rx->dispense_status ?? ''); // if you have it: pending/ready/picked
                                    $statCls = match ($rxStat) {
                                        'active' => 'b-ready',
                                        'expired' => 'b-expired',
                                        default => 'b-pending',
                                    };
                                    $dispCls = match ($rxDisp) {
                                        'ready' => 'b-ready',
                                        'picked' => 'b-picked',
                                        'cancelled' => 'b-cancelled',
                                        'pending' => 'b-pending',
                                        default => '',
                                    };
                                    $enc = str_replace('_', ' ', $rx->encounter ?? 'consult');
                                    $refills = (int) ($rx->refills ?? 0);
                                @endphp

                                <div class="blk">
                                    <div class="blk-head">
                                        <div>
                                            <div class="blk-title">
                                                <i class="fa-solid fa-prescription me-1"></i>Rx {{ $rx->code }}
                                            </div>
                                            <div class="blk-subtle mt-1">
                                                {{ $created }} • {{ ucfirst($enc) }}
                                                @if ($refills > 0)
                                                    • Refills: {{ $refills }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end d-flex flex-column gap-1 align-items-end">
                                            <span class="badge-soft {{ $statCls }}">{{ ucfirst($rxStat) }}</span>
                                            @if ($rxDisp)
                                                <span class="badge-soft {{ $dispCls }}">
                                                    <i class="fa-solid fa-bag-shopping me-1"></i>{{ ucfirst($rxDisp) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Items --}}
                                    @if ($rx->items->count())
                                        <div class="rx-items">
                                            @foreach ($rx->items as $it)
                                                <div class="rx-chip">
                                                    <strong>{{ $it->drug }}</strong>
                                                    @if ($it->dose)
                                                        • {{ $it->dose }}
                                                    @endif
                                                    @if ($it->frequency)
                                                        • {{ $it->frequency }}
                                                    @endif
                                                    @if ($it->days)
                                                        • {{ $it->days }}
                                                    @endif
                                                    @if ($it->quantity)
                                                        • Qty: {{ $it->quantity }}
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

                    {{-- Labworks --}}
                    <div class="tab-pane fade" id="lab" role="tabpanel" aria-labelledby="lab-tab">
                        <div class="d-flex flex-column">
                            @forelse($labworks as $lab)
                                @php
                                    $st = strtolower($lab->status ?? 'pending');
                                    $cls = match ($st) {
                                        'scheduled' => 'b-scheduled',
                                        'in_progress' => 'b-ready',
                                        'results_uploaded' => 'b-ready',
                                        'completed' => 'b-completed',
                                        'rejected', 'cancelled' => 'b-cancelled',
                                        default => 'b-pending',
                                    };
                                @endphp

                                <div class="blk">
                                    <div class="blk-head">
                                        <div>
                                            <div class="blk-title">
                                                <i class="fa-solid fa-flask-vial me-1"></i>#{{ $lab->code }} —
                                                {{ $lab->lab_type }}
                                            </div>
                                            <div class="blk-subtle mt-1">
                                                Requested {{ $lab->created_at?->format('M d, Y · g:ia') }}
                                                @if ($lab->collection_method)
                                                    • {{ ucfirst($lab->collection_method) }} collection
                                                @endif
                                                @if ($lab->scheduled_at)
                                                    • Scheduled: {{ $lab->scheduled_at->format('M d, Y · g:ia') }}
                                                @endif
                                                @if ($lab->labtech?->first_name)
                                                    • Labtech: {{ $lab->labtech->first_name }}
                                                    {{ $lab->labtech->last_name }}
                                                @endif
                                                @if ($lab->price)
                                                    • ₦{{ number_format($lab->price, 2) }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span
                                                class="badge-soft {{ $cls }}">{{ ucwords(str_replace('_', ' ', $st)) }}</span>
                                            @if ($lab->results_path)
                                                <div class="mt-1">
                                                    <a class="btn btn-outline-light btn-sm" target="_blank"
                                                        href="{{ Storage::disk('public')->url($lab->results_path) }}">
                                                        View Results
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($lab->address || $lab->notes)
                                        <div class="blk-subtle mt-2">
                                            @if ($lab->address)
                                                <div><strong>Address:</strong> {{ $lab->address }}</div>
                                            @endif
                                            @if ($lab->notes)
                                                <div class="mt-1">{{ $lab->notes }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center subtle py-4">No labworks found.</div>
                            @endforelse
                        </div>

                        @if ($labworks->hasPages())
                            <div class="mt-3">{!! $labworks->onEachSide(0)->links() !!}</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
