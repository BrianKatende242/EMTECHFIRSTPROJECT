@if(isset($school))
<ul class="metismenu" id="menu">
    <li class="nav-label first">Main Menu</li>
    <li>
            <a class="{{ request()->is('dashboard*') ? 'mm-active' : '' }}"
                href="{{ route('school.dashboard', ['school' => $school]) }}" aria-expanded="false">
                <i class="fa fa-dashboard"></i>
                <span class="nav-text">Dashboard</span>
            </a>
    </li>
    <li>
        <a class="" href="{{ route('students', ['school' => $school]) }}" aria-expanded="false">
            <i class="fa fa-users"></i>
            <span class="nav-text">Students</span>
        </a>
    </li>
    <li>
        <a class="" href="{{ route('book-doctor', ['school' => $school])}}" aria-expanded="false">
            <i class="fa fa-user-md"></i>
            <span class="nav-text">Book Doctor</span>
        </a>
    </li>
    <li>
        <a class="" href="{{ route('lab-tests', ['school' => $school]) }}" aria-expanded="false">
            <i class="fa fa-flask"></i>
            <span class="nav-text">Lab Tests</span>
        </a>
    </li>
</ul>
@elseif(isset($doctor))
<ul class="metismenu" id="menu">
    <li class="nav-label first">Doctor's Menu</li>
    <li>
        <a class="{{ request()->is('doctor-dashboard*') ? 'mm-active' : '' }}"
            href="{{ route('doctor.dashboard', ['doctorId' => $doctor->id]) }}" aria-expanded="false">
            <i class="fa fa-dashboard"></i>
            <span class="nav-text">Dashboard</span>
        </a>
    </li>
    <li>
        <a class="" href="{{ route('doctor.appointments', ['doctorId' => $doctor->id]) }}" aria-expanded="false">
            <i class="fa fa-calendar"></i>
            <span class="nav-text">Appointments</span>
        </a>
    </li>
    <li>
        <a class="" href="{{ route('doctor.meeting-link', ['doctorId' => $doctor->id]) }}" aria-expanded="false">
            <i class="fa fa-video-camera"></i>
            <span class="nav-text">Meeting link</span>
        </a>
    </li>
    <li>
        <a class="" href="{{ route('doctor.availability', ['doctorId' => $doctor->id]) }}" aria-expanded="false">
            <i class="fa fa-check"></i>
            <span class="nav-text">Availability</span>
        </a>
    </li>

</ul>
@endif