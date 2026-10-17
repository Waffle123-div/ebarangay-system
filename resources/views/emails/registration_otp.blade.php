<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8f9fa; padding: 40px 20px; text-align: center;">
    
    <div style="max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: left;">
        
        <div style="background-color: #1a56db; height: 5px; width: 100%;"></div>

        <div style="padding: 40px 30px;">
            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <h2 style="color: #1a56db; font-size: 22px; margin: 0; font-weight: bold;">
                    E-Barangay Cogon East
                </h2>
            </div>
            
            <p style="color: #495057; font-size: 15px; line-height: 1.6; margin-bottom: 20px;">
                Hello <strong>{{ $name }}</strong>,
                <br><br>
                Thank you for registering with the E-Barangay Online Portal. Please use the verification code below to verify your email address and continue setting up your account:
            </p>

            <div style="background-color: #f0f4ff; border: 1px solid #d0e0ff; border-radius: 8px; padding: 25px; text-align: center; margin-bottom: 25px;">
                <p style="color: #556987; font-size: 12px; margin: 0 0 10px 0; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">
                    Your 5-Digit Verification Code
                </p>
                <h1 style="color: #1a56db; font-size: 42px; margin: 0; letter-spacing: 10px; font-weight: 700;">
                    {{ $code }}
                </h1>
            </div>

            <p style="color: #6c757d; font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                Enter this code on the registration screen to unlock Step 2 (Account Setup). This code will expire in 15 minutes.
            </p>

            <div style="border-top: 1px solid #e9ecef; padding-top: 15px;">
                <p style="color: #adb5bd; font-size: 12px; line-height: 1.5; margin: 0;">
                    If you did not request this verification code, please ignore this email. Do not share your code with anyone.
                </p>
            </div>
        </div>
    </div>

    <p style="color: #adb5bd; font-size: 12px; margin-top: 25px;">
        &copy; {{ date('Y') }} E-Barangay System — Barangay Cogon East, Carmen, Cebu.<br>
        This is an automated security notification.
    </p>

</div>
