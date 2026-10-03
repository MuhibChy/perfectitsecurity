<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\QrService;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Two-factor authentication for ALL members (staff + customers).
 * TOTP primary; single-use hashed recovery codes as fallback.
 * QR codes are generated server-side — secrets never leave the platform.
 * 2FA state is independent of identity-verification state.
 */
class MfaController extends Controller
{
    public function challenge()
    {
        $user = Auth::user();
        if (! $user || ! $user->hasMfaEnabled()) {
            return redirect()->intended($user && $user->isCustomer() ? route('portal.dashboard') : route('admin.dashboard'));
        }

        if (session('mfa_passed')) {
            return redirect()->intended($user->isCustomer() ? route('portal.dashboard') : route('admin.dashboard'));
        }

        return view('auth.mfa-challenge');
    }

    public function verify(Request $request, TotpService $totp)
    {
        $request->validate(['code' => 'required|string']);
        $user = Auth::user();
        abort_unless($user && $user->hasMfaEnabled(), 403);

        // Per-account brute-force guard on top of the route throttle:
        // 10 failures lock the challenge for 15 minutes (TOTP codes are
        // 6 digits — throttling alone leaves an unbounded window).
        $lockKey = 'mfa-fail:'.$user->id;
        abort_if(\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($lockKey, 10), 429, 'Too many incorrect codes. Try again in 15 minutes.');

        if ($totp->verify($user->two_factor_secret, $request->code)) {
            \Illuminate\Support\Facades\RateLimiter::clear($lockKey);
            session(['mfa_passed' => true]);
            AuditLog::log('mfa.challenge_passed', 'auth', $user, '2FA challenge passed (TOTP).');

            return redirect()->intended($user->isCustomer() ? route('portal.dashboard') : route('admin.dashboard'));
        }

        // Fallback: single-use recovery code (consumed, never reusable).
        if ($user->consumeRecoveryCode(trim($request->code))) {
            session(['mfa_passed' => true]);
            AuditLog::log('mfa.recovery_used', 'auth', $user, '2FA recovery code consumed at login.');
            \App\Services\ServiceTrackingService::notify($user->id, 'mfa_recovery_used', 'Recovery code used', 'A two-factor recovery code was used to sign in. If this was not you, secure your account immediately.');

            return redirect()->intended($user->isCustomer() ? route('portal.dashboard') : route('admin.dashboard'))
                ->with('warning', 'You signed in with a recovery code. Generate new codes in Security settings.');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($lockKey, 900);
        AuditLog::log('mfa.challenge_failed', 'auth', $user, '2FA challenge failed (invalid code).');

        return back()->withErrors(['code' => 'Invalid authentication code.']);
    }

    public function setup(TotpService $totp, QrService $qr)
    {
        $user = Auth::user();

        $secret = $totp->generateSecret();
        session(['mfa_setup_secret' => $secret]);
        $uri = $totp->getProvisioningUri($secret, $user->email, config('app.name', 'PerfectITSecurity'));

        return view('auth.mfa-setup', [
            'secret' => $user->hasMfaEnabled() ? null : $secret,
            'uri' => $uri,
            'qrUrl' => $qr->dataUri($uri),
            'enabled' => $user->hasMfaEnabled(),
            'recoveryCount' => count($user->two_factor_recovery_codes ?? []),
        ]);
    }

    public function enable(Request $request, TotpService $totp)
    {
        $request->validate(['code' => 'required|string']);
        $user = Auth::user();

        $secret = session('mfa_setup_secret');
        abort_unless($secret, 422, 'Start MFA setup again.');

        if (! $totp->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Scan the QR code and try again.']);
        }

        // One-time recovery codes: shown once, stored as hashes only.
        $codes = collect(range(1, 8))->map(fn () => strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)))->all();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => array_map(fn ($c) => Hash::make($c), $codes),
        ])->save();

        session()->forget('mfa_setup_secret');
        session(['mfa_passed' => true]);
        AuditLog::log('mfa.enabled', 'auth', $user, '2FA enabled with recovery codes issued.');
        \App\Services\ServiceTrackingService::notify($user->id, 'mfa_enabled', 'Two-factor enabled', 'Two-factor authentication is now active on your account.');

        return redirect()->route('mfa.setup')->with([
            'success' => 'Two-factor authentication enabled. Save your recovery codes now — they are shown only once.',
            'recovery_codes' => $codes,
        ]);
    }

    public function disable(Request $request, TotpService $totp)
    {
        // Step-up: password re-authentication + current TOTP required to disable MFA.
        $request->validate(['code' => 'required|string', 'password' => 'required|string']);
        $user = Auth::user();
        abort_unless($user && $user->hasMfaEnabled(), 403);

        abort_unless(Hash::check($request->password, $user->password), 403, 'Password confirmation failed.');

        if (! $totp->verify($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $wasPrivileged = $user->isAdmin() || $user->isFinanceManager();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => [],
        ])->save();

        session()->forget('mfa_passed');
        AuditLog::log('mfa.disabled', 'auth', $user, '2FA disabled'.($wasPrivileged ? ' on a PRIVILEGED account.' : '.'));
        \App\Services\ServiceTrackingService::notify($user->id, 'mfa_disabled', 'Two-factor disabled', 'Two-factor authentication was turned off. If this was not you, contact support immediately.');

        return redirect()->route('mfa.setup')->with('success', 'Two-factor authentication disabled.');
    }

    /** Regenerate recovery codes (old codes invalidated). Requires password + TOTP code. */
    public function regenerateCodes(Request $request, TotpService $totp)
    {
        $request->validate(['code' => 'required|string', 'password' => 'required|string']);
        $user = Auth::user();
        abort_unless($user && $user->hasMfaEnabled(), 403);
        abort_unless(Hash::check($request->password, $user->password), 403, 'Password confirmation failed.');
        abort_unless($totp->verify($user->two_factor_secret, $request->code), 403, 'Invalid authentication code.');

        $codes = collect(range(1, 8))->map(fn () => strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)))->all();
        $user->forceFill(['two_factor_recovery_codes' => array_map(fn ($c) => Hash::make($c), $codes)])->save();
        AuditLog::log('mfa.codes_regenerated', 'auth', $user, 'Recovery codes regenerated; previous codes invalidated.');
        \App\Services\ServiceTrackingService::notify($user->id, 'mfa_codes_regenerated', 'Recovery codes regenerated', 'New recovery codes were issued; old ones no longer work.');

        return back()->with(['success' => 'New recovery codes issued (shown once).', 'recovery_codes' => $codes]);
    }
}
