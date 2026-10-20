@extends($layout ?? 'layouts.manager')

@section('title', 'Add Employee')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .employee-create-page { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; max-width: none; margin: 0; padding: 0 0 32px; color: #1e293b; }
    .signup-container { position: relative; max-width: 980px; margin: 0 auto; background-color: #ffffff; border-radius: 15px; padding: 35px 30px; box-shadow: 0 0 10px #bdb6b6ff; border: 1px solid #dde3ea; text-align: center; }
    label { display: block; margin-top: 20px; font-weight: 600; font-size: 14px; color: #4a4a4a; text-transform: uppercase; letter-spacing: 1.6px; text-align: left; }
    input[type="text"], input[type="email"], input[type="password"], select { width: 100%; margin-top: 7px; padding: 13px 17px; font-size: 16px; border-radius: 8px; border: 1px solid #9ef755ff; color: #0f172a; background: #ffffff; box-sizing: border-box; transition: border-color 0.3s ease; }
    input:focus, select:focus { outline: none; border-color: #8d9c1c; box-shadow: none; }
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
    html body .employee-create-page { font-family:'Poppins',"Segoe UI",sans-serif !important; max-width:720px !important; margin:0 auto !important; padding:34px 16px 40px !important; }
    html body .employee-create-page h1 { font-size:34px !important; color:#263248 !important; letter-spacing:0 !important; }
    html body .employee-create-page > p { font-size:.75rem !important; color:#666 !important; }
    html body .employee-create-page .signup-container { max-width:100% !important; border-radius:16px !important; border:1px solid #ddd !important; box-shadow:0 4px 18px rgba(0,0,0,.18) !important; padding:34px 44px 24px !important; }
    html body .employee-create-page .signup-container label { font-size:12px !important; font-weight:400 !important; letter-spacing:0 !important; color:#333 !important; margin-top:16px !important; }
    html body .employee-create-page .signup-container input[type="text"],
    html body .employee-create-page .signup-container input[type="email"],
    html body .employee-create-page .signup-container input[type="password"] { border:2px solid #a2b222 !important; border-radius:14px !important; padding:13px 16px !important; font-size:14px !important; }
    html body .employee-create-page .signup-container select { border:2px solid #777 !important; border-radius:14px !important; padding:11px 16px !important; font-size:14px !important; font-weight:700 !important; color:#263248 !important; box-shadow:none !important; background-color:#fff !important; }
    html body .employee-create-page .checkbox-row label { margin-top:0 !important; font-size:13px !important; text-transform:none !important; }
    html body .employee-create-page input[type="checkbox"] { accent-color:#a2b222 !important; }
    html body .employee-create-page .field-hint { font-size:11px !important; font-weight:400 !important; color:#767672 !important; }
    html body .employee-create-page .button-group .btn-form-action { background:#a2b222 !important; color:#000 !important; border:0 !important; border-radius:8px !important; padding:8px 16px !important; font-size:13px !important; min-height:0 !important; height:auto !important; }
    html body .employee-create-page .button-group .btn-form-action:hover { background:#8d9c1c !important; }
    html body .employee-create-page .branding { font-size:11px !important; margin-top:20px !important; }

    /* ===== Departments dropdown (tick several) ===== */
    .dept-dropdown { position: relative; margin-top: 7px; text-align: left; }
    .dept-dropdown-btn { width: 100%; height: 44px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; background: #fff; border: 2px solid #777; border-radius: 14px; font: 700 14px 'Poppins', sans-serif; color: #263248; cursor: pointer; text-align: left; }
    .dept-dropdown-btn svg { width: 16px; height: 16px; flex-shrink: 0; transition: transform .2s; }
    .dept-dropdown.open .dept-dropdown-btn svg { transform: rotate(180deg); }
    .dept-dropdown-panel { display: none; position: absolute; left: 0; right: 0; top: calc(100% + 4px); z-index: 50; background: #fff; border: 2px solid #777; border-radius: 14px; padding: 6px; box-shadow: 0 8px 20px rgba(0,0,0,.18); max-height: 260px; overflow-y: auto; }
    .dept-dropdown.open .dept-dropdown-panel { display: block; }
    html body .employee-create-page .signup-container .dept-option { display: flex !important; align-items: center; gap: 10px; margin: 0 !important; padding: 10px 12px; border-radius: 10px; cursor: pointer; font-size: 14px !important; font-weight: 700 !important; color: #263248 !important; text-transform: none !important; letter-spacing: 0 !important; }
    html body .employee-create-page .signup-container .dept-option:hover { background: #e9efb8; }
    html body .employee-create-page .signup-container .dept-option input { width: 18px; height: 18px; margin: 0; padding: 0; border: 0 !important; }
    .dept-option small { font-weight: 400; color: #767672; }
</style>

<div class="employee-create-page">
    <h1 style="font-weight:700; margin:0 0 8px;">Add Employee</h1>
    <p style="margin:0 0 18px;">Create a new user account and send their login details by email.</p>

    <div class="signup-container">
    <form method="POST" action="{{ route('manager.employees.store') }}" autocomplete="off">
        @csrf

        <div class="autofill-trap" aria-hidden="true">
            <input type="text" tabindex="-1" autocomplete="username">
            <input type="password" tabindex="-1" autocomplete="current-password">
        </div>

        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required />
        @error('first_name')<div class="error">{{ $message }}</div>@enderror

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required />
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

        <div id="employee-department-block" @style(['display: none' => $currentRole === 'manager'])>
            <label for="department">Department</label>
            <select name="department" id="department" @required($currentRole !== 'manager' && $currentRole !== 'admin') @disabled($currentRole === 'manager')>
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
            <div class="dept-dropdown" id="deptDropdown">
                <button type="button" class="dept-dropdown-btn" id="deptDropdownBtn" aria-expanded="false">
                    <span id="deptDropdownText">Select departments</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="dept-dropdown-panel" id="deptDropdownPanel">
                    @foreach(($departments ?? []) as $dept)
                        @php $owner = $deptManagers[$dept]['manager_name'] ?? null; @endphp
                        <label class="dept-option">
                            <input type="checkbox" name="managed_departments[]" value="{{ $dept }}" class="managed-dept-checkbox"
                                   {{ in_array($dept, $selectedManaged, true) ? 'checked' : '' }}>
                            <span>{{ $dept }}@if($owner) <small>— currently {{ $owner }}</small>@endif</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <p class="field-hint">
                Select one or more departments. Employees registered in these departments will be assigned to this manager automatically.
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

        <label for="password">Password</label>
        <input type="password" name="password" id="password" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')" required />
        @error('password')<div class="error">{{ $message }}</div>@enderror

        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')" required />

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
                if (isManager) { departmentSelect.removeAttribute('name'); } else { departmentSelect.name = 'department'; }
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrap = document.getElementById('deptDropdown');
    const btn = document.getElementById('deptDropdownBtn');
    const text = document.getElementById('deptDropdownText');
    if (!wrap || !btn) return;
    const boxes = wrap.querySelectorAll('.managed-dept-checkbox');
    function refresh() {
        const picked = Array.from(boxes).filter(b => b.checked).map(b => b.value);
        text.textContent = picked.length === 0 ? 'Select departments'
            : picked.length <= 2 ? picked.join(', ')
            : picked.length + ' departments selected';
    }
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const open = wrap.classList.toggle('open');
        btn.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target)) wrap.classList.remove('open');
    });
    boxes.forEach(b => b.addEventListener('change', refresh));
    refresh();
});
</script>
@endsection