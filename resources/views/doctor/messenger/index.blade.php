@extends('layouts.doctor')
@section('title', 'Messenger')

@push('styles')
    <style>
        .pane {
            background: #0f1a2e;
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .list {
            height: 70vh;
            overflow: auto;
            border-right: 1px solid var(--border);
        }

        .conv {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
        }

        .conv.active {
            background: #111f37;
        }

        .chat {
            height: 70vh;
            display: flex;
            flex-direction: column;
        }

        .chat-body {
            flex: 1;
            overflow: auto;
            padding: 14px;
        }

        .bubble {
            max-width: 70%;
            padding: 10px 12px;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .me-bubble {
            background: #1a2a48;
            margin-left: auto;
        }

        .them-bubble {
            background: #0d162a;
            border: 1px solid var(--border);
        }

        .chat-input {
            border-top: 1px solid var(--border);
            padding: 10px;
        }

        .small-muted {
            color: #9aa3b2;
            font-size: .85rem;
        }
    </style>
@endpush

@section('content')
    <div class="row g-0 pane">
        {{-- Conversation list --}}
        <div class="col-lg-4 list" id="convList">
            @foreach ($conversations as $c)
                @php
                    $p = $c->patient;
                    $name = trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: 'Patient #' . $p->id;
                    $last = optional($c->messages->first());
                    $preview = $last?->body ? \Illuminate\Support\Str::limit($last->body, 60) : 'No messages yet';
                    $isActive = optional($active)->id === $c->id;
                @endphp
                <div class="conv {{ $isActive ? 'active' : '' }}" data-id="{{ $c->id }}">
                    <div class="fw-semibold">{{ $name }}</div>
                    <div class="small-muted">{{ $preview }}</div>
                </div>
            @endforeach
            {{-- If you want pagination for conversations, add links here --}}
        </div>

        {{-- Chat pane --}}
        <div class="col-lg-8 chat">
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom"
                style="border-color:var(--border)">
                <div class="fw-semibold" id="chatTitle">
                    {{ optional($active?->patient)->first_name }} {{ optional($active?->patient)->last_name }}
                </div>
                <div class="small-muted" id="chatStatus">—</div>
            </div>

            <div class="chat-body" id="chatBody">
                @if ($active)
                    {!! view('doctor.messenger._thread', ['conversation' => $active, 'messages' => []])->render() !!}
                    {{-- We’ll load actual messages via AJAX right after mount to also mark as read --}}
                @else
                    <div class="small-muted">Select a conversation to start.</div>
                @endif
            </div>

            <div class="chat-input">
                <form id="msgForm" class="d-flex gap-2" {{ $active ? '' : 'style=display:none' }}>
                    @csrf
                    <input type="hidden" id="convId" name="conv_id" value="{{ $active->id ?? '' }}">
                    <input class="form-control" name="body" placeholder="Type a message…" autocomplete="off">
                    <button class="btn btn-gradient"><span class="btn-text"><i
                                class="fa-solid fa-paper-plane"></i></span></button>
                </form>
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
                // highlight active
                $('.conv').removeClass('active');
                $('.conv[data-id="' + id + '"]').addClass('active');

                $.get(`{{ url('/doctor/messenger') }}/${id}`, function(html) {
                    $chatBody.html(html);
                    scrollBottom();
                });
            }

            // On page load, if an active conv exists, load it fully (marks read)
            const initial = $('#convId').val();
            if (initial) loadThread(initial);

            // Click a conversation
            $(document).on('click', '.conv', function() {
                const id = $(this).data('id');
                loadThread(id);
            });

            // Send message
            $msgForm.on('submit', function(e) {
                e.preventDefault();
                const id = $('#convId').val();
                const $btn = $(this).find('button');
                const $input = $(this).find('input[name="body"]');
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

            // Optional: light polling for new messages every 6s (only when a thread is open)
            setInterval(function() {
                const id = $('#convId').val();
                if (!id) return;
                // Reload but keep scroll at bottom – simple approach:
                $.get(`{{ url('/doctor/messenger') }}/${id}`, function(html) {
                    $chatBody.html(html);
                    scrollBottom();
                });
            }, 6000);
        })();
    </script>
@endpush
