<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class SchoolOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $userType;

    public function __construct(string $otp, string $userType = 'school')
    {
        $this->otp = $otp;
        $this->userType = $userType;
    }

    public function envelope()
    {
        $subject = match ($this->userType) {
            'health_facility' => 'Your Health Facility Login OTP',
            'doctor' => 'Your Doctor Login OTP',
            default => 'Your School Login OTP',
        };

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'userType' => $this->userType,
            ],
        );
    }

    public function build()
    {
        $subject = match ($this->userType) {
            'health_facility' => 'Your Health Facility Login OTP',
            'doctor' => 'Your Doctor Login OTP',
            default => 'Your School Login OTP',
        };

        // Try to embed a PNG logo as CID for better email client support
        $logoCid = null;
        $pngPath = public_path('ketiai-logo.png');
        $this->withSymfonyMessage(function ($message) use (&$logoCid, $pngPath) {
            if (file_exists($pngPath)) {
                // Symfony\Component\Mime\Email supports embedFromPath
                try {
                    $logoCid = $message->embedFromPath($pngPath, 'keti-ai-logo.png');
                } catch (\Throwable $e) {
                    // If embedding fails, leave $logoCid as null
                }
            }
        });

        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'userType' => $this->userType,
                'logoCid' => $logoCid,
            ])
            ->subject($subject);
    }
}
