<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\CommunityConcern;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\NewDocumentRequestNotification;
use App\Notifications\NewCommunityConcernNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ResidentDashboardController extends Controller
{
    /**
     * Show the resident dashboard with REAL data.
     */
    public function index()
    {
        $user = Auth::user();

        // ==========================================
        // THE BOUNCER: Security Check!
        // ==========================================
        if (trim(strtolower($user->status)) !== 'active') {
            // If they are not active, kick them straight to the waiting room
            return redirect()->route('resident.pending');
        }

        // THE FIX: We now match documents using the unique EMAIL instead of the name!
        $pendingCount = DocumentRequest::where('resident_email', $user->email)->where('status', 'Pending')->count();
        $approvedCount = DocumentRequest::where('resident_email', $user->email)->where('status', 'Approved')->count();
        $totalCount = DocumentRequest::where('resident_email', $user->email)->count();

        // THE FIX: Fetch recent requests using EMAIL
        $recentRequests = DocumentRequest::where('resident_email', $user->email)
                                         ->orderBy('created_at', 'desc')
                                         ->take(5)
                                         ->get();

        // WELCOME LOGIC: Grab their first name, and check if they are "New" (0 total requests)
        $firstName = explode(' ', trim($user->name))[0];
        $isNewUser = ($totalCount === 0);

        return view('resident.dashboardresident', compact(
            'pendingCount', 
            'approvedCount', 
            'totalCount', 
            'recentRequests',
            'firstName',
            'isNewUser'
        ));
    }

    public function markNotificationsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function createRequest()
    {
        // THE BOUNCER
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        return view('resident.request_document');
    }

    /**
     * Save the new document request to the database.
     */
    public function store(Request $request)
    {
        // THE BOUNCER
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $newRequest = new DocumentRequest();
        $newRequest->tracking_number = 'REQ-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        $newRequest->resident_name = $request->input('full_name');
        $newRequest->resident_email = $request->input('email');
        $newRequest->document_type = $request->input('document_type');
        $newRequest->priority = $request->input('urgency');
        
        // THESE ARE THE CRITICAL LINES:
        $newRequest->age = $request->input('age');
        $newRequest->purpose = $request->input('purpose');
        
        $newRequest->status = 'Pending';
        $newRequest->save();

        // Log this activity
        ActivityLog::record('request_submitted', "Resident {$request->input('full_name')} submitted a new {$request->input('document_type')} request (#{$newRequest->tracking_number}).", Auth::id());

        // Notify all admin users
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewDocumentRequestNotification($newRequest));
        }

        return redirect()->route('resident.dashboard')->with('success', 'Document request submitted successfully!');
    }

    public function trackRequest()
    {
        // THE BOUNCER
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        // THE FIX: Fetch ALL requests for this resident by EMAIL, ordered from newest to oldest
        $allRequests = \App\Models\DocumentRequest::where('resident_email', auth()->user()->email)
                    ->latest()
                    ->get();

        // The single most recent request goes to the big timeline tracker
        $myRequest = $allRequests->first();

        // Pass BOTH the single request and the full history list to the page
        return view('resident.track_request', compact('myRequest', 'allRequests'));
    }

    /**
     * Used by AJAX Polling to check if any documents have been updated.
     */
    public function checkUpdates()
    {
        if (!auth()->check()) {
            return response()->json(['last_update' => null]);
        }

        // Fetch the absolute newest 'updated_at' timestamp for this resident
        $lastUpdate = DocumentRequest::where('resident_email', auth()->user()->email)->max('updated_at');

        return response()->json(['last_update' => $lastUpdate]);
    }

    /**
     * Display the Activity Logs page for the authenticated resident.
     */
    public function activityLogs(Request $request)
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $user = Auth::user();
        $query = ActivityLog::where('user_id', $user->id)->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();
        $actionTypes = ActivityLog::where('user_id', $user->id)->distinct()->pluck('action')->sort()->values();

        return view('resident.activity_logs', compact('logs', 'actionTypes'));
    }

    /**
     * View official transaction receipt for a document request.
     */
    public function viewReceipt($id)
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $user = Auth::user();
        $docRequest = DocumentRequest::findOrFail($id);

        // Security check: verify this request belongs to the logged-in resident
        if ($docRequest->resident_email !== $user->email && isset($docRequest->user_id) && $docRequest->user_id != $user->id) {
            abort(403, 'Unauthorized access to transaction receipt.');
        }

        // Calculate fee schedule
        $feeMap = [
            'Barangay Clearance'        => 50.00,
            'Certificate of Residency'   => 50.00,
            'Business Permit'           => 150.00,
            'Certificate of Indigency'  => 0.00,
        ];

        $feeAmount = $feeMap[$docRequest->document_type] ?? 50.00;
        $receiptNumber = 'RCT-' . date('Y', strtotime($docRequest->created_at)) . '-' . str_pad($docRequest->id, 6, '0', STR_PAD_LEFT);

        return view('resident.receipt', compact('docRequest', 'user', 'feeAmount', 'receiptNumber'));
    }

    /**
     * Download or print official transaction receipt.
     */
    public function downloadReceipt($id)
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $user = Auth::user();
        $docRequest = DocumentRequest::findOrFail($id);

        if ($docRequest->resident_email !== $user->email && isset($docRequest->user_id) && $docRequest->user_id != $user->id) {
            abort(403, 'Unauthorized access to transaction receipt.');
        }

        // Log receipt download action
        ActivityLog::record('receipt_viewed', "Viewed transaction receipt for request #{$docRequest->tracking_number} ({$docRequest->document_type}).", Auth::id());

        return $this->viewReceipt($id);
    }

    /**
     * Display the logged-in resident's community concerns.
     */
    public function communityConcerns()
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $concerns = CommunityConcern::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('resident.community_concerns', compact('concerns'));
    }

    /**
     * Store a new community concern submitted by the resident.
     */
    public function storeConcern(Request $request)
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:200',
            'category'      => ['required', Rule::in(CommunityConcern::categories())],
            'description'   => 'required|string|max:5000',
            'location'      => 'required|string|max:255',
            'incident_date' => 'nullable|date|before_or_equal:today',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = Auth::user();
        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('concern_attachments', 'public');
        }

        $concern = CommunityConcern::create([
            'concern_id'      => 'CON-' . date('Y') . '-' . str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id'         => $user->id,
            'resident_name'   => $user->name,
            'resident_email'  => $user->email,
            'contact_number'  => $user->phone_number,
            'address'         => $user->formattedAddress() ?: null,
            'title'           => $validated['title'],
            'category'        => $validated['category'],
            'description'     => $validated['description'],
            'location'        => $validated['location'],
            'incident_date'   => $validated['incident_date'] ?? null,
            'photo_path'      => $photoPath,
            'status'          => 'Pending',
        ]);

        ActivityLog::record(
            'concern_submitted',
            "Resident {$user->name} submitted community concern #{$concern->concern_id} ({$concern->category}).",
            $user->id
        );

        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewCommunityConcernNotification($concern));
        }

        if ($request->input('redirect_to') === 'welcome') {
            return redirect()->to(route('welcome') . '#concerns')->with('success', 'Your community concern has been submitted successfully!');
        }

        return redirect()->route('resident.concerns')->with('success', 'Your community concern has been submitted successfully!');
    }
}