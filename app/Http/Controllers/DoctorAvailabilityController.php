<?php

namespace App\Http\Controllers;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DoctorAvailabilityController extends Controller
{
    public function update(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

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