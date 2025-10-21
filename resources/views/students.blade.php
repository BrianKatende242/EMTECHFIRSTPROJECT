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
                                <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteStudentModal" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-dark">
                No students found for this school.
            </div>
        @endif
            </div>
        </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteStudentModalLabel">Delete Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <span id="studentName"></span>?</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteStudentForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        var deleteStudentModal = document.getElementById('deleteStudentModal');
        deleteStudentModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var studentId = button.getAttribute('data-student-id');
            var studentName = button.getAttribute('data-student-name');
            var modalStudentName = deleteStudentModal.querySelector('#studentName');
            var form = deleteStudentModal.querySelector('#deleteStudentForm');
            modalStudentName.textContent = studentName;
            form.action = '/students/' + studentId + '/delete'; // Adjust route as needed
        });

        // Toggle patient type sections
        document.querySelectorAll('input[name="patient_type"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var newPatientSection = document.getElementById('newPatientSection');
                var existingPatientSection = document.getElementById('existingPatientSection');
                var newPatientFields = newPatientSection.querySelectorAll('input, select');
                var existingPatientFields = existingPatientSection.querySelectorAll('input');

                if (this.value === 'existing') {
                    newPatientSection.style.display = 'none';
                    existingPatientSection.style.display = 'block';
                    
                    // Remove required attribute from new patient fields
                    newPatientFields.forEach(function(field) {
                        field.removeAttribute('required');
                    });
                    
                    // Add required to patient_id field
                    document.getElementById('patient_id').setAttribute('required', 'required');
                } else {
                    newPatientSection.style.display = 'block';
                    existingPatientSection.style.display = 'none';
                    
                    // Add required attribute to new patient fields
                    newPatientFields.forEach(function(field) {
                        if (field.name !== 'school_id') { // Don't make hidden fields required
                            field.setAttribute('required', 'required');
                        }
                    });
                    
                    // Remove required from patient_id field
                    document.getElementById('patient_id').removeAttribute('required');
                }
            });
        });
    </script>
@endsection