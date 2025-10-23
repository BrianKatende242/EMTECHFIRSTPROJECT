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
            $doctor = \App\Models\Doctor::with(['school', 'healthFacility', 'appointments' => function($q) {
                $q->latest()->take(5);
            }, 'availabilities'])->find($doctorId);

            if ($doctor) {
                // Calculate statistics
                $totalAppointments = $doctor->appointments()->count();
                $completedAppointments = $doctor->appointments()->where('status', 'completed')->count();
                $upcomingAppointments = $doctor->appointments()->whereIn('status', ['pending', 'scheduled'])->count();
                $cancelledAppointments = $doctor->appointments()->where('status', 'cancelled')->count();

                // Get recent appointments
                $recentAppointments = $doctor->appointments()->with('patient')->latest()->take(5)->get();

                // Get availability summary
                $availabilitySummary = [];
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                foreach ($days as $day) {
                    $availability = $doctor->availabilities()->where('day', $day)->first();
                    $availabilitySummary[$day] = $availability ? $availability->available : false;
                }

                return view('profile.doctor', compact(
                    'doctor',
                    'totalAppointments',
                    'completedAppointments',
                    'upcomingAppointments',
                    'cancelledAppointments',
                    'recentAppointments',
                    'availabilitySummary'
                ));
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
