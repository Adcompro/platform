<?php

namespace App\Mail;

use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class CalendarInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $attendee;
    public $organizer;
    public $icsContent;

    /**
     * Create a new message instance.
     */
    public function __construct(CalendarEvent $event, array $attendee)
    {
        $this->event = $event;
        $this->attendee = $attendee;
        $this->organizer = Auth::user();
        $this->icsContent = $this->generateIcsFile();
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Meeting Invitation: ' . $this->event->subject)
                    ->view('emails.calendar-invitation')
                    ->with([
                        'event' => $this->event,
                        'attendee' => $this->attendee,
                        'organizer' => $this->organizer,
                        'acceptUrl' => route('calendar.respond', ['event' => $this->event->id, 'response' => 'accept', 'email' => $this->attendee['email']]),
                        'declineUrl' => route('calendar.respond', ['event' => $this->event->id, 'response' => 'decline', 'email' => $this->attendee['email']]),
                        'tentativeUrl' => route('calendar.respond', ['event' => $this->event->id, 'response' => 'tentative', 'email' => $this->attendee['email']])
                    ])
                    ->attachData($this->icsContent, 'invite.ics', [
                        'mime' => 'text/calendar; charset=UTF-8; method=REQUEST'
                    ]);
    }

    /**
     * Generate ICS file content for calendar invitation
     */
    protected function generateIcsFile()
    {
        $uid = uniqid() . '@' . config('app.url');
        $dtstart = $this->event->start_datetime->format('Ymd\THis');
        $dtend = $this->event->end_datetime->format('Ymd\THis');
        $now = now()->format('Ymd\THis');
        
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Progress App//Calendar//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:REQUEST\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTAMP:{$now}\r\n";
        $ics .= "DTSTART:{$dtstart}\r\n";
        $ics .= "DTEND:{$dtend}\r\n";
        $ics .= "SUMMARY:{$this->event->subject}\r\n";
        
        if ($this->event->location) {
            $ics .= "LOCATION:{$this->event->location}\r\n";
        }
        
        if ($this->event->body) {
            $description = str_replace(["\r\n", "\n", "\r"], "\\n", $this->event->body);
            $ics .= "DESCRIPTION:{$description}\r\n";
        }
        
        $ics .= "ORGANIZER;CN={$this->organizer->name}:mailto:{$this->organizer->email}\r\n";
        
        // Add all attendees
        $attendees = $this->event->attendees;
        if (is_string($attendees)) {
            $attendees = json_decode($attendees, true);
        }
        if (is_array($attendees)) {
            foreach ($attendees as $att) {
                $ics .= "ATTENDEE;CN={$att['name']};RSVP=TRUE:mailto:{$att['email']}\r\n";
            }
        }
        
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "TRANSP:OPAQUE\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";
        
        return $ics;
    }
}