@extends('layouts.base')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="m-0">Patients</h3>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPatientModal">
            <i class="mdi mdi-account-plus me-2"></i> Add Patient
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-light text-dark">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($patients->count())
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped text-dark">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Contact</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                        <tr>
                            <td>{{ $patient->id }}</td>
                            <td>{{ $patient->name }}</td>
                            <td>{{ ucfirst($patient->gender) }}</td>
                            <td>{{ \Carbon\Carbon::parse($patient->birth_date)->age }}</td>
                            <td>{{ $patient->contact_number ?? 'N/A' }}</td>
                            <td class="text-end">
                                <a href="{{ route('patients.profile', ['patient' => $patient->id]) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="mdi mdi-account"></i> Profile
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-toggle="modal"
                                        data-target="#confirmDeleteModal"
                                        data-action="{{ route('patients.delete', ['patient' => $patient->id]) }}"
                                        data-name="{{ $patient->name }}">
                                    <i class="mdi mdi-delete"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-info">No patients yet.</div>
    @endif
</div>
{{-- Add Patient Modal --}}
<div class="modal fade" id="addPatientModal" tabindex="-1" role="dialog" aria-labelledby="addPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPatientModalLabel">Add Patient</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" style="display: block !important;"></button>
            </div>
            <form method="POST" action="{{ route('patients.create') }}" id="patientForm">
                @csrf
                <input type="hidden" name="health_facility_id" value="{{ $healthFacility->id }}">
                <div class="modal-body">
                    <!-- Patient Type Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Patient Type</label>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="form-check flex-fill">
                                <input class="form-check-input" type="radio" name="patient_type" id="newPatient" value="new" checked>
                                <label class="form-check-label" for="newPatient" style="word-wrap: break-word; hyphens: auto;">
                                    <strong>New Patient</strong><br>
                                    <small class="text-muted">Create a new patient record</small>
                                </label>
                            </div>
                            <div class="form-check flex-fill">
                                <input class="form-check-input" type="radio" name="patient_type" id="existingPatient" value="existing">
                                <label class="form-check-label" for="existingPatient" style="word-wrap: break-word; hyphens: auto;">
                                    <strong>Existing Patient</strong><br>
                                    <small class="text-muted">Associate existing patient by Patient ID</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Patient Section -->
                    <div id="existingPatientSection" class="d-none">
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="mb-3">Associate Existing Patient</h6>
                            <div class="mb-3">
                                <label class="form-label">Patient ID <span class="text-danger">*</span></label>
                                <input type="text" name="patient_id" class="form-control" placeholder="Enter Patient ID">
                                <small class="form-text text-muted">Enter the unique Patient ID to associate with this health facility</small>
                            </div>
                        </div>
                    </div>

                    <!-- New Patient Section -->
                    <div id="newPatientSection">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="mb-3">Create New Patient</h6>
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Birth Date <span class="text-danger">*</span></label>
                                    <input type="date" name="birth_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" placeholder="e.g., +256 711 111 111">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

        {{-- Confirm Delete Modal --}}
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-dark">
                        Are you sure you want to delete <strong id="deletePatientName">this patient</strong>? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                        <form id="deletePatientForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Patient type selection functionality
    const newPatientRadio = document.getElementById('newPatient');
    const existingPatientRadio = document.getElementById('existingPatient');
    const newPatientSection = document.getElementById('newPatientSection');
    const existingPatientSection = document.getElementById('existingPatientSection');
    const patientForm = document.getElementById('patientForm');

    function togglePatientSections() {
        if (newPatientRadio && newPatientRadio.checked) {
            if (newPatientSection) newPatientSection.classList.remove('d-none');
            if (existingPatientSection) existingPatientSection.classList.add('d-none');
            // Make new patient fields required
            document.querySelectorAll('#newPatientSection input[required], #newPatientSection select[required]').forEach(field => {
                field.setAttribute('required', 'required');
            });
            // Remove required from existing patient fields
            document.querySelectorAll('#existingPatientSection input[required]').forEach(field => {
                field.removeAttribute('required');
            });
        } else if (existingPatientRadio && existingPatientRadio.checked) {
            if (newPatientSection) newPatientSection.classList.add('d-none');
            if (existingPatientSection) existingPatientSection.classList.remove('d-none');
            // Make existing patient ID required
            document.querySelector('#existingPatientSection input[name="patient_id"]').setAttribute('required', 'required');
            // Remove required from new patient fields
            document.querySelectorAll('#newPatientSection input[required], #newPatientSection select[required]').forEach(field => {
                field.removeAttribute('required');
            });
        }
    }

    // Add event listeners with null checks
    if (newPatientRadio) newPatientRadio.addEventListener('change', togglePatientSections);
    if (existingPatientRadio) existingPatientRadio.addEventListener('change', togglePatientSections);

    // Initialize on modal show
    document.getElementById('addPatientModal').addEventListener('show.bs.modal', function() {
        // Reset to new patient by default
        if (newPatientRadio) newPatientRadio.checked = true;
        togglePatientSections();
    });

    var modalEl = document.getElementById('confirmDeleteModal');
    modalEl.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var action = button.getAttribute('data-action');
        var name = button.getAttribute('data-name');
        modalEl.querySelector('#deletePatientForm').setAttribute('action', action);
        modalEl.querySelector('#deletePatientName').textContent = name || 'this patient';
    });
});
</script>
@endpush
@endsection