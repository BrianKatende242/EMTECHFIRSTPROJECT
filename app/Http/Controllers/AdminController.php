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

        // Get monthly data for the current year
        $currentYear = date('Y');
        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = date('M', mktime(0, 0, 0, $month, 1));
            $startDate = date("$currentYear-$month-01");
            $endDate = date("$currentYear-$month-t");

            // Count appointments for this month
            $appointmentsCount = \App\Models\Appointment::whereBetween('appointment_time', [$startDate, $endDate])->count();

            // Sum revenue for this month
            $monthlyRevenue = \App\Models\Payment::where('status', 'completed')
                ->whereHas('appointment', function($query) use ($startDate, $endDate) {
                    $query->whereBetween('appointment_time', [$startDate, $endDate]);
                })
                ->sum('amount');

            $monthlyData[] = [
                'month' => $monthName,
                'appointments' => $appointmentsCount,
                'revenue' => (float) $monthlyRevenue
            ];
        }

        // Simple list of models we support in the admin panel
        $models = [
            'doctors' => 'Doctors',
            'appointments' => 'Appointments',
            'students' => 'Students',
            'schools' => 'Schools',
            'health-facilities' => 'Health Facilities',
            'patients' => 'Patients',
        ];

        return view('admin.index', compact('models', 'stats', 'monthlyData'));
    }
}
