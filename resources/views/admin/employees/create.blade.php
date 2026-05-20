@extends('layouts.admin')

@section('title', 'Add Employee')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: white;
        padding: 40px 20px;
        margin: 0;
    }
    .signup-container {
        max-width: 800px;
        margin: 5px auto 0;
        background-color: #ffffff;
        border-radius: 15px;
        padding: 35px 30px;
        box-shadow: 0 0 10px #bdb6b6ff;
        border: 1px solid #dde3ea;
        text-align: center;
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
    input[type="password"],
    select {
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
    /* Match auth/login password toggle (eye icons + placement) */
    .input-wrapper {
        position: relative;
        width: 100%;
    }
    .input-wrapper input[type="password"],
    .input-wrapper input[type="text"] {
        padding-right: 48px;
    }
    .toggle-password {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #a0aec0;
        transition: color 0.3s ease;
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
    .toggle-password:focus {
        outline: 2px solid rgba(178, 205, 22, 0.45);
        outline-offset: 2px;
        border-radius: 4px;
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
    .button-group .btn-form-action {
        box-sizing: border-box;
        min-height: 52px;
        padding: 15px 40px;
        font-weight: 600;
        font-size: 18px;
        line-height: 1.25;
        border-radius: 12px;
        cursor: pointer;
        border: none;
        background-color: #b2cd16;
        color: white;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s ease;
    }
    .button-group button.btn-form-action {
        font-family: inherit;
    }
    .button-group .btn-form-action:hover {
        background-color: #0f140acc;
    }
    .branding {
        margin-top: 35px;
        font-size: 13px;
        color: #a2a6b1;
        user-select: none;
        letter-spacing: 0.5px;
    }
    @media (max-width: 576px) {
        .signup-container { padding: 24px; }
        input[type="text"], input[type="email"], input[type="password"], select { font-size: 16px; padding: 11px 14px; }
        .input-wrapper input[type="password"],
        .input-wrapper input[type="text"] { padding-right: 44px; }
        .toggle-password { right: 12px; }
        .button-group {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .button-group .btn-form-action {
            width: 100%;
            min-height: 48px;
            padding: 12px 16px;
            font-size: 15px;
            border-radius: 12px;
        }
    }
</style>

<div class="signup-container">
    <h2>Add Employee</h2>
    <form method="POST" action="{{ route('admin.employees.store') }}">
        @csrf

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
        <input type="text" name="department" value="{{ old('department') }}" required />
        @error('department')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="designation">Designation</label>
        <input type="text" name="designation" value="{{ old('designation') }}" required />
        @error('designation')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="email">Username (Email)</label>
        <input type="email" name="email" value="{{ old('email') }}" required />
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <!-- Password with toggle (same icons as login) -->
        <label for="password">Password</label>
        <div class="input-wrapper">
            <input type="password" name="password" id="password" required />
            <span class="toggle-password" id="togglePasswordAdd" role="button" tabindex="0" aria-label="Show or hide password">
                <svg id="eyeOpenAdd" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                </svg>
                <svg id="eyeClosedAdd" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                </svg>
            </span>
        </div>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <!-- Confirm password with toggle (same icons as login) -->
        <label for="password_confirmation">Confirm Password</label>
        <div class="input-wrapper">
            <input type="password" name="password_confirmation" id="password_confirmation" required />
            <span class="toggle-password" id="togglePasswordAddConfirm" role="button" tabindex="0" aria-label="Show or hide confirm password">
                <svg id="eyeOpenAddConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                </svg>
                <svg id="eyeClosedAddConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                </svg>
            </span>
        </div>

        <label for="role">Role</label>
        <select name="role" required>
            <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee12</option>
            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        @error('role')
            <div class="error">{{ $message }}</div>
        @enderror

        <div class="button-group">
            <button type="submit" class="btn-form-action">Add Employee</button>
            <a href="{{ route('admin.employees.index') }}" class="btn-form-action">Back to List</a>
        </div>
    </form>

    <div class="branding">
        &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function setupPasswordToggle(toggleEl, inputId, openId, closedId) {
            const input = document.getElementById(inputId);
            const eyeOpen = document.getElementById(openId);
            const eyeClosed = document.getElementById(closedId);
            if (!toggleEl || !input || !eyeOpen || !eyeClosed) return;

            function flip() {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                eyeOpen.style.display = isPassword ? 'none' : 'block';
                eyeClosed.style.display = isPassword ? 'block' : 'none';
            }

            toggleEl.addEventListener('click', function (e) {
                e.preventDefault();
                flip();
            });
            toggleEl.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    flip();
                }
            });
        }

        setupPasswordToggle(document.getElementById('togglePasswordAdd'), 'password', 'eyeOpenAdd', 'eyeClosedAdd');
        setupPasswordToggle(document.getElementById('togglePasswordAddConfirm'), 'password_confirmation', 'eyeOpenAddConfirm', 'eyeClosedAddConfirm');
    });
</script>
@endsection
