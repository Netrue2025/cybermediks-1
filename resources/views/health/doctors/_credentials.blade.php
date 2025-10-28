{{-- <style>
    :root {
        --card: #0f172a;
        --border: #27344e;
        --text: #e5e7eb;
        --muted: #9aa3b2;
    }

    /* Avatar circle with initials */
    .avatar-mini {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #101a2e;
        color: #c9d7f2;
        border: 1px solid var(--border);
        font-weight: 700;
        font-size: .95rem;
    }

    /* Chip for 'Type' */
    .chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .22rem .55rem;
        border-radius: 999px;
        background: #101a2e;
        border: 1px solid var(--border);
        color: var(--text);
        font-size: .85rem;
        white-space: nowrap;
    }

    /* Badge variants for Status */
    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .22rem .55rem;
        border-radius: 999px;
        border: 1px solid var(--border);
        font-size: .85rem;
        color: var(--text);
        background: #0e162b;
        text-transform: capitalize;
    }

    .badge-soft.badge-on {
        background: rgba(34, 197, 94, .1);
        border-color: #1a6b3a;
        color: #86efac;
    }

    /* verified */
    .badge-soft.badge-off {
        background: rgba(239, 68, 68, .1);
        border-color: #7a1f2a;
        color: #fca5a5;
    }

    /* rejected */

    /* Table look */
    .table-darkish {
        --bs-table-bg: #0f172a;
        --bs-table-striped-bg: #111e35;
        --bs-table-hover-bg: #122240;
        color: var(--text);
        border-color: var(--border);
    }

    .table-darkish thead tr {
        border-bottom: 1px solid var(--border)
    }

    .table-darkish th {
        font-weight: 600;
        color: var(--muted)
    }

    .table-darkish td,
    .table-darkish th {
        vertical-align: middle
    }

    /* Truncate long notes nicely */
    .td-notes {
        max-width: 380px;
        color: var(--muted);
        font-size: .92rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Keep date/time compact on one line */
    .td-when {
        white-space: nowrap
    }
</style> --}}

@if ($docs->isEmpty())
    <div class="text-center section-subtle py-3">No credentials uploaded.</div>
@else
    <div class="d-flex align-items-center gap-2 mb-2">
        <div class="avatar-mini" style="width:42px;height:42px">
            {{ strtoupper(substr($doctor->first_name, 0, 1)) . strtoupper(substr($doctor->last_name, 0, 1)) }}
        </div>
        <div>
            <div class="fw-semibold">{{ $doctor->first_name }} {{ $doctor->last_name }}</div>
            <div class="section-subtle small">#D{{ str_pad($doctor->id, 4, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless align-middle">
            <thead>
                <tr class="section-subtle">
                    <th>Type</th>
                    <th>Status</th>
                    <th>Uploaded</th>
                    <th class="d-none d-md-table-cell">Reviewed</th>
                    <th class="d-none d-lg-table-cell">Notes</th>
                    <th class="text-end">File</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($docs as $c)
                    <tr>
                        <td><span class="chip">{{ $c->type ?? 'Document' }}</span></td>
                        <td>
                            <span
                                class="badge-soft
                {{ ($c->status ?? 'pending') === 'verified' ? 'badge-on' : (($c->status ?? 'pending') === 'rejected' ? 'badge-off' : '') }}">
                                {{ ucfirst($c->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="td-when">{{ $c->created_at?->format('M d, Y · g:ia') }}</td>
                        <td class="td-when d-none d-md-table-cell">
                            {{ $c->verified_at?->format('M d, Y · g:ia') ?? '—' }}</td>
                        <td class="td-notes d-none d-lg-table-cell">{{ $c->review_notes ?: '—' }}</td>
                        <td class="text-end">
                            <a href="{{ $c->url }}" target="_blank" class="btn btn-outline-light btn-sm">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
