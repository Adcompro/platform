<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ImportCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $result;
    public $duration;
    public $success;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, array $result, int $duration, bool $success)
    {
        $this->user = $user;
        $this->result = $result;
        $this->duration = $duration;
        $this->success = $success;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->success
            ? 'Teamleader Import Completed Successfully'
            : 'Teamleader Import Failed';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.import-completed',
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
