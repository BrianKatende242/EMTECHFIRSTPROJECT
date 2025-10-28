<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has admin privileges
        if (!auth()->check() || !auth()->user()->is_admin) {
            return redirect()->route('login')->with('error', 'Access denied. Admin privileges required.');
        }

        // Check if admin session has exceeded 6 hours
        $user = auth()->user();
        $lastActivity = session('last_activity');

        if ($lastActivity) {
            $inactiveHours = now()->diffInHours($lastActivity);
            if ($inactiveHours >= 6) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Your admin session has expired due to inactivity. Please log in again.');
            }
        }

        // Update last activity time
        session(['last_activity' => now()]);

        return $next($request);
    }
}
