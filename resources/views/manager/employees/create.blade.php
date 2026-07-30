@extends('layouts.manager')

@section('title', 'Add Employee')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .employee-create-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        /* Align heading/start with dashboard pages (no centered narrow column) */
        max-width: none;
        margin: 0;
        padding: 0 0 32px;
        color: #1e293b;
    }
    .signup-container {
        max-width: 980px;
        margin: 0 auto;
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
        font-size: 16px;
        border-radius: 8px;
        border: 1px solid #9ef755ff;
        color: #0f172a;
        background: #ffffff;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }
    input:focus,
    select:focus {
        outline: none;
        border-color: #138056;
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.12);
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
        font-weight: 700;
        font-size: 18px;
        line-height: 1.25;
        border-radius: 12px;
        cursor: pointer;
        border: 1px solid #91bf14;
        background-color: #b2cd16;
        color: #0a1347;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .button-group button.btn-form-action {
        font-family: inherit;
    }
    .button-group .btn-form-action:hover {
        background-color: #9eb812;
        color: #0a1347;
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

<div class="employee-create-page">
    <h1 style="font-weight:700; font-size:40px; color:#2c3e50; margin:0 0 8px; letter-spacing:0.04em;">Add Employee</h1>
    <p style="margin:0 0 18px; color:#64748b; font-size:0.92rem;">Create a new user account and send their login details by email.</p>

    <div class="signup-container">
    <form method="POST" action="{{ route('manager.employees.store') }}">
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
        <select name="department" id="department" required>
            <option value="">Select department</option>
            @foreach(($departments ?? []) as $dept)
                <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
        @error('department')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="manager_id">Assign to</label>
        <select name="manager_id" id="manager_id" data-selected="{{ old('manager_id', auth()->id()) }}">
            <option value="">Select manager</option>
            @foreach(($managers ?? []) as $mgr)
                <option value="{{ $mgr->id }}" @selected((string) old('manager_id', auth()->id()) === (string) $mgr->id)>
                    {{ trim(($mgr->first_name ?? '').' '.($mgr->last_name ?? '')) ?: 'Manager #'.$mgr->id }}
                </option>
            @endforeach
        </select>
        <p id="assign-hint" class="field-hint" style="text-align:left;margin-top:6px;color:#64748b;font-size:0.85rem;">Required for employees. This manager will only see leave for people assigned to them.</p>
        @error('manager_id')
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
            <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Senior Manager</option>
        </select>
        @error('role')
            <div class="error">{{ $message }}</div>
        @enderror

        <div class="button-group">
            <button type="submit" class="btn-form-action">Add Employee</button>
            <a href="{{ route('manager.employees.index') }}" class="btn-form-action">Back to List</a>
        </div>
    </form>

    <div class="branding">
        &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
    </div>
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

        const roleSelect = document.querySelector('select[name="role"]');
        const managerSelect = document.getElementById('manager_id');
        const assignHint = document.getElementById('assign-hint');
        const managers = @json(($managers ?? collect())->map(fn ($m) => [
            'id' => $m->id,
            'name' => trim(($m->first_name ?? '').' '.($m->last_name ?? '')),
        ])->values()->all());
        const seniors = @json(($seniorManagers ?? collect())->map(fn ($m) => [
            'id' => $m->id,
            'name' => trim(($m->first_name ?? '').' '.($m->last_name ?? '')),
        ])->values()->all());

        function fillOptions(list, placeholder, selectedId) {
            managerSelect.innerHTML = '';
            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = placeholder;
            managerSelect.appendChild(blank);
            list.forEach((item) => {
                const opt = document.createElement('option');
                opt.value = String(item.id);
                opt.textContent = item.name || ('User #' + item.id);
                if (selectedId && String(selectedId) === String(item.id)) {
                    opt.selected = true;
                }
                managerSelect.appendChild(opt);
            });
        }

        function syncAssignTo() {
            if (!roleSelect || !managerSelect) return;
            const role = roleSelect.value;
            const preferred = managerSelect.getAttribute('data-selected');

            if (role === 'employee') {
                managerSelect.disabled = false;
                managerSelect.required = true;
                fillOptions(managers, 'Select manager', preferred);
                if (assignHint) {
                    assignHint.textContent = 'Required for employees. This manager will only see leave for people assigned to them.';
                }
            } else if (role === 'manager') {
                managerSelect.disabled = false;
                managerSelect.required = false;
                const autoId = seniors.length ? seniors[0].id : '';
                fillOptions(seniors, 'Select senior manager', autoId);
                managerSelect.disabled = true;
                // Disabled fields are not submitted — hidden mirror keeps display value for UX only
                let hidden = document.getElementById('manager_id_auto');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.id = 'manager_id_auto';
                    managerSelect.parentNode.insertBefore(hidden, managerSelect.nextSibling);
                }
                hidden.name = ''; // server auto-assigns; no need to post
                if (assignHint) {
                    assignHint.textContent = 'Managers are automatically assigned to a Senior Manager.';
                }
            } else {
                managerSelect.disabled = true;
                managerSelect.required = false;
                fillOptions([], 'Not applicable', '');
                if (assignHint) {
                    assignHint.textContent = 'Senior Managers are not assigned to anyone.';
                }
            }
        }
        roleSelect?.addEventListener('change', syncAssignTo);
        syncAssignTo();
    });
</script>
@endsection
