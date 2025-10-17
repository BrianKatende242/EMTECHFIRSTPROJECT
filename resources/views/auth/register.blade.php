@extends('layouts.auth')

@section('title', 'Admin Registration')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header text-center">
                <h4 class="mb-0 text-white">Admin Registration</h4>
            </div>

            <div class="card-body">
                <!-- Alert container for AJAX responses -->
                <div id="alert-container" class="mb-3" style="display: none;">
                    <div id="alert-message" class="alert" role="alert"></div>
                </div>

                <form id="register-form" method="POST" action="{{ route('register') }}">
                    @csrf

                    @if(isset($invite))
                        <input type="hidden" name="invite_token" value="{{ $invite->token }}">
                    @endif

                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Name') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Enter your full name">
                        </div>
                        <div id="name-error" class="invalid-feedback d-block" style="display: none;"></div>
                        @error('name')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $email ?? '') }}" required autocomplete="email" {{ isset($email) ? 'readonly' : '' }} placeholder="Enter your email address">
                        </div>
                        <div id="email-error" class="invalid-feedback d-block" style="display: none;"></div>
                        @error('email')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Enter your password">
                        </div>
                        <div id="password-error" class="invalid-feedback d-block" style="display: none;"></div>
                        @error('password')
                            <div class="invalid-feedback d-block">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password">
                        </div>
                        <div id="password-confirm-error" class="invalid-feedback d-block" style="display: none;"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="register-btn" class="btn btn-primary btn-lg">
                            <span id="btn-text">
                                <i class="fas fa-user-plus me-2"></i>{{ __('Register') }}
                            </span>
                            <span id="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-2"></i>Registering...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    const submitBtn = document.getElementById('register-btn');
    const btnText = document.getElementById('btn-text');
    const btnLoading = document.getElementById('btn-loading');
    const alertContainer = document.getElementById('alert-container');
    const alertMessage = document.getElementById('alert-message');

    // Clear any existing server-side validation errors on page load
    clearErrors();

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear previous errors and alerts
        clearErrors();
        hideAlert();

        // Show loading state
        setLoading(true);

        // Get form data
        const formData = new FormData(form);

        // Send AJAX request
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json().catch(() => {
                console.error('Failed to parse JSON response');
                throw new Error('Invalid response format');
            });
        })
        .then(data => {
            console.log('Response data:', data);
            setLoading(false);

            if (data.success) {
                // Success - redirect or show success message
                showAlert('Registration successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 2000);
            } else {
                // Handle validation errors or general error messages
                if (data.errors) {
                    displayErrors(data.errors);
                } else if (data.message) {
                    showAlert(data.message, 'danger');
                } else {
                    showAlert('Registration failed. Please try again.', 'danger');
                }
            }
        })
        .catch(error => {
            setLoading(false);
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'danger');
        });
    });

    function setLoading(loading) {
        submitBtn.disabled = loading;
        btnText.style.display = loading ? 'none' : 'inline';
        btnLoading.style.display = loading ? 'inline' : 'none';
    }

    function showAlert(message, type) {
        alertMessage.textContent = message;
        alertMessage.className = `alert alert-${type}`;
        alertContainer.style.display = 'block';

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                hideAlert();
            }, 5000);
        }
    }

    function hideAlert() {
        alertContainer.style.display = 'none';
    }

    function displayErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`${field}-error`);
            if (errorElement) {
                errorElement.textContent = errors[field][0];
                errorElement.style.display = 'block';

                // Add is-invalid class to the input
                const input = document.getElementById(field);
                if (input) {
                    input.classList.add('is-invalid');
                }
            }
        });
    }

    function clearErrors() {
        // Hide all error messages
        const errorElements = document.querySelectorAll('.invalid-feedback');
        errorElements.forEach(element => {
            element.style.display = 'none';
        });

        // Remove is-invalid classes
        const invalidInputs = document.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
    }
});
</script>
@endpush
