<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'admin_number' => ['required', 'string'],
            'admin_password' => ['required', 'string'],
        ]);

        $expectedNumber = (string) config('admin.admin_number', 'ADMIN2025');
        $expectedPassword = (string) config('admin.password_plain', 'Admin@1234');

        if (
            hash_equals($expectedNumber, (string) $request->admin_number)
            && hash_equals($expectedPassword, (string) $request->admin_password)
        ) {
            $admin = User::query()
                ->where('role', 'admin')
                ->where(function ($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                })
                ->orderBy('id')
                ->first();

            if (!$admin) {
                return back()->withErrors([
                    'admin_number' => 'No active senior manager account is available.',
                ])->onlyInput('admin_number');
            }

            Auth::guard('web')->login($admin);
            $request->session()->regenerate();

            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'admin_number' => 'The provided credentials do not match our records.',
        ])->onlyInput('admin_number');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
