<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !$request->user()->is_active) {
            abort(403, 'Your account has been deactivated.');
        }

        if (!empty($roles)) {
            $hasRole = false;
            foreach ($roles as $role) {
                if ($request->user()->role === $role) {
                    $hasRole = true;
                    break;
                }
            }
            if (!$hasRole) {
                abort(403, 'You do not have permission to access this resource.');
            }
        }

        return $next($request);
    }
}
