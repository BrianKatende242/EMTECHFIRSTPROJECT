@extends('layouts.base')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar .nav-link { color: rgba(255,255,255,.8); }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.08);
        }
        .tab-content {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
@endpush

@section('content')
    @php($healthFacility = $healthFacility)
    <div class="container-fluid">
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-lg-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"><i class="fa fa-users" aria-hidden="true"></i></div>
                        <div class="stat-sep" aria-hidden="true"></div>
                        <div class="flex-grow-1">
                            <div class="stat-label">Patients</div>
                            <div class="stat-value">{{ data_get($stats, 'patients', 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"><i class="fa fa-calendar" aria-hidden="true"></i></div>
                        <div class="stat-sep" aria-hidden="true"></div>
                        <div class="flex-grow-1">
                            <div class="stat-label">Appointments</div>
                            <div class="stat-value">{{ data_get($stats, 'appointments', 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"><i class="fa fa-user-md" aria-hidden="true"></i></div>
                        <div class="stat-sep" aria-hidden="true"></div>
                        <div class="flex-grow-1">
                            <div class="stat-label">Available Doctors</div>
                            <div class="stat-value">{{ data_get($stats, 'availableDoctors', 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon"><i class="fa fa-envelope" aria-hidden="true"></i></div>
                        <div class="stat-sep" aria-hidden="true"></div>
                        <div class="flex-grow-1">
                            <div class="stat-label">Unread Messages</div>
                            <div class="stat-value">{{ data_get($stats, 'unreadMessages', 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header bg-purple text-white">Weekly Appointments</div>
                    <div class="card-body">
                        <canvas id="hfWeeklyAppointments" height="130"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card mb-3">
                    <div class="card-header">Appointments by Status</div>
                    <div class="card-body">
                        <canvas id="hfApptByStatus" height="200"></canvas>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Patients by Gender</div>
                    <div class="card-body">
                        <canvas id="hfPatientsByGender" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Weekly Appointments chart
        var ctx = document.getElementById('hfWeeklyAppointments').getContext('2d');
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

        // Doughnut: Appointments by Status
        var apptCtx = document.getElementById('hfApptByStatus').getContext('2d');
        new Chart(apptCtx, {
            type: 'doughnut',
            data: {
                labels: @json($appointmentStatusLabels),
                datasets: [{
                    data: @json($appointmentStatusData),
                    backgroundColor: ['#FF00F8', '#6c757d', '#000000', '#e83e8c'],
                    hoverBackgroundColor: ['#e000dc', '#5a6268', '#000000', '#d63384'],
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

        // Doughnut: Patients by Gender
        var genderCtx = document.getElementById('hfPatientsByGender').getContext('2d');
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
    });
    </script>
@endpush
@endsection