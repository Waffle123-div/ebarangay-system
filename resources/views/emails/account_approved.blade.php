<!DOCTYPE html>
<html>
<head>
    <title>Account Approved</title>
</head>
<body style="font-family: 'Open Sans', Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f8f9fe; padding: 20px;">
    
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 5px solid #0d6efd;">
        <h2 style="color: #0d6efd; margin-top: 0;">E-Barangay System</h2>
        
        <p style="font-size: 16px;">Hello <strong>{{ $user->name }}</strong>,</p>
        
        <p style="font-size: 16px;">Great news! Your residency documents have been reviewed, and your account has been <strong>approved</strong> by the barangay administration.</p>
        
        <p style="font-size: 16px;">You can now log in to your dashboard to request documents, track your requests, and access barangay services.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('resident.login') }}" style="background-color: #0d6efd; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;">Login to Your Account</a>
        </div>
        
        <p style="font-size: 14px; color: #6c757d;">If the button doesn't work, you can copy and paste this link directly into your browser:</p>
        <p style="font-size: 14px; word-break: break-all;"><a href="{{ route('resident.login') }}" style="color: #0d6efd;">{{ route('resident.login') }}</a></p>
        
        <hr style="border: none; border-top: 1px solid #e3e6f0; margin: 30px 0;">
        
        <p style="font-size: 14px; color: #8898aa; margin-bottom: 0;">Thank you,<br><strong>The Barangay Administration</strong></p>
    </div>

</body>
</html>