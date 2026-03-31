<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorCodeMail;

class AuthController extends Controller
{
    // Show login page
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Generate 2FA code
            $code = rand(100000, 999999);
            $user->two_factor_code = $code;
            $user->two_factor_expires_at = now()->addMinutes(10);
            $user->save();

            // Send email
            Mail::to($user->email)->send(new TwoFactorCodeMail($code));

            // Store user id in session temporarily
            $request->session()->put('2fa_user_id', $user->id);

            return redirect()->route('login.verify');
        }

        return redirect('/login')->withErrors(['login_error' => 'Invalid credentials.']);
    }

    // Show 2FA form
    public function show2faForm()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.verify2fa');
    }

    // Verify 2FA code
    public function verify2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (!$user || $user->two_factor_code !== $request->code) {
            return back()->withErrors(['code' => 'The provided code is invalid.']);
        }

        if (now()->greaterThan($user->two_factor_expires_at)) {
            return back()->withErrors(['code' => 'The provided code has expired.']);
        }

        // Code is valid
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('2fa_user_id');

        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'manager':
                return redirect()->route('manager.dashboard');
            case 'employee':
                return redirect()->route('dashboard');
            default:
                Auth::logout();
                return redirect('/login')->withErrors(['email' => 'Invalid user role.']);
        }
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot_password');
    }

    private function ensurePasswordResetTokensTableExists(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            return;
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    private function ensureUsersRememberTokenColumnExists(): void
    {
        if (Schema::hasColumn('users', 'remember_token')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->rememberToken();
        });
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $this->ensurePasswordResetTokensTableExists();

        try {
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('status', __($status));
            }

            return back()->withErrors(['email' => __($status)]);
        } catch (\Throwable $e) {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
            }

            $token = Password::broker()->createToken($user);
            $resetUrl = $request->getSchemeAndHttpHost() . route('password.reset', ['token' => $token, 'email' => $user->email], false);

            return back()
                ->with('status', 'Reset link generated.')
                ->with('reset_link', $resetUrl);
        }
    }

    public function showResetPasswordForm(Request $request, string $token)
    {
        $this->ensurePasswordResetTokensTableExists();
        return view('auth.reset_password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $this->ensurePasswordResetTokensTableExists();
        $this->ensureUsersRememberTokenColumnExists();
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    // Show authenticated user's dashboard
    public function dashboard()
    {
        return view('dashboard');
    }

    // Show edit profile form
    public function edit()
    {
        $user = auth()->user();
        return view('settings.edit', compact('user'));
    }

    // Update profile and password
   public function update(Request $request)
{
    $user = auth()->user();

    $rules = [
        'current_password' => ['nullable', 'string'],
        'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        'profile_picture' => ['nullable', 'image', 'max:2048'],
    ];
    if ($request->filled('password')) {
        $rules['current_password'] = ['required', 'current_password'];
    }

    $data = $request->validate($rules);

    if (!empty($data['password'])) {
        if (Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'New password cannot be the same as the current password.']);
        }
        $user->password = Hash::make($data['password']);
    }

    if ($request->hasFile('profile_picture')) {
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->profile_picture = $path;
    }

    $user->save();

    return redirect()->route('settings.edit')->with('success', 'Profile updated successfully.');
}

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
