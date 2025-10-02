<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notification' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .line {
            margin-bottom: 20px;
            color: #555;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white !important;
            padding: 14px 35px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }
        .button:hover {
            background: #5a67d8;
        }
        .footer {
            padding: 20px 30px;
            background: #f8f9fa;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .subcopy {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Header --}}
        <div class="header">
            <h1>AdCompro Progress</h1>
            <p style="margin: 5px 0; opacity: 0.9;">Project Management System</p>
        </div>

        {{-- Content --}}
        <div class="content">
            {{-- Greeting --}}
            @if (! empty($greeting))
                <div class="greeting">{{ $greeting }}</div>
            @else
                @if ($level === 'error')
                    <div class="greeting">Whoops!</div>
                @else
                    <div class="greeting">Hello!</div>
                @endif
            @endif

            {{-- Intro Lines --}}
            @foreach ($introLines as $line)
                <div class="line">{{ $line }}</div>
            @endforeach

            {{-- Action Button --}}
            @isset($actionText)
                <div class="button-container">
                    <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
                </div>
            @endisset

            {{-- Outro Lines --}}
            @foreach ($outroLines as $line)
                <div class="line">{{ $line }}</div>
            @endforeach

            {{-- Salutation --}}
            @if (! empty($salutation))
                <div style="margin-top: 30px;">
                    <div>{{ $salutation }}</div>
                </div>
            @else
                <div style="margin-top: 30px;">
                    <div>Best regards,<br>AdCompro Progress Team</div>
                </div>
            @endif

            {{-- Subcopy --}}
            @isset($actionText)
                <div class="subcopy">
                    If you're having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below into your web browser:<br>
                    <a href="{{ $actionUrl }}" style="color: #667eea; word-break: break-all;">{{ $actionUrl }}</a>
                </div>
            @endisset
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>© {{ date('Y') }} AdCompro BV. All rights reserved.</p>
            <p>
                <a href="https://progress.adcompro.app">progress.adcompro.app</a> | 
                <a href="mailto:support@adcompro.app">support@adcompro.app</a>
            </p>
            <p style="margin-top: 10px;">
                This is an automated message from AdCompro Progress.<br>
                Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>