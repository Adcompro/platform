<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $digest;
    public $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct($digest, User $recipient)
    {
        $this->digest = $digest;
        $this->recipient = $recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $period = \Carbon\Carbon::parse($this->digest['start_date'])->format('M d') . ' - ' . 
                  \Carbon\Carbon::parse($this->digest['end_date'])->format('M d');
        
        return new Envelope(
            subject: "Weekly Project Digest - {$period}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-digest',
        );
    }
}