@if(isset($school))
<ul class="metismenu" id="menu">
    <li class="nav-label first">Main Menu</li>
    <li class="{{ request()->routeIs('school.dashboard') ? 'mm-active' : '' }}">
            <a
                href="{{ route('school.dashboard', ['school' => $school]) }}" aria-expanded="false">
                <i class="fa fa-dashboard"></i>
                <span class="nav-text">Dashboard</span>
            </a>
    </li>
    <li class="{{ request()->routeIs('students') ? 'mm-active' : '' }}">
        <a href="{{ route('students', ['school' => $school]) }}" aria-expanded="false">
            <i class="fa fa-users"></i>
            <span class="nav-text">Students</span>
        </a>
    </li>
    <li class="{{ request()->routeIs('book-doctor') ? 'mm-active' : '' }}">
        <a href="{{ route('book-doctor', ['school' => $school])}}" aria-expanded="false">
            <i class="fa fa-user-md"></i>
            <span class="nav-text">Book Doctor</span>
        </a>
    </li>
    {{-- Lab Tests removed until laboratory functionality is available --}}
</ul>
@elseif(isset($healthFacility))
<ul class="metismenu" id="menu">
    <li class="nav-label first">Health Facility</li>
    <li class="{{ request()->routeIs('health-facility.dashboard') ? 'mm-active' : '' }}">
        <a href="{{ route('health-facility.dashboard', ['id' => $healthFacility->id]) }}" aria-expanded="false">
            <i class="fa fa-dashboard"></i>
            <span class="nav-text">Dashboard</span>
        </a>
    </li>
    <li class="{{ request()->routeIs('health-facility.patients') ? 'mm-active' : '' }}">
        <a href="{{ route('health-facility.patients', ['id' => $healthFacility->id]) }}" aria-expanded="false">
            <i class="fa fa-users"></i>
            <span class="nav-text">Patients</span>
        </a>
    </li>
    <li>
        {{-- Add Patient via modal on Patients page; link removed from sidebar --}}
    </li>
    <li class="{{ request()->routeIs('health-facility.book-doctor') ? 'mm-active' : '' }}">
        <a href="{{ route('health-facility.book-doctor', ['id' => $healthFacility->id]) }}" aria-expanded="false">
            <i class="fa fa-user-md"></i>
            <span class="nav-text">Book Doctor</span>
        </a>
    </li>
    {{-- Lab Tests removed until laboratory functionality is available --}}
</ul>
@elseif(isset($doctor))
<ul class="metismenu" id="menu">
    <li class="nav-label first">Doctor's Menu</li>
    <li class="{{ request()->routeIs('doctor.dashboard') ? 'mm-active' : '' }}">
        <a href="{{ route('doctor.dashboard', ['doctorId' => $doctor->id]) }}" aria-expanded="false">
            <i class="fa fa-dashboard"></i>
            <span class="nav-text">Dashboard</span>
        </a>
    </li>
    <li class="{{ request()->routeIs('doctor.appointments') ? 'mm-active' : '' }}">
        <a href="{{ route('doctor.appointments', ['doctorId' => $doctor->id]) }}" aria-expanded="false">
            <i class="fa fa-calendar"></i>
            <span class="nav-text">Appointments</span>
        </a>
    </li>
    <li class="{{ request()->routeIs('doctor.meeting-link') ? 'mm-active' : '' }}">
        <a href="{{ route('doctor.meeting-link', ['doctorId' => $doctor->id]) }}" aria-expanded="false">
            <i class="fa fa-video-camera"></i>
            <span class="nav-text">Meeting link</span>
        </a>
    </li>
    <li class="{{ request()->routeIs('doctor.availability') ? 'mm-active' : '' }}">
        <a href="{{ route('doctor.availability', ['doctorId' => $doctor->id]) }}" aria-expanded="false">
            <i class="fa fa-check"></i>
            <span class="nav-text">Availability</span>
        </a>
    </li>

</ul>
@endif

@php $currentUser = $user ?? auth()->user(); @endphp
@if($currentUser && ($currentUser->is_admin ?? false))
<ul class="metismenu mt-3" id="admin-menu">
    <li class="nav-label">Admin</li>
    <li>
        <a class="{{ request()->is('admin') ? 'mm-active' : '' }}" href="{{ route('admin.index') }}" aria-expanded="false">
            <i class="fa fa-cogs"></i>
            <span class="nav-text">Admin Home</span>
        </a>
    </li>
    <li>
        <a class="{{ request()->is('admin/doctors*') ? 'mm-active' : '' }}" href="{{ route('admin.model.index', 'doctors') }}">
            <i class="fa fa-user-md"></i>
            <span class="nav-text">Doctors</span>
        </a>
    </li>
    <li>
        <a class="{{ request()->is('admin/appointments*') ? 'mm-active' : '' }}" href="{{ route('admin.model.index', 'appointments') }}">
            <i class="fa fa-calendar"></i>
            <span class="nav-text">Appointments</span>
        </a>
    </li>
    <li>
        <a class="{{ request()->is('admin/students*') ? 'mm-active' : '' }}" href="{{ route('admin.model.index', 'students') }}">
            <i class="fa fa-users"></i>
            <span class="nav-text">Students</span>
        </a>
    </li>
    <li>
        <a class="{{ request()->is('admin/schools*') ? 'mm-active' : '' }}" href="{{ route('admin.model.index', 'schools') }}">
            <i class="fa fa-building"></i>
            <span class="nav-text">Schools</span>
        </a>
    </li>
    <li>
        <a class="{{ request()->is('admin/health-facilities*') ? 'mm-active' : '' }}" href="{{ route('admin.model.index', 'health-facilities') }}">
            <i class="fa fa-hospital-o"></i>
            <span class="nav-text">Health Facilities</span>
        </a>
    </li>
</ul>
@endif