@extends('layouts.base')

@section('title', $item ? 'Edit Duration' : 'Create Duration')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $item ? 'Edit Duration' : 'Create Duration' }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.durations.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ $item ? route('admin.durations.update', $item->id) : route('admin.durations.store') }}">
                    @csrf
                    @if($item)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="minutes">Minutes <span class="text-danger">*</span></label>
                                    <input type="number" name="minutes" id="minutes" class="form-control @error('minutes') is-invalid @enderror"
                                           value="{{ old('minutes', $item->minutes ?? '') }}" min="1" required>
                                    @error('minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration_type">Duration Type <span class="text-danger">*</span></label>
                                    <select name="duration_type" id="duration_type" class="form-control @error('duration_type') is-invalid @enderror" required>
                                        <option value="general" {{ old('duration_type', $item->duration_type ?? 'general') === 'general' ? 'selected' : '' }}>General</option>
                                        <option value="specialist" {{ old('duration_type', $item->duration_type ?? '') === 'specialist' ? 'selected' : '' }}>Specialist</option>
                                    </select>
                                    @error('duration_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Price (UGX) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">UGX</span>
                                        </div>
                                        <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror"
                                               value="{{ old('price', $item->price ?? '') }}" step="0.01" min="0" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="is_active"
                                       {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">Inactive durations won't be available for new appointments</small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="btn-group" role="group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ $item ? 'Update' : 'Create' }} Duration
                            </button>
                            <a href="{{ route('admin.durations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection