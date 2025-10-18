<?php

namespace App\Http\Controllers;
use App\Models\Doctor;
use Illuminate\Http\Request;


class DoctorAvailabilityController extends Controller
{
    public function update(Request $request, Doctor $doctor)
    {
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