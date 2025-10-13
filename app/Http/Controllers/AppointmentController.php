<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\School;
use App\Models\HealthFacility;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function store(Request $request)
{
    \Log::info('Appointment request data:', $request->all());

    // Validate request
    $validator = Validator::make($request->all(), [
        'doctor_id' => 'required|exists:doctors,id',
        'duration' => 'required|in:15,20,30,45,60',
        'appointment_date' => 'required|date|after:today',
        'appointment_time' => 'required|date_format:H:i',
        'reason' => 'required|string|max:500',
        'patient_id' => 'required|exists:patients,id',
        'school_id' => 'nullable|exists:schools,id',
        'health_facility_id' => 'nullable|exists:health_facilities,id'
    ]);

    // Additional validation
    $validator->after(function ($validator) use ($request) {
        // Combine date and time safely
        try {
            $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

            // Check if appointment is at least 1 hour in advance
            if (now()->diffInHours($appointmentDateTime) < 1) {
                $validator->errors()->add('appointment_time', 'Appointments must be scheduled at least 1 hour in advance.');
            }
        } catch (\Exception $e) {
            $validator->errors()->add('appointment_date', 'Invalid date or time format.');
            return;
        }

        // Validate patient belongs to the institution
        $patient = Patient::find($request->patient_id);
        if ($patient) {
            if ($request->filled('health_facility_id') && $patient->health_facility_id != $request->health_facility_id) {
                $validator->errors()->add('patient_id', 'Patient does not belong to this health facility');
            }
            if ($request->filled('school_id') && $patient->school_id != $request->school_id) {
                $validator->errors()->add('patient_id', 'Patient does not belong to this school');
            }
        }
    });

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // Create appointment
    try {
    $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);

    $appointment = Appointment::create([
            'doctor_id' => $request->doctor_id,
            'appointment_time' => $appointmentDateTime,
            'duration' => (int)$request->duration,
            'reason' => $request->reason,
            'status' => 'awaiting_payment',
            'health_facility_id' => $request->health_facility_id,
            'patient_id' => $request->patient_id,
            'school_id' => $request->school_id
        ]);

        // Send confirmation if needed
        $this->sendAppointmentConfirmation($appointment);

        // Check if this is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Appointment scheduled successfully',
                'appointment' => $appointment->load(['patient', 'doctor'])
            ]);
        }

        // Redirect based on context
        if ($appointment->health_facility_id) {
            return redirect()->route('health-facility.book-doctor', ['id' => $appointment->health_facility_id])
                ->with('success', 'Appointment booked successfully');
        }
        if ($appointment->school_id) {
            return redirect()->route('book-doctor', ['school' => $appointment->school_id])
                ->with('success', 'Appointment booked successfully');
        }
        return redirect()->back()->with('success', 'Appointment booked successfully');

    } catch (\Exception $e) {
        \Log::error('Appointment creation failed: '.$e->getMessage());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment creation failed',
                'error' => $e->getMessage()
            ], 500);
        }

        return redirect()->back()->with('error', 'Appointment creation failed');
    }
}
    public function index(Request $request)
    {
        $query = Appointment::query();
        
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id)
                  ->with(['patient', 'doctor']);
        } 
        elseif ($request->has('health_facility_id')) {
            $query->where('health_facility_id', $request->health_facility_id)
                  ->with(['patient', 'doctor']);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'Must specify school_id or health_facility_id'
            ], 400);
        }

        $appointments = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    public function checkStatus($referenceId)
    {
        $appointment = Appointment::where('payment_reference', $referenceId)
            ->with(['patient', 'doctor'])
            ->firstOrFail();

        return response()->json([
            'status' => $appointment->status,
            'appointment' => $appointment
        ]);
    }

    /**
     * Mark an appointment as cancelled
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $appointment->status = 'cancelled';
        $appointment->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => 'cancelled']);
        }

        return redirect()->back()->with('success', 'Appointment cancelled');
    }

    /**
     * Mark an appointment as completed
     */
    public function complete(Request $request, Appointment $appointment)
    {
        $appointment->status = 'completed';
        $appointment->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => 'completed']);
        }

        return redirect()->back()->with('success', 'Appointment marked completed');
    }

    protected function sendAppointmentConfirmation(Appointment $appointment)
    {
        $user = $appointment->patient;
        $institution = $appointment->school ?? $appointment->healthFacility;
        $doctor = $appointment->doctor;
        
        $message = "Appointment Confirmed:\n\n" .
                   "Patient: {$user->name}\n" .
                   "Doctor: Dr. {$doctor->name}\n" .
                   "Type: " . ($doctor->specialization === 'General Practitioner' ? 'General' : 'Specialist') . "\n" .
                   "Duration: {$appointment->duration} mins\n" .
                   "Time: {$appointment->appointment_time->format('D, M j, Y g:i A')}\n" .
                   "Reason: {$appointment->reason}";

        // Send to appropriate contacts
        if ($appointment->patient) {
            $contactNumber = $appointment->patient->contact_number ?? $appointment->patient->parent_contact;
            if ($contactNumber) {
                $this->sendSms($contactNumber, $message);
            }
        }

        // Send to institution
        if ($institution && $institution->contact_number) {
            $this->sendSms($institution->contact_number, $message);
        }
    }

    protected function sendSms($number, $message)
    {
        // SMS sending implementation
        \Log::info('SMS would be sent to: ' . $number, ['message' => $message]);
    }
}