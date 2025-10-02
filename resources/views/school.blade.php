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
                <div class="card-header bg-primary text-white">Recent Appointments</div>
                <div class="card-body">
                    @if(isset($appointments) && count($appointments) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped text-dark">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointments->take(5) as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_time ? $appointment->appointment_time->format('M d, Y h:i A') : '' }}</td>
                                    <td>{{ $appointment->student->name ?? '' }}</td>
                                    <td>{{ $appointment->doctor->name ?? '' }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $appointment->status == 'confirmed' ? 'success' : 
                                            ($appointment->status == 'pending_payment' ? 'warning' : 'danger') 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div class="alert alert-info">No recent appointments found.</div>
                    @endif
                </div>
            </div>
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