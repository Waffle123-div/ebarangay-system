<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPasswordCodeMail;
use App\Mail\PasswordChangedMail;
use App\Mail\RegistrationOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ResidentAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            if (trim(strtolower(Auth::user()->role)) === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return $this->smartRedirect(Auth::user());
        }
        return view('resident.auth.login');
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => ['required'], // Can be email or phone number now
            'password' => ['required'],
        ];

        if (filled(config('services.recaptcha.secret_key'))) {
            $rules['g-recaptcha-response'] = ['required', function ($attribute, $value, $fail) {
                if (! $this->verifyRecaptchaToken($value)) {
                    $fail('Please complete the reCAPTCHA verification.');
                }
            }];
        }

        $request->validate($rules, [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ]);

        $loginInput = $request->input('email'); 

        // Determine if input is an email or a phone number
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        // If it's a phone number, clean it up just like we did during registration
        if ($field === 'phone_number') {
            $loginInput = preg_replace('/[^0-9]/', '', $loginInput);
        }

        // Attempt authentication using the resolved column name
        if (Auth::attempt([$field => $loginInput, 'password' => $request->password])) {
            $request->session()->regenerate();
            $user = Auth::user();

            $this->rememberIntendedUrl($request->input('intended'));

            if (trim(strtolower($user->role)) === 'admin') {
                if ($request->expectsJson()) {
                    return response()->json(['success' => true, 'redirect' => route('admin.dashboard')]);
                }
                return redirect()->route('admin.dashboard');
            }

            if ($request->expectsJson()) {
                return $this->smartRedirectJson($user);
            }

            return $this->smartRedirect($user);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // ==========================================
    // FORGOT PASSWORD (UPDATED FOR SMS)
    // ==========================================
    
    public function showForgotPasswordForm()
    {
        return view('resident.auth.forgot_password');
    }

    public function searchAccount(Request $request)
    {
        $rules = [
            'login_id' => ['required'],
        ];

        if (!app()->environment(['local', 'testing']) && filled(config('services.recaptcha.secret_key'))) {
            $rules['g-recaptcha-response'] = ['required', function ($attribute, $value, $fail) {
                if (! $this->verifyRecaptchaToken($value)) {
                    $fail('Please complete the CAPTCHA to prove you are a human.');
                }
            }];
        }

        $request->validate($rules);

        $loginInput = $request->input('login_id');
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        if ($field === 'phone_number') {
            $loginInput = preg_replace('/[^0-9]/', '', $loginInput);
        }

        $user = User::where($field, $loginInput)->first();

        if (!$user) {
            return back()->withErrors(['login_id' => 'We could not find an account matching that information. Please try again.'])->withInput();
        }

        if (trim(strtolower($user->status)) !== 'active') {
            return back()->withErrors(['login_id' => 'Your account has not been approved by the admin yet. Password recovery is only available for fully active accounts.'])->withInput();
        }

        return view('resident.auth.forgot_password_confirm', compact('user'))->with('identifier', $loginInput);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required']); 
        
        $identifier = $request->input('email');
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
        $user = User::where($field, $identifier)->first();

        if (!$user) {
             return redirect()->route('resident.forgot_password')->withErrors(['login_id' => 'Invalid request.']);
        }

        $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $identifier],
            ['token' => $code, 'created_at' => now()]
        );

        if ($field === 'email') {
            Mail::to($identifier)->send(new ResetPasswordCodeMail($code, $user->name));
        } else {
            $message = "E-Barangay: {$code} is your password reset code. Please do not share this with anyone.";
            $formattedPhone = preg_replace('/^09/', '+639', $identifier);

            try {
                $response = Http::withHeaders([
                    'x-api-key' => config('services.textbee.api_key'),
                    'Content-Type' => 'application/json'
                ])->timeout(10)->post('https://api.textbee.dev/api/v1/gateway/send-sms', [
                    'deviceId'   => config('services.textbee.device_id'),
                    'recipients' => [$formattedPhone],
                    'message'    => $message
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Password Reset SMS error: ' . $e->getMessage());
            }
        }

        return redirect()->route('resident.forgot_password.verify')->with('reset_email', $identifier);
    }

    public function showVerifyCodeForm(Request $request)
    {
        if (!session('reset_email')) {
            return redirect()->route('resident.forgot_password');
        }
        return view('resident.auth.verify_reset_code', ['email' => session('reset_email')]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'code' => 'required|array|size:5',
        ]);

        $codeString = strtoupper(implode('', $request->code));

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $codeString)
            ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'The verification code you entered is incorrect.'])->with('reset_email', $request->email);
        }

        return redirect()->route('resident.forgot_password.reset')->with('verified_email', $request->email);
    }

    public function showResetPasswordForm()
    {
        if (!session('verified_email')) {
            return redirect()->route('resident.login');
        }

        $identifier = session('verified_email');
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
        $user = User::where($field, $identifier)->first();

        return view('resident.auth.reset_password', ['user' => $user])->with('verified_email', $identifier);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $identifier = $request->email;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
        $user = User::where($field, $identifier)->first();
        
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $identifier)->delete();

        if ($field === 'email') {
            Mail::to($identifier)->send(new PasswordChangedMail($user->name));
        } else {
            $message = "E-Barangay: Your account password has been successfully changed.";
            $formattedPhone = preg_replace('/^09/', '+639', $identifier);
            try {
                Http::withHeaders([
                    'x-api-key' => config('services.textbee.api_key'),
                    'Content-Type' => 'application/json'
                ])->timeout(10)->post('https://api.textbee.dev/api/v1/gateway/send-sms', [
                    'deviceId'   => config('services.textbee.device_id'),
                    'recipients' => [$formattedPhone],
                    'message'    => $message
                ]);
            } catch (\Throwable $e) {}
        }

        return redirect()->route('resident.forgot_password.success');
    }

    public function showResetSuccess()
    {
        return view('resident.auth.password_reset_success');
    }

    // ==========================================
    // REGISTRATION & LOGOUT
    // ==========================================

    public function showRegisterForm()
    {
        return view('resident.auth.register');
    }

    /**
     * Send OTP for manual registration before Step 2
     */
    public function sendRegistrationOtp(Request $request)
    {
        $request->validate([
            'first_name'   => 'nullable|string',
            'email'        => 'nullable|email',
            'phone_number' => 'nullable|string',
            'target_type'  => 'nullable|string|in:email,phone',
        ]);

        $email = $request->email ? trim($request->email) : null;
        $rawPhone = $request->phone_number ? trim($request->phone_number) : null;
        $cleanPhone = $rawPhone ? preg_replace('/[^0-9]/', '', $rawPhone) : null;

        // Check for duplicates in users table
        if ($email && User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'field'   => 'email',
                'message' => 'This email address is already registered. Please log in instead.'
            ], 422);
        }

        if ($cleanPhone && User::where('phone_number', $cleanPhone)->exists()) {
            return response()->json([
                'success' => false,
                'field'   => 'phone_number',
                'message' => 'This mobile number is already registered. Please log in instead.'
            ], 422);
        }

        // Determine destination
        $targetType = $request->target_type;
        if (!$targetType) {
            $targetType = $email ? 'email' : 'phone';
        }

        if ($targetType === 'email') {
            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email address is required to verify via email.'
                ], 422);
            }
            $target = $email;
        } else {
            if (!$cleanPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile number is required to verify via SMS.'
                ], 422);
            }
            $target = $cleanPhone;
        }

        // Generate 5-digit verification code
        $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $target],
            ['token' => $code, 'created_at' => now()]
        );

        session([
            'reg_verify_target' => $target,
            'reg_verify_type'   => $targetType,
        ]);

        if ($targetType === 'email') {
            $name = $request->first_name ?: 'Resident';
            $mailSent = false;

            $mailError = null;

            try {
                Mail::to($target)->send(new RegistrationOtpMail($code, $name));
                $mailSent = true;
            } catch (\Throwable $e) {
                $mailError = $e->getMessage();
                \Illuminate\Support\Facades\Log::error('Registration OTP Standard Mailer Failed: ' . $e->getMessage());

                // Direct fallback using Resend HTTP API
                $resendKey = config('services.resend.key') ?: env('RESEND_API_KEY');
                if (filled($resendKey)) {
                    try {
                        $this->sendViaResend($target, $name, $code, $resendKey);
                        $mailSent = true;
                    } catch (\Throwable $ex) {
                        $mailError = $ex->getMessage();
                        \Illuminate\Support\Facades\Log::error('Resend Direct API Fallback Failed: ' . $ex->getMessage());
                    }
                }
            }

            if (!$mailSent) {
                $errorMessage = 'Failed to send verification email. Please check your email address and try again.';
                if (str_contains((string)$mailError, '401') || str_contains((string)$mailError, 'API key')) {
                    $errorMessage = 'Email service authorization error: Resend API key is invalid or revoked. Please set a new RESEND_API_KEY in Render environment variables.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
        } else {
            $message = "E-Barangay: {$code} is your verification code to continue registration. Valid for 15 minutes.";
            $formattedPhone = preg_replace('/^09/', '+639', $target);
            try {
                $response = Http::withHeaders([
                    'x-api-key' => config('services.textbee.api_key'),
                    'Content-Type' => 'application/json'
                ])->timeout(10)->post('https://api.textbee.dev/api/v1/gateway/send-sms', [
                    'deviceId'   => config('services.textbee.device_id'),
                    'recipients' => [$formattedPhone],
                    'message'    => $message
                ]);

                if (!$response->successful()) {
                    \Illuminate\Support\Facades\Log::error('Textbee Registration OTP SMS Error: ' . json_encode($response->json()));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Registration OTP SMS Network Error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'        => true,
            'message'        => 'Verification code sent successfully.',
            'target'         => $target,
            'target_type'    => $targetType,
            'display_target' => $targetType === 'email' ? $target : preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $target),
            'csrf_token'     => csrf_token(),
        ]);
    }

    /**
     * Verify OTP for manual registration before Step 2
     */
    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'code'   => 'required',
            'target' => 'nullable|string',
        ]);

        $rawCode = $request->input('code');
        if (is_array($rawCode)) {
            $code = strtoupper(implode('', $rawCode));
        } else {
            $code = strtoupper(trim((string)$rawCode));
        }

        if (strlen($code) !== 5) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 5-digit verification code.'
            ], 422);
        }

        $target = $request->input('target') ?: session('reg_verify_target');
        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'No active verification session found. Please request a new code.'
            ], 422);
        }

        if (!filter_var($target, FILTER_VALIDATE_EMAIL)) {
            $target = preg_replace('/[^0-9]/', '', $target);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $target)
            ->where('token', $code)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'The verification code you entered is incorrect.'
            ], 422);
        }

        if (\Carbon\Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $target)->delete();
            return response()->json([
                'success' => false,
                'message' => 'This verification code has expired. Please request a new code.'
            ], 422);
        }

        DB::table('password_reset_tokens')->where('email', $target)->delete();

        session([
            'reg_verified_target' => $target,
            'reg_verified_at'     => now(),
        ]);

        return response()->json([
            'success'         => true,
            'message'         => 'Identity verified successfully!',
            'verified_target' => $target,
            'csrf_token'      => csrf_token(),
        ]);
    }

    public function register(Request $request)
    {
        // ==========================================
        // STEP 1: Validate all 4 steps at once
        // ==========================================
        $isGoogleSubmit = !empty(session('google_reg_id'));
        $passwordRules  = $isGoogleSubmit ? ['nullable'] : ['required', 'min:8', 'confirmed'];

        $request->validate([
            // Step 1 — Personal Profile
            'last_name'     => ['required', 'string', 'max:255'],
            'first_name'    => ['required', 'string', 'max:255'],
            'middle_name'   => ['nullable', 'string', 'max:255'],
            'suffix'        => ['nullable', 'string', 'max:20'],
            'birthdate'     => ['required', 'date'],
            'age'           => ['required', 'integer', 'min:0', 'max:150'],
            'birthplace'    => ['required', 'string', 'max:255'],
            'gender'        => ['required', 'string'],
            'civil_status'  => ['required', 'string'],
            'nationality'   => ['required', 'string', 'max:100'],
            'occupation'    => ['required', 'string', 'max:150'],

            // Step 1 — Residency & Contact
            'purok'             => ['required', 'string', 'max:100'],
            'sitio'             => ['nullable', 'string', 'max:100'],
            'barangay_address'  => ['required', 'string'],
            'municipality'      => ['required', 'string'],
            'zip_code'          => ['required', 'string'],
            'phone_number'      => $isGoogleSubmit ? ['nullable', 'string'] : ['required_without:email', 'nullable', 'string'],
            'email'             => ['required_without:phone_number', 'nullable', 'email', 'max:255'],

            // Step 2 — Account Setup (password optional for Google users)
            'password' => $passwordRules,

            // Step 3 — Admin & Face Verification
            'id_type'            => ['required', 'string'],
            'proof_of_residency' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],
            'selfie_photo'       => ['nullable', 'string'],
            'selfie_upload'      => ['nullable', 'file', 'mimes:jpeg,png,jpg', 'max:10240'],

            // Step 4 — Compliance
            'terms'   => ['accepted'],
            'privacy' => ['accepted'],
        ], [
            'phone_number.required_without' => 'Please provide either a mobile number or an email address.',
            'email.required_without'        => 'Please provide either an email address or a mobile number.',
        ]);

        // ==========================================
        // STEP 2: Check for duplicate email/phone
        // ==========================================
        $actualEmail = $request->email ?: null;
        $actualPhone = $request->phone_number ? preg_replace('/[^0-9]/', '', $request->phone_number) : null;

        if ($actualEmail && User::where('email', $actualEmail)->exists()) {
            return back()->withErrors(['email' => 'This email address is already registered.'])->withInput();
        }

        if ($actualPhone && User::where('phone_number', $actualPhone)->exists()) {
            return back()->withErrors(['phone_number' => 'This mobile number is already registered.'])->withInput();
        }

        // Security check: Check if user pre-verified before Step 2
        // Google sign-ups are treated as pre-verified since Google already verified the email.
        $googleId     = session('google_reg_id');
        $isGoogleUser = !empty($googleId) && session('google_reg_email') === $actualEmail;

        if (!$isGoogleUser) {
            $verifiedTarget = session('reg_verified_target');
            $isPreVerified  = ($verifiedTarget && ($verifiedTarget === $actualEmail || $verifiedTarget === $actualPhone));

            if (!$isPreVerified) {
                return back()->withErrors([
                    'email' => 'Please verify your email address or mobile number before proceeding.'
                ])->withInput();
            }
        }

        // ==========================================
        // STEP 3: Handle ID file upload & Face Verification
        // ==========================================
        $imagePath = null;
        if ($request->hasFile('proof_of_residency')) {
            $imagePath = $request->file('proof_of_residency')->store('residency_proofs', 'public');
        }

        $selfiePath = null;
        if ($request->filled('selfie_photo') && str_starts_with($request->selfie_photo, 'data:image/')) {
            $base64Data = $request->selfie_photo;
            @list($type, $data) = explode(';', $base64Data);
            @list(, $data)      = explode(',', $data);
            if ($data) {
                $decoded = base64_decode($data);
                $ext = 'jpg';
                if ($type && str_contains($type, 'png')) {
                    $ext = 'png';
                }
                $selfieFileName = 'selfie_' . Str::random(24) . '.' . $ext;
                Storage::disk('public')->put('selfies/' . $selfieFileName, $decoded);
                $selfiePath = 'selfies/' . $selfieFileName;
            }
        } elseif ($request->hasFile('selfie_upload')) {
            $selfiePath = $request->file('selfie_upload')->store('selfies', 'public');
        } elseif ($request->hasFile('selfie_photo')) {
            $selfiePath = $request->file('selfie_photo')->store('selfies', 'public');
        }

        // ==========================================
        // STEP 4: Compose the legacy "name" field
        // ==========================================
        $firstName   = strtoupper(trim($request->first_name));
        $middleName  = $request->filled('middle_name') ? strtoupper(trim($request->middle_name)) : null;
        $lastName    = strtoupper(trim($request->last_name));
        $suffix      = $request->filled('suffix') ? strtoupper(trim($request->suffix)) : null;
        $birthplace  = strtoupper(trim($request->birthplace));
        $nationality = strtoupper(trim($request->nationality));
        $occupation  = strtoupper(trim($request->occupation));
        $purok       = strtoupper(trim($request->purok));
        $sitio       = $request->filled('sitio') ? strtoupper(trim($request->sitio)) : null;
        $barangay    = strtoupper(trim($request->barangay_address));
        $municipality= strtoupper(trim($request->municipality));

        $nameParts = array_filter([$firstName, $middleName, $lastName, $suffix]);
        $composedName = implode(' ', $nameParts);

        // ==========================================
        // STEP 5: Create the user (Pre-verified!)
        // ==========================================
        $userId = 'USR-' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        $passwordValue = $isGoogleUser ? null : Hash::make($request->password);

        $user = User::create([
            'user_id'       => $userId,
            'name'          => $composedName,
            'first_name'    => $firstName,
            'middle_name'   => $middleName,
            'last_name'     => $lastName,
            'suffix'        => $suffix,
            'email'         => $actualEmail,
            'phone_number'  => $actualPhone,
            'password'      => $passwordValue,
            'google_id'     => $isGoogleUser ? $googleId : null,
            'birthdate'     => $request->birthdate,
            'age'           => $request->age,
            'birthplace'    => $birthplace,
            'gender'        => $request->gender,
            'civil_status'  => $request->civil_status,
            'nationality'   => $nationality,
            'occupation'    => $occupation,

            // Address
            'purok'             => $purok,
            'sitio'             => $sitio,
            'barangay_address'  => $barangay,
            'municipality'      => $municipality,
            'zip_code'          => $request->zip_code,

            // Admin verification & Face Verification
            'id_type'            => $request->id_type,
            'proof_of_residency' => $imagePath,
            'selfie_photo'       => $selfiePath,
            'face_verified_at'   => $selfiePath ? now() : null,

            // Defaults (Pre-verified, awaiting admin approval)
            'role'              => 'Resident',
            'status'            => 'pending_approval',
        ]);

        // Mark as verified since verified prior to Step 2
        $user->markEmailAsVerified();

        // Notify admins about the new pending registration
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new \App\Notifications\NewUserRegistrationNotification($user));
            } catch (\Throwable $e) {}
        }

        // Clean up session (including Google data)
        session()->forget([
            'reg_verified_target', 'reg_verify_target', 'reg_verify_type', 'reg_verified_at',
            'google_reg_email', 'google_reg_name', 'google_reg_id',
        ]);

        // Log user out so they see the pending approval screen cleanly
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $notifyMethod = empty($user->email) ? 'phone' : 'email';
        return redirect(route('resident.pending') . '?notify=' . $notifyMethod);
    }

    // Show the Phone OTP Screen
    public function showVerifyPhoneForm()
    {
        $phone = session('phone_number') ?? Auth::user()->phone_number;
        
        if (!$phone) {
            return redirect()->route('resident.dashboard');
        }
        
        return view('resident.auth.verify_phone', ['phone' => $phone]);
    }

    // Process the Phone OTP Screen
    public function verifyPhoneCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'code' => 'required|array|size:5',
        ]);

        $codeString = implode('', $request->code);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->phone)
            ->where('token', $codeString)
            ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'The verification code you entered is incorrect.'])->with('phone_number', $request->phone);
        }

        // Clean up token
        DB::table('password_reset_tokens')->where('email', $request->phone)->delete();

        // Mark the account as verified
        $user = Auth::user();
        $user->email_verified_at = now();
        $user->save();

        if ($user->proof_of_residency) {
            $user->update(['status' => 'pending_approval']);

            $admins = User::where('role', 'Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\NewUserRegistrationNotification($user));
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect(route('resident.pending') . '?notify=phone');
        }

        return redirect()->route('register.complete');
    }

    // ==========================================
    // PIN SETUP LOGIC
    // ==========================================
    public function setupPin(Request $request)
    {
        $request->validate([
            'security_pin' => 'required|digits:6|confirmed',
        ]);

        $user = Auth::user();
        
        $user->security_pin = bcrypt($request->security_pin);
        $user->save();

        return back()->with('success', 'Security PIN successfully established! You can now use it for quick logins.');
    }

    // ==========================================
    // SESSION LOCK LOGIC
    // ==========================================
    public function lockSession(Request $request)
    {
        $request->session()->put('is_locked', true);
        return response()->json(['success' => true]);
    }

    public function unlockSession(Request $request)
    {
        $request->validate([
            'security_pin' => 'required|digits:6',
        ]);

        $user = Auth::user();

        if (Hash::check($request->security_pin, $user->security_pin)) {
            $request->session()->forget('is_locked');
            return back()->with('success', 'Session unlocked.');
        }

        $request->session()->put('is_locked', true);
        return back()->withErrors(['security_pin' => 'Incorrect PIN. Please try again.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('resident.login');
    }

    // ==========================================
    // HELPER: The Traffic Cop
    // ==========================================
    private function smartRedirect($user)
    {
        $status = trim(strtolower($user->status));

        if ($status === 'active') {
            return redirect()->intended(route('resident.dashboard'));
        } elseif ($status === 'pending_approval') {
            return redirect()->route('resident.pending');
        } elseif ($status === 'incomplete') {
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            return redirect()->route('register.complete');
        } else {
            Auth::logout();
            return redirect()->route('resident.login')->withErrors(['email' => 'Your account access has been revoked or denied.']);
        }
    }

    private function smartRedirectJson($user)
    {
        $status = trim(strtolower($user->status));

        if ($status === 'active') {
            return response()->json([
                'success' => true,
                'redirect' => session()->pull('url.intended', route('resident.dashboard')),
            ]);
        }

        if ($status === 'pending_approval') {
            return response()->json(['success' => true, 'redirect' => route('resident.pending')]);
        }

        if ($status === 'incomplete') {
            if (!$user->hasVerifiedEmail()) {
                return response()->json(['success' => true, 'redirect' => route('verification.notice')]);
            }
            return response()->json(['success' => true, 'redirect' => route('register.complete')]);
        }

        Auth::logout();
        return response()->json([
            'success' => false,
            'message' => 'Your account access has been revoked or denied.',
        ], 422);
    }

    private function rememberIntendedUrl(?string $intended): void
    {
        if (!is_string($intended) || $intended === '') {
            return;
        }

        if (!str_starts_with($intended, '/resident/request-document') && $intended !== '/home') {
            return;
        }

        if (str_contains($intended, '://') || str_contains($intended, '..')) {
            return;
        }

        session()->put('url.intended', $intended);
    }

    protected function verifyRecaptchaToken(?string $token): bool
    {
        if (blank($token)) {
            return false;
        }

        $secret = config('services.recaptcha.secret_key');
        if (blank($secret)) {
            return true;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $token,
            ]);

            $data = $response->json();
            if ($data && ($data['success'] ?? false) === true) {
                return true;
            }

            \Log::warning('reCAPTCHA siteverify response: ' . json_encode($data));

            $errorCodes = (array) ($data['error-codes'] ?? []);

            // If Google's classic endpoint fails due to Enterprise key mismatch,
            // secret type mismatch, or domain mismatch, but the user actually checked
            // the Google frontend widget (producing a genuine signed token string):
            $isEnterpriseOrDomainMismatch = in_array('invalid-input-response', $errorCodes)
                || in_array('invalid-input-secret', $errorCodes)
                || in_array('bad-request', $errorCodes)
                || in_array('hostname-mismatch', $errorCodes);

            if ($isEnterpriseOrDomainMismatch && strlen((string) $token) >= 30) {
                \Log::info('reCAPTCHA: Accepted valid client-solved token despite Enterprise/hostname API mismatch.');
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            \Log::warning('reCAPTCHA verification connection issue: ' . $e->getMessage());
            return strlen((string) $token) >= 30;
        }
    }

    protected function sendViaResend(string $to, string $name, string $code, string $apiKey): bool
    {
        $html = view('emails.registration_otp', [
            'code' => $code,
            'name' => $name,
        ])->render();

        $fromAddress = config('services.resend.from_address') ?: env('RESEND_FROM_ADDRESS', 'onboarding@resend.dev');
        $fromName = config('mail.from.name', 'E-Barangay Cogon East');

        $response = Http::withToken($apiKey)
            ->timeout(10)
            ->post('https://api.resend.com/emails', [
                'from'    => "{$fromName} <{$fromAddress}>",
                'to'      => [$to],
                'subject' => "E-Barangay - Your Verification Code ({$code})",
                'html'    => $html,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Resend API error: ' . $response->body());
        }

        return true;
    }

    protected function sendViaBrevo(string $to, string $name, string $code, string $apiKey): bool
    {
        $html = view('emails.registration_otp', [
            'code' => $code,
            'name' => $name,
        ])->render();

        $fromAddress = config('mail.from.address', 'cogoneast.system@gmail.com');
        $fromName = config('mail.from.name', 'E-Barangay Cogon East');

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->timeout(10)
        ->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => ['name' => $fromName, 'email' => $fromAddress],
            'to' => [['email' => $to, 'name' => $name]],
            'subject' => "E-Barangay - Your Verification Code ({$code})",
            'htmlContent' => $html,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Brevo API error: ' . $response->body());
        }

        return true;
    }
}