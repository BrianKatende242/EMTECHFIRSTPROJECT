@extends('layouts.base')

@section('content')
    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="stat-widget-two card-body">
                    <div class="stat-content">
                        <div class="stat-text">Students</div>
                        <div class="stat-digit">{{ $studentsCount }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-success w-85" role="progressbar" aria-valuenow="{{ $studentsCount }}" aria-valuemin="0" aria-valuemax="100" aria-label="Students progress"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="stat-widget-two card-body">
                    <div class="stat-content">
                        <div class="stat-text">Appointments</div>
                        <div class="stat-digit">{{ $appointmentsCount }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-primary w-75" role="progressbar" aria-valuenow="{{ $appointmentsCount }}" aria-valuemin="0" aria-valuemax="100" aria-label="Appointments progress"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="stat-widget-two card-body">
                    <div class="stat-content">
                        <div class="stat-text">Lab Tests</div>
                        <div class="stat-digit">{{ $labTestsCount }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-warning w-50" role="progressbar" aria-valuenow="{{ $labTestsCount }}" aria-valuemin="0" aria-valuemax="100" aria-label="Lab tests progress"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card">
                <div class="stat-widget-two card-body">
                    <div class="stat-content">
                        <div class="stat-text">Doctors</div>
                        <div class="stat-digit">{{ $doctorsCount }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-danger w-65" role="progressbar" aria-valuenow="{{ $doctorsCount }}" aria-valuemin="0" aria-valuemax="100" aria-label="Doctors progress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">Weekly Activity</div>
                <div class="card-body">
                    <canvas id="weeklyActivityChart" height="120"></canvas>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var ctx = document.getElementById('weeklyActivityChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                            datasets: [
                                {
                                    label: 'Appointments',
                                    backgroundColor: 'rgba(0, 0, 0, 0.85)',
                                    borderColor: '#000000',
                                    borderWidth: 2,
                                    hoverBackgroundColor: '#000000',
                                    hoverBorderColor: '#593bdb',
                                    data: [3, 5, 2, 4, 6, 1, 0] // Replace with dynamic data
                                },
                                {
                                    label: 'Lab Tests',
                                    backgroundColor: '#593bdb',
                                    borderColor: '#593bdb',
                                    borderWidth: 2,
                                    hoverBackgroundColor: 'rgba(89, 59, 219, 0.85)',
                                    hoverBorderColor: '#000000',
                                    data: [2, 3, 1, 2, 4, 0, 0] // Replace with dynamic data
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'top' },
                                title: { display: false }
                            },
                            scales: {
                                x: { stacked: true },
                                y: { stacked: true, beginAtZero: true }
                            }
                        }
                    });
                });
            </script>
        </div>
        <div class="col-lg-4">
            <div class="card text-dark">
                <div class="card-header bg-info">Lab Test Summary</div>
                <div class="card-body">
                    @php
                        $pendingLabTests = isset($labTests) ? $labTests->where('status', 'pending')->count() : 0;
                        $completedLabTests = isset($labTests) ? $labTests->where('status', 'completed')->count() : 0;
                    @endphp
                    <div class="mb-3">
                        <span class="fw-bold">Pending:</span>
                        <span class="badge bg-warning text-dark">{{ $pendingLabTests }}</span>
                    </div>
                    <div>
                        <span class="fw-bold">Completed:</span>
                        <span class="badge bg-success">{{ $completedLabTests }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection