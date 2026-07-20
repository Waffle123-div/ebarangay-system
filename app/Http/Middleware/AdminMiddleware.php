<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Added trim() to ignore accidental spaces in the database, 
        // and strtolower() to ignore capitalization
        if (auth()->check() && trim(strtolower(auth()->user()->role)) !== 'admin') {
            return redirect()->route('resident.dashboard')->withErrors(['error' => 'Access Denied. Admins only.']);
        }

        return $next($request);
    }
}