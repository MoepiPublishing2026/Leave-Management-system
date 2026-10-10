@extends($layout ?? 'layouts.manager')

@section('title', 'Edit Employee')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .employee-create-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
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
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .toggle-password svg {
        width: 22px;
        height: 22px;
        stroke: #b2cd16;
    }
    .error {
        color: #ff1e5a;
        font-size: 13px;
        margin-top: 5px;
        text-align: left;
    }
    .field-hint {
        color: #64748b;
        font-size: 12px;
        margin-top: 5px;
        text-align: left;
        font-weight: 500;
        text-transform: none;
        letter-spacing: normal;
    }
    .checkbox-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
        text-align: left;
    }
    .checkbox-row input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin: 0;
        accent-color: #138056;
    }
    .checkbox-row label {
        margin: 0;
        text-transform: none;
        letter-spacing: normal;
        font-size: 15px;
        color: #334155;
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
        }
    }
</style>

<div class="employee-create-page">
    <h1 style="font-weight:700; font-size:40px; color:#2c3e50; margin:0 0 8px; letter-spacing:0.04em;">Edit Employee</h1>
    <p style="margin:0 0 18px; color:#64748b; font-size:0.92rem;">Update account details for {{ $employee->first_name }} {{ $employee->last_name }}.</p>

    <div class="signup-container">
    <form method="POST" action="{{ route('manager.employees.update', $employee->id) }}" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="autofill-trap" aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;">
            <input type="text" tabindex="-1" autocomplete="username">
            <input type="password" tabindex="-1" autocomplete="current-password">
        </div>

        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $employee->first_name) }}" required />
        @error('first_name')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $employee->last_name) }}" required />
        @error('last_name')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="role">Role</label>
        <select name="role" id="role" required>
            <option value="employee" {{ old('role', $employee->role) === 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="manager" {{ old('role', $employee->role) === 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin" {{ old('role', $employee->role) === 'admin' ? 'selected' : '' }}>Senior Manager</option>
        </select>
        @error('role')
            <div class="error">{{ $message }}</div>
        @enderror

        @php
            $currentRole = old('role', $employee->role);
            $selectedManaged = old('managed_departments', $managedDepartments ?? []);
            if (! is_array($selectedManaged)) {
                $selectedManaged = [];
            }
            $deptManagers = ($departmentManagers ?? collect())->toArray();
        @endphp

        <div id="employee-department-block" @style(['display: none' => $currentRole === 'manager'])>
            <label for="department">Department</label>
            <select name="department" id="department" @required($currentRole !== 'manager' && $currentRole !== 'admin') @disabled($currentRole === 'manager')>
                <option value="">Select department</option>
                @foreach(($departments ?? []) as $dept)
                    <option value="{{ $dept }}" {{ old('department', $employee->department) === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <p id="department-auto-hint" class="field-hint" style="text-align:left;margin-top:6px;color:#64748b;font-size:0.85rem;">
                Employees are automatically assigned to the manager responsible for this department.
            </p>
            @error('department')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div id="managed-departments-block" @style(['display: none' => $currentRole !== 'manager'])>
            <label>Departments responsible for</label>
            <div class="managed-dept-list" style="text-align:left;margin-top:8px;display:grid;gap:8px;">
                @foreach(($departments ?? []) as $dept)
                    @php
                        $ownerId = $deptManagers[$dept]['manager_id'] ?? null;
                        $ownerName = $deptManagers[$dept]['manager_name'] ?? null;
                        $ownedByOther = $ownerId && (int) $ownerId !== (int) $employee->id;
                    @endphp
                    <label style="display:flex;align-items:center;gap:10px;text-transform:none;letter-spacing:normal;font-size:15px;font-weight:600;color:#334155;margin:0;">
                        <input type="checkbox" name="managed_departments[]" value="{{ $dept }}" class="managed-dept-checkbox"
                               {{ in_array($dept, $selectedManaged, true) ? 'checked' : '' }}
                               style="width:18px;height:18px;accent-color:#138056;">
                        <span>{{ $dept }}@if($ownedByOther)<span style="color:#64748b;font-weight:500;"> — currently {{ $ownerName }}</span>@endif</span>
                    </label>
                @endforeach
            </div>
            <p class="field-hint" style="text-align:left;margin-top:6px;color:#64748b;font-size:0.85rem;">
                Select one or more departments. Employees registered in these departments will be assigned to this manager automatically.
            </p>
            @error('managed_departments')
                <div class="error">{{ $message }}</div>
            @enderror
            @error('managed_departments.*')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div id="assign-block" @style(['display: none' => $currentRole !== 'manager'])>
            <label for="manager_id">Assign to senior manager</label>
            <select name="manager_id" id="manager_id" data-selected="{{ old('manager_id', $employee->manager_id) }}" @disabled($currentRole !== 'manager') @required($currentRole === 'manager')>
                <option value="">Select senior manager</option>
                @foreach(($seniorManagers ?? []) as $senior)
                    <option value="{{ $senior->id }}" @selected((string) old('manager_id', $employee->manager_id) === (string) $senior->id)>
                        {{ trim(($senior->first_name ?? '').' '.($senior->last_name ?? '')) ?: 'Senior Manager #'.$senior->id }}
                    </option>
                @endforeach
            </select>
            <p id="assign-hint" class="field-hint" style="text-align:left;margin-top:6px;color:#64748b;font-size:0.85rem;">
                Required for managers. Choose which senior manager this manager reports to.
            </p>
            @error('manager_id')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <label for="employee_email">Username (Email)</label>
        <input
            type="email"
            name="email"
            id="employee_email"
            value="{{ old('email', $employee->email) }}"
            data-expected="{{ old('email', $employee->email) }}"
            autocomplete="off"
            required
        />
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">New Password</label>
        <div class="input-wrapper">
            <input type="password" name="password" id="password" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')" />
            <span class="toggle-password" id="togglePasswordEdit" role="button" tabindex="0" aria-label="Show or hide password">
                <svg id="eyeOpenEdit" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                </svg>
                <svg id="eyeClosedEdit" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                </svg>
            </span>
        </div>
        <p class="field-hint">Leave blank to keep the current password.</p>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password_confirmation">Confirm New Password</label>
        <div class="input-wrapper">
            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')" />
            <span class="toggle-password" id="togglePasswordEditConfirm" role="button" tabindex="0" aria-label="Show or hide confirm password">
                <svg id="eyeOpenEditConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M1.5 12s4.5-7.5 10.5-7.5S22.5 12 22.5 12 18 19.5 12 19.5 1.5 12 1.5 12z" />
                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                </svg>
                <svg id="eyeClosedEditConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3l18 18M10.6 10.6A3 3 0 0112 9a3 3 0 013 3c0 .5-.1.9-.3 1.3M6.7 6.7A10.9 10.9 0 001.5 12S6 19.5 12 19.5a10.6 10.6 0 004.7-1.1M17.3 17.3A10.9 10.9 0 0022.5 12s-4.5-7.5-10.5-7.5a10.6 10.6 0 00-4.9 1.2" />
                </svg>
            </span>
        </div>

        <div class="checkbox-row">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }} />
            <label for="is_active">Account is active</label>
        </div>
        @error('is_active')
            <div class="error">{{ $message }}</div>
        @enderror

        <div class="button-group">
            <button type="submit" class="btn-form-action">Save Changes</button>
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

        setupPasswordToggle(document.getElementById('togglePasswordEdit'), 'password', 'eyeOpenEdit', 'eyeClosedEdit');
        setupPasswordToggle(document.getElementById('togglePasswordEditConfirm'), 'password_confirmation', 'eyeOpenEditConfirm', 'eyeClosedEditConfirm');

        function clearBrowserAutofill() {
            const email = document.getElementById('employee_email');
            const password = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            const expectedEmail = email ? (email.getAttribute('data-expected') || '') : '';
            if (email && email.value !== expectedEmail) {
                email.value = expectedEmail;
            }
            if (password) password.value = '';
            if (confirm) confirm.value = '';
        }
        window.addEventListener('load', function () {
            clearBrowserAutofill();
            setTimeout(clearBrowserAutofill, 150);
        });

        const roleSelect = document.getElementById('role');
        const departmentSelect = document.getElementById('department');
        const departmentBlock = document.getElementById('employee-department-block');
        const managedBlock = document.getElementById('managed-departments-block');
        const assignBlock = document.getElementById('assign-block');
        const managerSelect = document.getElementById('manager_id');
        const departmentHint = document.getElementById('department-auto-hint');
        const deptManagers = @json(($departmentManagers ?? collect())->toArray());

        function updateDepartmentHint() {
            if (!departmentHint || !departmentSelect) return;
            const dept = departmentSelect.value;
            const owner = deptManagers[dept];
            if (!dept) {
                departmentHint.textContent = 'Employees are automatically assigned to the manager responsible for this department.';
                return;
            }
            if (owner && owner.manager_name) {
                departmentHint.textContent = 'This employee will be assigned to ' + owner.manager_name + ' (responsible for ' + dept + ').';
            } else {
                departmentHint.textContent = 'No manager is responsible for ' + dept + ' yet. Assign that department to a manager first.';
            }
        }

        function syncRoleFields() {
            if (!roleSelect) return;
            const role = roleSelect.value;
            const isEmployee = role === 'employee';
            const isManager = role === 'manager';

            if (departmentBlock) departmentBlock.style.display = isManager ? 'none' : 'block';
            if (managedBlock) managedBlock.style.display = isManager ? 'block' : 'none';
            if (assignBlock) assignBlock.style.display = isManager ? 'block' : 'none';

            if (departmentSelect) {
                departmentSelect.disabled = isManager;
                departmentSelect.required = isEmployee;
                if (isManager) {
                    departmentSelect.removeAttribute('name');
                } else {
                    departmentSelect.name = 'department';
                }
            }

            document.querySelectorAll('.managed-dept-checkbox').forEach((box) => {
                box.disabled = !isManager;
            });

            if (managerSelect) {
                managerSelect.disabled = !isManager;
                managerSelect.required = isManager;
                if (isManager) {
                    managerSelect.name = 'manager_id';
                } else {
                    managerSelect.removeAttribute('name');
                }
            }

            updateDepartmentHint();
        }

        departmentSelect?.addEventListener('change', updateDepartmentHint);
        roleSelect?.addEventListener('change', syncRoleFields);
        syncRoleFields();
    });
</script>
@endsection
