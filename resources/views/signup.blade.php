<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Signup - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-48.png') }}?v=3" type="image/png" sizes="48x48" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=3" sizes="180x180" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        html,
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            min-height: 100vh;
        }
        body {
            padding: 40px 20px;
            margin: 0;
        }
        .signup-container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            margin-top: 70px;
            background-color: #ffffff;
            border-radius: 15px;
            padding: 35px 30px;
            box-shadow: 0 0 10px #a7bd8dff;
            border: 1px solid #dde3ea;
            text-align: center;
        }
        .signup-logo {
            margin-bottom: 30px;
        }
        .signup-logo img {
            height: 150px;
            width: auto;
            max-width: 100%;
        }
        h2 {
            font-weight: 700;
            font-size: 32px;
            margin-bottom: 15px;
            color: #1b1e40;
            letter-spacing: -0.6px;
        }
        label {
            display: block;
            margin-top: 20px;
            font-weight: 600;
            font-size: 14px;
            color: #4a4a4a;
            text-transform: uppercase;
            letter-spacing: 1.6px;
            text-align: left;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            margin-top: 7px;
            padding: 13px 17px;
            font-size: 18px;
            border-radius: 8px;
            border: 1px solid #9ef755ff;
            color: #787c8d;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #a7ff43ff;
            color: #050505ff;
        }
        .error {
            color: #ff1e5a;
            font-size: 13px;
            margin-top: 5px;
            text-align: left;
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        button, .btn-login {
            padding: 15px 40px;
            font-weight: 600;
            font-size: 18px;
            border-radius: 12px;
            cursor: pointer;
            border: none;
            background: linear-gradient(135deg, #b2cd16 0%, #9ef755 100%);
            color: white;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }
        button::before, .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }
        button:hover::before, .btn-login:hover::before { left: 100%; }
        button:hover, .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(178, 205, 22, 0.3);
        }
        button:active, .btn-login:active { transform: translateY(0); }
        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
    <div class="signup-container">
        <div class="signup-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
        </div>

        <h2>SignUp</h2>

        <form method="POST" action="{{ route('signup.post') }}" autocomplete="off">
            @csrf

            <div aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;">
                <input type="text" tabindex="-1" autocomplete="username">
                <input type="password" tabindex="-1" autocomplete="current-password">
            </div>

            <label for="first_name">First Name</label>
            <input type="text" name="first_name" value="{{ old('first_name') }}" required />
            @error('first_name')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" value="{{ old('last_name') }}" required />
            @error('last_name')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="department">Department</label>
            <select name="department" required>
                <option value="">Select department</option>
                @foreach(config('departments', []) as $dept)
                    <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            @error('department')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="signup_email">Username (Email)</label>
            <input
                type="email"
                name="email"
                id="signup_email"
                value="{{ old('email') }}"
                data-expected="{{ old('email') }}"
                autocomplete="off"
                readonly
                onfocus="this.removeAttribute('readonly')"
                required
            />
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="password">Password</label>
            <input type="password" name="password" id="password" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')" required />
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')" required />

            <div class="button-group">
                <button type="submit">SignUp</button>
                <a href="{{ route('login') }}" class="btn-login">Login</a>
            </div>
        </form>

        <div class="branding">
            &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
        </div>
    </div>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                timer: 3500,
                timerProgressBar: true,
                showConfirmButton: false
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
                width: '40em',
                confirmButtonColor: '#b2cd16'
            });
        </script>
    @endif

    <script>
        function clearBrowserAutofill() {
            const email = document.getElementById('signup_email');
            const password = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            const expectedEmail = email ? (email.getAttribute('data-expected') || '') : '';
            if (email && email.value !== expectedEmail) {
                email.value = expectedEmail;
            }
            if (password) password.value = '';
            if (confirm) confirm.value = '';
        }
        window.addEventListener('load', function () {
            clearBrowserAutofill();
            setTimeout(clearBrowserAutofill, 150);
        });
    </script>
</body>
</html>
