@extends('layouts.patient')
@section('title', 'Create Appointment')

@push('styles')
    <style>
        :root {
            --border: #27344e;
            --card: #101a2e;
            --muted: #9aa3b2;
            --text: #e5e7eb;
            --success: #22c55e;
            --accent1: #8758e8;
            --accent2: #e0568a;
        }

        .cardx {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
        }

        .btn-gradient {
            background-image: linear-gradient(90deg, var(--accent1), var(--accent2));
            border: 0;
            color: #fff;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            background: #0b1222;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6b7280;
            box-shadow: none;
        }

        .doctor-mini {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #0f1628;
        }

        .avatar-sm {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #14203a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cfe0ff;
            font-weight: 700;
        }

        .subtle {
            color: var(--muted);
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="cardx">
                <h5 class="mb-1"><i class="fa-solid fa-calendar-plus me-2"></i>New Appointment</h5>
                <div class="subtle mb-3">Choose doctor, consultation type, and time.</div>

                <form id="apptForm" autocomplete="off">
                    @csrf

                    {{-- Doctor (preselected from query if provided) --}}
                    <div class="mb-3">
                        <label class="form-label">Doctor</label>
                        @if ($doctor)
                            <div class="doctor-mini mb-2">
                                <div class="avatar-sm">
                                    {{ strtoupper(substr($doctor->first_name, 0, 1)) . strtoupper(substr($doctor->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $doctor->first_name . ' '. $doctor->last_name }}</div>
                                    <div class="subtle small">ID: {{ $doctor->id }}</div>
                                </div>
                            </div>
                            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
                        @else
                            <select class="form-select" name="doctor_id" required>
                                <option value="">Select a doctor</option>
                                @foreach (\App\Models\User::where('role', 'doctor')->orderBy('first_name')->get(['id', 'first_name', 'last_name']) as $d)
                                    <option value="{{ $d->id }}">{{ $d->first_name }} {{ $d->last_name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="video">Video</option>
                                <option value="chat">Chat</option>
                                <option value="in_person">In-person</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Date & Time</label>
                            <input type="datetime-local" class="form-control" name="scheduled_at">
                            <div class="form-text text-white">Leave blank for “as soon as possible” for chat.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Duration (mins)</label>
                            <input type="number" class="form-control" name="duration" min="5" max="180"
                                placeholder="15">
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Reason (optional)</label>
                        <input class="form-control" name="reason" maxlength="255" placeholder="Short description">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="subtle">Price will auto-use doctor’s consult fee.</div>
                        <button class="btn btn-gradient" type="submit"><span class="btn-text">Request
                                Appointment</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#apptForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type=submit]');
            lockBtn($btn);
            $.post(`{{ route('patient.appointments.store') }}`, $(this).serialize())
                .done(res => {
                    flash('success', res.message || 'Appointment created');
                    if (res.redirect) window.location = res.redirect;
                })
                .fail(xhr => {
                    let msg = 'Failed to create appointment';
                    if (xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                    flash('danger', msg);
                })
                .always(() => unlockBtn($btn));
        });
    </script>
@endpush
