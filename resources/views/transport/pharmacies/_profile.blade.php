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
    <div class="col-lg-12">
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

</div>
