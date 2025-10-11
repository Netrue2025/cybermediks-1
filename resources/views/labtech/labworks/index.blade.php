@extends('layouts.labtech')
@section('title', 'Labworks')

@section('content')
    <div class="cardx mb-3">
        <form class="row g-2" method="GET">
            <div class="col-lg-5">
                <input class="form-control" name="q" value="{{ $q ?? '' }}"
                    placeholder="Search code, type, address…">
            </div>
            <div class="col-lg-3">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'accepted', 'rejected', 'scheduled', 'in_progress', 'results_uploaded', 'completed', 'cancelled'] as $s)
                        <option value="{{ $s }}" {{ ($status ?? '') === $s ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-grid">
                <button class="btn btn-gradient">Filter</button>
            </div>
        </form>
    </div>

    <div class="cardx">
        @if ($labworks->isEmpty())
            <div class="text-secondary">No labworks yet.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Patient</th>
                            <th>Test</th>
                            <th>Status</th>
                            <th>Scheduled</th>
                            <th>Price</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($labworks as $lab)
                            <tr>
                                <td>{{ $lab->code }}</td>
                                <td>{{ $lab->patient?->first_name }} {{ $lab->patient?->last_name }}</td>
                                <td>{{ $lab->lab_type }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $lab->status)) }}</td>
                                <td>{{ $lab->scheduled_at?->format('M d, Y g:ia') ?? '—' }}</td>
                                <td>{{ $lab->price ? '$' . number_format($lab->price, 2) : '—' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-outline-light btn-sm"
                                        href="{{ route('labtech.labworks.show', $lab) }}">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2">{{ $labworks->links() }}</div>
        @endif
    </div>
@endsection
