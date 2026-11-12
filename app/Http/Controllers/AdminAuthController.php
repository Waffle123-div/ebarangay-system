<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    // 1. Show the Login Page
    public function showLoginForm()
    {
        try {
            // If they are already logged in, check who they are before redirecting
            if (Auth::check()) {
                // THE FIX: Ignore capitalization
                if (trim(strtolower((string) Auth::user()->role)) === 'admin') {
                    return redirect()->route('admin.dashboard');
                }
                return redirect()->route('resident.dashboard');
            }
        } catch (\Throwable $e) {
            Log::warning('Admin login form auth check failed: ' . $e->getMessage());
        }

        return view('admin.auth.login');
    }

    // 2. Process the Login
    public function login(Request $request)
    {
        // Validate the form data
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            // Attempt to log in
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                // THE FIX: Ignore capitalization
                if (trim(strtolower((string) Auth::user()->role)) === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->route('resident.dashboard');
            }
        } catch (QueryException|\PDOException $e) {
            Log::error('Admin login database error: ' . $e->getMessage(), ['exception' => $e]);

            return back()->withErrors([
                'email' => 'Unable to sign in because the database is unavailable or not migrated yet.',
            ])->onlyInput('email');
        } catch (\Throwable $e) {
            Log::error('Admin login unexpected error: ' . $e->getMessage(), ['exception' => $e]);

            return back()->withErrors([
                'email' => 'Unable to complete admin sign in right now. Please check the server logs.',
            ])->onlyInput('email');
        }

        // Failed! Send back with an error
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // 3. Process Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}