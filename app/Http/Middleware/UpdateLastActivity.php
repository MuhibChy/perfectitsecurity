<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Throttled presence tracking (§Phase 11).
 * Updates users.last_activity_at at most once per 5 minutes per session —
 * meaningful-request presence without per-hit database writes.
 */
class UpdateLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = $request->user();
        if ($user && !$request->is('healthz') && !$request->is('api/health/*')) {
            $key = 'last_activity_ping';
            $last = $request->session()->get($key, 0);
            if (time() - (int) $last > 300) {
                $request->session()->put($key, time());
                try {
                    $user->forceFill(['last_activity_at' => now()])->saveQuietly();
                } catch (\Throwable $e) {
                }
            }
        }

        return $response;
    }
}
