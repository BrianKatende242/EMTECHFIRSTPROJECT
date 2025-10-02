<ul class="metismenu" id="menu">
    <li class="nav-label first">Main Menu</li>
    <li>
        <a class="{{ request()->is('dashboard*') ? 'mm-active' : '' }}" href="{{ route('school.dashboard', ['school' => $school]) }}" aria-expanded="false">
                <i class="fa fa-home"></i>
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