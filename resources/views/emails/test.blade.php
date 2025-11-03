<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 10px 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AdCompro Progress</h1>
        <p>Project Management System</p>
    </div>
    
    <div class="content">
        <h2>Test Email</h2>
        <p>{{ $testMessage }}</p>
        <p>If you received this email, your mail configuration is working correctly!</p>
        
        <p>This email was sent from the AdCompro Progress system to verify that email delivery is functioning properly.</p>
        
        <a href="https://progress.adcompro.app" class="button">Visit AdCompro Progress</a>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} AdCompro BV. All rights reserved.</p>
        <p>This is an automated message from AdCompro Progress</p>
        <p>
            <a href="https://progress.adcompro.app">progress.adcompro.app</a> | 
            <a href="mailto:noreply@adcompro.app">noreply@adcompro.app</a>
        </p>
    </div>
</body>
</html>