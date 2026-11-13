<!DOCTYPE html>
<html>
<head>
    <title>Document Request Accepted</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f8fafc; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 28px; border: 1px solid #e2e8f0; border-top: 5px solid #16a34a; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">

        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #1e40af; margin: 0 0 4px 0;">Document Request Accepted</h2>
            <p style="color: #64748b; margin: 0; font-size: 14px;">Barangay Cogon East &mdash; E-Barangay System</p>
        </div>

        <p>Hello <strong>{{ $documentRequest->resident_name }}</strong>,</p>

        <p>Good news! Your document request has been <strong>accepted</strong> by the Barangay Administration and is now <strong>being processed</strong>.</p>

        <div style="background-color: #f8fafc; padding: 16px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #16a34a;">
            <p style="margin: 0 0 8px 0;"><strong>Tracking Number:</strong> <span style="color: #1e40af; font-weight: bold;">{{ $documentRequest->tracking_number }}</span></p>
            <p style="margin: 0 0 8px 0;"><strong>Document:</strong> {{ $documentRequest->document_type }}</p>
            @if(!empty($documentRequest->purpose))
                <p style="margin: 0 0 8px 0;"><strong>Purpose:</strong> {{ $documentRequest->purpose }}</p>
            @endif
            <p style="margin: 0 0 8px 0;"><strong>Fee Amount:</strong> {{ (float) $documentRequest->fee > 0 ? 'PHP ' . number_format((float) $documentRequest->fee, 2) : 'Free of Charge' }}</p>
            <p style="margin: 0;"><strong>Current Status:</strong> <span style="color: #1d4ed8; font-weight: bold;">Processing</span></p>
        </div>

        <p>You will receive another notification once your document is ready for pick-up at the Barangay Hall.</p>

        <div style="text-align: center; margin: 24px 0;">
            <a href="{{ route('resident.request.track') }}" style="background-color: #1e40af; color: #ffffff; padding: 11px 22px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; display: inline-block;">Track Your Request</a>
        </div>

        <p style="font-size: 12px; color: #64748b; word-break: break-all;">
            If the button does not work, open this link in your browser:
            <a href="{{ route('resident.request.track') }}" style="color: #1e40af;">{{ route('resident.request.track') }}</a>
        </p>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 22px 0;">

        <p style="margin-bottom: 0; font-size: 14px;">Thank you,</p>
        <p style="margin-top: 4px; font-size: 14px;"><strong>Barangay Cogon East Administration</strong></p>
    </div>
</body>
</html>

