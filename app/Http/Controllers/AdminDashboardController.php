<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\User;
use PhpOffice\PhpWord\TemplateProcessor; 
use Illuminate\Support\Facades\Mail;     
use Illuminate\Support\Facades\Http;     
use App\Mail\AccountApproved;    
use App\Mail\DocumentReady;        
use Carbon\Carbon;
use App\Mail\AccountRejectedMail;                       

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
        $request->delete();

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
        // Your existing code that finds the user and updates their status
        $user = User::findOrFail($id);
        $user->status = 'Rejected';
        $user->save();

        // ADD THIS NEW LINE RIGHT HERE:
        Mail::to($user->email)->send(new AccountRejectedMail($user->name));

        // Your existing redirect
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

        // 1. Calculate counts for the User navigation tabs
        $tabCounts = [
            'All' => User::count(),
            'Resident' => User::where('role', 'Resident')->count(),
            'Admin' => User::where('role', 'Admin')->count(),
        ];

        $currentRole = $request->role ?? 'All';
        $query = User::orderBy('created_at', 'desc');

        // 2. Filter by Role Tab
        if ($currentRole !== 'All') {
            $query->where('role', $currentRole);
        }

        // 3. Search Bar Logic
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

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ]);

        return back()->with('success', 'User "' . $user->name . '" has been updated successfully.');
    }

    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);

        if (in_array(strtolower($user->status), ['active'])) {
            $user->status = 'inactive';
            $message = 'User "' . $user->name . '" has been deactivated.';
        } else {
            $user->status = 'active';
            $message = 'User "' . $user->name . '" has been reactivated.';
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
    // THE PIPELINE: ACTION METHODS
    // ==========================================

    public function acceptRequest($id)
    {
        $request = DocumentRequest::findOrFail($id);
        
        if (in_array($request->status, ['Pending', 'Under Review'])) {
            $request->status = 'Processing';
            $request->save();
        }

        return back()->with('success', 'Request accepted and moved to Processing.');
    }

    public function rejectRequest(Request $request, $id)
    {
        $docRequest = DocumentRequest::findOrFail($id);
        $docRequest->status = 'Rejected';
        $docRequest->save();

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

        return back()->with('success', 'Document marked as Completed!');
    }
}