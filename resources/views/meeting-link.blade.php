@extends('layouts.base')

@section('content')
<div class="row">
    <div class="col-md-8">
        <h2 class="mb-4">Meeting Link</h2>
        
        <div class="alert alert-warning text-dark">
            <i class="fa fa-exclamation-circle me-2"></i>
            Remember to record your meetings and keep track of time.
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary">
                <h4 class="text-white"><i class="fa fa-video-camera me-2"></i> Your Personal Meeting Room</h4>
            </div>
            <div class="card-body text-dark">
                <p>Your permanent meeting link:</p>
                <div class="meeting-link mb-3">
                    <strong>https://meet.jit.si/{{ $doctor->meeting_slug ?? 'dr-' . strtolower(str_replace(' ', '-', $doctor->name)) }}</strong>
                </div>
                <p class="text-muted">This link is permanent and will be shared with patients when they book appointments with you.</p>

                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-outline-primary" onclick="copyMeetingLink()">Copy Link</button>
                    <a href="https://meet.jit.si/{{ $doctor->meeting_slug ?? 'dr-' . strtolower(str_replace(' ', '-', $doctor->name)) }}" target="_blank" class="btn btn-success">Open Meeting Room</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-info text-white">
                <h4><i class="fa fa-calendar me-2"></i> Upcoming Appointments</h4>
            </div>
            <div class="card-body">
                @if(isset($upcomingAppointments) && $upcomingAppointments->count() > 0)
                <ul class="list-group">
                    @foreach($upcomingAppointments as $appointment)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $appointment->student?->name ?? $appointment->patient?->name ?? 'N/A' }}</strong><br>
                            <small>{{ $appointment->appointment_time->format('M d, Y h:i A') }}</small>
                        </div>
                        <a href="{{ $doctor->meeting_link }}" 
                            target="_blank" 
                            class="btn btn-sm btn-success">
                            Start Meeting
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted">No upcoming appointments.</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-success text-dark">
                <h4><i class="fa fa-bullhorn"></i> Quick Actions</h4>
            </div>
            <div class="card-body">
                <button class="btn btn-primary w-100 mb-2" onclick="copyMeetingLink()">
                    <i class="fa fa-copy me-2"></i> Copy Meeting Link
                </button>
                <a href="https://meet.jit.si/{{ $doctor->meeting_slug ?? 'dr-' . strtolower(str_replace(' ', '-', $doctor->name)) }}" 
                    target="_blank" 
                    class="btn btn-success w-100 mb-2">
                    <i class="fas fa-video me-2"></i> Test Meeting Room
                </a>
                <button class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#sendLinkModal">
                    <i class="fa fa-envelope me-2"></i> Send Link to School/Health Facility
                </button>
            </div>
        </div>

        <!-- Send Link Modal -->
        <div class="modal fade" id="sendLinkModal" tabindex="-1" aria-labelledby="sendLinkModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('doctor.send-link', ['doctor' => $doctor->id]) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="sendLinkModalLabel">Send Meeting Link</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="recipient_email" class="form-label">Recipient email</label>
                                <input type="email" class="form-control" id="recipient_email" name="recipient_email" required placeholder="example@school.edu">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message (optional)</label>
                                <textarea class="form-control" id="message" name="message" rows="3">Hi, here is my meeting link: {{ $doctor->meeting_slug ? 'https://meet.jit.si/'.$doctor->meeting_slug : 'https://meet.jit.si/dr-'.strtolower(str_replace(' ', '-', $doctor->name)) }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Send Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h4 class="text-white"><i class="fa fa-chart-line"></i> Statistics</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Total Appointments</h6>
                    <div class="progress">
                        <div class="progress-bar bg-primary" 
                                style="width: {{ isset($stats['total_appointments']) ? min(100, $stats['total_appointments'] / 50 * 100) : 0 }}%">
                            {{ $stats['total_appointments'] ?? 0 }}
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <h6>Completed Meetings</h6>
                    <div class="progress">
                        <div class="progress-bar bg-success" 
                                style="width: {{ isset($stats['completed_appointments']) && isset($stats['total_appointments']) ? min(100, $stats['completed_appointments'] / max(1, $stats['total_appointments']) * 100) : 0 }}%">
                            {{ $stats['completed_appointments'] ?? 0 }}
                        </div>
                    </div>
                </div>
                <div>
                    <h6>Upcoming</h6>
                    <div class="progress">
                        <div class="progress-bar bg-warning" 
                                style="width: {{ isset($stats['upcoming_appointments']) ? min(100, $stats['upcoming_appointments'] / 10 * 100) : 0 }}%">
                            {{ $stats['upcoming_appointments'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyMeetingLink() {
    const text = 'https://meet.jit.si/{{ $doctor->meeting_slug ?? 'dr-' . strtolower(str_replace(' ', '-', $doctor->name)) }}';
    navigator.clipboard?.writeText(text).then(function(){
        alert('Meeting link copied to clipboard');
    }).catch(function(){
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        alert('Meeting link copied to clipboard');
    });
}
</script>
@endpush