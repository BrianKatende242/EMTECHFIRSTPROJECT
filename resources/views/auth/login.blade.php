@extends('layouts.auth')

@section('title','Admin Login')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card shadow-lg border-0 rounded-3">
                    <!-- Header -->
                    <div class="card-header bg-primary text-white text-center py-4">
                        <div class="mb-3">
                            <img src="{{ asset('images/emoji-logo-black.svg') }}" alt="KETI AI" class="img-fluid" style="height: 50px; width: auto;">
                        </div>
                        <h2 class="h4 mb-0 fw-bold">Admin Portal</h2>
                        <p class="mb-0 opacity-75">Sign in to access your dashboard</p>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-4 p-lg-5 py-5">
                        <!-- Status Message -->
                        @if(session('status'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                                <i class="mdi mdi-check-circle me-2"></i>{{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Login Form -->
                        <form id="loginForm" method="POST" action="{{ route('login.post') }}" novalidate>
                            @csrf

                            <!-- Email Field -->
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold text-muted mb-3">Email Address</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-light">
                                        <i class="mdi mdi-email text-muted"></i>
                                    </span>
                                    <input type="email"
                                           id="email"
                                           name="email"
                                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}"
                                           placeholder="Enter your email address"
                                           required
                                           autocomplete="email"
                                           autofocus>
                                    @error('email')
                                        <div class="invalid-feedback d-block">
                                            <i class="mdi mdi-alert-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Password Field -->
                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold text-muted mb-3">Password</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-light">
                                        <i class="mdi mdi-lock text-muted"></i>
                                    </span>
                                    <input type="password"
                                           id="password"
                                           name="password"
                                           class="form-control form-control-lg @error('password') is-invalid @enderror"
                                           placeholder="Enter your password"
                                           required
                                           autocomplete="current-password">
                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            <i class="mdi mdi-alert-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-5">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="remember"
                                           id="remember"
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <a href="{{ route('password.request') }}" class="text-decoration-none text-primary fw-semibold">
                                    Forgot password?
                                </a>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-4">
                                <button type="submit" id="loginBtn" class="btn btn-primary btn-lg w-100 fw-bold rounded-3 py-3">
                                    <span class="btn-text">
                                        <i class="mdi mdi-login me-2"></i>Sign In
                                    </span>
                                    <span class="btn-loading d-none">
                                        <i class="mdi mdi-loading mdi-spin me-2"></i>Signing In...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted">
                            <i class="mdi mdi-shield me-1"></i>
                            © {{ date('Y') }} KETI AI. Secure admin access only.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = loginBtn.querySelector('.btn-text');
    const btnLoading = loginBtn.querySelector('.btn-loading');

    // Form validation
    const inputs = loginForm.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });

    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';

        if (field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            isValid = emailRegex.test(value);
            message = isValid ? '' : 'Please enter a valid email address.';
        } else if (field.type === 'password') {
            isValid = value.length >= 1;
            message = isValid ? '' : 'Password is required.';
        }

        field.classList.toggle('is-invalid', !isValid);
        field.classList.toggle('is-valid', isValid && value.length > 0);

        const feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = message;
        }

        return isValid;
    }

    // Form submission
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validate all fields
        let isFormValid = true;
        inputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            return;
        }

        // Show loading state
        loginBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        // Clear previous errors
        document.querySelectorAll('.alert').forEach(alert => alert.remove());

        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Success - redirect
                window.location.href = data.redirect || '/admin';
            } else {
                // Show errors
                showErrors(data.errors || data.message);
            }
        } catch (error) {
            console.error('Login error:', error);
            showErrors('An unexpected error occurred. Please try again.');
        } finally {
            // Reset loading state
            loginBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
        }
    });

    function showErrors(errors) {
        let errorHtml = '';

        if (typeof errors === 'string') {
            errorHtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert me-2"></i>${errors}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        } else if (typeof errors === 'object') {
            errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="mb-0">';
            for (const field in errors) {
                const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                messages.forEach(message => {
                    errorHtml += `<li><i class="mdi mdi-alert-circle me-1"></i>${message}</li>`;
                });
            }
            errorHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }

        if (errorHtml) {
            loginForm.insertAdjacentHTML('afterbegin', errorHtml);
        }
    }

    // Auto-focus first empty field
    const firstEmptyField = Array.from(inputs).find(input => !input.value);
    if (firstEmptyField) {
        firstEmptyField.focus();
    }
});
</script>
@endsection
