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
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt([...$credentials, 'is_active' => 1], $request->boolean('remember'))) {
            $request->session()->regenerate(); // prevents session fixation attacks

            // Update last_login
            $user = Auth::user();
            $user->last_login = now();
            $user->save();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة، أو الحساب موقوف.',
        ])->onlyInput('username');
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
