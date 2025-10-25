@extends('layouts.base')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="m-0">Transactions</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-light text-dark">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Transactions</h5>
            @if(isset($appointments) && $appointments->count())
            <div class="table-responsive">
                <table class="table table-striped text-dark">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Doctor</th>
                            <th>Appointment Date</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->id }}</td>
                            <td>{{ $appointment->patient->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->doctor->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->appointment_time ? $appointment->appointment_time->format('M d, Y H:i') : 'N/A' }}</td>
                            <td>{{ $appointment->duration->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($appointment->status ?? 'pending') }}
                                </span>
                            </td>
                            <td>UGX {{ number_format($appointment->duration->price ?? 0, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $appointments->links() }}
            </div>
            @else
                <div class="alert alert-info">No transactions found.</div>
            @endif
        </div>
    </div>
</div>
@endsection