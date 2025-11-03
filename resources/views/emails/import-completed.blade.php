<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Import Completed' : 'Import Failed' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: {{ $success ? '#10b981' : '#ef4444' }};
            color: white;
            padding: 32px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 32px 24px;
        }
        .stats {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .stat-row:last-child {
            border-bottom: none;
        }
        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }
        .stat-value {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: {{ $success ? '#10b981' : '#ef4444' }};
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            margin: 24px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 24px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .error-box {
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
            color: #991b1b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                @if($success)
                    ✅ Import Completed Successfully
                @else
                    ❌ Import Failed
                @endif
            </h1>
            <p>Teamleader Customer Import</p>
        </div>

        <div class="content">
            <p>Hi {{ $user->name }},</p>

            @if($success)
                <p>Your Teamleader import has been completed successfully! Here are the results:</p>

                <div class="stats">
                    @if(isset($result['customers_imported']))
                    <div class="stat-row">
                        <span class="stat-label">Customers Imported</span>
                        <span class="stat-value">{{ $result['customers_imported'] }}</span>
                    </div>
                    @endif

                    @if(isset($result['customers_skipped']))
                    <div class="stat-row">
                        <span class="stat-label">Customers Skipped</span>
                        <span class="stat-value">{{ $result['customers_skipped'] }}</span>
                    </div>
                    @endif

                    @if(isset($result['contacts_imported']) && $result['contacts_imported'] > 0)
                    <div class="stat-row">
                        <span class="stat-label">Contacts Imported</span>
                        <span class="stat-value">{{ $result['contacts_imported'] }}</span>
                    </div>
                    @endif

                    @if(isset($result['projects_imported']) && $result['projects_imported'] > 0)
                    <div class="stat-row">
                        <span class="stat-label">Projects Imported</span>
                        <span class="stat-value">{{ $result['projects_imported'] }}</span>
                    </div>
                    @endif

                    <div class="stat-row">
                        <span class="stat-label">Duration</span>
                        <span class="stat-value">{{ gmdate('i:s', $duration) }} minutes</span>
                    </div>
                </div>

                <p>You can now view and work with the imported data in your application.</p>

                <center>
                    <a href="{{ url('/customers') }}" class="button">View Customers</a>
                </center>
            @else
                <p>Unfortunately, your Teamleader import has failed. Please review the error below:</p>

                <div class="error-box">
                    <strong>Error Message:</strong><br>
                    {{ $result['error'] ?? 'Unknown error occurred' }}
                </div>

                <p><strong>Duration before failure:</strong> {{ gmdate('i:s', $duration) }} minutes</p>

                <p>Please try again or contact support if the problem persists.</p>

                <center>
                    <a href="{{ url('/settings/teamleader') }}" class="button">Try Again</a>
                </center>
            @endif

            <p style="margin-top: 32px;">
                <small style="color: #6b7280;">
                    This import ran in the background, so you could continue working while it processed.
                    @if($success)
                        All data has been safely saved to the database.
                    @endif
                </small>
            </p>
        </div>

        <div class="footer">
            <p>
                This email was sent by {{ config('app.name') }}<br>
                <a href="{{ url('/') }}" style="color: #6b7280;">{{ url('/') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
