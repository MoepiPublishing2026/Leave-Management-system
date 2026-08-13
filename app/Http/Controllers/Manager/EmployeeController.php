<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Services\UserDeletionService;
use App\Support\DepartmentManagers;

class EmployeeController extends Controller
{
    private function ensureManager(): void
    {
        if (strtolower((string) (Auth::user()->role ?? '')) !== 'manager') {
            abort(403);
        }
    }

    private function departments(?string $current = null): array
    {
        $departments = array_values(array_filter(config('departments', [])));

        if ($current !== null && $current !== '' && ! in_array($current, $departments, true)) {
            $departments[] = $current;
        }

        return $departments;
    }

    private function seniorManagersForSelect(): \Illuminate\Support\Collection
    {
        return User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->orderBy('id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    /** @return list<string> */
    private function selectedManagedDepartments(Request $request): array
    {
        $selected = $request->input('managed_departments', []);
        if (! is_array($selected)) {
            $selected = [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($d) => trim((string) $d),
            $selected
        ))));
    }

    private function resolveSeniorManagerId($requestedManagerId): ?int
    {
        $id = $requestedManagerId !== null && $requestedManagerId !== ''
            ? (int) $requestedManagerId
            : null;

        if ($id) {
            $exists = User::query()
                ->whereKey($id)
                ->where('role', 'admin')
                ->where('is_active', true)
                ->exists();

            if ($exists) {
                return $id;
            }
        }

        return User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');
    }

    public function index()
    {
        $this->ensureManager();

        $employees = User::query()
            ->with(['manager', 'managedDepartments'])
            ->orderByRaw("FIELD(role, 'employee', 'manager', 'admin')")
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('manager.employees.index', compact('employees'));
    }

    public function create()
    {
        $this->ensureManager();

        return view('manager.employees.create', [
            'departments' => $this->departments(),
            'seniorManagers' => $this->seniorManagersForSelect(),
            'departmentManagers' => DepartmentManagers::ownershipMap(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureManager();

        $role = (string) $request->input('role');
        $managedDepartments = $this->selectedManagedDepartments($request);

        $rules = [
            'first_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
            'last_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
            'email' => 'required|email:rfc|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:employee,manager,admin',
        ];

        if ($role === 'employee') {
            $rules['department'] = ['required', 'string', Rule::in($this->departments())];
        } elseif ($role === 'manager') {
            $rules['managed_departments'] = ['required', 'array', 'min:1'];
            $rules['managed_departments.*'] = ['string', Rule::in($this->departments())];
            $rules['manager_id'] = [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'admin')->where('is_active', true)),
            ];
        } else {
            $rules['department'] = ['nullable', 'string', Rule::in($this->departments())];
        }

        $request->validate($rules, [
            'first_name.regex' => 'First name must only contain letters.',
            'last_name.regex' => 'Last name must only contain letters.',
            'department.in' => 'Please select a valid company department.',
            'managed_departments.required' => 'Select at least one department this manager is responsible for.',
            'managed_departments.min' => 'Select at least one department this manager is responsible for.',
            'manager_id.required' => 'Please assign this manager to a senior manager.',
            'manager_id.exists' => 'Please select a valid senior manager.',
        ]);

        if ($role === 'manager') {
            $conflicts = DepartmentManagers::conflicts($managedDepartments);
            if ($conflicts !== []) {
                $lines = [];
                foreach ($conflicts as $dept => $name) {
                    $lines[] = "{$dept} is already managed by {$name}.";
                }

                return redirect()->back()
                    ->withErrors(['managed_departments' => implode(' ', $lines)])
                    ->withInput();
            }
        }

        if ($role === 'employee') {
            $managerId = DepartmentManagers::managerIdForDepartment($request->department);
            if (! $managerId) {
                return redirect()->back()
                    ->withErrors(['department' => 'No manager is responsible for this department yet. Assign the department to a manager first.'])
                    ->withInput();
            }
        } elseif ($role === 'manager') {
            $managerId = $this->resolveSeniorManagerId($request->manager_id);
            if ($managerId === null) {
                return redirect()->back()
                    ->withErrors(['role' => 'A Senior Manager account must exist before you can add a manager.'])
                    ->withInput();
            }
        } else {
            $managerId = null;
        }

        try {
            $rawPassword = $request->password;
            $department = $role === 'manager'
                ? ($managedDepartments[0] ?? null)
                : ($request->department ?: null);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'department' => $department,
                'designation' => $department,
                'email' => $request->email,
                'password' => Hash::make($rawPassword),
                'role' => $role,
                'manager_id' => $managerId,
            ]);

            if ($role === 'manager') {
                DepartmentManagers::syncForManager($user, $managedDepartments);
            }

            Mail::to($user->email)->send(new WelcomeUserMail($user, $rawPassword));

            return redirect()->route('manager.employees.index')->with('success', 'Employee added successfully.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while creating the employee. Please try again.'])
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->ensureManager();
        $employee = User::with('managedDepartments')->findOrFail($id);

        return view('manager.employees.edit', [
            'employee' => $employee,
            'departments' => $this->departments($employee->department),
            'seniorManagers' => $this->seniorManagersForSelect(),
            'departmentManagers' => DepartmentManagers::ownershipMap(),
            'managedDepartments' => old('managed_departments', $employee->managedDepartmentNames()),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->ensureManager();
        $employee = User::findOrFail($id);

        $role = (string) $request->input('role');
        $managedDepartments = $this->selectedManagedDepartments($request);

        $rules = [
            'first_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
            'last_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
            'email' => 'required|email:rfc|max:255|unique:users,email,'.$employee->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:employee,manager,admin',
        ];

        if ($role === 'employee') {
            $rules['department'] = ['required', 'string', Rule::in($this->departments($employee->department))];
        } elseif ($role === 'manager') {
            $rules['managed_departments'] = ['required', 'array', 'min:1'];
            $rules['managed_departments.*'] = ['string', Rule::in($this->departments($employee->department))];
            $rules['manager_id'] = [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'admin')->where('is_active', true)),
            ];
        } else {
            $rules['department'] = ['nullable', 'string', Rule::in($this->departments($employee->department))];
        }

        $request->validate($rules, [
            'first_name.regex' => 'First name must only contain letters.',
            'last_name.regex' => 'Last name must only contain letters.',
            'department.in' => 'Please select a valid company department.',
            'managed_departments.required' => 'Select at least one department this manager is responsible for.',
            'managed_departments.min' => 'Select at least one department this manager is responsible for.',
            'manager_id.required' => 'Please assign this manager to a senior manager.',
            'manager_id.exists' => 'Please select a valid senior manager.',
        ]);

        $isSelf = (int) Auth::id() === (int) $employee->id;
        $isActive = $request->boolean('is_active');

        if ($isSelf && $request->role !== 'manager') {
            return redirect()->back()
                ->withErrors(['role' => 'You cannot change your own role.'])
                ->withInput();
        }

        if ($isSelf && ! $isActive) {
            return redirect()->back()
                ->withErrors(['is_active' => 'You cannot deactivate your own account.'])
                ->withInput();
        }

        if ($role === 'manager') {
            $conflicts = DepartmentManagers::conflicts($managedDepartments, (int) $employee->id);
            if ($conflicts !== []) {
                $lines = [];
                foreach ($conflicts as $dept => $name) {
                    $lines[] = "{$dept} is already managed by {$name}.";
                }

                return redirect()->back()
                    ->withErrors(['managed_departments' => implode(' ', $lines)])
                    ->withInput();
            }
        }

        if ($role === 'employee') {
            $managerId = DepartmentManagers::managerIdForDepartment($request->department);
            if (! $managerId) {
                return redirect()->back()
                    ->withErrors(['department' => 'No manager is responsible for this department yet. Assign the department to a manager first.'])
                    ->withInput();
            }
        } elseif ($role === 'manager') {
            $managerId = $this->resolveSeniorManagerId($request->manager_id);
            if ($managerId === null) {
                return redirect()->back()
                    ->withErrors(['role' => 'A Senior Manager account must exist before you can set this role to Manager.'])
                    ->withInput();
            }
        } else {
            $managerId = null;
        }

        $previousRole = strtolower((string) ($employee->role ?? ''));
        $department = $role === 'manager'
            ? ($managedDepartments[0] ?? $employee->department)
            : ($request->department ?: null);

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'department' => $department,
            'designation' => $department,
            'email' => $request->email,
            'role' => $role,
            'is_active' => $isActive,
            'manager_id' => $managerId,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

        if ($role === 'manager') {
            DepartmentManagers::syncForManager($employee->fresh(), $managedDepartments);
        } elseif ($previousRole === 'manager') {
            DepartmentManagers::clearForManager($employee);
        }

        return redirect()->route('manager.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $this->ensureManager();
        $employee = User::findOrFail($id);

        if ((int) Auth::id() === (int) $employee->id) {
            return redirect()->route('manager.employees.index')->with('error', 'You cannot delete the currently logged-in user.');
        }

        DB::beginTransaction();
        try {
            if (strtolower((string) ($employee->role ?? '')) === 'manager') {
                DepartmentManagers::clearForManager($employee);
            }
            $deletion = app(UserDeletionService::class);
            $deletion->deleteUser($employee);
            $deletion->purgeOrphanedLeaveRequests();
            DB::commit();

            return redirect()->route('manager.employees.index')->with('success', 'Employee deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return redirect()->route('manager.employees.index')->with('error', 'Unable to delete this employee because related records could not be removed.');
        }
    }
}
