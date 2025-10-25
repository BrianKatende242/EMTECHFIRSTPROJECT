<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\School;
use App\Models\HealthFacility;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Duration;
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
            'duration_id' => 'required|exists:durations,id',
            'appointment_time' => 'required|date',
            'reason' => 'required|string|max:500',
            'patient_id' => 'required|exists:patients,id',
            'school_id' => 'nullable|exists:schools,id',
            'health_facility_id' => 'nullable|exists:health_facilities,id'
        ]);

        // Additional validation
        $validator->after(function ($validator) use ($request) {
            // Parse the appointment time
            try {
                $appointmentDateTime = Carbon::parse($request->appointment_time);

                // Check if appointment is in the past
                if ($appointmentDateTime->isPast()) {
                    $validator->errors()->add('appointment_time', 'Cannot schedule appointments in the past.');
                }
            } catch (\Exception $e) {
                $validator->errors()->add('appointment_time', 'Invalid date or time format.');
                return;
            }

            // Check for time conflicts with existing appointments
            if ($request->filled('doctor_id') && $request->filled('appointment_time') && $request->filled('duration_id')) {
                $appointmentDateTime = Carbon::parse($request->appointment_time);
                $duration = Duration::find($request->duration_id);

                if ($duration) {
                    $proposedStart = $appointmentDateTime;
                    $proposedEnd = $appointmentDateTime->copy()->addMinutes($duration->minutes);

                    // Get existing appointments for this doctor on the same date
                    $existingAppointments = Appointment::where('doctor_id', $request->doctor_id)
                        ->whereDate('appointment_time', $appointmentDateTime->toDateString())
                        ->where('status', '!=', 'cancelled')
                        ->get();

                    foreach ($existingAppointments as $existing) {
                        $existingStart = Carbon::parse($existing->appointment_time);
                        $existingDuration = $existing->duration ?? Duration::find($existing->duration_id);
                        $existingEnd = $existingStart->copy()->addMinutes($existingDuration ? $existingDuration->minutes : 30); // Default 30 mins if duration not found

                        // Check for overlap
                        if (($proposedStart->between($existingStart, $existingEnd) && !$proposedStart->equalTo($existingEnd)) ||
                            ($proposedEnd->between($existingStart, $existingEnd) && !$proposedEnd->equalTo($existingStart)) ||
                            ($proposedStart->lessThanOrEqualTo($existingStart) && $proposedEnd->greaterThan($existingStart))) {
                            $validator->errors()->add('appointment_time', 'This time slot conflicts with an existing appointment for this doctor.');
                            break;
                        }
                    }
                }
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        // Create appointment
        try {
            $appointmentDateTime = Carbon::parse($request->appointment_time);

            $appointment = Appointment::create([
                'doctor_id' => $request->doctor_id,
                'appointment_time' => $appointmentDateTime,
                'duration_id' => $request->duration_id,
                'reason' => $request->reason,
                'status' => 'awaiting_payment',
                'health_facility_id' => $request->health_facility_id,
                'patient_id' => $request->patient_id,
                'school_id' => $request->school_id
            ]);

            // Send confirmation if needed
            // $this->sendAppointmentConfirmation($appointment); // Removed - confirmation now requires payment

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
            } else {
                return redirect()->back()->with('error', 'Appointment creation failed');
            }
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
        // Check if appointment has been paid
        if ($appointment->payment_status === 'completed') {
            $message = 'Cannot cancel appointment that has already been paid.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }

            return redirect()->back()->with('error', $message);
        }

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

    /**
     * Delete a cancelled appointment
     */
    public function destroy(Request $request, Appointment $appointment)
    {
        // Only allow deletion of cancelled appointments
        if ($appointment->status !== 'cancelled') {
            $message = 'Only cancelled appointments can be deleted.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $appointment->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Appointment deleted successfully']);
        }

        return redirect()->back()->with('success', 'Appointment deleted successfully');
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
                   "Duration: {$appointment->duration->minutes} mins\n" .
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