<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMfaVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        // Only enforce MFA for staff who have enabled it.
        if ($user->isStaff() && $user->hasMfaEnabled() && !session('mfa_passed')) {
            if ($request->routeIs('mfa.*') || $request->routeIs('logout')) {
                return $next($request);
            }
            return redirect()->route('mfa.challenge');
        }

        return $next($request);
    }
}
