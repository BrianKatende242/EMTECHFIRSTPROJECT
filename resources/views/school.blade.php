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
@endsection