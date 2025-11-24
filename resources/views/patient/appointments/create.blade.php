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

        .modal-content {
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--text);
        }

        .modal-header {
            border-bottom: 1px solid var(--border);
        }

        .modal-footer {
            border-top: 1px solid var(--border);
        }

        .countdown-text {
            color: var(--muted);
            font-size: 0.9rem;
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

    {{-- Insufficient Balance Modal --}}
    <div class="modal fade" id="insufficientBalanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-black">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-exclamation-triangle text-warning me-2"></i>Insufficient Balance
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">You don't have sufficient funds in your wallet to book this appointment.</p>
                    <div class="mb-3">
                        <strong>Required:</strong> ₦<span id="requiredAmount">0.00</span><br>
                        <strong>Your Balance:</strong> ₦<span id="currentBalance">0.00</span>
                    </div>
                    <p class="countdown-text mb-0">
                        <span id="countdownText">Redirecting to wallet page in <span id="countdown">5</span> seconds...</span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="{{ route('patient.wallet.index') }}" class="btn btn-gradient" id="fundAccountBtn">
                        <i class="fa-solid fa-wallet me-2"></i>Fund Account
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            let countdownInterval = null;

            function clearCountdown() {
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
            }

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
                        if (xhr.status === 422 && xhr.responseJSON?.error === 'insufficient_balance') {
                            // Show insufficient balance modal
                            const data = xhr.responseJSON;
                            $('#requiredAmount').text(parseFloat(data.required_amount || 0).toFixed(2));
                            $('#currentBalance').text(parseFloat(data.current_balance || 0).toFixed(2));
                            
                            const modal = new bootstrap.Modal(document.getElementById('insufficientBalanceModal'));
                            modal.show();
                            
                            // Start countdown
                            let seconds = 5;
                            const $countdown = $('#countdown');
                            
                            clearCountdown();
                            
                            countdownInterval = setInterval(() => {
                                seconds--;
                                $countdown.text(seconds);
                                
                                if (seconds <= 0) {
                                    clearCountdown();
                                    window.location.href = '{{ route('patient.wallet.index') }}';
                                }
                            }, 1000);
                            
                            // Clean up on modal close
                            $('#insufficientBalanceModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
                                clearCountdown();
                            });
                            
                            // Clear countdown when fund account button is clicked
                            $('#fundAccountBtn').off('click').on('click', function() {
                                clearCountdown();
                            });
                        } else {
                            let msg = 'Failed to create appointment';
                            if (xhr.responseJSON?.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                            } else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
                            flash('danger', msg);
                        }
                    })
                    .always(() => unlockBtn($btn));
            });
        })();
    </script>
@endpush
