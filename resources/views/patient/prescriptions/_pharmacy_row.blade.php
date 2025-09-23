@php
    $initials =
        strtoupper(substr($p->first_name ?? ($p->name ?? 'P'), 0, 1)) . strtoupper(substr($p->last_name ?? '', 0, 1));
    $hrs = $p->pharmacyProfile?->hours ?: '—';
    $is247 = (bool) $p->pharmacyProfile?->is_24_7;
    $radius = $p->pharmacyProfile?->delivery_radius_km;
@endphp

<div class="d-flex align-items-center justify-content-between"
    style="background:#0f1a2e;border:1px solid var(--border);border-radius:10px;padding:10px 12px;">
    <div class="d-flex align-items-center gap-2">
        <div class="avatar-sm"
            style="width:38px;height:38px;border-radius:50%;background:#14203a;display:flex;align-items:center;justify-content:center;font-weight:700;color:#cfe0ff;">
            {{ $initials }}
        </div>
        <div>
            <div class="fw-semibold">
                {{ trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')) ?: $p->name ?? 'Pharmacy #' . $p->id }}
            </div>
            <div class="text-secondary small">
                Hours: {{ $is247 ? '24/7' : $hrs }}
                @if (!is_null($radius))
                    • Delivery radius: {{ $radius }} km
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if ($is247)
            <span class="badge-soft" style="background:rgba(34,197,94,.12);border-color:#1f6f43">24/7</span>
        @endif
        <button class="btn btn-success btn-sm" data-pharm-select="{{ $p->id }}">
            Select pharmacy
        </button>
    </div>
</div>
