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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">Student Management</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newStudentModal"><i class="fa fa-plus"></i> New Student</button>
                </div>

        <!-- New Student Modal -->
        <div class="modal fade" id="newStudentModal" tabindex="-1" role="dialog" aria-labelledby="newStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newStudentModalLabel">New Student</h5>
                        <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('students.create') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" required class="form-control" id="name" name="name">
                            </div>
                            <div class="form-group">
                                <label for="grade">Grade:</label>
                                <input type="text" required class="form-control" id="grade" name="grade">
                            </div>
                            <div class="form-group">
                                <label for="age">Age:</label>
                                <input type="number" required class="form-control" id="age" name="age">
                            </div>
                            <div class="form-group">
                                <label for="parent_contact">Parent Contact:</label>
                                <input type="text" required class="form-control" id="parent_contact" name="parent_contact">
                            </div>
                            <div class="form-group">
                                <label for="birth_date">Birth Date:</label>
                                <input type="date" required class="form-control" id="birth_date" name="birth_date">
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
                            <td>{{ $student->grade }}</td>
                            <td>{{ \Carbon\Carbon::parse($student->birth_date)->age }}</td>
                            <td>{{ $student->parent_contact ?? 'N/A' }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStudentModal" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <span id="studentName"></span>?</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteStudentForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
    </script>
@endsection