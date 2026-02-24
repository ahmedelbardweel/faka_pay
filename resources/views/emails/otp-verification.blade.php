<!DOCTYPE html>
<html>
<head>
    <title>Your Verification Code</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        .logo { font-size: 24px; font-weight: bold; color: #10b981; margin-bottom: 20px; }
        .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #111827; background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 30px 0; display: inline-block; }
        .footer { margin-top: 40px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">{{ config('app.name') }}</div>
        <h2>Verification Code</h2>
        <p>Hello {{ $name }},</p>
        <p>Please use the following verification code to complete your action. This code is valid for 10 minutes.</p>
        
        <div class="otp-code">{{ $otpCode }}</div>
        
        <p>If you did not request this, please ignore this email.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
