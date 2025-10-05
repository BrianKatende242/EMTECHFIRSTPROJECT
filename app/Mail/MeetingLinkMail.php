<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Doctor;

class MeetingLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var Doctor */
    public $doctor;

    /** @var string */
    public $messageContent;

    /** @var string */
    public $link;

    /**
     * Create a new message instance.
     */
    public function __construct(Doctor $doctor, string $messageContent, string $link)
    {
        $this->doctor = $doctor;
        $this->messageContent = $messageContent;
        $this->link = $link;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Meeting link from Dr. ' . ($this->doctor->name ?? 'Your Doctor');

        return $this->subject($subject)
                    ->view('emails.meeting-link')
                    ->with([
                        'doctor' => $this->doctor,
                        'messageContent' => $this->messageContent,
                        'link' => $this->link,
                    ]);
    }
}
