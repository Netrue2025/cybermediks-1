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

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .field-error {
            color: #f8d7da;
            border-left: 3px solid #dc3545;
            padding-left: .5rem;
            margin-top: .25rem;
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

                                <input type="time" step="60" class="form-control form-control-sm"
                                    name="start[{{ $d }}]" value="{{ substr($row['start'], 0, 5) }}">
                                <input type="time" step="60" class="form-control form-control-sm"
                                    name="end[{{ $d }}]" value="{{ substr($row['end'], 0, 5) }}">


                                {{-- <input type="time" class="form-control form-control-sm" name="start[{{ $d }}]"
                                    value="{{ $row['start'] }}">
                                <input type="time" class="form-control form-control-sm" name="end[{{ $d }}]"
                                    value="{{ $row['end'] }}"> --}}
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

            const $form = $(this);
            const $btn = $('#btnSaveSched');

            // helpers
            const toInputName = (laravelKey) => laravelKey.replace(/\.(\w+)/g, '[$1]');
            const clearErrors = () => {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.field-error').remove();
                $('#schedErrors').remove();
            };
            const showTopErrors = (messages) => {
                if (!messages.length) return;
                const html = `
      <div id="schedErrors" class="alert alert-danger mt-2">
        <div class="fw-semibold mb-1">Please fix the following:</div>
        <ul class="m-0 ps-3">${messages.map(m => `<li>${m}</li>`).join('')}</ul>
      </div>`;
                $form.prepend(html);
            };

            lockBtn($btn);
            clearErrors();

            $.post(`{{ route('doctor.schedule.store') }}`, $form.serialize())
                .done(res => {
                    flash('success', res.message || 'Schedule saved');
                })
                .fail(err => {
                    const status = err.status;
                    const payload = err.responseJSON || {};
                    const msg = payload.message || 'Failed to save schedule';
                    const errors = payload.errors || {};

                    // Collect non-mapped messages to show at top
                    const topMessages = [];
                    if (status !== 422) topMessages.push(msg);

                    // Map each Laravel error (e.g. "start.Mon") to input name "start[Mon]"
                    let firstInvalid = null;
                    Object.entries(errors).forEach(([key, messages]) => {
                        const inputName = toInputName(key);
                        const $field = $form.find(`[name="${inputName}"]`);

                        if ($field.length) {
                            // Mark invalid + append error under the row
                            $field.addClass('is-invalid');
                            // Place error at the row level if available, else right after the field
                            const $row = $field.closest('.slot');
                            const $host = $row.length ? $row : $field.parent();
                            $host.append(
                                `<div class="invalid-feedback d-block field-error">${messages[0]}</div>`
                            );

                            if (!firstInvalid) firstInvalid = $field;
                        } else {
                            // If we can't find the field, push to top errors
                            topMessages.push(messages[0]);
                        }
                    });

                    // Top errors (unknown keys or generic)
                    if (!Object.keys(errors).length) topMessages.push(msg);
                    showTopErrors(topMessages);

                    // Scroll to first invalid input
                    if (firstInvalid && firstInvalid.length) {
                        $('html, body').animate({
                            scrollTop: firstInvalid.offset().top - 120
                        }, 250);
                        firstInvalid.trigger('focus');
                    }

                    flash('danger', 'Please fix the highlighted errors.');
                })
                .always(() => unlockBtn($btn));
        });
    </script>
@endpush
