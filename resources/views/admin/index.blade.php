@extends('layouts.base')

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-muted mb-3">Welcome to the admin panel</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Doctors -->
        <div class="col-lg-3 col-sm-6 mb-2">
            <div class="card">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <div class="stat-label mb-2">Doctors</div>
                        <h5 class="mb-0">{{ $stats['doctors'] }}</h5>
                    </div>
                    <i class="mdi mdi-account-location icon-xl text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Schools -->
        <div class="col-lg-3 col-sm-6 mb-2">
            <div class="card">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <div class="stat-label mb-2">Schools</div>
                        <h5 class="mb-0">{{ $stats['schools'] }}</h5>
                    </div>
                    <i class="mdi mdi-school icon-xl text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Health Facilities -->
        <div class="col-lg-3 col-sm-6 mb-2">
            <div class="card">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <div class="stat-label mb-2">Health Facilities</div>
                        <h5 class="mb-0">{{ $stats['health_facilities'] }}</h5>
                    </div>
                    <i class="mdi mdi-hospital-building icon-xl text-primary"></i>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="col-lg-3 col-sm-6 mb-2">
            <div class="card">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <div class="stat-label mb-2">Revenue</div>
                        <h5 class="mb-0">UGX {{ number_format($stats['revenue'], 0) }}</h5>
                    </div>
                    <i class="mdi mdi-square-inc-cash icon-xl text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Annual Revenue & Appointments Chart -->
    <div class="row mb-4">
        <div class="col-lg-8 col-md-12">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h5 class="mb-0">Annual Revenue & Appointments Overview</h5>
                    <small class="text-muted">Monthly data for {{ date('Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="chart-container annual-chart-container">
                        <canvas id="annualChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctor Location Map -->
        <div class="col-lg-4 col-md-12">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h5 class="mb-0">Location Distribution</h5>
                    <small class="text-muted">Schools, Health Facilities & Doctors</small>
                </div>
                <div class="card-body p-0">
                    <div id="doctorMap" style="height: 350px; width: 100%; border-radius: 0 0 15px 15px;"></div>
                </div>
                <div class="card-footer bg-white border-0 p-3">
                    <div class="doctor-stats">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="location-stat p-2 rounded" style="background: rgba(102, 126, 234, 0.1);">
                                    <i class="mdi mdi-school text-primary d-block mb-1" style="font-size: 1.2rem;"></i>
                                    <div class="fw-bold text-primary">{{ $stats['schools'] }}</div>
                                    <small class="text-muted">Schools</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="location-stat p-2 rounded" style="background: rgba(240, 147, 251, 0.1);">
                                    <i class="mdi mdi-hospital-building text-danger d-block mb-1" style="font-size: 1.2rem;"></i>
                                    <div class="fw-bold text-danger">{{ $stats['health_facilities'] }}</div>
                                    <small class="text-muted">Facilities</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="location-stat p-2 rounded" style="background: rgba(149, 165, 166, 0.1);">
                                    <i class="mdi mdi-doctor text-muted d-block mb-1" style="font-size: 1.2rem;"></i>
                                    <div class="fw-bold text-muted">{{ $stats['independent_doctors'] }}</div>
                                    <small class="text-muted">Doctors</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution Charts -->
    <div class="row mb-4">
        <!-- Entity Distribution -->
        <div class="col-xl-6 col-lg-6 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h5 class="mb-0">Entity Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="distributionChart"></canvas>
                    </div>
                    <div class="chart-legend mt-3 text-center">
                        <div class="row">
                            <div class="col-4">
                                <strong>{{ $stats['doctors'] }}</strong><br><small>Doctors</small>
                            </div>
                            <div class="col-4">
                                <strong>{{ $stats['schools'] }}</strong><br><small>Schools</small>
                            </div>
                            <div class="col-4">
                                <strong>{{ $stats['health_facilities'] }}</strong><br><small>Facilities</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient Gender Distribution -->
        <div class="col-xl-6 col-lg-6 col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h5 class="mb-0">Patient Gender Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="patientGenderChart"></canvas>
                    </div>
                    <div class="chart-legend mt-3 text-center">
                        <div class="row">
                            <div class="col-6">
                                <strong>{{ $stats['patients_male'] ?? 0 }}</strong><br><small>Male Patients</small>
                            </div>
                            <div class="col-6">
                                <strong>{{ $stats['patients_female'] ?? 0 }}</strong><br><small>Female Patients</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ======================= STYLES ======================= --}}
<style>
.card {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    border: none;
}
.card-header {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-bottom: none;
    border-radius: 15px 15px 0 0 !important;
}
.chart-container {
    position: relative;
    height: 350px;
    width: 100%;
}
.annual-chart-container {
    height: 350px !important;
}
.location-stat {
    transition: all 0.3s ease;
}
.location-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.info.legend {
    background: white;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-size: 12px;
    line-height: 1.4;
}
</style>

{{-- ======================= SCRIPTS ======================= --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Distribution Donut Chart
    const distCtx = document.getElementById('distributionChart');
    if (distCtx) {
        new Chart(distCtx, {
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
                        'rgba(102,126,234,0.8)',
                        'rgba(240,147,251,0.8)',
                        'rgba(79,172,254,0.8)'
                    ],
                    borderWidth: 2
                }]
            },
            options: { cutoutPercentage: 60, responsive: true }
        });
    }

    // Patient Gender Chart
    const genderCtx = document.getElementById('patientGenderChart');
    if (genderCtx) {
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male Patients', 'Female Patients'],
                datasets: [{
                    data: [
                        {{ $stats['patients_male'] ?? 0 }},
                        {{ $stats['patients_female'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(79,172,254,0.8)',
                        'rgba(240,147,251,0.8)'
                    ],
                    borderWidth: 2
                }]
            },
            options: { cutoutPercentage: 60, responsive: true }
        });
    }

    // Annual Revenue & Appointments Chart
    const annualCtx = document.getElementById('annualChart');
    if (annualCtx) {
        const monthlyData = @json($monthlyData);
        const labels = monthlyData.map(item => item.month);
        const appointments = monthlyData.map(item => item.appointments);
        const revenue = monthlyData.map(item => item.revenue);

        new Chart(annualCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Appointments',
                        data: appointments,
                        backgroundColor: 'rgba(102,126,234,0.8)',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Revenue (UGX)',
                        data: revenue,
                        type: 'line',
                        borderColor: 'rgba(240,147,251,1)',
                        backgroundColor: 'rgba(240,147,251,0.3)',
                        fill: false,
                        yAxisID: 'y1',
                        tension: 0.4
                    }
                ]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Appointments' } },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'Revenue (UGX)' }
                    }
                }
            }
        });
    }

    // Doctor Location Map
    function initializeDoctorMap() {
        const mapEl = document.getElementById('doctorMap');
        if (!mapEl) return;

        if (mapEl._leaflet_id) mapEl.innerHTML = "";
        const map = L.map('doctorMap').setView([1.3733, 32.2903], 7);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const locations = @json($locations);
        const colors = { school: '#667eea', health_facility: '#f093fb', doctor: '#95a5a6' };
        const icons = { school: 'mdi-school', health_facility: 'mdi-hospital-building', doctor: 'mdi-doctor' };

        locations.forEach(([name, lat, lng, type, entityType, city]) => {
            const icon = L.divIcon({
                html: `<div style="background:${colors[type]};border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border:2px solid white;">
                           <i class="mdi ${icons[type]}" style="color:white;font-size:14px;"></i>
                       </div>`,
                className: 'custom-doctor-marker'
            });
            L.marker([lat, lng], { icon })
                .addTo(map)
                .bindPopup(`<div style="text-align:center;">
                    <strong>${name}</strong><br>
                    <small>${entityType}</small><br>
                    <small>${city}</small><br>
                    <span style="color:${colors[type]};">●</span> ${entityType}
                </div>`);
        });

        // Legend
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function() {
            const div = L.DomUtil.create('div', 'info legend');
            div.innerHTML = `
                <strong>Location Types</strong><br>
                <span style="color:#667eea;">●</span> Schools<br>
                <span style="color:#f093fb;">●</span> Health Facilities<br>
                <span style="color:#95a5a6;">●</span> Doctors
            `;
            return div;
        };
        legend.addTo(map);
    }

    setTimeout(initializeDoctorMap, 100);
});
</script>
@endpush

@endsection
