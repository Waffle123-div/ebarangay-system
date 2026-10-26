<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use App\Models\ActivityLog;
use App\Models\User;
use App\Mail\RegistrationOtpMail;

class ResidentSettingsController extends Controller
{
    // ==========================================
    // BOUNCER HELPER
    // ==========================================
    private function bouncer()
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }
        return null;
    }

    // ==========================================
    // ACTIVITY LOG HELPER
    // ==========================================
    private function logActivity(string $action, string $description = ''): void
    {
        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => $action,
            'description'=> $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // ==========================================
    // INDEX — Show Settings Page
    // ==========================================
    public function index(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $user = Auth::user();

        // Get active sessions from the sessions table
        $sessions = collect();
        try {
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($session) {
                    $session->last_active_at = \Carbon\Carbon::createFromTimestamp($session->last_activity);
                    $session->is_current     = ($session->id === session()->getId());
                    // Parse a basic device/browser label from user_agent
                    $ua = strtolower($session->user_agent ?? '');
                    $session->device = str_contains($ua, 'mobile') ? 'Mobile' : 'Desktop';
                    if (str_contains($ua, 'chrome'))       $session->browser = 'Chrome';
                    elseif (str_contains($ua, 'firefox')) $session->browser = 'Firefox';
                    elseif (str_contains($ua, 'safari'))  $session->browser = 'Safari';
                    elseif (str_contains($ua, 'edge'))    $session->browser = 'Edge';
                    else                                  $session->browser = 'Unknown';
                    return $session;
                });
        } catch (\Exception $e) {
            // Sessions table may not yet exist in some environments
        }

        // Activity logs — latest 30
        $activityLogs = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        // Active tab (for re-opening after redirect)
        $activeTab = $request->input('tab', 'profile');

        return view('resident.settings', compact('user', 'sessions', 'activityLogs', 'activeTab'));
    }

    // ==========================================
    // OTP — Send Security Verification OTP
    // ==========================================
    public function sendOtp(Request $request)
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized account status.',
            ], 403);
        }

        $user = Auth::user();
        $channel = $request->input('channel', 'email');

        // Generate 5-digit random OTP
        $otpCode = (string) rand(10000, 99999);

        // Store OTP in session (valid for 10 minutes)
        session([
            'settings_otp_code'       => $otpCode,
            'settings_otp_expires_at' => now()->addMinutes(10),
            'settings_otp_channel'    => $channel,
        ]);

        if ($channel === 'email') {
            if (!$user->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'No email address registered on your account.',
                ], 400);
            }

            // Log code to storage/logs/laravel.log for easy testing on localhost
            Log::info("Settings Security OTP Email for {$user->email}: {$otpCode}");

            // Send email
            try {
                $name = $user->first_name ?: ($user->name ?: 'Resident');
                Mail::to($user->email)->send(new RegistrationOtpMail($otpCode, $name));
            } catch (\Throwable $e) {
                Log::error('Settings OTP Email Delivery Failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email address.',
            ]);
        } elseif ($channel === 'phone') {
            if (!$user->phone_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'No mobile number registered on your account.',
                ], 400);
            }

            // Log code to storage/logs/laravel.log for easy testing on localhost
            Log::info("Settings Security OTP SMS for {$user->phone_number}: {$otpCode}");

            $message = "E-Barangay: {$otpCode} is your security verification code. Valid for 10 minutes.";
            $formattedPhone = preg_replace('/^09/', '+639', $user->phone_number);

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
                    Log::error('Textbee Settings OTP SMS Error: ' . json_encode($response->json()));
                }
            } catch (\Throwable $e) {
                Log::error('Settings OTP SMS Network Error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your mobile phone number via SMS.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid verification channel selected.',
        ], 400);
    }

    // ==========================================
    // OTP — Verify Security OTP Code
    // ==========================================
    public function verifyOtp(Request $request)
    {
        $inputCode   = trim($request->input('code'));
        $sessionCode = session('settings_otp_code');
        $expiresAt   = session('settings_otp_expires_at');

        if (!$sessionCode || !$expiresAt || now()->greaterThan($expiresAt)) {
            return response()->json([
                'success' => false,
                'message' => 'The verification code has expired. Please request a new one.',
            ], 400);
        }

        if ((string) $inputCode !== (string) $sessionCode) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect verification code. Please check and try again.',
            ], 400);
        }

        // Authorize action in session
        session(['settings_otp_verified' => true]);
        session()->forget(['settings_otp_code', 'settings_otp_expires_at']);

        return response()->json([
            'success' => true,
            'message' => 'Security verification successful.',
        ]);
    }

    // ==========================================
    // PROFILE UPDATE
    // ==========================================
    public function updateProfile(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $request->validate([
            'first_name'       => 'required|string|max:100',
            'middle_name'      => 'nullable|string|max:100',
            'last_name'        => 'required|string|max:100',
            'suffix'           => 'nullable|string|max:20',
            'birthdate'        => 'nullable|date',
            'age'              => 'nullable|integer|min:0|max:150',
            'birthplace'       => 'nullable|string|max:255',
            'gender'           => 'nullable|string|max:50',
            'civil_status'     => 'nullable|string|max:50',
            'nationality'      => 'nullable|string|max:100',
            'occupation'       => 'nullable|string|max:150',
            'phone_number'     => 'nullable|string|max:20',
            'house_number'     => 'nullable|string|max:50',
            'street'           => 'nullable|string|max:150',
            'barangay_address' => 'nullable|string|max:150',
            'municipality'     => 'nullable|string|max:150',
            'province'         => 'nullable|string|max:150',
            'zip_code'         => 'nullable|string|max:10',
            'profile_picture'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::user();

        $firstName    = strtoupper(trim($request->first_name));
        $middleName   = $request->filled('middle_name') ? strtoupper(trim($request->middle_name)) : null;
        $lastName     = strtoupper(trim($request->last_name));
        $suffix       = $request->filled('suffix') ? strtoupper(trim($request->suffix)) : null;
        $birthplace   = $request->filled('birthplace') ? strtoupper(trim($request->birthplace)) : null;
        $nationality  = $request->filled('nationality') ? strtoupper(trim($request->nationality)) : null;
        $occupation   = $request->filled('occupation') ? strtoupper(trim($request->occupation)) : null;
        $houseNumber  = $request->filled('house_number') ? strtoupper(trim($request->house_number)) : null;
        $street       = $request->filled('street') ? strtoupper(trim($request->street)) : null;
        $barangay     = $request->filled('barangay_address') ? strtoupper(trim($request->barangay_address)) : null;
        $municipality = $request->filled('municipality') ? strtoupper(trim($request->municipality)) : null;
        $province     = $request->filled('province') ? strtoupper(trim($request->province)) : null;

        $data = [
            'first_name'       => $firstName,
            'middle_name'      => $middleName,
            'last_name'        => $lastName,
            'suffix'           => $suffix,
            'birthdate'        => $request->birthdate,
            'age'              => $request->age,
            'birthplace'       => $birthplace,
            'gender'           => $request->gender,
            'civil_status'     => $request->civil_status,
            'nationality'      => $nationality,
            'occupation'       => $occupation,
            'phone_number'     => $request->phone_number,
            'house_number'     => $houseNumber,
            'street'           => $street,
            'barangay_address' => $barangay,
            'municipality'     => $municipality,
            'province'         => $province,
            'zip_code'         => $request->zip_code,
        ];

        // Sync the full `name` field for backward compatibility
        $data['name'] = trim(implode(' ', array_filter([$firstName, $middleName, $lastName, $suffix])));

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old picture if it exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);
        $this->logActivity('Profile Updated', 'Personal information and address were updated.');

        return redirect()->route('resident.settings', ['tab' => 'profile'])
            ->with('success', 'Profile updated successfully!');
    }

    // ==========================================
    // ACCOUNT UPDATE (Username + Email)
    // ==========================================
    public function updateAccount(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $user = Auth::user();

        $request->validate([
            'username' => 'nullable|string|max:50|alpha_dash|unique:users,username,' . $user->id,
            'email'    => 'nullable|email|max:255|unique:users,email,' . $user->id,
        ]);

        $changes = [];
        $oldEmail = $user->email;

        if ($request->filled('username')) {
            $user->username = $request->username;
            $changes[] = 'username';
        }

        if ($request->filled('email') && $request->email !== $oldEmail) {
            $user->email = $request->email;
            $user->email_verified_at = null; // Require re-verification
            $changes[] = 'email';
        }

        $user->save();

        if (!empty($changes)) {
            $this->logActivity('Account Updated', 'Changed: ' . implode(', ', $changes) . '.');
        }

        $message = 'Account settings saved.';
        if (in_array('email', $changes)) {
            $message = 'Email updated. Please verify your new email address.';
            return redirect()->route('verification.notice')->with('info', $message);
        }

        return redirect()->route('resident.settings', ['tab' => 'account'])
            ->with('success', $message);
    }

    // ==========================================
    // PASSWORD CHANGE
    // ==========================================
    public function updatePassword(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Your current password is incorrect.'])
                ->with('active_tab', 'account');
        }

        $user->update(['password' => Hash::make($request->password)]);
        
        // Clear OTP authorization flag
        session()->forget('settings_otp_verified');

        $this->logActivity('Password Changed', 'Account password was successfully changed.');

        return redirect()->route('resident.settings', ['tab' => 'account'])
            ->with('success', 'Password changed successfully!');
    }

    // ==========================================
    // SECURITY — 2FA Toggle
    // ==========================================
    public function updateSecurity(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $user   = Auth::user();
        $enable = (bool) $request->input('two_factor_enabled', false);

        $user->update(['two_factor_enabled' => $enable]);
        $this->logActivity(
            'Security Settings Updated',
            'Two-Factor Authentication was ' . ($enable ? 'enabled' : 'disabled') . '.'
        );

        return redirect()->route('resident.settings', ['tab' => 'security'])
            ->with('success', '2FA ' . ($enable ? 'enabled' : 'disabled') . ' successfully.');
    }

    // ==========================================
    // MY IDs — Upload / Replace Valid ID
    // ==========================================
    public function uploadId(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $request->validate([
            'id_type'       => 'required|string|in:National ID,Driver\'s License,Passport,PhilHealth,UMID,Voter\'s ID',
            'id_photo'      => 'required|image|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'id_expiry_date'=> 'nullable|date|after:today',
        ]);

        $user = Auth::user();

        // Remove old ID photo if present
        if ($user->id_photo && Storage::disk('public')->exists($user->id_photo)) {
            Storage::disk('public')->delete($user->id_photo);
        }

        $path = $request->file('id_photo')->store('valid_ids', 'public');

        $user->update([
            'id_type'       => $request->id_type,
            'id_photo'      => $path,
            'id_expiry_date'=> $request->id_expiry_date,
            'id_uploaded_at'=> now(),
            'id_verified_at'=> null, // Reset verification when a new ID is uploaded
        ]);

        // Clear OTP authorization flag
        session()->forget('settings_otp_verified');

        $this->logActivity('ID Uploaded', "Uploaded {$request->id_type} as valid ID.");

        return redirect()->route('resident.settings', ['tab' => 'ids'])
            ->with('success', 'Valid ID uploaded successfully. Please wait for admin verification.');
    }

    // ==========================================
    // SESSION MANAGEMENT — Logout Other Devices
    // ==========================================
    public function logoutOtherDevices(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $request->validate(['current_password_sessions' => 'required']);

        if (!Hash::check($request->current_password_sessions, Auth::user()->password)) {
            return back()
                ->withErrors(['current_password_sessions' => 'Password is incorrect.'])
                ->with('active_tab', 'security');
        }

        // Delete all sessions except the current one
        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', session()->getId())
            ->delete();

        $this->logActivity('Logout Other Devices', 'All other active sessions were terminated.');

        return redirect()->route('resident.settings', ['tab' => 'security'])
            ->with('success', 'All other devices have been logged out.');
    }

    // ==========================================
    // SESSION MANAGEMENT — Logout All Devices
    // ==========================================
    public function logoutAllDevices(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $request->validate(['current_password_all' => 'required']);

        if (!Hash::check($request->current_password_all, Auth::user()->password)) {
            return back()
                ->withErrors(['current_password_all' => 'Password is incorrect.'])
                ->with('active_tab', 'security');
        }

        $this->logActivity('Logout All Devices', 'All sessions including current device were terminated.');

        // Flush and regenerate
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('resident.login')
            ->with('success', 'You have been logged out from all devices.');
    }

    // ==========================================
    // ACCOUNT MANAGEMENT — Deactivate
    // ==========================================
    public function deactivateAccount(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $request->validate(['confirm_deactivate' => 'required|in:DEACTIVATE']);

        $user = Auth::user();
        $this->logActivity('Account Deactivated', 'Resident requested account deactivation.');

        $user->update(['status' => 'deactivated']);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('resident.login')
            ->with('info', 'Your account has been deactivated. Contact the barangay office to reactivate.');
    }

    // ==========================================
    // ACCOUNT MANAGEMENT — Delete Account
    // ==========================================
    public function deleteAccount(Request $request)
    {
        if ($bounce = $this->bouncer()) return $bounce;

        $request->validate([
            'confirm_delete'   => 'required|in:DELETE',
            'password_delete'  => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_delete, $user->password)) {
            return back()
                ->withErrors(['password_delete' => 'Password is incorrect.'])
                ->with('active_tab', 'manage');
        }

        // Clean up uploaded files
        if ($user->profile_picture) Storage::disk('public')->delete($user->profile_picture);
        if ($user->id_photo)        Storage::disk('public')->delete($user->id_photo);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('resident.login')
            ->with('info', 'Your account has been permanently deleted.');
    }
}