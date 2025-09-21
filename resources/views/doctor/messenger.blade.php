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
    </style>
@endpush

@section('content')
    <div class="row g-0 pane">
        <div class="col-lg-4 list">
            <div class="conv active">
                <div class="fw-semibold">Ebuka Mbanusi</div>
                <div class="text-secondary small">Last message preview…</div>
            </div>
            <div class="conv">
                <div class="fw-semibold">Don Joe</div>
                <div class="text-secondary small">Hi doctor, about my results…</div>
            </div>
        </div>

        <div class="col-lg-8 chat">
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom"
                style="border-color:var(--border)">
                <div class="fw-semibold">Ebuka Mbanusi</div>
                <div class="text-secondary small">Online</div>
            </div>
            <div class="chat-body">
                <div class="bubble them-bubble">Hello doctor 👋</div>
                <div class="bubble me-bubble">Hi! How can I help today?</div>
            </div>
            <div class="chat-input">
                <form id="msgForm" class="d-flex gap-2">
                    @csrf
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
        $('#msgForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button');
            lockBtn($btn);
            // TODO: POST to /doctor/messages
            setTimeout(() => {
                flash('success', 'Sent (demo)');
                unlockBtn($btn);
            }, 400);
        });
    </script>
@endpush
