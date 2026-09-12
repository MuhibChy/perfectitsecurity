<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isCustomer()) {
            abort(403, 'Access restricted to customers only.');
        }
        if (!$request->user()->is_active) {
            abort(403, 'Your account has been deactivated.');
        }
        return $next($request);
    }
}
