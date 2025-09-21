@extends('layouts.patient')
@section('title', 'Nearby Pharmacies')

@push('styles')
    <style>
        .map-box {
            background: #0b1222;
            border: 1px solid var(--border);
            border-radius: 14px;
            height: 340px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9aa3b2;
        }

        .ph-row {
            background: #0d162a;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
        }
    </style>
@endpush

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-location-dot"></i>
            <h5 class="m-0">Nearby Pharmacies</h5>
        </div>
        <div class="section-subtle">Find and contact pharmacies around you</div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="map-box" id="pharmMap">Map preview</div>
            </div>
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-2">
                    @foreach ([['MediCare Pharmacy', '0.8 km', 'Open • Closes 9:00 PM'], ['HealthPlus', '1.3 km', 'Open • 24/7'], ['City Drugs', '2.1 km', 'Closed • Opens 8:00 AM']] as $p)
                        <div class="ph-row d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">{{ $p[0] }}</div>
                                <div class="section-subtle small">{{ $p[2] }}</div>
                                <div class="mt-1 chip">{{ $p[1] }}</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-light btn-sm"><i class="fa-regular fa-message me-1"></i>
                                    Chat</button>
                                <button class="btn btn-gradient btn-sm">Directions</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Optional: try to center map placeholder using saved location (from the modal we built)
        $(function() {
            const lat = @json(auth()->user()->lat ?? null);
            const lng = @json(auth()->user()->lng ?? null);
            const $map = $('#pharmMap');
            if (lat && lng) {
                $map.text('Map centered at ' + lat.toFixed(4) + ', ' + lng.toFixed(4));
            } else {
                $map.text('Map preview — enable location for accuracy.');
            }
        });
    </script>
@endpush
