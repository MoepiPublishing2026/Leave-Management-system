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

class EmployeeController extends Controller
{
    private function ensureManager(): void
    {
        if (strtolower((string) (Auth::user()->role ?? '')) !== 'manager') {
            abort(403);
        }
    }

    private function departments(): array
    {
        return config('departments', []);
    }

    private function managersForSelect(): \Illuminate\Support\Collection
    {
        return User::query()
            ->managers()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
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

    /** Employees report to a manager; managers auto-report to a senior manager (admin). */
    private function resolveReportsToId(string $role, $requestedManagerId): ?int
    {
        if ($role === 'employee') {
            return $requestedManagerId !== null && $requestedManagerId !== ''
                ? (int) $requestedManagerId
                : null;
        }

        if ($role === 'manager') {
            return User::query()
                ->where('role', 'admin')
                ->where('is_active', true)
                ->orderBy('id')
                ->value('id');
        }

        return null;
    }

    public function index()
    {
        $this->ensureManager();

        // Full staff list so managers can assign employees (leave views stay scoped by manager_id)
        $employees = User::query()
            ->with('manager')
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
            'managers' => $this->managersForSelect(),
            'seniorManagers' => $this->seniorManagersForSelect(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureManager();

        $request->validate([
            'first_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
            'last_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
            'department' => ['required', 'string', Rule::in($this->departments())],
            'manager_id' => [
                Rule::requiredIf(fn () => $request->input('role') === 'employee'),
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'manager')),
            ],
            'email' => 'required|email:rfc|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:employee,manager,admin',
        ], [
            'first_name.regex' => 'First name must only contain letters.',
            'last_name.regex' => 'Last name must only contain letters.',
            'department.in' => 'Please select a valid company department.',
            'manager_id.required' => 'Please assign this employee to a manager.',
            'manager_id.exists' => 'Please select a valid manager.',
        ]);

        try {
            $rawPassword = $request->password;
            $role = $request->role;
            $managerId = $this->resolveReportsToId($role, $request->manager_id);

            if ($role === 'manager' && $managerId === null) {
                return redirect()->back()
                    ->withErrors(['role' => 'A Senior Manager account must exist before you can add a manager.'])
                    ->withInput();
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'department' => $request->department,
                'designation' => $request->department, // department now serves as designation
                'email' => $request->email,
                'password' => Hash::make($rawPassword),
                'role' => $role,
                'manager_id' => $managerId,
            ]);

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
        $employee = User::findOrFail($id);

        return view('manager.employees.edit', [
            'employee' => $employee,
            'departments' => $this->departments(),
            'managers' => $this->managersForSelect(),
            'seniorManagers' => $this->seniorManagersForSelect(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->ensureManager();
        $employee = User::findOrFail($id);

        $request->validate([
            'first_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
            'last_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
            'department' => ['required', 'string', Rule::in($this->departments())],
            'manager_id' => [
                Rule::requiredIf(fn () => $request->input('role') === 'employee'),
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'manager')),
            ],
            'email' => 'required|email:rfc|max:255|unique:users,email,' . $employee->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:employee,manager,admin',
        ], [
            'first_name.regex' => 'First name must only contain letters.',
            'last_name.regex' => 'Last name must only contain letters.',
            'department.in' => 'Please select a valid company department.',
            'manager_id.required' => 'Please assign this employee to a manager.',
            'manager_id.exists' => 'Please select a valid manager.',
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

        $role = $request->role;
        $managerId = $this->resolveReportsToId($role, $request->manager_id);

        if ($role === 'manager' && $managerId === null) {
            return redirect()->back()
                ->withErrors(['role' => 'A Senior Manager account must exist before you can set this role to Manager.'])
                ->withInput();
        }

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'department' => $request->department,
            'designation' => $request->department,
            'email' => $request->email,
            'role' => $role,
            'is_active' => $isActive,
            'manager_id' => $managerId,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $employee->update($data);

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
