@extends('layouts.base')

@section('content')
    <div class="tab-pane fade show active" id="students">
        <div class="d-flex justify-content-between mb-3">
            <h2>Student Management</h2>
            <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#newStudentModal"><i class="fa fa-plus"></i> New Student</button>
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
        <div class="table-responsive mt-4">
            <table class="table table-hover text-dark">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Grade</th>
                        <th>Age</th>
                        <th>Parent Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->grade }}</td>
                        <td>{{ \Carbon\Carbon::parse($student->birth_date)->age }}</td>
                        <td>{{ $student->parent_contact ?? 'N/A' }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger">
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
@endsection