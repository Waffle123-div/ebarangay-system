<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\CommunityConcern;
use App\Models\CommunityConcernRemark;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\ConcernStatusUpdatedNotification;
use PhpOffice\PhpWord\TemplateProcessor; 
use Illuminate\Support\Facades\Mail;    
use Illuminate\Support\Facades\Http;    
use App\Mail\AccountApproved;    
use App\Mail\DocumentReady;        
use Carbon\Carbon;
use App\Mail\AccountRejectedMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\BarangaySetting;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalRequests = DocumentRequest::count();
        $pendingReviews = DocumentRequest::where('status', 'Pending')->count();
        $approvedToday = DocumentRequest::where('status', 'Approved')->count();
        $activeUsers = User::where('status', 'Active')->orWhere('status', 'active')->count();
        $recentRequests = DocumentRequest::orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('totalRequests', 'pendingReviews', 'approvedToday', 'activeUsers', 'recentRequests'));
    }

    public function destroy($id)
    {
        $request = DocumentRequest::findOrFail($id);
        $trackingNumber = $request->tracking_number;
        $request->delete();

        ActivityLog::record('request_deleted', "Permanently deleted document request #{$trackingNumber}.");

        return back()->with('success', 'Request deleted permanently.');
    }

    public function bulkDestroyRequests(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:document_requests,id',
        ]);

        $ids = $request->input('ids', []);
        $requests = DocumentRequest::whereIn('id', $ids)->get();

        $deletedCount = 0;
        foreach ($requests as $docReq) {
            $docReq->delete();
            $deletedCount++;
        }

        ActivityLog::record('requests_bulk_deleted', "Permanently deleted {$deletedCount} document request(s).");

        return back()->with('success', "Permanently deleted {$deletedCount} document request(s).");
    }

    public function updateRequest(Request $httpRequest, $id)
    {
        $docRequest = DocumentRequest::findOrFail($id);

        $httpRequest->validate([
            'document_type'  => ['nullable', 'string', 'max:1000'],
            'purpose'        => ['nullable', 'string', 'max:2000'],
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:10'],
            'age'            => ['nullable', 'integer', 'min:0', 'max:150'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'resident_email' => ['nullable', 'email', 'max:255'],
            'fee'            => ['nullable', 'numeric', 'min:0'],
            'id_type'        => ['nullable', 'string', 'max:255'],
        ]);

        $fee = $httpRequest->filled('fee') ? $httpRequest->fee : null;

        $updateData = [
            'first_name'     => strtoupper(trim($httpRequest->first_name)),
            'last_name'      => strtoupper(trim($httpRequest->last_name)),
            'middle_initial' => $httpRequest->filled('middle_initial') ? strtoupper(trim($httpRequest->middle_initial)) : null,
            'age'            => $httpRequest->age,
            'contact_number' => $httpRequest->contact_number,
            'resident_email' => $httpRequest->resident_email,
            'fee'            => $fee,
            'id_type'        => $httpRequest->filled('id_type') ? $httpRequest->id_type : null,
        ];

        if ($docRequest->user && empty($docRequest->user->id_type) && $httpRequest->filled('id_type')) {
            $docRequest->user->update(['id_type' => $httpRequest->id_type]);
        }

        $docType = $httpRequest->input('document_type', $docRequest->document_type);
        $purpose = $httpRequest->filled('purpose') ? strtoupper(trim($httpRequest->purpose)) : $docRequest->purpose;

        if ($httpRequest->filled('documents_json')) {
            $decoded = json_decode($httpRequest->documents_json, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $first = reset($decoded);
                $docType = is_array($first) ? ($first['type'] ?? $docType) : $docType;
                $purpose = is_array($first) && !empty($first['purpose']) ? strtoupper(trim($first['purpose'])) : $purpose;
            }
        } elseif ($httpRequest->has('doc_types') && is_array($httpRequest->doc_types) && count($httpRequest->doc_types) > 0) {
            $docType = trim($httpRequest->doc_types[0] ?? $docType);
            if (!empty($httpRequest->doc_purposes[0])) {
                $purpose = strtoupper(trim($httpRequest->doc_purposes[0]));
            }
        }

        $updateData['document_type'] = $docType;
        $updateData['document_type_id'] = \App\Models\DocumentType::where('name', $docType)->value('id');
        $updateData['purpose'] = $purpose;
        $updateData['documents'] = [
            [
                'type'         => $docType,
                'purpose'      => $purpose,
                'fee'          => $fee,
                'is_generated' => (bool)$docRequest->is_generated,
            ]
        ];

        $docRequest->update($updateData);

        ActivityLog::record('request_updated', "Updated document request #{$docRequest->tracking_number} ({$docRequest->document_type}).");

        return back()->with('success', 'Request updated successfully.');
    }

    public function updateFee(Request $request, $id)
    {
        $docRequest = DocumentRequest::findOrFail($id);

        $request->validate([
            'fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $fee = $request->filled('fee') ? (float)$request->fee : null;
        $docRequest->fee = $fee;

        // Sync documents JSON array if present
        if (!empty($docRequest->documents) && is_array($docRequest->documents)) {
            $docs = $docRequest->documents;
            foreach ($docs as &$doc) {
                $doc['fee'] = $fee;
            }
            $docRequest->documents = $docs;
        }

        $docRequest->save();

        $feeText = $fee !== null ? '₱' . number_format($fee, 2) : 'To be settled';
        ActivityLog::record('request_fee_updated', "Updated fee for request #{$docRequest->tracking_number} to {$feeText}.");

        return back()->with('success', "Fee for request #{$docRequest->tracking_number} updated to {$feeText}.");
    }

    public function accountApprovals(Request $request)
    {
        $tabCounts = [
            'pending_approval' => User::where('status', 'pending_approval')->count(),
            'active' => User::where('status', 'active')->count(),
            'rejected' => User::where('status', 'rejected')->count(),
        ];

        $currentStatus = $request->status ?? 'pending_approval';
        $query = User::where('status', $currentStatus);

        if ($request->filled('id_type')) {
            $query->where('id_type', $request->id_type);
        }

        $pendingUsers = $query->latest()->get(); 

        return view('admin.account_approvals', compact('pendingUsers', 'tabCounts', 'currentStatus'));
    }

    public function approveAccount($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'active']);

        ActivityLog::record('account_approved', "Approved resident account for {$user->name} (ID: {$user->user_id}).");

        if ($user->email) {
            Mail::to($user->email)->send(new AccountApproved($user));
        }

        if ($user->phone_number) {
            $formattedPhone = preg_replace('/^09/', '+639', $user->phone_number);
            $message = "Hello {$user->name}, your E-Barangay account has been approved by the Admin! You can now log in to the website.";
            
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
                    \Illuminate\Support\Facades\Log::error('Approval SMS Textbee Error: ' . json_encode($response->json()));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Approval SMS network error: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Resident account approved successfully. A notification has been sent.');
    }

    public function rejectAccount($id) 
    {
        $user = User::findOrFail($id);
        $user->status = 'Rejected';
        $user->save();

        ActivityLog::record('account_rejected', "Rejected resident account for {$user->name} (ID: {$user->user_id}).");

        Mail::to($user->email)->send(new AccountRejectedMail($user->name));

        return redirect()->back()->with('success', 'Account Rejected');
    }

    public function users(Request $request)
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'Active')->orWhere('status', 'active')->count();
        $newRegistrations = User::whereMonth('created_at', Carbon::now()->month)
                                ->whereYear('created_at', Carbon::now()->year)
                                ->count();
        $adminUsers = User::where('role', 'Admin')->count();

        $tabCounts = [
            'All' => User::count(),
            'Resident' => User::where('role', 'Resident')->count(),
            'Admin' => User::where('role', 'Admin')->count(),
        ];

        $currentRole = $request->role ?? 'All';
        $query = User::orderBy('created_at', 'desc');

        if ($currentRole !== 'All') {
            $query->where('role', $currentRole);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $allUsers = $query->get();

        return view('admin.users.users_list', compact(
            'totalUsers', 
            'activeUsers', 
            'newRegistrations', 
            'adminUsers', 
            'allUsers',
            'currentRole',
            'tabCounts'
        ));
    }

    public function viewUser($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'required|in:Admin,Resident',
        ]);

        $oldRole = $user->role;
        $fullName = strtoupper(trim($request->name));
        $nameParts = explode(' ', $fullName);
        $user->update([
            'name'       => $fullName,
            'first_name' => $nameParts[0] ?? $user->first_name,
            'last_name'  => count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $user->last_name,
            'email'      => $request->email,
            'role'       => $request->role,
        ]);

        ActivityLog::record('user_updated', "Updated user {$user->name}: role changed from {$oldRole} to {$request->role}.");

        return back()->with('success', 'User "' . $user->name . '" has been updated successfully.');
    }

    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);

        if (in_array(strtolower($user->status), ['active'])) {
            $user->status = 'inactive';
            $message = 'User "' . $user->name . '" has been deactivated.';
            ActivityLog::record('user_deactivated', "Deactivated account for user {$user->name}.");
        } else {
            $user->status = 'active';
            $message = 'User "' . $user->name . '" has been reactivated.';
            ActivityLog::record('user_reactivated', "Reactivated account for user {$user->name}.");
        }

        $user->save();

        return back()->with('success', $message);
    }

    public function destroyUser($id)
    {
        if ((int)$id === (int)auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own admin account.']);
        }

        $user = User::findOrFail($id);
        $name = $user->name;

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();

        ActivityLog::record('user_deleted', "Permanently deleted user {$name} (ID: {$id}).");

        return back()->with('success', "User \"{$name}\" has been permanently deleted.");
    }

    public function bulkDestroyUsers(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:users,id',
        ]);

        $currentUserId = auth()->id();
        $ids = collect($request->input('ids', []))->reject(fn ($id) => (int)$id === (int)$currentUserId)->values();

        if ($ids->isEmpty()) {
            return back()->withErrors(['error' => 'No valid users could be deleted (cannot delete yourself).']);
        }

        $users = User::whereIn('id', $ids)->get();
        $deletedCount = 0;

        foreach ($users as $u) {
            if ($u->profile_picture && Storage::disk('public')->exists($u->profile_picture)) {
                Storage::disk('public')->delete($u->profile_picture);
            }
            $u->delete();
            $deletedCount++;
        }

        ActivityLog::record('users_bulk_deleted', "Permanently deleted {$deletedCount} user(s).");

        return back()->with('success', "Permanently deleted {$deletedCount} user(s).");
    }

    public function requests(Request $request)
    {
        $totalRequests = DocumentRequest::count();
        $pendingReviews = DocumentRequest::where('status', 'Pending')->count();
        $approvedToday = DocumentRequest::where('status', 'Approved')
                                        ->whereDate('updated_at', Carbon::today())
                                        ->count();
        $overdueRequests = DocumentRequest::where('status', 'Pending')
                                        ->where('created_at', '<', Carbon::now()->subDays(3))
                                        ->count();

        $avgProcessing = round(
            (float) (DocumentRequest::where('status', 'Approved')
                ->whereNotNull('updated_at')
                ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
                ->value('avg_days') ?? 0),
            1
        );

        $tabCounts = [
            'Pending_Review' => DocumentRequest::whereIn('status', ['Pending', 'Under Review'])->count(),
            'Processing' => DocumentRequest::where('status', 'Processing')->count(),
            'Ready' => DocumentRequest::where('status', 'Ready')->count(),
            'Completed' => DocumentRequest::where('status', 'Completed')->count(),
            'Rejected' => DocumentRequest::where('status', 'Rejected')->count(),
        ];

        $currentStatus = $request->status ?? 'Pending_Review';

        $query = DocumentRequest::with('user')->orderBy('created_at', 'desc');

        if ($currentStatus === 'Pending_Review') {
            $query->whereIn('status', ['Pending', 'Under Review']);
        } elseif ($currentStatus !== 'All') {
            $query->where('status', $currentStatus);
        }

        $dbTypes = DocumentType::orderBy('name')->get();
        $validTypes = $dbTypes->isNotEmpty()
            ? $dbTypes->pluck('name')->toArray()
            : [
                'Barangay Clearance',
                'Certificate of Residency',
                'Medical Assistance',
                'Certificate of Indigency',
                'Certificate of Cohabitation',
                'Cohabitation Certificate',
                'Indigency Bail Certificate',
                'Barangay Certification for Electric Connection',
                'Electric Connection Certificate',
                'Electric Certificate',
                'Senior Citizen Certification',
                'Certification for Senior Citizen Cash Assistance',
                'Certification for Burial Assistance',
                'Barangay Business Clearance',
                'Acknowledgement Receipt',
                'Barangay Certificate',
                'Barangay Certification (R.A. 11261 - First Time Jobseeker)',
                'Barangay Certification (First Time Jobseeker)',
                'First Time Job Seeker',
                'First Time Jobseeker',
                'Certification for Senior Citizen',
                'Cash Assistance Senior Certificate',
                'Barangay Certification (For Probation & Bail)',
                'Barangay Certification (For Probation and Bail)',
                'Indigency Bail Certificate',
                'Bail Certificate',
                'Probation Barangay Certificate',
                'Oath of Undertaking (R.A. 11261)',
                'Oath of Undertaking Job Seeker',
                'Barangay Certificate',
                'Scholarship Indigency',
                'Acknowledgement Receipt',
                'Brgy Clearance for Business Permit',
            ];

        if ($request->has('document_type') && in_array($request->document_type, $validTypes)) {
            $type = $request->document_type;
            $query->where(function($q) use ($type) {
                $q->where('document_type', $type)
                  ->orWhere('document_type', 'like', "%{$type}%")
                  ->orWhere('documents', 'like', "%{$type}%");
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('resident_name', 'like', "%{$search}%")
                  ->orWhere('document_type', 'like', "%{$search}%");
            });
        }

        $allRequests = $query->paginate(15)->withQueryString();
        $currentDocType = $request->document_type ?? 'All';

        $activeDocTypes = $dbTypes->where('is_active', true)->values();
        $residents = User::where('role', 'Resident')
            ->whereIn('status', ['Active', 'active'])
            ->orderBy('name')
            ->get(['id', 'user_id', 'name', 'first_name', 'last_name', 'middle_name', 'email', 'phone_number', 'purok', 'street', 'age', 'id_type']);

        return view('admin.requests.index', compact(
            'totalRequests', 
            'pendingReviews', 
            'approvedToday', 
            'overdueRequests', 
            'avgProcessing',
            'allRequests',
            'currentDocType',
            'currentStatus',
            'tabCounts',
            'validTypes',
            'activeDocTypes',
            'residents'
        ));
    }

    public function showRequest($id)
    {
        $request = DocumentRequest::find($id) 
            ?? DocumentRequest::where('tracking_number', $id)->first();

        if ($request) {
            return redirect()->route('admin.requests', [
                'status' => 'All',
                'search' => $request->tracking_number,
            ]);
        }

        return redirect()->route('admin.requests')->with('info', "Request #{$id} was not found or has already been cleared.");
    }

    public function reports()
    {
        return view('admin.reports.reports_overview');
    }

    public function profile()
    {
        $user = auth()->user(); 
        return view('admin.profile.profile_settings', compact('user'));
    }

    /**
     * Securely stream private resident selfies to authorized administrators.
     */
    public function showSelfie($filename)
    {
        $path = 'selfies/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Selfie not found.');
        }

        return Storage::disk('local')->response($path);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'contact_number'   => 'nullable|string|max:30',
            'profile_picture'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'current_password' => 'nullable|string',
            'new_password'     => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('new_password')) {
            if ($request->filled('current_password') && !Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'The current password you entered is incorrect.'])->withInput();
            }
            $user->password = Hash::make($request->input('new_password'));
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->contact_number = $request->input('contact_number');

        if ($request->input('remove_profile_picture') == '1') {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = null;
        } elseif ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        $user->save();
        
        ActivityLog::record('profile_updated', "Admin {$user->name} updated their profile settings.");

        return back()->with('success', 'Profile updated successfully!');
    }

    // ==========================================
    // ADMIN SETTINGS
    // ==========================================

    public function adminSettings()
    {
        $user = auth()->user();
        $settings = BarangaySetting::allAssoc();
        return view('admin.settings', compact('user', 'settings'));
    }

    public function updateAdminProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name  = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        ActivityLog::record('profile_updated', "Admin updated their profile information.");

        return back()->with('success', 'Profile information updated successfully!')->with('active_tab', 'profile-tab');
    }

    public function updateAdminPassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password'          => 'required',
            'new_password'              => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect.'])->withInput()->with('active_tab', 'password-tab');
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        ActivityLog::record('password_changed', "Admin changed their account password.");

        return back()->with('password_success', 'Password changed successfully!')->with('active_tab', 'password-tab');
    }

    public function updateBarangaySettings(Request $request)
    {
        $group = $request->input('_group', 'general');

        if ($group === 'barangay') {
            $request->validate([
                'barangay_name'          => 'required|string|max:255',
                'municipality'           => 'required|string|max:255',
                'province'               => 'required|string|max:255',
                'region'                 => 'nullable|string|max:255',
                'barangay_tagline'       => 'nullable|string|max:255',
                'barangay_captain'       => 'nullable|string|max:255',
                'barangay_secretary'     => 'nullable|string|max:255',
                'barangay_treasurer'     => 'nullable|string|max:255',
                'barangay_hall_address'  => 'nullable|string|max:500',
                'official_contact'       => 'nullable|string|max:255',
                'emergency_hotline'      => 'nullable|string|max:255',
                'official_email'         => 'nullable|email|max:255',
                'office_hours'           => 'nullable|string|max:255',
                'barangay_logo'          => 'nullable|image|max:2048',
                'municipal_logo'         => 'nullable|image|max:2048',
            ]);

            $fields = [
                'barangay_name', 'municipality', 'province', 'region', 'barangay_tagline',
                'barangay_captain', 'barangay_secretary', 'barangay_treasurer',
                'barangay_hall_address', 'official_contact', 'emergency_hotline',
                'official_email', 'office_hours'
            ];

            foreach ($fields as $field) {
                if ($request->has($field)) {
                    BarangaySetting::set($field, $request->input($field), 'barangay');
                }
            }

            if ($request->hasFile('barangay_logo')) {
                $path = $request->file('barangay_logo')->store('settings', 'public');
                BarangaySetting::set('barangay_logo', $path, 'barangay');
            }

            if ($request->hasFile('municipal_logo')) {
                $path = $request->file('municipal_logo')->store('settings', 'public');
                BarangaySetting::set('municipal_logo', $path, 'barangay');
            }

            ActivityLog::record('settings_updated', "Admin updated Barangay Information and Officials.");
            return back()->with('success', 'Barangay Information and Officials updated successfully!')->with('active_tab', 'barangay-tab');
        }

        if ($group === 'payment') {
            $request->validate([
                'gcash_account_name'     => 'nullable|string|max:255',
                'gcash_account_number'   => 'nullable|string|max:255',
                'gcash_qr_code'          => 'nullable|image|max:2048',
                'maya_account_name'      => 'nullable|string|max:255',
                'maya_account_number'    => 'nullable|string|max:255',
                'payment_instructions'   => 'nullable|string|max:1000',
            ]);

            BarangaySetting::set('payment_gcash_enabled', $request->has('payment_gcash_enabled') ? '1' : '0', 'payment');
            BarangaySetting::set('gcash_account_name', $request->input('gcash_account_name'), 'payment');
            BarangaySetting::set('gcash_account_number', $request->input('gcash_account_number'), 'payment');
            BarangaySetting::set('payment_maya_enabled', $request->has('payment_maya_enabled') ? '1' : '0', 'payment');
            BarangaySetting::set('maya_account_name', $request->input('maya_account_name'), 'payment');
            BarangaySetting::set('maya_account_number', $request->input('maya_account_number'), 'payment');
            BarangaySetting::set('payment_instructions', $request->input('payment_instructions'), 'payment');

            if ($request->hasFile('gcash_qr_code')) {
                $path = $request->file('gcash_qr_code')->store('settings', 'public');
                BarangaySetting::set('gcash_qr_code', $path, 'payment');
            }

            ActivityLog::record('settings_updated', "Admin updated Payment and GCash/Maya settings.");
            return back()->with('success', 'Payment and QR settings updated successfully!')->with('active_tab', 'payment-tab');
        }

        if ($group === 'documents') {
            $request->validate([
                'default_processing_days'          => 'required|integer|min:1|max:30',
                'tracking_prefix'                  => 'required|string|max:20',
                'max_active_requests_per_resident' => 'required|integer|min:1|max:20',
            ]);

            BarangaySetting::set('default_processing_days', $request->input('default_processing_days'), 'documents');
            BarangaySetting::set('tracking_prefix', strtoupper(trim($request->input('tracking_prefix'))), 'documents');
            BarangaySetting::set('online_requests_enabled', $request->has('online_requests_enabled') ? '1' : '0', 'documents');
            BarangaySetting::set('max_active_requests_per_resident', $request->input('max_active_requests_per_resident'), 'documents');

            ActivityLog::record('settings_updated', "Admin updated Document Request policies.");
            return back()->with('success', 'Document processing policies updated successfully!')->with('active_tab', 'documents-tab');
        }

        if ($group === 'residents') {
            $request->validate([
                'resident_verification_mode' => 'required|in:manual,auto',
                'minimum_registration_age'   => 'required|integer|min:1|max:100',
                'purok_list'                 => 'required|string',
                'accepted_ids'               => 'required|string',
            ]);

            BarangaySetting::set('resident_verification_mode', $request->input('resident_verification_mode'), 'residents');
            BarangaySetting::set('minimum_registration_age', $request->input('minimum_registration_age'), 'residents');
            BarangaySetting::set('purok_list', $request->input('purok_list'), 'residents');
            BarangaySetting::set('accepted_ids', $request->input('accepted_ids'), 'residents');

            ActivityLog::record('settings_updated', "Admin updated Resident registration and verification rules.");
            return back()->with('success', 'Resident registration rules updated successfully!')->with('active_tab', 'residents-tab');
        }

        if ($group === 'announcements') {
            $request->validate([
                'announcement_type'          => 'required|in:info,warning,danger,success',
                'announcement_category'      => 'nullable|string|max:100',
                'announcement_title'         => 'required|string|max:255',
                'announcement_message'       => 'required|string|max:2000',
                'announcement_action_url'    => 'nullable|string|max:255',
                'announcement_action_text'   => 'nullable|string|max:50',
                'announcement_expiry_date'   => 'nullable|date',
            ]);

            BarangaySetting::set('announcement_enabled', $request->has('announcement_enabled') ? '1' : '0', 'announcements');
            BarangaySetting::set('announcement_type', $request->input('announcement_type'), 'announcements');
            BarangaySetting::set('announcement_category', $request->input('announcement_category', 'General Advisory'), 'announcements');
            BarangaySetting::set('announcement_title', $request->input('announcement_title'), 'announcements');
            BarangaySetting::set('announcement_message', $request->input('announcement_message'), 'announcements');
            BarangaySetting::set('announcement_action_url', $request->input('announcement_action_url'), 'announcements');
            BarangaySetting::set('announcement_action_text', $request->input('announcement_action_text'), 'announcements');
            BarangaySetting::set('announcement_is_dismissible', $request->has('announcement_is_dismissible') ? '1' : '0', 'announcements');
            BarangaySetting::set('announcement_expiry_date', $request->input('announcement_expiry_date'), 'announcements');

            ActivityLog::record('settings_updated', "Admin updated Portal Announcement banner.");
            return back()->with('success', 'Portal Announcement updated successfully!')->with('active_tab', 'announcements-tab');
        }

        if ($group === 'system') {
            $request->validate([
                'session_timeout_minutes' => 'required|integer|min:15|max:1440',
            ]);

            BarangaySetting::set('system_maintenance_mode', $request->has('system_maintenance_mode') ? '1' : '0', 'system');
            BarangaySetting::set('session_timeout_minutes', $request->input('session_timeout_minutes'), 'system');

            ActivityLog::record('settings_updated', "Admin updated System and Maintenance configuration.");
            return back()->with('success', 'System configuration updated successfully!')->with('active_tab', 'system-tab');
        }

        return back();
    }

    public function exportDatabaseBackup()
    {
        $databaseName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');
        $dbKey = "Tables_in_" . $databaseName;

        $sql = "-- E-Barangay System Database Backup\n";
        $sql .= "-- Database: {$databaseName}\n";
        $sql .= "-- Generated at: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Generated by: " . auth()->user()->name . " (" . auth()->user()->email . ")\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $table = $tableObj->$dbKey ?? reset($tableObj);
            $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
            if (!empty($createTable)) {
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    foreach ($rows as $row) {
                        $rowArray = (array)$row;
                        $columns = array_map(fn($col) => "`" . addslashes($col) . "`", array_keys($rowArray));
                        $values = array_map(function ($val) {
                            if (is_null($val)) return "NULL";
                            return "'" . addslashes((string)$val) . "'";
                        }, array_values($rowArray));

                        $sql .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sql .= "\n";
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        ActivityLog::record('backup_downloaded', "Admin downloaded a database backup (.sql).");

        $filename = 'ebarangay_backup_' . date('Y_m_d_His') . '.sql';

        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ==========================================
    // DOCUMENT TYPES CATALOG (CRUD)
    // ==========================================

    public function documentTypes(Request $request)
    {
        $query = DocumentType::query();

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $documentTypes = $query->orderBy('name')->get();

        $stats = [
            'total'    => DocumentType::count(),
            'active'   => DocumentType::where('is_active', true)->count(),
            'inactive' => DocumentType::where('is_active', false)->count(),
            'paid'     => DocumentType::where('fee', '>', 0)->count(),
            'free'     => DocumentType::where('fee', 0)->count(),
        ];

        return view('admin.document_types.index', compact('documentTypes', 'stats'));
    }

    public function storeDocumentType(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255|unique:document_types,name',
            'fee'             => 'required|numeric|min:0',
            'processing_days' => 'nullable|string|max:100',
            'description'     => 'nullable|string|max:1000',
            'requirements'    => 'nullable|string',
            'is_active'       => 'nullable|boolean',
            'template_file'   => [
                'nullable',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (!$value || !$value->isValid()) {
                        return;
                    }
                    $ext = strtolower(trim($value->getClientOriginalExtension() ?: $value->guessExtension() ?: ''));
                    if (!in_array($ext, ['docx', 'doc'])) {
                        $fail('The template file field must be a file of type: docx, doc.');
                    }
                },
            ],
        ]);

        $requirementsArray = [];
        if ($request->filled('requirements')) {
            $lines = preg_split('/[\r\n,]+/', $request->requirements);
            $requirementsArray = array_values(array_filter(array_map('trim', $lines)));
        }

        $templateFileName = null;
        if ($request->hasFile('template_file')) {
            $destDir = storage_path('app/document_templates');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            $file = $request->file('template_file');
            $slug = Str::slug($request->name ?: 'document');
            $ext = strtolower(trim($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'docx'));
            $cleanName = 'template_' . $slug . '_' . time() . '.' . $ext;
            $file->move($destDir, $cleanName);
            $templateFileName = 'document_templates/' . $cleanName;
        }

        $docType = DocumentType::create([
            'name'            => trim($request->name),
            'fee'             => $request->fee,
            'processing_days' => $request->processing_days ?: '1-2 Business Days',
            'description'     => $request->description,
            'requirements'    => $requirementsArray,
            'template_file'   => $templateFileName,
            'is_active'       => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        ActivityLog::record('document_type_created', "Created new document type: {$docType->name} with fee ₱" . number_format($docType->fee, 2) . ($templateFileName ? " and custom Word template." : "."));

        return back()->with('success', "Document type '{$docType->name}' added successfully" . ($templateFileName ? " with custom Word template." : "."));
    }

    public function updateDocumentType(Request $request, $id)
    {
        $docType = DocumentType::findOrFail($id);

        $request->validate([
            'name'            => ['required', 'string', 'max:255', Rule::unique('document_types', 'name')->ignore($docType->id)],
            'fee'             => 'required|numeric|min:0',
            'processing_days' => 'nullable|string|max:100',
            'description'     => 'nullable|string|max:1000',
            'requirements'    => 'nullable|string',
            'is_active'       => 'nullable|boolean',
            'template_file'   => [
                'nullable',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (!$value || !$value->isValid()) {
                        return;
                    }
                    $ext = strtolower(trim($value->getClientOriginalExtension() ?: $value->guessExtension() ?: ''));
                    if (!in_array($ext, ['docx', 'doc'])) {
                        $fail('The template file field must be a file of type: docx, doc.');
                    }
                },
            ],
            'remove_template' => 'nullable|boolean',
        ]);

        $requirementsArray = [];
        if ($request->filled('requirements')) {
            $lines = preg_split('/[\r\n,]+/', $request->requirements);
            $requirementsArray = array_values(array_filter(array_map('trim', $lines)));
        }

        $updateData = [
            'name'            => trim($request->name),
            'fee'             => $request->fee,
            'processing_days' => $request->processing_days ?: '1-2 Business Days',
            'description'     => $request->description,
            'requirements'    => $requirementsArray,
            'is_active'       => $request->has('is_active') ? $request->boolean('is_active') : $docType->is_active,
        ];

        if ($request->hasFile('template_file')) {
            $destDir = storage_path('app/document_templates');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            if ($docType->template_file && !in_array($docType->template_file, ['clearance_template.docx', 'residency_template.docx', 'business_permit_template.docx', 'indigency_template.docx'])) {
                @unlink(storage_path('app/' . $docType->template_file));
                @unlink(storage_path('app/private/' . $docType->template_file));
            }
            $file = $request->file('template_file');
            $slug = Str::slug($request->name ?: 'document');
            $ext = strtolower(trim($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'docx'));
            $cleanName = 'template_' . $slug . '_' . time() . '.' . $ext;
            $file->move($destDir, $cleanName);
            $updateData['template_file'] = 'document_templates/' . $cleanName;
        } elseif ($request->boolean('remove_template')) {
            if ($docType->template_file && !in_array($docType->template_file, ['clearance_template.docx', 'residency_template.docx', 'business_permit_template.docx', 'indigency_template.docx'])) {
                @unlink(storage_path('app/' . $docType->template_file));
                @unlink(storage_path('app/private/' . $docType->template_file));
            }
            $updateData['template_file'] = null;
        }

        $docType->update($updateData);

        ActivityLog::record('document_type_updated', "Updated document type: {$docType->name}." . (!empty($updateData['template_file']) ? " Attached new Word template: " . basename($updateData['template_file']) : ""));

        return back()->with('success', "Document type '{$docType->name}' updated successfully" . (!empty($updateData['template_file']) ? " with updated Word template." : "."));
    }

    public function downloadDocumentTypeTemplate($id)
    {
        $docType = DocumentType::findOrFail($id);
        if (!$docType->template_file) {
            return back()->with('error', 'This document type does not have a custom template.');
        }

        $possiblePaths = [
            storage_path('app/' . $docType->template_file),
            storage_path('app/document_templates/' . $docType->template_file),
            storage_path('app/templates/' . $docType->template_file),
            storage_path('app/private/' . $docType->template_file),
            storage_path('app/private/document_templates/' . $docType->template_file),
        ];

        $foundPath = null;
        foreach ($possiblePaths as $p) {
            if (file_exists($p)) {
                $foundPath = $p;
                break;
            }
        }

        if (!$foundPath) {
            return back()->with('error', 'Template file not found on disk.');
        }

        $downloadName = Str::slug($docType->name) . '_template.' . pathinfo($foundPath, PATHINFO_EXTENSION);
        return response()->download($foundPath, $downloadName);
    }

    public function toggleDocumentTypeStatus($id)
    {
        $docType = DocumentType::findOrFail($id);
        $docType->is_active = !$docType->is_active;
        $docType->save();

        $statusText = $docType->is_active ? 'activated' : 'deactivated';
        ActivityLog::record('document_type_status_toggled', "Document type '{$docType->name}' has been {$statusText}.");

        return back()->with('success', "Document type '{$docType->name}' has been {$statusText}.");
    }

    public function destroyDocumentType($id)
    {
        $docType = DocumentType::findOrFail($id);
        $name = $docType->name;

        $docType->delete();
        ActivityLog::record('document_type_deleted', "Permanently deleted document type: {$name}.");

        return back()->with('success', "Document type '{$name}' has been permanently deleted.");
    }

    public function bulkDestroyDocumentTypes(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:document_types,id',
        ]);

        $ids = $request->input('ids', []);
        $docTypes = DocumentType::whereIn('id', $ids)->get();

        $deletedCount = 0;
        foreach ($docTypes as $docType) {
            $docType->delete();
            $deletedCount++;
        }

        ActivityLog::record('document_types_bulk_deleted', "Permanently deleted {$deletedCount} document type(s).");

        return back()->with('success', "Permanently deleted {$deletedCount} document type(s).");
    }

    // ==========================================
    // WALK-IN REQUEST (ADMIN CRUD)
    // ==========================================

    public function storeWalkInRequest(Request $request)
    {
        $request->validate([
            'resident_type'  => 'required|in:registered,walkin',
            'user_id'        => 'required_if:resident_type,registered|nullable|exists:users,id',
            'first_name'     => 'required_if:resident_type,walkin|nullable|string|max:255',
            'last_name'      => 'required_if:resident_type,walkin|nullable|string|max:255',
            'middle_initial' => 'nullable|string|max:10',
            'age'            => 'nullable|integer|min:0|max:150',
            'contact_number' => 'nullable|string|max:20',
            'resident_email' => 'nullable|email|max:255',
            'purok'          => 'nullable|string|max:255',
            'street'         => 'nullable|string|max:255',
            'id_type'        => 'nullable|string|max:255',
            'document_type'  => 'required|string|max:255',
            'purpose'        => 'required|string|max:2000',
            'priority'       => 'nullable|in:Normal,Urgent',
            'initial_status' => 'nullable|in:Pending,Processing,Ready,Completed',
            'fee'            => 'nullable|numeric|min:0',
            'waive_fee'      => 'nullable|boolean',
            'attachments.*'  => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ]);

        $newRequest = new DocumentRequest();
        do {
            $trackingNumber = 'REQ-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (DocumentRequest::where('tracking_number', $trackingNumber)->exists());
        $newRequest->tracking_number = $trackingNumber;

        if ($request->resident_type === 'registered') {
            $user = User::findOrFail($request->user_id);
            $newRequest->user_id = $user->id;
            $newRequest->first_name = strtoupper($user->first_name ?: (explode(' ', trim($user->name))[0] ?? ''));
            $newRequest->middle_initial = strtoupper($user->middle_name ? substr(trim($user->middle_name), 0, 1) : '');
            $newRequest->last_name = strtoupper($user->last_name ?: (implode(' ', array_slice(explode(' ', trim($user->name)), 1)) ?: $user->name));
            $newRequest->contact_number = $user->phone_number;
            $newRequest->resident_email = $user->email ? strtolower($user->email) : null;
            $newRequest->purok = strtoupper($user->purok ?? '');
            $newRequest->street = strtoupper($user->sitio ?? $user->street ?? '');
            $newRequest->city = strtoupper($request->input('city', 'COGON EAST, CARMEN, CEBU'));
            $newRequest->age = $user->age;
            $newRequest->id_type = $request->input('id_type') ?: $user->id_type;
        } else {
            $newRequest->user_id = null;
            $newRequest->first_name = strtoupper(trim($request->first_name));
            $newRequest->middle_initial = $request->filled('middle_initial') ? strtoupper(trim($request->middle_initial)) : null;
            $newRequest->last_name = strtoupper(trim($request->last_name));
            $newRequest->contact_number = $request->contact_number;
            $newRequest->resident_email = $request->filled('resident_email') ? strtolower(trim($request->resident_email)) : null;
            $newRequest->purok = $request->filled('purok') ? strtoupper(trim($request->purok)) : null;
            $newRequest->street = $request->filled('street') ? strtoupper(trim($request->street)) : null;
            $newRequest->city = strtoupper($request->input('city', 'COGON EAST, CARMEN, CEBU'));
            $newRequest->age = $request->age;
            $newRequest->id_type = $request->id_type;
        }

        $docType = trim($request->document_type);
        $purpose = strtoupper(trim($request->purpose));
        $fee = $request->boolean('waive_fee') ? 0 : ($request->filled('fee') ? (float)$request->fee : null);

        $newRequest->document_type = $docType;
        $newRequest->document_type_id = \App\Models\DocumentType::where('name', $docType)->value('id');
        $newRequest->purpose = $purpose;
        $newRequest->fee = $fee;
        $newRequest->priority = $request->input('priority', 'Normal');
        $newRequest->status = $request->input('initial_status', 'Pending');
        $newRequest->documents = [
            [
                'type'         => $docType,
                'purpose'      => $purpose,
                'fee'          => $fee,
                'is_generated' => false,
            ]
        ];

        $newRequest->save();

        if ($request->hasFile('attachments')) {
            $attachmentsData = [];
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $path = $file->store('attachments', 'public');
                    $ext = strtolower($file->getClientOriginalExtension());
                    $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'image' : 'document';
                    $attachmentsData[] = [
                        'file_path' => $path,
                        'file_name' => $originalName,
                        'doc_type'  => 'Walk-in Attachment',
                        'file_type' => $fileType,
                        'file_size' => $file->getSize(),
                    ];
                }
            }
            if (!empty($attachmentsData)) {
                $newRequest->attachments = $attachmentsData;
                $newRequest->save();
            }
        }

        ActivityLog::record(
            'walkin_request_created',
            "Admin created walk-in request #{$newRequest->tracking_number} ({$newRequest->document_type}) for {$newRequest->resident_name}."
        );

        return back()->with('success', "Walk-in request #{$newRequest->tracking_number} created successfully with status '{$newRequest->status}'.");
    }

    // ==========================================
    // ACTIVITY LOGS
    // ==========================================

    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(25)->withQueryString();
        $actionTypes = ActivityLog::distinct()->pluck('action')->sort()->values();

        return view('admin.activity_logs', compact('logs', 'actionTypes'));
    }

    public function destroyActivityLog($id)
    {
        $log = ActivityLog::findOrFail($id);
        $desc = $log->description;
        $log->delete();

        return back()->with('success', 'Activity log deleted successfully.');
    }

    public function bulkDestroyActivityLogs(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:activity_logs,id',
        ]);

        $ids = $request->input('ids', []);
        $deletedCount = ActivityLog::whereIn('id', $ids)->delete();

        return back()->with('success', "Permanently deleted {$deletedCount} activity log(s).");
    }

    // ==========================================
    // ADMIN NOTIFICATIONS (AJAX)
    // ==========================================

    public function getAdminNotifications()
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->notifications()->latest()->take(10)->get()->map(function ($n) {
            return [
                'id'         => $n->id,
                'message'    => $n->data['message'] ?? '',
                'link'       => $n->data['link'] ?? '#',
                'type'       => $n->data['type'] ?? 'info',
                'read'       => !is_null($n->read_at),
                'created_at' => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAdminNotificationsRead(Request $request)
    {
        $user = auth()->user();

        if ($request->filled('notification_id')) {
            $notification = $user->notifications()->find($request->notification_id);
            if ($notification) {
                $notification->markAsRead();
            }
        } else {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    // ==========================================
    // THE PIPELINE: ACTION METHODS
    // ==========================================

    public function acceptRequest(Request $request, $id)
    {
        $docRequest = DocumentRequest::findOrFail($id);
        
        if ($request->filled('fee')) {
            $fee = (float)$request->fee;
            $docRequest->fee = $fee;

            if (!empty($docRequest->documents) && is_array($docRequest->documents)) {
                $docs = $docRequest->documents;
                foreach ($docs as &$doc) {
                    $doc['fee'] = $fee;
                }
                $docRequest->documents = $docs;
            }
        }

        if (in_array($docRequest->status, ['Pending', 'Under Review'])) {
            $docRequest->status = 'Processing';
        }

        $docRequest->save();

        ActivityLog::record('request_accepted', "Accepted document request #{$docRequest->tracking_number} ({$docRequest->document_type}) for {$docRequest->resident_name}.");

        return back()->with('success', 'Request accepted and moved to Processing.');
    }

    public function rejectRequest(Request $request, $id)
    {
        $docRequest = DocumentRequest::findOrFail($id);
        $docRequest->status = 'Rejected';
        $docRequest->save();

        ActivityLog::record('request_rejected', "Rejected document request #{$docRequest->tracking_number} ({$docRequest->document_type}) for {$docRequest->resident_name}.");

        return back()->with('success', 'Request has been rejected.');
    }

    private function numberToWords(float $number): string
    {
        $whole = (int)floor($number);
        $fraction = (int)round(($number - $whole) * 100);

        if ($whole == 0 && $fraction == 0) {
            return 'ZERO PESOS ONLY';
        }

        $ones = [
            0 => '', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR',
            5 => 'FIVE', 6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE',
            10 => 'TEN', 11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN',
            14 => 'FOURTEEN', 15 => 'FIFTEEN', 16 => 'SIXTEEN', 17 => 'SEVENTEEN',
            18 => 'EIGHTEEN', 19 => 'NINETEEN',
        ];
        $tens = [
            2 => 'TWENTY', 3 => 'THIRTY', 4 => 'FORTY', 5 => 'FIFTY',
            6 => 'SIXTY', 7 => 'SEVENTY', 8 => 'EIGHTY', 9 => 'NINETY',
        ];

        $convertChunk = function ($n) use ($ones, $tens, &$convertChunk) {
            if ($n == 0) return '';
            if ($n < 20) return $ones[$n];
            if ($n < 100) return $tens[(int)($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
            if ($n < 1000) {
                return $ones[(int)($n / 100)] . ' HUNDRED' . ($n % 100 ? ' ' . $convertChunk($n % 100) : '');
            }
            return '';
        };

        $words = [];
        if ($whole >= 1000000) {
            $millions = (int)($whole / 1000000);
            $words[] = $convertChunk($millions) . ' MILLION';
            $whole %= 1000000;
        }
        if ($whole >= 1000) {
            $thousands = (int)($whole / 1000);
            $words[] = $convertChunk($thousands) . ' THOUSAND';
            $whole %= 1000;
        }
        if ($whole > 0) {
            $words[] = $convertChunk($whole);
        }

        $pesosPart = trim(implode(' ', $words)) . ' PESOS';
        if ($fraction > 0) {
            $fractionWords = $convertChunk($fraction);
            return trim($pesosPart . ' AND ' . $fractionWords . ' CENTAVOS ONLY');
        }

        return trim($pesosPart . ' ONLY');
    }

    private function createWordDocumentForType(DocumentRequest $request, string $docType, ?string $purpose = null): string
    {
        // Load official barangay settings for dynamic headers and signatories
        $settings = BarangaySetting::allAssoc();
        $barangayName = strtoupper($settings['barangay_name'] ?? 'Barangay Cogon East');
        $city = $settings['city_municipality'] ?? 'Ormoc City';
        $province = $settings['province'] ?? 'Leyte';
        $captain = strtoupper($settings['barangay_captain'] ?? ($settings['punong_barangay'] ?? 'Hon. Punong Barangay'));
        $secretary = strtoupper($settings['barangay_secretary'] ?? ($settings['secretary'] ?? 'Barangay Secretary'));

        $templateFile = '';
        $outputName = '';
        $normalizedDocType = trim($docType);

        // 1. Check if DocumentType has an attached custom template
        $docTypeRecord = null;
        if (!empty($request->document_type_id)) {
            $docTypeRecord = DocumentType::find($request->document_type_id);
        }
        if (!$docTypeRecord) {
            $docTypeRecord = DocumentType::where('name', $normalizedDocType)->first();
        }

        $customTemplatePath = null;
        if ($docTypeRecord && !empty($docTypeRecord->template_file)) {
            $possiblePaths = [
                storage_path('app/' . $docTypeRecord->template_file),
                storage_path('app/document_templates/' . $docTypeRecord->template_file),
                storage_path('app/templates/' . $docTypeRecord->template_file),
                storage_path('app/private/' . $docTypeRecord->template_file),
                storage_path('app/private/document_templates/' . $docTypeRecord->template_file),
                public_path('templates/' . $docTypeRecord->template_file),
            ];
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $customTemplatePath = $path;
                    break;
                }
            }
        }

        // Output file naming
        $cleanDoc = preg_replace('/[^A-Za-z0-9_]/', '_', $normalizedDocType);
        $outputName = $cleanDoc ?: 'Document';

        // 2. Fall back to standard templates if no custom template
        if (!$customTemplatePath) {
            switch ($normalizedDocType) {
                case 'Barangay Clearance':
                    $templateFile = 'clearance_template.docx';
                    $outputName = 'Clearance';
                    break;
                case 'Senior Citizen Certification':
                case 'Certification for Senior Citizen':
                    $templateFile = 'senior_citizen_certification_template.docx';
                    $outputName = 'Senior_Citizen_Certification';
                    break;
                case 'Certification for Senior Citizen Cash Assistance':
                case 'Cash Assistance Senior Certificate':
                    $templateFile = 'senior_citizen_cash_assistance_template.docx';
                    $outputName = 'Senior_Citizen_Cash_Assistance';
                    break;
                case 'Certification for Burial Assistance':
                case 'Burial Senior Citizen Certificate':
                case 'Burial Assistance':
                    $templateFile = 'burial_assistance_template.docx';
                    $outputName = 'Burial_Assistance';
                    break;
                case 'Barangay Business Clearance':
                case 'Business Permit':
                case 'Brgy Clearance for Business Permit':
                    $templateFile = 'business_clearance_template.docx';
                    $outputName = 'Business_Clearance';
                    break;
                case 'Barangay Certification for Electric Connection':
                case 'Electric Connection Certificate':
                case 'Electric Certificate':
                    $templateFile = 'electric_connection_template.docx';
                    $outputName = 'Electric_Connection_Certificate';
                    break;
                case 'Acknowledgement Receipt':
                    $templateFile = 'acknowledgement_receipt_template.docx';
                    $outputName = 'Acknowledgement_Receipt';
                    break;
                case 'Certificate of Cohabitation':
                case 'Cohabitation Certificate':
                    $templateFile = 'cohabitation_certificate_template.docx';
                    $outputName = 'Certificate_of_Cohabitation';
                    break;
                case 'Barangay Certificate':
                    $templateFile = 'barangay_certificate_template.docx';
                    $outputName = 'Barangay_Certificate';
                    break;
                case 'Barangay Certification (R.A. 11261 - First Time Jobseeker)':
                case 'Barangay Certification (First Time Jobseeker)':
                case 'First Time Job Seeker':
                case 'First Time Jobseeker':
                    $templateFile = 'first_time_jobseeker_template.docx';
                    $outputName = 'First_Time_Jobseeker_Certification';
                    break;
                case 'Certificate of Residency':
                    $templateFile = 'residency_template.docx';
                    $outputName = 'Residency';
                    break;
                case 'Certificate of Indigency':
                    $templateFile = 'indigency_template.docx';
                    $outputName = 'Indigency';
                    break;
                case 'Barangay Certification (For Probation & Bail)':
                case 'Barangay Certification (For Probation and Bail)':
                case 'Probation & Bail':
                case 'Probation and Bail':
                case 'Indigency Bail Certificate':
                case 'Bail Certificate':
                case 'Probation Barangay Certificate':
                    $templateFile = 'probation_bail_template.docx';
                    $outputName = 'Probation_and_Bail_Certification';
                    break;
                case 'Oath of Undertaking (R.A. 11261)':
                case 'Oath of Undertaking':
                case 'Oath of Undertaking Job Seeker':
                    $templateFile = 'oath_of_undertaking_template.docx';
                    $outputName = 'Oath_of_Undertaking';
                    break;
                default:
                    $templateFile = '';
                    break;
            }
        }

        $residentName = strtoupper($request->resident_name ?: 'RESIDENT APPLICANT');
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $residentName);
        $safeTracking = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->tracking_number);
        $fileName = $safeName . '_' . $outputName . '_' . $safeTracking . '.docx';
        $tempPath = storage_path('app/' . $fileName);

        // Resolve resident user if available to get additional profile info (like civil status)
        $user = null;
        if (!empty($request->user_id)) {
            $user = User::find($request->user_id);
        } elseif (!empty($request->resident_email)) {
            $user = User::where('email', $request->resident_email)->first();
        }

        $civilStatus = strtoupper($request->civil_status ?? ($user?->civil_status ?? 'SINGLE'));
        $orNumber = $request->or_number ?? ($request->or_no ?? str_replace('REQ', 'REC', $request->tracking_number));
        $numericFee = ($request->fee !== null && (float)$request->fee > 0) ? (float)$request->fee : 0;
        $amountFormatted = number_format($numericFee, 2);

        $rawAge = $request->age ?: ($user?->age ?? null);
        if (!$rawAge && $user?->birthdate) {
            $rawAge = \Carbon\Carbon::parse($user->birthdate)->age;
        }
        $cleanAge = $rawAge ? trim(preg_replace('/[^0-9]/', '', (string)$rawAge)) : '';
        if (empty($cleanAge)) {
            $cleanAge = $rawAge ?: 'legal age';
        }

        $purokVal = $request->purok ?: ($user?->purok ?? '');
        if (is_numeric($purokVal)) {
            $purokVal = 'Purok ' . $purokVal;
        }

        $templatePath = $customTemplatePath ?: ($templateFile ? storage_path('app/' . $templateFile) : null);

        if ($templatePath && file_exists($templatePath)) {
            // Process standard or custom Word template using PhpWord TemplateProcessor
            $templateProcessor = new TemplateProcessor($templatePath);

            // Core merge fields
            $templateProcessor->setValue('name', $residentName);
            $templateProcessor->setValue('resident_name', $residentName);
            $templateProcessor->setValue('full_name', $residentName);
            $templateProcessor->setValue('tracking_number', $request->tracking_number);
            $templateProcessor->setValue('date', date('jS \d\a\y \o\f F, Y'));
            $templateProcessor->setValue('day', date('jS'));
            $templateProcessor->setValue('month', date('F'));
            $templateProcessor->setValue('year', date('Y'));
            $templateProcessor->setValue('current_date', date('F d, Y'));
            $templateProcessor->setValue('age', $cleanAge);
            $templateProcessor->setValue('civil_status', $civilStatus);
            $templateProcessor->setValue('purpose', strtoupper($purpose ?: ($request->purpose ?? 'Whatever legal purpose it may serve')));
            $templateProcessor->setValue('barangay_name', $barangayName);
            $templateProcessor->setValue('city', $city);
            $templateProcessor->setValue('province', $province);
            $templateProcessor->setValue('captain', $captain);
            $templateProcessor->setValue('captain_name', $captain);
            $templateProcessor->setValue('punong_barangay', $captain);
            $templateProcessor->setValue('secretary', $secretary);
            $templateProcessor->setValue('secretary_name', $secretary);
            $templateProcessor->setValue('barangay_secretary', $secretary);

            // Additional fields for new official templates
            $birthdateFormatted = 'N/A';
            if ($user?->birthdate) {
                $birthdateFormatted = date('F d, Y', strtotime($user->birthdate));
            } elseif (!empty($request->birthdate)) {
                $birthdateFormatted = date('F d, Y', strtotime($request->birthdate));
            } elseif ($cleanAge && is_numeric($cleanAge)) {
                $birthdateFormatted = date('F d, Y', strtotime("-{$cleanAge} years"));
            }
            $templateProcessor->setValue('birthdate', $birthdateFormatted);

            // Burial assistance fields
            $deceasedName = !empty($request->deceased_name) ? strtoupper($request->deceased_name) : 'THE DECEASED RESIDENT';
            $dateOfDeath = !empty($request->date_of_death) ? date('F d, Y', strtotime($request->date_of_death)) : date('F d, Y');
            $relationship = !empty($request->relationship) ? $request->relationship : 'authorized family member';
            $templateProcessor->setValue('deceased_name', $deceasedName);
            $templateProcessor->setValue('date_of_death', $dateOfDeath);
            $templateProcessor->setValue('relationship', $relationship);

            // Business clearance fields
            $businessName = !empty($request->business_name) ? strtoupper($request->business_name) : 'REGISTERED BUSINESS';
            $businessAddress = !empty($request->business_address) ? strtoupper($request->business_address) : "BARANGAY {$barangayName}, {$city}, {$province}";
            $templateProcessor->setValue('business_name', $businessName);
            $templateProcessor->setValue('business_address', $businessAddress);

            // Acknowledgement receipt fields
            $treasurer = strtoupper($settings['barangay_treasurer'] ?? ($settings['treasurer'] ?? 'HON. BARANGAY TREASURER'));
            $templateProcessor->setValue('treasurer_name', $treasurer);
            $templateProcessor->setValue('barangay_treasurer', $treasurer);
            $amountInWords = $this->numberToWords($numericFee);
            $templateProcessor->setValue('amount_in_words', $amountInWords);

            // Cohabitation fields
            $partnerName = !empty($request->partner_name) ? strtoupper($request->partner_name) : (!empty($request->partner) ? strtoupper($request->partner) : 'COMMON-LAW PARTNER');
            $yearsCohabitating = !empty($request->years_cohabitating) ? $request->years_cohabitating : (!empty($request->years) ? $request->years : '5');
            $templateProcessor->setValue('partner_name', $partnerName);
            $templateProcessor->setValue('years_cohabitating', $yearsCohabitating);

            // First time jobseeker fields
            $residenceYears = !empty($request->residence_years) ? $request->residence_years : (!empty($request->years) ? $request->years : '5');
            $templateProcessor->setValue('residence_years', $residenceYears);

            // Additional resident & document details for custom templates
            $templateProcessor->setValue('address', strtoupper($request->address ?? ''));
            $templateProcessor->setValue('purok', strtoupper($purokVal));
            $templateProcessor->setValue('contact_number', $request->contact_number ?? '');
            $templateProcessor->setValue('email', $request->resident_email ?? '');
            $templateProcessor->setValue('id_type', $request->id_type ?? 'None');
            $templateProcessor->setValue('fee', $request->fee !== null ? '₱' . number_format((float)$request->fee, 2) : 'FREE');
            $templateProcessor->setValue('amount', $amountFormatted);
            $templateProcessor->setValue('amount_paid', $amountFormatted);
            $templateProcessor->setValue('or_number', $orNumber);
            $templateProcessor->setValue('or_no', $orNumber);
            $templateProcessor->setValue('document_type', $normalizedDocType);
            $templateProcessor->setValue('document_title', $normalizedDocType);

            $templateProcessor->saveAs($tempPath);
        } else {
            // Dynamically generate official municipal document with PhpWord
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->setDefaultFontName('Times New Roman');
            $phpWord->setDefaultFontSize(12);

            $section = $phpWord->addSection([
                'marginTop'    => 1440,
                'marginBottom' => 1440,
                'marginLeft'   => 1440,
                'marginRight'  => 1440,
            ]);

            // Official Header
            $section->addText('Republic of the Philippines', ['name' => 'Times New Roman', 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addText("Province of {$province}", ['name' => 'Times New Roman', 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addText("Municipality/City of {$city}", ['name' => 'Times New Roman', 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addText("BARANGAY {$barangayName}", ['name' => 'Times New Roman', 'size' => 14, 'bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addText('OFFICE OF THE PUNONG BARANGAY', ['name' => 'Times New Roman', 'size' => 11, 'italic' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addTextBreak(1);

            // Document Title
            $docTitle = strtoupper($normalizedDocType);
            if (!str_contains($docTitle, 'CERTIFICATE') && !str_contains($docTitle, 'CLEARANCE') && !str_contains($docTitle, 'PERMIT')) {
                $docTitle = "BARANGAY CERTIFICATE ({$docTitle})";
            }
            $section->addText($docTitle, ['name' => 'Times New Roman', 'size' => 16, 'bold' => true, 'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addTextBreak(2);

            // Salutation
            $section->addText('TO WHOM IT MAY CONCERN:', ['name' => 'Times New Roman', 'size' => 12, 'bold' => true]);
            $section->addTextBreak(1);

            // Body
            $address = $request->purok ? "of {$request->purok}" : "of this barangay";
            $ageText = $request->age ? "{$request->age} years of age" : "of legal age";

            $body = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
            $body->addText("\t\tThis is to certify that ");
            $body->addText($residentName, ['bold' => true]);
            $body->addText(", {$ageText}, Filipino citizen, is a bonafide resident {$address}, Barangay {$barangayName}, {$city}, {$province}.");
            $section->addTextBreak(1);

            $effectivePurpose = strtoupper($purpose ?: ($request->purpose ?? 'whatever legal purpose it may serve'));
            $body2 = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
            $body2->addText("\t\tThis certification is issued upon the request of the above-named person for the purpose of ");
            $body2->addText($effectivePurpose, ['bold' => true]);
            $body2->addText(' and for whatever legal intent it may serve.');
            $section->addTextBreak(1);

            $issuedDate = date('jS \d\a\y \o\f F, Y');
            $section->addText("Issued this {$issuedDate} at the Barangay Hall of Barangay {$barangayName}, {$city}, {$province}.", ['name' => 'Times New Roman', 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
            $section->addTextBreak(1);

            $section->addText("Tracking No: {$request->tracking_number}", ['name' => 'Times New Roman', 'size' => 10, 'italic' => true]);

            // Signatures
            $section->addTextBreak(3);
            $section->addText("HON. {$captain}", ['name' => 'Times New Roman', 'size' => 12, 'bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $section->addText('Punong Barangay', ['name' => 'Times New Roman', 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

            $section->addTextBreak(2);
            $section->addText('Doc No. _____ Page No. _____ Book No. _____ Series of ' . date('Y'), ['name' => 'Times New Roman', 'size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($tempPath);
        }

        return $tempPath;
    }

    public function generateWord(Request $httpRequest, $id)
    {
        $request = DocumentRequest::findOrFail($id);

        $request->is_generated = true;

        if (in_array($request->status, ['Pending', 'Under Review'])) {
            $request->status = 'Processing';
        }
        $request->save();

        ActivityLog::record('document_generated', "Generated Word document for request #{$request->tracking_number} ({$request->document_type}).");

        $requestedDocType = $httpRequest->input('doc_type');
        $docsList = $request->documents_list;

        // 1. Specific requested document type
        if ($requestedDocType) {
            $matchingItem = collect($docsList)->firstWhere('type', $requestedDocType);
            $docPurpose = $matchingItem['purpose'] ?? $request->purpose;
            $tempPath = $this->createWordDocumentForType($request, $requestedDocType, $docPurpose);
            $downloadName = basename($tempPath);

            return response()->download($tempPath, $downloadName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        }

        // 2. Multi-document request: bundle into ZIP
        if (count($docsList) > 1) {
            $generatedFiles = [];
            foreach ($docsList as $docItem) {
                $itemType = $docItem['type'] ?? $request->document_type;
                $itemPurpose = $docItem['purpose'] ?? $request->purpose;
                $generatedFiles[] = $this->createWordDocumentForType($request, $itemType, $itemPurpose);
            }

            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->resident_name);
            $safeTracking = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->tracking_number);
            $zipName = "{$safeName}_Documents_{$safeTracking}.zip";
            $zipPath = storage_path('app/' . $zipName);

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($generatedFiles as $filePath) {
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, basename($filePath));
                    }
                }
                $zip->close();
            }

            foreach ($generatedFiles as $filePath) {
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            return response()->download($zipPath, $zipName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        }

        // 3. Single document request
        $singleDocType = $docsList[0]['type'] ?? $request->document_type;
        $singlePurpose = $docsList[0]['purpose'] ?? $request->purpose;
        $tempPath = $this->createWordDocumentForType($request, $singleDocType, $singlePurpose);
        $downloadName = basename($tempPath);

        return response()->download($tempPath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function markReady($id)
    {
        $request = DocumentRequest::findOrFail($id);
        $request->status = 'Ready';
        $request->save();

        ActivityLog::record('request_ready', "Marked request #{$request->tracking_number} as Ready for Pick-up.");

        if ($request->resident_email) {
            Mail::to($request->resident_email)->send(new DocumentReady($request));
        }

        $user = null;
        if (isset($request->user_id)) {
            $user = User::find($request->user_id);
        } else if ($request->resident_email) {
            $user = User::where('email', $request->resident_email)->first();
        }

        if ($user) {
            $user->notify(new \App\Notifications\DocumentReadyNotification($request));
        }

        return back()->with('success', 'Document marked as Ready for Pick-up! Notification sent to the resident.');
    }

    public function markCompleted($id)
    {
        $request = DocumentRequest::findOrFail($id);
        $request->status = 'Completed';
        $request->save();

        ActivityLog::record('request_completed', "Marked request #{$request->tracking_number} as Completed (claimed by resident).");

        return back()->with('success', 'Document marked as Completed!');
    }

    public function concerns(Request $request)
    {
        $query = CommunityConcern::with(['user', 'remarks.admin', 'updatedByAdmin'])->orderBy('created_at', 'desc');

        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('concern_id', 'like', "%{$search}%")
                  ->orWhere('resident_name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $concerns = $query->get();
        $currentStatus = $request->status ?? 'All';

        $statusCounts = [
            'All' => CommunityConcern::count(),
            'Pending' => CommunityConcern::where('status', 'Pending')->count(),
            'Under Review' => CommunityConcern::where('status', 'Under Review')->count(),
            'In Progress' => CommunityConcern::where('status', 'In Progress')->count(),
            'Resolved' => CommunityConcern::where('status', 'Resolved')->count(),
            'Rejected' => CommunityConcern::where('status', 'Rejected')->count(),
        ];

        return view('admin.concerns.index', compact('concerns', 'currentStatus', 'statusCounts'));
    }

    public function updateConcern(Request $request, $id)
    {
        $concern = CommunityConcern::findOrFail($id);

        $validated = $request->validate([
            'status'        => ['required', Rule::in(CommunityConcern::statuses())],
            'admin_remarks' => 'nullable|string|max:5000',
        ]);

        $previousStatus = $concern->status;
        $concern->status = $validated['status'];
        $concern->updated_by = auth()->id();

        if (!empty($validated['admin_remarks'])) {
            $concern->admin_remarks = $validated['admin_remarks'];

            CommunityConcernRemark::create([
                'community_concern_id' => $concern->id,
                'admin_id'             => auth()->id(),
                'remark'               => $validated['admin_remarks'],
            ]);
        }

        $concern->save();

        ActivityLog::record(
            'concern_updated',
            "Updated community concern #{$concern->concern_id} to {$concern->status}."
        );

        $resident = User::find($concern->user_id);
        if ($resident) {
            $message = $this->buildConcernNotificationMessage($concern, $previousStatus, $validated['admin_remarks'] ?? null);
            $resident->notify(new ConcernStatusUpdatedNotification($concern, $message));
        }

        return back()->with('success', 'Community concern updated successfully.');
    }

    private function buildConcernNotificationMessage(CommunityConcern $concern, string $previousStatus, ?string $remarks): string
    {
        if ($remarks) {
            return 'Barangay Staff replied to your concern: "' . $remarks . '"';
        }

        if ($concern->status === 'Under Review' && $previousStatus !== 'Under Review') {
            return 'Your concern is now Under Review.';
        }

        if ($concern->status === 'In Progress') {
            return 'Your concern is now In Progress.';
        }

        if ($concern->status === 'Resolved') {
            return 'Your concern has been marked as Resolved.';
        }

        if ($concern->status === 'Rejected') {
            return 'Your concern has been marked as Rejected.';
        }

        return 'Your community concern status has been updated to ' . $concern->status . '.';
    }
}