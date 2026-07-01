<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Password Updated - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" sizes="32x32" />
    <link rel="stylesheet" href="{{ asset('css/ui-consistency.css') }}" />
    <style>
        html, body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 520px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 10px #a7bd8dff;
            text-align: center;
        }
        h2 {
            margin-bottom: 12px;
            color: #2d3748;
        }
        p {
            color: #4a5568;
            line-height: 1.6;
        }
        a {
            color: #b2cd16;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password updated</h2>
        <p>Your password has been reset successfully. You can now sign in with your new password.</p>
        <p id="redirect-note">Returning you to the login page…</p>
        <p><a href="{{ route('login') }}?password_reset=1" id="login-link">Continue to login</a></p>
    </div>

    <script>
        (function () {
            const loginUrl = @json(route('login') . '?password_reset=1');

            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.location.href = loginUrl;
                    window.opener.focus();
                    document.getElementById('redirect-note').textContent = 'Returning you to your original login tab…';
                    setTimeout(function () {
                        window.close();
                    }, 1200);
                    return;
                } catch (error) {
                }
            }

            window.setTimeout(function () {
                window.location.href = loginUrl;
            }, 1500);
        })();
    </script>
</body>
</html>
