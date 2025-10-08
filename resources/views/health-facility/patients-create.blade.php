@extends('layouts.base')

@section('content')
<div class="container-fluid">
    <h3 class="mb-4">Add Patient</h3>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('patients.create') }}">
                @csrf
                <input type="hidden" name="health_facility_id" value="{{ $healthFacility->id }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select form-control" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Medical History (Optional)</label>
                        <textarea name="medical_history" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('health-facility.patients', ['id' => $healthFacility->id]) }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection