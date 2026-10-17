<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request; 
use Illuminate\Auth\Events\Registered;

class GoogleController extends Controller
{
    // 1. Redirect to Google's OAuth page
    public function redirectToGoogle(Request $request)
    {
        $intended = $request->query('intended');
        if (is_string($intended)
            && (str_starts_with($intended, '/resident/request-document') || $intended === '/home')
            && !str_contains($intended, '://')
            && !str_contains($intended, '..')) {
            $request->session()->put('url.intended', $intended);
        }

        return Socialite::driver('google')->redirect();
    }

    // 2. Handle the return from Google
    public function handleGoogleCallback()
    {
        try {
            // Stateless() prevents session mismatch crashes on cold starts
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Check if a user with this Google ID or Email already exists
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // If they previously registered manually but are now using Google, link their Google ID
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }

                // ==========================================
                // THE SMART ROUTING SYSTEM (existing users)
                // ==========================================
                if (trim(strtolower($user->status)) === 'active') {
                    Auth::login($user);
                    return redirect()->intended(route('resident.dashboard'));

                } elseif (trim(strtolower($user->status)) === 'pending_approval') {
                    return redirect()->route('resident.pending');

                } elseif (trim(strtolower($user->status)) === 'incomplete') {
                    // ==========================================
                    // INCOMPLETE GOOGLE USER: Route them through
                    // the full registration wizard (new flow).
                    // Delete the old minimal stub so the wizard
                    // can create a complete user record.
                    // ==========================================
                    $user->delete();

                    session([
                        'google_reg_email'    => $googleUser->email,
                        'google_reg_name'     => $googleUser->name,
                        'google_reg_id'       => $googleUser->id,
                        'reg_verified_target' => $googleUser->email,
                        'reg_verified_at'     => now(),
                    ]);

                    return redirect()->route('resident.register')->with('via_google', true);

                } else {
                    return redirect()->route('resident.login')
                        ->withErrors(['email' => 'Your account access has been revoked or denied.']);
                }

            } else {
                // ==========================================
                // NEW USER: Store Google data in session
                // and redirect to the full registration wizard.
                // Email is pre-filled and OTP is bypassed
                // since Google already verified the email.
                // ==========================================
                session([
                    'google_reg_email'    => $googleUser->email,
                    'google_reg_name'     => $googleUser->name,
                    'google_reg_id'       => $googleUser->id,
                    // Mark the email as already verified so the OTP step is skipped
                    'reg_verified_target' => $googleUser->email,
                    'reg_verified_at'     => now(),
                ]);

                return redirect()->route('resident.register')->with('via_google', true);
            }

        } catch (\Exception $e) {
            return redirect()->route('resident.login')
                ->withErrors(['email' => 'System Error: ' . $e->getMessage()]);
        }
    }
}