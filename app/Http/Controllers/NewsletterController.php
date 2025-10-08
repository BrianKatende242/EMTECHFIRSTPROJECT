<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsletterSubscriber;
use App\Mail\VerifyNewsletterSubscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first('email')
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));

        DB::beginTransaction();

        try {
            // Case-insensitive lookup
            $existing = NewsletterSubscriber::whereRaw('LOWER(email) = ?', [$email])->first();

            if ($existing) {
                // If never verified, (re)send verification email and ensure inactive until verified
                if (!$existing->isVerified()) {
                    $existing->update([
                        'verification_token' => Str::random(60),
                        'is_active' => false,
                    ]);

                    Mail::to($existing->email)
                        ->send(new VerifyNewsletterSubscription($existing));

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Subscription pending verification. We\'ve sent a new verification email.'
                    ]);
                }

                // If already verified
                if ($existing->is_active) {
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'You\'re already subscribed to the newsletter.'
                    ]);
                }

                // Verified but inactive (previously unsubscribed): reactivate
                $existing->update(['is_active' => true]);
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'You have been resubscribed to the newsletter.'
                ]);
            }

            // New subscriber: create and send verification
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'verification_token' => Str::random(60),
                'is_active' => false
            ]);

            Mail::to($subscriber->email)
                ->send(new VerifyNewsletterSubscription($subscriber));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription successful! Please check your email to verify.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Service temporarily unavailable: ' . $e->getMessage()
            ], 503);
        }
    }

    public function verify($token, Request $request)
    {
        $subscriber = NewsletterSubscriber::where('verification_token', $token)->first();

        if (!$subscriber) {
            if (!$request->wantsJson()) {
                return response()->view('newsletter-verify', [
                    'status' => 'invalid',
                    'email' => null,
                ], 400);
            }
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification token.'
            ], 400);
        }

        if ($subscriber->isVerified()) {
            if (!$request->wantsJson()) {
                return response()->view('newsletter-verify', [
                    'status' => 'already',
                    'email' => $subscriber->email,
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.'
            ]);
        }

        $subscriber->verify();

        if (!$request->wantsJson()) {
            return response()->view('newsletter-verify', [
                'status' => 'success',
                'email' => $subscriber->email,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!'
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->input('email')));

        $subscriber = NewsletterSubscriber::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($subscriber) {
            $subscriber->update(['is_active' => false]);
        }

        // Respond with HTML page if not requesting JSON (e.g., clicked from email link)
        if (!$request->wantsJson()) {
            return response()->view('newsletter-unsubscribed', [
                'email' => $email,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'You have been unsubscribed from the newsletter. If this email was not subscribed, no action was taken.'
        ]);
    }
}