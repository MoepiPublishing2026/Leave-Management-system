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
    }
    .password-toggle .toggle-icon {
        position: absolute;
        right: 10px;
        cursor: pointer;
        user-select: none;
        font-size: 18px;
        color: #666;
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
            <span class="toggle-icon" onclick="togglePasswordVisibility('current_password', this)">👁️</span>
        </div>
        @error('current_password')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="password">New Password</label>
        <div class="password-toggle">
            <input id="password" name="password" type="password" placeholder="Leave blank to keep current" />
            <span class="toggle-icon" onclick="togglePasswordVisibility('password', this)">👁️</span>
        </div>
        @error('password')
            <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="password_confirmation">Confirm Password</label>
        <div class="password-toggle">
            <input id="password_confirmation" name="password_confirmation" type="password" />
            <span class="toggle-icon" onclick="togglePasswordVisibility('password_confirmation', this)">👁️</span>
        </div>

        <label for="profile_picture">Profile Picture</label>
        <input id="profile_picture" name="profile_picture" type="file" accept="image/*" />
        @if ($user->profile_picture)
            <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; margin-top: 18px;" />
        @endif

        <button type="submit">Save Changes</button>
    </form>
</div>

<script>
    function togglePasswordVisibility(fieldId, iconElement) {
        const input = document.getElementById(fieldId);
        if (input.type === 'password') {
            input.type = 'text';
            iconElement.textContent = '🙈';
        } else {
            input.type = 'password';
            iconElement.textContent = '👁️';
        }
    }
</script>

@endsection
