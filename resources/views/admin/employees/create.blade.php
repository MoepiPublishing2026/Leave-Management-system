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
    .has-toggle {
        position: relative;
    }
    .has-toggle input[type="password"],
    .has-toggle input[type="text"] {
        padding-right: 48px;
    }
    .toggle-password {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        background: white;
        border-radius: 8px;
        border: none;
        outline: none;
        padding: 0;
    }
    .toggle-password svg {
        width: 20px;
        height: 20px;
        stroke: #b2cd16;
        background: transparent;
        pointer-events: none;
    }
    .toggle-password:focus {
        outline: 2px solid #4a4a4a;
    }
    .toggle-password .icon-closed {
        display: none;
    }
    .toggle-password[aria-pressed="true"] .icon-open {
        display: none;
    }
    .toggle-password[aria-pressed="true"] .icon-closed {
        display: block;
    }
    .form-row {
        position: relative;
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
        background-color: #b2cd16;
        color: white;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s ease;
    }
    button:hover, .btn-login:hover {
        background-color: #0f140acc;
    }
    .btn-login {
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
        .toggle-password { right: 8px; }
        .button-group {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .button-group button,
        .button-group .btn-login {
            width: 100%;
            padding: 11px 14px;
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

        <!-- Password with toggle -->
        <label for="password">Password</label>
        <div class="form-row has-toggle">
            <input type="password" name="password" id="password" required />
            <button type="button" class="toggle-password" aria-label="Show or hide password"
                aria-pressed="false" data-target="password">
                <svg class="icon-open" data-state="open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.4 12S6.9 4.5 12 4.5 21.6 12 21.6 12 17.1 19.5 12 19.5 2.4 12 2.4 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="icon-closed" data-state="closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5"/>
                </svg>
            </button>
        </div>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <!-- Confirm password with toggle -->
        <label for="password_confirmation">Confirm Password</label>
        <div class="form-row has-toggle">
            <input type="password" name="password_confirmation" id="password_confirmation" required />
            <button type="button" class="toggle-password" aria-label="Show or hide confirm password"
                aria-pressed="false" data-target="password_confirmation">
                <svg class="icon-open" data-state="open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.4 12S6.9 4.5 12 4.5 21.6 12 21.6 12 17.1 19.5 12 19.5 2.4 12 2.4 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg class="icon-closed" data-state="closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5"/>
                </svg>
            </button>
        </div>

        <label for="role">Role</label>
        <select name="role" required>
            <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        @error('role')
            <div class="error">{{ $message }}</div>
        @enderror

        <div class="button-group">
            <button type="submit">Add Employee</button>
            <a href="{{ route('admin.employees.index') }}" class="btn-login">Back to List</a>
        </div>
    </form>

    <div class="branding">
        &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle password function
        function togglePasswordFor(button) {
            const targetId = button.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
        }
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                togglePasswordFor(btn);
            });
        });
    });
</script>
@endsection
