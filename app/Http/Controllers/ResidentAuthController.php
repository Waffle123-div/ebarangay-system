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
            'email' => ['required', 'email'],
            'password' => ['required'],
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

        $credentials = $request->validate($rules);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (trim(strtolower($user->role)) === 'admin') {
                return redirect()->route('admin.dashboard'); 
            } 
            return $this->smartRedirect($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // ==========================================
    // FORGOT PASSWORD
    // ==========================================
    
    public function showForgotPasswordForm()
    {
        return view('resident.auth.forgot_password');
    }

    public function searchAccount(Request $request)
    {
        $rules = [
            'email' => ['required', 'email'],
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find an account matching that email address. Please try again.'])->withInput();
        }

        if (trim(strtolower($user->status)) !== 'active') {
            return back()->withErrors(['email' => 'Your account has not been approved by the admin yet. Password recovery is only available for fully active accounts.'])->withInput();
        }

        return view('resident.auth.forgot_password_confirm', compact('user'));
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::where('email', $request->email)->first();

        $code = strtoupper(Str::random(5));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $code, 'created_at' => now()]
        );

        Mail::to($request->email)->send(new ResetPasswordCodeMail($code, $user->name));

        return redirect()->route('resident.forgot_password.verify')->with('reset_email', $request->email);
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
            'email' => 'required|email',
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

        $user = User::where('email', session('verified_email'))->first();
        return view('resident.auth.reset_password', ['user' => $user])->with('verified_email', session('verified_email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        Mail::to($request->email)->send(new PasswordChangedMail($user->name));

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
            // Email flow
            event(new Registered($user));
            return redirect()->route('verification.notice');
        } else {
            // Phone flow: Generate a 5-digit number
            $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // Store it temporarily (reusing the password reset table for convenience)
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $actualPhone],
                ['token' => $code, 'created_at' => now()]
            );

            // THE FIX: Trigger the real SMS through Semaphore
            $message = "E-Barangay: {$code} is your verification code. Please do not share this with anyone.";

            Http::post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => env('SEMAPHORE_API_KEY'),
                'number' => $actualPhone,
                'message' => $message,
                'sendername' => 'SEMAPHORE' // Free accounts must use SEMAPHORE as the sender name
            ]);

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
}