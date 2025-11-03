<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $userName;

    /**
     * Create a new message instance.
     */
    public function __construct($token, $email, $userName = null)
    {
        $this->token = $token;
        $this->email = $email;
        $this->userName = $userName ?? 'User';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password - AdCompro Progress',
            from: 'noreply@adcompro.app',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $resetUrl = url('/reset-password/' . $this->token . '?email=' . urlencode($this->email));
        
        return new Content(
            view: 'emails.password-reset',
            with: [
                'resetUrl' => $resetUrl,
                'userName' => $this->userName,
            ],
        );
    }
}