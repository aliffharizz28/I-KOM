<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body>
    <p>Hello,</p>

    <p>You have requested to reset your password. Click the link below to reset your password:</p>

    <p>
        <a href="{{ url('/reset-password/'.$token.'?email='.$email) }}">
        Reset Password
        </a>
    </p>

    <p>This link will expire in 60 minutes.</p>

    <p>If you did not request a password reset, please ignore this email.</p>

    <p>Regards,<br>{{ config('app.name') }}</p>
</body>
</html>
