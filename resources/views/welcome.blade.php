<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-48.png') }}?v=3" type="image/png" sizes="48x48" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=3" sizes="180x180" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .login-container {
            max-width: 600px;
            width: 100%;
            background: white;
            padding: 30px;
            border-radius: 20px;
             box-shadow: 0 0 10px #a7bd8dff;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
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
            margin-bottom: 30px;
        }

        .login-logo img {
            height: 200px;
            width: auto;
            max-width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 22px;
            font-size: 24px;
            color: #2d3748;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 45px 12px 14px;
            font-size: 15px;
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

        input::placeholder {
            color: #a0aec0;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            transition: color 0.3s ease;
            width: 22px;
            height: 22px;
        }

        .toggle-password:hover svg {
            stroke: #b2cd16;
        }

        .toggle-password svg {
            width: 22px;
            height: 22px;
            stroke: #b2cd16;
            transition: stroke 0.3s ease;
        }

        .error {
            color: #e53e3e;
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .error::before {
            content: '⚠';
            font-size: 14px;
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

        button:hover::before {
            left: 100%;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(178, 205, 22, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .extra-links {
            margin-top: 18px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .extra-links a {
            color: #b2cd16;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
        }

        .extra-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #b2cd16;
            transition: width 0.3s ease;
        }

        .extra-links a:hover::after {
            width: 100%;
        }

        .extra-links a:hover {
            color: #9ef755;
        }

        .branding {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            color: #a0aec0;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 26px 22px;
                max-width: 100%;
            }

            h2 {
                font-size: 24px;
            }

            .login-logo img {
                height: 80px;
            }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/ui-consistency.css') }}" />
</head>

<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
        </div>

        <h2 class="auth-title">Login</h2>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <input type="text" name="email" id="email_or_number" value="{{ old('email') }}"
                        placeholder="Enter your email" required autofocus />
                </div>
                @error('login_error')
                <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Enter your password" required />
                    <span class="toggle-password" id="togglePassword">
                        <!-- Default: Eye (Visible) -->
                        <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                            <circle cx="12" cy="12" r="3" stroke-width="2" />
                        </svg>

                        <!-- Hidden: Eye Slash -->
                        <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                        </svg>
                    </span>
                </div>
                @error('password')
                <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="extra-links">
            <a href="{{ route('password.request') }}">Forgot Password?</a>
        </div>

        <div class="branding">
            &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');
        const eyeOpen = document.querySelector('#eyeOpen');
        const eyeClosed = document.querySelector('#eyeClosed');

        togglePassword.addEventListener('click', () => {
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            eyeOpen.style.display = isPassword ? 'none' : 'block';
            eyeClosed.style.display = isPassword ? 'block' : 'none';
        });
    </script>

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                html: '<ul style="text-align:left; list-style-position: inside;">' + @json($errors->all()).map(e => '<li>' + e + '</li>').join('') + '</ul>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#b2cd16'
            });
        </script>
    @endif

    @if (session('status'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('status')),
                confirmButtonText: 'OK',
                confirmButtonColor: '#b2cd16'
            });
        </script>
    @endif
</body>

</html>
