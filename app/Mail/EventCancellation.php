<?php

namespace App\Mail;

use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class EventCancellation extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $attendee;
    public $reason;
    public $organizer;

    /**
     * Create a new message instance.
     */
    public function __construct(CalendarEvent $event, array $attendee, ?string $reason = null)
    {
        $this->event = $event;
        $this->attendee = $attendee;
        $this->reason = $reason;
        $this->organizer = Auth::user();
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Event Cancelled: ' . $this->event->subject)
                    ->view('emails.event-cancellation')
                    ->with([
                        'event' => $this->event,
                        'attendee' => $this->attendee,
                        'reason' => $this->reason,
                        'organizer' => $this->organizer
                    ]);
    }
}