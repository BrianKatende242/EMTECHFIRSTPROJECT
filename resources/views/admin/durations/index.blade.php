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
                            <h1 class="h3 mb-1 fw-bold text-white">Durations Management</h1>
                            <p class="mb-0 opacity-85">Manage appointment duration settings and pricing</p>
                        </div>
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#createDurationModal">
                                <i class="mdi mdi-plus me-3"></i> <span>Add Duration</span>
                            </button>
                            <form action="{{ route('admin.durations.seed') }}" method="POST" class="d-inline" id="seedForm">
                                @csrf
                                <button type="submit" class="btn btn-info btn-sm" id="seedDurationsBtn">
                                    <i class="mdi mdi-seed me-2"></i>
                                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                    <span>Seed Defaults</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-12 mb-2">
            <input type="text" id="admin-search" class="form-control" placeholder="Search by minutes or prices...">
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="status-filter" class="form-select form-control">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="sort-filter" class="form-select form-control">
                <option value="">Sort by...</option>
                <option value="minutes_asc">Minutes (Low to High)</option>
                <option value="minutes_desc">Minutes (High to Low)</option>
                <option value="general_price_asc">General Price (Low to High)</option>
                <option value="general_price_desc">General Price (High to Low)</option>
                <option value="specialist_price_asc">Specialist Price (Low to High)</option>
                <option value="specialist_price_desc">Specialist Price (High to Low)</option>
                <option value="created_at_desc">Newest First</option>
                <option value="created_at_asc">Oldest First</option>
            </select>
        </div>
        <div class="col-md-3 col-sm-12 mb-2">
            <button type="button" id="clear-filters" class="btn btn-outline-secondary w-100">Clear</button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-dismiss="alert"></button>
        </div>
    @endif

    <!-- Durations Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="mdi mdi-timer me-2"></i>Durations List
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="durations-table">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">#</th>
                            <th class="border-0">Duration</th>
                            <th class="border-0">General Price</th>
                            <th class="border-0">Specialist Price</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Created</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr data-minutes="{{ $item->minutes }}"
                            data-general-price="{{ $item->general_price }}"
                            data-specialist-price="{{ $item->specialist_price }}"
                            data-is-active="{{ $item->is_active ? '1' : '0' }}">
                            <td>
                                <strong>{{ $item->id }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 bg-primary text-white d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-timer"></i>
                                    </div>
                                    <div class="fw-bold">{{ $item->minutes }} minutes</div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-success">UGX {{ number_format($item->general_price, 0) }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-warning">UGX {{ number_format($item->specialist_price, 0) }}</div>
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success text-white">
                                        <i class="fa fa-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary text-white">
                                        <i class="fa fa-pause-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ optional($item->created_at)->format('M j, Y') ?? '-' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.durations.edit', $item->id) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.durations.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit" onclick="return confirm('Delete this duration?')" title="Delete">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="mdi mdi-timer fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No durations found</h5>
                                <p class="text-muted">Try adjusting your filters or add a new duration.</p>
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

<!-- Create Duration Modal -->
<div class="modal fade doctors-modal" id="createDurationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Duration</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.durations.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="modal_minutes">Minutes <span class="text-danger">*</span></label>
                                <input type="number" name="minutes" id="modal_minutes" class="form-control @error('minutes') is-invalid @enderror"
                                       value="{{ old('minutes') }}" min="1" required>
                                @error('minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modal_general_price">General Price (UGX) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">UGX</span>
                                    </div>
                                    <input type="number" name="general_price" id="modal_general_price" class="form-control @error('general_price') is-invalid @enderror"
                                           value="{{ old('general_price') }}" step="1" min="0" required>
                                </div>
                                @error('general_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modal_specialist_price">Specialist Price (UGX) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">UGX</span>
                                    </div>
                                    <input type="number" name="specialist_price" id="modal_specialist_price" class="form-control @error('specialist_price') is-invalid @enderror"
                                           value="{{ old('specialist_price') }}" step="1" min="0" required>
                                </div>
                                @error('specialist_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="modal_is_active" checked>
                            <label class="custom-control-label" for="modal_is_active">Active</label>
                        </div>
                        <small class="form-text text-muted">Inactive durations won't be available for new appointments</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Duration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const searchInput = document.getElementById('admin-search');
    const statusFilter = document.getElementById('status-filter');
    const sortFilter = document.getElementById('sort-filter');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const tableRows = Array.from(document.querySelectorAll('#durations-table tbody tr'));

    // Seed Durations Functionality
    const seedForm = document.getElementById('seedForm');
    const seedDurationsBtn = document.getElementById('seedDurationsBtn');

    if (seedForm && seedDurationsBtn) {
        seedForm.addEventListener('submit', function(e) {
            const spinner = seedDurationsBtn.querySelector('.spinner-border');
            const icon = seedDurationsBtn.querySelector('.mdi-seed');
            const text = seedDurationsBtn.querySelector('span:not(.spinner-border)');

            // Show spinner and disable button
            spinner.classList.remove('d-none');
            icon.classList.add('d-none');
            text.textContent = 'Seeding...';
            seedDurationsBtn.disabled = true;

            // Let the form submit normally - the spinner will show during the request
            // The page will reload with success/error message from the controller
        });
    }

    function filterDurations() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        const status = statusFilter?.value || '';

        tableRows.forEach(row => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) return;

            const minutes = row.getAttribute('data-minutes') || '';
            const generalPrice = row.getAttribute('data-general-price') || '';
            const specialistPrice = row.getAttribute('data-specialist-price') || '';
            const isActive = row.getAttribute('data-is-active') || '';

            const matchesSearch = !q ||
                minutes.includes(q) ||
                generalPrice.includes(q) ||
                specialistPrice.includes(q);

            const matchesStatus = !status || isActive === status;

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    function sortDurations() {
        const sortValue = sortFilter?.value || '';
        if (!sortValue) return;

        const tbody = document.querySelector('#durations-table tbody');
        const rowsArray = Array.from(tableRows).filter(row => !row.querySelector('td[colspan]'));

        rowsArray.sort((a, b) => {
            let aVal, bVal;

            switch(sortValue) {
                case 'minutes_asc':
                    aVal = parseInt(a.getAttribute('data-minutes') || '0');
                    bVal = parseInt(b.getAttribute('data-minutes') || '0');
                    return aVal - bVal;
                case 'minutes_desc':
                    aVal = parseInt(a.getAttribute('data-minutes') || '0');
                    bVal = parseInt(b.getAttribute('data-minutes') || '0');
                    return bVal - aVal;
                case 'general_price_asc':
                    aVal = parseFloat(a.getAttribute('data-general-price') || '0');
                    bVal = parseFloat(b.getAttribute('data-general-price') || '0');
                    return aVal - bVal;
                case 'general_price_desc':
                    aVal = parseFloat(a.getAttribute('data-general-price') || '0');
                    bVal = parseFloat(b.getAttribute('data-general-price') || '0');
                    return bVal - aVal;
                case 'specialist_price_asc':
                    aVal = parseFloat(a.getAttribute('data-specialist-price') || '0');
                    bVal = parseFloat(b.getAttribute('data-specialist-price') || '0');
                    return aVal - bVal;
                case 'specialist_price_desc':
                    aVal = parseFloat(a.getAttribute('data-specialist-price') || '0');
                    bVal = parseFloat(b.getAttribute('data-specialist-price') || '0');
                    return bVal - aVal;
                case 'created_at_desc':
                    // For simplicity, assume newer items have higher IDs
                    aVal = parseInt(a.querySelector('strong')?.textContent || '0');
                    bVal = parseInt(b.querySelector('strong')?.textContent || '0');
                    return bVal - aVal;
                case 'created_at_asc':
                    aVal = parseInt(a.querySelector('strong')?.textContent || '0');
                    bVal = parseInt(b.querySelector('strong')?.textContent || '0');
                    return aVal - bVal;
                default:
                    return 0;
            }
        });

        // Re-append sorted rows
        rowsArray.forEach(row => tbody.appendChild(row));
    }

    function clearFilters() {
        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = '';
        if (sortFilter) sortFilter.value = '';
        filterDurations();
        // Reset to original order
    }

    if (searchInput) searchInput.addEventListener('input', filterDurations);
    if (statusFilter) statusFilter.addEventListener('change', filterDurations);
    if (sortFilter) sortFilter.addEventListener('change', function() {
        filterDurations();
        sortDurations();
    });
    if (clearFiltersBtn) clearFiltersBtn.addEventListener('click', clearFilters);
});
</script>
@endpush