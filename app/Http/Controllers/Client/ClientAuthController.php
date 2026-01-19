<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAuthController extends Controller
{
    /**
     * Show client login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }

        return view('client.auth.login');
    }

    /**
     * Handle client login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('client')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login
            Auth::guard('client')->user()->update([
                'last_login_at' => now()
            ]);

            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Handle client logout
     */
    public function logout(Request $request)
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
