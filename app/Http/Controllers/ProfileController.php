<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(Request $request, $doctorId = null)
    {
        // If a doctor id was provided in the URL, try to load that doctor (preserve context)
        if ($doctorId) {
            $doctor = \App\Models\Doctor::find($doctorId);
            if ($doctor) {
                return view('profile.doctor', compact('doctor'));
            }
        }

        // If a doctor guard is authenticated, show the doctor profile
        if (Auth::guard('doctor')->check()) {
            $doctor = Auth::guard('doctor')->user();
            return view('profile.doctor', compact('doctor'));
        }

        $user = Auth::user();
        // If the web user is an admin, render admin profile or generic user profile
        if ($user && ($user->is_admin ?? false)) {
            return view('profile.user', compact('user'));
        }

        // Default for regular web users
        return view('profile.user', compact('user'));
    }
}
