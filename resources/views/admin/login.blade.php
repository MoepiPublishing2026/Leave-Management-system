<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Senior Manager Login - Leave Management</title>
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
        .login-container {
            max-width: 600px;
            width: 100%;
            background: #ffffff;
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
            margin-bottom: 24px;
        }
        .login-logo img {
            height: 160px;
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
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
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
            box-shadow: 0 0 0 3px rgba(178, 205, 22, 0.1);
        }
        .error {
            color: #e53e3e;
            font-size: 13px;
            margin-top: 6px;
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
            color: #fff;
            margin-top: 10px;
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
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/ui-consistency.css') }}" />
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}" alt="Moepi Publishing Logo" />
        </div>

        <h2>Senior Manager Login</h2>

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="form-group">
                <label for="admin_number">Senior Manager Number</label>
                <input type="text" name="admin_number" id="admin_number" value="{{ old('admin_number') }}" required autofocus />
                @error('admin_number')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="admin_password">Password</label>
                <input type="password" name="admin_password" id="admin_password" required />
                @error('admin_password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="extra-links">
            <a href="{{ route('login') }}">Employee / Manager login</a>
        </div>
    </div>

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
</body>
</html>
