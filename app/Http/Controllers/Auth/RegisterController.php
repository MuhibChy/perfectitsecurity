<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    use \App\Http\Controllers\Concerns\ResolvesPhoneInput;

    public function showRegistrationForm()
    {
        return view('auth.register', [
            'roleOptions' => \App\Support\RoleRegistry::registerable(),
            'isProduction' => app()->environment('production'),
        ]);
    }

    public function register(Request $request)
    {
        $allowedRoles = array_column(\App\Support\RoleRegistry::registerable(), 'name');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'size:2'],
            'country_search' => ['nullable', 'string', 'max:100'],
            'national_number' => ['nullable', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            // Role manipulation is neutralized server-side: unknown or
            // disallowed values fall back to the customer policy outcome.
            'role' => ['nullable', 'string', 'max:50', 'in:'.implode(',', array_merge(['customer'], $allowedRoles))],
        ]);

        $outcome = \App\Support\RoleRegistry::registrationOutcome(
            $validated['role'] ?? 'customer',
            app()->environment('production')
        );

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $this->resolvePhoneInput($request) ?? $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'role' => $outcome['assigned'],
            'requested_role' => $outcome['requested'],
            'role_approval_status' => $outcome['status'],
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            // Don't fail registration if the mailer is down (e.g. mailhog
            // not running locally). User can resend verification later.
            \Illuminate\Support\Facades\Log::warning('Verification email failed to send on registration: '.$e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }
        Auth::login($user);
        $request->session()->regenerate();

        if ($user->hasPendingRoleRequest()) {
            \App\Models\AuditLog::log('role.requested', 'roles', $user, "Role requested at registration: {$user->requested_role} (pending approval).", null, ['requested_role' => $user->requested_role]);

            return redirect()->route('verification.notice')->with('success', 'Account created. Your request for the '.\App\Support\RoleRegistry::displayName($user->requested_role).' role is pending administrator approval.');
        }

        return redirect()->route('verification.notice');
    }
}
