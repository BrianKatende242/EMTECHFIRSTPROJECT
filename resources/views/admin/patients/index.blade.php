@extends('layouts.base')

@push('styles')
<link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header Section with Gradient Background -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm doctors-header-gradient">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h1 class="h3 mb-1 fw-bold text-white">Patients Management</h1>
                            <p class="mb-0 opacity-85">Manage patient information and records</p>
                        </div>
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createPatientModal">
                                <i class="fa fa-plus me-3"></i> <span>Add Patient</span>
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
            <input type="text" id="admin-search" class="form-control" placeholder="Search by name, ID, or contact...">
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="gender-filter" class="form-select form-control">
                <option value="">All Genders</option>
                @foreach($genders as $gender)
                    <option value="{{ $gender }}">{{ ucfirst($gender) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="school-filter" class="form-select form-control">
                <option value="">All Schools</option>
                @foreach($schools as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="facility-filter" class="form-select form-control">
                <option value="">All Facilities</option>
                @foreach($hfs as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1 col-sm-12 mb-2">
            <input type="number" id="min-age" class="form-control" placeholder="Min Age" min="0">
        </div>
        <div class="col-md-1 col-sm-12 mb-2">
            <input type="number" id="max-age" class="form-control" placeholder="Max Age" min="0">
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

    <!-- Patients Cards Grid -->
    <div class="row g-4" id="admin-patients-cards">
        @forelse($items as $patient)
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 patient-card"
             data-name="{{ strtolower($patient->name ?? '') }}"
             data-patient-id="{{ strtolower($patient->patient_id ?? '') }}"
             data-contact="{{ strtolower($patient->contact_number ?? '') }}"
             data-parent-contact="{{ strtolower($patient->parent_contact ?? '') }}"
             data-gender="{{ $patient->gender }}"
             data-school="{{ $patient->school_id }}"
             data-facility="{{ $patient->health_facility_id }}"
             data-age="{{ $patient->age }}">
            <div class="card h-100 shadow-sm border-0 patient-card">
                <!-- Card Header with Patient Info -->
                <div class="card-header bg-gradient-primary text-white position-relative patient-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold text-white" style="font-size: 1rem;">{{ $patient->name }}</h6>
                            <small class="opacity-85">{{ $patient->patient_id }} • {{ $patient->age ?? '-' }} years old</small>
                        </div>
                        <!-- Gender Badge -->
                        <div class="ms-2">
                            @php
                                $genderColors = [
                                    'male' => 'primary',
                                    'female' => 'success',
                                    'other' => 'secondary'
                                ];
                                $genderColor = $genderColors[$patient->gender] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $genderColor }} rounded-pill px-2 py-1 patient-gender-badge">
                                <i class="fa fa-{{ $patient->gender === 'male' ? 'mars' : ($patient->gender === 'female' ? 'venus' : 'genderless') }} me-1"></i>{{ ucfirst($patient->gender ?? 'Unknown') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <!-- Contact Information -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-address-card me-2"></i>Contact Information
                        </h6>
                        <div class="row g-2">
                            @if($patient->contact_number)
                            <div class="col-12">
                                <div class="d-flex align-items-center p-2 rounded patient-contact-section">
                                    <i class="fa fa-phone text-primary me-2" style="width: 16px;"></i>
                                    <a href="tel:{{ $patient->contact_number }}" class="text-decoration-none small text-dark fw-medium">{{ $patient->contact_number }}</a>
                                </div>
                            </div>
                            @endif

                            @if($patient->parent_contact)
                            <div class="col-12">
                                <div class="d-flex align-items-center p-2 rounded patient-parent-section">
                                    <i class="fa fa-user-friends text-success me-2" style="width: 16px;"></i>
                                    <span class="small text-dark fw-medium">Parent: {{ $patient->parent_contact }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Institution Information -->
                    @if($patient->school || $patient->healthFacility)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-building me-2"></i>Affiliation
                        </h6>
                        <div class="row g-2">
                            @if($patient->school)
                            <div class="col-12">
                                <div class="d-flex align-items-center p-2 rounded patient-school-section">
                                    <i class="fa fa-school text-info me-2" style="width: 16px;"></i>
                                    <span class="badge bg-info small text-white fw-medium">{{ Str::limit($patient->school->name, 25) }}</span>
                                    @if($patient->grade)
                                        <small class="ms-2 text-muted">Grade: {{ $patient->grade }}</small>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($patient->healthFacility)
                            <div class="col-12">
                                <div class="d-flex align-items-center p-2 rounded patient-facility-section">
                                    <i class="fa fa-hospital text-warning me-2" style="width: 16px;"></i>
                                    <span class="badge bg-warning text-dark small fw-medium">{{ Str::limit($patient->healthFacility->name, 25) }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Appointments Summary -->
                    @if($patient->appointments && $patient->appointments->count() > 0)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-calendar-check me-2"></i>Appointments
                        </h6>
                        <div class="p-2 rounded patient-appointments-section">
                            <div class="small text-dark fw-medium">{{ $patient->appointments->count() }} total appointments</div>
                            <div class="small text-muted">
                                {{ $patient->appointments->where('status', 'completed')->count() }} completed,
                                {{ $patient->appointments->whereIn('status', ['pending', 'scheduled'])->count() }} upcoming
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Birth Date -->
                    @if($patient->birth_date)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-birthday-cake me-2"></i>Birth Information
                        </h6>
                        <div class="p-2 rounded patient-birth-section">
                            <div class="small text-dark fw-medium">{{ optional($patient->birth_date)->format('M j, Y') }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- Metadata -->
                    <div class="pt-2 border-top border-light">
                        <small class="text-muted d-block">
                            <i class="fa fa-calendar-plus me-2"></i>
                            Created {{ optional($patient->created_at)->format('M j, Y') ?? '-' }}
                        </small>
                        @if($patient->updated_at && $patient->updated_at != $patient->created_at)
                        <small class="text-muted d-block">
                            <i class="fa fa-edit me-2"></i>
                            Updated {{ optional($patient->updated_at)->format('M j, Y') ?? '-' }}
                        </small>
                        @endif
                    </div>
                </div>

                <!-- Enhanced Card Footer -->
                <div class="card-footer bg-white border-0 p-3">
                    <div class="row g-2">
                        <div class="col-auto">
                            <a href="{{ route('admin.model.edit', ['patients', $patient->id]) }}" class="btn btn-outline-info btn-sm fw-semibold px-3 patient-card-btn" title="View/Edit" style="border-radius: 8px;">
                                <i class="fa fa-eye me-1"></i>View
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.model.edit', ['patients', $patient->id]) }}" class="btn btn-outline-secondary btn-sm fw-semibold px-3 patient-card-btn" title="Edit" style="border-radius: 8px;">
                                <i class="fa fa-edit me-1"></i>Edit
                            </a>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle fw-semibold px-3" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li>
                                        <a href="{{ route('patients.profile', $patient->id) }}" class="dropdown-item" target="_blank">
                                            <i class="fa fa-external-link me-2 text-info"></i>View Full Profile
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.model.destroy', ['patients', $patient->id]) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit" onclick="return confirm('Delete this patient?')" style="padding: 0.5rem 1rem;">
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
                    <i class="fa fa-user-md fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No patients found</h5>
                    <p class="text-muted">Try adjusting your filters or add a new patient.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">{{ $items->appends(request()->query())->links() }}</div>
</div>

<!-- Create Patient Modal (Placeholder) -->
<div class="modal fade doctors-modal" id="createPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Patient creation form would go here. For now, use the generic form.</p>
                <a href="{{ route('admin.patients.create') }}" class="btn btn-primary btn-sm">Go to Create Form</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const searchInput = document.getElementById('admin-search');
    const genderFilter = document.getElementById('gender-filter');
    const schoolFilter = document.getElementById('school-filter');
    const facilityFilter = document.getElementById('facility-filter');
    const minAgeInput = document.getElementById('min-age');
    const maxAgeInput = document.getElementById('max-age');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const cards = Array.from(document.querySelectorAll('#admin-patients-cards .patient-card'));

    function filterPatients() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        const selectedGender = (genderFilter?.value || '').toLowerCase();
        const selectedSchool = schoolFilter?.value || '';
        const selectedFacility = facilityFilter?.value || '';
        const minAge = minAgeInput?.value ? parseInt(minAgeInput.value) : null;
        const maxAge = maxAgeInput?.value ? parseInt(maxAgeInput.value) : null;

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const patientId = card.getAttribute('data-patient-id') || '';
            const contact = card.getAttribute('data-contact') || '';
            const parentContact = card.getAttribute('data-parent-contact') || '';
            const gender = card.getAttribute('data-gender') || '';
            const school = card.getAttribute('data-school') || '';
            const facility = card.getAttribute('data-facility') || '';
            const age = card.getAttribute('data-age') ? parseInt(card.getAttribute('data-age')) : null;

            const matchesSearch = !q || name.includes(q) || patientId.includes(q) || contact.includes(q) || parentContact.includes(q);
            const matchesGender = !selectedGender || gender === selectedGender;
            const matchesSchool = !selectedSchool || school === selectedSchool;
            const matchesFacility = !selectedFacility || facility === selectedFacility;
            const matchesMinAge = !minAge || (age !== null && age >= minAge);
            const matchesMaxAge = !maxAge || (age !== null && age <= maxAge);

            card.style.display = (matchesSearch && matchesGender && matchesSchool && matchesFacility && matchesMinAge && matchesMaxAge) ? '' : 'none';
        });
    }

    function clearFilters() {
        if (searchInput) searchInput.value = '';
        if (genderFilter) genderFilter.value = '';
        if (schoolFilter) schoolFilter.value = '';
        if (facilityFilter) facilityFilter.value = '';
        if (minAgeInput) minAgeInput.value = '';
        if (maxAgeInput) maxAgeInput.value = '';
        filterPatients();
    }

    if (searchInput) searchInput.addEventListener('input', filterPatients);
    if (genderFilter) genderFilter.addEventListener('change', filterPatients);
    if (schoolFilter) schoolFilter.addEventListener('change', filterPatients);
    if (facilityFilter) facilityFilter.addEventListener('change', filterPatients);
    if (minAgeInput) minAgeInput.addEventListener('input', filterPatients);
    if (maxAgeInput) maxAgeInput.addEventListener('input', filterPatients);
    if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', clearFilters);
});
</script>
@endpush