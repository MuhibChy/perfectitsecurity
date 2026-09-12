<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MfaController extends Controller
{
    public function challenge()
    {
        $user = Auth::user();
        if (!$user || !$user->hasMfaEnabled()) {
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

        if (!$totp->verify($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        session(['mfa_passed' => true]);

        return redirect()->intended($user->isCustomer() ? route('portal.dashboard') : route('admin.dashboard'));
    }

    public function setup(TotpService $totp)
    {
        $user = Auth::user();
        abort_unless($user && $user->isStaff(), 403);

        if (!$user->two_factor_secret || $user->hasMfaEnabled()) {
            // Keep existing confirmed secret; generate pending secret in session for re-setup
        }

        $secret = $totp->generateSecret();
        session(['mfa_setup_secret' => $secret]);
        $uri = $totp->getProvisioningUri($secret, $user->email, config('app.name', 'TechSupport'));

        return view('auth.mfa-setup', [
            'secret' => $secret,
            'uri' => $uri,
            'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($uri),
            'enabled' => $user->hasMfaEnabled(),
        ]);
    }

    public function enable(Request $request, TotpService $totp)
    {
        $request->validate(['code' => 'required|string']);
        $user = Auth::user();
        abort_unless($user && $user->isStaff(), 403);

        $secret = session('mfa_setup_secret');
        abort_unless($secret, 422, 'Start MFA setup again.');

        if (!$totp->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Scan the QR code and try again.']);
        }

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ])->save();

        session()->forget('mfa_setup_secret');
        session(['mfa_passed' => true]);

        return redirect()->route('mfa.setup')->with('success', 'Two-factor authentication enabled.');
    }

    public function disable(Request $request, TotpService $totp)
    {
        $request->validate(['code' => 'required|string']);
        $user = Auth::user();
        abort_unless($user && $user->isStaff() && $user->hasMfaEnabled(), 403);

        if (!$totp->verify($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
        ])->save();

        session()->forget('mfa_passed');

        return redirect()->route('mfa.setup')->with('success', 'Two-factor authentication disabled.');
    }
}
