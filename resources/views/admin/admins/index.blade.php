@extends('layouts.admin')
@section('title', 'Admins')

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

        .avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #17223a;
            border: 1px solid var(--border);
            color: #cfe0ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700
        }

        .help {
            color: var(--muted);
            font-size: .85rem
        }

        tr th,
        tr td {
            color: white !important;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3">
        {{-- LEFT: Admins list --}}
        <div class="col-lg-7">
            <div class="cardx">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-shield-halved"></i>
                        <h5 class="m-0">Existing Admins</h5>
                    </div>
                    <span class="help">Manage platform administrators</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless table-hover table-striped table-darkish align-middle">
                        <thead>
                            <tr class="help">
                                <th style="width:54px"> </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admins as $a)
                                @php
                                    $ini = strtoupper(substr($a->first_name, 0, 1) . substr($a->last_name, 0, 1));
                                @endphp
                                <tr>
                                    <td><span class="avatar-sm">{{ $ini }}</span></td>
                                    <td class="fw-semibold">
                                        {{ $a->first_name }} {{ $a->last_name }}
                                        <div class="help">ID: {{ $a->id }}</div>
                                    </td>
                                    <td>{{ $a->email }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.admins.destroy', $a->id) }}"
                                            onsubmit="return confirm('Remove admin?')" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-light btn-sm"
                                                {{ auth()->id() === $a->id ? 'disabled' : '' }}>
                                                <i class="fa-regular fa-trash-can me-1"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center help py-4">No admins found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $admins->links() }}</div>
            </div>
        </div>

        {{-- RIGHT: Create admin --}}
        <div class="col-lg-5">
            <div class="cardx">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <h5 class="m-0">Create Admin</h5>
                </div>
                <div class="help mb-3">Provision a new administrator account.</div>

                <form method="POST" action="{{ route('admin.admins.store') }}" autocomplete="off" id="createAdminForm">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small help mb-1">First name</label>
                            <input class="form-control" name="first_name" placeholder="First name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small help mb-1">Last name</label>
                            <input class="form-control" name="last_name" placeholder="Last name" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small help mb-1">Email</label>
                            <input class="form-control" type="email" name="email" placeholder="email@domain.com"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small help mb-1">Password</label>
                            <div class="input-group">
                                <input class="form-control" type="password" name="password" id="pwd"
                                    placeholder="••••••••" required>
                                <button class="btn btn-outline-light" type="button" id="togglePwd"><i
                                        class="fa-regular fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small help mb-1">Confirm Password</label>
                            <input class="form-control" type="password" name="password_confirmation" id="pwd2"
                                placeholder="Repeat password" required>
                        </div>
                        <div class="col-12 d-grid mt-1">
                            <button class="btn btn-gradient">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Create
                            </button>
                        </div>
                    </div>
                </form>

                <div class="help mt-3">
                    Tip: You can force a reset later by sending a password reset link to the admin’s email.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Password show/hide
        $('#togglePwd').on('click', function() {
            const $i = $(this).find('i');
            const $pwd = $('#pwd');
            const type = $pwd.attr('type') === 'password' ? 'text' : 'password';
            $pwd.attr('type', type);
            $i.toggleClass('fa-eye fa-eye-slash');
        });

        // Simple client check for password confirmation (optional UX)
        $('#createAdminForm').on('submit', function(e) {
            const p1 = $('#pwd').val();
            const p2 = $('#pwd2').val();
            if (p1 !== p2) {
                e.preventDefault();
                alert('Passwords do not match.');
            }
        });
    </script>
@endpush
