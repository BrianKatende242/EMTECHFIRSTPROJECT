{{-- resources/views/payment/cancel.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-warning" style="font-size: 4rem;"></i>
                        </div>
                        
                        <h2 class="text-warning mb-3">Payment Cancelled</h2>
                        
                        @if(isset($appointment))
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Appointment Not Confirmed</h5>
                            <hr>
                            <p>The appointment for <strong>{{ $appointment->patient->name }}</strong> with 
                            <strong>Dr. {{ $appointment->doctor->name }}</strong> has been cancelled due to incomplete payment.</p>
                        </div>
                        @endif
                        
                        <p class="text-muted mb-4">
                            Your payment was cancelled and no charges have been made to your account. 
                            You can try booking the appointment again.
                        </p>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ url()->previous() }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                            <button class="btn btn-outline-primary" onclick="history.back()">
                                <i class="fas fa-redo me-2"></i>Try Again
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>