<!DOCTYPE html>
<html>
<head>
    <title>Welcome to E-School</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
<h2>Welcome to E-School!</h2>

<p>Your school has been registered successfully.</p>

<div style="background: #f4f4f4; padding: 15px; border-radius: 5px; display: inline-block;">
    <p><strong>School:</strong> {{ $schoolName }}</p>
    <p><strong>Administrator:</strong> {{ $adminName }}</p>
    <p><strong>Email:</strong> {{ $adminEmail }}</p>
    <p><strong>Phone:</strong> {{ $adminPhone }}</p>
</div>

<p>Best Regards,<br>E-School Team</p>
</body>
</html>
