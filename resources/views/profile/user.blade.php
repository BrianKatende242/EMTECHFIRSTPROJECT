@extends('layouts.base')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <h4>{{ $user->name ?? 'Profile' }}</h4>
                <p class="text-muted">{{ $user->email ?? '' }}</p>

                <dl class="row">
                    <dt class="col-sm-3">Role</dt>
                    <dd class="col-sm-9">{{ ($user->is_admin ?? false) ? 'Admin' : 'User' }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
