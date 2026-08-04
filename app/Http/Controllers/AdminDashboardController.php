<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Models\ActivityLog;
use PhpOffice\PhpWord\TemplateProcessor; 
use Illuminate\Support\Facades\Mail;     
use Illuminate\Support\Facades\Http;     
use App\Mail\AccountApproved;    
use App\Mail\DocumentReady;        
use Carbon\Carbon;
use App\Mail\AccountRejectedMail;
use Illuminate\Support\Facades\Hash;

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
            $message = "Hello {$user->name}, your E-Barangay account has been approved by the Admin! You can now log in to request documents.";
            
            Http::post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => env('SEMAPHORE_API_KEY'),
                'number' => $user->phone_number,
                'message' => $message,
                'sendername' => 'SEMAPHORE'
            ]);
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
        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
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

        $approvedDocs = DocumentRequest::where('status', 'Approved')->get();
        $avgProcessing = 0;
        
        if ($approvedDocs->count() > 0) {
            $totalDays = $approvedDocs->sum(function ($doc) {
                return $doc->created_at->diffInDays($doc->updated_at); 
            });
            $avgProcessing = round($totalDays / $approvedDocs->count(), 1); 
        }

        $tabCounts = [
            'Pending_Review' => DocumentRequest::whereIn('status', ['Pending', 'Under Review'])->count(),
            'Processing' => DocumentRequest::where('status', 'Processing')->count(),
            'Ready' => DocumentRequest::where('status', 'Ready')->count(),
            'Completed' => DocumentRequest::where('status', 'Completed')->count(),
            'Rejected' => DocumentRequest::where('status', 'Rejected')->count(),
        ];

        $currentStatus = $request->status ?? 'Pending_Review';

        $query = DocumentRequest::orderBy('created_at', 'desc');

        if ($currentStatus === 'Pending_Review') {
            $query->whereIn('status', ['Pending', 'Under Review']);
        } elseif ($currentStatus !== 'All') {
            $query->where('status', $currentStatus);
        }

        $validTypes = ['Barangay Clearance', 'Certificate of Indigency', 'Certificate of Residency', 'Business Permit'];
        if ($request->has('document_type') && in_array($request->document_type, $validTypes)) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                  ->orWhere('resident_name', 'like', "%{$search}%")
                  ->orWhere('document_type', 'like', "%{$search}%");
            });
        }

        $allRequests = $query->get();
        $currentDocType = $request->document_type ?? 'All';

        return view('admin.requests.index', compact(
            'totalRequests', 
            'pendingReviews', 
            'approvedToday', 
            'overdueRequests', 
            'avgProcessing',
            'allRequests',
            'currentDocType',
            'currentStatus',
            'tabCounts'
        ));
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

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->contact_number = $request->input('contact_number');

        if ($request->filled('new_password')) {
            $user->password = bcrypt($request->input('new_password'));
        }

        $user->save();
        
        return back()->with('success', 'Profile updated successfully!');
    }

    // ==========================================
    // ADMIN SETTINGS
    // ==========================================

    public function adminSettings()
    {
        $user = auth()->user();
        return view('admin.settings', compact('user'));
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

        return back()->with('success', 'Profile information updated successfully!');
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
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect.'])->withInput();
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        ActivityLog::record('password_changed', "Admin changed their account password.");

        return back()->with('password_success', 'Password changed successfully!');
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
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    // ==========================================
    // THE PIPELINE: ACTION METHODS
    // ==========================================

    public function acceptRequest($id)
    {
        $request = DocumentRequest::findOrFail($id);
        
        if (in_array($request->status, ['Pending', 'Under Review'])) {
            $request->status = 'Processing';
            $request->save();
        }

        ActivityLog::record('request_accepted', "Accepted document request #{$request->tracking_number} ({$request->document_type}) for {$request->resident_name}.");

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

    public function generateWord($id)
    {
        $request = DocumentRequest::findOrFail($id);

        $request->is_generated = true;

        if (in_array($request->status, ['Pending', 'Under Review'])) {
            $request->status = 'Processing';
        }
        $request->save();

        ActivityLog::record('document_generated', "Generated Word document for request #{$request->tracking_number} ({$request->document_type}).");

        $templateFile = '';
        $outputName = '';

        switch ($request->document_type) {
            case 'Barangay Clearance':
                $templateFile = 'clearance_template.docx';
                $outputName = 'Clearance';
                break;
            case 'Certificate of Residency':
                $templateFile = 'residency_template.docx';
                $outputName = 'Residency';
                break;
            case 'Business Permit':
                $templateFile = 'business_permit_template.docx';
                $outputName = 'Business_Permit';
                break;
            case 'Certificate of Indigency':
            default:
                $templateFile = 'indigency_template.docx';
                $outputName = 'Indigency';
                break;
        }

        $templatePath = storage_path('app/' . $templateFile);
        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('name', $request->resident_name);
        $templateProcessor->setValue('tracking_number', $request->tracking_number);
        $templateProcessor->setValue('date', date('F j, Y'));
        $templateProcessor->setValue('age', $request->age);
        $templateProcessor->setValue('purpose', $request->purpose);

        $fileName = str_replace(' ', '_', $request->resident_name) . '_' . $outputName . '.docx';
        $tempPath = storage_path('app/' . $fileName);
        $templateProcessor->saveAs($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
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
}