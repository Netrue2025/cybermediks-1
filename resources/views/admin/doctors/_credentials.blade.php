@php
    // expects: $doctor (User), $docs (Collection<DoctorCredential>)
@endphp

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
        <table class="table table-borderless table-darkish align-middle">
            <thead>
                <tr class="section-subtle">
                    <th>Type</th>
                    <th>Status</th>
                    <th>Uploaded</th>
                    <th>Reviewed</th>
                    <th>Notes</th>
                    <th class="text-end">File</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($docs as $c)
                    <tr>
                        <td><span class="chip">{{ $c->type ?? 'Document' }}</span></td>
                        <td>
                            <span
                                class="badge-soft {{ ($c->status ?? 'pending') === 'verified' ? 'badge-on' : (($c->status ?? 'pending') === 'rejected' ? 'badge-off' : '') }}">
                                {{ ucfirst($c->status ?? 'pending') }}
                            </span>
                        </td>
                        <td>{{ $c->created_at?->format('M d, Y · g:ia') }}</td>
                        <td>{{ $c->verified_at?->format('M d, Y · g:ia') ?? '—' }}</td>
                        <td class="section-subtle small">{{ $c->review_notes ?: '—' }}</td>
                        <td class="text-end">
                            {{-- uses $c->url accessor from model --}}
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
