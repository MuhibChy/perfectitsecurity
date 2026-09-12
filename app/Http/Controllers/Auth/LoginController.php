<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $lockoutKey = 'login_attempts:' . $email;
        $attempts = Cache::get($lockoutKey, 0);

        // Check if account is locked out
        if ($attempts >= self::MAX_FAILED_ATTEMPTS) {
            $ttl = Cache::get($lockoutKey . ':ttl', self::LOCKOUT_MINUTES);
            AuditLog::log('login_locked', 'auth', null, "Login blocked for {$email} — account locked after " . self::MAX_FAILED_ATTEMPTS . " failed attempts.", ['email' => $email]);

            return back()->withErrors([
                'email' => 'Your account has been temporarily locked due to too many failed login attempts. Please try again in ' . $ttl . ' minutes.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account has been deactivated.']);
            }

            // Clear failed attempts on successful login
            Cache::forget($lockoutKey);
            Cache::forget($lockoutKey . ':ttl');

            $request->session()->regenerate();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditLog::log('login', 'auth', $user, 'User logged in');

            if ($user->isCustomer()) {
                return redirect()->intended(route('portal.dashboard'));
            }
            return redirect()->intended(route('admin.dashboard'));
        }

        // Increment failed attempts
        $newAttempts = $attempts + 1;
        $remaining = self::MAX_FAILED_ATTEMPTS - $newAttempts;

        if ($newAttempts === 1) {
            Cache::put($lockoutKey, $newAttempts, now()->addMinutes(self::LOCKOUT_MINUTES));
            Cache::put($lockoutKey . ':ttl', self::LOCKOUT_MINUTES, now()->addMinutes(self::LOCKOUT_MINUTES));
        } else {
            Cache::put($lockoutKey, $newAttempts, now()->addMinutes(self::LOCKOUT_MINUTES));
        }

        AuditLog::log('login_failed', 'auth', null, 'Failed login attempt for ' . $email . ' (' . $newAttempts . '/' . self::MAX_FAILED_ATTEMPTS . ')', ['email' => $email]);

        $message = 'The provided credentials do not match our records.';
        if ($remaining > 0 && $remaining <= 2) {
            $message .= " You have {$remaining} attempt(s) remaining before your account is temporarily locked.";
        }

        return back()->withErrors([
            'email' => $message,
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        AuditLog::log('logout', 'auth', $request->user(), 'User logged out');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
