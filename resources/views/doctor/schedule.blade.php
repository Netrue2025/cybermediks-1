@extends('layouts.doctor')
@section('title', 'Manage Schedule')

@push('styles')
    <style>
        .cardx {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
        }

        .slot {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="cardx">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-calendar-days"></i>
                    <h5 class="m-0">Availability</h5>
                </div>
                <div class="text-secondary mb-2">Set the days and time windows you’re available.</div>

                <form id="schedForm">
                    @csrf
                    @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d)
                        @php
                            $row = $schedule[$d] ?? ['start' => '09:00', 'end' => '17:00', 'enabled' => true];
                        @endphp
                        <div class="slot d-flex align-items-center justify-content-between mb-2">
                            <div class="fw-semibold">{{ $d }}</div>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="time" class="form-control form-control-sm" name="start[{{ $d }}]"
                                    value="{{ $row['start'] }}">
                                <input type="time" class="form-control form-control-sm" name="end[{{ $d }}]"
                                    value="{{ $row['end'] }}">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="enabled[{{ $d }}]"
                                        {{ $row['enabled'] ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-grid mt-3">
                        <button class="btn btn-gradient" id="btnSaveSched"><span class="btn-text">Save
                                Schedule</span></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="cardx h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-clock"></i>
                    <h5 class="m-0">Upcoming Appointments</h5>
                </div>
                <div class="text-secondary mb-2">Overview of your next visits</div>

                <div class="d-flex flex-column gap-2">
                    @forelse($upcoming as $a)
                        <div class="d-flex justify-content-between align-items-start slot">
                            <div>
                                <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($a->scheduled_at)->format('D, M d · g:ia') }}
                                </div>
                                <div class="text-secondary small">
                                    Patient: {{ $a->patient?->first_name }} {{ $a->patient?->last_name }}
                                    @if ($a->title)
                                        — {{ $a->title }}
                                    @endif
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-dark border">{{ str_replace('_', ' ', $a->type) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-secondary py-4">No upcoming appointments.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#schedForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#btnSaveSched');
            lockBtn($btn);

            $.post(`{{ route('doctor.schedule.store') }}`, $(this).serialize())
                .done(res => {
                    flash('success', res.message || 'Schedule saved');
                })
                .fail(err => {
                    const msg = err.responseJSON?.message || 'Failed to save schedule';
                    flash('danger', msg);
                    if (err.responseJSON?.errors) console.warn(err.responseJSON.errors);
                })
                .always(() => unlockBtn($btn));
        });
    </script>
@endpush
