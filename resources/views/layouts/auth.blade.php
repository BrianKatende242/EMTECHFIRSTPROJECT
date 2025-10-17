<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login')</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ asset('images/auth_bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background-color: transparent;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .card-body {
            background-color: rgba(255, 255, 255, 0.75);
        }
        .card-header {
            background: #CC00C6 !important;
            border: none;
        }
        .btn-primary {
            background: #CC00C6 !important;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #CC00C6 !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(204, 0, 198, 0.3);
        }
        .form-control:focus {
            border-color: #CC00C6;
            box-shadow: 0 0 0 0.2rem rgba(204, 0, 198, 0.25);
        }
        .form-label {
            color: #CC00C6;
        }
        .form-check-label {
            color: #CC00C6;
        }
        .form-check-input {
            accent-color: #CC00C6;
        }
        .form-check-input:checked {
            background-color: #CC00C6 !important;
            border-color: #CC00C6 !important;
        }
        .card-body a {
            color: #CC00C6;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #CC00C6;
        }
        .input-group-text i {
            color: #CC00C6 !important;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('vendor/global/global.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
