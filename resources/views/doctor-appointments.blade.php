@extends('layouts.base')

@section('content')
<div class="container py-4 card shadow-sm mb-4">
    <h2 class="mb-4">Appointments</h2>
    @if($appointments->count())
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle text-dark">
                <thead class="table-primary">
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>School</th>
                        <th>Health Facility</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->appointment_time->format('M d, Y h:i A') }}</td>
                        <td>{{ $appointment->student->name ?? '-' }}</td>
                        <td>{{ $appointment->school->name ?? '-' }}</td>
                        <td>{{ $appointment->healthFacility->name ?? 'N/A' }}</td>
                        <td>{{ $appointment->duration }} mins</td>
                        <td>
                            <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'cancelled' ? 'danger' : 'warning') }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="" class="btn btn-sm btn-info" title="View">
                                    <i class="fa fa-video-camera"></i>
                                </a>
                                @if($appointment->status !== 'cancelled')
                                    <form method="POST" action="" style="display:inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancel" onclick="return confirm('Cancel this appointment?')">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($appointment->status === 'pending')
                                    <form method="POST" action="{{ route('appointments.complete', $appointment->id) }}" style="display:inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success" title="Mark Complete" onclick="return confirm('Mark as completed?')">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info mt-4">No appointments found.</div>
    @endif
</div>
@endsection
