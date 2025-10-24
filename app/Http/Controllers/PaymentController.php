<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $momoService;

    public function __construct(MomoService $momoService)
    {
        $this->momoService = $momoService;
    }

    // Show pay page for an appointment
    public function showAppointmentPayForm(Appointment $appointment)
    {
        if ($appointment->status !== 'awaiting_payment') {
            // Redirect to appropriate booking page with success message
            if ($appointment->school_id) {
                return redirect()->route('book-doctor', ['school' => $appointment->school_id])
                    ->with('success', 'This appointment has already been confirmed and paid for.');
            } elseif ($appointment->health_facility_id) {
                return redirect()->route('health-facility.book-doctor', ['id' => $appointment->health_facility_id])
                    ->with('success', 'This appointment has already been confirmed and paid for.');
            } else {
                return redirect('/')->with('success', 'This appointment has already been confirmed and paid for.');
            }
        }
        // Load relations and pass sidebar context so menu renders
        $appointment->load(['school', 'doctor', 'patient', 'healthFacility', 'duration']);
        $school = $appointment->school;
        $doctor = $appointment->doctor;
        $healthFacility = $appointment->healthFacility;
        return view('payments/appointment-pay', compact('appointment', 'school', 'doctor', 'healthFacility'));
    }

    // Initialize API User (one-time setup)
    public function initApiUser()
    {
        $result = $this->momoService->createApiUser();
        return response()->json([
            'success' => $result,
            'message' => $result ? 'API user created successfully' : 'Failed to create API user'
        ]);
    }

    // Get API User details
    public function getApiUser()
    {
        $user = $this->momoService->getApiUser();
        return response()->json($user);
    }

    // Request payment
    public function requestPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'phone_number' => 'required|string',
            'external_id' => 'required|string'
        ]);

        $result = $this->momoService->requestToPay(
            $validated['amount'],
            $validated['phone_number'],
            $validated['external_id'],
            'Payment for doctor appointment',
            'School health service'
        );

        return response()->json($result);
    }

    // Check payment status
    public function paymentStatus($referenceId)
    {
        $status = $this->momoService->getPaymentStatus($referenceId);
        return response()->json($status);
    }

    // Get account balance
    public function accountBalance()
    {
        $balance = $this->momoService->getAccountBalance();
        return response()->json($balance);
    }

    // Handle MoMo callback
    public function handleCallback(Request $request)
    {
        Log::info('MoMo Callback Received:', $request->all());
        
        // Process the callback - update your database, etc.
        // $referenceId = $request->input('referenceId');
        // $status = $request->input('status');
        
        return response()->json(['success' => true]);
    }

    // Appointment checkout from web: triggers a MoMo payment request for an appointment
    public function createAppointmentCheckout(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'phone_number' => 'nullable|string',
            'amount' => 'nullable|numeric',
        ]);

        $appointment = Appointment::with(['patient', 'healthFacility'])->findOrFail($validated['appointment_id']);

        // Determine payer phone and amount
        $phone = $validated['phone_number'] ?? ($appointment->patient->contact_number ?? $appointment->patient->parent_contact ?? $appointment->healthFacility->contact ?? null);
        if (!$phone) {
            $message = 'No phone number available for this appointment.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }

        // Normalize phone to MSISDN digits without plus (e.g., 2567XXXXXXXX)
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '07')) {
            // 07XXXXXXXX -> 2567XXXXXXXX
            $digits = '256' . substr($digits, 1);
        } elseif (str_starts_with($digits, '2560')) {
            // 2560XXXXXXXX -> 2567XXXXXXXX (drop the 0)
            $digits = '256' . substr($digits, 3);
        } elseif (str_starts_with($digits, '0') && strlen($digits) >= 9) {
            // 0XXXXXXXXX -> 256XXXXXXXXX (general fallback)
            $digits = '256' . substr($digits, 1);
        }
        if (str_starts_with($digits, '256')) {
            $phone = $digits;
        } else {
            // Last resort: assume already in international without country code is 9 digits starting with 7
            if (strlen($digits) === 9 && $digits[0] === '7') {
                $phone = '256' . $digits;
            } else {
                $phone = $digits; // pass as-is
            }
        }

        $amount = $validated['amount'] ?? ($appointment->duration ? $appointment->duration->getPrice() : 1.00); // allow override on pay page

        // External ID ties request to this appointment
        $externalId = 'appointment-' . $appointment->id . '-' . time();

    $result = $this->momoService->requestToPay($amount, $phone, $externalId, 'Appointment payment', 'KETI AI');

        if (!($result['success'] ?? false)) {
            $message = $result['message'] ?? 'Failed to initiate payment';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }

        // Store payment reference on appointment for tracking
        $appointment->payment_reference = $result['reference_id'] ?? null;
        // Keep status as awaiting_payment until confirmed by callback or manual success
        $appointment->save();

        $message = 'Payment request sent. Please approve on your phone.';
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'redirect' => route('payment.appointment.success', $appointment->id)]);
        }
        return back()->with('success', $message);
    }

    // Mark appointment as paid (manual success landing)
    public function appointmentSuccess(Appointment $appointment)
    {
        // Change status to confirmed and send email to doctor
        $appointment->status = 'confirmed';
        $appointment->save();

        // Send email notification to doctor
        $this->sendAppointmentConfirmationEmail($appointment);

        return redirect()->back()->with('success', 'Payment confirmed and appointment marked as confirmed.');
    }

    // Dummy payment confirmation for testing (bypasses actual payment)
    public function confirmPaymentDummy(Request $request, Appointment $appointment)
    {
        // Only allow confirmation if appointment is awaiting payment
        if ($appointment->status !== 'awaiting_payment') {
            return response()->json([
                'success' => false,
                'message' => 'Appointment is not awaiting payment confirmation.'
            ], 400);
        }

        // Change status to confirmed
        $appointment->status = 'confirmed';
        $appointment->save();

        // Send email notification to doctor
        $this->sendAppointmentConfirmationEmail($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully. Doctor has been notified.'
        ]);
    }

    // Send appointment confirmation email to doctor
    protected function sendAppointmentConfirmationEmail(Appointment $appointment)
    {
        $doctor = $appointment->doctor;
        $patient = $appointment->patient;
        $institution = $appointment->school ?? $appointment->healthFacility;

        if ($doctor && $doctor->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($doctor->email)->send(
                    new \App\Mail\AppointmentConfirmationMail($appointment, $doctor, $patient, $institution)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send appointment confirmation email: ' . $e->getMessage());
            }
        }
    }

    // Cancel payment flow for appointment
    public function appointmentCancel(Appointment $appointment)
    {
        // Optionally set a specific status; keep awaiting_payment so user can retry
        return redirect()->back()->with('error', 'Payment was canceled. You can try again.');
    }
}