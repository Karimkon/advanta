<?php

namespace App\Http\Controllers\Subcontractor;

use App\Http\Controllers\Controller;
use App\Models\Subcontractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SubcontractorAuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('subcontractor')->check()) {
            return redirect()->route('subcontractor.dashboard');
        }

        return view('subcontractor.auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $subcontractor = Subcontractor::where('email', $request->email)->first();

        if (!$subcontractor) {
            return back()->withErrors([
                'email' => 'No subcontractor account found with this email.',
            ])->withInput();
        }

        if ($subcontractor->status !== 'active') {
            return back()->withErrors([
                'email' => 'Your account is inactive. Please contact administration.',
            ])->withInput();
        }

        if (!$subcontractor->password) {
            return back()->withErrors([
                'email' => 'Your account has not been set up for login. Please contact administration.',
            ])->withInput();
        }

        if (Auth::guard('subcontractor')->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ], $request->filled('remember'))) {
            $request->session()->regenerate();

            // Update last login
            $subcontractor->update(['last_login_at' => now()]);

            return redirect()->intended(route('subcontractor.dashboard'));
        }

        return back()->withErrors([
            'password' => 'The provided password is incorrect.',
        ])->withInput();
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('subcontractor')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('subcontractor.login');
    }
}
