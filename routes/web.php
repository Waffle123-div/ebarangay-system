<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ResidentDashboardController;
use App\Http\Controllers\ResidentSettingsController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\ResidentAuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\ResidentCompletionController;
use Illuminate\Http\Request;
use App\Http\Middleware\AdminMiddleware;

// We added these 3 imports for the custom verification auto-login
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ChatbotController;

// ==========================================
// THE TRAFFIC COP (Root Route)
// ==========================================
Route::get('/', function () {
    // 1. If they are already logged in, send them to their correct dashboard!
    if (Auth::check()) {
        if (trim(strtolower(Auth::user()->role)) === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('resident.dashboard');
    }
    
    // 2. If they are NOT logged in, show the brand new Guest Landing Page!
    return view('welcome');
});

// Welcome page accessible while logged in (for Home sidebar link)
Route::get('/home', function () {
    return view('welcome');
})->name('welcome');

// ==========================================
// GOOGLE AUTHENTICATION ROUTES
// ==========================================
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ==========================================
// ADMIN AUTHENTICATION ROUTES (Unprotected)
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::match(['get', 'post'], '/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// ==========================================
// ADMIN PROTECTED ROUTES (Requires Login)
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', AdminMiddleware::class])->group(function () {
    
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/requests', [AdminDashboardController::class, 'requests'])->name('requests');
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    
    // Profile Routes
    Route::get('/profile', [AdminDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [AdminDashboardController::class, 'updateProfile'])->name('profile.update');

    // Document Generation Route
    Route::get('/requests/generate/{id}', [AdminDashboardController::class, 'generateWord'])->name('requests.generate');
    Route::get('/requests/{id}', [AdminDashboardController::class, 'showRequest'])->name('requests.show');
    
    // Action Button Routes (legacy placeholder routes removed — use .accept and .reject)
    Route::delete('/requests/bulk-delete', [AdminDashboardController::class, 'bulkDestroyRequests'])->name('requests.bulk-delete');
    Route::delete('/requests/{id}', [AdminDashboardController::class, 'destroy'])->name('requests.destroy');

    // --- NEW PIPELINE ROUTES (Accept/Reject Gateway) ---
    Route::patch('/requests/{id}/accept', [AdminDashboardController::class, 'acceptRequest'])->name('requests.accept');
    Route::patch('/requests/{id}/reject', [AdminDashboardController::class, 'rejectRequest'])->name('requests.reject');
    Route::patch('/requests/{id}/ready', [AdminDashboardController::class, 'markReady'])->name('requests.ready');
    Route::patch('/requests/{id}/complete', [AdminDashboardController::class, 'markCompleted'])->name('requests.complete');
    Route::patch('/requests/{id}/update', [AdminDashboardController::class, 'updateRequest'])->name('requests.update');
    Route::patch('/requests/{id}/fee', [AdminDashboardController::class, 'updateFee'])->name('requests.updateFee');

    // Account Approval Routes
    Route::get('/account-approvals', [AdminDashboardController::class, 'accountApprovals'])->name('approvals');
    Route::patch('/account-approvals/{id}/approve', [AdminDashboardController::class, 'approveAccount'])->name('approvals.approve');
    Route::patch('/account-approvals/{id}/reject', [AdminDashboardController::class, 'rejectAccount'])->name('approvals.reject');

    // User Management Action Routes
    Route::delete('/users/bulk-delete', [AdminDashboardController::class, 'bulkDestroyUsers'])->name('users.bulk-delete');
    Route::get('/users/{id}', [AdminDashboardController::class, 'viewUser'])->name('users.view');
    Route::patch('/users/{id}/update', [AdminDashboardController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{id}/toggle-status', [AdminDashboardController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::delete('/users/{id}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');

    // Activity Logs
    Route::delete('/activity-logs/bulk-delete', [AdminDashboardController::class, 'bulkDestroyActivityLogs'])->name('activity-logs.bulk-delete');
    Route::delete('/activity-logs/{id}', [AdminDashboardController::class, 'destroyActivityLog'])->name('activity-logs.destroy');
    Route::get('/activity-logs', [AdminDashboardController::class, 'activityLogs'])->name('activity-logs');

    // Community Concerns
    Route::get('/concerns', [AdminDashboardController::class, 'concerns'])->name('concerns');
    Route::patch('/concerns/{id}', [AdminDashboardController::class, 'updateConcern'])->name('concerns.update');

    // Document Types Catalog / Document Management (CRUD)
    Route::get('/document-types', [AdminDashboardController::class, 'documentTypes'])->name('document-types.index');
    Route::get('/document-management', [AdminDashboardController::class, 'documentTypes'])->name('documents.index');
    Route::get('/documents', [AdminDashboardController::class, 'documentTypes']);
    Route::post('/document-types', [AdminDashboardController::class, 'storeDocumentType'])->name('document-types.store');
    Route::patch('/document-types/{id}', [AdminDashboardController::class, 'updateDocumentType'])->name('document-types.update');
    Route::patch('/document-types/{id}/toggle-status', [AdminDashboardController::class, 'toggleDocumentTypeStatus'])->name('document-types.toggle-status');
    Route::delete('/document-types/bulk-delete', [AdminDashboardController::class, 'bulkDestroyDocumentTypes'])->name('document-types.bulk-delete');
    Route::delete('/document-types/{id}', [AdminDashboardController::class, 'destroyDocumentType'])->name('document-types.destroy');
    Route::get('/document-types/{id}/template', [AdminDashboardController::class, 'downloadDocumentTypeTemplate'])->name('document-types.download-template');

    // Walk-in Request Route
    Route::post('/requests/walk-in', [AdminDashboardController::class, 'storeWalkInRequest'])->name('requests.walk-in');

    // Admin Settings
    Route::get('/settings', [AdminDashboardController::class, 'adminSettings'])->name('settings');
    Route::post('/settings/profile', [AdminDashboardController::class, 'updateAdminProfile'])->name('settings.profile');
    Route::post('/settings/password', [AdminDashboardController::class, 'updateAdminPassword'])->name('settings.password');
    Route::post('/settings/barangay', [AdminDashboardController::class, 'updateBarangaySettings'])->name('settings.barangay');
    Route::get('/settings/backup-db', [AdminDashboardController::class, 'exportDatabaseBackup'])->name('settings.backup-db');

    // Admin Notifications (AJAX)
    Route::get('/notifications', [AdminDashboardController::class, 'getAdminNotifications'])->name('notifications.get');
    Route::post('/notifications/mark-read', [AdminDashboardController::class, 'markAdminNotificationsRead'])->name('notifications.read');
});

// ==========================================
// VERIFICATION ROUTES (Email & Phone)
// ==========================================

// 1. The Waiting Rooms (Needs auth because they are currently sitting on the screen)
Route::middleware('auth')->group(function () {
    
    // Email Verification Routes
    Route::get('/email/verify', function () {
        return view('resident.verify_email');
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('success', 'A new verification link has been sent to your email address.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Resend verification email failed: ' . $e->getMessage());
            return back()->with('success', 'Verification email request processed. Please check your inbox.');
        }
    })->middleware('throttle:6,1')->name('verification.send');

    // NEW: Phone Verification Routes
    Route::get('/phone/verify', [ResidentAuthController::class, 'showVerifyPhoneForm'])->name('verify_phone.notice');
    Route::post('/phone/verify', [ResidentAuthController::class, 'verifyPhoneCode'])->name('verify_phone.submit');
});

// 2. The actual link they click in their Gmail (Moved OUTSIDE auth middleware so we can auto-login!)
Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {
    
    $user = User::findOrFail($id);

    // Security check: Verify the hash matches their email
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid or expired verification link.');
    }

    // Mark the email as verified in the database
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    // Automatically log the user back in so they never see the login screen!
    Auth::login($user);

    // Now correctly route them based on their status
    if ($user->status === 'incomplete') {
        // Manual registration users already have ID uploaded from the 4-step wizard
        if ($user->proof_of_residency) {
            $user->update(['status' => 'pending_approval']);

            // Notify admins about the new pending registration
            $admins = \App\Models\User::where('role', 'Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\NewUserRegistrationNotification($user));
            }

            Auth::logout();
            $notifyMethod = empty($user->email) ? 'phone' : 'email';
            return redirect(route('resident.pending') . '?notify=' . $notifyMethod);
        }

        return redirect()->route('register.complete');
    }

    return redirect()->route('resident.dashboard'); 
    
})->middleware(['signed'])->name('verification.verify');


// ==========================================
// RESIDENT AUTHENTICATION ROUTES
// ==========================================
Route::prefix('resident')->name('resident.')->group(function () {
    
    // Guest routes (Only for people NOT logged in)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [ResidentAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [ResidentAuthController::class, 'login'])->name('login.submit');
        Route::get('/forgot-password', [ResidentAuthController::class, 'showForgotPasswordForm'])->name('forgot_password');
        Route::post('/forgot-password/search', [ResidentAuthController::class, 'searchAccount'])->name('forgot_password.search');
        Route::post('/forgot-password/send', [ResidentAuthController::class, 'sendResetLink'])->name('forgot_password.send');
        
        // Removed the "resident." prefix from these routes
        Route::get('/forgot-password/verify', [ResidentAuthController::class, 'showVerifyCodeForm'])->name('forgot_password.verify');
        Route::post('/forgot-password/verify', [ResidentAuthController::class, 'verifyCode'])->name('forgot_password.verify.submit');
        Route::get('/forgot-password/reset', [ResidentAuthController::class, 'showResetPasswordForm'])->name('forgot_password.reset');
        Route::post('/forgot-password/reset', [ResidentAuthController::class, 'resetPassword'])->name('forgot_password.reset.submit');
        Route::get('/forgot-password/success', [ResidentAuthController::class, 'showResetSuccess'])->name('forgot_password.success');
    });

    // Registration routes (Accessible so Sign Up button always opens registration)
    Route::get('/register', [ResidentAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [ResidentAuthController::class, 'register'])->name('register.submit');
    Route::post('/register/send-otp', [ResidentAuthController::class, 'sendRegistrationOtp'])->name('register.send_otp');
    Route::post('/register/verify-otp', [ResidentAuthController::class, 'verifyRegistrationOtp'])->name('register.verify_otp');

    // Logout route (Support GET and POST to prevent 419 expired page errors)
    Route::match(['get', 'post'], '/logout', [ResidentAuthController::class, 'logout'])->name('logout');

});

// ==========================================
// RESIDENT COMPLETION ROUTES (Google & Phone Sign-ups)
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/complete-registration', [ResidentCompletionController::class, 'showForm'])->name('register.complete');
    Route::post('/complete-registration', [ResidentCompletionController::class, 'submitForm'])->name('register.complete.submit');
});

// The "Waiting Room" after submitting the 3-step form
// MOVED OUTSIDE the auth middleware so logged-out users can wait here!
Route::get('/resident/pending-approval', function () {
    return view('resident.pending_approval');
})->name('resident.pending');

// ==========================================
// PIN SETUP ROUTE (Must NOT have the 'require.pin' middleware)
// ==========================================
Route::post('/resident/setup-pin', [ResidentAuthController::class, 'setupPin'])
    ->name('resident.pin.setup')
    ->middleware(['auth', 'verified']);

// ==========================================
// RESIDENT PANEL ROUTES (Protected, Verified, & PIN Required)
// ==========================================
// NEW: Added 'require.pin' to the middleware array!
Route::prefix('resident')->name('resident.')->middleware(['auth', 'verified', 'require.pin'])->group(function () {
    Route::get('/', [ResidentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/request-document', [ResidentDashboardController::class, 'createRequest'])->name('request.create');
    Route::get('/track-request', [ResidentDashboardController::class, 'trackRequest'])->name('request.track');

    // Community Concerns
    Route::get('/community-concerns', [ResidentDashboardController::class, 'communityConcerns'])->name('concerns');
    Route::post('/community-concerns', [ResidentDashboardController::class, 'storeConcern'])->name('concern.store');
    
    // Form Submission Route
    Route::post('/request-document', [ResidentDashboardController::class, 'store'])->name('request.store');

    // THE NEW AJAX POLLING ROUTE
    Route::get('/requests/check-updates', [ResidentDashboardController::class, 'checkUpdates'])->name('requests.check');

    // THE SESSION LOCK ROUTES
    Route::post('/session/lock', [ResidentAuthController::class, 'lockSession'])->name('session.lock');
    Route::post('/session/unlock', [ResidentAuthController::class, 'unlockSession'])->name('session.unlock');

    // When the resident clicks the bell, the red badge needs to disappear.
    Route::post('/notifications/mark-read', [ResidentDashboardController::class, 'markNotificationsRead'])->name('notifications.read');

    // ==========================================
    // RESIDENT ACTIVITY LOGS & RECEIPTS
    // ==========================================
    Route::get('/activity-logs', [ResidentDashboardController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/receipt/{id}', [ResidentDashboardController::class, 'viewReceipt'])->name('receipt.view');
    Route::get('/receipt/{id}/download', [ResidentDashboardController::class, 'downloadReceipt'])->name('receipt.download');

    // ==========================================
    // RESIDENT SETTINGS ROUTES
    // ==========================================
    Route::get('/settings', [ResidentSettingsController::class, 'index'])->name('settings');
    Route::post('/settings/profile', [ResidentSettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/account', [ResidentSettingsController::class, 'updateAccount'])->name('settings.account');
    Route::post('/settings/password', [ResidentSettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/security', [ResidentSettingsController::class, 'updateSecurity'])->name('settings.security');
    Route::post('/settings/id', [ResidentSettingsController::class, 'uploadId'])->name('settings.id');
    Route::post('/settings/logout-others', [ResidentSettingsController::class, 'logoutOtherDevices'])->name('settings.logout-others');
    Route::post('/settings/logout-all', [ResidentSettingsController::class, 'logoutAllDevices'])->name('settings.logout-all');
    Route::post('/settings/deactivate', [ResidentSettingsController::class, 'deactivateAccount'])->name('settings.deactivate');
    Route::delete('/settings/delete', [ResidentSettingsController::class, 'deleteAccount'])->name('settings.delete');

});

Route::post('/chatbot/message', [ChatbotController::class, 'handleChat'])->name('chatbot.message');