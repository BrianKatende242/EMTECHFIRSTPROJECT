@extends('layouts.base')

@push('styles')
<link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header Section with Gradient Background -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm appointments-header-gradient">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h1 class="h3 mb-1 fw-bold text-white">Appointments Management</h1>
                            <p class="mb-0 opacity-85">Manage healthcare appointments and their details</p>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createAppointmentModal">
                                <i class="fa fa-plus me-3"></i> <span>Schedule</span>
                            </button>
                            <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                                <i class="fa fa-tasks me-3"></i> <span>Bulk Update</span>
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
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Appointments Cards Grid -->
    <div class="row g-4" id="admin-appointments-cards">
        @forelse($items as $appointment)
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 appointment-card"
             data-patient="{{ strtolower($appointment->patient->name ?? '') }}"
             data-reason="{{ strtolower($appointment->reason ?? '') }}"
             data-status="{{ $appointment->status }}"
             data-doctor="{{ $appointment->doctor_id }}"
             data-date="{{ $appointment->appointment_time->format('Y-m-d') }}">
            <div class="card h-100 shadow-sm border-0 appointment-card">
                <!-- Card Header with Status Badge -->
                <div class="card-header bg-gradient-primary text-white position-relative appointment-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold text-white" style="font-size: 1rem;">Appointment #{{ $appointment->id }}</h6>
                            <small class="opacity-85">{{ optional($appointment->appointment_time)->format('M j, Y g:i A') ?? '-' }}</small>
                        </div>
                        <!-- Status Badge -->
                        <div class="ms-2">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'scheduled' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $statusColor = $statusColors[$appointment->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} rounded-pill px-2 py-1 appointment-status-badge">
                                <i class="fa fa-circle me-1" style="font-size: 0.5rem;"></i>{{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <!-- Patient Information -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-user me-2"></i>Patient
                        </h6>
                        <div class="p-2 rounded appointment-patient-section">
                            <div class="fw-medium text-dark">{{ $appointment->patient->name ?? '-' }}</div>
                            @if($appointment->patient)
                                <small class="text-muted">ID: {{ $appointment->patient->patient_id ?? $appointment->patient->id }}</small>
                            @endif
                        </div>
                    </div>

                    <!-- Doctor Information -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-user-md me-2"></i>Doctor
                        </h6>
                        <div class="p-2 rounded appointment-doctor-section">
                            <div class="fw-medium text-dark">{{ $appointment->doctor->name ?? '-' }}</div>
                            @if($appointment->doctor && $appointment->doctor->specialization)
                                <small class="text-muted">{{ $appointment->doctor->specialization }}</small>
                            @endif
                        </div>
                    </div>

                    <!-- Reason and Duration -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-calendar-check me-2"></i>Details
                        </h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="p-2 rounded appointment-details-section">
                                    <div class="small text-dark fw-medium">{{ $appointment->reason ?? 'No reason specified' }}</div>
                                    @if($appointment->duration)
                                        <small class="text-muted">Duration: {{ $appointment->duration->name }} ({{ $appointment->duration->minutes }} min)</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Institution -->
                    @if($appointment->school || $appointment->healthFacility)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-building me-2"></i>Institution
                        </h6>
                        <div class="p-2 rounded appointment-institution-section">
                            @if($appointment->school)
                                <span class="badge bg-info small text-white fw-medium">{{ Str::limit($appointment->school->name, 30) }}</span>
                            @elseif($appointment->healthFacility)
                                <span class="badge bg-warning text-dark small fw-medium">{{ Str::limit($appointment->healthFacility->name, 30) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Payment Info -->
                    @if($appointment->payment_reference)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-credit-card me-2"></i>Payment
                        </h6>
                        <div class="p-2 rounded appointment-payment-section">
                            <code class="small text-primary fw-medium">{{ $appointment->payment_reference }}</code>
                        </div>
                    </div>
                    @endif

                    <!-- Metadata -->
                    <div class="pt-2 border-top border-light">
                        <small class="text-muted d-block">
                            <i class="fa fa-calendar-plus me-2"></i>
                            Created {{ optional($appointment->created_at)->format('M j, Y') ?? '-' }}
                        </small>
                        @if($appointment->updated_at && $appointment->updated_at != $appointment->created_at)
                        <small class="text-muted d-block">
                            <i class="fa fa-edit me-2"></i>
                            Updated {{ optional($appointment->updated_at)->format('M j, Y') ?? '-' }}
                        </small>
                        @endif
                    </div>
                </div>

                <!-- Enhanced Card Footer -->
                <div class="card-footer bg-white border-0 p-3">
                    <div class="row g-2">
                        <div class="col-auto">
                            <a href="{{ route('admin.model.edit', ['appointments', $appointment->id]) }}" class="btn btn-outline-info btn-sm fw-semibold px-3 appointment-card-btn" title="View/Edit" style="border-radius: 8px;">
                                <i class="fa fa-eye me-1"></i>View
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.model.edit', ['appointments', $appointment->id]) }}" class="btn btn-outline-secondary btn-sm fw-semibold px-3 appointment-card-btn" title="Edit" style="border-radius: 8px;">
                                <i class="fa fa-edit me-1"></i>Edit
                            </a>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle fw-semibold px-3" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    @if($appointment->status !== 'completed')
                                    <li>
                                        <form action="{{ route('admin.appointments.bulk') }}" method="POST" style="display:inline-block">
                                            @csrf
                                            <input type="hidden" name="action" value="complete">
                                            <input type="hidden" name="ids[]" value="{{ $appointment->id }}">
                                            <button class="dropdown-item" type="submit" style="padding: 0.5rem 1rem;">
                                                <i class="fa fa-check me-2 text-success"></i>Mark Complete
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                    @if($appointment->status !== 'cancelled')
                                    <li>
                                        <form action="{{ route('admin.appointments.bulk') }}" method="POST" style="display:inline-block">
                                            @csrf
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="ids[]" value="{{ $appointment->id }}">
                                            <button class="dropdown-item" type="submit" style="padding: 0.5rem 1rem;">
                                                <i class="fa fa-times me-2 text-danger"></i>Cancel
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.model.destroy', ['appointments', $appointment->id]) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit" onclick="return confirm('Delete this appointment?')" style="padding: 0.5rem 1rem;">
                                                <i class="fa fa-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0">
                <div class="card-body text-center py-5">
                    <i class="fa fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No appointments found</h5>
                    <p class="text-muted">Try adjusting your filters or schedule a new appointment.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">{{ $items->appends(request()->query())->links() }}</div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Update Appointments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.appointments.bulk') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Appointments</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach($items as $appointment)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $appointment->id }}" id="appointment-{{ $appointment->id }}">
                                <label class="form-check-label" for="appointment-{{ $appointment->id }}">
                                    #{{ $appointment->id }} - {{ $appointment->patient->name ?? 'Unknown' }} ({{ ucfirst($appointment->status) }})
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select" required>
                            <option value="">Choose action...</option>
                            <option value="complete">Mark as Completed</option>
                            <option value="cancel">Cancel Appointments</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                </div>
            </form>
        </div>
    </div>
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
    const cards = Array.from(document.querySelectorAll('#admin-appointments-cards .appointment-card'));

    function filterAppointments() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        const selectedStatus = (statusFilter?.value || '').toLowerCase();
        const selectedDoctor = doctorFilter?.value || '';
        const dateFrom = dateFromInput?.value || '';
        const dateTo = dateToInput?.value || '';

        cards.forEach(card => {
            const patient = card.getAttribute('data-patient') || '';
            const reason = card.getAttribute('data-reason') || '';
            const status = card.getAttribute('data-status') || '';
            const doctor = card.getAttribute('data-doctor') || '';
            const date = card.getAttribute('data-date') || '';

            const matchesSearch = !q || patient.includes(q) || reason.includes(q);
            const matchesStatus = !selectedStatus || status === selectedStatus;
            const matchesDoctor = !selectedDoctor || doctor === selectedDoctor;
            const matchesDateFrom = !dateFrom || date >= dateFrom;
            const matchesDateTo = !dateTo || date <= dateTo;

            card.style.display = (matchesSearch && matchesStatus && matchesDoctor && matchesDateFrom && matchesDateTo) ? '' : 'none';
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
