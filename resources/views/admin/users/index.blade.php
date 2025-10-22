@extends('layouts.base')

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-white">
                            <h1 class="h3 mb-1 fw-bold">Company Admins Management</h1>
                            <p class="mb-0 opacity-85">Manage company administrators and their access</p>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#inviteAdminModal">
                                <i class="fa fa-envelope me-2"></i>Invite
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-md-6 col-sm-12 mb-2">
            <input type="text" id="admin-search" class="form-control" placeholder="Search by name or email...">
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Admins Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fa fa-users me-2"></i>Admins List
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="admins-table">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">#</th>
                            <th class="border-0">Admin</th>
                            <th class="border-0">Email</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Created</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $admin)
                        <tr data-name="{{ strtolower($admin->name ?? '') }}"
                            data-email="{{ strtolower($admin->email ?? '') }}">
                            <td>
                                <strong>{{ $admin->id }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 bg-success text-white d-flex align-items-center justify-content-center">
                                        {{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $admin->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($admin->email)
                                    <i class="fa fa-envelope text-primary me-1"></i>
                                    <a href="mailto:{{ $admin->email }}" class="text-decoration-none">{{ $admin->email }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-success text-white">
                                    <i class="fa fa-circle me-1" style="font-size: 0.5rem;"></i>Active
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ optional($admin->created_at)->format('M j, Y') ?? '-' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.model.edit', ['users', $admin->id]) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="More Actions">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <form action="{{ route('admin.model.destroy', ['users', $admin->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit" onclick="return confirm('Delete this admin?')">
                                                        <i class="fa fa-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No admins found</h5>
                                <p class="text-muted">Invite new admins to get started.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">{{ $items->links() }}</div>
</div>

<!-- Invite Admin Modal -->
<div class="modal fade" id="inviteAdminModal" tabindex="-1" role="dialog" aria-labelledby="inviteAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inviteAdminModalLabel">Invite Admin</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.send-invite') }}">
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

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input name="email" type="email" class="form-control" required placeholder="admin@example.com" value="{{ old('email') }}">
                        @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                        <div class="form-text">An invitation link will be sent to this email address.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary btn-sm">Send Invite</button>
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
    const tableRows = Array.from(document.querySelectorAll('#admins-table tbody tr'));

    function filterAdmins() {
        const q = (searchInput?.value || '').trim().toLowerCase();

        tableRows.forEach(row => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) return;

            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';

            const matchesSearch = !q || name.includes(q) || email.includes(q);

            row.style.display = matchesSearch ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterAdmins);
});
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 1rem;
    font-weight: bold;
    border: 2px solid #e9ecef;
    margin-right: 1.5rem !important;
}

.table-responsive {
    border-radius: 0.375rem;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    vertical-align: middle;
}

.table tbody td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

.btn-group .btn {
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }

    .avatar-circle {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endpush