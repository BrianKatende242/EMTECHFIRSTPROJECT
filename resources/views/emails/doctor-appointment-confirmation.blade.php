<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        h1 {
            color: #28a745;
            margin: 20px 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .appointment-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .detail-row {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Appointment Confirmed</h1>
        </div>

        <div class="content">
            <p>Hello Dr. {{ $doctor->name }},</p>

            <div class="success-message">
                <strong>Great news!</strong> Payment has been successfully received for your appointment. The appointment is now confirmed and scheduled.
            </div>

            <div class="appointment-details">
                <h3 style="margin-top: 0; color: #28a745;">Appointment Details</h3>

                <div class="detail-row">
                    <span class="detail-label">Patient:</span> {{ $patient->name }}
                </div>

                <div class="detail-row">
                    <span class="detail-label">Appointment Type:</span> {{ $doctor->specialization === 'General Practitioner' ? 'General Consultation' : 'Specialist Consultation' }}
                </div>

                <div class="detail-row">
                    <span class="detail-label">Duration:</span> {{ $appointment->duration->minutes }} minutes
                </div>

                <div class="detail-row">
                    <span class="detail-label">Date & Time:</span> {{ $appointment->appointment_time->format('l, F j, Y \a\t g:i A') }}
                </div>

                <div class="detail-row">
                    <span class="detail-label">Reason:</span> {{ $appointment->reason }}
                </div>

                @if($institution)
                <div class="detail-row">
                    <span class="detail-label">Institution:</span> {{ $institution->name }}
                </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Appointment ID:</span> #{{ $appointment->id }}
                </div>
            </div>

            <p>Please be prepared for this appointment. If you need to reschedule or have any questions, you can manage this appointment through your doctor dashboard.</p>

            <p>Thank you for your service!</p>
            <p>Best regards,<br>The {{ config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>This is an automated notification for appointment confirmation.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>