<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .url-text {
            word-break: break-all;
            color: #007bff;
            font-size: 12px;
            margin-top: 10px;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>
        
        <h1>Welcome, {{ $user->name }}!</h1>
        
        <div class="content">
            <p>Thank you for registering with {{ config('app.name') }}. You're just one step away from accessing your account.</p>
            
            <p>Please click the button below to verify your email address:</p>
            
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
            </div>
            
            <div class="info-box">
                <strong>Account Details:</strong>
                <ul>
                    <li>Email: {{ $user->email }}</li>
                    <li>Company: {{ $user->company ? $user->company->name : 'Not assigned' }}</li>
                    <li>Role: {{ ucfirst(str_replace('_', ' ', $user->role)) }}</li>
                </ul>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ Important:</strong>
                <p style="margin: 10px 0 0 0;">This verification link will expire in 60 minutes for security reasons. If the link expires, you can request a new verification email from the login page.</p>
            </div>
            
            <p>If you're having trouble clicking the button, copy and paste this URL into your browser:</p>
            <p class="url-text">{{ $verificationUrl }}</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message from {{ config('app.name') }}.</p>
            <p>If you did not create an account, no further action is required.</p>
            <p style="margin-top: 15px;">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>