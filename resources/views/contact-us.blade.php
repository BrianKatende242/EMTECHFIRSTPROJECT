@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Contact Submissions</h4>
                </div>
                <div class="card-body">
                    @if(isset($submissions) && count($submissions) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Message</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                        <tr>
                                            <td>{{ $submission->id }}</td>
                                            <td>{{ $submission->first_name }} {{ $submission->last_name }}</td>
                                            <td>{{ $submission->email }}</td>
                                            <td>{{ $submission->phone ?? 'N/A' }}</td>
                                            <td>{{ Str::limit($submission->message, 50) }}</td>
                                            <td>{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>No contact submissions found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection