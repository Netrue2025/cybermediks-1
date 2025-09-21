<div class="d-flex flex-column gap-3">
@forelse ($prescriptions as $rx)
    @php
        $doctorName = $rx->doctor?->full_name ?? 'Doctor';
        $itemsPreview = $rx->items->take(1)->pluck('drug')->first();
        $viewPayload = [
            'id'     => $rx->id,
            'code'   => $rx->code,
            'status' => $rx->status,
            'doctor' => $doctorName,
            'notes'  => $rx->notes,
            'items'  => $rx->items->map(fn($i)=>[
                'drug'=>$i->drug,
                'dose'=>$i->dose,
                'frequency'=>$i->frequency,
                'days'=>$i->days,
                'directions'=>$i->directions,
            ])->values(),
        ];
    @endphp

    <div class="rx-row">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="fw-semibold">
                    {{ $itemsPreview ?? 'Prescription' }}
                    @if($rx->items->count() > 1)
                        <span class="section-subtle">+{{ $rx->items->count()-1 }} more</span>
                    @endif
                </div>
                <div class="section-subtle small">Prescribed by {{ $doctorName }}</div>
                <div class="mt-2 d-flex gap-2">
                    <span class="rx-badge">Rx {{ $rx->code }}</span>
                    <span class="rx-status">{{ str_replace('_',' ',$rx->status) }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-light btn-sm"
                        data-rx-view='@json($viewPayload)'>View</button>
                <button class="btn btn-gradient btn-sm"
                        data-rx-refill="{{ $rx->id }}">Refill</button>
            </div>
        </div>
    </div>
@empty
    <div class="section-subtle">No prescriptions found.</div>
@endforelse
</div>

@if ($prescriptions->hasPages())
    <div class="mt-3">{!! $prescriptions->links() !!}</div>
@endif
