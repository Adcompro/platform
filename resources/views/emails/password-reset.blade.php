<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
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
        .security-note {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Header --}}
        <div class="header">
            <h1>AdCompro Progress</h1>
            <p style="margin: 5px 0; opacity: 0.9;">Password Reset Request</p>
        </div>

        {{-- Content --}}
        <div class="content">
            <div class="greeting">Hello {{ $userName }}!</div>
            
            <p>You are receiving this email because we received a password reset request for your account.</p>
            
            <div class="button-container">
                <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            </div>
            
            <p>This password reset link will expire in <strong>60 minutes</strong>.</p>
            
            <p>If you did not request a password reset, no further action is required. Your password will remain unchanged.</p>
            
            <div class="security-note">
                <strong>🔒 Security Note:</strong><br>
                For your security, this link can only be used once. If you need to reset your password again, please request a new link.
            </div>
            
            <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">
                If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:<br>
                <a href="{{ $resetUrl }}" style="color: #667eea; word-break: break-all; font-size: 11px;">{{ $resetUrl }}</a>
            </p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>© {{ date('Y') }} AdCompro BV. All rights reserved.</p>
            <p>
                <a href="https://progress.adcompro.app">progress.adcompro.app</a> | 
                <a href="mailto:support@adcompro.app">support@adcompro.app</a>
            </p>
            <p style="margin-top: 10px;">
                This is an automated security message from AdCompro Progress.<br>
                Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>