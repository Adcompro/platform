<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Project Digest</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px 10px 0 0; text-align: center; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; border-radius: 0 0 10px 10px; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: bold; color: #1f2937; margin-bottom: 15px; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; }
        .stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f9fafb; padding: 15px; border-radius: 8px; text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #374151; }
        .stat-label { font-size: 12px; color: #6b7280; }
        .risk-item { background: #fef2f2; border-left: 4px solid #ef4444; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .recommendation { background: #f3f4f6; padding: 15px; margin: 10px 0; border-radius: 8px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .button { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1 style="margin: 0;">Weekly Project Digest</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">
                {{ \Carbon\Carbon::parse($digest['start_date'])->format('F d') }} - {{ \Carbon\Carbon::parse($digest['end_date'])->format('F d, Y') }}
            </p>
        </div>

        {{-- Content --}}
        <div class="content">
            {{-- Greeting --}}
            <p>Hi {{ $recipient->name }},</p>
            <p>Here's your weekly project management summary:</p>

            {{-- AI Summary --}}
            @if(isset($digest['ai_summary']))
            <div class="section">
                <h2 class="section-title">Executive Summary</h2>
                <p>{{ $digest['ai_summary'] }}</p>
            </div>
            @endif

            {{-- Project Statistics --}}
            @if(isset($digest['projects']))
            <div class="section">
                <h2 class="section-title">Project Activity</h2>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-value">{{ $digest['projects']['new'] }}</div>
                        <div class="stat-label">New Projects</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $digest['projects']['completed'] }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $digest['projects']['active'] }}</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-card" style="background: #fef2f2;">
                        <div class="stat-value" style="color: #dc2626;">{{ $digest['projects']['at_risk'] }}</div>
                        <div class="stat-label">At Risk</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Time Tracking --}}
            @if(isset($digest['time']))
            <div class="section">
                <h2 class="section-title">Time Tracking</h2>
                <p>
                    <strong>{{ $digest['time']['total_hours'] }} hours</strong> logged this week<br>
                    <strong>{{ $digest['time']['billable_percentage'] }}%</strong> billable rate<br>
                    <strong>{{ $digest['time']['daily_average'] }} hours</strong> daily average
                </p>
            </div>
            @endif

            {{-- Risks --}}
            @if(isset($digest['risks']) && count($digest['risks']) > 0)
            <div class="section">
                <h2 class="section-title">⚠️ Attention Required</h2>
                @foreach(array_slice($digest['risks'], 0, 3) as $risk)
                <div class="risk-item">
                    <strong>{{ $risk['project'] }}</strong><br>
                    {{ $risk['message'] }}
                </div>
                @endforeach
            </div>
            @endif

            {{-- Recommendations --}}
            @if(isset($digest['ai_recommendations']) && count($digest['ai_recommendations']) > 0)
            <div class="section">
                <h2 class="section-title">💡 AI Recommendations</h2>
                @foreach(array_slice($digest['ai_recommendations'], 0, 3) as $rec)
                <div class="recommendation">
                    <strong>{{ $rec['action'] }}</strong><br>
                    <small>{{ $rec['reason'] }}</small>
                </div>
                @endforeach
            </div>
            @endif

            {{-- CTA Buttons --}}
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/dashboard') }}" class="button">View Dashboard</a>
                <a href="{{ route('ai-digest.preview') }}" class="button" style="background: #6b7280;">Full Report</a>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>
                This report was automatically generated by your AI Project Assistant<br>
                Powered by Claude AI • {{ config('app.name') }}
            </p>
            <p>
                <a href="{{ route('ai-digest.index') }}" style="color: #6b7280;">Manage digest settings</a>
            </p>
        </div>
    </div>
</body>
</html>