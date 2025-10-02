<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Invitation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        h1 {
            color: #1e40af;
            margin: 0;
            font-size: 24px;
        }
        .event-details {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
            width: 120px;
        }
        .detail-value {
            color: #111827;
            flex: 1;
        }
        .description {
            margin: 20px 0;
            padding: 15px;
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
        .actions {
            margin: 30px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .btn-accept {
            background-color: #10b981;
            color: white;
        }
        .btn-tentative {
            background-color: #f59e0b;
            color: white;
        }
        .btn-decline {
            background-color: #ef4444;
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .attendees {
            margin: 20px 0;
        }
        .attendee-list {
            list-style: none;
            padding: 0;
        }
        .attendee-item {
            padding: 8px 12px;
            background-color: #f9fafb;
            margin-bottom: 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Meeting Invitation</h1>
            <p style="color: #6b7280; margin-top: 10px;">You have been invited to a meeting</p>
        </div>

        <div class="event-details">
            <h2 style="color: #1e40af; margin-top: 0;">{{ $event->subject }}</h2>
            
            <div class="detail-row">
                <span class="detail-label">📆 Date:</span>
                <span class="detail-value">
                    @if($event->is_all_day)
                        {{ $event->start_datetime->format('l, F j, Y') }}
                        (All Day)
                    @else
                        {{ $event->start_datetime->format('l, F j, Y') }}
                    @endif
                </span>
            </div>
            
            @if(!$event->is_all_day)
            <div class="detail-row">
                <span class="detail-label">⏰ Time:</span>
                <span class="detail-value">
                    {{ $event->start_datetime->format('g:i A') }} - {{ $event->end_datetime->format('g:i A') }}
                    ({{ $event->start_datetime->diffInMinutes($event->end_datetime) }} minutes)
                </span>
            </div>
            @endif
            
            @if($event->location)
            <div class="detail-row">
                <span class="detail-label">📍 Location:</span>
                <span class="detail-value">{{ $event->location }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">👤 Organizer:</span>
                <span class="detail-value">{{ $organizer->name }} ({{ $organizer->email }})</span>
            </div>
        </div>

        @if($event->body)
        <div class="description">
            <h3 style="margin-top: 0; color: #92400e;">📝 Description</h3>
            <p style="margin: 0; white-space: pre-wrap;">{{ $event->body }}</p>
        </div>
        @endif

        @php
            $eventAttendees = $event->attendees;
            if (is_string($eventAttendees)) {
                $eventAttendees = json_decode($eventAttendees, true);
            }
        @endphp
        @if(is_array($eventAttendees) && count($eventAttendees) > 1)
        <div class="attendees">
            <h3 style="color: #1e40af;">👥 Attendees</h3>
            <ul class="attendee-list">
                @foreach($eventAttendees as $att)
                <li class="attendee-item">
                    {{ $att['name'] ?? 'Unknown' }} 
                    @if(isset($att['email']) && $att['email'] !== $att['name'])
                        ({{ $att['email'] }})
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="actions">
            <p style="margin-bottom: 20px; color: #6b7280;">Please respond to this invitation:</p>
            <a href="{{ $acceptUrl }}" class="btn btn-accept">✓ Accept</a>
            <a href="{{ $tentativeUrl }}" class="btn btn-tentative">? Tentative</a>
            <a href="{{ $declineUrl }}" class="btn btn-decline">✗ Decline</a>
        </div>

        <div style="background-color: #eff6ff; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #1e40af;">
                💡 <strong>Tip:</strong> This invitation includes a calendar file (.ics) that you can open with your calendar application to automatically add this event.
            </p>
        </div>

        <div class="footer">
            <p>This invitation was sent from {{ config('app.name') }}</p>
            <p>{{ config('app.url') }}</p>
        </div>
    </div>
</body>
</html>