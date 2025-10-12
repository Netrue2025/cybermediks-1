@extends('layouts.admin')
@section('title', 'Specialties')

@push('styles')
    <style>
        .table-darkish {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, .03);
            --bs-table-striped-color: var(--text);
            --bs-table-hover-bg: rgba(255, 255, 255, .05);
            --bs-table-hover-color: var(--text);
            color: var(--text)
        }

        .spec-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .28rem .6rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #0e162b;
            font-size: .85rem
        }

        .ico-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%
        }

        .ico-box {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            background: #0f1a2e
        }

        .help {
            color: var(--muted);
            font-size: .85rem
        }
                tr th, tr td {
            color: white !important;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">
        {{-- LEFT: Search + List --}}
        <div class="col-lg-7">
            <div class="cardx mb-3">
                <form class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label small help mb-1">Search</label>
                        <input class="form-control" name="q" value="{{ $q }}" placeholder="Search specialties">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-gradient w-100"><i class="fa-solid fa-magnifying-glass me-1"></i>
                            Search</button>
                    </div>
                </form>
            </div>

            <div class="cardx">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                        <thead>
                            <tr class="help">
                                <th style="width:56px">Icon</th>
                                <th>Name</th>
                                <th>Color</th>
                                <th>Slug</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $s)
                                <tr>
                                    <td>
                                        <div class="ico-box">
                                            @if ($s->icon)
                                                <i class="{{ $s->icon }}"></i>
                                            @else
                                                <i class="fa-solid fa-stethoscope"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $s->name }}
                                        @if ($s->doctors_count ?? false)
                                            <div class="help">Linked doctors: {{ $s->doctors_count }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="spec-chip">
                                            <span class="ico-dot" style="background: {{ $s->color ?: '#64748b' }}"></span>
                                            <code>{{ $s->color ?: '#64748b' }}</code>
                                        </span>
                                    </td>
                                    <td><code>{{ $s->slug }}</code></td>
                                    <td class="text-end">
                                        {{-- Quick edit modal trigger --}}
                                        <button class="btn btn-ghost btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editSpecModal" data-id="{{ $s->id }}"
                                            data-name="{{ $s->name }}" data-icon="{{ $s->icon }}"
                                            data-color="{{ $s->color }}" data-slug="{{ $s->slug }}">
                                            <i class="fa-regular fa-pen-to-square me-1"></i> Edit
                                        </button>

                                        <form class="d-inline" method="POST"
                                            action="{{ route('admin.specialties.destroy', $s->id) }}"
                                            onsubmit="return confirm('Delete specialty?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-light btn-sm"><i
                                                    class="fa-regular fa-trash-can me-1"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center help py-4">No specialties</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">{{ $items->withQueryString()->links() }}</div>
            </div>
        </div>

        {{-- RIGHT: Create --}}
        <div class="col-lg-5">
            <div class="cardx">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-sitemap"></i>
                    <h5 class="m-0">Add Specialty</h5>
                </div>
                <div class="help mb-3">Name, Font Awesome icon (e.g. <code>fa-solid fa-heart-pulse</code>), and brand color.
                </div>

                <form method="POST" action="{{ route('admin.specialties.store') }}" id="createSpecForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small help mb-1">Name</label>
                        <input class="form-control" name="name" placeholder="e.g., Cardiology" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small help mb-1">Icon (Font Awesome classes)</label>
                        <div class="input-group">
                            <input class="form-control" name="icon" id="iconCreate"
                                placeholder="fa-solid fa-heart-pulse">
                            <span class="input-group-text" id="iconCreatePreview"
                                style="background:#0e162b;border-color:var(--border)">
                                <i class="fa-solid fa-stethoscope"></i>
                            </span>
                        </div>
                        <div class="help mt-1">Paste full class string, e.g. <code>fa-solid fa-user-doctor</code>.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small help mb-1">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" value="#8758e8"
                                id="colorPickerCreate" style="max-width:56px">
                            <input class="form-control" name="color" id="colorCreate" value="#8758e8"
                                placeholder="#8758e8">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-gradient">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal fade" id="editSpecModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" id="editSpecForm"
                style="background:var(--card);color:var(--text);border:1px solid var(--border)">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h6 class="modal-title">Edit Specialty</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editId">
                    <div class="mb-3">
                        <label class="form-label small help mb-1">Name</label>
                        <input class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small help mb-1">Icon</label>
                        <div class="input-group">
                            <input class="form-control" name="icon" id="editIcon"
                                placeholder="fa-solid fa-heart-pulse">
                            <span class="input-group-text" id="iconEditPreview"
                                style="background:#0e162b;border-color:var(--border)">
                                <i class="fa-solid fa-stethoscope"></i>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label small help mb-1">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" value="#8758e8"
                                id="colorPickerEdit" style="max-width:56px">
                            <input class="form-control" name="color" id="editColor" value="#8758e8"
                                placeholder="#8758e8">
                        </div>
                    </div>
                    <div class="help mt-2">Slug is auto-managed from the name.</div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-gradient px-3"><i class="fa-solid fa-floppy-disk me-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Create form previews
        $('#iconCreate').on('input', function() {
            const cls = $(this).val().trim() || 'fa-solid fa-stethoscope';
            $('#iconCreatePreview').html(`<i class="${cls}"></i>`);
        });
        $('#colorPickerCreate').on('input', function() {
            $('#colorCreate').val($(this).val());
        });
        $('#colorCreate').on('input', function() {
            $('#colorPickerCreate').val($(this).val());
        });

        // Edit modal wiring
        const editModal = document.getElementById('editSpecModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name') || '';
            const icon = btn.getAttribute('data-icon') || 'fa-solid fa-stethoscope';
            const color = btn.getAttribute('data-color') || '#64748b';
            const slug = btn.getAttribute('data-slug') || '';

            // Fill fields
            $('#editId').val(id);
            $('#editName').val(name);
            $('#editIcon').val(icon);
            $('#editColor').val(color);
            $('#colorPickerEdit').val(color);
            $('#iconEditPreview').html(`<i class="${icon}"></i>`);

            // Set action URL
            const action = `{{ url('admin/specialties') }}/${id}`;
            $('#editSpecForm').attr('action', action);
        });

        $('#editIcon').on('input', function() {
            const cls = $(this).val().trim() || 'fa-solid fa-stethoscope';
            $('#iconEditPreview').html(`<i class="${cls}"></i>`);
        });
        $('#colorPickerEdit').on('input', function() {
            $('#editColor').val($(this).val());
        });
        $('#editColor').on('input', function() {
            $('#colorPickerEdit').val($(this).val());
        });
    </script>
@endpush
