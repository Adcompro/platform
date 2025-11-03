<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Project Digest - {{ \Carbon\Carbon::parse($digest['start_date'])->format('M d') }} - {{ \Carbon\Carbon::parse($digest['end_date'])->format('M d, Y') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background-color: #667eea;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        .period {
            font-size: 14px;
            opacity: 0.9;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .stat-grid {
            width: 100%;
            margin: 20px 0;
        }
        .stat-grid td {
            background: #f9fafb;
            padding: 10px;
            text-align: center;
            width: 25%;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #374151;
        }
        .stat-label {
            font-size: 10px;
            color: #6b7280;
        }
        .risk-item {
            background: #fef2f2;
            border-left: 3px solid #ef4444;
            padding: 8px;
            margin: 8px 0;
        }
        .recommendation {
            background: #f3f4f6;
            padding: 10px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            padding-top: 30px;
            color: #6b7280;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .project-table th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        .project-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Weekly Project Digest</h1>
        <div class="period">
            {{ \Carbon\Carbon::parse($digest['start_date'])->format('F d') }} - {{ \Carbon\Carbon::parse($digest['end_date'])->format('F d, Y') }}
        </div>
    </div>

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
        <h2 class="section-title">Project Overview</h2>
        <table class="stat-grid">
            <tr>
                <td>
                    <div class="stat-value">{{ $digest['projects']['new'] }}</div>
                    <div class="stat-label">New Projects</div>
                </td>
                <td>
                    <div class="stat-value">{{ $digest['projects']['completed'] }}</div>
                    <div class="stat-label">Completed</div>
                </td>
                <td>
                    <div class="stat-value">{{ $digest['projects']['active'] }}</div>
                    <div class="stat-label">Active</div>
                </td>
                <td style="background: #fef2f2;">
                    <div class="stat-value" style="color: #dc2626;">{{ $digest['projects']['at_risk'] }}</div>
                    <div class="stat-label">At Risk</div>
                </td>
            </tr>
        </table>

        @if($digest['projects']['top_projects']->count() > 0)
        <h3 style="font-size: 14px; margin-top: 20px;">Top Performing Projects</h3>
        <table class="project-table">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th style="text-align: right;">Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($digest['projects']['top_projects'] as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td style="text-align: right;">{{ $project->completion_rate }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- Time Tracking --}}
    @if(isset($digest['time']))
    <div class="section">
        <h2 class="section-title">Time Tracking Summary</h2>
        <table>
            <tr>
                <td width="33%"><strong>Total Hours:</strong> {{ $digest['time']['total_hours'] }}</td>
                <td width="33%"><strong>Billable:</strong> {{ $digest['time']['billable_percentage'] }}%</td>
                <td width="34%"><strong>Daily Average:</strong> {{ $digest['time']['daily_average'] }}</td>
            </tr>
        </table>

        @if($digest['time']['top_contributors']->count() > 0)
        <h3 style="font-size: 14px; margin-top: 20px;">Top Contributors</h3>
        <table class="project-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th style="text-align: right;">Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach($digest['time']['top_contributors'] as $contributor)
                <tr>
                    <td>{{ $contributor['user'] }}</td>
                    <td style="text-align: right;">{{ $contributor['hours'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- Invoicing --}}
    @if(isset($digest['invoices']))
    <div class="section">
        <h2 class="section-title">Invoice Summary</h2>
        <table>
            <tr>
                <td width="50%">
                    <strong>Generated:</strong> {{ $digest['invoices']['new_count'] }} invoices<br>
                    <strong>Total Amount:</strong> €{{ number_format($digest['invoices']['total_amount'], 2) }}
                </td>
                <td width="50%">
                    <strong>Collected:</strong> {{ $digest['invoices']['collection_rate'] }}%<br>
                    <strong>Paid Amount:</strong> €{{ number_format($digest['invoices']['paid_amount'], 2) }}
                </td>
            </tr>
        </table>
        
        @if($digest['invoices']['overdue_count'] > 0)
        <div style="background: #fef2f2; padding: 10px; margin-top: 10px;">
            <strong style="color: #dc2626;">⚠ {{ $digest['invoices']['overdue_count'] }} overdue invoices totaling €{{ number_format($digest['invoices']['overdue_amount'], 2) }}</strong>
        </div>
        @endif
    </div>
    @endif

    {{-- Risk Analysis --}}
    @if(isset($digest['risks']) && count($digest['risks']) > 0)
    <div class="section">
        <h2 class="section-title">Risk Analysis</h2>
        @foreach($digest['risks'] as $risk)
        <div class="risk-item">
            <strong>{{ $risk['project'] }}</strong> ({{ ucfirst($risk['severity']) }})<br>
            {{ $risk['message'] }}
        </div>
        @endforeach
    </div>
    @endif

    {{-- AI Recommendations --}}
    @if(isset($digest['ai_recommendations']) && count($digest['ai_recommendations']) > 0)
    <div class="section">
        <h2 class="section-title">AI Recommendations</h2>
        @foreach($digest['ai_recommendations'] as $index => $rec)
        <div class="recommendation">
            <strong>{{ $index + 1 }}. {{ $rec['action'] }}</strong>
            @if($rec['priority'])
            <span style="font-size: 10px; color: 
                {{ $rec['priority'] == 'high' ? '#dc2626' : '' }}
                {{ $rec['priority'] == 'medium' ? '#f59e0b' : '' }}
                {{ $rec['priority'] == 'low' ? '#10b981' : '' }}">
                ({{ ucfirst($rec['priority']) }} Priority)
            </span>
            @endif
            <br>
            @if($rec['reason'])
            <small>{{ $rec['reason'] }}</small><br>
            @endif
            @if($rec['expected_outcome'])
            <small><em>Expected: {{ $rec['expected_outcome'] }}</em></small>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>
            This report was automatically generated by your AI Project Assistant<br>
            Powered by Claude AI • {{ config('app.name') }}<br>
            Generated on {{ now()->format('F d, Y H:i') }}
        </p>
    </div>
</body>
</html>