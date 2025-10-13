@extends('layouts.pharmacy')
@section('title', 'Settings')

@push('styles')
    <style>
        .cardx {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px
        }

        .ps-row {
            background: #0e182b;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px
        }

        .badge-soft {
            display: inline-flex;
            gap: .35rem;
            align-items: center;
            border: 1px solid var(--border);
            background: #0e162b;
            border-radius: 999px;
            padding: .2rem .55rem;
            font-size: .85rem;
            color: #cfe0ff
        }
    </style>
@endpush

@section('content')
    <form class="cardx" method="post" action="{{ route('pharmacy.settings.update') }}">
        @csrf
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-gear"></i>
            <h5 class="m-0">Pharmacy Settings</h5>
        </div>
        <div class="subtle mb-3">Update your profile & operating preferences</div>

        <div class="row g-3">

            <div class="col-lg-6">
                <div class="ps-row d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-semibold">Open 24/7</div>
                        <div class="subtle small">If enabled, hours below are ignored.</div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input type="hidden" name="is_24_7" value="0">
                        <input type="checkbox" class="form-check-input" name="is_24_7" value="1"
                            {{ old('is_24_7', (int) $profile->is_24_7) ? 'checked' : '' }}>

                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ps-row">
                    <label class="form-label">Delivery radius (km)</label>
                    <input type="number" min="0" step="0.1" name="delivery_radius_km" class="form-control"
                        value="{{ old('delivery_radius_km', $profile->delivery_radius_km) }}">
                </div>
            </div>

            <div class="col-lg-12">
                <div class="ps-row">
                    <label class="form-label">Hours (JSON or text)</label>
                    <textarea name="hours" rows="4" class="form-control"
                        placeholder='e.g. {"Mon":"09:00-17:00", "Tue":"09:00-17:00"}'>{{ old('hours', $profile->hours) }}</textarea>
                    <div class="subtle small mt-1">Tip: You can store a JSON map per day, or plain text.</div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-gradient">Save Settings</button>
        </div>
    </form>

    @php $isPending = ($profile->status === 'pending'); @endphp

    <h4>Status: {{ ucfirst($profile->status ?? '—') }}</h4>
    @if ($profile->status === 'rejected')
        <h5>Reason: {{ $profile->rejection_reason }}</h5>
    @endif

    @if ($isPending)
        <div class="alert alert-warning mt-2">
            Your license is currently under review. You can upload a new file after it’s rejected.
        </div>
    @endif

    <form class="cardx" method="POST" action="{{ route('pharmacy.settings.license.update') }}"
        enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="ps-row">
                    <label class="form-label" for="operating_license">Operating License</label>
                    <input id="operating_license" name="operating_license" type="file"
                        class="form-control @error('operating_license') is-invalid @enderror"
                        accept=".pdf,.jpg,.jpeg,.png,.webp" {{ $isPending ? 'disabled' : 'required' }}>
                    @error('operating_license')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ps-row">
                    <label class="form-label" for="license_no">License No.</label>
                    <input id="license_no" name="license_no" class="form-control @error('license_no') is-invalid @enderror"
                        value="{{ old('license_no', $profile->license_no) }}" maxlength="120"
                        {{ $isPending ? 'disabled' : 'required' }}>
                    @error('license_no')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary" {{ $isPending ? 'disabled' : '' }}>
                Save License
            </button>
        </div>

        @if (!empty($profile->status))
            <div class="mt-2">
                <span class="badge-soft">
                    Status: {{ ucfirst($profile->status) }}
                    @if ($profile->status === 'rejected' && $profile->rejection_reason)
                        — Reason: {{ $profile->rejection_reason }}
                    @endif
                </span>
            </div>
        @endif
    </form>


@endsection
