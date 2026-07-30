<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title }}</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background:#f8fafc; color:#0f172a; padding:40px 20px; }
        .card {
            max-width:520px; margin:0 auto; background:#fff; border-radius:12px; padding:32px 28px;
            border:1px solid #e2e8f0; text-align:center;
        }
        .tick {
            width:72px; height:72px; margin:0 auto 18px; border-radius:50%;
            background: #dcfce7; color:#166534; display:flex; align-items:center; justify-content:center;
        }
        .tick svg { width:40px; height:40px; }
        .tick--error { background:#fee2e2; color:#991b1b; }
        h1 { font-size:1.4rem; margin:0 0 12px; color: {{ !empty($ok) ? '#166534' : '#991b1b' }}; }
        .status-pill {
            display:inline-block; margin:4px 0 14px; padding:6px 14px; border-radius:999px;
            font-weight:700; font-size:0.9rem; background:#ecfdf5; color:#166534;
        }
        p { line-height:1.5; color:#475569; margin:0 0 12px; }
        a { color:#0a1347; font-weight:700; }
    </style>
</head>
<body>
@php
    $submitted = !empty($submitted);
    $ok = !empty($ok);
@endphp
    <div class="card">
        @if ($submitted || $ok)
            <div class="tick" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
            </div>
        @else
            <div class="tick tick--error" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </div>
        @endif

        <h1>{{ $title }}</h1>
        @if (!empty($finalStatus))
            <div class="status-pill">{{ $finalStatus }}</div>
        @endif
        <p>{{ $message }}</p>
        @if ($submitted)
            <p><strong>Submitted</strong> — you can close this page.</p>
        @endif
        <p><a href="{{ url('/login') }}">Sign in to the Leave Management System</a></p>
    </div>
</body>
</html>
