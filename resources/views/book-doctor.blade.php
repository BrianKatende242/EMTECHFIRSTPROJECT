@extends('layouts.base')


@section('content')
        <div class="d-flex justify-content-between mb-4">
            <h2>Doctor Appointments</h2>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newAppointmentModal"><i class="fa fa-plus"></i> New Appointment</button>
            <div class="modal fade" id="newAppointmentModal" tabindex="-1" aria-labelledby="newAppointmentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="newAppointmentModalLabel">New Doctor Appointment</h5>
                            <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('appointments.store') }}" method="POST">
    @csrf
    <input type="hidden" name="school_id" value="{{ $school->id }}">

    <div class="mb-3">
        <label for="patient_id" class="form-label">Patient</label>
        <select class="form-control form-select" name="patient_id" required>
            <option value="">Select patient</option>
            @foreach($patients as $patient)
            <option value="{{ $patient->id }}">{{ $patient->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="doctor_id" class="form-label">Doctor</label>
        <select class="form-control form-select" name="doctor_id" required>
            <option value="">Select doctor</option>
            @foreach($doctors as $doctor)
            <option value="{{ $doctor->id }}">{{ $doctor->name }} - {{ $doctor->specialization }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="appointment_time" class="form-label">Appointment Time</label>
        <input type="datetime-local" class="form-control" name="appointment_time" required>
    </div>

    <div class="mb-3">
        <label for="duration" class="form-label">Duration (mins)</label>
        <select class="form-control form-select" name="duration" required>
            <option value="">Select Duration</option>
            <option value="15">15 minutes</option>
            <option value="20">20 minutes</option>
            <option value="30">30 minutes</option>
            <option value="45">45 minutes</option>
            <option value="60">60 minutes</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="reason" class="form-label">Reason</label>
        <textarea class="form-control" name="reason" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Book Appointment</button>
                        </form>

                        </div>
                    </div>
                </div>
        </div>
        </div>

        @if($appointments->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Doctor</th>
                <th>Duration</th>
                <th>Amount</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
            <tr>
                <td>{{ $appointment->appointment_time->format('M d, Y h:i A') }}</td>
                <td>{{ $appointment->student->name }}</td>
                <td>Dr. {{ $appointment->doctor->name }}</td>
                <td>{{ $appointment->duration }} mins</td>
                <td>{{ number_format($appointment->amount) }} UGX</td>
                <td>{{ $appointment->reason }}</td>
                <td>
                    <span class="badge bg-{{ 
                        $appointment->status == 'confirmed' ? 'success' : 
                        ($appointment->status == 'pending_payment' ? 'warning' : 'danger') 
                    }}">
                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
        </div>
        @else
        <div class="alert alert-info text-dark">
            No appointments found.
        </div>
        @endif
@endsection