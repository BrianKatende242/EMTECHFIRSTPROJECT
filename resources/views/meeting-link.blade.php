@extends('layouts.base')

@section('content')
@php
    $meetingSlug = $doctor->meeting_slug ?? ('dr-' . strtolower(str_replace(' ', '-', $doctor->name)));
    $meetingUrl = 'https://meet.jit.si/' . $meetingSlug;
@endphp
<div class="row">
    <div class="col-md-8">
        <h2 class="mb-3">Meeting Link</h2>
        <div id="ml-alert" class="mb-3"></div>

        <div class="alert alert-warning text-dark">
            <i class="fa fa-exclamation-circle me-2"></i>
            Remember to record your meetings and keep track of time.
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                <h4 class="mb-0 text-white">Your Personal Meeting Room</h4>
                <i class="fa fa-video-camera fa-2x text-white" aria-hidden="true"></i>
            </div>
            <div class="card-body text-dark">
                <label class="form-label small text-muted">Permanent meeting link</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="meetingLinkInput" value="{{ $meetingUrl }}" readonly>
                    <button class="btn btn-outline-primary" type="button" id="copyLinkBtn">
                        <i class="fa fa-copy me-1"></i> Copy
                    </button>
                </div>
                <p class="text-muted mb-0">This link is permanent and will be shared with patients when they book appointments with you.</p>

                <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
                    <a href="{{ $meetingUrl }}" target="_blank" class="btn btn-success">
                        <i class="fa fa-play me-1"></i> Open Meeting Room
                    </a>
                    <span aria-hidden="true" class="mx-1 d-none d-sm-inline-block" style="width:1px; height:38px; background:#e9ecef; display:inline-block;"></span>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#sendLinkModal">
                        <i class="fa fa-envelope me-2"></i> Send Link to School/Health Facility
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-purple text-white">
                <h4 class="mb-0 text-white"><i class="fa fa-calendar me-2 text-white" aria-hidden="true"></i> Upcoming Appointments</h4>
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
                        <a href="{{ $meetingUrl }}" 
                            target="_blank" 
                            class="btn btn-sm btn-success">
                            Start Meeting
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted mb-0">No upcoming appointments.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">

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
                                <textarea class="form-control" id="message" name="message" rows="3">Hi, here is my meeting link: {{ $meetingUrl }}</textarea>
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
document.addEventListener('DOMContentLoaded', function(){
  const copyBtn = document.getElementById('copyLinkBtn');
  const input = document.getElementById('meetingLinkInput');
  const alertBox = document.getElementById('ml-alert');
  function showInlineAlert(msg, type='success'){
    if(!alertBox) return;
    alertBox.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
    setTimeout(()=>{ const a = alertBox.querySelector('.alert'); if(a){ a.classList.remove('show'); a.addEventListener('transitionend', ()=> alertBox.innerHTML = ''); } }, 2500);
  }
  if(copyBtn && input){
    copyBtn.addEventListener('click', function(){
      const text = input.value;
      (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject())
      .then(()=> showInlineAlert('Meeting link copied'))
      .catch(()=>{
        const el = document.createElement('textarea');
        el.value = text; document.body.appendChild(el); el.select(); document.execCommand('copy'); document.body.removeChild(el);
        showInlineAlert('Meeting link copied');
      });
    });
  }
});
</script>
@endpush