<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if ($request->expectsJson()) {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return response()->json(['success' => true, 'redirect' => '/admin']);
            }
            return response()->json(['success' => false, 'errors' => ['email' => 'Invalid credentials']], 422);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))){
            $request->session()->regenerate();
            return redirect()->intended('/admin');
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Logout from default guard
        try {
            Auth::logout();
        } catch (\Exception $e) {
            // ignore
        }

        // Also attempt to logout doctor guard if present
        try {
            if (Auth::guard('doctor')->check()) {
                Auth::guard('doctor')->logout();
            }
        } catch (\Exception $e) {
            // ignore
        }

        // Invalidate session and regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to external site after logout
        return redirect()->away('https://ketiai.com');
    }
}
