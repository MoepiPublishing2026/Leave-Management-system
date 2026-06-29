<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            color: #333333;
            padding: 20px;
        }
        h2 {
            color: #1b1e40;
            font-weight: 700;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
        }
        .login-details {
            background-color: #f7fcef;
            border: 1px solid #b2d12f;
            max-width: 100%;
            width: 100%;
            padding: 16px 20px;
            border-radius: 8px;
            color: #4a7523;
            font-weight: 600;
            box-sizing: border-box;
        }
        .login-details p {
            margin: 0 0 10px;
            font-size: 16px;
            line-height: 1.6;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .login-details p:last-child {
            margin-bottom: 0;
        }
        a.login-button {
            display: inline-block;
            background-color: #a2d109;
            padding: 12px 25px;
            margin-top: 20px;
            color: white !important;
            font-weight: 700;
            border-radius: 25px;
            text-decoration: none;
            font-size: 16px;
        }
        a.login-button:hover {
            background-color: #87b20a;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h2>Hello {{ $user->first_name }},</h2>

    <p>Welcome to our system!</p>

    <p>Your login details are as follows:</p>
    <div class="login-details">
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Password:</strong> {{ $rawPassword }}</p>
    </div>

    <a href="{{ url('/login') }}" class="login-button">Log In</a>

    <p>Thank you!</p>

    <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">&copy; {{ date('Y') }} Moepi Publishing. All rights reserved.</p>
</body>
</html>
