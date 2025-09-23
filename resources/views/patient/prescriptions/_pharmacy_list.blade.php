@if ($pharmacies->isEmpty())
    <div class="text-center text-secondary py-3">No pharmacies match your search.</div>
@else
    <div class="d-flex flex-column gap-2">
        @foreach ($pharmacies as $p)
            @include('patient.prescriptions._pharmacy_row', ['p' => $p])
        @endforeach
    </div>
@endif
