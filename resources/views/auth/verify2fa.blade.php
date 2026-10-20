<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Two-Factor Authentication - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" sizes="192x192" />
    <link rel="icon" href="{{ asset('images/favicon-48.png') }}?v=3" type="image/png" sizes="48x48" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-192.png') }}?v=3" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=3" sizes="180x180" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/auth-design.css') }}?v=1" />
</head>
<body class="auth-page auth-verify-page">
    <main class="auth-shell verify-shell">
        <div class="auth-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}?v=20260817t" alt="Moepi Publishing Logo" />
        </div>

        <h1 class="auth-title desktop-title">OTP Verification</h1>
        <p class="auth-copy desktop-copy">Please&nbsp; enter the OTP (One-Time-Password) send to<br>your email</p>

        <h1 class="auth-title mobile-title">Two-Factor Authentication</h1>
        <p class="auth-copy mobile-copy">A verification code has been sent to your email.Please<br>enter it below to continue</p>

        @if (session('status'))
            <div class="auth-status" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login.verify.post') }}" class="auth-form verify-form" id="verificationForm">
            @csrf

            <input type="hidden" name="code" id="code" value="{{ old('code') }}" />

            <div class="desktop-otp">
                <label class="otp-label">Verification Code</label>
                <div class="otp-grid" id="otpGrid" aria-label="Six digit verification code">
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            class="otp-digit"
                            type="text"
                            maxlength="1"
                            inputmode="text"
                            autocomplete="one-time-code"
                            aria-label="Verification character {{ $i + 1 }}"
                        />
                    @endfor
                </div>
            </div>

            <div class="auth-form-group mobile-code-group">
                <label class="auth-label" for="mobileCode">Verification Code</label>
                <input
                    class="auth-input"
                    type="text"
                    id="mobileCode"
                    maxlength="6"
                    value="{{ old('code') }}"
                    placeholder="Enter verification code"
                    autocomplete="one-time-code"
                />
            </div>

            <button class="auth-primary" type="submit">Verify</button>
        </form>

        <div class="verify-links">
            <form method="POST" action="{{ route('login.verify.resend') }}" class="resend-form">
                @csrf
                <button type="submit" class="resend-button">
                    <span class="label-desktop">Resend verification code</span>
                    <span class="label-mobile">Resend authentication code</span>
                </button>
            </form>
            <span class="verify-separator" aria-hidden="true">|</span>
            <a class="auth-link" href="{{ route('login') }}">Back to Login</a>
        </div>

        <footer class="auth-footer">
            <p class="auth-copyright">&copy;{{ date('Y') }} Moepi Publishing.All rights reserved</p>
        </footer>
    </main>

    <script>
        (function () {
            const form = document.getElementById('verificationForm');
            const hiddenCode = document.getElementById('code');
            const mobileCode = document.getElementById('mobileCode');
            const digits = Array.from(document.querySelectorAll('.otp-digit'));
            const initialCode = (hiddenCode.value || '').slice(0, 6);

            function syncDesktopCode() {
                hiddenCode.value = digits.map(input => input.value).join('').slice(0, 6);
            }

            function syncMobileCode() {
                hiddenCode.value = (mobileCode.value || '').slice(0, 6);
            }

            initialCode.split('').forEach((char, index) => {
                if (digits[index]) digits[index].value = char;
            });

            digits.forEach((input, index) => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\s/g, '').slice(-1);
                    syncDesktopCode();
                    if (input.value && digits[index + 1]) digits[index + 1].focus();
                });

                input.addEventListener('keydown', event => {
                    if (event.key === 'Backspace' && !input.value && digits[index - 1]) {
                        digits[index - 1].focus();
                    }
                });

                input.addEventListener('paste', event => {
                    event.preventDefault();
                    const pasted = (event.clipboardData || window.clipboardData)
                        .getData('text')
                        .replace(/\s/g, '')
                        .slice(0, 6);

                    pasted.split('').forEach((char, pasteIndex) => {
                        if (digits[pasteIndex]) digits[pasteIndex].value = char;
                    });
                    syncDesktopCode();
                    const focusIndex = Math.min(pasted.length, digits.length - 1);
                    if (digits[focusIndex]) digits[focusIndex].focus();
                });
            });

            mobileCode.addEventListener('input', syncMobileCode);

            form.addEventListener('submit', () => {
                if (window.matchMedia('(max-width: 640px)').matches) {
                    syncMobileCode();
                } else {
                    syncDesktopCode();
                }
            });

            if (digits[0] && !window.matchMedia('(max-width: 640px)').matches) {
                digits[0].focus();
            }
        })();
    </script>

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Verification Failed',
                html: '<ul style="text-align:left;">' + @json($errors->all()).map(e => '<li>' + e + '</li>').join('') + '</ul>',
                confirmButtonText: 'OK',
                width: '30em',
                confirmButtonColor: '#29374c'
            });
        </script>
    @endif
</body>
</html>
