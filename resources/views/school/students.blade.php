@extends('layouts.base')

@section('content')
    <div class="tab-pane fade show active" id="students">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger mb-3" id="deleteErrorAlert">
                {{ session('error') }}
            </div>
            <script>
                setTimeout(function() {
                    var alert = document.getElementById('deleteErrorAlert');
                    if (alert) {
                        alert.style.display = 'none';
                    }
                }, 3000);
            </script>
        @endif

        @if(session('success'))
            <div class="alert alert-success mb-3" id="successAlert">
                {{ session('success') }}
            </div>
            <script>
                setTimeout(function() {
                    var alert = document.getElementById('successAlert');
                    if (alert) {
                        alert.style.display = 'none';
                    }
                }, 3000);
            </script>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">Student Management</h2>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newStudentModal"><i class="fa fa-plus"></i> Register</button>
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#newStudentModal"><i class="mdi mdi-cloud-upload"></i> <span>Import</span></button>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fa fa-filter"></i> Filters
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('students', ['school' => $school->id]) }}" id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search">Search by Name:</label>
                                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Enter student name">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="grade">Grade:</label>
                                        <select class="form-control" id="grade" name="grade">
                                            <option value="">All Grades</option>
                                            @php
                                                $allGrades = $school->students()->pluck('grade')->unique()->filter()->sort();
                                            @endphp
                                            @foreach($allGrades as $gradeOption)
                                                <option value="{{ $gradeOption }}" {{ request('grade') == $gradeOption ? 'selected' : '' }}>{{ $gradeOption }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="gender">Gender:</label>
                                        <select class="form-control" id="gender" name="gender">
                                            <option value="">All Genders</option>
                                            <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="min_age">Min Age:</label>
                                        <input type="number" class="form-control" id="min_age" name="min_age" value="{{ request('min_age') }}" min="1" max="25" placeholder="Min age">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="max_age">Max Age:</label>
                                        <input type="number" class="form-control" id="max_age" name="max_age" value="{{ request('max_age') }}" min="1" max="25" placeholder="Max age">
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fa fa-search"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @if(request()->hasAny(['search', 'grade', 'gender', 'min_age', 'max_age']))
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <a href="{{ route('students', ['school' => $school->id]) }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fa fa-times"></i> Clear Filters
                                        </a>
                                        <small class="text-muted ml-2">
                                            Showing {{ $students->count() }} of {{ $school->students()->count() }} students
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

        <!-- New Student Modal -->
        <div class="modal fade" id="newStudentModal" tabindex="-1" role="dialog" aria-labelledby="newStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newStudentModalLabel">New Student</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('students.create') }}" method="POST">
                            @csrf
                            
                            <!-- Patient Type Selection -->
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Patient Type:</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="patient_type" id="new_patient" value="new" checked>
                                    <label class="form-check-label" for="new_patient">
                                        New Student
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="patient_type" id="existing_patient" value="existing">
                                    <label class="form-check-label" for="existing_patient">
                                        Existing Patient (Enter Patient ID)
                                    </label>
                                </div>
                            </div>

                            <!-- Existing Patient Section -->
                            <div id="existingPatientSection" style="display: none;">
                                <div class="form-group">
                                    <label for="patient_id">Patient ID:</label>
                                    <input type="text" class="form-control" id="patient_id" name="patient_id" placeholder="e.g., P000001">
                                    <small class="form-text text-muted">Enter the existing patient's ID to associate them with this school.</small>
                                </div>
                            </div>

                            <!-- New Patient Section -->
                            <div id="newPatientSection">
                                <div class="form-group">
                                    <label for="name">Name:</label>
                                    <input type="text" class="form-control" id="name" name="name">
                                </div>
                                <div class="form-group">
                                    <label for="gender">Gender:</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="grade">Grade:</label>
                                    <input type="text" class="form-control" id="grade" name="grade">
                                </div>
                                <div class="form-group">
                                    <label for="parent_contact">Parent Contact:</label>
                                    <input type="text" class="form-control" id="parent_contact" name="parent_contact">
                                </div>
                                <div class="form-group">
                                    <label for="birth_date">Birth Date:</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date">
                                </div>
                            </div>
                            
                            <input type="text" name="school_id" value="{{ $school->id }}" hidden>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($students->count() > 0)
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-hover align-middle text-dark">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Grade</th>
                            <th>Age</th>
                            <th>Parent Contact</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td class="text-center">{{ $student->id }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ ucfirst($student->gender ?? 'N/A') }}</td>
                            <td>{{ $student->grade }}</td>
                            <td>{{ \Carbon\Carbon::parse($student->birth_date)->age }}</td>
                            <td>{{ $student->parent_contact ?? 'N/A' }}</td>
                            <td class="text-center">
                                <a href="{{ route('patients.profile', ['patient' => $student->id]) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fa fa-user"></i> Profile
                                </a>
                                <form method="POST" action="{{ route('students.delete', ['school' => $school->id, 'student' => $student->id]) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete {{ $student->name }}?')">
                                        <i class="mdi mdi-delete"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="alert alert-info text-dark">
                No students found for this school.
            </div>
        @endif
            </div>
        </div>
@endsection