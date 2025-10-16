@extends('layouts.doctor')
@section('title', 'Messenger')

@push('styles')
    <style>
        .pane {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px
        }

        .divider {
            border-bottom: 1px solid var(--border)
        }

        /* Left: conversations */
        .side {
            height: 76vh;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border);
        }

        .side-head {
            padding: 10px 12px
        }

        .search-wrap {
            position: relative
        }

        .search-wrap .ico {
            position: absolute;
            left: .6rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9aa3b2
        }

        .search-wrap input {
            padding-left: 2rem
        }

        .tabs {
            display: flex;
            gap: 6px;
            flex-wrap: wrap
        }

        .tab {
            border: 1px solid var(--border);
            background: #0e162b;
            border-radius: 999px;
            padding: .25rem .6rem;
            font-size: .85rem;
            cursor: pointer;
            color: #b8c2d6;
        }

        .tab.active {
            background: #13203a;
            border-color: #2a3854;
            color: #e5e7eb
        }

        .list {
            flex: 1;
            overflow: auto
        }

        .conv {
            display: grid;
            grid-template-columns: 42px 1fr auto;
            gap: 10px;
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
        }

        .conv:hover {
            background: #101c33
        }

        .conv.active {
            background: #111f37
        }

        .avatar-sm {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #14203a;
            color: #cfe0ff;
            font-weight: 700
        }

        .c-name {
            font-weight: 600
        }

        .c-prev {
            color: #9aa3b2;
            font-size: .85rem
        }

        .c-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px
        }

        .c-time {
            color: #9aa3b2;
            font-size: .8rem;
            white-space: nowrap
        }

        .badge-dot {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .1rem .45rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .75rem;
            background: #0e162b
        }

        .badge-unread {
            background: rgba(34, 197, 94, .1);
            border-color: #1f6f43;
            color: #b9ffcf
        }

        /* Center: chat */
        .chat {
            height: 76vh;
            display: flex;
            flex-direction: column
        }

        .chat-head {
            padding: 10px 12px
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .55rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            font-size: .85rem;
            background: #0e162b;
            color: #cfe0ff
        }

        .pill.on {
            background: rgba(34, 197, 94, .08);
            border-color: #1f6f43
        }

        .pill.pending {
            background: rgba(245, 158, 11, .08);
            border-color: #856013
        }

        .pill.closed {
            background: rgba(239, 68, 68, .08);
            border-color: #6f2b2b
        }

        .chat-body {
            flex: 1;
            overflow: auto;
            padding: 14px 14px 6px
        }

        .day-sep {
            position: sticky;
            top: 0;
            background: rgba(15, 26, 46, .85);
            backdrop-filter: blur(6px);
            padding: 6px 10px;
            border: 1px solid var(--border);
            border-radius: 999px;
            width: max-content;
            margin: 8px auto;
            color: #9aa3b2;
            font-size: .8rem
        }

        .bubble {
            max-width: 74%;
            padding: 10px 12px;
            border-radius: 12px;
            margin-bottom: 8px;
            word-wrap: break-word
        }

        .me-bubble {
            background: #1a2a48;
            margin-left: auto
        }

        .them-bubble {
            background: #0d162a;
            border: 1px solid var(--border)
        }

        .b-meta {
            color: #9aa3b2;
            font-size: .75rem;
            margin-top: 4px
        }

        .chat-input {
            border-top: 1px solid var(--border);
            padding: 10px
        }

        .composer {
            display: grid;
            grid-template-columns: 38px 1fr 42px;
            gap: 8px;
            align-items: end
        }

        .btn-ghost {
            background: #0e162b;
            border: 1px solid #283652;
            color: #e5e7eb
        }

        .btn-ghost:hover {
            background: #1a2845;
            color: #fff
        }

        textarea.form-control {
            min-height: 42px;
            max-height: 180px;
            resize: vertical
        }

        .disabled-note {
            color: #9aa3b2;
            font-size: .85rem
        }

        @media (max-width: 992px) {
            .side {
                height: 38vh;
                border-right: 0;
                border-bottom: 1px solid var(--border)
            }

            .chat {
                height: calc(76vh + 38vh)
            }
        }
    </style>
@endpush

@section('content')
    <div class="pane">
        <div class="row g-0">

            {{-- LEFT: Conversations --}}
            <div class="col-lg-4 side">
                <div class="side-head divider">
                    <div class="search-wrap mb-2">
                        <i class="fa-solid fa-magnifying-glass ico"></i>
                        <input id="convSearch" class="form-control" placeholder="Search patient or message…">
                    </div>
                    <div class="tabs" id="convTabs">
                        @php $filter = request('filter'); @endphp
                        <span class="tab {{ $filter === '' || $filter === null ? 'active' : '' }}" data-filter="">All</span>
                        <span class="tab {{ $filter === 'pending' ? 'active' : '' }}" data-filter="pending">Pending</span>
                        <span class="tab {{ $filter === 'active' ? 'active' : '' }}" data-filter="active">Active</span>
                        <span class="tab {{ $filter === 'closed' ? 'active' : '' }}" data-filter="closed">Closed</span>
                    </div>
                </div>

                <div class="list" id="convList">
                    @foreach ($conversations as $c)
                        @php
                            $p = $c->patient;
                            $name = trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: 'Patient #' . $p->id;
                            $last = optional($c->messages->first());
                            $preview = $last?->body
                                ? \Illuminate\Support\Str::limit($last->body, 60)
                                : 'No messages yet';
                            $isActive = optional($active)->id === $c->id;
                            $initials =
                                strtoupper(
                                    mb_substr($p->first_name ?? '', 0, 1) . mb_substr($p->last_name ?? '', 0, 1),
                                ) ?:
                                'PT';
                            $unread = property_exists($c, 'unread_for_doctor') ? (int) $c->unread_for_doctor : 0;
                        @endphp
                        <div class="conv {{ $isActive ? 'active' : '' }}" data-id="{{ $c->id }}"
                            data-name="{{ $name }}" data-preview="{{ $preview }}"
                            data-status="{{ $c->status }}">
                            <div class="avatar-sm">{{ $initials }}</div>
                            <div>
                                <div class="c-name">{{ $name }}</div>
                                <div class="c-prev">{{ $preview }}</div>
                            </div>
                            <div class="c-meta">
                                <div class="c-time">{{ $c->updated_at?->shortRelativeDiffForHumans() }}</div>
                                @if ($unread > 0)
                                    <span class="badge-dot badge-unread"><i class="fa-solid fa-circle"
                                            style="font-size:.5rem"></i> {{ $unread }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                {{-- add pagination here if needed --}}
            </div>

            {{-- CENTER: Chat --}}
            <div class="col-lg-8 chat">
                <div class="chat-head divider d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $p = $active?->patient;
                            $initials =
                                strtoupper(
                                    mb_substr($p->first_name ?? '', 0, 1) . mb_substr($p->last_name ?? '', 0, 1),
                                ) ?:
                                'PT';
                        @endphp
                        <div class="avatar-sm" id="chatAvatar">{{ $active ? $initials : 'PT' }}</div>
                        <div>
                            <div class="fw-semibold" id="chatTitle">
                                {{ optional($p)->first_name }} {{ optional($p)->last_name }}
                            </div>
                            <div class="small-muted" id="chatStatus">
                                @if ($active)
                                    {{ ucfirst($active->status) }} • updated {{ $active->updated_at?->diffForHumans() }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2" id="chatActions">
                        @if ($active)
                            @if ($active->status === 'pending')
                                <span class="pill pending"><i class="fa-solid fa-clock"></i> Pending</span>
                                <button class="btn btn-success btn-sm" data-accept="{{ $active->id }}"><i
                                        class="fa-solid fa-check me-1"></i> Accept</button>
                                <button class="btn btn-ghost btn-sm" data-close="{{ $active->id }}"><i
                                        class="fa-solid fa-xmark me-1"></i> Close</button>
                            @elseif($active->status === 'active')
                                <span class="pill on"><i class="fa-solid fa-circle-check"></i> Active</span>
                                <button class="btn btn-ghost btn-sm" data-close="{{ $active->id }}"><i
                                        class="fa-solid fa-xmark me-1"></i> Close</button>
                            @else
                                <span class="pill closed"><i class="fa-solid fa-ban"></i> Closed</span>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="chat-body" id="chatBody">
                    @if ($active)
                        {!! view('doctor.messenger._thread', [
                            'conversation' => $active,
                            'messages' => [],
                            'patientId' => $patientId,
                            'patientDetails' => $patientDetails,
                            'appointmentId' => $appointmentId,
                        ])->render() !!}
                    @else
                        <div class="small-muted">Select a conversation to start.</div>
                    @endif
                </div>

                <div class="chat-input border-top py-2">
                    @php $isClosed = $active && $active->status === 'closed'; @endphp

                    <form id="msgForm" class="{{ $active && !$isClosed ? '' : 'd-none' }}">
                        @csrf
                        <input type="hidden" id="convId" name="conv_id" value="{{ $active->id ?? '' }}">

                        <div class="input-group">
                            <button type="button" class="btn btn-gradient" id="btnPrescription" title="Attach">
                                New Prescription
                            </button>

                            <textarea class="form-control" name="body" rows="1"
                                placeholder="{{ $isClosed ? 'Conversation is closed' : 'Type a message…' }}" autocomplete="off"></textarea>

                            <button type="submit" class="btn btn-primary" id="btnSend">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>

                    @if ($isClosed)
                        <div class="mt-2">
                            <button class="btn btn-primary" id="btnReopen">Reopen Conversation</button>
                            <div class="form-text mt-2">This conversation is closed. You can reopen it from the patient
                                card.</div>
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>

    {{-- PRESCRIPTION MODAL --}}
    <div class="modal fade" id="prescriptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:#121a2c;border:1px solid var(--border);border-radius:18px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Add Prescrition</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rxForm" class="cardx">
                        @csrf
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-file-medical"></i>
                            <h5 class="m-0">New e-Prescription</h5>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">Patient</label>
                                <input type="text" id="patientId" name="patient_id" hidden value="">
                                <input type="text" id="appointmentId" name="appointment_id" hidden value="">
                                <h3 id="patientDetails"></h3>

                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Encounter Type</label>
                                <select class="form-select disabled" name="encounter" required>
                                    <option value="chat" selected>Chat</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Refills</label>
                                <input type="number" min="0" value="0" class="form-control"
                                    name="refills">
                            </div>
                        </div>

                        <hr class="my-4" style="border-color:var(--border);opacity:.6">

                        <div id="rxItems" class="d-flex flex-column gap-2">
                            <div class="rx-item">
                                <div class="row g-2">
                                    <div class="col-lg-4">
                                        <label class="form-label">Drug</label>
                                        <input class="form-control" name="items[0][drug]" placeholder="Amoxicillin 500mg"
                                            required>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Dose</label>
                                        <input class="form-control" name="items[0][dose]" placeholder="1 tab">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Freq.</label>
                                        <input class="form-control" name="items[0][freq]" placeholder="2×/day">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Days</label>
                                        <input class="form-control" type="number" name="items[0][days]"
                                            placeholder="7">
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="form-label">Qty</label>
                                        <input class="form-control" type="number" name="items[0][quantity]"
                                            placeholder="14">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-outline-light btn-sm" id="addItem"><i
                                    class="fa-solid fa-plus me-1"></i>Add Item</button>
                            <button class="btn btn-gradient ms-auto" id="btnIssue"><span class="btn-text">Issue
                                    Prescription</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="closeChatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background:#0f1628;border:1px solid #27344e;border-radius:18px;color:#e5e7eb;">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Close Chat</h5>
                    <button type="button" id="closeModalBtn" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Hidden input ensures 0 is sent when unchecked -->
                    <input type="hidden" name="prescription_is_required" value="0">

                    <div class="form-check form-switch d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="prescription_is_required"
                            name="prescription_is_required" value="1">
                        <label class="form-check-label" for="prescription_is_required">
                            Prescription not required
                        </label>
                    </div>

                    <p class="mt-3 mb-0" style="font-size:.9rem;color:#9aa3b2;">
                        Toggle this if you can close the chat without issuing a prescription.
                    </p>

                    <div class="mt-4 d-flex gap-2">
                        <button type="button" class="btn btn-outline-light flex-fill" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn flex-fill" id="endChat" data-close="{{ $active?->id }}"
                            style="background:linear-gradient(135deg,#3b82f6,#06b6d4);color:#fff;border:0;">
                            Close Chat
                        </button>
                    </div>
                </div>

                <div class="modal-footer border-0 d-flex justify-content-between">
                    <button class="btn btn-success" id="newPrescription" type="button">
                        New Prescription
                    </button>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script>
        (function() {
            const $chatBody = $('#chatBody');
            const $msgForm = $('#msgForm');

            function scrollBottom() {
                $chatBody.scrollTop($chatBody.prop('scrollHeight'));
            }

            function loadThread(id) {
                $('#convId').val(id);
                $msgForm.show();

                // highlight selection
                $('.conv').removeClass('active');
                $('.conv[data-id="' + id + '"]').addClass('active');

                // title + status from list row data (instant UI)
                const $row = $('.conv[data-id="' + id + '"]');
                const name = $row.data('name') || 'Patient';
                $('#chatTitle').text(name);

                $.get(`{{ url('/doctor/messenger') }}/${id}`, function(html) {
                    $chatBody.html(html);
                    scrollBottom();
                });

                // optional: update header status text after load by pinging a tiny meta endpoint if you have one
            }

            // initial load (also marks read server-side per your existing endpoint)
            const initial = $('#convId').val();
            if (initial) loadThread(initial);

            // list click
            $(document).on('click', '.conv', function() {
                loadThread($(this).data('id'));
            });

            // send
            $msgForm.on('submit', function(e) {
                e.preventDefault();
                const id = $('#convId').val();
                const $btn = $('#btnSend');
                const $input = $(this).find('textarea[name="body"]');
                const body = $input.val().trim();
                if (!body) return;
                lockBtn($btn);

                $.post(`{{ url('/doctor/messenger') }}/${id}/messages`, $(this).serialize())
                    .done(res => {
                        if (res?.html) {
                            $chatBody.append(res.html);
                            $input.val('');
                            scrollBottom();
                        }
                    })
                    .fail(err => {
                        flash('danger', err.responseJSON?.message || 'Failed to send');
                    })
                    .always(() => unlockBtn($btn));
            });

            // Ctrl+Enter to send
            $(document).on('keydown', 'textarea[name="body"]', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    $('#msgForm').trigger('submit');
                }
            });

            // reopen button
            $('#btnReopen').on('click', function() {
                const id = $('#convId').val();
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('doctor.conversations.reopen', ['conversation' => '__ID__']) }}`.replace(
                    '__ID__', id), {
                    _token: `{{ csrf_token() }}`
                }).done(res => {
                    flash('success', res.message || 'Reopened');
                    window.location.reload()
                }).fail(xhr => {
                    flash('danger', xhr.responseJSON?.message || 'Failed to reopen');
                }).always(() => unlockBtn($btn));
            });

            // filter tabs (client-side quick filter + optional server reload)
            $('#convTabs .tab').on('click', function() {
                $('#convTabs .tab').removeClass('active');
                $(this).addClass('active');
                const f = $(this).data('filter') || '';
                // quick client filter
                if (!f) {
                    $('.conv').show();
                } else {
                    $('.conv').each(function() {
                        $(this).toggle($(this).data('status') === f);
                    });
                }
                // or do a server reload:
                // window.location = `{{ route('doctor.messenger') }}?filter=${encodeURIComponent(f)}`
            });

            // search (client-side)
            $('#convSearch').on('input', function() {
                const q = ($(this).val() || '').toLowerCase();
                $('.conv').each(function() {
                    const text = (($(this).data('name') || '') + ' ' + ($(this).data('preview') || ''))
                        .toLowerCase();
                    $(this).toggle(text.includes(q));
                });
            });

            // quick accept/close from header (uses routes provided earlier)
            $(document).on('click', '[data-accept]', function() {
                const id = $(this).data('accept');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('doctor.conversations.accept', ['conversation' => '__ID__']) }}`.replace(
                    '__ID__', id), {
                    _token: `{{ csrf_token() }}`
                }).done(res => {
                    flash('success', res.message || 'Accepted');
                    window.location.reload();
                }).fail(xhr => {
                    flash('danger', xhr.responseJSON?.message || 'Failed to accept');
                }).always(() => unlockBtn($btn));
            });

            $(document).on('click', '[data-close]', function() {
                new bootstrap.Modal(document.getElementById('closeChatModal')).show();
            });

            $("#newPrescription").on('click', function() {
                $("#closeModalBtn").trigger('click');
                $("#btnPrescription").trigger('click');
            })

            $('#endChat').on('click', function() {
                const is_required = $('#prescription_is_required').prop('checked') ? 1 : 0;
                const id = $(this).data('close');
                const $btn = $(this);

                if (is_required === 0) {
                    flash('danger', 'Prescription is required');
                    return;
                }

                lockBtn($btn);

                $.post(
                        `{{ route('doctor.conversations.close', ['conversation' => '__ID__']) }}`.replace(
                            '__ID__', id), {
                            _token: `{{ csrf_token() }}`,
                            prescription_is_required: is_required // <-- send it
                        }
                    )
                    .done(res => {
                        flash('success', res.message || 'Closed');
                        window.location.reload();
                        // loadThread(id); // not needed if you reload
                    })
                    .fail(xhr => {
                        flash('danger', xhr.responseJSON?.message || 'Failed to close');
                    })
                    .always(() => unlockBtn($btn));
            });


            // light polling every 6s only if a thread is open and window focused
            setInterval(function() {
                const id = $('#convId').val();
                $.get(`{{ url('/doctor/messenger') }}/${id}`, function(html) {
                    // naive replace; your partial already renders newest state and marks read
                    $chatBody.html(html);
                    scrollBottom();
                });
            }, 5000);

            $('#addItem').on('click', function() {
                const i = rxIndex++;
                $('#rxItems').append(`
                <div class="rx-item">
                    <div class="row g-2">
                    <div class="col-lg-4"><label class="form-label">Drug</label><input class="form-control" name="items[${i}][drug]" placeholder="e.g., Amoxicillin 500mg" required></div>
                    <div class="col-lg-2"><label class="form-label">Dose</label><input class="form-control" name="items[${i}][dose]" placeholder="1 tab"></div>
                    <div class="col-lg-2"><label class="form-label">Freq.</label><input class="form-control" name="items[${i}][freq]" placeholder="2×/day"></div>
                    <div class="col-lg-2"><label class="form-label">Days</label><input class="form-control" type="number" name="items[${i}][days]" placeholder="7"></div>
                    <div class="col-lg-2"><label class="form-label">Qty</label><input class="form-control" type="number" name="items[${i}][quantity]" placeholder="14"></div>
                    </div>
                </div>`);
            });

            $('#rxForm').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#btnIssue');
                lockBtn($btn);

                $.ajax({
                    url: `{{ route('doctor.prescriptions.store') }}`,
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': `{{ csrf_token() }}`
                    },
                    success: function(res) {
                        flash('success', res.message || 'Prescription issued');
                        // fallback: clear the form for another entry
                        $('#rxForm')[0].reset();
                        $('#rxItems').html($('#rxItems .rx-item').first()); // keep first row
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to issue prescription';
                        flash('danger', msg);
                        // Optional: show validation errors
                        if (xhr.responseJSON?.errors) {
                            console.warn(xhr.responseJSON.errors);
                        }
                    },
                    complete: function() {
                        unlockBtn($btn);
                    }
                });
            });

            $('#btnPrescription').on('click', function() {
                let appointmentId = $("#serverAppointmentId").val();
                let patientId = $("#serverPatientId").val();
                let patientName = $('#serverPatientDetails').val();
                let patientDetails = patientName + ' (ID: ' + patientId + ')';
                $("#patientId").val(patientId)
                $("#appointmentId").val(appointmentId)
                $("#patientDetails").text(patientDetails);
                new bootstrap.Modal(document.getElementById('prescriptionModal')).show();
            });

        })();
    </script>
@endpush
