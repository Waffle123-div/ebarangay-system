<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Open Sans', Arial, sans-serif; background-color: #f8f9fe; padding: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #dc3545; color: #ffffff; padding: 15px; border-radius: 8px 8px 0 0; text-align: center; font-weight: bold; font-size: 18px; }
        .content { padding: 20px 0; color: #32325d; line-height: 1.6; }
        .footer { font-size: 12px; color: #8898aa; text-align: center; margin-top: 20px; border-top: 1px solid #e9ecef; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            E-Barangay Registration Update
        </div>
        <div class="content">
            <p>Maayong adlaw, <strong>{{ $residentName }}</strong>!</p>
            
            <p>Thank you for registering with the E-Barangay System of Barangay Cogon East, Carmen, Cebu.</p>
            
            <p>Unfortunately, we could not approve your account registration at this time. This usually happens for one of the following reasons:</p>
            
            <ul>
                <li><strong>Missing Valid ID:</strong> You did not upload a valid ID during registration.</li>
                <li><strong>Address Mismatch:</strong> The ID you uploaded does not show that you are a legitimate resident of Barangay Cogon East.</li>
                <li><strong>Unclear Details:</strong> The image uploaded was blurry or the details provided do not match our barangay records.</li>
            </ul>

            <p><strong>What to do next?</strong><br>
            Please try registering again and make sure to upload a clear photo of a Valid ID that clearly displays your address in Brgy. Cogon East.</p>
            
            <p>Daghang salamat,<br>
            <strong>Barangay Admin</strong></p>
        </div>
        <div class="footer">
            &copy; 2026 E-Barangay System - Barangay Cogon East, Carmen, Cebu<br>
            This is an automated message. Please do not reply to this email.
        </div>
    </div>
</body>
</html>