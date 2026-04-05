<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Korva') }} - OTP Verification</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f7; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a1a2e; padding: 24px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .body { padding: 32px 24px; text-align: center; }
        .otp-code { display: inline-block; background: #f0f0f5; color: #1a1a2e; font-size: 32px; font-weight: bold; letter-spacing: 8px; padding: 16px 32px; border-radius: 8px; margin: 24px 0; }
        .message { color: #555; font-size: 14px; line-height: 1.6; }
        .footer { background: #f9f9fb; padding: 16px; text-align: center; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name', 'Korva') }}</h1>
        </div>
        <div class="body">
            <p class="message">Your verification code is:</p>
            <div class="otp-code">{{ $otp }}</div>
            <p class="message">This code will expire in 30 minutes.<br>Do not share this code with anyone.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Korva') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
