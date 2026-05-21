<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UserDeletionService;

class EmployeeController extends Controller
{
    // Show list of employees
    public function index()
    {
        $employees = User::whereIn('role', ['employee', 'manager','admin'])->get();
        return view('admin.employees.index', compact('employees'));
    }

    // Show create employee form
    public function create()
    {
        return view('admin.employees.create');
    }

    // Store new employee
  public function store(Request $request)
{
    $request->validate([
        'first_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
        'last_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
        'department' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z][A-Za-z\s&\-\/]*$/'],
        'designation' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z][A-Za-z\s&\-\/]*$/'],
        'email' => 'required|email:rfc|max:255|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|in:employee,manager,admin',
    ], [
        'first_name.regex' => 'First name must only contain letters.',
        'last_name.regex' => 'Last name must only contain letters.',
        'department.regex' => 'Department must only contain letters (no numbers).',
        'designation.regex' => 'Designation must only contain letters (no numbers).',
    ]);

    try {
        $rawPassword = $request->password;

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'department' => $request->department,
            'designation' => $request->designation,
            'email' => $request->email,
            'password' => Hash::make($rawPassword),
            'role' => $request->role,
        ]);

        Mail::to($user->email)->send(new WelcomeUserMail($user, $rawPassword));

        return redirect()->route('admin.employees.index')->with('success', 'Employee added successfully.');
    }catch (\Exception $e) {
    // Log the error for debugging
    Log::error($e->getMessage());

            // Pass the message into withErrors so you can see it in the view
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while creating the employee: ' ])
                ->withInput();
        }

}

    public function destroy($id)
    {
        $employee = User::findOrFail($id);

        if ((int) Auth::id() === (int) $employee->id) {
            return redirect()->route('admin.employees.index')->with('error', 'You cannot delete the currently logged-in user.');
        }

        DB::beginTransaction();
        try {
            $deletion = app(UserDeletionService::class);
            $deletion->deleteUser($employee);
            $deletion->purgeOrphanedLeaveRequests();
            DB::commit();

            return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return redirect()->route('admin.employees.index')->with('error', 'Unable to delete this employee because related records could not be removed.');
        }
    }
}
