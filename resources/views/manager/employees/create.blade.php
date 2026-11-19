@extends($layout ?? 'layouts.manager')

@section('title', 'Add Employee')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .employee-create-page { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; max-width: none; margin: 0; padding: 0 0 32px; color: #1e293b; }
    .signup-container { position: relative; width: min(100%, 210mm); max-width: 210mm; margin: 0 auto; background-color: #ffffff; border-radius: 15px; padding: 35px 30px; box-shadow: 0 0 10px #bdb6b6ff; border: 1px solid #dde3ea; text-align: center; box-sizing: border-box; }
    label { display: block; margin-top: 20px; font-weight: 600; font-size: 14px; color: #4a4a4a; text-transform: uppercase; letter-spacing: 1.6px; text-align: left; }
    input[type="text"], input[type="email"], input[type="password"], select { width: 100%; margin-top: 7px; padding: 13px 17px; font-size: 16px; border-radius: 8px; border: 1px solid #9ef755ff; color: #0f172a; background: #ffffff; box-sizing: border-box; transition: border-color 0.3s ease; }
    input:focus, select:focus { outline: none; border-color: #c3d010; box-shadow: none; }
    .autofill-trap { position: absolute; left: -10000px; top: 0; width: 1px; height: 1px; overflow: hidden; opacity: 0; pointer-events: none; }
    .error { color: #ff1e5a; font-size: 13px; margin-top: 5px; text-align: left; }
    .field-hint { color: #64748b; font-size: 12px; margin-top: 5px; text-align: left; font-weight: 500; text-transform: none; letter-spacing: normal; }
    .checkbox-row { display: flex; align-items: center; gap: 10px; margin-top: 20px; text-align: left; }
    .checkbox-row input[type="checkbox"] { width: 18px; height: 18px; margin: 0; }
    .checkbox-row label { margin: 0; text-transform: none; letter-spacing: normal; }
    .button-group { display: flex; justify-content: center; gap: 15px; margin-top: 30px; position: relative; z-index: 1; }
    .button-group .btn-form-action { box-sizing: border-box; cursor: pointer; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
    .button-group button.btn-form-action { font-family: inherit; }
    .branding { margin-top: 35px; font-size: 13px; color: #a2a6b1; user-select: none; letter-spacing: 0.5px; }
    @media (max-width: 576px) {
    .button-group { flex-direction: column; align-items: stretch; gap: 10px; }
    .button-group .btn-form-action { width: 100%; }
        html body .employee-create-page .signup-container { padding: 24px !important; }
    }

    /* ===== Match prototype ===== */
    html body .employee-create-page { font-family:'Poppins',"Segoe UI",sans-serif !important; width:min(100%,210mm) !important; max-width:210mm !important; margin:0 auto !important; padding:34px 16px 40px !important; box-sizing:border-box !important; }
    html body .employee-create-page h1 { font-size:34px !important; color:#263248 !important; letter-spacing:0 !important; }
    html body .employee-create-page > p { font-size:.75rem !important; color:#666 !important; }
    html body .employee-create-page .signup-container { max-width:100% !important; border-radius:16px !important; border:1px solid #ddd !important; box-shadow:0 4px 18px rgba(0,0,0,.18) !important; padding:34px 44px 24px !important; }
    html body .employee-create-page .signup-container label { font-size:12px !important; font-weight:400 !important; letter-spacing:0 !important; color:#333 !important; margin-top:16px !important; }
    html body .employee-create-page .signup-container input[type="text"],
    html body .employee-create-page .signup-container input[type="email"],
    html body .employee-create-page .signup-container input[type="password"] { border:2px solid #d4e21a !important; border-radius:14px !important; padding:13px 16px !important; font-size:14px !important; }
    html body .employee-create-page .password-toggle { position: relative; margin-top: 7px; }
    html body .employee-create-page .password-toggle input { margin-top: 0 !important; padding-right: 46px !important; width: 100%; }
    html body .employee-create-page .password-toggle input::-ms-reveal,
    html body .employee-create-page .password-toggle input::-ms-clear { display: none; }
    html body .employee-create-page .password-toggle .toggle-password {
        position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
        display: inline-flex; align-items: center; justify-content: center;
        padding: 0; border: 0; background: transparent; cursor: pointer;
        color: #d4e21a; line-height: 0;
    }
    html body .employee-create-page .password-toggle .toggle-password svg { width: 22px; height: 22px; stroke: #d4e21a; }
    html body .employee-create-page .signup-container select { border:2px solid #777 !important; border-radius:14px !important; padding:11px 16px !important; font-size:14px !important; font-weight:700 !important; color:#263248 !important; box-shadow:none !important; background-color:#fff !important; }
    html body .employee-create-page .checkbox-row label { margin-top:0 !important; font-size:13px !important; text-transform:none !important; }
    html body .employee-create-page input[type="checkbox"] { accent-color:#d4e21a !important; }
    html body .employee-create-page .field-hint { font-size:11px !important; font-weight:400 !important; color:#767672 !important; }
    
    /* ===== Prototype match: Bright Lime Green Buttons ===== */
    html body .employee-create-page .button-group .btn-form-action {
        background: #d4e21a !important; /* Bright lime green */
        border: 1px solid #c3d010 !important; /* Slight border */
        color: #0a1347 !important; /* Dark text */
        border-radius: 6px !important; /* Squared corners */
        padding: 8px 24px !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        min-height: 38px !important;
        height: auto !important;
        box-shadow: none !important;
        transition: background-color 0.15s ease;
    }
    html body .employee-create-page .button-group .btn-form-action:hover {
        background: #c3d010 !important; /* Slightly darker on hover */
        color: #0a1347 !important;
    }
    
    html body .employee-create-page .branding { font-size:11px !important; margin-top:20px !important; }

    /* ===== Manager departments checklist ===== */
    .managed-dept-list {
        margin-top: 7px;
        text-align: left;
        border: 2px solid #777;
        border-radius: 14px;
        padding: 6px;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
    }
    html body .employee-create-page .signup-container .dept-option {
        display: flex !important;
        align-items: center;
        gap: 10px;
        margin: 0 !important;
        padding: 10px 12px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #263248 !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
    }
    html body .employee-create-page .signup-container .dept-option:hover { background: #e9efb8; }
    html body .employee-create-page .signup-container .dept-option input {
        width: 18px !important;
        height: 18px !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        flex-shrink: 0;
        accent-color: #d4e21a;
    }
    .dept-option small { font-weight: 400; color: #767672; }
</style>

<div class="employee-create-page">
    <h1 style="font-weight:700; margin:0 0 18px;">Add Employee</h1>

    <div class="signup-container">
    <form method="POST" action="{{ route('manager.employees.store') }}" autocomplete="off">
        @csrf

        <div class="autofill-trap" aria-hidden="true">
            <input type="text" tabindex="-1" autocomplete="username">
            <input type="password" tabindex="-1" autocomplete="current-password">
        </div>

        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required maxlength="15" pattern="[A-Z][a-zA-Z]*" title="Must start with a capital letter and contain letters only" />
        @error('first_name')<div class="error">{{ $message }}</div>@enderror

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required maxlength="15" pattern="[A-Z][a-zA-Z]*" title="Must start with a capital letter and contain letters only" />
        @error('last_name')<div class="error">{{ $message }}</div>@enderror

        <label for="role">Role</label>
        <select name="role" id="role" required>
            <option value="employee" {{ old('role', 'employee') === 'employee' ? 'selected' : '' }}>Employee</option>
            <option value="manager" {{ old('role', 'employee') === 'manager' ? 'selected' : '' }}>Manager</option>
            <option value="admin" {{ old('role', 'employee') === 'admin' ? 'selected' : '' }}>Senior Manager</option>
        </select>
        @error('role')<div class="error">{{ $message }}</div>@enderror

        @php
            $currentRole = old('role', 'employee');
            $selectedManaged = old('managed_departments', []);
            if (! is_array($selectedManaged)) {
                $selectedManaged = [];
            }
            $deptManagers = ($departmentManagers ?? collect())->toArray();
        @endphp

        <div id="employee-department-block" @style(['display: none' => $currentRole === 'admin'])>
            <label for="department">Department</label>
            <select name="department" id="department" @required($currentRole === 'employee' || $currentRole === 'manager')>
                <option value="">Select department</option>
                @foreach(($departments ?? []) as $dept)
                    <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <p id="department-auto-hint" class="field-hint">
                Employees are automatically assigned to the manager responsible for this department.
            </p>
            @error('department')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div id="managed-departments-block" @style(['display: none' => $currentRole !== 'manager'])>
            <label>Departments responsible for</label>
            <div class="managed-dept-list" id="managedDeptList">
                @forelse(($departments ?? []) as $dept)
                    @php $owner = $deptManagers[$dept]['manager_name'] ?? null; @endphp
                    <label class="dept-option">
                        <input type="checkbox" name="managed_departments[]" value="{{ $dept }}" class="managed-dept-checkbox"
                               {{ in_array($dept, $selectedManaged, true) ? 'checked' : '' }}
                               @disabled($currentRole !== 'manager')>
                        <span>{{ $dept }}@if($owner) <small>? currently {{ $owner }}</small>@endif</span>
                    </label>
                @empty
                    <p class="field-hint" style="margin:8px;">No departments configured.</p>
                @endforelse
            </div>
            <p class="field-hint">
                Select one or more departments this manager is responsible for. Employees in these departments will be assigned to them automatically.
            </p>
            @error('managed_departments')<div class="error">{{ $message }}</div>@enderror
            @error('managed_departments.*')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div id="assign-block" @style(['display: none' => $currentRole !== 'manager'])>
            <label for="manager_id">Assign to senior manager</label>
            <select name="manager_id" id="manager_id" data-selected="{{ old('manager_id') }}" @disabled($currentRole !== 'manager') @required($currentRole === 'manager')>
                <option value="">Select senior manager</option>
                @foreach(($seniorManagers ?? []) as $senior)
                    <option value="{{ $senior->id }}" @selected((string) old('manager_id') === (string) $senior->id)>
                        {{ trim(($senior->first_name ?? '').' '.($senior->last_name ?? '')) ?: 'Senior Manager #'.$senior->id }}
                    </option>
                @endforeach
            </select>
            <p id="assign-hint" class="field-hint">
                Required for managers. Choose which senior manager this manager reports to.
            </p>
            @error('manager_id')<div class="error">{{ $message }}</div>@enderror
        </div>

        <label for="employee_email">Username (Email)</label>
        <input type="email" name="email" id="employee_email" value="{{ old('email') }}" data-expected="{{ old('email') }}"
               autocomplete="off" autocapitalize="off" spellcheck="false"
               readonly onfocus="this.removeAttribute('readonly')" required />
        @error('email')<div class="error">{{ $message }}</div>@enderror
        <p class="field-hint">A login password is created automatically and emailed to this address.</p>

            <div class="button-group">
            <button type="submit" class="btn-form-action" style="background: #cbe212 !important; color: #0a1347 !important; border: 1px solid #b5cb0e !important; border-radius: 6px !important; padding: 8px 24px !important; font-weight: 700 !important; font-size: 14px !important; min-height: 38px !important; box-shadow: none !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; cursor: pointer !important;">Add Employee</button>
            <a href="{{ route('manager.employees.index') }}" class="btn-form-action" style="background: #cbe212 !important; color: #0a1347 !important; border: 1px solid #b5cb0e !important; border-radius: 6px !important; padding: 8px 24px !important; font-weight: 700 !important; font-size: 14px !important; min-height: 38px !important; box-shadow: none !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; text-decoration: none !important;">Back to List</a>
        </div>
    </form>

    <div class="branding">
        &copy; {{ date('Y') }} Moepi Publishing. All rights reserved.
    </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function clearBrowserAutofill() {
            const email = document.getElementById('employee_email');
            const expectedEmail = email ? (email.getAttribute('data-expected') || '') : '';
            if (email && email.value !== expectedEmail) {
                email.value = expectedEmail;
            }
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
            if (!departmentHint || !departmentSelect || !roleSelect) return;
            const role = roleSelect.value;
            const dept = departmentSelect.value;

            if (role === 'manager') {
                departmentHint.textContent = 'Department this manager falls under (shown on the employees list).';
                return;
            }

            if (!dept) {
                departmentHint.textContent = 'Employees are automatically assigned to the manager responsible for this department.';
                return;
            }
            const owner = deptManagers[dept];
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
            const isAdmin = role === 'admin';

            if (departmentBlock) departmentBlock.style.display = isAdmin ? 'none' : 'block';
            if (managedBlock) managedBlock.style.display = isManager ? 'block' : 'none';
            if (assignBlock) assignBlock.style.display = isManager ? 'block' : 'none';

            if (departmentSelect) {
                departmentSelect.disabled = isAdmin;
                departmentSelect.required = isEmployee || isManager;
                if (isAdmin) {
                    departmentSelect.removeAttribute('name');
                } else {
                    departmentSelect.name = 'department';
                }
            }

            document.querySelectorAll('.managed-dept-checkbox').forEach((box) => { box.disabled = !isManager; });

            if (managerSelect) {
                managerSelect.disabled = !isManager;
                managerSelect.required = isManager;
                if (isManager) { managerSelect.name = 'manager_id'; } else { managerSelect.removeAttribute('name'); }
            }

            updateDepartmentHint();
        }

        departmentSelect?.addEventListener('change', updateDepartmentHint);
        roleSelect?.addEventListener('change', syncRoleFields);
        syncRoleFields();
    });
</script>
@endsection