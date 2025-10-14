@extends('layouts.base')

@section('content')
<script>
// Prevent Morris.js charts from initializing on this page
// This prevents the "Graph container element not found" error
if (typeof Morris !== 'undefined') {
    Morris.Bar = function() { return {}; };
    Morris.Line = function() { return {}; };
    Morris.Donut = function() { return {}; };
}
</script>
<div class="row">
    <div class="col-12">
                <div class="card">
            <div class="card-header bg-secondary text-white position-relative overflow-hidden">
                
                <div class="d-flex align-items-center mb-2">
                    <div class="me-3" style="width: 60px; height: 60px; border-radius: 50%; margin-right: 1rem;">
                        <div class="avatar-circle bg-white text-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%;">
                            <i class="fa fa-user-md fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-1 fw-bold text-white">
                            <i class="fa fa-calendar-check me-3"></i>
                            Manage Availability
                        </h4>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="opacity-75">
                        <i class="fa fa-clock me-1"></i>
                        Set your weekly availability schedule and appointment limits
                    </small>
                </div>
            </div>
            <div class="card-body">
                <!-- Quick Overview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="text-center mb-3">
                            <i class="fa fa-calendar-week me-2 text-primary"></i>
                            Weekly Availability Overview
                        </h5>
                        <div class="d-flex justify-content-center flex-wrap gap-2">
                            @foreach($days as $day)
                                @php
                                    $availability = $doctor->availabilities->where('day', $day)->first();
                                    $isAvailable = $availability && $availability->available;
                                    $maxAppointments = $availability ? $availability->max_appointments : 0;
                                @endphp
                                <div class="text-center">
                                    <div class="badge fs-6 p-2 text-white {{ $isAvailable ? 'bg-success' : 'bg-secondary' }} mb-1">
                                        <strong>{{ substr($day, 0, 3) }}</strong>
                                    </div>
                                    <br>
                                    <small class="text-muted">{{ $maxAppointments }} slots</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <form id="availabilityForm">
                    @csrf
                    <div class="row">
                        @php
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                        @endphp
                        @foreach($days as $day)
                            @php
                                $availability = $doctor->availabilities->where('day', $day)->first();
                                $isAvailable = $availability && $availability->available;
                                $maxAppointments = $availability ? $availability->max_appointments : 0;
                            @endphp
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card availability-card {{ $isAvailable ? 'border-success' : 'border-secondary' }} h-100">
                                    <div class="card-header {{ $isAvailable ? 'bg-success text-white' : 'bg-light' }} d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fa fa-calendar-day me-2"></i>
                                            <h6 class="mb-0 text-capitalize fw-bold">{{ $day }}</h6>
                                        </div>
                                        <div class="availability-status">
                                            @if($isAvailable)
                                                <span class="badge bg-white text-success">
                                                    <i class="fa fa-check-circle"></i> Available
                                                </span>
                                            @else
                                                <span class="badge bg-secondary text-white">
                                                    <i class="fa fa-times-circle"></i> Unavailable
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Availability Toggle -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="form-label mb-0 fw-semibold">Available for Appointments</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input availability-toggle"
                                                           type="checkbox"
                                                           id="available_{{ $day }}"
                                                           name="days[{{ $day }}][available]"
                                                           value="1"
                                                           {{ $isAvailable ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Max Appointments -->
                                        <div class="mb-3">
                                            <label for="max_appointments_{{ $day }}" class="form-label fw-semibold">
                                                <i class="fa fa-users me-1"></i> Maximum Appointments
                                            </label>
                                            <div class="input-group">
                                                <input type="number"
                                                       class="form-control max-appointments text-center fw-bold {{ $isAvailable ? 'border-success' : '' }}"
                                                       id="max_appointments_{{ $day }}"
                                                       name="days[{{ $day }}][max_appointments]"
                                                       min="0"
                                                       max="50"
                                                       value="{{ $maxAppointments }}"
                                                       {{ $isAvailable ? '' : 'disabled' }}
                                                       placeholder="0">
                                                <span class="input-group-text {{ $isAvailable ? 'bg-success text-white' : '' }}">
                                                    <i class="fa fa-user-md"></i>
                                                </span>
                                            </div>
                                            <small class="text-muted">Set to 0 for unlimited</small>
                                        </div>

                                        <!-- Quick Stats -->
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="p-2 bg-light rounded">
                                                    <div class="fw-bold text-primary">{{ $maxAppointments }}</div>
                                                    <small class="text-muted">Max Slots</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-2 bg-light rounded">
                                                    <div class="fw-bold {{ $isAvailable ? 'text-success' : 'text-secondary' }}">
                                                        {{ $isAvailable ? 'Active' : 'Inactive' }}
                                                    </div>
                                                    <small class="text-muted">Status</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-success btn-lg px-4 py-2 shadow-sm" id="saveAvailability">
                                    <i class="fa fa-save me-2"></i> Save All Availability Settings
                                </button>
                            </div>
                            <div id="saveStatus" class="mt-3 text-center"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Card Header Styles */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
}

.card-header-bg-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image:
        radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(255,255,255,0.05) 0%, transparent 50%);
    pointer-events: none;
}

.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    border: 3px solid rgba(255,255,255,0.3);
}

.availability-indicator {
    position: relative;
}

.availability-indicator::after {
    content: '';
    position: absolute;
    top: -5px;
    right: -5px;
    width: 12px;
    height: 12px;
    background: #28a745;
    border-radius: 50%;
    border: 2px solid white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

.card-title {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
}

.badge {
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Existing styles */
.availability-card {
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 12px;
    overflow: hidden;
}

.availability-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.availability-card.border-success {
    box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.25);
}

.availability-card .card-header {
    transition: all 0.3s ease;
    border-bottom: none;
    padding: 1rem 1.25rem;
}

.availability-status .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
    border-radius: 20px;
}

.max-appointments {
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
}

.max-appointments:disabled {
    background-color: #f8f9fa;
    opacity: 0.6;
}

.input-group-text {
    transition: all 0.3s ease;
    border-radius: 0 8px 8px 0;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.availability-toggle {
    transform: scale(1.2);
}

.btn-success {
    border-radius: 25px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(25, 135, 84, 0.3);
}

.gap-2 {
    gap: 0.5rem !important;
}

@media (max-width: 768px) {
    .availability-card {
        margin-bottom: 1rem;
    }

    .d-flex.justify-content-center.flex-wrap {
        justify-content: space-around !important;
    }

    .card-header .d-flex {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .avatar-circle {
        align-self: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Prevent Morris.js charts from initializing on this page
if (typeof Morris !== 'undefined') {
    Morris.Bar = function() { return {}; };
    Morris.Line = function() { return {}; };
    Morris.Donut = function() { return {}; };
}
</script>
<script>
$(document).ready(function() {
    // Auto-save timeout variable
    let autoSaveTimeout;

    // Function to update card appearance
    function updateCardAppearance(day) {
        const card = $('#available_' + day).closest('.availability-card');
        const header = card.find('.card-header');
        const statusBadge = card.find('.availability-status .badge');
        const maxInput = card.find('.max-appointments');
        const inputGroupText = card.find('.input-group-text');
        const isChecked = $('#available_' + day).is(':checked');

        if (isChecked) {
            card.removeClass('border-secondary').addClass('border-success');
            header.removeClass('bg-light').addClass('bg-success text-white');
            statusBadge.removeClass('bg-secondary text-white').addClass('bg-white text-success')
                      .html('<i class="fa fa-check-circle"></i> Available');
            maxInput.removeClass('border-secondary').addClass('border-success');
            inputGroupText.addClass('bg-success text-white');
        } else {
            card.removeClass('border-success').addClass('border-secondary');
            header.removeClass('bg-success text-white').addClass('bg-light');
            statusBadge.removeClass('bg-white text-success').addClass('bg-secondary text-white')
                      .html('<i class="fa fa-times-circle"></i> Unavailable');
            maxInput.removeClass('border-success').addClass('border-secondary');
            inputGroupText.removeClass('bg-success text-white');
        }
    }

    // Function to update weekly overview badges
    function updateWeeklyOverview() {
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        days.forEach(function(day) {
            const isAvailable = $('#available_' + day).is(':checked');
            const maxAppointments = parseInt($('#max_appointments_' + day).val()) || 0;
            // Find badge by day index instead of text content
            const dayIndex = days.indexOf(day);
            const badgeContainer = $('.d-flex.justify-content-center.flex-wrap .text-center').eq(dayIndex);
            const badge = badgeContainer.find('.badge');
            const slotsText = badgeContainer.find('small');

            if (isAvailable) {
                badge.removeClass('bg-secondary').addClass('bg-success');
            } else {
                badge.removeClass('bg-success').addClass('bg-secondary');
            }
            slotsText.text(maxAppointments + ' slots');
        });
    }

    // Handle availability toggle changes
    $('.availability-toggle').change(function() {
        const day = $(this).attr('id').replace('available_', '');
        const isChecked = $(this).is(':checked');
        const maxAppointmentsInput = $('#max_appointments_' + day);

        // Smooth transition for input field
        if (isChecked) {
            maxAppointmentsInput.prop('disabled', false).fadeTo(200, 1);
            // Set a default value if it's 0
            if (maxAppointmentsInput.val() == '0') {
                maxAppointmentsInput.val('10'); // Default to 10 appointments
            }
        } else {
            maxAppointmentsInput.prop('disabled', true).fadeTo(200, 0.6);
            maxAppointmentsInput.val('0');
        }

        // Update card appearance immediately
        updateCardAppearance(day);

        // Update weekly overview
        updateWeeklyOverview();

        // Auto-save after a short delay for toggles
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            $('#availabilityForm').submit();
        }, 500);
    });

    // Initialize card appearances and weekly overview on page load
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    days.forEach(function(day) {
        updateCardAppearance(day);
    });
    updateWeeklyOverview();

    // Handle form submission
    $('#availabilityForm').submit(function(e) {
        e.preventDefault();

        const saveButton = $('#saveAvailability');
        const saveStatus = $('#saveStatus');

        // Check if CSRF token is available
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (!csrfToken) {
            saveStatus.html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle me-2"></i>CSRF token not found. Please refresh the page.</div>');
            return;
        }

        // Disable button and show loading
        saveButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        saveStatus.html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin me-2"></i>Saving availability...</div>');

        // Build data object manually to ensure all days are included
        const data = {
            _token: csrfToken,
            days: {}
        };

        days.forEach(function(day) {
            const isAvailable = $('#available_' + day).is(':checked');
            const maxAppointments = parseInt($('#max_appointments_' + day).val()) || 0;

            data.days[day] = {
                available: isAvailable ? 1 : 0,
                max_appointments: maxAppointments
            };
        });

        $.ajax({
            url: '{{ route("doctor.update-availability", $doctor) }}',
            method: 'POST',
            data: data,
            beforeSend: function() {
            },
            success: function(response) {
                saveStatus.html('<div class="alert alert-success"><i class="fa fa-check-circle me-2"></i>Availability updated successfully!</div>');
                saveButton.prop('disabled', false).html('<i class="fa fa-save"></i> Save Availability');

                // Clear status after 3 seconds
                setTimeout(function() {
                    saveStatus.html('');
                }, 3000);
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred while saving availability.';

                if (xhr.status === 419) {
                    errorMessage = 'Session expired. Please refresh the page and try again.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }

                saveStatus.html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle me-2"></i>' + errorMessage + '</div>');
                saveButton.prop('disabled', false).html('<i class="fa fa-save"></i> Save All Availability Settings');

                // If CSRF token mismatch, try to refresh the page
                if (xhr.status === 419) {
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            }
        });
    });

    // Auto-save functionality (optional) - only for max appointments changes
    $('.max-appointments').on('input change', function() {
        clearTimeout(autoSaveTimeout);
        // Update weekly overview immediately when max appointments change
        updateWeeklyOverview();
        // Auto-save after 2 seconds of inactivity
        autoSaveTimeout = setTimeout(function() {
            $('#availabilityForm').submit();
        }, 2000);
    });

    // Add smooth animations for max appointments input
    $('.max-appointments').on('focus', function() {
        $(this).addClass('border-primary');
    }).on('blur', function() {
        $(this).removeClass('border-primary');
    });
});
</script>
@endpush
