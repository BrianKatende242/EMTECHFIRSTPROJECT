<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Get statistics for dashboard
        $stats = [
            'doctors' => \App\Models\Doctor::count(),
            'schools' => \App\Models\School::count(),
            'health_facilities' => \App\Models\HealthFacility::count(),
            'revenue' => \App\Models\Payment::where('status', 'completed')->sum('amount'),
        ];

        // Simple list of models we support in the admin panel
        $models = [
            'doctors' => 'Doctors',
            'appointments' => 'Appointments',
            'students' => 'Students',
            'schools' => 'Schools',
            'health-facilities' => 'Health Facilities',
            'patients' => 'Patients',
        ];

        return view('admin.index', compact('models', 'stats'));
    }
}
