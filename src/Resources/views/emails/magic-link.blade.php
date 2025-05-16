<!DOCTYPE html>
<html>
<head>
    <title>Your Magic Login Link</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: white !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Hello {{ $user->name }}</h1>
    <p>You requested a magic login link for your Candle Analytics dashboard.</p>
    <p>Click the button below to log in:</p>
    <a href="{{ $url }}" class="button">Log In to Your Analytics Dashboard</a>
    <p>This link will expire in 15 minutes.</p>
    <p>If you didn't request this link, you can safely ignore this email.</p>

    <div class="footer">
        <p>This email was sent from your Candle Analytics installation.</p>
    </div>
</body>
</html>
