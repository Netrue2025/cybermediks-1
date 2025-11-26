@extends('layouts.labtech')
@section('title', 'Labwork ' . $lab->code)

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="cardx">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fw-bold">#{{ $lab->code }} — {{ $lab->lab_type }}</div>
                        <div class="text-secondary small">
                            Status: {{ ucwords(str_replace('_', ' ', $lab->status)) }}
                            @if ($lab->scheduled_at)
                                • Scheduled: {{ $lab->scheduled_at->format('M d, Y g:ia') }}
                            @endif
                            @if ($lab->price)
                                • Price: ₦{{ number_format($lab->price, 2) }}
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                <div class="small text-secondary">
                    <div><strong>Patient:</strong> {{ $lab->patient?->first_name }} {{ $lab->patient?->last_name }}</div>
                    <div><strong>Collection:</strong> {{ $lab->collection_method === 'home' ? 'Home' : 'In Lab' }}</div>
                    @if ($lab->address)
                        <div><strong>Address:</strong> {{ $lab->address }}</div>
                    @endif
                    @if ($lab->preferred_at)
                        <div><strong>Preferred:</strong> {{ $lab->preferred_at->format('M d, Y g:ia') }}</div>
                    @endif
                    @if ($lab->notes)
                        <div class="mt-2">{{ $lab->notes }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- inside resources/views/labtech/labworks/show.blade.php --}}

        <div class="col-lg-5">
            <div class="cardx">
                @php
                    $st = $lab->status;
                    $hasResults = !empty($lab->results_path);

                    // Derived step flags
                    $accepted = in_array(
                        $st,
                        ['accepted', 'scheduled', 'in_progress', 'results_uploaded', 'completed'],
                        true,
                    );
                    $scheduled = !is_null($lab->scheduled_at);
                    $priced = !is_null($lab->price) && $lab->price >= 0; // require a price (>=0)
                    $mayStart = $accepted && $scheduled && $priced;
                    $inProgress = $st === 'in_progress' || $st === 'results_uploaded' || $st === 'completed';
                    $canUpload = $inProgress;
                    $canComplete = $st === 'results_uploaded' && $hasResults;
                    $isFinished = in_array($st, ['rejected', 'cancelled', 'completed'], true);
                @endphp

                <style>
                    .step {
                        display: flex;
                        gap: .6rem;
                        align-items: flex-start;
                        padding: .5rem .25rem;
                        opacity: .95
                    }

                    .step .dot {
                        width: 14px;
                        height: 14px;
                        border-radius: 50%;
                        margin-top: .25rem;
                        background: #2a3854;
                        border: 1px solid var(--border)
                    }

                    .step.done .dot {
                        background: #22c55e;
                        border-color: #1f6f43
                    }

                    .step.active .dot {
                        background: #60a5fa;
                        border-color: #2563eb
                    }

                    .step .meta {
                        flex: 1
                    }

                    .step .title {
                        font-weight: 700
                    }

                    .step .hint {
                        color: var(--muted);
                        font-size: .9rem
                    }

                    fieldset:disabled {
                        opacity: .55
                    }
                </style>

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold">Workflow</div>
                    <span class="badge-soft {{ $st === 'completed' ? 'badge-on' : ($isFinished ? 'badge-off' : '') }}">
                        {{ ucwords(str_replace('_', ' ', $st)) }}
                    </span>
                </div>

                {{-- STEP 1: Accept / Reject --}}
                <div class="step {{ $accepted || $isFinished ? 'done' : ($st === 'pending' ? 'active' : '') }}">
                    <div class="dot"></div>
                    <div class="meta">
                        <div class="title">1) Review</div>
                        <div class="hint">Accept or reject the request.</div>

                        @if (!$isFinished && $st === 'pending')
                            <div class="d-flex gap-2 mt-2">
                                <button class="btn btn-success btn-sm" data-once
                                    data-accept="{{ $lab->id }}">Accept</button>
                                <div class="input-group input-group-sm" style="max-width:320px">
                                    <input type="text" class="form-control" placeholder="Reason (optional)"
                                        id="rejectReason">
                                    <button class="btn btn-outline-danger btn-sm" data-once
                                        data-reject="{{ $lab->id }}">Reject</button>
                                </div>
                            </div>
                        @elseif($accepted)
                            <div class="mt-2"><span class="badge-soft badge-on">Accepted</span></div>
                        @elseif($isFinished)
                            <div class="mt-2"><span class="badge-soft badge-off">{{ ucwords($st) }}</span></div>
                        @endif
                    </div>
                </div>

                {{-- STEP 2: Schedule + Price (both required before Start) --}}
                <div
                    class="step {{ $accepted && $scheduled && $priced ? 'done' : ($accepted && !$isFinished ? 'active' : '') }}">
                    <div class="dot"></div>
                    <div class="meta">
                        <div class="title">2) Schedule & Price</div>
                        <div class="hint">Set a scheduled time and a price, then save both.</div>

                        <fieldset class="mt-2" {{ $accepted && !$isFinished ? '' : 'disabled' }}>
                            <label class="form-label small mb-1">Schedule</label>
                            <div class="d-flex gap-2">
                                <input type="datetime-local" class="form-control" id="scheduledAt"
                                    value="{{ $lab->scheduled_at?->format('Y-m-d\TH:i') }}">
                                <button class="btn btn-outline-light btn-sm" data-once
                                    data-schedule="{{ $lab->id }}" {{ !$inProgress ? '' : 'disabled' }}>Save</button>
                            </div>
                        </fieldset>

                        <fieldset class="mt-2" {{ $accepted && !$isFinished ? '' : 'disabled' }}>
                            <label class="form-label small mb-1">Price</label>
                            <div class="input-group input-group-sm" style="max-width:260px;">
                                <span class="input-group-text">₦</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="priceInput"
                                    value="{{ $lab->price }}">
                                <button class="btn btn-outline-light" data-once
                                    data-price="{{ $lab->id }}" {{ !$inProgress ? '' : 'disabled' }}>Save</button>
                            </div>
                        </fieldset>

                        <div class="small subtle mt-2">
                            @if (!$scheduled)
                                <span>• Schedule not set</span>
                            @else
                                <span class="text-success">• Schedule set</span>
                            @endif
                            @if (!$priced)
                                <span class="ms-2">• Price not set</span>
                            @else
                                <span class="text-success ms-2">• Price set</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Start --}}
                <div class="step {{ $inProgress ? 'done' : ($mayStart && !$isFinished ? 'active' : '') }}">
                    <div class="dot"></div>
                    <div class="meta">
                        <div class="title">3) Start</div>
                        <div class="hint">Begin labwork (moves to in progress).</div>

                        <div class="mt-2 d-grid">
                            <button class="btn btn-outline-light btn-sm" data-once data-start="{{ $lab->id }}"
                                {{ $mayStart && !$isFinished && !$inProgress ? '' : 'disabled' }}>
                                Mark In Progress
                            </button>
                        </div>
                    </div>
                </div>

                {{-- STEP 4: Upload Results --}}
                <div
                    class="step {{ $st === 'results_uploaded' ? 'done' : ($inProgress && !$isFinished ? 'active' : '') }}">
                    <div class="dot"></div>
                    <div class="meta">
                        <div class="title">4) Upload Results</div>
                        <div class="hint">Attach PDF/images/docs and optional notes.</div>

                        <fieldset class="mt-2" {{ $inProgress && !$isFinished ? '' : 'disabled' }}>
                            <input type="file" id="resultsFile" class="form-control mb-2"
                                accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx,.xls,.xlsx">
                            <textarea id="resultsNotes" rows="2" class="form-control mb-2" placeholder="Notes (optional)">{{ old('notes', $lab->results_notes) }}</textarea>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm" data-once
                                    data-upload="{{ $lab->id }}">Upload</button>
                                @if ($hasResults)
                                    <a class="btn btn-outline-light btn-sm" target="_blank"
                                        href="{{ Storage::disk('public')->url($lab->results_path) }}">Preview</a>
                                @endif
                            </div>
                        </fieldset>

                        @if ($hasResults && !$isFinished)
                            <div class="mt-2">
                                <button class="btn btn-outline-danger btn-sm" data-once
                                    data-clear="{{ $lab->id }}">Remove Results</button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- STEP 5: Complete --}}
                <div class="step {{ $st === 'completed' ? 'done' : ($canComplete ? 'active' : '') }}">
                    <div class="dot"></div>
                    <div class="meta">
                        <div class="title">5) Complete</div>
                        <div class="hint">Finish the job (requires uploaded results).</div>

                        <div class="mt-2 d-grid">
                            <button class="btn btn-primary" data-once data-complete="{{ $lab->id }}"
                                {{ $canComplete ? '' : 'disabled' }}>
                                Mark Completed
                            </button>

                             <a class="btn btn-outline-light btn-sm" target="_blank" href="{{ Storage::disk('public')->url($lab->results_path) }}">View Result</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </div>
@endsection
@push('scripts')
    <script>
        (function() {
            const csrf = `{{ csrf_token() }}`;

            function lockBtn($b) {
                try {
                    $b.prop('disabled', true).addClass('opacity-50')
                } catch (e) {}
            }

            function unlockBtn($b) {
                try {
                    $b.prop('disabled', false).removeClass('opacity-50')
                } catch (e) {}
            }

            function doneBtn($b, label) {
                try {
                    $b.prop('disabled', true).addClass('opacity-50').attr('aria-disabled', 'true').text(label || $b
                        .text())
                } catch (e) {}
            }

            // One-click guard
            $(document).on('click', '[data-once]', function(e) {
                const $t = $(this);
                if ($t.data('onceDone')) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    return false;
                }
            });

            function markOnce($b) {
                $b.data('onceDone', true);
            }

            // STEP 1: Accept
            $(document).on('click', '[data-accept]', function() {
                const id = $(this).data('accept'),
                    $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('labtech.labworks.accept', '__ID__') }}`.replace('__ID__', id), {
                        _token: csrf
                    })
                    .done(r => {
                        flash('success', r.message || 'Accepted');
                        doneBtn($btn, 'Accepted');
                        markOnce($btn);
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed');
                        unlockBtn($btn);
                    });
            });

            // STEP 1: Reject
            $(document).on('click', '[data-reject]', function() {
                const id = $(this).data('reject'),
                    $btn = $(this);
                const reason = $('#rejectReason').val() || '';
                lockBtn($btn);
                $.post(`{{ route('labtech.labworks.reject', '__ID__') }}`.replace('__ID__', id), {
                        _token: csrf,
                        reason
                    })
                    .done(r => {
                        flash('success', r.message || 'Rejected');
                        doneBtn($btn, 'Rejected');
                        markOnce($btn);
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed');
                        unlockBtn($btn);
                    });
            });

            // STEP 2: Schedule
            $(document).on('click', '[data-schedule]', function() {
                const id = $(this).data('schedule'),
                    $btn = $(this);
                const dt = $('#scheduledAt').val();
                if (!dt) return flash('danger', 'Select a schedule');
                lockBtn($btn);
                $.post(`{{ route('labtech.labworks.schedule', '__ID__') }}`.replace('__ID__', id), {
                        _token: csrf,
                        scheduled_at: dt
                    })
                    .done(r => {
                        flash('success', r.message || 'Schedule saved');
                        doneBtn($btn, 'Saved');
                        markOnce($btn);
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to save schedule');
                        unlockBtn($btn);
                    });
            });

            // STEP 2: Price
            $(document).on('click', '[data-price]', function() {
                const id = $(this).data('price'),
                    $btn = $(this);
                const price = parseFloat($('#priceInput').val());
                if (isNaN(price) || price < 0) return flash('danger', 'Enter a valid price');
                lockBtn($btn);
                $.post(`{{ route('labtech.labworks.price', '__ID__') }}`.replace('__ID__', id), {
                        _token: csrf,
                        price
                    })
                    .done(r => {
                        flash('success', r.message || 'Price saved');
                        doneBtn($btn, 'Saved');
                        markOnce($btn);
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to save price');
                        unlockBtn($btn);
                    });
            });

            // STEP 3: Start
            $(document).on('click', '[data-start]', function() {
                const id = $(this).data('start'),
                    $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('labtech.labworks.start', '__ID__') }}`.replace('__ID__', id), {
                        _token: csrf
                    })
                    .done(r => {
                        flash('success', r.message || 'Marked in progress');
                        doneBtn($btn, 'In Progress');
                        markOnce($btn);
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to start');
                        unlockBtn($btn);
                    });
            });

            // STEP 4: Upload results
            $(document).on('click', '[data-upload]', function() {
                const id = $(this).data('upload'),
                    $btn = $(this);
                const fileInput = document.getElementById('resultsFile');
                if (!fileInput || !fileInput.files || !fileInput.files.length) return flash('danger',
                    'Choose a file to upload');
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('file', fileInput.files[0]);
                fd.append('notes', $('#resultsNotes').val() || '');
                lockBtn($btn);
                $.ajax({
                        url: `{{ route('labtech.labworks.uploadResults', '__ID__') }}`.replace('__ID__',
                            id),
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false
                    })
                    .done(r => {
                        flash('success', r.message || 'Results uploaded');
                        doneBtn($btn, 'Uploaded');
                        markOnce($btn);
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to upload');
                        unlockBtn($btn);
                    });
            });

            // STEP 4: Clear results
            $(document).on('click', '[data-clear]', function() {
                const id = $(this).data('clear'),
                    $btn = $(this);
                if (!confirm('Remove uploaded results?')) return;
                lockBtn($btn);
                $.post(`{{ route('labtech.labworks.clearResults', '__ID__') }}`.replace('__ID__', id), {
                        _token: csrf
                    })
                    .done(r => {
                        flash('success', r.message || 'Results removed');
                        doneBtn($btn, 'Removed');
                        markOnce($btn);
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to remove');
                        unlockBtn($btn);
                    });
            });

            // STEP 5: Complete
            $(document).on('click', '[data-complete]', function() {
                const id = $(this).data('complete'),
                    $btn = $(this);
                lockBtn($btn);
                $.post(`{{ route('labtech.labworks.complete', '__ID__') }}`.replace('__ID__', id), {
                        _token: csrf
                    })
                    .done(r => {
                        flash('success', r.message || 'Marked completed');
                        doneBtn($btn, 'Completed');
                        markOnce($btn);
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to complete');
                        unlockBtn($btn);
                    });
            });

        })();
    </script>
@endpush
