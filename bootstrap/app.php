<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all reverse proxies (e.g. Render, Cloudflare, AWS ALB) for SSL/HTTPS termination
        $middleware->trustProxies(at: '*');

        // Your existing guest redirect logic
        $middleware->redirectGuestsTo(function (Illuminate\Http\Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            return route('resident.login');
        });

        // Exclude logout routes from CSRF to avoid 419 page expired when sessions time out
        $middleware->validateCsrfTokens(except: [
            'resident/logout',
            'admin/logout',
        ]);

        // NEW: Registering our custom PIN security middleware alias!
        $middleware->alias([
            'require.pin' => \App\Http\Middleware\EnsureResidentHasPin::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch. Please reload the page.'], 419);
            }
            return redirect()->back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation']))
                ->withErrors(['email' => 'Your session expired. Please try again.']);
        });
    })->create();