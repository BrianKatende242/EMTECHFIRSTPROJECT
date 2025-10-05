@extends('layouts.base')

@section('content')
<div class="container py-4">
    <h2>Admin</h2>
    <div class="list-group mt-3">
        @foreach($models as $key => $label)
            <a href="{{ route('admin.model.index', $key) }}" class="list-group-item list-group-item-action">{{ $label }}</a>
        @endforeach
    </div>
</div>
@endsection
