<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribed</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 2rem; color: #111; }
        .card { max-width: 560px; margin: 10vh auto; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
        h1 { font-size: 1.25rem; margin: 0 0 0.5rem; }
        p { color: #374151; }
        .muted { color: #6b7280; font-size: 0.9rem; }
        a { color: #2563eb; text-decoration: none; }
    </style>
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta property="og:title" content="You have been unsubscribed" />
    <meta property="og:description" content="Newsletter preferences updated." />
</head>
<body>
    <div class="card">
        <h1>You're unsubscribed</h1>
        <p>
            @if(!empty($email))
                We've updated your newsletter preferences for <strong>{{ $email }}</strong>.
            @else
                We've updated your newsletter preferences.
            @endif
        </p>
        <p class="muted">If this address wasn't subscribed, no action was taken.</p>
        <p class="muted">You can resubscribe any time on our website.</p>
    </div>
</body>
</html>
