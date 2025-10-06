@extends('layouts.base')

@section('content')
    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card stat-students">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fa fa-users" aria-hidden="true"></i>
                    </div>
                    <div class="stat-sep" aria-hidden="true"></div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Students</div>
                        <div class="stat-value">{{ $studentsCount }}</div>
                        <div class="progress mt-2" role="progressbar" aria-label="Students progress" aria-valuenow="{{ $studentsCount }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar w-85"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card stat-appointments">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                    </div>
                    <div class="stat-sep" aria-hidden="true"></div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Appointments</div>
                        <div class="stat-value">{{ $appointmentsCount }}</div>
                        <div class="progress mt-2" role="progressbar" aria-label="Appointments progress" aria-valuenow="{{ $appointmentsCount }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar w-75"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card stat-labtests">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fa fa-flask" aria-hidden="true"></i>
                    </div>
                    <div class="stat-sep" aria-hidden="true"></div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Lab Tests</div>
                        <div class="stat-value">{{ $labTestsCount }}</div>
                        <div class="progress mt-2" role="progressbar" aria-label="Lab tests progress" aria-valuenow="{{ $labTestsCount }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar w-50"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card stat-card stat-doctors">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fa fa-user-md" aria-hidden="true"></i>
                    </div>
                    <div class="stat-sep" aria-hidden="true"></div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Doctors</div>
                        <div class="stat-value">{{ $doctorsCount }}</div>
                        <div class="progress mt-2" role="progressbar" aria-label="Doctors progress" aria-valuenow="{{ $doctorsCount }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar w-65"></div>
                        </div>
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
            @php
                $pendingLabTests = isset($labTests) ? $labTests->where('status', 'pending')->count() : 0;
                $completedLabTests = isset($labTests) ? $labTests->where('status', 'completed')->count() : 0;
            @endphp
            <div class="card stat-card stat-labtests">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon"><i class="fa fa-flask" aria-hidden="true"></i></div>
                        <div class="stat-sep" aria-hidden="true"></div>
                        <div>
                            <div class="h5 mb-0">Lab Test Summary</div>
                            <small class="text-muted">Pending and completed</small>
                        </div>
                    </div>
                    <div class="simple-stats">
                        <div class="d-flex align-items-center justify-content-between py-2">
                            <div class="d-flex align-items-center">
                                <span class="legend-dot me-2" style="background:#593bdb"></span>
                                <span class="fw-semibold">Completed</span>
                            </div>
                            <div class="stat-value">{{ $completedLabTests }}</div>
                        </div>
                        <hr class="soft-hr my-1">
                        <div class="d-flex align-items-center justify-content-between py-2">
                            <div class="d-flex align-items-center">
                                <span class="legend-dot me-2" style="background: rgba(0,0,0,0.65)"></span>
                                <span class="fw-semibold">Pending</span>
                            </div>
                            <div class="stat-value">{{ $pendingLabTests }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection