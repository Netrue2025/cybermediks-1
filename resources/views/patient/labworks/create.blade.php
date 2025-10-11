@extends('layouts.patient')
@section('title', 'Request Labwork')

@section('content')
    <div class="cardx">
        <h5 class="mb-2">Request Labwork</h5>

        <form method="POST" action="{{ route('patient.labworks.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">Lab Test</label>
                    <select name="lab_type" class="form-select" required>
                        <option value="" hidden>Select test…</option>
                        @foreach ($labTypes as $t)
                            <option value="{{ $t }}" {{ old('lab_type') === $t ? 'selected' : '' }}>{{ $t }}
                            </option>
                        @endforeach
                    </select>
                    @error('lab_type')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label class="form-label">Provider</label>
                    <select name="labtech_id" class="form-select" required>
                        <option value="" hidden>Select provider…</option>
                        @foreach ($providers as $p)
                            @php $label = trim(($p->first_name.' '.$p->last_name)) ?: ($p->name ?: $p->email); @endphp
                            <option value="{{ $p->id }}" {{ old('labtech_id') == $p->id ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                    @error('labtech_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label class="form-label">Collection Method</label>
                    <select name="collection_method" id="collMethod" class="form-select" required>
                        <option value="in_lab" {{ old('collection_method') === 'in_lab' ? 'selected' : '' }}>Come to lab</option>
                        <option value="home" {{ old('collection_method') === 'home' ? 'selected' : '' }}>Home collection
                        </option>
                    </select>
                </div>

                <div class="col-lg-6" id="addressWrap" style="{{ old('collection_method') === 'home' ? '' : 'display:none' }}">
                    <label class="form-label">Home Address</label>
                    <input name="address" class="form-control" value="{{ old('address') }}" placeholder="Street, city">
                    @error('address')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-6">
                    <label class="form-label">Preferred Date/Time (optional)</label>
                    <input type="datetime-local" name="preferred_at" class="form-control"
                        value="{{ old('preferred_at') }}">
                </div>

                <div class="col-lg-12">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                </div>

                <div class="col-12 d-grid">
                    <button class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('collMethod').addEventListener('change', function() {
            const wrap = document.getElementById('addressWrap');
            if (this.value === 'home') wrap.style.display = '';
            else wrap.style.display = 'none';
        });
    </script>
@endpush
