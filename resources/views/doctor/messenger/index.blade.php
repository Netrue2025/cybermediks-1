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
                        {!! view('doctor.messenger._thread', ['conversation' => $active, 'messages' => []])->render() !!}
                    @else
                        <div class="small-muted">Select a conversation to start.</div>
                    @endif
                </div>

                <div class="chat-input">
                    @php $isClosed = $active && $active->status === 'closed'; @endphp
                    <form id="msgForm" class="composer" {{ $active && !$isClosed ? '' : 'style=display:none' }}>
                        @csrf
                        <input type="hidden" id="convId" name="conv_id" value="{{ $active->id ?? '' }}">
                        <button type="button" class="btn btn-ghost" id="btnAttach" title="Attach"><i
                                class="fa-solid fa-paperclip"></i></button>
                        <textarea class="form-control" name="body"
                            placeholder="{{ $isClosed ? 'Conversation is closed' : 'Type a message…' }}" autocomplete="off"></textarea>
                        <button class="btn btn-gradient" id="btnSend"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                    @if ($isClosed)
                    <br>
                    <button class="btn btn-gradient" id="btnReopen">Reopen Conversation</button>
                    <br>
                        <div class="disabled-note">This conversation is closed. You can reopen it from the patient card.
                        </div>
                    @endif
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
                    loadThread(id);
                    $msgForm.show();
                    $btn.hide();
                    $(".disabled-note").remove();
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
                    loadThread(id);
                }).fail(xhr => {
                    flash('danger', xhr.responseJSON?.message || 'Failed to accept');
                }).always(() => unlockBtn($btn));
            });

            $(document).on('click', '[data-close]', function() {
                const id = $(this).data('close');
                const $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('doctor.conversations.close', ['conversation' => '__ID__']) }}`.replace(
                    '__ID__', id), {
                    _token: `{{ csrf_token() }}`
                }).done(res => {
                    flash('success', res.message || 'Closed');
                    loadThread(id);
                }).fail(xhr => {
                    flash('danger', xhr.responseJSON?.message || 'Failed to close');
                }).always(() => unlockBtn($btn));
            });

            // light polling every 6s only if a thread is open and window focused
            setInterval(function() {
                const id = $('#convId').val();
                if (!id || !document.hasFocus()) return;
                $.get(`{{ url('/doctor/messenger') }}/${id}`, function(html) {
                    // naive replace; your partial already renders newest state and marks read
                    $chatBody.html(html);
                    scrollBottom();
                });
            }, 6000);

        })();
    </script>
@endpush
