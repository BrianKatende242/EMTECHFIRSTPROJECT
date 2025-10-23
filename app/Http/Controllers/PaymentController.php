<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\MarzPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $marzPayService;

    public function __construct(MarzPayService $marzPayService)
    {
        $this->marzPayService = $marzPayService;
    }

    // Show pay page for an appointment
    public function showAppointmentPayForm(Appointment $appointment)
    {
        if ($appointment->status !== 'awaiting_payment') {
            return back()->with('error', 'This appointment is not awaiting payment.');
        }
        // Load relations and pass sidebar context so menu renders
        $appointment->load(['school', 'doctor', 'patient', 'healthFacility', 'duration']);
        $school = $appointment->school;
        $doctor = $appointment->doctor;
        $healthFacility = $appointment->healthFacility;
        return view('payments/appointment-pay', compact('appointment', 'school', 'doctor', 'healthFacility'));
    }

    // Initialize API User (one-time setup) - Not needed for MarzPay
    public function initApiUser()
    {
        return response()->json([
            'success' => true,
            'message' => 'MarzPay does not require API user initialization'
        ]);
    }

    // Get API User details - Not applicable for MarzPay
    public function getApiUser()
    {
        return response()->json([
            'success' => true,
            'message' => 'MarzPay uses API key authentication'
        ]);
    }

    // Request payment using MarzPay
    public function requestPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:500|max:10000000',
            'phone_number' => 'required|string|regex:/^\+256\d{9}$/',
            'external_id' => 'nullable|string'
        ]);

        try {
            $data = [
                'amount' => $validated['amount'],
                'phone_number' => $validated['phone_number'],
                'country' => 'UG',
                'reference' => (string) Str::uuid(),
                'description' => 'Payment request',
                'callback_url' => route('marzpay.webhook'),
            ];

            $result = $this->marzPayService->collectMoney($data);

            if (($result['status'] ?? null) === 'success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment collection initiated successfully',
                    'data' => $result['data'],
                    'reference_id' => $result['data']['transaction']['uuid'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initiate payment'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment Request Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing payment'
            ], 500);
        }
    }

    /**
     * Send payment to customer (disbursement)
     */
    public function sendPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:500|max:10000000',
            'phone_number' => 'required|string|regex:/^\+256\d{9}$/',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $data = [
                'amount' => $request->amount,
                'phone_number' => $request->phone_number,
                'country' => 'UG',
                'reference' => (string) Str::uuid(),
                'description' => $request->description ?? 'Payment disbursement',
                'callback_url' => route('marzpay.webhook'),
            ];

            $response = $this->marzPayService->sendMoney($data);

            if (($response['status'] ?? null) === 'success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment sent successfully',
                    'data' => $response['data']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Payment sending failed'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment Sending Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending payment'
            ], 500);
        }
    }

    // Check payment status
    public function paymentStatus($referenceId)
    {
        try {
            $status = $this->marzPayService->getTransaction($referenceId);
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Payment Status Check Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to check payment status'
            ], 500);
        }
    }

    // Get account balance
    public function accountBalance()
    {
        try {
            $balance = $this->marzPayService->getBalance();
            return response()->json([
                'success' => true,
                'data' => $balance
            ]);
        } catch (\Exception $e) {
            Log::error('Account Balance Check Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to check account balance'
            ], 500);
        }
    }

    // Handle MarzPay callback/webhook
    public function handleCallback(Request $request)
    {
        try {
            $payload = $request->all();

            // Basic security checks
            if (!$this->validateWebhookRequest($request)) {
                Log::warning('Invalid webhook request received', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'headers' => $request->headers->all()
                ]);
                return response('Unauthorized', 401);
            }

            // TODO: Implement signature verification when MarzPay provides signature details
            // $signature = $request->header('X-MarzPay-Signature');
            // if (!$this->verifyWebhookSignature($payload, $signature)) {
            //     return response('Invalid signature', 401);
            // }

            Log::info('MarzPay Webhook Received:', $payload);

            // Process webhook based on event type
            $eventType = $payload['event_type'] ?? null;
            $transaction = $payload['transaction'] ?? null;

            if (!$transaction) {
                Log::warning('Invalid MarzPay webhook payload - missing transaction data');
                return response('Invalid webhook payload', 400);
            }

            switch ($eventType) {
                case 'collection.completed':
                    $this->handleSuccessfulCollection($transaction);
                    break;

                case 'collection.failed':
                    $this->handleFailedCollection($transaction);
                    break;

                case 'collection.pending':
                    $this->handlePendingCollection($transaction);
                    break;

                case 'collection.cancelled':
                    $this->handleCancelledCollection($transaction);
                    break;

                default:
                    Log::info('Unhandled MarzPay webhook event type: ' . $eventType);
            }

            return response('Webhook processed successfully', 200);

        } catch (\Exception $e) {
            Log::error('MarzPay Webhook Processing Error: ' . $e->getMessage());
            return response('Webhook processing failed', 500);
        }
    }

    // Appointment checkout from web: triggers a MarzPay payment request for an appointment
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

        // Normalize phone to international format (+256xxxxxxxxx)
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '07')) {
            // 07XXXXXXXX -> +2567XXXXXXXX
            $phone = '+256' . substr($digits, 1);
        } elseif (str_starts_with($digits, '2560')) {
            // 2560XXXXXXXX -> +2567XXXXXXXX (drop the 0)
            $phone = '+256' . substr($digits, 3);
        } elseif (str_starts_with($digits, '0') && strlen($digits) >= 9) {
            // 0XXXXXXXXX -> +256XXXXXXXXX (general fallback)
            $phone = '+256' . substr($digits, 1);
        } elseif (str_starts_with($digits, '256')) {
            $phone = '+' . $digits;
        } else {
            // Last resort: assume already in international without country code is 9 digits starting with 7
            if (strlen($digits) === 9 && $digits[0] === '7') {
                $phone = '+256' . $digits;
            } else {
                $phone = '+' . $digits; // pass as-is with plus
            }
        }

        $amount = $validated['amount'] ?? ($appointment->duration ? $appointment->duration->getPrice() : 1.00); // allow override on pay page

        try {
            $data = [
                'amount' => $amount,
                'phone_number' => $phone,
                'country' => 'UG',
                'reference' => (string) Str::uuid(),
                'description' => 'Appointment payment - ' . $appointment->id,
                'callback_url' => route('marzpay.webhook'),
            ];

            $result = $this->marzPayService->collectMoney($data);

            if (($result['status'] ?? null) === 'success') {
                // Store payment reference on appointment for tracking
                $appointment->payment_reference = $result['data']['transaction']['uuid'] ?? null;
                $appointment->save();

                $message = 'Payment request sent. Please approve on your phone.';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'redirect' => route('payment.appointment.success', $appointment->id),
                        'reference_id' => $appointment->payment_reference
                    ]);
                }
                return back()->with('success', $message);
            }

            $message = $result['message'] ?? 'Failed to initiate payment';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);

        } catch (\Exception $e) {
            Log::error('Appointment Checkout Error: ' . $e->getMessage());
            $message = 'An error occurred while processing payment';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }
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

    /**
     * Handle successful collection webhook
     */
    private function handleSuccessfulCollection($transaction)
    {
        try {
            $reference = $transaction['reference'] ?? null;
            $uuid = $transaction['uuid'] ?? null;

            if ($reference && str_starts_with($reference, 'appointment-')) {
                // This is an appointment payment
                $appointmentId = explode('-', $reference)[1] ?? null;
                if ($appointmentId) {
                    $appointment = Appointment::find($appointmentId);
                    if ($appointment) {
                        $appointment->status = 'confirmed';
                        $appointment->payment_status = 'completed';
                        $appointment->save();

                        Log::info('Appointment payment completed', [
                            'appointment_id' => $appointmentId,
                            'transaction_uuid' => $uuid
                        ]);
                    }
                }
            }

            // TODO: Store transaction record in database
            // TODO: Send payment confirmation notifications

        } catch (\Exception $e) {
            Log::error('Handle Successful Collection Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle failed collection webhook
     */
    private function handleFailedCollection($transaction)
    {
        try {
            $reference = $transaction['reference'] ?? null;

            if ($reference && str_starts_with($reference, 'appointment-')) {
                $appointmentId = explode('-', $reference)[1] ?? null;
                if ($appointmentId) {
                    $appointment = Appointment::find($appointmentId);
                    if ($appointment) {
                        $appointment->payment_status = 'failed';
                        $appointment->save();

                        Log::warning('Appointment payment failed', [
                            'appointment_id' => $appointmentId,
                            'transaction_uuid' => $transaction['uuid'] ?? null
                        ]);
                    }
                }
            }

            // TODO: Send payment failure notifications

        } catch (\Exception $e) {
            Log::error('Handle Failed Collection Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle pending collection webhook
     */
    private function handlePendingCollection($transaction)
    {
        try {
            $reference = $transaction['reference'] ?? null;

            if ($reference && str_starts_with($reference, 'appointment-')) {
                $appointmentId = explode('-', $reference)[1] ?? null;
                if ($appointmentId) {
                    $appointment = Appointment::find($appointmentId);
                    if ($appointment) {
                        $appointment->payment_status = 'pending';
                        $appointment->save();
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Handle Pending Collection Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle cancelled collection webhook
     */
    private function handleCancelledCollection($transaction)
    {
        try {
            $reference = $transaction['reference'] ?? null;

            if ($reference && str_starts_with($reference, 'appointment-')) {
                $appointmentId = explode('-', $reference)[1] ?? null;
                if ($appointmentId) {
                    $appointment = Appointment::find($appointmentId);
                    if ($appointment) {
                        $appointment->payment_status = 'cancelled';
                        $appointment->save();
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Handle Cancelled Collection Error: ' . $e->getMessage());
        }
    }

    /**
     * Validate webhook request for basic security
     */
    private function validateWebhookRequest(Request $request)
    {
        // Check if request is from allowed IPs (if MarzPay provides IP ranges)
        $allowedIps = config('services.marzpay.allowed_ips', []);
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            Log::warning('Webhook request from unauthorized IP: ' . $request->ip());
            return false;
        }

        // Check content type
        if ($request->header('Content-Type') !== 'application/json') {
            Log::warning('Invalid content type for webhook: ' . $request->header('Content-Type'));
            return false;
        }

        // Check if payload is valid JSON
        if (!$request->isJson()) {
            Log::warning('Webhook payload is not valid JSON');
            return false;
        }

        return true;
    }

    /**
     * Verify webhook signature (placeholder for future implementation)
     * TODO: Implement when MarzPay provides signature verification details
     */
    private function verifyWebhookSignature($payload, $signature)
    {
        // Placeholder for signature verification
        // MarzPay may provide HMAC signature verification in the future
        $webhookSecret = config('services.marzpay.webhook_secret');

        if (!$webhookSecret) {
            Log::warning('Webhook secret not configured - signature verification skipped');
            return true; // Allow webhooks if secret not configured (for development)
        }

        // TODO: Implement actual signature verification
        // Example implementation (adjust based on MarzPay's signature method):
        // $expectedSignature = hash_hmac('sha256', json_encode($payload), $webhookSecret);
        // return hash_equals($expectedSignature, $signature);

        return true; // Placeholder - allow all for now
    }
}