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
                $totalLabTests = max($pendingLabTests + $completedLabTests, 1);
                $completionRate = round(($completedLabTests / $totalLabTests) * 100);
            @endphp
            <div class="card stat-card stat-labtests">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon"><i class="fa fa-flask" aria-hidden="true"></i></div>
                        <div class="stat-sep" aria-hidden="true"></div>
                        <div>
                            <div class="h5 mb-0">Lab Test Summary</div>
                            <small class="text-muted">Overall completion</small>
                        </div>
                    </div>
                    <div class="row align-items-start g-3">
                        <div class="col-12 col-md-6 text-center">
                            <canvas id="labTestsDonut" height="150"></canvas>
                            <div class="legend-inline d-flex justify-content-center gap-3 mt-2 small">
                                <div class="d-flex align-items-center"><span class="legend-dot me-2" style="background:#593bdb"></span><span>Completed</span></div>
                                <div class="d-flex align-items-center"><span class="legend-dot me-2" style="background: rgba(0,0,0,0.65)"></span><span>Pending</span></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="metric-badges d-flex flex-wrap gap-2 mb-3">
                                <div class="metric-badge completed">
                                    <div class="label">Completed</div>
                                    <div class="value">{{ $completedLabTests }}</div>
                                </div>
                                <div class="metric-badge pending">
                                    <div class="label">Pending</div>
                                    <div class="value">{{ $pendingLabTests }}</div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1 small text-muted">
                                    <span>Completion</span>
                                    <span>{{ $completionRate }}%</span>
                                </div>
                                <div class="progress" role="progressbar" aria-label="Lab test completion progress" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $completionRate }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $recentLabTests = isset($labTests) ? $labTests->take(5) : collect();
                        $topTypes = isset($labTests)
                            ? $labTests->groupBy('test_type')->map->count()->sortDesc()->take(3)
                            : collect();
                    @endphp

                    <hr class="soft-hr my-3">

                    <div class="card-section mb-3">
                        <div class="section-header d-flex align-items-center mb-2">
                            <div class="title me-2">Top Test Types</div>
                            <small class="text-muted">last {{ isset($labTests) ? min($labTests->count(), 50) : 0 }}</small>
                        </div>
                        <div class="chips">
                            @forelse($topTypes as $type => $count)
                                <span class="chip">{{ $type }} <span class="chip-count">{{ $count }}</span></span>
                            @empty
                                <span class="text-muted small">No lab tests yet.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="section-header d-flex align-items-center mb-2">
                        <div class="title me-2">Recent Lab Tests</div>
                        <small class="text-muted">latest 5</small>
                        <a class="ms-auto btn btn-sm btn-outline-primary" href="{{ route('lab-tests', ['school' => $school->id]) }}">View all</a>
                    </div>
                    <ul class="recent-list list-unstyled mb-0">
                        @forelse($recentLabTests as $lt)
                            @php $statusClass = ($lt->status ?? '') === 'completed' ? 'pill-completed' : 'pill-pending'; @endphp
                            <li class="recent-item d-flex align-items-center py-2">
                                <div class="flex-grow-1">
                                    <div class="title fw-semibold">
                                        {{ optional($lt->student)->name ?? 'Student' }}
                                        <span class="text-muted">• {{ $lt->test_type ?? 'Test' }}</span>
                                    </div>
                                    <div class="subtext text-muted small">{{ \Carbon\Carbon::parse($lt->created_at)->format('M j, Y') }}</div>
                                </div>
                                <span class="status-pill {{ $statusClass }}">{{ ucfirst($lt->status ?? 'pending') }}</span>
                            </li>
                        @empty
                            <li class="text-muted small">No recent lab tests.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var donutCtx = document.getElementById('labTestsDonut').getContext('2d');
                    var completed = {{ $completedLabTests }};
                    var pending = {{ $pendingLabTests }};
                    var total = Math.max(completed + pending, 1);
                    var pct = Math.round((completed / total) * 100);

                    const centerText = {
                        id: 'centerText',
                        afterDraw(chart) {
                            const {ctx, chartArea: {width, height}} = chart;
                            ctx.save();
                            ctx.font = '600 18px system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif';
                            ctx.fillStyle = '#1f1f1f';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(pct + '%', chart.getDatasetMeta(0).data[0].x, chart.getDatasetMeta(0).data[0].y);
                            ctx.restore();
                        }
                    };

                    new Chart(donutCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Completed', 'Pending'],
                            datasets: [{
                                data: [completed, pending],
                                backgroundColor: ['#593bdb', 'rgba(0,0,0,0.65)'],
                                borderColor: ['#593bdb', 'rgba(0,0,0,0.85)'],
                                borderWidth: 2,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            cutout: '65%',
                            plugins: {
                                legend: { display: false },
                                tooltip: { enabled: true }
                            }
                        },
                        plugins: [centerText]
                    });
                });
            </script>
        </div>
    </div>
@endsection