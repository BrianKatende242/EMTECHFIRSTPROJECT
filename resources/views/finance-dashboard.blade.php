@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Finance Dashboard</h4>
                </div>
                <div class="card-body">
                    @if(isset($error))
                        <div class="alert alert-danger">
                            {{ $error }}
                        </div>
                    @else
                        <div class="row">
                            <div class="col-12">
                                <h5>Loan Data</h5>
                                @if(isset($loans) && count($loans) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($loans as $loan)
                                                    <tr>
                                                        <td>{{ $loan['id'] ?? 'N/A' }}</td>
                                                        <td>{{ $loan['amount'] ?? 'N/A' }}</td>
                                                        <td>{{ $loan['status'] ?? 'N/A' }}</td>
                                                        <td>{{ $loan['created_at'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p>No loan data available.</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection