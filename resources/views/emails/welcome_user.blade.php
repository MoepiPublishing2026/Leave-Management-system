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
        ul {
            background-color: #f7fcef;
            border: 1px solid #b2d12f;
            max-width: 320px;
            padding: 15px 25px;
            border-radius: 8px;
            list-style-type: none;
            color: #4a7523;
            font-weight: 600;
        }
        ul li {
            margin-bottom: 8px;
            font-size: 16px;
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
            transition: background-color 0.3s ease;
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
    <ul>
        <li><b>Email:</b> {{ $user->email }}</li>
        <li><b>Password:</b> {{ $rawPassword }}</li>
    </ul>

    <a href="{{ url('/login') }}" class="login-button">Log In</a>

    <p>Thank you!</p>

    <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">&copy; {{ date('Y') }} Moepi Publishing. All rights reserved.</p>
</body>
</html>
