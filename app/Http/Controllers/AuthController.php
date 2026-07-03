<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login form
    public function showLogin()
    {
        // If already logged in, skip the login page and go to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    // Handle the submitted login form
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt checks email+password against the shared users table.
        // The extra is_active=1 condition blocks suspended accounts from the
        // first system from logging in here too.
        // The "remember" checkbox makes the session persist across browser restarts.
        if (Auth::attempt([...$credentials, 'is_active' => 1], $request->boolean('remember'))) {
            $request->session()->regenerate(); // prevents session fixation attacks

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة، أو الحساب موقوف.',
        ])->onlyInput('email');
    }

    // Log the user out and clear their session
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
