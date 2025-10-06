@extends('layouts.base')

@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Header: doctor info and prominent meeting card --}}
    @php
        $meetingSlug = $doctor->meeting_slug ?? null;
        $meetingLink = $meetingSlug ? ('https://meet.jit.si/' . $meetingSlug) : ('https://meet.jit.si/dr-' . strtolower(str_replace(' ', '-', $doctor->name)));
    @endphp

    {{-- compute small aggregates from appointments passed by controller --}}
    @php
        $appointments = $appointments ?? collect();
        $upcomingAppointments = $upcomingAppointments ?? collect();

        // unique patients count (student or patient)
        $patientsIds = $appointments->map(function($a){
            return data_get($a, 'student.id') ?? data_get($a, 'patient.id') ?? null;
        })->filter()->unique();
        $patientsCount = $patientsIds->count();

        $pendingCount = $appointments->where('status', 'pending')->count();

        // Prepare last 7 days labels and counts (including today)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = \Carbon\Carbon::today()->subDays($i);
            $chartLabels[] = $d->format('M d');
            $count = $appointments->filter(function($a) use ($d) {
                try {
                    $at = \Carbon\Carbon::parse(data_get($a, 'appointment_time'));
                    return $at->isSameDay($d);
                } catch (\Exception $e) {
                    return false;
                }
            })->count();
            $chartData[] = $count;
        }

        $stats = $stats ?? ['total_appointments' => $appointments->count(), 'completed_appointments' => $appointments->where('status','completed')->count(), 'upcoming_appointments' => $upcomingAppointments->count()];
    @endphp

    {{-- Summary cards --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 text-muted">Total Appointments</h6>
                    <h3 class="mb-1">{{ $stats['total_appointments'] ?? 0 }}</h3>
                    <small class="text-muted">Patients: {{ $patientsCount }}</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 text-muted">Completed</h6>
                    <h3 class="mb-1 text-success">{{ $stats['completed_appointments'] ?? 0 }}</h3>
                    <small class="text-muted">This period</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 text-muted">Upcoming</h6>
                    <h3 class="mb-1 text-warning">{{ $stats['upcoming_appointments'] ?? 0 }}</h3>
                    <small class="text-muted">Next appointments</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2 text-muted">Pending</h6>
                    <h3 class="mb-1 text-danger">{{ $pendingCount }}</h3>
                    <small class="text-muted">Action required</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Appointments (Last 7 days)</h5>
                    <div class="text-muted small">Updated: {{ now()->format('M d, Y') }}</div>
                </div>
                <div class="card-body" style="min-height:220px;">
                    <canvas id="appointmentsChart" height="160"></canvas>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Upcoming Appointments</h5>
                </div>
                <div class="card-body">
                    @if($upcomingAppointments->count())
                        <ul class="list-group">
                            @foreach($upcomingAppointments as $appt)
                                @php
                                    $studentName = data_get($appt, 'student.name') ?? data_get($appt, 'patient.name') ?? 'Unknown';
                                    $time = isset($appt->appointment_time) ? \Carbon\Carbon::parse($appt->appointment_time)->format('M d, Y h:i A') : 'N/A';
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $studentName }}</strong><br>
                                        <small class="text-muted">{{ $time }}</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ $meetingLink }}" target="_blank" class="btn btn-sm btn-success">Start</a>
                                        <a href="/doctor/{{ $doctor->id }}/appointments" class="btn btn-sm btn-outline-secondary">View</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No upcoming appointments.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary w-100 mb-2" onclick="copyMeetingLink()">Copy Meeting Link</button>
                    <a href="{{ $meetingLink }}" target="_blank" class="btn btn-success w-100 mb-2">Test Meeting Room</a>
                    <!-- Edit Meeting Link removed: permanent meeting link is displayed above -->
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Notifications</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">You have <strong>{{ $notificationsCount ?? 0 }}</strong> notifications.</p>
                    {{-- Placeholder list, can be wired to real notifications later --}}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyMeetingLink() {
            const text = '{{ $meetingLink }}';
            navigator.clipboard?.writeText(text).then(function(){
                alert('Meeting link copied to clipboard');
            }).catch(function(){
                // fallback
                const el = document.createElement('textarea');
                el.value = text;
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
                alert('Meeting link copied to clipboard');
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('appointmentsChart');
            if (!ctx) return;

            var labels = {!! json_encode($chartLabels) !!};
            var data = {!! json_encode($chartData) !!};

            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not loaded');
                return;
            }

            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Appointments',
                        data: data,
                        backgroundColor: 'rgba(255, 0, 248, 0.12)', // KETI pink fill
                        borderColor: '#000000', // black line
                        pointBackgroundColor: '#FF00F8', // pink points
                        pointBorderColor: '#000000', // black point border
                        pointHoverBackgroundColor: '#000000',
                        pointHoverBorderColor: '#FF00F8',
                        fill: true,
                        tension: 0.25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, precision:0 } }
                }
            });
        });
    </script>
    @endpush

@endsection