@extends('layouts.patient')
@section('title', 'My Labworks')

@section('content')
    <div class="cardx mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="m-0">My Labworks</h5>
            <a class="btn btn-success btn-sm" href="{{ route('patient.labworks.create') }}">Request Labwork</a>
        </div>
    </div>

    <div class="cardx">
        @if ($labworks->isEmpty())
            <div class="text-secondary">No labwork requests yet.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Test</th>
                            <th>Provider</th>
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
                                <td>{{ $lab->lab_type }}</td>
                                <td>
                                    @php $p = $lab->labtech; @endphp
                                    {{ $p ? (trim($p->first_name . ' ' . $p->last_name) ?: ($p->name ?: $p->email)) : '—' }}
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $lab->status)) }}</td>
                                <td>{{ $lab->scheduled_at?->format('M d, Y g:ia') ?? '—' }}</td>
                                <td>@money($lab->price)</td>
                                <td class="text-end">
                                    <a href="{{ route('patient.labworks.show', $lab) }}"
                                        class="btn btn-outline-light btn-sm">Open</a>
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
