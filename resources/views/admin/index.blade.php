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

    <!-- Annual Revenue & Appointments Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h5 class="mb-0">Annual Revenue & Appointments Overview</h5>
                    <small class="text-muted">Monthly data for {{ date('Y') }} (Sample Data)</small>
                </div>
                <div class="card-body">
                    <div class="chart-container annual-chart-container" style="position: relative; height: 400px; width: 100%; border: 2px solid #ddd; background: #f9f9f9;">
                        <canvas id="annualChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Entity Distribution Chart -->
        <div class="col-xl-6 col-lg-6 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h5 class="mb-0">Entity Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 350px; width: 100%;">
                        <canvas id="distributionChart"></canvas>
                    </div>
                    <div class="chart-legend mt-3">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                    <div class="legend-text">
                                        <strong>{{ $stats['doctors'] }}</strong><br>
                                        <small>Doctors</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                                    <div class="legend-text">
                                        <strong>{{ $stats['schools'] }}</strong><br>
                                        <small>Schools</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                                    <div class="legend-text">
                                        <strong>{{ $stats['health_facilities'] }}</strong><br>
                                        <small>Health Facilities</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient Gender Distribution Chart -->
        <div class="col-xl-6 col-lg-6 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h5 class="mb-0">Patient Gender Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 350px; width: 100%;">
                        <canvas id="patientGenderChart"></canvas>
                    </div>
                    <div class="chart-legend mt-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                                    <div class="legend-text">
                                        <strong>{{ $stats['patients_male'] ?? 0 }}</strong><br>
                                        <small>Male Patients</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                                    <div class="legend-text">
                                        <strong>{{ $stats['patients_female'] ?? 0 }}</strong><br>
                                        <small>Female Patients</small>
                                    </div>
                                </div>
                            </div>
                        </div>
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

/* Chart Container */
.chart-container {
    position: relative;
    height: 350px;
    width: 100%;
    margin: 0 auto;
}

/* Annual Chart Container - specific height */
.annual-chart-container {
    height: 400px !important;
}

/* Chart Legend Styles */
.chart-legend {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

.legend-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.legend-text {
    font-size: 0.85rem;
    color: #4a5568;
    text-align: center;
}

.legend-text strong {
    font-size: 1.2rem;
    color: #2d3748;
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

{{-- Chart Scripts --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Distribution Donut Chart
    const ctx = document.getElementById('distributionChart');
    if (ctx) {
        const distributionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Doctors', 'Schools', 'Health Facilities'],
                datasets: [{
                    data: [
                        {{ $stats['doctors'] }},
                        {{ $stats['schools'] }},
                        {{ $stats['health_facilities'] }}
                    ],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(240, 147, 251, 0.8)',
                        'rgba(79, 172, 254, 0.8)'
                    ],
                    borderColor: [
                        'rgba(102, 126, 234, 1)',
                        'rgba(240, 147, 251, 1)',
                        'rgba(79, 172, 254, 1)'
                    ],
                    borderWidth: 2,
                    hoverBackgroundColor: [
                        'rgba(102, 126, 234, 0.9)',
                        'rgba(240, 147, 251, 0.9)',
                        'rgba(79, 172, 254, 0.9)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false // Hide default legend, using custom legend below
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            const label = data.labels[tooltipItem.index] || '';
                            const value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || 0;
                            const total = data.datasets[tooltipItem.datasetIndex].data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                cutoutPercentage: 60, // Creates donut effect
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    }

    // Patient Gender Distribution Donut Chart
    const ctx2 = document.getElementById('patientGenderChart');
    if (ctx2) {
        const patientGenderChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Male Patients', 'Female Patients'],
                datasets: [{
                    data: [
                        {{ $stats['patients_male'] ?? 0 }},
                        {{ $stats['patients_female'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(79, 172, 254, 0.8)',
                        'rgba(240, 147, 251, 0.8)'
                    ],
                    borderColor: [
                        'rgba(79, 172, 254, 1)',
                        'rgba(240, 147, 251, 1)'
                    ],
                    borderWidth: 2,
                    hoverBackgroundColor: [
                        'rgba(79, 172, 254, 0.9)',
                        'rgba(240, 147, 251, 0.9)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false // Hide default legend, using custom legend below
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            const label = data.labels[tooltipItem.index] || '';
                            const value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || 0;
                            const total = data.datasets[tooltipItem.datasetIndex].data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                cutoutPercentage: 60, // Creates donut effect
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    }

    // Annual Revenue & Appointments Bar Chart
    const ctx3 = document.getElementById('annualChart');
    if (ctx3) {
        const monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthlyAppointments = [5, 8, 12, 15, 10, 18, 22, 25, 20, 28, 30, 35];
        const monthlyRevenue = [50000, 75000, 120000, 150000, 100000, 180000, 220000, 250000, 200000, 280000, 300000, 350000];

        const annualChart = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Appointments',
                    data: monthlyAppointments,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    yAxisID: 'y',
                    order: 2
                }, {
                    label: 'Revenue (UGX)',
                    data: monthlyRevenue,
                    backgroundColor: 'rgba(240, 147, 251, 0.8)',
                    borderColor: 'rgba(240, 147, 251, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1',
                    order: 1,
                    type: 'line',
                    fill: false,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(240, 147, 251, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            let label = data.datasets[tooltipItem.datasetIndex].label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (tooltipItem.datasetIndex === 1) {
                                label += 'UGX ' + tooltipItem.yLabel.toLocaleString();
                            } else {
                                label += tooltipItem.yLabel;
                            }
                            return label;
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        type: 'linear',
                        display: true,
                        position: 'left',
                        scaleLabel: {
                            display: true,
                            labelString: 'Number of Appointments'
                        },
                        gridLines: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            beginAtZero: true
                        },
                        id: 'y'
                    }, {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        scaleLabel: {
                            display: true,
                            labelString: 'Revenue (UGX)'
                        },
                        gridLines: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return 'UGX ' + value.toLocaleString();
                            }
                        },
                        id: 'y1'
                    }]
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
});
</script>
@endpush
@endsection