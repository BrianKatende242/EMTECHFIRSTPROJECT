@extends('layouts.base')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="m-0">Book Doctor</h3>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookDoctorModal">
                <i class="fa fa-calendar-plus me-2"></i> New Appointment
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-light text-dark">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Recent Appointments</h5>
            @if(isset($appointments) && $appointments->count())
            <div class="table-responsive">
                <table class="table table-striped text-dark">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end">Meeting</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appt)
                        <tr>
                            <td>{{ $appt->id }}</td>
                            <td>{{ optional($appt->patient)->name ?? '—' }}</td>
                            <td>{{ optional($appt->doctor)->name ? 'Dr. ' . $appt->doctor->name : '—' }}</td>
                            <td>{{ optional($appt->appointment_time)->format('D, M j, Y g:i A') }}</td>
                            <td>{{ $appt->duration }} mins</td>
                            <td><span class="badge bg-secondary text-uppercase text-white">{{ $appt->status }}</span></td>
                            <td class="text-end">
                                @if($appt->doctor)
                                    <a href="https://meet.jit.si/{{ $appt->doctor->meeting_slug ?? 'dr-' . strtolower(str_replace(' ', '-', $appt->doctor->name)) }}"
                                       class="btn btn-sm btn-outline-dark"
                                       title="Open Jitsi meeting"
                                       target="_blank" rel="noopener">
                                        <i class="fa fa-video-camera"></i>
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
                <div class="alert alert-light text-dark mb-0">No appointments yet.</div>
            @endif
        </div>
    </div>

    {{-- Book Doctor Modal --}}
    <div class="modal fade" id="bookDoctorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="appointment-form" action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="health_facility_id" value="{{ $healthFacility->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select form-control" required>
                                    <option value="">Select Patient</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Doctor</label>
                                <select name="doctor_id" class="form-select form-control" required>
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}">Dr. {{ $doc->name }} ({{ $doc->specialization }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Duration</label>
                                <select name="duration" class="form-select form-control" required>
                                    <option value="15">15 minutes</option>
                                    <option value="20">20 minutes</option>
                                    <option value="30">30 minutes</option>
                                    <option value="45">45 minutes</option>
                                    <option value="60">60 minutes</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date & Time</label>
                                <input type="datetime-local" name="appointment_time" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reason</label>
                                <input type="text" name="reason" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection