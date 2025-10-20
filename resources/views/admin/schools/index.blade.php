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
                            <h1 class="h3 mb-1 fw-bold text-white">Schools Management</h1>
                            <p class="mb-0 opacity-85">Manage school information and records</p>
                        </div>
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createSchoolModal">
                                <i class="fa fa-plus me-3"></i> <span>Add School</span>
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
            <input type="text" id="admin-search" class="form-control" placeholder="Search by name, email, or contact...">
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="sort-filter" class="form-select form-control">
                <option value="">Sort by...</option>
                <option value="name_asc">Name (A-Z)</option>
                <option value="name_desc">Name (Z-A)</option>
                <option value="student_count_desc">Most Students</option>
                <option value="student_count_asc">Least Students</option>
                <option value="created_at_desc">Newest First</option>
                <option value="created_at_asc">Oldest First</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <button type="button" id="clear-filters" class="btn btn-outline-secondary w-100">Clear</button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Schools Cards Grid -->
    <div class="row g-4" id="admin-schools-cards">
        @forelse($items as $school)
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 school-card"
             data-name="{{ strtolower($school->name ?? '') }}"
             data-email="{{ strtolower($school->email ?? '') }}"
             data-contact="{{ strtolower($school->contact ?? '') }}">
            <div class="card h-100 shadow-sm border-0 school-card">
                <!-- Card Header with School Info -->
                <div class="card-header bg-gradient-primary text-white position-relative school-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold text-white" style="font-size: 1rem;">{{ $school->name }}</h6>
                            <small class="opacity-85">{{ $school->students->count() }} students enrolled</small>
                        </div>
                        <!-- Status Badge -->
                        <div class="ms-2">
                            <span class="badge bg-success rounded-pill px-2 py-1 school-status-badge">
                                <i class="fa fa-check-circle me-1"></i>Active
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
                            @if($school->email)
                            <div class="col-12">
                                <div class="d-flex align-items-center p-2 rounded school-contact-section">
                                    <i class="fa fa-envelope text-primary me-2" style="width: 16px;"></i>
                                    <a href="mailto:{{ $school->email }}" class="text-decoration-none small text-dark fw-medium">{{ $school->email }}</a>
                                </div>
                            </div>
                            @endif

                            @if($school->contact)
                            <div class="col-12">
                                <div class="d-flex align-items-center p-2 rounded school-contact-section">
                                    <i class="fa fa-phone text-success me-2" style="width: 16px;"></i>
                                    <a href="tel:{{ $school->contact }}" class="text-decoration-none small text-dark fw-medium">{{ $school->contact }}</a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-chart-bar me-2"></i>Statistics
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-2 rounded school-stats-section text-center">
                                    <div class="fw-bold text-primary">{{ $school->students->count() }}</div>
                                    <small class="text-muted">Students</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded school-stats-section text-center">
                                    <div class="fw-bold text-info">{{ $school->doctors->count() }}</div>
                                    <small class="text-muted">Doctors</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    @if($school->address)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-map-marker-alt me-2"></i>Location
                        </h6>
                        <div class="p-2 rounded school-address-section">
                            <div class="small text-dark fw-medium">{{ $school->address }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- Metadata -->
                    <div class="pt-2 border-top border-light">
                        <small class="text-muted d-block">
                            <i class="fa fa-calendar-plus me-2"></i>
                            Created {{ optional($school->created_at)->format('M j, Y') ?? '-' }}
                        </small>
                        @if($school->updated_at && $school->updated_at != $school->created_at)
                        <small class="text-muted d-block">
                            <i class="fa fa-edit me-2"></i>
                            Updated {{ optional($school->updated_at)->format('M j, Y') ?? '-' }}
                        </small>
                        @endif
                    </div>
                </div>

                <!-- Enhanced Card Footer -->
                <div class="card-footer bg-white border-0 p-3">
                    <div class="row g-2">
                        <div class="col-auto">
                            <a href="{{ route('admin.model.edit', ['schools', $school->id]) }}" class="btn btn-outline-info btn-sm fw-semibold px-3 school-card-btn" title="View/Edit" style="border-radius: 8px;">
                                <i class="fa fa-eye me-1"></i>View
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.model.edit', ['schools', $school->id]) }}" class="btn btn-outline-secondary btn-sm fw-semibold px-3 school-card-btn" title="Edit" style="border-radius: 8px;">
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
                                        <a href="{{ route('admin.model.edit', ['schools', $school->id]) }}" class="dropdown-item" target="_blank">
                                            <i class="fa fa-external-link me-2 text-info"></i>View Full Details
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.model.destroy', ['schools', $school->id]) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit" onclick="return confirm('Delete this school?')" style="padding: 0.5rem 1rem;">
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
                    <i class="fa fa-school fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No schools found</h5>
                    <p class="text-muted">Try adjusting your filters or add a new school.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">{{ $items->appends(request()->query())->links() }}</div>
</div>

<!-- Create School Modal (Placeholder) -->
<div class="modal fade doctors-modal" id="createSchoolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New School</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">School creation form would go here. For now, use the generic form.</p>
                <a href="{{ route('admin.schools.create') }}" class="btn btn-primary btn-sm">Go to Create Form</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const searchInput = document.getElementById('admin-search');
    const sortFilter = document.getElementById('sort-filter');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const cards = Array.from(document.querySelectorAll('#admin-schools-cards .school-card'));

    function filterSchools() {
        const q = (searchInput?.value || '').trim().toLowerCase();

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const email = card.getAttribute('data-email') || '';
            const contact = card.getAttribute('data-contact') || '';

            const matchesSearch = !q || name.includes(q) || email.includes(q) || contact.includes(q);

            card.style.display = matchesSearch ? '' : 'none';
        });
    }

    function sortSchools() {
        const sortValue = sortFilter?.value || '';
        if (!sortValue) return;

        const container = document.getElementById('admin-schools-cards');
        const cardsArray = Array.from(cards);

        cardsArray.sort((a, b) => {
            let aVal, bVal;

            switch(sortValue) {
                case 'name_asc':
                    aVal = a.getAttribute('data-name') || '';
                    bVal = b.getAttribute('data-name') || '';
                    return aVal.localeCompare(bVal);
                case 'name_desc':
                    aVal = a.getAttribute('data-name') || '';
                    bVal = b.getAttribute('data-name') || '';
                    return bVal.localeCompare(aVal);
                case 'student_count_asc':
                    aVal = parseInt(a.querySelector('.fw-bold.text-primary')?.textContent || '0');
                    bVal = parseInt(b.querySelector('.fw-bold.text-primary')?.textContent || '0');
                    return aVal - bVal;
                case 'student_count_desc':
                    aVal = parseInt(a.querySelector('.fw-bold.text-primary')?.textContent || '0');
                    bVal = parseInt(b.querySelector('.fw-bold.text-primary')?.textContent || '0');
                    return bVal - aVal;
                default:
                    return 0;
            }
        });

        // Re-append sorted cards
        cardsArray.forEach(card => container.appendChild(card));
    }

    function clearFilters() {
        if (searchInput) searchInput.value = '';
        if (sortFilter) sortFilter.value = '';
        filterSchools();
        // Reset to original order (you might want to store original order)
    }

    if (searchInput) searchInput.addEventListener('input', filterSchools);
    if (sortFilter) sortFilter.addEventListener('change', function() {
        filterSchools();
        sortSchools();
    });
    if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', clearFilters);
});
</script>
@endpush