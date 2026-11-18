<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Session authentication for MoCU Graduation Companion staff/admin users.
 */
class LoginController extends Controller
{
    /**
     * Show the login form (or redirect home if already authenticated).
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('SmartGradu_App.auth.login');
    }

    /**
     * Authenticate with email/password and regenerate the session.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => __('These credentials do not match our records.'),
                ]);
        }

        // Only active staff/admin accounts may remain signed in.
        $user = Auth::user();
        if (! $user || ! $user->is_active || ! $user->isStaff()) {
            Auth::logout();

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => __('These credentials do not match our records.'),
                ]);
        }

        // Prevent session fixation after successful login.
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Log out, invalidate the session, and rotate the CSRF token.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
