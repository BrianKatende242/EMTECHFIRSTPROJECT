@extends('layouts.base')

@section('content')
<div class="container-fluid px-3 py-2">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-muted mb-3">Welcome to the admin panel</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Doctors Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card doctors-card shadow h-100">
                <div class="stat-card-inner">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-content">
                                <div class="stat-number">{{ $stats['doctors'] }}</div>
                                <div class="stat-label">Doctors</div>
                                <div class="stat-trend">
                                    <i class="fa fa-arrow-up text-success"></i>
                                    <span class="text-success">+12%</span> from last month
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fa fa-user-md"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schools Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card schools-card shadow h-100">
                <div class="stat-card-inner">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-content">
                                <div class="stat-number">{{ $stats['schools'] }}</div>
                                <div class="stat-label">Schools</div>
                                <div class="stat-trend">
                                    <i class="fa fa-arrow-up text-success"></i>
                                    <span class="text-success">+8%</span> from last month
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fa fa-building"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Facilities Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card facilities-card shadow h-100">
                <div class="stat-card-inner">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-content">
                                <div class="stat-number">{{ $stats['health_facilities'] }}</div>
                                <div class="stat-label">Health Facilities</div>
                                <div class="stat-trend">
                                    <i class="fa fa-arrow-up text-success"></i>
                                    <span class="text-success">+15%</span> from last month
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fa fa-hospital-o"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card revenue-card shadow h-100">
                <div class="stat-card-inner">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="stat-content">
                                <div class="stat-number">UGX {{ number_format($stats['revenue'], 0) }}</div>
                                <div class="stat-label">Revenue</div>
                                <div class="stat-trend">
                                    <i class="fa fa-arrow-up text-success"></i>
                                    <span class="text-success">+23%</span> from last month
                                </div>
                            </div>
                            <div class="stat-icon">
                                <i class="fa fa-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Links -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h5 class="mb-0">Management</h5>
                </div>
                <div class="card-body py-3">
                    <div class="row">
                        @foreach($models as $key => $label)
                            <div class="col-md-4 col-sm-6 mb-2">
                                <a href="{{ route('admin.model.index', $key) }}" class="btn btn-outline-primary btn-block d-flex align-items-center justify-content-center py-2">
                                    <i class="fa fa-list mr-2"></i>
                                    Manage {{ $label }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Stat Cards */
.stat-card {
    border: none;
    border-radius: 15px;
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
    background: #f8f9fa;
    color: #2d3748;
    padding: 2px;
    background-clip: padding-box;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
}

.stat-card-inner {
    background: #f8f9fa;
    border-radius: 13px;
    height: 100%;
}

/* Doctors Card */
.doctors-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%), #f8f9fa;
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
}

/* Schools Card */
.schools-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%), #f8f9fa;
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
}

/* Facilities Card */
.facilities-card {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%), #f8f9fa;
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
}

/* Revenue Card */
.revenue-card {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%), #f8f9fa;
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-trend {
    font-size: 0.75rem;
    color: #718096;
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-trend i {
    font-size: 0.7rem;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.card-body {
    padding: 1.5rem;
}

/* Management Section */
.card {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    border: none;
}

.card-header {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-bottom: none;
    border-radius: 15px 15px 0 0 !important;
    padding: 1rem 1.5rem;
}

.card-header h5 {
    color: #2d3748;
    font-weight: 600;
    margin: 0;
}

.btn-outline-primary {
    border: 2px solid;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    background: transparent;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-outline-primary i {
    margin-right: 8px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stat-number {
        font-size: 2rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }

    .card-body {
        padding: 1rem;
    }
}
</style>
@endsection