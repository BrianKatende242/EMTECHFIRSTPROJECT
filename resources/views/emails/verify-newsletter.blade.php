<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Verify Your Subscription</title>
	<style>
		/* Basic email-safe styles */
		body { margin:0; padding:0; background:#0f172a; color:#0b1220; }
		.container { width:100%; background:#0f172a; padding:24px 0; }
		.card { max-width:620px; margin:0 auto; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 10px 30px rgba(2,6,23,0.25); }
		.header { background:linear-gradient(135deg,#0ea5e9,#22c55e); padding:28px 32px; text-align:center; }
		.header img { height:40px; }
		.content { padding:28px 32px; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; }
		h1 { font-size:22px; margin:0 0 8px; color:#0b1220; }
		p { color:#334155; line-height:1.6; margin:0 0 16px; }
		.btn { display:inline-block; background:#16a34a; color:#ffffff !important; text-decoration:none; padding:12px 18px; border-radius:10px; font-weight:600; }
		.btn:hover { background:#15803d; }
		.footer { padding:20px 32px 28px; color:#94a3b8; font-size:12px; }
		.muted { color:#64748b; font-size:13px; }
		.spacer { height:4px; background:linear-gradient(90deg,#0ea5e9,#22c55e); }
	</style>
</head>
<body>
	<div class="container">
		<div class="card">
			<div class="header">
				<img src="{{ asset('images/emoji-logo-white.svg') }}" alt="{{ config('app.name') }}">
			</div>
			<div class="content">
				<h1>Verify your subscription</h1>
				<p>Thanks for subscribing to our newsletter! Please confirm your email address to start receiving updates.</p>

				<p style="text-align:center; margin:24px 0;">
					<a href="{{ $verificationUrl }}" class="btn" target="_blank" rel="noopener">Verify Email Address</a>
				</p>

				<p class="muted">If the button doesn’t work, copy and paste this link into your browser:</p>
				<p class="muted" style="word-break:break-all;">{{ $verificationUrl }}</p>

				<p class="muted">If you didn’t request this, you can safely ignore this email.</p>
			</div>
			<div class="spacer"></div>
			<div class="footer">
				Sent by {{ config('app.name') }} • {{ config('app.url') }}
			</div>
		</div>
	</div>
</body>
</html>
