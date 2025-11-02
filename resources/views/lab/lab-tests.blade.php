@extends('layouts.base')

@section('content')
    <div class="d-flex justify-content-between mb-4">
        <h2>Lab Test Requests</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLabTestModal">
            <i class="fa fa-plus me-2"></i> New Request
        </button>
    </div>

    @if($labTests->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped text-dark">
            <thead class="table-dark">
                <tr>
                    <th>Request Date</th>
                    <th>Student</th>
                    <th>Test Type</th>
                        <th>Status</th>
                        <th>Results</th>
                        <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($labTests as $labTest)
                <tr>
                    <td>{{ $labTest->created_at->format('M d, Y') }}</td>
                    <td>{{ $labTest->patient->name }}</td>
                    <td>{{ $labTest->test_type }}</td>
                    <td>
                        <span class="badge bg-{{ 
                            $labTest->status == 'completed' ? 'success' : 
                            ($labTest->status == 'processing' ? 'warning' : 'secondary') 
                        }} text-white">
                            {{ ucfirst($labTest->status) }}
                        </span>
                    </td>
                    <td>
                                        @if($labTest->results)
                                            <a href="#" class="btn btn-sm btn-info">View</a>
                                        @else
                                            <span class="text-muted">Pending</span>
                                        @endif
                    </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('lab-tests.destroy', ['school' => $school->id, 'labTest' => $labTest->id]) }}" method="POST" onsubmit="return confirm('Delete this lab test request?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">
            {{ $labTests->links() }}
        </div>
    </div>
    @else
    <div class="alert alert-info text-dark">
        No lab tests found.
    </div>
    @endif

    <!-- Lab Test Modal -->
    <div class="modal fade" id="newLabTestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Lab Test</h5>
                    <button type="button" class="btn" data-bs-dismiss="modal"><i class="fa fa-times"></i></button>
                </div>
                <form id="labtest-form" action="{{ route('lab-tests.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="school_id" value="{{ $school->id }}">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <select name="student_id" class="form-control form-select" required>
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Test Type</label>
                            <select name="test_type" class="form-control form-select" required>
                                <option value="">Select Test</option>
                                <option value="Blood Test">Blood Test</option>
                                <option value="Urine Test">Urine Test</option>
                                <option value="X-Ray">X-Ray</option>
                                <option value="Allergy Test">Allergy Test</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection