<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Headers;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $testMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($message = null)
    {
        $this->testMessage = $message ?? 'This is a test email from AdCompro Progress system.';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Email from AdCompro Progress',
            from: 'noreply@adcompro.app',
            replyTo: 'noreply@adcompro.app'
        );
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        return new Headers(
            messageId: null,
            references: [],
            text: [
                'X-Mailer' => 'AdCompro Progress',
                'X-Priority' => '1',  // Hoge prioriteit (1 = hoog, 3 = normaal, 5 = laag)
                'X-MSMail-Priority' => 'High',
                'Importance' => 'high',
                // GEEN List-Unsubscribe header (dit maakt het een mailinglijst)
                // GEEN Precedence: bulk (dit maakt het bulk mail)
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            with: [
                'testMessage' => $this->testMessage,
            ],
        );
    }
}