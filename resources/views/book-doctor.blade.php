@extends('layouts.base')


@section('content')
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">Doctor Appointments</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAppointmentModal"><i class="fa fa-plus"></i> New Appointment</button>
                </div>
                <!-- Modal and form remain unchanged -->
                <div>
                    @if($appointments->count() > 0)
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-hover align-middle text-dark">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Student</th>
                                        <th>Doctor</th>
                                        <th>Duration</th>
                                        <th>Amount</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
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
                                            }} text-white">
                                                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($appointment->status === 'awaiting_payment')
                                                <a href="{{ route('payment.appointment.pay', $appointment) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-credit-card me-1"></i> Pay
                                                </a>
                                            @else
                                                —
                                            @endif
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
                </div>
            </div>
        </div>
            <div class="modal fade" id="newAppointmentModal" tabindex="-1" aria-labelledby="newAppointmentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="newAppointmentModalLabel">New Doctor Appointment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('appointments.store') }}" method="POST">
    @csrf
    <input type="hidden" name="school_id" value="{{ $school->id }}">

    <div class="mb-3">
        <label for="student_id" class="form-label">Student</label>
        <select id="student_id" class="form-control form-select" name="student_id" required>
            <option value="">Select student</option>
            @foreach($patients as $patient)
            <option value="{{ $patient->id }}">{{ $patient->name }}</option>
            @endforeach
        </select>
        @error('student_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="doctor_id" class="form-label">Doctor</label>
        <select id="doctor_id" class="form-control form-select" name="doctor_id" required>
            <option value="">Select doctor</option>
            @foreach($doctors as $doctor)
            <option value="{{ $doctor->id }}">{{ $doctor->name }} - {{ $doctor->specialization }}</option>
            @endforeach
        </select>
        @error('doctor_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="appointment_time" class="form-label">Appointment Time</label>
        <input id="appointment_time" type="datetime-local" class="form-control" name="appointment_time" required>
        @error('appointment_time')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="duration" class="form-label">Duration (mins)</label>
        <select id="duration" class="form-control form-select" name="duration" required>
            <option value="">Select Duration</option>
            <option value="15">15 minutes</option>
            <option value="20">20 minutes</option>
            <option value="30">30 minutes</option>
            <option value="45">45 minutes</option>
            <option value="60">60 minutes</option>
        </select>
        @error('duration')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="reason" class="form-label">Reason</label>
        <textarea id="reason" class="form-control" name="reason" required></textarea>
        @error('reason')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Book Appointment</button>
                        </form>

                        </div>
                    </div>
                </div>
        </div>
        </div>
@endsection