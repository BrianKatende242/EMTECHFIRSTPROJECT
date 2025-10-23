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
                            <h1 class="h3 mb-1 fw-bold text-white">Payments Management</h1>
                            <p class="mb-0 opacity-85">Manage payment transactions and their details</p>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                                <i class="fa fa-plus me-3"></i> <span>Record</span>
                            </button>
                            <button type="button" class="btn btn-secondary outline btn-sm" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                                <i class="mdi mdi-export me-3"></i> <span>Export</span>
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
            <input type="text" id="admin-search" class="form-control" placeholder="Search by reference, phone, or amount...">
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <select id="status-filter" class="form-select form-control">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-12 mb-2">
            <select id="appointment-filter" class="form-select form-control">
                <option value="">All Appointments</option>
                @foreach($appointments as $id => $name)
                    <option value="{{ $id }}">Appointment #{{ $id }} - {{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <input type="date" id="date-from" class="form-control" placeholder="From Date">
        </div>
        <div class="col-md-2 col-sm-12 mb-2">
            <input type="date" id="date-to" class="form-control" placeholder="To Date">
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Payments Cards Grid -->
    <div class="row g-4" id="admin-payments-cards">
        @forelse($items as $payment)
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 payment-card"
             data-reference="{{ strtolower($payment->reference_id ?? '') }}"
             data-phone="{{ strtolower($payment->phone_number ?? '') }}"
             data-amount="{{ $payment->amount }}"
             data-status="{{ $payment->status }}"
             data-appointment="{{ $payment->appointment_id }}">
            <div class="card h-100 shadow-sm border-0 payment-card">
                <!-- Card Header with Status Badge -->
                <div class="card-header bg-gradient-primary text-white position-relative payment-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold text-white" style="font-size: 1rem;">Payment #{{ $payment->id }}</h6>
                            <small class="opacity-85">{{ optional($payment->created_at)->format('M j, Y g:i A') ?? '-' }}</small>
                        </div>
                        <!-- Status Badge -->
                        <div class="ms-2">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    'cancelled' => 'secondary'
                                ];
                                $statusColor = $statusColors[$payment->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} rounded-pill px-2 py-1 payment-status-badge">
                                <i class="fa fa-circle me-1" style="font-size: 0.5rem;"></i>{{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <!-- Amount Section -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-money-bill-wave me-2"></i>Amount
                        </h6>
                        <div class="p-2 rounded payment-amount-section">
                            <div class="fw-bold text-success" style="font-size: 1.2rem;">${{ number_format($payment->amount, 2) }}</div>
                        </div>
                    </div>

                    <!-- Reference & Phone -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-hashtag me-2"></i>Payment Details
                        </h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="p-2 rounded payment-details-section">
                                    <div class="small text-dark fw-medium">Reference: <code>{{ $payment->reference_id }}</code></div>
                                    <div class="small text-dark fw-medium">Phone: {{ $payment->phone_number }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Info -->
                    @if($payment->appointment)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa fa-calendar-check me-2"></i>Related Appointment
                        </h6>
                        <div class="p-2 rounded payment-appointment-section">
                            <div class="small text-dark fw-medium">Appointment #{{ $payment->appointment->id }}</div>
                            @if($payment->appointment->patient)
                                <div class="small text-muted">Patient: {{ $payment->appointment->patient->name }}</div>
                            @endif
                            @if($payment->appointment->doctor)
                                <div class="small text-muted">Doctor: {{ $payment->appointment->doctor->name }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Metadata -->
                    <div class="pt-2 border-top border-light">
                        <small class="text-muted d-block">
                            <i class="fa fa-calendar-plus me-2"></i>
                            Created {{ optional($payment->created_at)->format('M j, Y') ?? '-' }}
                        </small>
                        @if($payment->updated_at && $payment->updated_at != $payment->created_at)
                        <small class="text-muted d-block">
                            <i class="fa fa-edit me-2"></i>
                            Updated {{ optional($payment->updated_at)->format('M j, Y') ?? '-' }}
                        </small>
                        @endif
                    </div>
                </div>

                <!-- Enhanced Card Footer -->
                <div class="card-footer bg-white border-0 p-3">
                    <div class="row g-2">
                        <div class="col-auto">
                            <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-outline-info btn-sm fw-semibold px-3 payment-card-btn" title="View/Edit" style="border-radius: 8px;">
                                <i class="fa fa-eye me-1"></i>View
                            </a>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn btn-outline-secondary btn-sm fw-semibold px-3 payment-card-btn" title="Edit" style="border-radius: 8px;">
                                <i class="fa fa-edit me-1"></i>Edit
                            </a>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle fw-semibold px-3" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    @if($payment->status !== 'completed')
                                    <li>
                                        <form action="{{ route('admin.payments.bulk') }}" method="POST" style="display:inline-block">
                                            @csrf
                                            <input type="hidden" name="action" value="complete">
                                            <input type="hidden" name="ids[]" value="{{ $payment->id }}">
                                            <button class="dropdown-item" type="submit" style="padding: 0.5rem 1rem;">
                                                <i class="fa fa-check me-2 text-success"></i>Mark Completed
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                    @if($payment->status !== 'failed')
                                    <li>
                                        <form action="{{ route('admin.payments.bulk') }}" method="POST" style="display:inline-block">
                                            @csrf
                                            <input type="hidden" name="action" value="fail">
                                            <input type="hidden" name="ids[]" value="{{ $payment->id }}">
                                            <button class="dropdown-item" type="submit" style="padding: 0.5rem 1rem;">
                                                <i class="fa fa-times me-2 text-danger"></i>Mark Failed
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit" onclick="return confirm('Delete this payment?')" style="padding: 0.5rem 1rem;">
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
                    <i class="fa fa-credit-card fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No payments found</h5>
                    <p class="text-muted">Try adjusting your filters or record a new payment.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">{{ $items->appends(request()->query())->links() }}</div>
</div>

<!-- Create Payment Modal (Placeholder) -->
<div class="modal fade doctors-modal" id="createPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record New Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Payment creation form would go here. For now, use the generic form.</p>
                <a href="{{ route('admin.payments.create') }}" class="btn btn-primary btn-sm">Go to Create Form</a>
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
    const appointmentFilter = document.getElementById('appointment-filter');
    const dateFromInput = document.getElementById('date-from');
    const dateToInput = document.getElementById('date-to');
    const cards = Array.from(document.querySelectorAll('#admin-payments-cards .payment-card'));

    function filterPayments() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        const selectedStatus = (statusFilter?.value || '').toLowerCase();
        const selectedAppointment = appointmentFilter?.value || '';
        const dateFrom = dateFromInput?.value || '';
        const dateTo = dateToInput?.value || '';

        cards.forEach(card => {
            const reference = card.getAttribute('data-reference') || '';
            const phone = card.getAttribute('data-phone') || '';
            const amount = card.getAttribute('data-amount') || '';
            const status = card.getAttribute('data-status') || '';
            const appointment = card.getAttribute('data-appointment') || '';

            const matchesSearch = !q || reference.includes(q) || phone.includes(q) || amount.includes(q);
            const matchesStatus = !selectedStatus || status === selectedStatus;
            const matchesAppointment = !selectedAppointment || appointment === selectedAppointment;

            card.style.display = (matchesSearch && matchesStatus && matchesAppointment) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterPayments);
    if (statusFilter) statusFilter.addEventListener('change', filterPayments);
    if (appointmentFilter) appointmentFilter.addEventListener('change', filterPayments);
    if (dateFromInput) dateFromInput.addEventListener('change', filterPayments);
    if (dateToInput) dateToInput.addEventListener('change', filterPayments);
});
</script>
@endpush