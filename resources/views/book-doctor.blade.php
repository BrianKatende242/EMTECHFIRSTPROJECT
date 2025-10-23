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
                            <form action="{{ route('appointments.store') }}" method="POST">
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
        @error('patient_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="appointment_time" class="form-label">Appointment Time</label>
        <input id="appointment_time" type="datetime-local" class="form-control" name="appointment_time" required>
        @error('appointment_time')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
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
        @error('duration_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="doctor_id" class="form-label">Doctor</label>
        <select id="doctor_id" class="form-control form-select" name="doctor_id" required>
            <option value="">Select Date First</option>
            @foreach($doctors ?? [] as $doc)
                <option value="{{ $doc->id }}" data-specialization="{{ $doc->specialization }}" style="display: none;">Dr. {{ $doc->name }} ({{ $doc->specialization }})</option>
            @endforeach
        </select>
        @error('doctor_id')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="reason" class="form-label">Reason</label>
        <textarea id="reason" class="form-control" name="reason" required></textarea>
        @error('reason')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Book Appointment</button>
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
    const doctorOptions = doctorSelect.querySelectorAll('option[data-specialization]');
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

    appointmentTimeInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        if (!selectedDate || isNaN(selectedDate.getTime())) {
            // Reset doctor options
            doctorOptions.forEach(option => {
                option.style.display = 'none';
            });
            doctorSelect.value = '';
            filterDurationOptions(); // Also reset duration options
            return;
        }

        const dayOfWeek = selectedDate.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();

        // Fetch available doctors for this day
        fetch(`/api/doctors/available?day=${dayOfWeek}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const availableDoctorIds = data.doctors.map(doctor => doctor.id.toString());

                    doctorOptions.forEach(option => {
                        if (availableDoctorIds.includes(option.value)) {
                            option.style.display = 'block';
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Reset selection if current selection is not available
                    if (doctorSelect.value && !availableDoctorIds.includes(doctorSelect.value)) {
                        doctorSelect.value = '';
                    }

                    // Filter duration options after doctor availability is loaded
                    filterDurationOptions();
                }
            })
            .catch(error => {
                console.error('Error fetching available doctors:', error);
            });
    });

    // Handle form submission to extract duration_id and type
    const appointmentForm = document.querySelector('form[action*="appointments.store"]');
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