<?php

namespace App\Http\Controllers;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DoctorAvailabilityController extends Controller
{
    public function update(Request $request)
    {
        // For insecure access, require doctor ID parameter
        $doctorId = $request->input('doctor_id') ?? $request->query('doctorId');

        if (!$doctorId) {
            return response()->json(['success' => false, 'message' => 'Doctor ID is required'], 400);
        }

        $doctor = Doctor::findOrFail($doctorId);

        foreach ($request->input('days', []) as $day => $data) {
            $doctor->availabilities()->updateOrCreate(
                ['day' => strtolower($day)],
                [
                    'available' => (bool) ($data['available'] ?? 0),
                    'max_appointments' => $data['max_appointments'] ?? 0,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Availability updated successfully.']);
    }
}