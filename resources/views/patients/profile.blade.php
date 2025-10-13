@extends('layouts.base')

@section('content')
<div class="container-fluid">
    <!-- Patient Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="patient-avatar">
                                <i class="fa fa-user-circle fa-4x text-primary"></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h2 class="mb-1">{{ $patient->name }}</h2>
                            <p class="text-muted mb-2">
                                <strong>Patient ID:</strong> {{ $patient->patient_id }}
                                <button class="btn btn-sm btn-outline-secondary ml-2 copy-btn" data-clipboard-text="{{ $patient->patient_id }}" title="Copy Patient ID">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </p>
                            <div class="d-flex flex-wrap">
                                <span class="badge badge-primary" style="margin-right: 8px;">{{ ucfirst($patient->gender) }}</span>
                                @if($patient->birth_date)
                                <span class="badge badge-info" style="margin-right: 8px;">{{ $patient->birth_date->age }} years old</span>
                                @endif
                                @if($patient->school)
                                <span class="badge badge-success">Student</span>
                                @elseif($patient->healthFacility)
                                <span class="badge badge-warning">Health Facility Patient</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="btn-group" role="group">
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                                <button class="btn btn-outline-primary" onclick="window.print()">
                                    <i class="fa fa-print"></i> Print Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $patient->appointments->count() }}</h3>
                    <p>Total Appointments</p>
                </div>
                <div class="icon">
                    <i class="fa fa-calendar-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $patient->labTests->count() }}</h3>
                    <p>Lab Tests</p>
                </div>
                <div class="icon">
                    <i class="fa fa-flask"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $patient->maternalDocuments->count() }}</h3>
                    <p>Maternal Documents</p>
                </div>
                <div class="icon">
                    <i class="fa fa-file-medical"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $patient->appointments->where('status', 'completed')->count() }}</h3>
                    <p>Completed Appointments</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Patient Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-user mr-2"></i>Patient Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Full Name:</dt>
                                <dd class="col-sm-7">{{ $patient->name }}</dd>

                                <dt class="col-sm-5">Gender:</dt>
                                <dd class="col-sm-7">{{ ucfirst($patient->gender) }}</dd>

                                <dt class="col-sm-5">Date of Birth:</dt>
                                <dd class="col-sm-7">{{ $patient->birth_date ? $patient->birth_date->format('M d, Y') : 'Not provided' }}</dd>

                                <dt class="col-sm-5">Age:</dt>
                                <dd class="col-sm-7">{{ $patient->birth_date ? $patient->birth_date->age . ' years' : 'Not available' }}</dd>

                                <dt class="col-sm-5">Contact:</dt>
                                <dd class="col-sm-7">{{ $patient->contact_number ?: 'Not provided' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                @if($patient->parent_contact)
                                <dt class="col-sm-5">Parent Contact:</dt>
                                <dd class="col-sm-7">{{ $patient->parent_contact }}</dd>
                                @endif

                                @if($patient->grade)
                                <dt class="col-sm-5">Grade:</dt>
                                <dd class="col-sm-7">{{ $patient->grade }}</dd>
                                @endif

                                @if($patient->healthFacility)
                                <dt class="col-sm-5">Health Facility:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge badge-primary">{{ $patient->healthFacility->name }}</span>
                                </dd>
                                @endif

                                @if($patient->school)
                                <dt class="col-sm-5">School:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge badge-success">{{ $patient->school->name }}</span>
                                </dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    @if($patient->medicalHistories->count() > 0)
                    <div class="mt-3">
                        <h5><i class="fa fa-history mr-2"></i>Medical History</h5>
                        <div class="timeline">
                            @foreach($patient->medicalHistories->sortByDesc('recorded_date') as $history)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info">
                                    <i class="fa fa-user-md"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $history->doctor->name }}</strong>
                                            <small class="text-muted d-block">{{ $history->doctor->specialization ?? 'General' }}</small>
                                        </div>
                                        <small class="text-muted">
                                            {{ $history->recorded_date ? $history->recorded_date->format('M d, Y') : $history->created_at->format('M d, Y') }}
                                        </small>
                                    </div>
                                    <div class="mt-2">
                                        <p class="mb-0">{{ $history->content }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Lab Tests Section -->
            @if($patient->labTests->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-flask mr-2"></i>Lab Test History
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fa fa-vial mr-1"></i>Test Type</th>
                                <th><i class="fa fa-calendar-plus mr-1"></i>Requested Date</th>
                                <th><i class="fa fa-tasks mr-1"></i>Status</th>
                                <th><i class="fa fa-clipboard-check mr-1"></i>Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patient->labTests->sortByDesc('created_at') as $labTest)
                            <tr>
                                <td><strong>{{ $labTest->test_type ?: 'N/A' }}</strong></td>
                                <td>{{ $labTest->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ $labTest->status === 'completed' ? 'success' : 'warning' }}">
                                        <i class="fa fa-{{ $labTest->status === 'completed' ? 'check-circle' : 'clock' }} mr-1"></i>
                                        {{ ucfirst($labTest->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($labTest->results)
                                        <span class="text-success">{{ $labTest->results }}</span>
                                    @else
                                        <span class="text-muted">Pending results</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-bolt mr-2"></i>Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary mb-2" data-toggle="modal" data-target="#scheduleAppointmentModal">
                            <i class="fa fa-calendar-plus mr-2"></i>Schedule Appointment
                        </button>
                        <button class="btn btn-success mb-2" disabled>
                            <i class="fa fa-flask mr-2"></i>Order Lab Test
                        </button>
                        <button class="btn btn-info mb-2" disabled>
                            <i class="fa fa-file-medical mr-2"></i>Add Medical Note
                        </button>
                        <button class="btn btn-warning mb-2" disabled>
                            <i class="fa fa-edit mr-2"></i>Update Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Maternal Documents Section -->
            @if($patient->maternalDocuments->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-baby mr-2"></i>Maternal Health Documents
                    </h3>
                </div>
                <div class="card-body">
                    @foreach($patient->maternalDocuments->sortByDesc('created_at') as $document)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                        <div>
                            <strong>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</strong><br>
                            <small class="text-muted">{{ $document->created_at->format('M d, Y') }}</small>
                        </div>
                        <div>
                            @if($document->file_path)
                            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-eye"></i> View
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Full Width Appointment History -->
    @if($patient->appointments->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-calendar-alt mr-2"></i>Appointment History
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fa fa-calendar-day mr-1"></i>Date & Time</th>
                                <th><i class="fa fa-user-md mr-1"></i>Doctor</th>
                                <th><i class="fa fa-info-circle mr-1"></i>Status</th>
                                <th><i class="fa fa-sticky-note mr-1"></i>Notes</th>
                                <th><i class="fa fa-cogs mr-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patient->appointments->sortByDesc('appointment_date') as $appointment)
                            <tr>
                                <td>{{ $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y H:i') : 'N/A' }}</td>
                                <td>
                                    @if($appointment->doctor)
                                        <strong>{{ $appointment->doctor->name }}</strong><br>
                                        <small class="text-muted">{{ $appointment->doctor->specialization ?? 'General' }}</small>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : ($appointment->status === 'awaiting_payment' ? 'warning' : 'secondary')) }}">
                                        <i class="fa fa-{{ $appointment->status === 'completed' ? 'check' : ($appointment->status === 'cancelled' ? 'times' : ($appointment->status === 'awaiting_payment' ? 'credit-card' : 'clock')) }} mr-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                    </span>
                                </td>
                                <td>{{ $appointment->notes ?: 'No notes' }}</td>
                                <td>
                                    @if($appointment->status === 'awaiting_payment')
                                        <a href="{{ route('payment.appointment.pay', $appointment) }}" class="btn btn-sm btn-success">
                                            <i class="fa fa-credit-card mr-1"></i>Pay Now
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Full Width Lab Tests Section -->
    @if($patient->labTests->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-flask mr-2"></i>Lab Test History
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="fa fa-vial mr-1"></i>Test Type</th>
                                <th><i class="fa fa-calendar-plus mr-1"></i>Requested Date</th>
                                <th><i class="fa fa-tasks mr-1"></i>Status</th>
                                <th><i class="fa fa-clipboard-check mr-1"></i>Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patient->labTests->sortByDesc('created_at') as $labTest)
                            <tr>
                                <td><strong>{{ $labTest->test_type ?: 'N/A' }}</strong></td>
                                <td>{{ $labTest->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ $labTest->status === 'completed' ? 'success' : 'warning' }}">
                                        <i class="fa fa-{{ $labTest->status === 'completed' ? 'check-circle' : 'clock' }} mr-1"></i>
                                        {{ ucfirst($labTest->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($labTest->results)
                                        <span class="text-success">{{ $labTest->results }}</span>
                                    @else
                                        <span class="text-muted">Pending results</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Schedule Appointment Modal -->
<div class="modal fade" id="scheduleAppointmentModal" tabindex="-1" role="dialog" aria-labelledby="scheduleAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleAppointmentModalLabel">
                    <i class="fa fa-calendar-plus mr-2"></i>Schedule Appointment for {{ $patient->name }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="appointmentForm" method="POST" action="{{ route('appointments.store') }}">
                @csrf
                <div class="modal-body">
                    <!-- Hidden fields for patient and institution -->
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    @if($patient->school)
                        <input type="hidden" name="school_id" value="{{ $patient->school->id }}">
                    @elseif($patient->healthFacility)
                        <input type="hidden" name="health_facility_id" value="{{ $patient->healthFacility->id }}">
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="doctor_id" class="form-label">
                                    <i class="fa fa-user-md mr-1"></i>Doctor <span class="text-danger">*</span>
                                </label>
                                <select name="doctor_id" id="doctor_id" class="form-control" required>
                                    <option value="">Select Doctor</option>
                                    @php
                                        $doctors = \App\Models\Doctor::all();
                                    @endphp
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">
                                            Dr. {{ $doctor->name }} - {{ $doctor->specialization ?? 'General' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duration_id" class="form-label">
                                    <i class="fa fa-clock mr-1"></i>Duration <span class="text-danger">*</span>
                                </label>
                                <select name="duration_id" id="duration_id" class="form-control" required>
                                    <option value="">Select Duration</option>
                                    @foreach(\App\Models\Duration::active()->get() as $duration)
                                        <option value="{{ $duration->id }}">{{ $duration->minutes }} minutes - {{ ucfirst($duration->type) }}: UGX {{ number_format($duration->getPrice(), 0) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="appointment_date" class="form-label">
                                    <i class="fa fa-calendar mr-1"></i>Appointment Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="appointment_date" id="appointment_date" class="form-control" required
                                       min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="appointment_time" class="form-label">
                                    <i class="fa fa-clock mr-1"></i>Appointment Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" name="appointment_time" id="appointment_time" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="reason" class="form-label">
                            <i class="fa fa-comment mr-1"></i>Reason for Visit <span class="text-danger">*</span>
                        </label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required
                                  placeholder="Please describe the reason for this appointment..."></textarea>
                    </div>

                    <!-- Patient Info Summary -->
                    <div class="alert alert-info text-dark">
                        <h6><i class="fa fa-info-circle mr-1"></i>Appointment Details</h6>
                        <p class="mb-1"><strong>Patient:</strong> {{ $patient->name }} (ID: {{ $patient->patient_id }})</p>
                        @if($patient->school)
                            <p class="mb-1"><strong>Institution:</strong> {{ $patient->school->name }} (School)</p>
                        @elseif($patient->healthFacility)
                            <p class="mb-1"><strong>Institution:</strong> {{ $patient->healthFacility->name }} (Health Facility)</p>
                        @endif
                        <p class="mb-0"><strong>Age:</strong> {{ $patient->birth_date ? $patient->birth_date->age . ' years' : 'Not specified' }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-calendar-check mr-1"></i>Schedule Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Patient Avatar */
.patient-avatar {
    margin-bottom: 15px;
    text-align: center;
}

/* Status Badges */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
}

/* Small Stats Boxes */
.small-box {
    border-radius: 0.375rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 20px;
    position: relative;
    display: block;
    color: #fff;
    transition: transform 0.2s ease-in-out;
}

.small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,.15);
}

.small-box .inner {
    padding: 15px;
}

.small-box .inner h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    white-space: nowrap;
    padding: 0;
    line-height: 1;
}

.small-box .inner p {
    font-size: 0.9rem;
    margin: 0;
    opacity: 0.9;
}

.small-box .icon {
    color: rgba(0,0,0,0.15);
    z-index: 0;
    position: absolute;
    right: 15px;
    top: 15px;
    font-size: 70px;
    transition: all .3s linear;
}

.small-box:hover .icon {
    transform: scale(1.1);
}

.small-box.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

.small-box.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}

.small-box.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    color: #212529 !important;
}

.small-box.bg-warning .icon {
    color: rgba(0,0,0,0.2);
}

.small-box.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%) !important;
}

/* Print Styles */
@media print {
    .btn, .card-tools, .btn-group, .small-box .icon {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
}
.card {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    transition: box-shadow 0.2s ease-in-out;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,.15);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    border-radius: 0.5rem 0.5rem 0 0 !important;
    padding: 1rem 1.25rem;
}

.card-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
}

.card-title i {
    color: #6c757d;
    margin-right: 8px;
}

/* Table Enhancements */
.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
    transform: scale(1.01);
    transition: all 0.2s ease-in-out;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Button Enhancements */
.btn {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,.2);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Description List Styling */
dl.row dt {
    font-weight: 600;
    color: #495057;
    text-align: right;
    padding-right: 15px;
    border-right: 2px solid #dee2e6;
}

dl.row dd {
    margin-bottom: 0.5rem;
    color: #6c757d;
}

/* Alert Enhancements */
.alert {
    border: none;
    border-radius: 0.375rem;
    border-left: 4px solid;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-left-color: #17a2b8;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .small-box .inner h3 {
        font-size: 1.8rem;
    }

    dl.row dt {
        text-align: left;
        border-right: none;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 5px;
        margin-bottom: 5px;
    }
}

/* Print Styles */
@media print {
    .btn, .card-tools, .btn-group, .small-box .icon {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
}

/* Copy Button Styling */
.copy-btn {
    padding: 0.125rem 0.25rem;
    font-size: 0.75rem;
    line-height: 1;
    border-radius: 0.25rem;
    transition: all 0.2s ease-in-out;
}

.copy-btn:hover {
    transform: scale(1.05);
}

.copy-btn i {
    font-size: 0.75rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    color: #fff;
}

.timeline-content {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 15px;
    margin-left: 10px;
}
</style>

{{-- Copy functionality script --}}
<script>
    // Copy to clipboard functionality
    document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.copy-btn');

        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const textToCopy = this.getAttribute('data-clipboard-text');

                if (navigator.clipboard && window.isSecureContext) {
                    // Use the Clipboard API when available
                    navigator.clipboard.writeText(textToCopy).then(function() {
                        showCopyFeedback(button, 'Copied!');
                    }).catch(function(err) {
                        console.error('Failed to copy: ', err);
                        fallbackCopyTextToClipboard(textToCopy, button);
                    });
                } else {
                    // Fallback for older browsers
                    fallbackCopyTextToClipboard(textToCopy, button);
                }
            });
        });

        function fallbackCopyTextToClipboard(text, button) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopyFeedback(button, 'Copied!');
                } else {
                    showCopyFeedback(button, 'Failed to copy', true);
                }
            } catch (err) {
                showCopyFeedback(button, 'Failed to copy', true);
            }

            document.body.removeChild(textArea);
        }

        function showCopyFeedback(button, message, isError = false) {
            const originalIcon = button.querySelector('i');
            const originalClass = originalIcon.className;

            // Change icon temporarily
            originalIcon.className = isError ? 'fa fa-exclamation-triangle' : 'fa fa-check';

            // Change button color temporarily
            button.classList.remove('btn-outline-secondary');
            button.classList.add(isError ? 'btn-outline-danger' : 'btn-outline-success');

            // Reset after 2 seconds
            setTimeout(function() {
                originalIcon.className = originalClass;
                button.classList.remove(isError ? 'btn-outline-danger' : 'btn-outline-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        }
    });

    // Appointment form submission
    $(document).ready(function() {
        $('#appointmentForm').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Scheduling...');

            // Clear any previous alerts
            $('.alert').not('.alert-info').remove();

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Show success message
                    const successAlert = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle mr-1"></i>
                            <strong>Success!</strong> Appointment scheduled successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                    form.closest('.modal-content').prepend(successAlert);

                    // Reset form
                    form[0].reset();

                    // Close modal after 2 seconds
                    setTimeout(function() {
                        $('#scheduleAppointmentModal').modal('hide');
                        // Reload page to show new appointment
                        location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while scheduling the appointment.';

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    // Show error message
                    const errorAlert = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            <strong>Error!</strong> ${errorMessage}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    `;
                    form.closest('.modal-content').prepend(errorAlert);
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Reset form when modal is closed
        $('#scheduleAppointmentModal').on('hidden.bs.modal', function() {
            $('#appointmentForm')[0].reset();
            $('.alert').not('.alert-info').remove();
        });
    });
</script>
@endsection