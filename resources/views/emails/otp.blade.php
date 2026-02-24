<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>رمز التحقق</title>
    <style>
        body {
            margin: 0; padding: 0;
            background-color: #0f0f0f;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #e0e0e0;
        }
        .container {
            max-width: 520px;
            margin: 40px auto;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #00e676 0%, #00b248 100%);
            padding: 32px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #000;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #003d1a;
        }
        .body {
            padding: 36px 32px;
            text-align: center;
        }
        .body p {
            font-size: 15px;
            color: #aaa;
            margin: 0 0 24px;
            line-height: 1.7;
        }
        .otp-box {
            display: inline-block;
            background: #111;
            border: 2px dashed #00e676;
            border-radius: 12px;
            padding: 18px 40px;
            margin-bottom: 28px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #00e676;
            font-family: 'Courier New', monospace;
        }
        .action-label {
            display: inline-block;
            background: #1e3a29;
            color: #00e676;
            border-radius: 20px;
            padding: 4px 16px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .expiry {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
        }
        .footer {
            background: #111;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #444;
            border-top: 1px solid #222;
        }
        .footer a { color: #00e676; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>رمز التحقق الخاص بك</p>
        </div>
        <div class="body">
            <p>طلبت رمز تحقق للعملية التالية:</p>
            <div class="action-label">{{ $actionType }}</div>
            <br/>
            <div class="otp-box">
                <div class="otp-code">{{ $otpCode }}</div>
            </div>
            <p class="expiry">⏱ ينتهي هذا الرمز خلال <strong style="color:#fff">10 دقائق</strong></p>
            <p style="margin-top:20px;font-size:13px;">إذا لم تطلب هذا الرمز، تجاهل هذا البريد.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} — جميع الحقوق محفوظة
        </div>
    </div>
</body>
</html>
