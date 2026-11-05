@php
    $formAction = $formAction ?? route('login.post');
    $fieldName = $fieldName ?? 'email';
    $fieldId = $fieldId ?? 'email_or_number';
    $fieldLabel = $fieldLabel ?? 'Email Address';
    $fieldPlaceholder = $fieldPlaceholder ?? 'Enter your email address';
    $fieldAutocomplete = $fieldAutocomplete ?? 'username';
    $fieldError = $fieldError ?? 'login_error';
    $passwordName = $passwordName ?? 'password';
    $passwordId = $passwordId ?? 'password';
    $passwordError = $passwordError ?? 'password';
    $belowLinkHref = $belowLinkHref ?? route('password.request');
    $belowLinkText = $belowLinkText ?? 'Forgot Password?';
    $showStatus = $showStatus ?? true;
    $showPasswordResetNotice = $showPasswordResetNotice ?? true;
@endphp
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
    <link rel="stylesheet" href="{{ asset('css/auth-design.css') }}?v=4" />
</head>
<body class="auth-page auth-login-page">
    <main class="auth-shell login-shell">
        <div class="auth-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
        </div>

        <h1 class="auth-title">Login</h1>
        <div class="auth-mobile-divider" aria-hidden="true"></div>

        <form method="POST" action="{{ $formAction }}" class="auth-form login-form">
            @csrf

            <div class="auth-form-group email-group">
                <label class="auth-label" for="{{ $fieldId }}">{{ $fieldLabel }}</label>
                <div class="auth-input-wrap">
                    <input
                        class="auth-input"
                        type="text"
                        name="{{ $fieldName }}"
                        id="{{ $fieldId }}"
                        value="{{ old($fieldName) }}"
                        placeholder="{{ $fieldPlaceholder }}"
                        required
                        autofocus
                        autocomplete="{{ $fieldAutocomplete }}"
                    />
                </div>
                @error($fieldError)
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-form-group password-group">
                <label class="auth-label" for="{{ $passwordId }}">Password</label>
                <div class="auth-input-wrap">
                    <input
                        class="auth-input password-input"
                        type="password"
                        name="{{ $passwordName }}"
                        id="{{ $passwordId }}"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    />
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password" aria-pressed="false">
                        <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                        </svg>
                    </button>
                </div>
                @error($passwordError)
                    <div class="auth-error">{{ $message }}</div>
                @enderror
            </div>

            <a class="auth-link forgot-link" href="{{ $belowLinkHref }}">{{ $belowLinkText }}</a>
            <button class="auth-primary login-button" type="submit">Login</button>
        </form>

        <footer class="auth-footer">
            <p class="auth-copyright">&copy;{{ date('Y') }} Moepi Publishing.All rights reserved</p>
        </footer>
    </main>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector(@json('#' . $passwordId));
        const eyeOpen = document.querySelector('#eyeOpen');
        const eyeClosed = document.querySelector('#eyeClosed');

        togglePassword.addEventListener('click', () => {
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            eyeOpen.style.display = isPassword ? 'none' : 'block';
            eyeClosed.style.display = isPassword ? 'block' : 'none';
            togglePassword.setAttribute('aria-pressed', String(isPassword));
            togglePassword.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    </script>

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                html: '<ul style="text-align:left; list-style-position: inside;">' + @json($errors->all()).map(e => '<li>' + e + '</li>').join('') + '</ul>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#29374c'
            });
        </script>
    @endif

    @if ($showStatus && session('status'))
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

    @if ($showPasswordResetNotice && request()->query('password_reset') === '1')
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Password updated',
                text: 'Your password has been reset. Please sign in with your new password.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#29374c'
            });
        </script>
    @endif
</body>
</html>
