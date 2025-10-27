@extends('layouts.base')

@push('styles')
<link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h1 class="h3 mb-1 fw-bold">Appointments Management</h1>
                            <p class="mb-0 opacity-85">Manage healthcare appointments and their details</p>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createAppointmentModal">
                                <i class="mdi mdi-download me-2"></i>Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-12 mb-2">
            <input type="text" id="admin-search" class="form-control" placeholder="Search by patient name, reason, or ID...">
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="status-filter" class="form-select form-control">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="doctor-filter" class="form-select form-control">
                <option value="">All Doctors</option>
                @foreach($doctors as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <input type="date" id="date-from" class="form-control" placeholder="From Date">
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <input type="date" id="date-to" class="form-control" placeholder="To Date">
        </div>
        <div class="col-md-1 col-sm-12 mb-2">
            <button type="button" id="clear-filters" class="btn btn-outline-secondary w-100">Clear</button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Appointments Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="appointments-table">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold px-4 py-3">ID</th>
                            <th class="border-0 fw-semibold px-4 py-3">Date & Time</th>
                            <th class="border-0 fw-semibold px-4 py-3">Patient</th>
                            <th class="border-0 fw-semibold px-4 py-3">Doctor</th>
                            <th class="border-0 fw-semibold px-4 py-3">Reason</th>
                            <th class="border-0 fw-semibold px-4 py-3">Institution</th>
                            <th class="border-0 fw-semibold px-4 py-3">Status</th>
                            <th class="border-0 fw-semibold px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $appointment)
                        <tr class="appointment-row"
                            data-patient="{{ strtolower($appointment->patient->name ?? '') }}"
                            data-reason="{{ strtolower($appointment->reason ?? '') }}"
                            data-status="{{ $appointment->status }}"
                            data-doctor="{{ $appointment->doctor_id }}"
                            data-date="{{ $appointment->appointment_time->format('Y-m-d') }}">
                            <td class="px-4 py-3">
                                <span class="fw-medium text-primary">#{{ $appointment->id }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ optional($appointment->appointment_time)->format('M j, Y') ?? '-' }}</span>
                                    <small class="text-muted">{{ optional($appointment->appointment_time)->format('g:i A') ?? '-' }}</small>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $appointment->patient->name ?? '-' }}</span>
                                    @if($appointment->patient)
                                        <small class="text-muted">ID: {{ $appointment->patient->patient_id ?? $appointment->patient->id }}</small>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $appointment->doctor->name ?? '-' }}</span>
                                    @if($appointment->doctor && $appointment->doctor->specialization)
                                        <small class="text-muted">{{ $appointment->doctor->specialization }}</small>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="d-flex flex-column">
                                    <span class="text-truncate" style="max-width: 200px;" title="{{ $appointment->reason ?? 'No reason specified' }}">
                                        {{ Str::limit($appointment->reason ?? 'No reason specified', 30) }}
                                    </span>
                                    @if($appointment->duration)
                                        <small class="text-muted">{{ $appointment->duration->minutes }} min</small>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($appointment->school)
                                    <span class="badge bg-info text-white">{{ Str::limit($appointment->school->name, 25) }}</span>
                                @elseif($appointment->healthFacility)
                                    <span class="badge bg-warning text-dark">{{ Str::limit($appointment->healthFacility->name, 25) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'scheduled' => 'info',
                                        'confirmed' => 'custom-green-text',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusColor = $statusColors[$appointment->status] ?? 'secondary';
                                @endphp
                                @if($appointment->status === 'confirmed')
                                    <span class="badge px-2 py-1" style="background-color: white; color: #28a745; border: 1px solid #28a745;">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                @else
                                    <span class="badge bg-{{ $statusColor }} px-2 py-1">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" class="btn btn-outline-info btn-sm view-appointment-btn" title="View Details"
                                        data-appointment-id="{{ $appointment->id }}"
                                        data-patient-name="{{ $appointment->patient->name ?? '-' }}"
                                        data-patient-id="{{ $appointment->patient->patient_id ?? $appointment->patient->id ?? '-' }}"
                                        data-doctor-name="{{ $appointment->doctor->name ?? '-' }}"
                                        data-doctor-specialization="{{ $appointment->doctor->specialization ?? '-' }}"
                                        data-date="{{ optional($appointment->appointment_time)->format('M j, Y') ?? '-' }}"
                                        data-time="{{ optional($appointment->appointment_time)->format('g:i A') ?? '-' }}"
                                        data-reason="{{ $appointment->reason ?? 'No reason specified' }}"
                                        data-duration="{{ $appointment->duration->minutes ?? '-' }}"
                                        data-status="{{ ucfirst($appointment->status) }}"
                                        data-institution="{{ $appointment->school ? $appointment->school->name : ($appointment->healthFacility ? $appointment->healthFacility->name : '-') }}"
                                        data-payment-ref="{{ $appointment->payment_reference ?? '-' }}">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="mdi mdi-calendar text-muted mb-3" style="font-size: 3rem;"></i>
                                <h5 class="text-muted">No appointments found</h5>
                                <p class="text-muted">Try adjusting your filters or schedule a new appointment.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">{{ $items->appends(request()->query())->links() }}</div>
</div>

<!-- Create Appointment Modal (Placeholder - would need full form implementation) -->
<div class="modal fade appointments-modal" id="createAppointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Appointment creation form would go here. For now, use the generic form.</p>
                <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary btn-sm">Go to Create Form</a>
            </div>
        </div>
    </div>
</div>

<!-- View Appointment Details Modal -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1" aria-labelledby="viewAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewAppointmentModalLabel">
                    <i class="mdi mdi-calendar-clock me-2"></i>Appointment Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3">
                                    <i class="mdi mdi-account me-2"></i>Patient Information
                                </h6>
                                <div class="mb-2">
                                    <strong>Name:</strong> <span id="modal-patient-name">-</span>
                                </div>
                                <div class="mb-0">
                                    <strong>ID:</strong> <span id="modal-patient-id">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-success mb-3">
                                    <i class="mdi mdi-doctor me-2"></i>Doctor Information
                                </h6>
                                <div class="mb-2">
                                    <strong>Name:</strong> <span id="modal-doctor-name">-</span>
                                </div>
                                <div class="mb-0">
                                    <strong>Specialization:</strong> <span id="modal-doctor-specialization">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-info mb-3">
                                    <i class="mdi mdi-calendar me-2"></i>Appointment Schedule
                                </h6>
                                <div class="mb-2">
                                    <strong>Date:</strong> <span id="modal-date">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Time:</strong> <span id="modal-time">-</span>
                                </div>
                                <div class="mb-0">
                                    <strong>Duration:</strong> <span id="modal-duration">-</span> minutes
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-warning mb-3">
                                    <i class="mdi mdi-information me-2"></i>Appointment Details
                                </h6>
                                <div class="mb-2">
                                    <strong>Status:</strong>
                                    <span id="modal-status" class="badge ms-2">-</span>
                                </div>
                                <div class="mb-2">
                                    <strong>Institution:</strong> <span id="modal-institution">-</span>
                                </div>
                                <div class="mb-0">
                                    <strong>Payment Ref:</strong> <span id="modal-payment-ref">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-secondary mb-3">
                                    <i class="mdi mdi-note-text me-2"></i>Reason for Visit
                                </h6>
                                <p class="mb-0" id="modal-reason">No reason specified</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const searchInput = document.getElementById('admin-search');
    const statusFilter = document.getElementById('status-filter');
    const doctorFilter = document.getElementById('doctor-filter');
    const dateFromInput = document.getElementById('date-from');
    const dateToInput = document.getElementById('date-to');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const rows = Array.from(document.querySelectorAll('#appointments-table .appointment-row'));

    // View Appointment Modal functionality
    // Direct click handler for view appointment buttons
    document.addEventListener('click', function(e) {
        const viewBtn = e.target.closest('.view-appointment-btn');
        if (viewBtn) {
            e.preventDefault();
            e.stopPropagation();

            // Get data from button attributes
            const appointmentId = viewBtn.getAttribute('data-appointment-id');
            const patientName = viewBtn.getAttribute('data-patient-name');
            const patientId = viewBtn.getAttribute('data-patient-id');
            const doctorName = viewBtn.getAttribute('data-doctor-name');
            const doctorSpecialization = viewBtn.getAttribute('data-doctor-specialization');
            const date = viewBtn.getAttribute('data-date');
            const time = viewBtn.getAttribute('data-time');
            const reason = viewBtn.getAttribute('data-reason');
            const duration = viewBtn.getAttribute('data-duration');
            const status = viewBtn.getAttribute('data-status');
            const institution = viewBtn.getAttribute('data-institution');
            const paymentRef = viewBtn.getAttribute('data-payment-ref');

            // Populate modal with data
            document.getElementById('modal-patient-name').textContent = patientName;
            document.getElementById('modal-patient-id').textContent = patientId;
            document.getElementById('modal-doctor-name').textContent = doctorName;
            document.getElementById('modal-doctor-specialization').textContent = doctorSpecialization;
            document.getElementById('modal-date').textContent = date;
            document.getElementById('modal-time').textContent = time;
            document.getElementById('modal-reason').textContent = reason;
            document.getElementById('modal-duration').textContent = duration;
            document.getElementById('modal-institution').textContent = institution;
            document.getElementById('modal-payment-ref').textContent = paymentRef;

            // Set status badge with appropriate color
            const statusBadge = document.getElementById('modal-status');
            statusBadge.textContent = status;
            statusBadge.className = 'badge ms-2';

            const statusColors = {
                'Pending': 'bg-warning text-dark',
                'Scheduled': 'bg-info text-white',
                'Confirmed': 'bg-white text-success border border-success',
                'Completed': 'bg-success text-white',
                'Cancelled': 'bg-danger text-white'
            };

            if (status === 'Confirmed') {
                statusBadge.classList.add('bg-white', 'text-success', 'border', 'border-success');
            } else {
                const colorClass = statusColors[status] || 'bg-secondary text-white';
                statusBadge.classList.add(...colorClass.split(' '));
            }

            // Show modal using jQuery (same as transaction modal)
            $('#viewAppointmentModal').modal('show');
        }
    });

    function filterAppointments() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        const selectedStatus = (statusFilter?.value || '').toLowerCase();
        const selectedDoctor = doctorFilter?.value || '';
        const dateFrom = dateFromInput?.value || '';
        const dateTo = dateToInput?.value || '';

        rows.forEach(row => {
            const patient = row.getAttribute('data-patient') || '';
            const reason = row.getAttribute('data-reason') || '';
            const status = row.getAttribute('data-status') || '';
            const doctor = row.getAttribute('data-doctor') || '';
            const date = row.getAttribute('data-date') || '';

            const matchesSearch = !q || patient.includes(q) || reason.includes(q);
            const matchesStatus = !selectedStatus || status === selectedStatus;
            const matchesDoctor = !selectedDoctor || doctor === selectedDoctor;
            const matchesDateFrom = !dateFrom || date >= dateFrom;
            const matchesDateTo = !dateTo || date <= dateTo;

            row.style.display = (matchesSearch && matchesStatus && matchesDoctor && matchesDateFrom && matchesDateTo) ? '' : 'none';
        });
    }

    function clearFilters() {
        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = '';
        if (doctorFilter) doctorFilter.value = '';
        if (dateFromInput) dateFromInput.value = '';
        if (dateToInput) dateToInput.value = '';
        filterAppointments();
    }

    if (searchInput) searchInput.addEventListener('input', filterAppointments);
    if (statusFilter) statusFilter.addEventListener('change', filterAppointments);
    if (doctorFilter) doctorFilter.addEventListener('change', filterAppointments);
    if (dateFromInput) dateFromInput.addEventListener('change', filterAppointments);
    if (dateToInput) dateToInput.addEventListener('change', filterAppointments);
    if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', clearFilters);
});
</script>
@endpush
