{{-- resources/views/pharmacy/inventory.blade.php --}}
@extends('layouts.pharmacy')
@section('title','Inventory')

@section('content')
  <div class="cardx">
    <h5 class="mb-2">Upload Inventory (CSV)</h5>
    <form method="POST" action="{{ route('pharmacy.inventory.upload') }}" enctype="multipart/form-data">
      @csrf
      <div class="mb-2">
        <label class="form-label">CSV File</label>
        <input type="file" name="inventory" class="form-control" accept=".csv,text/csv" required>
      </div>
      <button class="btn btn-primary">Upload</button>
    </form>
    @if(session('ok'))<div class="alert alert-success mt-2">{{ session('ok') }}</div>@endif
    @error('inventory')<div class="alert alert-danger mt-2">{{ $message }}</div>@enderror
  </div>
@endsection
