<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            // Redirect to welcome page - session expired or not logged in
            return redirect('/')->with('error', 'Your session has expired. Please log in again.');
        }

        if (!in_array(Auth::user()->role, $roles)) {
            return redirect('/')->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
