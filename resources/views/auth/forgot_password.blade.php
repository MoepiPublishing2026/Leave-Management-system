<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-48.png') }}?v=3" type="image/png" sizes="48x48" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=3" sizes="180x180" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/auth-design.css') }}?v=1" />
</head>
<body class="auth-page auth-forgot-page">
    <main class="auth-shell forgot-shell">
        <div class="auth-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
        </div>

        <h1 class="auth-title">Forgot password?</h1>
        <p class="auth-copy">Enter your email address for your account and we will send<br class="desktop-only-break"> you password reset link</p>

        <form method="POST" action="{{ route('password.email') }}" class="auth-form forgot-form">
            @csrf

            <div class="auth-form-group">
                <label class="auth-label" for="email">
                    <span class="label-desktop">Email Address</span>
                    <span class="label-mobile">Email</span>
                </label>
                <input
                    class="auth-input"
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email address"
                    required
                    autofocus
                    autocomplete="email"
                />
                @error('email')
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <button class="auth-primary" type="submit">Send</button>
        </form>

        <div class="forgot-actions">
            <a class="auth-link" href="{{ route('login') }}">Back to Login</a>
        </div>

        <footer class="auth-footer">
            <p class="auth-copyright">&copy;{{ date('Y') }} Moepi Publishing.All rights reserved</p>
        </footer>
    </main>

    @if (session('status') && session('reset_link'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Reset Link',
                html: '<a href=' + @json(session('reset_link')) + ' style="color:#234e52; font-weight:800; word-break:break-all;">Open Reset Link</a>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#29374c'
            });
        </script>
    @elseif (session('status'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('status')),
                confirmButtonText: 'OK',
                confirmButtonColor: '#29374c'
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
                confirmButtonColor: '#29374c',
                width: '40em'
            });
        </script>
    @endif
</body>
</html>
