<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // 1. Show the Login Page
    public function showLoginForm()
    {
        // If they are already logged in, check who they are before redirecting
        if (Auth::check()) {
            // THE FIX: Ignore capitalization
            if (strtolower(Auth::user()->role) === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('resident.dashboard');
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

        // Attempt to log in
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // THE FIX: Ignore capitalization
            if (strtolower(Auth::user()->role) === 'admin') {
                return redirect()->route('admin.dashboard'); 
            } 
            
            return redirect()->route('resident.dashboard'); 
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