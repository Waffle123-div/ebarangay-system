<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8f9fa; padding: 40px 20px; text-align: center;">
    
    <div style="max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: left;">
        
        <div style="background-color: #0d6efd; height: 5px; width: 100%;"></div>

        <div style="padding: 40px 30px;">
            <h2 style="color: #212529; font-size: 24px; margin-top: 0; margin-bottom: 20px; font-weight: bold;">
                E-Barangay Support
            </h2>
            
            <p style="color: #495057; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                Hi <strong>{{ $name }}</strong>,
                <br><br>
                We received a request to recover access to your E-Barangay account. Please use the secure verification code below to reset your password.
            </p>

            <div style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 25px; text-align: center; margin-bottom: 30px;">
                <p style="color: #6c757d; font-size: 13px; margin: 0 0 10px 0; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">
                    Your Verification Code
                </p>
                <h1 style="color: #0d6efd; font-size: 46px; margin: 0; letter-spacing: 12px; font-weight: 700;">
                    {{ $code }}
                </h1>
            </div>

            <div style="border-top: 1px solid #e9ecef; padding-top: 20px;">
                <h3 style="color: #212529; font-size: 16px; margin-top: 0; margin-bottom: 8px; font-weight: bold;">
                    Didn't request this?
                </h3>
                <p style="color: #6c757d; font-size: 13px; line-height: 1.5; margin: 0;">
                    If you did not request a password reset, you can safely ignore this email. Someone may have typed your email address by mistake. Do not share your code with anyone.
                </p>
            </div>
        </div>
    </div>

    <p style="color: #adb5bd; font-size: 12px; margin-top: 25px;">
        &copy; {{ date('Y') }} E-Barangay System. Carmen, Cebu, Philippines.<br>
        This is an automated message, please do not reply.
    </p>

</div>