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
                                • Price: ${{ number_format($lab->price, 2) }}
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

        <div class="col-lg-5">
            <div class="cardx">
                @php
                    $st = $lab->status;
                    $hasResults = !empty($lab->results_path);
                    $canEditMeta = !in_array($st, ['rejected', 'completed', 'cancelled'], true);
                    $canAccept = $st === 'pending';
                    $canReject = $st === 'pending';
                    $canSchedule = $canEditMeta;
                    $canPrice = $canEditMeta;
                    $canStart = in_array($st, ['accepted', 'scheduled'], true);
                    $canUpload = in_array($st, ['accepted', 'scheduled', 'in_progress', 'results_uploaded'], true);
                    $canClear =
                        $hasResults &&
                        in_array($st, ['accepted', 'scheduled', 'in_progress', 'results_uploaded'], true);
                    $canComplete =
                        $hasResults &&
                        in_array($st, ['results_uploaded', 'in_progress', 'scheduled', 'accepted'], true);
                    $statusHint = match ($st) {
                        'pending' => 'Review and accept or reject this request.',
                        'accepted' => 'Optionally schedule/timebox and set a price. Start when ready.',
                        'scheduled'
                            => 'You can adjust schedule/price. Start when the patient arrives or collection begins.',
                        'in_progress' => 'Upload results when ready. You can complete after results are uploaded.',
                        'results_uploaded' => 'Results are uploaded. You may mark this labwork completed.',
                        'completed' => 'Completed — no further actions allowed.',
                        'rejected' => 'Rejected — no further actions allowed.',
                        'cancelled' => 'Cancelled — no further actions allowed.',
                        default => '—',
                    };
                @endphp

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold">Actions</div>
                    <span
                        class="badge-soft {{ in_array($st, ['completed']) ? 'badge-ready' : (in_array($st, ['rejected', 'cancelled']) ? 'badge-cancelled' : 'badge-pending') }}">
                        {{ ucwords(str_replace('_', ' ', $st)) }}
                    </span>
                </div>
                <div class="small subtle mb-3">{{ $statusHint }}</div>

                {{-- Accept / Reject --}}
                @if ($canAccept || $canReject)
                    <div class="d-flex gap-2 mb-3">
                        @if ($canAccept)
                            <button class="btn btn-success btn-sm" data-once
                                data-accept="{{ $lab->id }}">Accept</button>
                        @endif
                        @if ($canReject)
                            <div class="input-group input-group-sm" style="max-width: 360px;">
                                <input type="text" class="form-control" placeholder="Reason (optional)"
                                    id="rejectReason">
                                <button class="btn btn-outline-danger btn-sm" data-once
                                    data-reject="{{ $lab->id }}">Reject</button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Schedule --}}
                <fieldset class="mt-2" {{ $canSchedule ? '' : 'disabled' }}>
                    <label class="form-label small">Schedule</label>
                    <div class="d-flex gap-2">
                        <input type="datetime-local" class="form-control" id="scheduledAt"
                            value="{{ $lab->scheduled_at?->format('Y-m-d\TH:i') }}">
                        <button class="btn btn-outline-light btn-sm" data-once
                            data-schedule="{{ $lab->id }}">Save</button>
                    </div>
                </fieldset>

                {{-- Price --}}
                <fieldset class="mt-3" {{ $canPrice ? '' : 'disabled' }}>
                    <label class="form-label small">Price</label>
                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" class="form-control" id="priceInput"
                            value="{{ $lab->price }}">
                        <button class="btn btn-outline-light" data-once data-price="{{ $lab->id }}">Save</button>
                    </div>
                </fieldset>

                {{-- Start / In Progress --}}
                <div class="mt-3 d-grid">
                    <button class="btn btn-outline-light btn-sm" data-once data-start="{{ $lab->id }}"
                        {{ $canStart ? '' : 'disabled' }}>
                        Mark In Progress
                    </button>
                </div>

                <hr>

                {{-- Results upload BEFORE completion --}}
                <fieldset class="mt-2" {{ $canUpload ? '' : 'disabled' }}>
                    <label class="form-label small">Upload Results (PDF/Images/Docs)</label>
                    <input type="file" id="resultsFile" class="form-control mb-2"
                        accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx,.xls,.xlsx">
                    <textarea id="resultsNotes" rows="2" class="form-control mb-2" placeholder="Notes (optional)">{{ old('notes', $lab->results_notes) }}</textarea>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success btn-sm" data-once data-upload="{{ $lab->id }}">Upload</button>
                        @if ($hasResults)
                            <a class="btn btn-outline-light btn-sm" target="_blank"
                                href="{{ Storage::disk('public')->url($lab->results_path) }}">Preview</a>
                        @endif
                    </div>
                </fieldset>

                @if ($canClear)
                    <div class="mt-2">
                        <button class="btn btn-outline-danger btn-sm" data-once data-clear="{{ $lab->id }}">Remove
                            Results</button>
                    </div>
                @endif

                {{-- Complete --}}
                <div class="mt-3 d-grid">
                    <button class="btn btn-primary" data-once data-complete="{{ $lab->id }}"
                        {{ $canComplete ? '' : 'disabled' }}>
                        Mark Completed
                    </button>
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
                    $b.prop('disabled', true).addClass('opacity-50');
                } catch (e) {}
            }

            function unlockBtn($b) {
                try {
                    $b.prop('disabled', false).removeClass('opacity-50');
                } catch (e) {}
            }

            function doneBtn($b, label) {
                try {
                    $b.prop('disabled', true)
                        .addClass('opacity-50')
                        .attr('aria-disabled', 'true')
                        .text(label || $b.text());
                    $b.closest('[data-once]').attr('data-once-done', '1');
                } catch (e) {}
            }
            // One-click guard: after one successful click, we won’t fire again
            $(document).on('click', '[data-once]', function(e) {
                const $t = $(this);
                if ($t.attr('data-once-done') === '1') {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    return false;
                }
            });

            // Accept
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
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed');
                        unlockBtn($btn);
                    });
            });

            // Reject
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
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed');
                        unlockBtn($btn);
                    });
            });

            // Schedule
            $(document).on('click', '[data-schedule]', function() {
                const id = $(this).data('schedule'),
                    $btn = $(this);
                const dt = $('#scheduledAt').val();
                lockBtn($btn);
                $.post(`{{ route('labtech.labworks.schedule', '__ID__') }}`.replace('__ID__', id), {
                        _token: csrf,
                        scheduled_at: dt
                    })
                    .done(r => {
                        flash('success', r.message || 'Schedule saved');
                        doneBtn($btn, 'Saved');
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to save');
                        unlockBtn($btn);
                    });
            });

            // Price
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
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to save');
                        unlockBtn($btn);
                    });
            });

            // Start / In progress
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
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to start');
                        unlockBtn($btn);
                    });
            });

            // Upload Results (file + notes)
            $(document).on('click', '[data-upload]', function() {
                const id = $(this).data('upload'),
                    $btn = $(this);
                const fileInput = document.getElementById('resultsFile');
                if (!fileInput || !fileInput.files || !fileInput.files.length) {
                    return flash('danger', 'Please choose a file to upload');
                }
                const notes = $('#resultsNotes').val() || '';
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('file', fileInput.files[0]);
                fd.append('notes', notes);

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
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to upload');
                        unlockBtn($btn);
                    });
            });

            // Clear Results
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
                        location.reload();
                    })
                    .fail(x => {
                        flash('danger', x.responseJSON?.message || 'Failed to remove');
                        unlockBtn($btn);
                    });
            });

            // Complete
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
