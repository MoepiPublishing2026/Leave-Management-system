<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" sizes="32x32" />
    <link rel="icon" href="{{ asset('images/favicon-16.png') }}" type="image/png" sizes="16x16" />
    <link rel="icon" href="{{ asset('images/favicon-192.png') }}" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-512.png') }}" type="image/png" sizes="512x512" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}" sizes="180x180" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 700px;
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
        input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            font-size: 16px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            color: #2d3748;
            background: #f7fafc;
            transition: all 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: #b2cd16;
            background: white;
            box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.1);
        }
        .error {
            color: #e53e3e;
            font-size: 13px;
            margin-top: 6px;
        }
        button {
            width: 100%;
            padding: 16px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 10px;
            cursor: pointer;
            border: none;
            background: linear-gradient(135deg, #b2cd16 0%, #9ef755 100%);
            color: white;
            margin-top: 6px;
        }
        .links {
            margin-top: 18px;
            text-align: center;
        }
        .links a {
            color: #b2cd16;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
        }
        @media (max-width: 480px) {
            .container { padding: 30px 22px; }
            h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}" alt="Moepi Publishing Logo" />
        </div>
        <h2>Forgot Password</h2>
        <p>Enter your email address and we will send you a password reset link.</p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus />
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Send Reset Link</button>
        </form>

        <div class="links">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
        
        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #a0aec0;">
            &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
        </div>
    </div>

    @if (session('status') && session('reset_link'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Reset Link',
                html: '<a href=' + @json(session('reset_link')) + ' style="color:#234e52; font-weight:800; word-break:break-all;" target="_blank" rel="noopener">Open Reset Link</a>',
                confirmButtonText: 'OK'
            });
        </script>
    @elseif (session('status'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('status')),
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Please fix the following errors:',
                html: '<ul style="text-align:left;">' + @json($errors->all()).map(e => '<li>' + e + '</li>').join('') + '</ul>',
                confirmButtonText: 'OK',
                width: '40em'
            });
        </script>
    @endif
</body>
</html>
