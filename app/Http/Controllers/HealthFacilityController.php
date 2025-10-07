<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HealthFacility;
use Illuminate\Support\Facades\Log;
use App\Models\Message;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Carbon\Carbon;


class HealthFacilityController extends Controller
{
    /**
     * Register a new health facility
     */
    public function registerHealthFacility(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:health_facilities',
                'contact' => 'required|string|max:20',
                'file_url' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error validating health facility: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error validating health facility data',
                'errors' => $e->errors()
            ], 422);
        }

        $healthFacility = HealthFacility::create($validated);

        return response()->json([
            'message' => 'Health facility registered successfully',
            'health_facility' => $healthFacility
        ], 201);
    }

    /**
     * Get all health facilities
     */
   public function getHealthFacilities()
{
    $healthFacilities = HealthFacility::all();
    
    return response()->json([
        'success' => true,
        'data' => $healthFacilities
    ]);
}

    /**
     * Update file URL for most recently created health facility
     */
    public function updateLatestHealthFacilityFile(Request $request)
    {
        $healthFacility = HealthFacility::latest()->first();

        if (!$healthFacility) {
            return response()->json([
                'message' => 'No health facility records found to update'
            ], 404);
        }

        $validated = $request->validate([
            'file_url' => 'required|string|url'
        ]);

        $healthFacility->update(['file_url' => $validated['file_url']]);

        Log::info('Latest health facility file URL updated', [
            'health_facility_id' => $healthFacility->id,
            'file_url' => $validated['file_url']
        ]);

        return response()->json([
            'message' => 'File URL updated for most recent health facility',
            'health_facility_id' => $healthFacility->id,
            'file_url' => $healthFacility->file_url
        ]);
    }

    /**
     * Update specific health facility by ID
     */
    public function updateHealthFacility(Request $request, $id)
    {
        $healthFacility = HealthFacility::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:health_facilities,email,'.$healthFacility->id,
            'contact' => 'sometimes|string|max:20',
            'file_url' => 'nullable|string'
        ]);

        $healthFacility->update($validated);

        return response()->json([
            'message' => 'Health facility updated successfully',
            'health_facility' => $healthFacility
        ]);
    }

    public function showDashboard($id)
    {
        $healthFacility = HealthFacility::findOrFail($id);
    
        $notifications = collect(); 
    
        $allDoctors = Doctor::all(); // <-- fetch all doctors in the DB
    
        $unreadMessages = Message::where('health_facility_id', $id)
                                 ->where('is_read', false)
                                 ->count();
    
        $appointments = Appointment::where('health_facility_id', $id)
                                   ->latest()
                                   ->take(10)
                                   ->get();
    
        $availableDoctors = Doctor::where('health_facility_id', $id)
                                  ->whereHas('availabilities', function ($query) {
                                      $query->where('available', true);
                                  })
                                  ->get();
    
        $messages = Message::where('health_facility_id', $id)
                           ->orderBy('created_at', 'desc')
                           ->get();
    
        $patients = Patient::where('health_facility_id', $id)->get();

        // Metrics
        $patientsCount = $patients->count();
        $appointmentsCount = Appointment::where('health_facility_id', $id)->count();
        $availableDoctorsCount = Doctor::where('health_facility_id', $id)
            ->whereHas('availabilities', function ($query) {
                $query->where('available', true);
            })->count();

        // Build 7-day appointments series (Mon-Sun of current week)
        $startOfWeek = Carbon::now()->startOfWeek();
        $labels = [];
        $series = [];
        for ($i = 0; $i < 7; $i++) {
            $day = (clone $startOfWeek)->addDays($i);
            $labels[] = $day->format('D');
            $countForDay = Appointment::where('health_facility_id', $id)
                ->whereDate('created_at', $day->toDateString())
                ->count();
            $series[] = $countForDay;
        }

        // Doughnut: Appointments by status
        $confirmedCount = Appointment::where('health_facility_id', $id)->where('status', 'confirmed')->count();
        $pendingCount = Appointment::where('health_facility_id', $id)->whereIn('status', ['pending','pending_payment'])->count();
        $completedCount = Appointment::where('health_facility_id', $id)->where('status', 'completed')->count();
        $cancelledCount = Appointment::where('health_facility_id', $id)->where('status', 'cancelled')->count();
        $appointmentStatusLabels = ['Confirmed', 'Pending', 'Completed', 'Cancelled'];
        $appointmentStatusData = [$confirmedCount, $pendingCount, $completedCount, $cancelledCount];

        // Doughnut: Patients by gender
        $maleCount = Patient::where('health_facility_id', $id)->where('gender', 'male')->count();
        $femaleCount = Patient::where('health_facility_id', $id)->where('gender', 'female')->count();
        $otherCount = Patient::where('health_facility_id', $id)->where('gender', 'other')->count();
        $unknownCount = Patient::where('health_facility_id', $id)->whereNull('gender')->orWhere('gender','')->count();
        $genderLabels = ['Male', 'Female', 'Other', 'Unspecified'];
        $genderData = [$maleCount, $femaleCount, $otherCount, $unknownCount];
    
        return view('Health-Facility-Instance', [
            'healthFacility' => $healthFacility,
            'unreadMessages' => $unreadMessages,
            'allDoctors' => $allDoctors,
            'appointments' => $appointments,
            'availableDoctors' => $availableDoctors,
            'patients' => $patients,
            'messages' => $messages,
            'stats' => [
                'patients' => $patientsCount,
                'appointments' => $appointmentsCount,
                'availableDoctors' => $availableDoctorsCount,
                'unreadMessages' => $unreadMessages,
            ],
            'weeklyLabels' => $labels,
            'weeklyData' => $series,
            'appointmentStatusLabels' => $appointmentStatusLabels,
            'appointmentStatusData' => $appointmentStatusData,
            'genderLabels' => $genderLabels,
            'genderData' => $genderData,
        ]);
    }

    public function patients($id)
    {
        $healthFacility = HealthFacility::findOrFail($id);
        $patients = Patient::where('health_facility_id', $id)->latest()->get();
        return view('health-facility/patients', compact('healthFacility', 'patients'));
    }

    public function createPatient($id)
    {
        $healthFacility = HealthFacility::findOrFail($id);
        return view('health-facility/patients-create', compact('healthFacility'));
    }

    public function bookDoctor($id)
    {
        $healthFacility = HealthFacility::findOrFail($id);
        $patients = Patient::where('health_facility_id', $id)->latest()->get();
        $doctors = Doctor::latest()->get();
        return view('health-facility/book-doctor', compact('healthFacility', 'patients', 'doctors'));
    }

    public function labTests($id)
    {
        $healthFacility = HealthFacility::findOrFail($id);
        // Placeholder: if LabTest supports health_facility_id, filter; else show empty list
        $labTests = collect();
        return view('health-facility/lab-tests', compact('healthFacility', 'labTests'));
    }
    


    
}