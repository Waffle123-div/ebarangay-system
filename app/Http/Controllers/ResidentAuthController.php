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
use Illuminate\Support\Facades\Mail;

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

        if (filled(env('RECAPTCHA_SECRET_KEY'))) {
            $rules['g-recaptcha-response'] = ['required', function ($attribute, $value, $fail) {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $value,
                ]);
                if (! $response->json('success')) {
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
        // THE FIX: Changed 'email' to 'login_id' to match the updated view
        $rules = [
            'login_id' => ['required'],
        ];

        if (!app()->environment('local')) {
            $rules['g-recaptcha-response'] = ['required', function ($attribute, $value, $fail) {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $value,
                ]);
                if (! $response->json('success')) {
                    $fail('Please complete the CAPTCHA to prove you are a human.');
                }
            }];
        }

        $request->validate($rules);

        // THE FIX: Determine if they entered an email or phone number
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

        // Pass BOTH the user and the exact identifier they typed in
        return view('resident.auth.forgot_password_confirm', compact('user'))->with('identifier', $loginInput);
    }

    public function sendResetLink(Request $request)
    {
        // THE FIX: Removed the "|email|exists:users,email" rule so phone numbers don't get rejected!
        $request->validate(['email' => 'required']); 
        
        $identifier = $request->input('email');
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
        $user = User::where($field, $identifier)->first();

        if (!$user) {
             return redirect()->route('resident.forgot_password')->withErrors(['login_id' => 'Invalid request.']);
        }

        // THE FIX: Generate a 5-digit number instead of letters so it's easy to text
        $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        // Store it in the tokens table (it's safe to store a phone number in the 'email' column temporarily)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $identifier],
            ['token' => $code, 'created_at' => now()]
        );

        if ($field === 'email') {
            // SEND EMAIL
            Mail::to($identifier)->send(new ResetPasswordCodeMail($code, $user->name));
        } else {
            // SEND SMS VIA TEXTBEE
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
        // THE FIX: Removed |email validation rule here too
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
        // THE FIX: Removed |email validation rule
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
            // SEND SUCCESS SMS
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

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'], 
            'password' => ['required', 'min:8', 'confirmed'],
            'birthdate' => ['required', 'date'],
            'gender' => ['required', 'string'],
        ]);

        $isEmail = filter_var($request->email, FILTER_VALIDATE_EMAIL);
        
        $actualEmail = null;
        $actualPhone = null;

        if ($isEmail) {
            $actualEmail = $request->email;
            if (User::where('email', $actualEmail)->exists()) {
                return back()->withErrors(['email' => 'This email address is already registered.'])->withInput();
            }
        } else {
            $actualPhone = preg_replace('/[^0-9]/', '', $request->email);
            
            if (strlen($actualPhone) < 10) {
                return back()->withErrors(['email' => 'Please enter a valid email address or mobile number.'])->withInput();
            }

            if (User::where('phone_number', $actualPhone)->exists()) {
                return back()->withErrors(['email' => 'This mobile number is already registered.'])->withInput();
            }
        }

        $userId = 'USR-' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        $user = User::create([
            'user_id' => $userId,
            'name' => $request->name,
            'email' => $actualEmail,             
            'phone_number' => $actualPhone,      
            'password' => Hash::make($request->password), 
            'birthdate' => $request->birthdate, 
            'gender' => $request->gender,       
            'role' => 'Resident',
            'status' => 'incomplete', 
        ]);

        Auth::login($user);

        if ($actualEmail) {
            // Email flow: Send verification email safely without hanging request
            try {
                event(new Registered($user));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Registration email verification failed to send: ' . $e->getMessage());
            }
            return redirect()->route('verification.notice');
        } else {
            // Phone flow: Generate a 5-digit number
            $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // Store it temporarily (reusing the password reset table for convenience)
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $actualPhone],
                ['token' => $code, 'created_at' => now()]
            );

            // Trigger the real SMS through your physical Tecno phone via Textbee
            $message = "E-Barangay: {$code} is your verification code. Please do not share this with anyone.";

            // FORCE INTERNATIONAL FORMAT: Change 09... to +639...
            $formattedPhone = preg_replace('/^09/', '+639', $actualPhone);

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
                    \Illuminate\Support\Facades\Log::error('Textbee API Error: ' . json_encode($response->json()));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Registration SMS network error: ' . $e->getMessage());
            }

            return redirect()->route('verify_phone.notice')->with('phone_number', $actualPhone);
        }
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

        // Mark the account as verified (Reusing the email verified timestamp so Laravel core security passes)
        $user = Auth::user();
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('register.complete');
    }

    // ==========================================
    // PIN SETUP LOGIC
    // ==========================================
    public function setupPin(Request $request)
    {
        $request->validate([
            'security_pin' => 'required|digits:6|confirmed', // Requires a 'security_pin_confirmation' field
        ]);

        $user = Auth::user();
        
        // Hash it for security, just like a password!
        $user->security_pin = bcrypt($request->security_pin);
        $user->save();

        return back()->with('success', 'Security PIN successfully established! You can now use it for quick logins.');
    }

    // ==========================================
    // SESSION LOCK LOGIC
    // ==========================================
    public function lockSession(Request $request)
    {
        // Securely flag the session as locked
        $request->session()->put('is_locked', true);
        return response()->json(['success' => true]);
    }

    public function unlockSession(Request $request)
    {
        $request->validate([
            'security_pin' => 'required|digits:6',
        ]);

        $user = Auth::user();

        // Check if the entered PIN matches the hashed PIN in the database
        if (Hash::check($request->security_pin, $user->security_pin)) {
            $request->session()->forget('is_locked');
            return back()->with('success', 'Session unlocked.');
        }

        // If wrong PIN, keep it locked and kick them back with an error
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
}