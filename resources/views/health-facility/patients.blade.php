@extends('layouts.base')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="m-0">Patients</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
            <i class="fa fa-user-plus me-2"></i> Add Patient
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
                                <form method="POST" action="{{ route('patients.delete', ['patient' => $patient->id]) }}" onsubmit="return confirm('Delete this patient? This action cannot be undone.');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
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
<div class="modal fade" id="addPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('patients.create') }}">
                @csrf
                <input type="hidden" name="health_facility_id" value="{{ $healthFacility->id }}">
                <div class="modal-body">
                        <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select form-control" required>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                        </select>
                                </div>
                                <div class="col-md-6">
                                        <label class="form-label">Birth Date</label>
                                        <input type="date" name="birth_date" class="form-control" required>
                                </div>
                        </div>
                        <div class="mt-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control">
                        </div>
                        <div class="mt-3">
                                <label class="form-label">Medical History (Optional)</label>
                                <textarea name="medical_history" rows="3" class="form-control"></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection