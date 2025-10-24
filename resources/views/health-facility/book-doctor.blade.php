@extends('layouts.base')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="m-0">Book Doctor</h3>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#bookDoctorModal">
                <i class="mdi mdi-calendar-plus me-2"></i> New Appointment
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-light text-dark">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Recent Appointments</h5>
            @if(isset($appointments) && $appointments->count())
            <div class="table-responsive">
                <table class="table table-striped text-dark">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appt)
                        <tr>
                            <td>{{ $appt->id }}</td>
                            <td>{{ optional($appt->patient)->name ?? '—' }}</td>
                            <td>{{ optional($appt->doctor)->name ? 'Dr. ' . $appt->doctor->name : '—' }}</td>
                            <td>{{ optional($appt->appointment_time)->format('D, M j, Y g:i A') }}</td>
                            <td>{{ $appt->duration ? $appt->duration->minutes . ' mins' : '—' }}</td>
                            <td>{{ $appt->duration ? number_format($appt->duration->getPriceForDoctor($appt->doctor), 0) . ' UGX' : '—' }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $appt->status == 'confirmed' ? 'success' : 
                                    ($appt->status == 'awaiting_payment' ? 'warning' : 'secondary') 
                                }} text-white">
                                    {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($appt->status === 'awaiting_payment')
                                    <a href="{{ route('payment.appointment.pay', $appt) }}" class="btn btn-sm btn-primary">
                                        <i class="mdi mdi-credit-card me-1"></i> Pay
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="alert alert-light text-dark mb-0">No appointments yet.</div>
            @endif
        </div>
    </div>

    {{-- Book Doctor Modal --}}
    <div class="modal fade" id="bookDoctorModal" tabindex="-1" role="dialog" aria-labelledby="bookDoctorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookDoctorModalLabel">Book Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="appointment-form" action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="health_facility_id" value="{{ $healthFacility->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select form-control" required>
                                    <option value="">Select Patient</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date & Time</label>
                                <input type="datetime-local" id="appointment_time" name="appointment_time" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Doctor</label>
                                <select id="doctor_id" name="doctor_id" class="form-select form-control" required>
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" data-specialization="{{ $doc->specialization }}">Dr. {{ $doc->name }} ({{ $doc->specialization }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Duration</label>
                                <select name="duration_id" class="form-select form-control" required>
                                    <option value="">Select Duration</option>
                                    @foreach(\App\Models\Duration::active()->get() as $duration)
                                        <option value="{{ $duration->id }}-general" data-duration-id="{{ $duration->id }}" data-type="general" data-price="{{ $duration->general_price }}">
                                            {{ $duration->minutes }} minutes - General: UGX {{ number_format($duration->general_price, 0) }}
                                        </option>
                                        <option value="{{ $duration->id }}-specialist" data-duration-id="{{ $duration->id }}" data-type="specialist" data-price="{{ $duration->specialist_price }}">
                                            {{ $duration->minutes }} minutes - Specialist: UGX {{ number_format($duration->specialist_price, 0) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Reason</label>
                                <input type="text" name="reason" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const appointmentTimeInput = document.getElementById('appointment_time');
    const doctorSelect = document.getElementById('doctor_id');
    const durationSelect = document.getElementById('duration_id');
    const durationOptions = durationSelect.querySelectorAll('option[data-duration-id]');

    // Function to filter duration options based on selected doctor
    function filterDurationOptions() {
        const selectedDoctorOption = doctorSelect.options[doctorSelect.selectedIndex];
        const doctorSpecialization = selectedDoctorOption ? selectedDoctorOption.getAttribute('data-specialization') : null;

        // Show/hide duration options based on doctor type
        durationOptions.forEach(option => {
            const optionType = option.getAttribute('data-type');
            const isGeneralDoctor = doctorSpecialization && doctorSpecialization.toLowerCase().includes('general practitioner');

            if (optionType === 'general' && isGeneralDoctor) {
                option.style.display = 'block';
            } else if (optionType === 'specialist' && !isGeneralDoctor && doctorSpecialization) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        // Reset duration selection if current selection is not available
        if (durationSelect.value) {
            const selectedOption = durationSelect.querySelector(`option[value="${durationSelect.value}"]`);
            if (selectedOption && selectedOption.style.display === 'none') {
                durationSelect.value = '';
            }
        }
    }

    // Filter duration options when doctor is selected
    doctorSelect.addEventListener('change', filterDurationOptions);

    // Handle form submission to extract duration_id and type
    const appointmentForm = document.getElementById('appointment-form');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            const durationValue = durationSelect.value;
            if (durationValue) {
                const selectedOption = durationSelect.querySelector(`option[value="${durationValue}"]`);
                if (selectedOption) {
                    const durationId = selectedOption.getAttribute('data-duration-id');
                    const type = selectedOption.getAttribute('data-type');

                    // Create hidden inputs for duration_id and consultation_type
                    const durationIdInput = document.createElement('input');
                    durationIdInput.type = 'hidden';
                    durationIdInput.name = 'duration_id';
                    durationIdInput.value = durationId;
                    appointmentForm.appendChild(durationIdInput);

                    const typeInput = document.createElement('input');
                    typeInput.type = 'hidden';
                    typeInput.name = 'consultation_type';
                    typeInput.value = type;
                    appointmentForm.appendChild(typeInput);

                    // Update the original select to have the correct value
                    durationSelect.value = durationId;
                }
            }
        });
    }

    // Initial filter of duration options
    filterDurationOptions();
});
</script>
@endpush