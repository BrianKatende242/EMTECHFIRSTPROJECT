@extends('layouts.base')

@php
    use Illuminate\Support\Str;
@endphp

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
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Appointment Date</th>
                            <th>Duration</th>
                            <th>Appointment Status</th>
                            <th>Payment Status</th>
                            <th>Amount</th>
                            <th>Payment Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->id }}</td>
                            <td>{{ $appointment->patient->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->doctor->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->appointment_time ? $appointment->appointment_time->format('M d, Y H:i') : 'N/A' }}</td>
                            <td>{{ $appointment->duration ? $appointment->duration->minutes . ' mins ' . ucfirst($appointment->duration->type) : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status ?? 'pending')) }}
                                </span>
                            </td>
                            <td>
                                @if($appointment->payment_status)
                                    <span class="badge bg-{{ $appointment->payment_status === 'completed' ? 'success' : ($appointment->payment_status === 'failed' ? 'danger' : 'info') }}">
                                        {{ ucfirst($appointment->payment_status) }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Not Started</span>
                                @endif
                            </td>
                            <td>UGX {{ number_format($appointment->duration ? $appointment->duration->getPrice() : 0, 0) }}</td>
                            <td>
                                @if($appointment->payment_reference)
                                    <small class="text-muted">{{ Str::limit($appointment->payment_reference, 20) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
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