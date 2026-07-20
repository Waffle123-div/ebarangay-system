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
                // THE SMART ROUTING SYSTEM
                // ==========================================
                if (trim(strtolower($user->status)) === 'active') {
                    Auth::login($user);
                    return redirect()->route('resident.dashboard');

                } elseif (trim(strtolower($user->status)) === 'pending_approval') {
                    return redirect()->route('resident.pending');

                } elseif (trim(strtolower($user->status)) === 'incomplete') {
                    Auth::login($user);
                    
                    // If they haven't verified their email yet, send them to the waiting room
                    if (!$user->hasVerifiedEmail()) {
                        return redirect()->route('verification.notice');
                    }
                    
                    return redirect()->route('register.complete');

                } else {
                    return redirect()->route('resident.login')->withErrors(['email' => 'Your account access has been revoked or denied.']);
                }

            } else {
                // ==========================================
                // NEW USER LOGIC
                // ==========================================
                // If they don't exist in the database, we seamlessly auto-register them
                // regardless of whether they clicked "Login" or "Sign up".

                $userId = 'USR-' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
                
                $newUser = User::create([
                    'user_id' => $userId,
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => null, 
                    'role' => 'Resident',
                    'status' => 'incomplete',
                    // 'email_verified_at' is omitted so they are FORCED to verify
                ]);

                // Trigger the Verification Email
                event(new Registered($newUser));

                // Log them in so the system knows who is waiting
                Auth::login($newUser);
                
                // Send them directly to Waiting Room 1
                return redirect()->route('verification.notice');
            }

        } catch (\Exception $e) {
            // Print the ACTUAL error to the screen if Google auth completely fails
            return redirect()->route('resident.login')->withErrors(['email' => 'System Error: ' . $e->getMessage()]);
        }
    }
}