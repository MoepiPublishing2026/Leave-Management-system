<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" sizes="32x32" />
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
            background: linear-gradient(90deg, #b2cd16 0%, #9ef755 100%);
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
            background: #f7fafc;
            transition: all 0.3s ease;
        }
        input:focus {
            outline: none;
            border-color: #b2cd16;
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
            stroke: #b2cd16;
        }
        .toggle-password svg {
            width: 22px;
            height: 22px;
            stroke: #b2cd16;
            transition: stroke 0.3s ease;
        }
        
        .error { color: #e53e3e; font-size: 13px; margin-top: 6px; }
        
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
            margin-top: 10px;
        }
        .links { margin-top: 18px; text-align: center; }
        .links a { color: #b2cd16; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}" alt="Moepi Publishing Logo" />
        </div>
        <h2>Reset Password</h2>

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
                        <svg id="eyeOpen1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eyeClosed1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password_confirmation" id="password_confirmation" required />
                    <span class="toggle-password" id="togglePassword2">
                        <svg id="eyeOpen2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eyeClosed2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </span>
                </div>
            </div>

            <button type="submit">Reset Password</button>
        </form>

        <div class="links"><a href="{{ route('login') }}">Back to Login</a></div>
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