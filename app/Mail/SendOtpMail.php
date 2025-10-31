<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $expiryMinutes;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, int $expiryMinutes)
    {
        $this->otp = $otp;
        $this->expiryMinutes = $expiryMinutes;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Your OTP Code')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'expiryMinutes' => $this->expiryMinutes,
            ]);
    }
}
