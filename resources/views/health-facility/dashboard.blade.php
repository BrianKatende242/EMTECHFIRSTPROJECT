@extends('layouts.base')

@section('content')
    <div class="row g-3 g-md-4 mb-4">
        <!-- Patients Card -->
         <div class="col-lg-3 col-sm-6 mb-2">
            <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between justify-content-md-center justify-content-xl-between flex-wrap">
                    <div>
                      <div class="stat-label mb-2">Patients</div>
                      <h5 class="mb-0">{{ data_get($stats, 'patients', 0) }}</h5>
                    </div>
                    <i class="mdi mdi-account-multiple icon-xl text-primary"></i>
                  </div>
                </div>
            </div>
        </div>

        <!-- Appointments Card -->
        <div class="col-lg-3 col-sm-6 mb-2">
            <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between justify-content-md-center justify-content-xl-between flex-wrap">
                    <div>
                      <div class="stat-label mb-2">Appointments</div>
                      <h5 class="mb-0">{{ data_get($stats, 'appointments', 0) }}</h5>
                    </div>
                    <i class="mdi mdi-calendar-check icon-xl text-primary"></i>
                  </div>
                </div>
            </div>
        </div>

        <!-- Available Doctors Card -->
        <div class="col-lg-3 col-sm-6 mb-2">
            <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between justify-content-md-center justify-content-xl-between flex-wrap">
                    <div>
                      <div class="stat-label mb-2">Staff</div>
                      <h5 class="mb-0">{{ \App\Models\Doctor::where('health_facility_id', $healthFacility->id)->count() }}</h5>
                    </div>
                    <i class="mdi mdi-account-group icon-xl text-primary"></i>
                  </div>
                </div>
            </div>
        </div>

        <!-- Spending Card -->
         <div class="col-lg-3 col-sm-6 mb-2">
            <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between justify-content-md-center justify-content-xl-between flex-wrap">
                    <div>
                      <div class="stat-label mb-2">Spending</div>
                      <h5 class="mb-0">UGX {{ number_format(\App\Models\Appointment::where('health_facility_id', $healthFacility->id)->with('duration')->get()->sum(function($appointment) { return $appointment->duration->price ?? 0; }), 0) }}</h5>
                    </div>
                    <i class="mdi mdi-cash-multiple icon-xl text-primary"></i>
                  </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Weekly Appointments</div>
                <div class="card-body">
                    <canvas id="hfWeeklyAppointments" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header" style="background-color: magenta; color: white;">Patients by Gender</div>
                <div class="card-body">
                    <canvas id="hfPatientsByGender" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Awaiting Approval -->
    @php
        $awaitingApprovalAppointments = $appointments->where('status', 'awaiting_approval')->take(5);
    @endphp
    @if($awaitingApprovalAppointments->count() > 0)
    <div class="row g-3 g-md-4 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Appointments Awaiting Approval</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date & Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($awaitingApprovalAppointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->patient->name }}</td>
                                    <td>Dr. {{ $appointment->doctor->name }}</td>
                                    <td>{{ $appointment->appointment_time->format('M j, Y g:i A') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('appointments.approve', $appointment) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($appointments->where('status', 'awaiting_approval')->count() > 5)
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-outline-primary">View All Awaiting Approval</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Weekly Appointments chart
        var ctx = document.getElementById('hfWeeklyAppointments');
        if (ctx) {
            ctx = ctx.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($weeklyLabels),
                    datasets: [
                        {
                            label: 'Appointments',
                            backgroundColor: 'rgba(0,0,0,0.85)',
                            borderColor: '#000',
                            borderWidth: 2,
                            hoverBackgroundColor: '#000',
                            hoverBorderColor: '#593bdb',
                            data: @json($weeklyData)
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
                    }
                }
            });
        }

        // Doughnut: Patients by Gender
        var genderCtx = document.getElementById('hfPatientsByGender');
        if (genderCtx) {
            genderCtx = genderCtx.getContext('2d');
            new Chart(genderCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($genderLabels),
                    datasets: [{
                        data: @json($genderData),
                        backgroundColor: ['#000000', '#FF00F8', '#593bdb', '#adb5bd'],
                        hoverBackgroundColor: ['#000000', '#e000dc', '#4c3ac3', '#9aa0a6'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
    </script>
@endpush
@endsection