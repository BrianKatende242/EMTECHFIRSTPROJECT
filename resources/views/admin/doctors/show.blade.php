@extends('layouts.base')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Dr. {{ $doctor->name }}</h2>
        <div class="d-flex gap-2 flex-nowrap">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editDoctorModal">Edit</button>
            <a href="#" class="btn btn-sm btn-primary" onclick="navigator.clipboard && navigator.clipboard.writeText('https://meet.jit.si/{{ $doctor->meeting_slug }}')">Copy Meeting URL</a>
            <a target="_blank" href="https://meet.jit.si/{{ $doctor->meeting_slug }}" class="btn btn-sm btn-success">Join Meeting</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <img src="{{ $doctor->file_url ?? asset('images/doctor.png') }}" alt="profile" class="rounded-circle mb-3" style="width:120px; height:120px; object-fit:cover;">
                    <h5 class="card-title">{{ $doctor->name }}</h5>
                    <p class="mb-1 small text-muted">{{ $doctor->specialization }}</p>
                    <p class="mb-1 small">{{ $doctor->contact }}</p>
                    <p class="mb-0 small text-muted">{{ $doctor->email }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Stats</h6>
                    <ul class="list-unstyled mb-0">
                        <li>Total appointments: <strong>{{ $totalAppointments }}</strong></li>
                        <li>Completed: <strong>{{ $completed }}</strong></li>
                        <li>Cancelled: <strong>{{ $cancelled }}</strong></li>
                        <li>Upcoming: <strong>{{ $upcoming }}</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Recent Appointments</h6>
                    <div class="table-responsive">
                        @php $recent = $doctor->appointments()->with(['student','patient','school','healthFacility'])->latest()->limit(50)->get(); @endphp
                        @if($recent->isEmpty())
                            <div class="text-muted">None found</div>
                        @else
                        <table class="table table-striped table-sm text-dark">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th class="text-nowrap">Time</th>
                                    <th>Student/Patient</th>
                                    <th class="text-nowrap">Institution</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent as $appt)
                                <tr>
                                    <td>{{ $appt->id }}</td>
                                    <td class="text-nowrap">{{ optional($appt->appointment_time)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $appt->user()?->name ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $appt->institution()?->name ?? '-' }}</td>
                                    <td>{{ $appt->status ?? '-' }}</td>
                                    <td class="text-nowrap">
                                        <div class="d-flex gap-2 flex-nowrap">
                                            <a href="{{ route('doctor.dashboard', $doctor->id) }}" class="btn btn-sm btn-outline-primary">View Dashboard</a>
                                            <a href="{{ route('doctor.meeting-link', $doctor->id) }}" class="btn btn-sm btn-outline-secondary">Meeting Links</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Edit Doctor Modal -->
<div class="modal fade" id="editDoctorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.model.update', ['doctors', $doctor->id]) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="{{ route('admin.doctors.show', $doctor->id) }}">
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Name</label>
                            <input name="name" class="form-control" required value="{{ old('name', $doctor->name) }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Email</label>
                            <input name="email" type="email" class="form-control" value="{{ old('email', $doctor->email) }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Specialization</label>
                            <input name="specialization" class="form-control" value="{{ old('specialization', $doctor->specialization) }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Contact</label>
                            <input name="contact" class="form-control" value="{{ old('contact', $doctor->contact) }}">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">School</label>
                            @php $schools = \App\Models\School::pluck('name','id'); @endphp
                            <select name="school_id" class="form-select form-control">
                                <option value="">-- none --</option>
                                @foreach($schools as $id => $label)
                                    <option value="{{ $id }}" @if(old('school_id', $doctor->school_id) == $id) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Health Facility</label>
                            @php $hfs = \App\Models\HealthFacility::pluck('name','id'); @endphp
                            <select name="health_facility_id" class="form-select form-control">
                                <option value="">-- none --</option>
                                @foreach($hfs as $id => $label)
                                    <option value="{{ $id }}" @if(old('health_facility_id', $doctor->health_facility_id) == $id) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Profile Image</label>
                            <input name="file_url" type="file" accept="image/*" class="form-control">
                            @if($doctor->file_url)
                                <div class="mt-2"><img src="{{ $doctor->file_url }}" style="max-height:60px" alt="profile"></div>
                            @endif
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Open edit modal if validation errors present from update
    @if($errors->any() && old())
        var myModal = new bootstrap.Modal(document.getElementById('editDoctorModal'));
        myModal.show();
    @endif
});
</script>
@endpush
