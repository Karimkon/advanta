<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('client')->check()) {
            return redirect()->route('client.login');
        }

        $client = Auth::guard('client')->user();

        if (!$client->isActive()) {
            Auth::guard('client')->logout();
            return redirect()->route('client.login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}
