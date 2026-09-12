<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="color: #2c3e50;">Your Verification Code</h2>
        <p>Dear user,</p>
        <p>Use the following code to verify your email address. This code will expire at <strong>{{ $expiration }}</strong>.</p>
        <p style="font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0;">{{ $otp }}</p>
        <p>If you did not request this email, you can safely ignore it.</p>
        <p>Thank you,<br/>The IT Support Team</p>
    </div>
</body>
</html>
