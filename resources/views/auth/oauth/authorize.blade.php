<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Authorize Application - Leave Management</title>
    <link rel="icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" sizes="32x32" />
    <link rel="shortcut icon" href="{{ asset('images/favicon-32.png') }}" type="image/png" />
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}" sizes="180x180" />

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

        .authorize-container {
            max-width: 600px;
            width: 100%;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 10px #a7bd8dff;
            position: relative;
            overflow: hidden;
        }

        .authorize-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #b2cd16 0%, #9ef755 100%);
        }

        .authorize-logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .authorize-logo img {
            height: 120px;
            width: auto;
            max-width: 100%;
        }

        .client-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            margin: 0 auto 18px;
            border-radius: 14px;
            background: linear-gradient(135deg, #b2cd16 0%, #9ef755 100%);
        }

        .client-icon svg {
            width: 28px;
            height: 28px;
            stroke: #ffffff;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 22px;
            color: #2d3748;
            font-weight: 600;
        }

        .authorize-subtitle {
            text-align: center;
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .authorize-subtitle strong {
            color: #2d3748;
        }

        .account-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            background: #fafbfa;
            margin-bottom: 24px;
        }

        .account-card .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #b2cd16 0%, #9ef755 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .account-card .account-meta {
            min-width: 0;
        }

        .account-card .account-label {
            font-size: 11px;
            font-weight: 600;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .account-card .account-email {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .scope-notice {
            font-size: 13px;
            color: #718096;
            background: #fafbfa;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .authorize-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        form {
            margin: 0;
        }

        button {
            width: 100%;
            padding: 14px;
            font-weight: 600;
            font-size: 15px;
            border-radius: 10px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        button.btn-authorize {
            background: linear-gradient(135deg, #b2cd16 0%, #9ef755 100%);
            color: white;
        }

        button.btn-authorize::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }

        button.btn-authorize:hover::before {
            left: 100%;
        }

        button.btn-authorize:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(178, 205, 22, 0.3);
        }

        button.btn-deny {
            background: #ffffff;
            color: #718096;
            border: 2px solid #e2e8f0;
        }

        button.btn-deny:hover {
            border-color: #e53e3e;
            color: #e53e3e;
        }

        button:active {
            transform: translateY(0);
        }

        .branding {
            text-align: center;
            margin-top: 22px;
            font-size: 12px;
            color: #a0aec0;
        }

        @media (max-width: 480px) {
            .authorize-container {
                padding: 26px 22px;
                max-width: 100%;
            }

            h2 {
                font-size: 20px;
            }

            .authorize-logo img {
                height: 70px;
            }
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/ui-consistency.css') }}" />
</head>

<body>
    <div class="authorize-container">
        <div class="authorize-logo">
            <img src="{{ asset('images/logo_trimmed.png') }}" alt="Moepi Publishing Logo" />
        </div>

        <div class="client-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v4h8z" />
            </svg>
        </div>

        <h2 class="auth-title">Authorize {{ $client->name }}</h2>
        <p class="authorize-subtitle">
            <strong>{{ $client->name }}</strong> is requesting access to your account.
        </p>

        <div class="account-card">
            <div class="avatar">{{ strtoupper(substr($user->email, 0, 1)) }}</div>
            <div class="account-meta">
                <div class="account-label">Logged in as</div>
                <div class="account-email">{{ $user->email }}</div>
            </div>
        </div>

        <div class="scope-notice">
            Approving this request will let {{ $client->name }} access data on your behalf. You can revoke this
            access at any time from your account settings.
        </div>

        <div class="authorize-actions">
            <form method="POST" action="{{ route('passport.authorizations.approve') }}">
                @csrf

                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">

                <button type="submit" class="btn-authorize">
                    Authorize
                </button>
            </form>

            <form method="POST" action="{{ route('passport.authorizations.deny') }}">
                @csrf
                @method('DELETE')

                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">

                <button type="submit" class="btn-deny">
                    Deny
                </button>
            </form>
        </div>

        <div class="branding">
            &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
        </div>
    </div>
</body>

</html>