@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<style>
    /* Your existing styles here (unchanged) */
    .settings-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 0 12px #ddd;
    }
    h1 {
        font-weight: 700;
        font-size: 24px;
        margin-bottom: 20px;
        color: #32384d;
    }
    label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        border-left: 4px solid #a5d817;
        padding-left: 8px;
        color: #4a4a4a;
    }
    input[type="text"],
    input[type="email"],
    select,
    input[type="password"],
    input[type="file"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 18px;
        border: 2px solid #a5d817;
        border-radius: 6px;
        font-size: 15px;
        color: #333;
        box-sizing: border-box;
        transition: border-color 0.3s;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    select:focus,
    input[type="password"]:focus,
    input[type="file"]:focus {
        outline: none;
        border-color: #819c0f;
        background-color: #f3f9d2;
    }
    .error-message {
        color: red;
        font-size: 13px;
        margin-top: -14px;
        margin-bottom: 10px;
    }
    button[type="submit"] {
        background-color: #a5d817;
        color: #1b1b17;
        font-weight: 700;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    button[type="submit"]:hover {
        background-color: #819c0f;
    }
    .password-toggle {
        position: relative;
        display: flex;
        align-items: center;
    }
    .password-toggle input {
        flex: 1;
        padding-right: 45px;
        margin-bottom: 0;
    }
    .password-toggle .toggle-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        user-select: none;
        color: #a0aec0;
        transition: color 0.3s ease;
        width: 22px;
        height: 22px;
    }
    .password-toggle .toggle-icon:hover svg {
        stroke: #b2cd16;
    }
    .password-toggle .toggle-icon svg {
        width: 22px;
        height: 22px;
        stroke: #b2cd16;
        transition: stroke 0.3s ease;
    }
    .password-toggle {
        margin-bottom: 18px;
    }
</style>

<div class="settings-container">
    <h1>Update Profile</h1>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf

        <label for="first_name">First Name</label>
        <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" readonly style="background-color: #f3f4f6; color: #9ca3af; cursor: not-allowed;" />

        <label for="last_name">Last Name</label>
        <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" readonly style="background-color: #f3f4f6; color: #9ca3af; cursor: not-allowed;" />

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" readonly style="background-color: #f3f4f6; color: #9ca3af; cursor: not-allowed;" />

        <label for="current_password">Current Password</label>
        <div class="password-toggle">
            <input id="current_password" name="current_password" type="password" placeholder="Required to change password" />
            <span class="toggle-icon" id="toggleCurrentPassword">
                <svg id="eyeOpenCurrent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                </svg>
                <svg id="eyeClosedCurrent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                </svg>
            </span>
        </div>
        @error('current_password')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="password">New Password</label>
        <div class="password-toggle">
            <input id="password" name="password" type="password" placeholder="Leave blank to keep current" />
            <span class="toggle-icon" id="togglePassword">
                <svg id="eyeOpenPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                </svg>
                <svg id="eyeClosedPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                </svg>
            </span>
        </div>
        @error('password')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="password_confirmation">Confirm Password</label>
        <div class="password-toggle">
            <input id="password_confirmation" name="password_confirmation" type="password" />
            <span class="toggle-icon" id="togglePasswordConfirmation">
                <svg id="eyeOpenPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                </svg>
                <svg id="eyeClosedPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                </svg>
            </span>
        </div>

        <label for="profile_picture">Profile Picture</label>
        <input id="profile_picture" name="profile_picture" type="file" accept="image/*" />
        @if ($user->profile_picture)
            <img src="{{ route('users.profile_picture', ['user' => $user->id]) }}?v={{ optional($user->updated_at)->timestamp }}" alt="Profile Picture" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; margin-top: 18px;" />
        @endif

        <button type="submit">Save Changes</button>
    </form>
</div>

<script>
    function setupToggle(inputId, toggleId, openId, closedId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);
        const eyeOpen = document.getElementById(openId);
        const eyeClosed = document.getElementById(closedId);
        if (!toggle || !input || !eyeOpen || !eyeClosed) return;

        toggle.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eyeOpen.style.display = isPassword ? 'none' : 'block';
            eyeClosed.style.display = isPassword ? 'block' : 'none';
        });
    }

    setupToggle('current_password', 'toggleCurrentPassword', 'eyeOpenCurrent', 'eyeClosedCurrent');
    setupToggle('password', 'togglePassword', 'eyeOpenPassword', 'eyeClosedPassword');
    setupToggle('password_confirmation', 'togglePasswordConfirmation', 'eyeOpenPasswordConfirmation', 'eyeClosedPasswordConfirmation');
</script>

@endsection
