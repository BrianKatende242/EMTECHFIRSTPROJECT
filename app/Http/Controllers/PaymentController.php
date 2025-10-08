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
        ]);

        $appointment = Appointment::with(['patient', 'student'])->findOrFail($validated['appointment_id']);

        // Determine payer phone and amount
        $phone = $appointment->patient->contact_number ?? $appointment->student->parent_contact ?? null;
        if (!$phone) {
            return back()->with('error', 'No phone number available for this appointment.');
        }

        $amount = $appointment->amount ?? 1.00; // fallback minimal amount for sandbox

        // External ID ties request to this appointment
        $externalId = 'appointment-' . $appointment->id . '-' . time();

        $result = $this->momoService->requestToPay($amount, $phone, $externalId, 'Appointment payment', 'KETI AI');

        if (!($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? 'Failed to initiate payment');
        }

        // Store payment reference on appointment for tracking
        $appointment->payment_reference = $result['reference_id'] ?? null;
        // Keep status as awaiting_payment until confirmed by callback or manual success
        $appointment->save();

        return back()->with('success', 'Payment request sent. Please approve on your phone.');
    }

    // Mark appointment as paid (manual success landing)
    public function appointmentSuccess(Appointment $appointment)
    {
        $appointment->status = 'confirmed';
        $appointment->save();
        return redirect()->back()->with('success', 'Payment confirmed and appointment marked as confirmed.');
    }

    // Cancel payment flow for appointment
    public function appointmentCancel(Appointment $appointment)
    {
        // Optionally set a specific status; keep awaiting_payment so user can retry
        return redirect()->back()->with('error', 'Payment was canceled. You can try again.');
    }
}