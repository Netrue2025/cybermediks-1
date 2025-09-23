@php
    /** @var \App\Models\User $pharmacy */
    /** @var \App\Models\PharmacyProfile|null $profile */
    $profile = $profile ?? null;
    $initials = strtoupper(substr($pharmacy->first_name, 0, 1)) . strtoupper(substr($pharmacy->last_name, 0, 1));
@endphp

<div class="d-flex align-items-center gap-2 mb-3">
    <div class="avatar-mini" style="width:42px;height:42px">{{ $initials }}</div>
    <div>
        <div class="fw-semibold">{{ $pharmacy->first_name }} {{ $pharmacy->last_name }}</div>
        <div class="section-subtle small">{{ $pharmacy->email }}</div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="cardx">
            <div class="fw-semibold mb-2">Business Info</div>
            <div class="section-subtle small">License No.</div>
            <div class="mb-2">{{ $profile?->license_no ?? '—' }}</div>

            <div class="section-subtle small">24/7</div>
            <div class="mb-2">
                <span class="badge-soft {{ $profile?->is_24_7 ?? false ? 'badge-on' : 'badge-off' }}">
                    <i
                        class="fa-solid {{ $profile?->is_24_7 ?? false ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                    {{ $profile?->is_24_7 ?? false ? 'Yes' : 'No' }}
                </span>
            </div>

            <div class="section-subtle small">Hours</div>
            <div class="mb-2">{{ $profile?->hours ?? '—' }}</div>

            <div class="section-subtle small">Delivery Radius (km)</div>
            <div>{{ $profile?->delivery_radius_km ?? '—' }}</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="cardx">
            <div class="fw-semibold mb-2">Quick Edit</div>

            {{-- Quick radius update --}}
            <form id="radiusForm" data-pharmacy-id="{{ $pharmacy->id }}" method="POST"
                action="{{ route('admin.pharmacies.updateRadius', $pharmacy->id) }}">
                @csrf
                <label class="form-label small section-subtle mb-1">Delivery Radius (km)</label>
                <div class="input-group mb-2">
                    <input type="number" step="0.1" min="0" name="delivery_radius_km" class="form-control"
                        value="{{ old('delivery_radius_km', $profile?->delivery_radius_km ?? '') }}">
                    <button class="btn btn-gradient" type="submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save
                    </button>
                </div>
                <div class="section-subtle small">Adjust the service radius used for patient delivery options.</div>
            </form>

            <hr class="my-3" style="border-color:var(--border);opacity:.6;">

            {{-- Toggle 24/7 shortcut --}}
            <form method="POST" action="{{ route('admin.pharmacies.toggle24', $pharmacy->id) }}">
                @csrf
                <button class="btn btn-outline-light w-100">
                    <i class="fa-solid fa-power-off me-1"></i>
                    Toggle 24/7 (currently: {{ $profile?->is_24_7 ?? false ? 'ON' : 'OFF' }})
                </button>
            </form>
        </div>
    </div>
</div>
