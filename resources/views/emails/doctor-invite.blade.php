<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Registration Invite - {{ config('app.name') }}</title>
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
            color: #CC00C6;
            margin: 20px 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background-color: #CC00C6;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #a800a8;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Doctor Registration Invitation</h1>
        </div>

        <div class="content">
            <p>Hello,</p>

            <p>You have been invited to join <strong>{{ config('app.name') }}</strong> as a healthcare professional. This invitation allows you to provide medical consultations and manage patient appointments.</p>

            @if(isset($invite) && $invite->specialization)
            <div class="info-box">
                <strong>Specialization:</strong> {{ $invite->specialization }}<br>
                @if($invite->school)
                <strong>Institution:</strong> {{ $invite->school->name }} (School)
                @elseif($invite->healthFacility)
                <strong>Institution:</strong> {{ $invite->healthFacility->name }} (Health Facility)
                @endif
            </div>
            @endif

            <p>To complete your registration and set up your doctor account, please click the button below:</p>

            <div style="text-align: center;">
                <a href="{{ $inviteUrl }}" style="display: inline-block; background-color: #CC00C6; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; text-align: center; margin: 20px 0;">Accept Invitation & Register</a>
            </div>

            <div class="warning">
                <strong>Important:</strong> This invitation link is valid for 24 hours and can only be used once. If you did not expect this invitation, please ignore this email.
            </div>

            <p>If the button above doesn't work, you can copy and paste this link into your browser:</p>
            <p style="word-break: break-all; background-color: #f8f8f8; padding: 10px; border-radius: 3px; font-family: monospace;">{{ $inviteUrl }}</p>

            <p>We look forward to having you join our healthcare network!</p>
            <p>Best regards,<br>The {{ config('app.name') }} Team</p>
        </div>

        <div class="footer">
            <p>This email was sent to you because you were invited to join {{ config('app.name') }} as a healthcare professional.</p>
            <p>If you have any questions, please contact our support team.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>