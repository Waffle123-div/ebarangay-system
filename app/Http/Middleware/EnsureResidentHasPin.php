<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureResidentHasPin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If they are a Resident, active, and DO NOT have a PIN set yet...
        if ($user && $user->role === 'Resident' && $user->status === 'active' && is_null($user->security_pin)) {
            
            // Allow them to access the dashboard ONLY to see the PIN setup modal,
            // but block them from submitting any forms or navigating away!
            if (!$request->routeIs('resident.dashboard') && !$request->routeIs('resident.pin.setup')) {
                return redirect()->route('resident.dashboard')->with('warning', 'Please set up your security PIN first.');
            }
        }

        return $next($request);
    }
}