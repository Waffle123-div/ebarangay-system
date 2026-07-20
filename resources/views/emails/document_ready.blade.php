<!DOCTYPE html>
<html>
<head>
    <title>Document Ready for Pick-up</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #0d6efd; margin-bottom: 5px;">Your Document is Ready!</h2>
            <p style="color: #6c757d; margin-top: 0;">Barangay Cogon East</p>
        </div>

        <p>Hello <strong>{{ $documentRequest->resident_name }}</strong>,</p>
        
        <p>Good news! Your requested document is now printed, signed, and ready for pick-up at the barangay hall.</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0dcaf0;">
            <p style="margin: 0 0 10px 0;"><strong>Document:</strong> {{ $documentRequest->document_type }}</p>
            <p style="margin: 0 0 10px 0;"><strong>Tracking Number:</strong> <span style="color: #0d6efd; font-weight: bold;">{{ $documentRequest->tracking_number }}</span></p>
            <p style="margin: 0;"><strong>Status:</strong> Ready for Pick-up</p>
        </div>

        <p>Please present your tracking number to the front desk staff upon arrival to claim your document.</p>
        
        <br>
        <p style="margin-bottom: 0;">Thank you,</p>
        <p style="margin-top: 5px;"><strong>Barangay Cogon East Administration</strong></p>
    </div>
</body>
</html>