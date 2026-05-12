<!DOCTYPE html>
<html>
<head>
    <title>Your Two-Factor Authentication Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px;">
    <h2 style="color: #2d3748;">Two-Factor Authentication</h2>
    <p>Hello,</p>
    <p>Your two-factor authentication code is:</p>
    <div style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #b2cd16; padding: 20px; background: #f7fafc; display: inline-block; border-radius: 10px; margin: 10px 0;">
        {{ $code }}
    </div>
    <p>This code will expire in 10 minutes.</p>
    <p>If you did not request this, please ignore this email.</p>
    <br>
    <p>Regards,<br>Leave Management</p>

    <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">&copy; {{ date('Y') }} Moepi Publishing. All rights reserved.</p>
</body>
</html>