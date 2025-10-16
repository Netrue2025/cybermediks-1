@extends('layouts.patient')
@section('title', 'Messages')

@push('styles')
    <style>
        :root {
            --border: #27344e;
            --card: #101a2e;
            --panel: #0f1628;
            --muted: #9aa3b2;
            --text: #e5e7eb;
        }

        .msg-wrap {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 16px;
        }

        @media(max-width: 992px) {
            .msg-wrap {
                grid-template-columns: 1fr;
            }
        }

        .pane {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .pane-head {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            background: #0f1628;
        }

        .pane-body {
            padding: 12px;
            max-height: 70vh;
            overflow: auto;
        }

        .conv-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: #dbe3f7;
            text-decoration: none;
        }

        .conv-item:hover {
            background: #0f1628;
        }

        .conv-item.active {
            outline: 1px solid rgba(135, 88, 232, .45);
            background: #121f2a;
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #14203a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #cfe0ff;
        }

        .bubble {
            max-width: 80%;
            padding: 10px 12px;
            border-radius: 12px;
            margin-bottom: 8px;
            display: inline-block;
        }

        .me-bubble {
            background: #1b2a4a;
            border: 1px solid var(--border);
            color: #e5e7eb;
            border-bottom-right-radius: 4px;
        }

        .other-bubble {
            background: #0f1628;
            border: 1px solid var(--border);
            color: #e5e7eb;
            border-bottom-left-radius: 4px;
        }

        .bubble-meta {
            color: var(--muted);
            font-size: .8rem;
            margin-top: 2px;
        }

        .composer {
            border-top: 1px solid var(--border);
            padding: 10px;
            background: #0f1628;
        }

        .composer .form-control {
            background: #0b1222;
            color: var(--text);
            border: 1px solid var(--border);
        }
    </style>
@endpush

@section('content')
    <div class="msg-wrap">
        {{-- Left: Conversations --}}
        <div class="pane">
            <div class="pane-head d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Conversations</div>
                <a href="{{ route('patient.dashboard') }}" class="text-decoration-none" style="color:#cfe0ff;">
                    <i class="fa-solid fa-plus me-1"></i> New
                </a>
            </div>
            <div class="pane-body" id="convList">
                <div class="text-center text-secondary py-4">Loading...</div>
            </div>
        </div>

        {{-- Right: Thread --}}
        <div class="pane d-flex flex-column">
            <div class="pane-head" id="threadHead">
                <span class="fw-semibold">Select a conversation</span>
            </div>

            <div class="pane-body" id="threadBody"></div>

            <div class="composer">
                <form id="sendForm" class="d-flex gap-2">
                    <input type="hidden" id="convId">
                    <input class="form-control" id="msgBody" placeholder="Type a message..." autocomplete="off">
                    <button class="btn btn-gradient" type="submit"><span class="btn-text">Send</span></button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const $convList = $('#convList');
        const $threadHead = $('#threadHead');
        const $threadBody = $('#threadBody');
        const $sendForm = $('#sendForm');
        const $msgBody = $('#msgBody');
        const $convId = $('#convId');

        let activeId = null;

        function initialsOf(name) {
            return (name || '').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
        }

        function renderConvItem(c) {
            return `
                <a href="#" class="conv-item ${activeId===c.id?'active':''}" data-id="${c.id}">
                    <div class="avatar-sm">${c.initials}</div>
                    <div class="flex-grow-1">
                    <div class="fw-semibold">${c.doctor.name}</div>
                    <div class="text-white small">${c.updated_at}</div>
                    </div>
                </a>
            `;
        }

        function fetchConversations() {
            $.get(`{{ route('patient.messages.conversations') }}`)
                .done(res => {
                    if (!res.data.length) {
                        $convList.html('<div class="text-center text-secondary py-4">No conversations yet.</div>');
                        return;
                    }
                    $convList.empty();
                    res.data.forEach(c => $convList.append(renderConvItem(c)));

                    // open from ?c= or first conversation
                    const urlC = new URLSearchParams(location.search).get('c');
                    const openId = urlC || res.data[0].id;
                    openConversation(openId);
                })
                .fail(() => $convList.html('<div class="text-center text-danger py-4">Failed to load.</div>'));
        }

        function openConversation(id) {
            activeId = id;
            $convId.val(id);
            $convList.find('.conv-item').removeClass('active');
            $convList.find(`.conv-item[data-id="${id}"]`).addClass('active');

            $.get(`{{ url('/patient/messages') }}/${id}`)
                .done(res => {
                    $threadHead.html(
                        `<span class="fw-semibold"><i class="fa-solid fa-user-doctor me-2"></i>${res.conversation.doctor.name}</span>`
                    );
                    $threadBody.empty();
                    res.messages.forEach(m => appendMessage(m, m.sender_id === {{ auth()->id() }}));
                    $threadBody.scrollTop($threadBody[0].scrollHeight);
                })
                .fail(() => flash('danger', 'Could not open conversation'));
        }

        function appendMessage(m, mine = false) {
            const bubbleClass = mine ? 'me-bubble ms-auto' : 'other-bubble';
            $threadBody.append(`
                <div class="d-flex ${mine?'justify-content-end':''}">
                    <div>
                    <div class="bubble ${bubbleClass}">${$('<div/>').text(m.body).html()}</div>
                    <div class="bubble-meta ${mine?'text-end':''}">${m.created_at}</div>
                    </div>
                </div>
            `);
        }

        // click a conversation
        $(document).on('click', '.conv-item', function(e) {
            e.preventDefault();
            openConversation($(this).data('id'));
        });

        // send form
        $sendForm.on('submit', function(e) {
            e.preventDefault();
            const id = $convId.val();
            const body = $msgBody.val().trim();
            if (!id || !body) return;

            const $btn = $(this).find('button[type=submit]');
            lockBtn($btn);

            $.post(`{{ url('/patient/messages') }}/${id}`, {
                    body
                })
                .done(res => {
                    appendMessage(res.message, true);
                    $msgBody.val('');
                    $threadBody.scrollTop($threadBody[0].scrollHeight);
                })
                .fail(xhr => flash('danger', xhr.responseJSON?.message || 'Failed to send'))
                .always(() => unlockBtn($btn));
        });

        // Boot
        setInterval(() => {
            fetchConversations();
        }, 5000);

        fetchConversations();

        // If coming from “Chat” button: support both ?doctor and ?doctor_id
        (function() {
            const qs = new URLSearchParams(location.search);
            const docId = qs.get('doctor') || qs.get('doctor_id');
            if (!docId) return;

            $.post(`{{ route('patient.messages.start') }}`, {
                    doctor_id: docId
                })
                .done(res => {
                    if (res.redirect) {
                        // Server returns ?c=<conversation_id>
                        location.href = res.redirect;
                    } else {
                        // Fallback: re-fetch convos and open the newest
                        fetchConversations();
                    }
                })
                .fail(xhr => flash('danger', xhr.responseJSON?.message || 'Could not start conversation'));
        })();
    </script>
@endpush
