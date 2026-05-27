<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Two-Factor Authentication - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" sizes="32x32" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}" sizes="180x180" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html,
        body {
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
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 10px #a7bd8dff;
            position: relative;
            overflow: hidden;
        }
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #b2cd16 0%, #9ef755 100%);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo img {
            height: 170px;
            width: auto;
            max-width: 100%;
        }
        h2 {
            text-align: center;
            margin-bottom: 18px;
            font-size: 28px;
            color: #2d3748;
            font-weight: 600;
        }
        p {
            text-align: center;
            color: #64748b;
            margin-bottom: 22px;
            font-size: 14px;
            line-height: 1.4;
        }
        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            font-size: 18px;
            letter-spacing: 5px;
            text-align: center;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            color: #2d3748;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: #b2cd16;
            background: white;
            box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.1);
        }
        button {
            width: 100%;
            padding: 14px;
            font-weight: 600;
            font-size: 15px;
            border-radius: 10px;
            cursor: pointer;
            border: none;
            background: linear-gradient(135deg, #b2cd16 0%, #9ef755 100%);
            color: white;
            margin-top: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }
        button:hover::before { left: 100%; }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(178, 205, 22, 0.3);
        }
        button:active { transform: translateY(0); }
        .links {
            margin-top: 18px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .links a {
            color: #b2cd16;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
        }
        .links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #b2cd16;
            transition: width 0.3s ease;
        }
        .links a:hover::after { width: 100%; }
        .links a:hover { color: #9ef755; }

        .branding {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            color: #a0aec0;
        }
        @media (max-width: 480px) {
            .container { padding: 30px 22px; }
            h2 { font-size: 24px; }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/ui-consistency.css') }}" />
</head>
<body>
    <div class="container">
        <div class="login-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}" alt="Moepi Publishing Logo" />
        </div>
        <h2 class="auth-title">Two-Factor Authentication</h2>
        <p>A verification code has been sent to your email. Please enter it below to continue.</p>

        <form method="POST" action="{{ route('login.verify.post') }}">
            @csrf
            <div class="form-group">
                <label for="code">Verification Code</label>
                <input type="text" name="code" id="code" required autofocus maxlength="6" autocomplete="off" />
            </div>

            <button type="submit">Verify</button>
        </form>

        <div class="links">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
        
        <div class="branding">
            &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
        </div>
    </div>

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Verification Failed',
                html: '<ul style="text-align:left;">' + @json($errors->all()).map(e => '<li>' + e + '</li>').join('') + '</ul>',
                confirmButtonText: 'OK',
                width: '30em',
                confirmButtonColor: '#b2cd16'
            });
        </script>
    @endif
</body>
</html>
