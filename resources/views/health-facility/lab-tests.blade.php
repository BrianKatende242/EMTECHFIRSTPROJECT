@extends('layouts.base')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="m-0">Lab Tests</h3>
        <a href="{{ route('health-facility.patients', ['id' => $healthFacility->id]) }}" class="btn btn-outline-secondary">Back to Patients</a>
    </div>

    @if($labTests->count())
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Student/Patient</th>
                                <th>Test Type</th>
                                <th>Status</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labTests as $test)
                                <tr>
                                    <td>{{ $test->created_at?->format('M d, Y') }}</td>
                                    <td>{{ optional($test->student)->name ?? '—' }}</td>
                                    <td>{{ $test->test_type }}</td>
                                    <td>
                                        <span class="badge bg-{{ $test->status === 'completed' ? 'success' : ($test->status === 'processing' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($test->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($test->results)
                                            <span class="text-success">Available</span>
                                        @else
                                            <span class="text-muted">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-light text-dark border">No lab tests found.</div>
    @endif
</div>
@endsection