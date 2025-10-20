@extends('layouts.base')

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header Section with Gradient Background -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm doctors-header-gradient">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h1 class="h3 mb-1 fw-bold text-white">Doctors Management</h1>
                            <p class="mb-0 opacity-85">Manage healthcare professionals and their information</p>
                        </div>
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createDoctorModal">
                                <i class="fa fa-plus me-3"></i> <span>Register</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
<div class="row mb-4">
    <div class="col-md-4 col-sm-12 mb-2">
        <input type="text" id="admin-search" class="form-control" placeholder="Search by name, email, or specialization...">
    </div>
    <div class="col-md-4 col-sm-12 mb-2">
        @php
            $specializations = \App\Models\Doctor::select('specialization')
                ->distinct()
                ->whereNotNull('specialization')
                ->pluck('specialization')
                ->sort();
        @endphp
        <select id="specialization-filter" class="form-select form-control">
            <option value="">All Specializations</option>
            @foreach($specializations as $specialization)
                <option value="{{ strtolower($specialization) }}">{{ $specialization }}</option>
            @endforeach
        </select>
    </div>
</div>


    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


        <!-- Doctors Cards Grid -->
        <div class="row g-4" id="admin-doctors-cards">
            @forelse($items as $index => $doctor)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 doctor-card"
                 data-name="{{ strtolower($doctor->name ?? '') }}"
                 data-email="{{ strtolower($doctor->email ?? '') }}"
                 data-specialization="{{ strtolower($doctor->specialization ?? '') }}">
                <div class="card h-100 shadow-sm border-0 doctor-card">
                    <!-- Card Header with Enhanced Avatar -->
                    <div class="card-header bg-gradient-primary text-white position-relative doctor-card-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-4 bg-white text-primary d-flex align-items-center justify-content-center border border-3 border-white doctor-avatar">
                                {{ strtoupper(substr($doctor->name ?? 'D', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold text-white" style="font-size: 1.1rem;">{{ $doctor->name ?? '-' }}</h6>
                                <small class="opacity-85">#{{ $doctor->id }}</small>
                                @if($doctor->specialization)
                                    <div class="mt-1">
                                        <span class="badge bg-light text-primary small fw-semibold">{{ $doctor->specialization }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- Status Indicator -->
                        <div class="position-absolute top-0 end-0 mt-2 me-2">
                            <span class="badge bg-success rounded-pill px-2 py-1 doctor-status-badge">
                                <i class="fa fa-circle me-2" style="font-size: 0.5rem;"></i>Active
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <!-- Contact Information Section -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa fa-address-card me-2"></i>Contact Information
                            </h6>
                            <div class="row g-2">
                                @if($doctor->email)
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 rounded doctor-contact-section">
                                        <i class="fa fa-envelope text-primary me-2" style="width: 16px;"></i>
                                        <a href="mailto:{{ $doctor->email }}" class="text-decoration-none small text-dark fw-medium">{{ $doctor->email }}</a>
                                    </div>
                                </div>
                                @endif

                                @if($doctor->contact)
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 rounded doctor-contact-section">
                                        <i class="fa fa-phone text-success me-2" style="width: 16px;"></i>
                                        <a href="tel:{{ $doctor->contact }}" class="text-decoration-none small text-dark fw-medium">{{ $doctor->contact }}</a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Affiliation Section -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa fa-building me-2"></i>Affiliations
                            </h6>
                            <div class="row g-2">
                                @if($doctor->school)
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 rounded doctor-school-section">
                                        <i class="fa fa-school text-info me-2" style="width: 16px;"></i>
                                        <span class="badge bg-info small text-white fw-medium">{{ Str::limit($doctor->school->name, 25) }}</span>
                                    </div>
                                </div>
                                @endif

                                @if($doctor->healthFacility)
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-2 rounded doctor-facility-section">
                                        <i class="fa fa-hospital text-warning me-2" style="width: 16px;"></i>
                                        <span class="badge bg-warning text-dark small fw-medium">{{ Str::limit($doctor->healthFacility->name, 25) }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Meeting Information -->
                        @if($doctor->meeting_slug)
                        <div class="mb-3">
                            <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa fa-video me-2"></i>Meeting Room
                            </h6>
                            <div class="p-2 rounded doctor-meeting-section">
                                <code class="small text-primary fw-medium">{{ $doctor->meeting_slug }}</code>
                                <button class="btn btn-sm btn-outline-primary ms-2 doctor-copy-btn" onclick="navigator.clipboard.writeText('https://meet.jit.si/{{ $doctor->meeting_slug }}')" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        @endif

                        <!-- Metadata -->
                        <div class="pt-2 border-top border-light">
                            <small class="text-muted d-block">
                                <i class="fa fa-calendar-plus me-2"></i>
                                Created {{ optional($doctor->created_at)->format('M j, Y') ?? '-' }}
                            </small>
                            @if($doctor->updated_at && $doctor->updated_at != $doctor->created_at)
                            <small class="text-muted d-block">
                                <i class="fa fa-edit me-2"></i>
                                Updated {{ optional($doctor->updated_at)->format('M j, Y') ?? '-' }}
                            </small>
                            @endif
                        </div>
                    </div>

                    <!-- Enhanced Card Footer -->
                    <div class="card-footer bg-white border-0 p-3">
                        <div class="row g-2">
                            <div class="col-auto">
                                <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="btn btn-outline-info btn-sm fw-semibold px-3 doctor-card-btn" title="View Profile" style="border-radius: 8px;">
                                    <i class="fa fa-eye me-3"></i>View
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('admin.model.edit', ['doctors', $doctor->id]) }}" class="btn btn-outline-secondary btn-sm fw-semibold px-3 doctor-card-btn" title="Edit" style="border-radius: 8px;">
                                    <i class="fa fa-edit me-3"></i>Edit
                                </a>
                            </div>
                            <div class="col-auto">
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle fw-semibold px-3" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <form action="{{ route('admin.doctors.send-login', $doctor->id) }}" method="POST" style="display:inline-block">
                                                @csrf
                                                <button class="dropdown-item" type="submit" onclick="return confirm('Send login OTP to this doctor?')" style="padding: 0.5rem 1rem;">
                                                    <i class="fa fa-envelope me-3 text-primary"></i>Send Login Link
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.model.destroy', ['doctors', $doctor->id]) }}" method="POST" style="display:inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button class="dropdown-item text-danger" type="submit" onclick="return confirm('Delete this doctor?')" style="padding: 0.5rem 1rem;">
                                                    <i class="fa fa-trash me-3"></i>Delete
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
                        <h5 class="text-muted">No doctors found</h5>
                        <p class="text-muted">Try adjusting your filters or create a new doctor.</p>

                    </div>
                </div>
            </div>
            @endforelse
        </div>
    <!-- Create Doctor Modal -->
<div class="modal fade doctors-modal" id="createDoctorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.model.store', 'doctors') }}" enctype="multipart/form-data">
                @csrf
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
                            <input name="name" class="form-control" required value="{{ old('name') }}">
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Email</label>
                            <input name="email" type="email" class="form-control" value="{{ old('email') }}">
                            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Specialization</label>
                            <input name="specialization" class="form-control" value="{{ old('specialization') }}">
                            @error('specialization')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Contact</label>
                            <input name="contact" class="form-control" value="{{ old('contact') }}">
                            @error('contact')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">School</label>
                            @php $schools = \App\Models\School::pluck('name','id'); @endphp
                            <select name="school_id" class="form-select form-control">
                                <option value="">-- none --</option>
                                @foreach($schools as $id => $label)
                                    <option value="{{ $id }}" @if(old('school_id') == $id) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Health Facility</label>
                            @php $hfs = \App\Models\HealthFacility::pluck('name','id'); @endphp
                            <select name="health_facility_id" class="form-select form-control">
                                <option value="">-- none --</option>
                                @foreach($hfs as $id => $label)
                                    <option value="{{ $id }}" @if(old('health_facility_id') == $id) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Profile Image</label>
                            <input name="file_url" type="file" accept="image/*" class="form-control">
                            @error('file_url')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Meeting Slug</label>
                            <input name="meeting_slug" class="form-control" value="{{ old('meeting_slug', $generatedSlug ?? '') }}" readonly>

                            <label class="form-label small mt-2">Meeting URL</label>
                            <div class="input-group">
                                <input id="modal-meeting-url" type="text" class="form-control" value="{{ 'https://meet.jit.si/' . (old('meeting_slug', $generatedSlug ?? '')) }}" readonly>
                                <button type="button" id="copy-modal-meeting-url" class="btn btn-outline-secondary btn-sm">Copy</button>
                            </div>
                            <div id="modal-copy-feedback" class="small text-success mt-1" style="display:none">Copied to clipboard</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary btn-sm">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const searchInput = document.getElementById('admin-search');
    const specializationFilter = document.getElementById('specialization-filter');
    const cards = Array.from(document.querySelectorAll('#admin-doctors-cards .doctor-card'));

    function filterDoctors() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        const selectedSpec = (specializationFilter?.value || '').toLowerCase();

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const email = card.getAttribute('data-email') || '';
            const specialization = card.getAttribute('data-specialization') || '';

            const matchesSearch = !q || name.includes(q) || email.includes(q) || specialization.includes(q);
            const matchesSpec = !selectedSpec || specialization === selectedSpec;

            card.style.display = (matchesSearch && matchesSpec) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterDoctors);
    if (specializationFilter) specializationFilter.addEventListener('change', filterDoctors);
});
</script>
@endpush
