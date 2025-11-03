<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Cancelled</title>
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
            border-bottom: 2px solid #ef4444;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        h1 {
            color: #dc2626;
            margin: 0;
            font-size: 24px;
        }
        .cancelled-badge {
            display: inline-block;
            background-color: #fee2e2;
            color: #dc2626;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .event-details {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ef4444;
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
        .reason-box {
            margin: 20px 0;
            padding: 15px;
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .notice {
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚫 Event Cancelled</h1>
            <p style="color: #6b7280; margin-top: 10px;">The following event has been cancelled</p>
        </div>

        <div class="cancelled-badge">
            CANCELLED
        </div>

        <div class="event-details">
            <h2 style="color: #dc2626; margin-top: 0; text-decoration: line-through;">{{ $event->subject }}</h2>
            
            <div class="detail-row">
                <span class="detail-label">📆 Was scheduled:</span>
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

        @if($reason)
        <div class="reason-box">
            <h3 style="margin-top: 0; color: #92400e;">📝 Cancellation Reason</h3>
            <p style="margin: 0; white-space: pre-wrap;">{{ $reason }}</p>
        </div>
        @endif

        <div class="notice">
            <p style="margin: 0; font-size: 14px; color: #1e40af;">
                <strong>Please note:</strong> This event has been removed from your calendar. If you had already added it to your personal calendar, please remove it manually.
            </p>
        </div>

        <div style="background-color: #fee2e2; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #dc2626;">
                <strong>⚠️ Important:</strong> If you have any questions about this cancellation, please contact the organizer directly at {{ $organizer->email }}.
            </p>
        </div>

        <div class="footer">
            <p>This cancellation notice was sent from {{ config('app.name') }}</p>
            <p>{{ config('app.url') }}</p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 10px;">
                Cancelled on {{ now()->format('F j, Y \a\t g:i A') }}
            </p>
        </div>
    </div>
</body>
</html>