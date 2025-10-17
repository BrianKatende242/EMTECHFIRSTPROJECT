@extends('layouts.auth')

@section('title','Admin Login')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header text-white py-4 d-flex flex-column align-items-center">
                    <img src="{{ asset('images/emoji-logo-white.svg') }}" alt="KETI AI" style="height: 40px; width: auto;">
                    <h1 class="mt-2 fw-bold display-7">Admin Portal</h1>
                </div>

                <div class="card-body p-5">
                    @if(session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form id="login-form" method="POST" action="{{ route('login.post') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                       placeholder="Enter your email">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="current-password"
                                       placeholder="Enter your password">
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <a class="text-decoration-none" href="{{ route('password.request') }}">
                                Forgot your password?
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-footer text-center py-3 bg-light">
                    <small class="text-muted">© {{ date('Y') }} KETI AI. All rights reserved.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
    submitButton.disabled = true;

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '/admin';
        } else {
            // Show errors
            let errorHtml = '';
            if (data.errors) {
                for (let field in data.errors) {
                    let errorMsg = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                    errorHtml += '<div class="alert alert-danger">' + errorMsg + '</div>';
                }
            } else if (data.message) {
                errorHtml = '<div class="alert alert-danger">' + data.message + '</div>';
            }
            const existingAlert = document.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            const form = document.getElementById('login-form');
            form.insertAdjacentHTML('afterbegin', errorHtml);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorHtml = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) existingAlert.remove();
        const form = document.getElementById('login-form');
        form.insertAdjacentHTML('afterbegin', errorHtml);
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
});
</script>
@endsection
