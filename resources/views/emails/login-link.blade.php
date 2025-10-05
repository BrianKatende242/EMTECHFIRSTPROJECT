<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your login link</title>
  </head>
  <body style="font-family:system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color:#333;">
    <div style="max-width:600px;margin:0 auto;padding:24px;">
      <h2 style="margin-top:0">Hello {{ $doctor->name ?? 'Doctor' }},</h2>
      <p>You requested a one-time login link. Click the button below to sign in. This link will expire shortly and can only be used once.</p>

      <p style="text-align:center;margin:24px 0;">
        <a href="{{ $loginUrl }}" style="background:#0d6efd;color:#fff;padding:12px 18px;border-radius:6px;text-decoration:none;display:inline-block">Sign in to your account</a>
      </p>

      <p>If the button doesn't work, paste this URL into your browser:</p>
      <p style="word-break:break-all;font-size:13px;color:#555">{{ $loginUrl }}</p>

      <p style="margin-top:24px;font-size:13px;color:#777">If you didn't request this link, you can ignore this email.</p>

      <p style="margin-top:18px;color:#777">Thanks,<br>The Team</p>
    </div>
  </body>
  </html>
