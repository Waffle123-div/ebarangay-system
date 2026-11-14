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

// Imports for custom verification auto-login
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ChatbotController;

// ==========================================
// LANDING PAGE (Root Route)
// ==========================================
Route::get('/', function (Request $request) {
    if (Auth::check()) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
    return view('welcome');
})->name('welcome');

// Welcome page alias (for Home sidebar link or /home)
Route::get('/home', function () {
    return view('welcome');
});

// CSRF Token refresh endpoint
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf_token');

// Stored media / branding streaming routes (cloud bucket & local storage compatible)
Route::get('/branding/{key}/{filename?}', function (string $key, ?string $filename = null, \App\Services\StoredFileService $storedFileService) {
    $path = \App\Models\BarangaySetting::get($key);

    return $storedFileService->serve($path, ['settings'], 'Branding image not found.');
})->whereIn('key', ['barangay_logo', 'municipal_logo', 'gcash_qr_code'])->name('branding.asset');

Route::get('/media/profile-avatar/{filename?}', function (?string $filename = null, \App\Services\StoredFileService $storedFileService) {
    return $storedFileService->serve(
        Auth::user()?->profile_picture,
        ['profile_pictures'],
        'Profile picture not found.'
    );
})->middleware('auth')->name('media.profile_avatar');

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
    Route::get('/profile/avatar/{filename?}', [AdminDashboardController::class, 'showAvatar'])->name('profile.avatar');
    Route::post('/profile/update', [AdminDashboardController::class, 'updateProfile'])->name('profile.update');

    // Secure Resident Selfie, ID Document, & Attachment Routes for Staff
    Route::get('/resident-selfie/{filename}', [AdminDashboardController::class, 'showSelfie'])->name('resident.selfie');
    Route::get('/resident-id-document/{user}/{filename?}', [AdminDashboardController::class, 'showIdDocument'])->name('resident.id_document');
    Route::get('/requests/{documentRequest}/attachment/{index}/{filename?}', [AdminDashboardController::class, 'showRequestAttachment'])
        ->whereNumber('index')
        ->name('requests.attachment');

    // Document Generation Route
    Route::get('/requests/generate/{id}', [AdminDashboardController::class, 'generateWord'])->name('requests.generate');
    Route::get('/requests/{id}', [AdminDashboardController::class, 'showRequest'])->name('requests.show');
    
    // Action Button Routes
    Route::delete('/requests/bulk-delete', [AdminDashboardController::class, 'bulkDestroyRequests'])->name('requests.bulk-delete');
    Route::delete('/requests/{id}', [AdminDashboardController::class, 'destroy'])->name('requests.destroy');

    // --- PIPELINE ROUTES (Accept/Reject Gateway) ---
    Route::patch('/requests/{id}/accept', [AdminDashboardController::class, 'acceptRequest'])->name('requests.accept');
    Route::patch('/requests/{id}/reject', [AdminDashboardController::class, 'rejectRequest'])->name('requests.reject');
    Route::patch('/requests/{id}/ready', [AdminDashboardController::class, 'markReady'])->name('requests.ready');
    Route::patch('/requests/{id}/complete', [AdminDashboardController::class, 'markCompleted'])->name('requests.complete');
    Route::patch('/requests/{id}/update', [AdminDashboardController::class, 'updateRequest'])->name('requests.update');
    Route::patch('/requests/{id}/fee', [AdminDashboardController::class, 'updateFee'])->name('requests.updateFee');

    // Account Approval Routes
    Route::get('/account-approvals', [AdminDashboardController::class, 'accountApprovals'])->name('approvals');
    Route::post('/account-approvals/bulk-approve', [AdminDashboardController::class, 'bulkApproveAccounts'])->name('approvals.bulk-approve');
    Route::post('/account-approvals/bulk-reject', [AdminDashboardController::class, 'bulkRejectAccounts'])->name('approvals.bulk-reject');
    Route::patch('/account-approvals/{id}/approve', [AdminDashboardController::class, 'approveAccount'])->name('approvals.approve');
    Route::patch('/account-approvals/{id}/reject', [AdminDashboardController::class, 'rejectAccount'])->name('approvals.reject');

    // User Management Action Routes
    Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
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
    Route::get('/concerns/{concern}/photo/{filename?}', [AdminDashboardController::class, 'showConcernPhoto'])->name('concerns.photo');
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

// 1. The Waiting Rooms
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

    // Phone Verification Routes
    Route::get('/phone/verify', [ResidentAuthController::class, 'showVerifyPhoneForm'])->name('verify_phone.notice');
    Route::post('/phone/verify', [ResidentAuthController::class, 'verifyPhoneCode'])->name('verify_phone.submit');
});

// 2. Verification link from email
Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {
    
    $user = User::findOrFail($id);

    // Security check: Verify hash matches email
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid or expired verification link.');
    }

    // Mark email as verified in database
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    // Automatically log user in
    Auth::login($user);

    // Route based on user status
    if ($user->status === 'incomplete') {
        if ($user->proof_of_residency) {
            $user->update(['status' => 'pending_approval']);

            // Notify admins
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
    
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [ResidentAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [ResidentAuthController::class, 'login'])->name('login.submit');
        Route::get('/forgot-password', [ResidentAuthController::class, 'showForgotPasswordForm'])->name('forgot_password');
        Route::post('/forgot-password/search', [ResidentAuthController::class, 'searchAccount'])->name('forgot_password.search');
        Route::post('/forgot-password/send', [ResidentAuthController::class, 'sendResetLink'])->name('forgot_password.send');
        
        Route::get('/forgot-password/verify', [ResidentAuthController::class, 'showVerifyCodeForm'])->name('forgot_password.verify');
        Route::post('/forgot-password/verify', [ResidentAuthController::class, 'verifyCode'])->name('forgot_password.verify.submit');
        Route::get('/forgot-password/reset', [ResidentAuthController::class, 'showResetPasswordForm'])->name('forgot_password.reset');
        Route::post('/forgot-password/reset', [ResidentAuthController::class, 'resetPassword'])->name('forgot_password.reset.submit');
        Route::get('/forgot-password/success', [ResidentAuthController::class, 'showResetSuccess'])->name('forgot_password.success');
    });

    // Registration routes
    Route::get('/register', [ResidentAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [ResidentAuthController::class, 'register'])->name('register.submit');
    Route::post('/register/send-otp', [ResidentAuthController::class, 'sendRegistrationOtp'])->name('register.send_otp');
    Route::post('/register/verify-otp', [ResidentAuthController::class, 'verifyRegistrationOtp'])->name('register.verify_otp');

    // Logout route
    Route::match(['get', 'post'], '/logout', [ResidentAuthController::class, 'logout'])->name('logout');

});

// ==========================================
// RESIDENT COMPLETION ROUTES
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/complete-registration', [ResidentCompletionController::class, 'showForm'])->name('register.complete');
    Route::post('/complete-registration', [ResidentCompletionController::class, 'submitForm'])->name('register.complete.submit');
});

// Pending approval screen
Route::get('/resident/pending-approval', function () {
    return view('resident.pending_approval');
})->name('resident.pending');

// ==========================================
// PIN SETUP ROUTE
// ==========================================
Route::post('/resident/setup-pin', [ResidentAuthController::class, 'setupPin'])
    ->name('resident.pin.setup')
    ->middleware(['auth', 'verified']);

// ==========================================
// RESIDENT PANEL ROUTES (Protected, Verified, & PIN Required)
// ==========================================
Route::prefix('resident')->name('resident.')->middleware(['auth', 'verified', 'require.pin'])->group(function () {
    Route::get('/', [ResidentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/request-document', [ResidentDashboardController::class, 'createRequest'])->name('request.create');
    Route::get('/track-request', [ResidentDashboardController::class, 'trackRequest'])->name('request.track');

    // Community Concerns
    Route::get('/community-concerns', [ResidentDashboardController::class, 'communityConcerns'])->name('concerns');
    Route::get('/community-concerns/{concern}/photo/{filename?}', [ResidentDashboardController::class, 'showConcernPhoto'])->name('concerns.photo');
    Route::post('/community-concerns', [ResidentDashboardController::class, 'storeConcern'])->name('concern.store');
    
    // Form Submission Route
    Route::post('/request-document', [ResidentDashboardController::class, 'store'])->name('request.store');

    // AJAX polling route
    Route::get('/requests/check-updates', [ResidentDashboardController::class, 'checkUpdates'])->name('requests.check');

    // Session lock routes
    Route::post('/session/lock', [ResidentAuthController::class, 'lockSession'])->name('session.lock');
    Route::post('/session/unlock', [ResidentAuthController::class, 'unlockSession'])->name('session.unlock');

    // Resident Notifications
    Route::get('/notifications', [ResidentDashboardController::class, 'getNotifications'])->name('notifications.get');
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
    Route::get('/settings/file/{type}/{filename?}', [ResidentSettingsController::class, 'showFile'])
        ->whereIn('type', ['profile_picture', 'id_photo'])
        ->name('settings.file');
    Route::post('/settings/send-otp', [ResidentSettingsController::class, 'sendOtp'])->name('settings.send_otp');
    Route::post('/settings/verify-otp', [ResidentSettingsController::class, 'verifyOtp'])->name('settings.verify_otp');
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

// Convenience aliases for receipt routes (allows both route('receipt.view') and route('resident.receipt.view'))
Route::get('/receipt/{id}', [ResidentDashboardController::class, 'viewReceipt'])->name('receipt.view')->middleware(['auth', 'verified', 'require.pin']);
Route::get('/receipt/{id}/download', [ResidentDashboardController::class, 'downloadReceipt'])->name('receipt.download')->middleware(['auth', 'verified', 'require.pin']);

Route::post('/chatbot/message', [ChatbotController::class, 'handleChat'])->name('chatbot.message');