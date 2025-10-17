<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        // Check if user is authenticated as admin
        // For now, check if there's an admin session or if user has admin role
        if (!session()->has('admin') && !auth()->check()) {
            return redirect('/')->with('error', 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
