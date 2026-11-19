
@php
    $formAction = $formAction ?? route('password.update');
    $fieldName = $fieldName ?? 'email';
    $fieldId = $fieldId ?? 'email';
    $fieldLabel = $fieldLabel ?? 'Email Address';
    $fieldPlaceholder = $fieldPlaceholder ?? 'Enter your email address';
    $fieldAutocomplete = $fieldAutocomplete ?? 'username';
    $fieldError = $fieldError ?? 'email';
    $passwordName = $passwordName ?? 'password';
    $passwordId = $passwordId ?? 'password';
    $passwordError = $passwordError ?? 'password';
    $confirmPasswordName = $confirmPasswordName ?? 'password_confirmation';
    $confirmPasswordId = $confirmPasswordId ?? 'password_confirmation';
    $confirmPasswordError = $confirmPasswordError ?? 'password_confirmation';
    $belowLinkHref = $belowLinkHref ?? route('login');
    $belowLinkText = $belowLinkText ?? 'Back to Login';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password - Leave Management</title>

    <link rel="icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-48.png') }}?v=3" type="image/png" sizes="48x48" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=3" sizes="180x180" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="{{ asset('css/auth-design.css') }}?v=4" />
</head>

<body class="auth-page auth-reset-password-page">

    <main class="auth-shell login-shell">

        <div class="auth-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
        </div>

        <h1 class="auth-title">Reset Password</h1>

        <div class="auth-mobile-divider" aria-hidden="true"></div>

        <form method="POST" action="{{ $formAction }}" class="auth-form login-form">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}" />

            <div class="auth-form-group email-group">

                <label class="auth-label" for="{{ $fieldId }}">
                    {{ $fieldLabel }}
                </label>

                <div class="auth-input-wrap">
                    <input
                        class="auth-input"
                        type="email"
                        name="{{ $fieldName }}"
                        id="{{ $fieldId }}"
                        value="{{ old($fieldName, $email) }}"
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

                <label class="auth-label" for="{{ $passwordId }}">
                    New Password
                </label>

                <div class="auth-input-wrap">

                    <input
                        class="auth-input password-input"
                        type="password"
                        name="{{ $passwordName }}"
                        id="{{ $passwordId }}"
                        placeholder="Enter your new password"
                        required
                        autocomplete="new-password"
                    />

                    <button
                        type="button"
                        class="toggle-password"
                        id="togglePassword"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
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

           
            <div class="auth-form-group password-group">

                <label class="auth-label" for="{{ $confirmPasswordId }}">
                    Confirm Password
                </label>

                <div class="auth-input-wrap">

                    <input
                        class="auth-input password-input"
                        type="password"
                        name="{{ $confirmPasswordName }}"
                        id="{{ $confirmPasswordId }}"
                        placeholder="Confirm your new password"
                        required
                        autocomplete="new-password"
                    />

                    <button
                        type="button"
                        class="toggle-password"
                        id="toggleConfirmPassword"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
                        <svg id="confirmEyeOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>

                        <svg id="confirmEyeClosed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                        </svg>
                    </button>

                </div>

                @error($confirmPasswordError)
                    <div class="auth-error">{{ $message }}</div>
                @enderror

            </div>

            <button class="auth-primary login-button" type="submit">
                Reset Password
            </button>

        </form>

        <a class="auth-link forgot-link" href="{{ $belowLinkHref }}">
            {{ $belowLinkText }}
        </a>

        <footer class="auth-footer">
            <p class="auth-copyright">
                &copy;{{ date('Y') }} Moepi Publishing.All rights reserved
            </p>
        </footer>

    </main>

    <script>
        function setupPasswordToggle(inputId, buttonId, openId, closedId) {
            const passwordField = document.getElementById(inputId);
            const toggleButton = document.getElementById(buttonId);
            const eyeOpen = document.getElementById(openId);
            const eyeClosed = document.getElementById(closedId);

            toggleButton.addEventListener('click', () => {
                const isPassword = passwordField.type === 'password';

                passwordField.type = isPassword ? 'text' : 'password';

                eyeOpen.style.display = isPassword ? 'none' : 'block';
                eyeClosed.style.display = isPassword ? 'block' : 'none';

                toggleButton.setAttribute('aria-pressed', String(isPassword));
                toggleButton.setAttribute(
                    'aria-label',
                    isPassword ? 'Hide password' : 'Show password'
                );
            });
        }

        setupPasswordToggle(
            '{{ $passwordId }}',
            'togglePassword',
            'eyeOpen',
            'eyeClosed'
        );

        setupPasswordToggle(
            '{{ $confirmPasswordId }}',
            'toggleConfirmPassword',
            'confirmEyeOpen',
            'confirmEyeClosed'
        );
    </script>

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Reset Password Failed',
                html: '<ul style="text-align:left; list-style-position: inside;">' +
                    @json($errors->all()).map(e => '<li>' + e + '</li>').join('') +
                    '</ul>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#29374c'
            });
        </script>
    @endif

</body>
</html>
