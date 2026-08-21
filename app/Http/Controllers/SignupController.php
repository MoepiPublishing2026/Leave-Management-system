<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Support\DepartmentManagers;

class SignupController extends Controller
{
    public function showSignupForm()
    {
        return view('signup');
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department' => ['required', 'string', Rule::in(config('departments', []))],
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'department.in' => 'Please select a valid company department.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $managerId = DepartmentManagers::managerIdForDepartment($request->department);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'department' => $request->department,
            'designation' => $request->department,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'manager_id' => $managerId,
        ]);

        return redirect()->route('login')->with('success', 'Signup successful. Please login.');
    }
}
