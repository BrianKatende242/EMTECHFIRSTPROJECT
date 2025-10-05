<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Simple list of models we support in the admin panel
        $models = [
            'doctors' => 'Doctors',
            'appointments' => 'Appointments',
            'students' => 'Students',
            'schools' => 'Schools',
            'health-facilities' => 'Health Facilities',
        ];

        return view('admin.index', compact('models'));
    }
}
