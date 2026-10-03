<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

/**
 * Per-member security dashboard: email/phone/identity/2FA state, login
 * history (from audit, never secrets), recovery-code status. Secrets,
 * codes and document bodies are never rendered here.
 */
class SecurityDashboardController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $logins = AuditLog::where('user_id', $user->id)
            ->whereIn('action', ['login', 'login_failed', 'logout', 'mfa.challenge_passed', 'mfa.recovery_used', 'mfa.enabled', 'mfa.disabled', 'password.changed', 'identity.verified'])
            ->latest()->limit(20)->get();
        $summary = $user->verificationSummary();
        $summary['recovery_codes'] = $user->hasMfaEnabled() ? count($user->two_factor_recovery_codes ?? []).' remaining' : 'not configured';
        $summary['member_id'] = $user->member_number;

        return view('security.dashboard', compact('user', 'logins', 'summary'));
    }
}
