<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $doctor;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($doctor, $loginUrl)
    {
        $this->doctor = $doctor;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your one-time login link')
                    ->view('emails.login-link')
                    ->with([
                        'doctor' => $this->doctor,
                        'loginUrl' => $this->loginUrl,
                    ]);
    }
}
