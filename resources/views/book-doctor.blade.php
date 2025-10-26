@extends('layouts.base')


@section('content')
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">Doctor Appointments</h2>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newAppointmentModal"><i class="mdi mdi-stethoscope"></i> Book Doctor</button>

                    </div>
                </div>
                <!-- Modal and form remain unchanged -->
                <div>
                    @if($appointments->count() > 0)
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-hover align-middle text-dark">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Student</th>
                                        <th>Doctor</th>
                                        <th>Duration</th>
                                        <th>Amount</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->appointment_time->format('M d, Y h:i A') }}</td>
                                        <td>{{ $appointment->patient->name }}</td>
                                        <td>Dr. {{ $appointment->doctor->name }}</td>
                                        <td>{{ $appointment->duration ? $appointment->duration->minutes . ' mins' : '—' }}</td>
                                        <td>{{ $appointment->duration ? number_format($appointment->duration->getPriceForDoctor($appointment->doctor)) . ' UGX' : '—' }}</td>
                                        <td>{{ $appointment->reason }}</td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $appointment->status == 'confirmed' ? 'success' : 
                                                ($appointment->status == 'pending_payment' ? 'warning' : 'danger') 
                                            }} text-white">
                                                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($appointment->status === 'awaiting_payment')
                                                <a href="{{ route('payment.appointment.pay', $appointment) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-credit-card me-1"></i> Pay
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
                        <div class="alert alert-info text-dark">
                            No appointments found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
            <div class="modal fade" id="newAppointmentModal" tabindex="-1" aria-labelledby="newAppointmentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="newAppointmentModalLabel">New Doctor Appointment</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- Error messages container -->
                            <div id="appointment-errors" class="alert alert-danger" style="display: none;"></div>
                            <div id="appointment-success" class="alert alert-success" style="display: none;"></div>
                            
                            <form id="appointment-form" action="{{ route('appointments.store') }}" method="POST">
    @csrf
    <input type="hidden" name="school_id" value="{{ $school->id }}">

    <div class="mb-3">
        <label for="patient_id" class="form-label">Student</label>
        <select id="patient_id" class="form-control form-select" name="patient_id" required>
            <option value="">Select student</option>
            @foreach($patients as $patient)
            <option value="{{ $patient->id }}">{{ $patient->name }}</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="patient_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="appointment_time" class="form-label">Appointment Time</label>
        <input id="appointment_time" type="datetime-local" class="form-control" name="appointment_time" required>
        <div class="invalid-feedback" id="appointment_time-error"></div>
    </div>

    <div class="mb-3">
        <label for="duration_id" class="form-label">Duration</label>
        <select id="duration_id" class="form-control form-select" name="duration_id" required>
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
        <div class="invalid-feedback" id="duration_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="doctor_id" class="form-label">Doctor</label>
        <select id="doctor_id" class="form-control form-select" name="doctor_id" required>
            <option value="">Select Doctor</option>
            @foreach($doctors ?? [] as $doc)
                <option value="{{ $doc->id }}">Dr. {{ $doc->name }} ({{ $doc->specialization }})</option>
            @endforeach
        </select>
        <div class="invalid-feedback" id="doctor_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="reason" class="form-label">Reason</label>
        <textarea id="reason" class="form-control" name="reason" required></textarea>
        <div class="invalid-feedback" id="reason-error"></div>
    </div>

    <button type="submit" class="btn btn-primary" id="submit-btn">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
        Book Appointment
    </button>
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
    const appointmentForm = document.getElementById('appointment-form');
    const submitBtn = document.getElementById('submit-btn');
    const submitBtnText = submitBtn.querySelector('span:last-child');
    const spinner = submitBtn.querySelector('.spinner-border');

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

    // Function to show errors
    function showErrors(errors) {
        // Clear previous errors
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        document.querySelectorAll('.form-control').forEach(el => {
            el.classList.remove('is-invalid');
        });

        // Hide success message
        document.getElementById('appointment-success').style.display = 'none';

        // Show error message
        const errorContainer = document.getElementById('appointment-errors');
        if (typeof errors === 'string') {
            errorContainer.textContent = errors;
            errorContainer.style.display = 'block';
        } else if (typeof errors === 'object') {
            let errorMessages = [];
            for (const [field, messages] of Object.entries(errors)) {
                if (Array.isArray(messages)) {
                    messages.forEach(message => errorMessages.push(message));
                } else {
                    errorMessages.push(messages);
                }

                // Show field-specific errors
                const errorElement = document.getElementById(field + '-error');
                if (errorElement) {
                    errorElement.textContent = Array.isArray(messages) ? messages[0] : messages;
                    errorElement.style.display = 'block';
                    const inputElement = document.getElementById(field);
                    if (inputElement) {
                        inputElement.classList.add('is-invalid');
                    }
                }
            }

            if (errorMessages.length > 0) {
                errorContainer.innerHTML = errorMessages.join('<br>');
                errorContainer.style.display = 'block';
            }
        }
    }

    // Function to show success
    function showSuccess(message) {
        // Clear errors
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        document.querySelectorAll('.form-control').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.getElementById('appointment-errors').style.display = 'none';

        // Show success message
        const successContainer = document.getElementById('appointment-success');
        successContainer.textContent = message;
        successContainer.style.display = 'block';

        // Reset form after 2 seconds and close modal
        setTimeout(() => {
            appointmentForm.reset();
            $('#newAppointmentModal').modal('hide');
            successContainer.style.display = 'none';
            // Reload page to show new appointment
            location.reload();
        }, 2000);
    }

    // Function to set loading state
    function setLoading(loading) {
        submitBtn.disabled = loading;
        spinner.style.display = loading ? 'inline-block' : 'none';
        submitBtnText.textContent = loading ? 'Booking...' : 'Book Appointment';
    }

    // Handle form submission via AJAX
    appointmentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear previous messages
        document.getElementById('appointment-errors').style.display = 'none';
        document.getElementById('appointment-success').style.display = 'none';

        // Prepare form data
        const formData = new FormData(appointmentForm);

        // Handle duration selection
        const durationValue = durationSelect.value;
        if (durationValue) {
            const selectedOption = durationSelect.querySelector(`option[value="${durationValue}"]`);
            if (selectedOption) {
                const durationId = selectedOption.getAttribute('data-duration-id');
                const type = selectedOption.getAttribute('data-type');

                // Update form data with correct values
                formData.set('duration_id', durationId);
                formData.append('consultation_type', type);
            }
        }

        setLoading(true);

        // Submit via AJAX
        fetch(appointmentForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            setLoading(false);

            if (data.success) {
                showSuccess(data.message || 'Appointment booked successfully!');
            } else {
                showErrors(data.errors || data.message || 'An error occurred');
            }
        })
        .catch(error => {
            setLoading(false);
            console.error('AJAX Error:', error);
            showErrors('Network error occurred. Please try again.');
        });
    });

    // Filter duration options when doctor is selected
    doctorSelect.addEventListener('change', filterDurationOptions);

    // Initial filter of duration options
    filterDurationOptions();
});
</script>
@endpush