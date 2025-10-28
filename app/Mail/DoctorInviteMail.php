<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inviteUrl;
    public $invite;

    /**
     * Create a new message instance.
     */
    public function __construct($inviteUrl, $invite = null)
    {
        $this->inviteUrl = $inviteUrl;
        $this->invite = $invite;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Doctor Registration Invite',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-invite',
            with: [
                'inviteUrl' => $this->inviteUrl,
                'invite' => $this->invite,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}