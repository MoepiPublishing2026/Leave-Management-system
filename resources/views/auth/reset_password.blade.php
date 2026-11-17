<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-48.png') }}?v=3" type="image/png" sizes="48x48" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" />
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
            max-width: 600px;
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
            background: linear-gradient(90deg, #d4e21a 0%, #9ef755 100%);
        }
        .login-logo { text-align: center; margin-bottom: 20px; }
        .login-logo img { height: 170px; width: auto; max-width: 100%; }
        h2 { text-align: center; margin-bottom: 25px; font-size: 28px; color: #2d3748; }
        
        .form-group { margin-bottom: 20px; width: 100%; }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #4a5568;
            text-transform: uppercase;
        }
        
        /* Fixed input styling */
        .input-wrapper { position: relative; width: 100%; }
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            display: block;
            padding: 14px 45px 14px 16px;
            font-size: 16px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            color: #2d3748;
            background: #ffffff;
            transition: all 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: #d4e21a;
            background: white;
            box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.1);
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            width: 22px;
            height: 22px;
            transition: color 0.3s ease;
        }
        .toggle-password:hover svg {
            stroke: #d4e21a;
        }
        .toggle-password svg {
            width: 22px;
            height: 22px;
            stroke: #d4e21a;
            transition: stroke 0.3s ease;
        }
        
        .error { color: #e53e3e; font-size: 13px; margin-top: 6px; }
        
        button {
            width: 100%;
            padding: 14px;
            font-weight: 600;
            font-size: 15px;
            border-radius: 10px;
            cursor: pointer;
            border: none;
            background: linear-gradient(135deg, #d4e21a 0%, #9ef755 100%);
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

        .links {
            margin-top: 18px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .links a {
            color: #d4e21a;
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
            background: #d4e21a;
            transition: width 0.3s ease;
        }

        .links a:hover::after {
            width: 100%;
        }

        .links a:hover {
            color: #9ef755;
        }

        .branding {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            color: #a0aec0;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/ui-consistency.css') }}" />
</head>
<body>
    <div class="container auth-card">
        <div class="login-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
        </div>
        <h2 class="auth-title">Reset Password</h2>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}" />

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required autofocus />
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" required />
                    <span class="toggle-password" id="togglePassword1">
                        <svg id="eyeOpen1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                            <circle cx="12" cy="12" r="3" stroke-width="2" />
                        </svg>
                        <svg id="eyeClosed1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                        </svg>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" required />
                    <span class="toggle-password" id="togglePassword2">
                        <svg id="eyeOpen2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                            <circle cx="12" cy="12" r="3" stroke-width="2" />
                        </svg>
                        <svg id="eyeClosed2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                        </svg>
                    </span>
                </div>
            </div>

            <button type="submit">Reset Password</button>
        </form>

        <div class="links"><a href="{{ route('login') }}">Back to Login</a></div>

        <div class="branding">
            &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
        </div>
    </div>

    <script>
        function setupToggle(inputId, toggleId, openId, closedId) {
            document.querySelector(`#${toggleId}`).addEventListener('click', () => {
                const input = document.querySelector(`#${inputId}`);
                const open = document.querySelector(`#${openId}`);
                const closed = document.querySelector(`#${closedId}`);
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                open.style.display = isPass ? 'none' : 'block';
                closed.style.display = isPass ? 'block' : 'none';
            });
        }
        setupToggle('password', 'togglePassword1', 'eyeOpen1', 'eyeClosed1');
        setupToggle('password_confirmation', 'togglePassword2', 'eyeOpen2', 'eyeClosed2');
    </script>
</body>
</html>