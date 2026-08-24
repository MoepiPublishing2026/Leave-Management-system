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
use App\Services\ProfilePictureService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorCodeMail;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    // Show login page
    public function showLoginForm()
    {
        // request()->session()->regenerateToken();
        return view('auth.login');
    }


            //This is to avoid 2FA for local testing and development only, when pushed to production and git , uncomment the login function below and comment out this one
            // public function login(Request $request)
            // {
            //     $request->validate([
            //         'email' => 'required|email',
            //         'password' => 'required|string',
            //     ]);

            //     $user = User::where('email', $request->email)->first();

            //     if ($user && Hash::check($request->password, $user->password)) {

            //         // Login user directly without 2FA
            //         Auth::login($user);

            //         // Regenerate session for security
            //         $request->session()->regenerate();

            //         // Redirect based on role
            //         switch ($user->role) {
            //             case 'admin':
            //                 return redirect()->route('admin.dashboard');

            //             case 'manager':
            //                 return redirect()->route('manager.dashboard');

            //             case 'employee':
            //                 return redirect()->route('dashboard');

            //             default:
            //                 Auth::logout();

            //                 return redirect('/login')
            //                     ->withErrors([
            //                         'email' => 'Invalid user role.'
            //                     ]);
            //         }
            //     }

            //     return redirect('/login')
            //         ->withErrors([
            //             'login_error' => 'Invalid credentials.'
            //         ]);
            // }




    // Handle login this is for production (when pushed to production and git , uncomment this and comment out the local login above)
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

            $mailSent = $this->sendTwoFactorCode($user, (string) $code);

            // Store user id in session temporarily
            $request->session()->put('2fa_user_id', $user->id);

            if ($mailSent) {
                return redirect()->route('login.verify');
            }

            if (app()->environment('local')) {
                return redirect()->route('login.verify')->with(
                    'status',
                    'The verification email could not be sent (mail server unreachable). Use this code: '.$code
                );
            }

            return redirect('/login')->withErrors([
                'login_error' => 'We could not send your verification email. Check your internet connection and try again.',
            ]);
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

        if (!$user || (string) $user->two_factor_code !== (string) $request->code) {
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

    public function resend2fa(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (! $user) {
            $request->session()->forget('2fa_user_id');

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Your login session is no longer valid.']);
        }

        $previousCode = $user->two_factor_code;
        $previousExpiry = $user->two_factor_expires_at;
        $code = random_int(100000, 999999);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        $mailSent = $this->sendTwoFactorCode($user, (string) $code);

        if ($mailSent) {
            return back()->with('status', 'A new authentication code has been sent to your email.');
        }

        if (app()->environment('local')) {
            return back()->with(
                'status',
                'The verification email could not be sent (mail server unreachable). Use this code: '.$code
            );
        }

        $user->two_factor_code = $previousCode;
        $user->two_factor_expires_at = $previousExpiry;
        $user->save();

        return back()->withErrors([
            'code' => 'The new authentication code could not be sent. Please try again.',
        ]);
    }

    private function sendTwoFactorCode(User $user, string $code): bool
    {
        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($code, \App\Support\EmailGreeting::displayName($user)));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
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
            return response()->view('auth.password_reset_complete');
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
        return view('profile.edit', compact('user'));
    }

    // Update profile and password
    public function update(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_picture' => ['nullable', 'image', 'max:10240'],
        ];
        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $data = $request->validate($rules);

        $changed = false;

        if (! empty($data['password'])) {
            if (Hash::check($data['password'], $user->password)) {
                return back()->withErrors(['password' => 'New password cannot be the same as the current password.']);
            }
            $user->password = Hash::make($data['password']);
            $changed = true;
        }

        if ($request->hasFile('profile_picture')) {
            $user->profile_picture = app(ProfilePictureService::class)->store(
                $request->file('profile_picture'),
                $user->profile_picture
            );
            $changed = true;
        }

        if (! $changed) {
            return redirect()->route('profile.edit')->with('success', 'No changes were made.');
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

public function destroyProfilePicture(Request $request)
{
    $user = auth()->user();

    if (! empty($user->profile_picture)) {
        app(ProfilePictureService::class)->delete($user->profile_picture);
        $user->profile_picture = null;
        $user->save();
    }

    return redirect()->route('profile.edit')->with('success', 'Profile picture removed.');
}

public function profilePicture(User $user)
{
    if (empty($user->profile_picture)) {
        abort(404);
    }

    $disk = Storage::disk('public');
    $path = (string) $user->profile_picture;

    if (!$disk->exists($path)) {
        abort(404);
    }

    $fullPath = $disk->path($path);

    return response()->file($fullPath, [
        'Cache-Control' => 'private, max-age=3600',
    ]);
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
