<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use Illuminate\Support\Facades\Auth;

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

        // THE FIX: Match documents using the unique USER_ID instead of Email! 
        // This ensures mobile-only users still see their requests.
        $pendingCount = DocumentRequest::where('user_id', $user->id)->where('status', 'Pending')->count();
        $approvedCount = DocumentRequest::where('user_id', $user->id)->where('status', 'Approved')->count();
        $totalCount = DocumentRequest::where('user_id', $user->id)->count();

        // THE FIX: Fetch recent requests using USER_ID
        $recentRequests = DocumentRequest::where('user_id', $user->id)
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
        
        // Save new separated Personal Info (forced to uppercase for database consistency)
        $newRequest->last_name = strtoupper($request->input('last_name'));
        $newRequest->first_name = strtoupper($request->input('first_name'));
        $newRequest->middle_initial = strtoupper($request->input('middle_initial'));
        $newRequest->age = $request->input('age');

        // THE FIX: Gracefully handle optional email and contact number
        $newRequest->contact_number = $request->input('contact_number') ?? Auth::user()->phone_number;
        $inputEmail = $request->input('email') ?? Auth::user()->email;
        $newRequest->resident_email = $inputEmail ? strtolower($inputEmail) : null;

        // Save separated Address fields
        $newRequest->purok = strtoupper($request->input('purok'));
        $newRequest->street = strtoupper($request->input('street'));
        $newRequest->city = strtoupper($request->input('city', 'COGON EAST, CARMEN, CEBU'));
        
        // Document Info
        $newRequest->document_type = $request->input('document_type');
        $newRequest->priority = $request->input('urgency');
        $newRequest->purpose = strtoupper($request->input('purpose'));
        
        $newRequest->status = 'Pending';
        $newRequest->user_id = Auth::id(); 
        
        $newRequest->save();

        // Automatically record this action in the Activity Logs
        $log = new \App\Models\ActivityLog();
        $log->user_id = Auth::id();
        $log->action = 'Document Request';
        $log->description = 'Requested a ' . $request->input('document_type');
        $log->ip_address = $request->ip();
        $log->user_agent = $request->userAgent();
        $log->save();

        return redirect()->route('resident.dashboard')->with('success', 'Document request submitted successfully!');
    }

    public function trackRequest()
    {
        // THE BOUNCER
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        // THE FIX: Fetch ALL requests by USER_ID so mobile users aren't left out
        $allRequests = \App\Models\DocumentRequest::where('user_id', auth()->id())
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

        // THE FIX: Check updates via USER_ID
        $lastUpdate = DocumentRequest::where('user_id', auth()->id())->max('updated_at');

        return response()->json(['last_update' => $lastUpdate]);
    }

    /**
     * Show the activity logs for the logged-in resident.
     */
    public function activityLogs(Request $request)
    {
        // THE BOUNCER
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        // 1. Fetch the unique action types for the dropdown filter
        $actionTypes = \App\Models\ActivityLog::where('user_id', Auth::id())
                                              ->select('action')
                                              ->distinct()
                                              ->pluck('action');

        // 2. Build the query to get the logs
        $query = \App\Models\ActivityLog::where('user_id', Auth::id());

        // 3. If the user selected a specific action from the dropdown, filter by it
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 4. Get the final list of logs, paginated to 10 per page
        // withQueryString() ensures filters stay active when navigating pages
        $logs = $query->latest()->paginate(10)->withQueryString();

        // Pass BOTH variables to the view
        return view('resident.activity_logs', compact('logs', 'actionTypes'));
    }

    /**
     * View the receipt for a specific document request.
     */
    public function viewReceipt($id)
    {
        // THE BOUNCER
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $docRequest = \App\Models\DocumentRequest::findOrFail($id);

        // THE FIX: Security check now matches USER_ID
        if ((string)$docRequest->user_id !== (string)Auth::id()) {
            abort(403, 'Unauthorized access to this receipt.');
        }

        $receiptNumber = str_replace('REQ', 'REC', $docRequest->tracking_number);

        // Define the fee amount
        $feeAmount = 0; 
        if ($docRequest->document_type == 'Barangay Clearance') {
            $feeAmount = 50;
        } elseif ($docRequest->document_type == 'Business Permit') {
            $feeAmount = 150;
        }

        // Pass ALL required variables to the view
        return view('resident.receipt', compact('docRequest', 'receiptNumber', 'feeAmount'));
    }

    /**
     * Show the community concerns for the logged-in resident.
     */
    public function communityConcerns()
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $authUser = Auth::user();

        // THE FIX: Define the missing resident address using their database info
        $residentAddress = trim("{$authUser->house_number} {$authUser->street}, {$authUser->barangay_address}");

        $categories = [
            'Infrastructure & Roads',
            'Sanitation & Garbage',
            'Safety & Security',
            'Noise & Disturbances',
            'Others'
        ];

        // Fetch the user's community concerns
        $concerns = \App\Models\CommunityConcern::where('user_id', Auth::id())
                                                ->latest()
                                                ->get();

        // Pass ALL variables to the view, including the new residentAddress
        return view('resident.community_concerns', compact('concerns', 'authUser', 'categories', 'residentAddress'));
    }

    /**
     * Save a new community concern submitted by the resident.
     */
    public function storeConcern(Request $request)
    {
        // THE BOUNCER: Security Check
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        // Validate the incoming data
        $request->validate([
            'category' => 'required|string',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();

        // Create the new concern
        $concern = new \App\Models\CommunityConcern();
        
        // Generate a unique concern tracking ID
        $concern->concern_id = 'CON-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        // Auto-fill the resident's details
        $concern->user_id = $user->id;
        $concern->resident_name = $user->name;
        $concern->resident_email = $user->email;
        $concern->contact_number = $user->contact_number ?? 'N/A';
        $concern->address = trim("{$user->house_number} {$user->street}, {$user->barangay_address}");

        // Save the form inputs
        $concern->category = $request->input('category');
        $concern->description = $request->input('description');
        
        // Auto-fill the missing database columns
        $concern->title = $request->input('title', $request->input('category') . ' Report'); 
        $concern->location = $request->input('location', 'Not specified');
        $concern->incident_date = $request->input('incident_date', \Carbon\Carbon::now());

        $concern->status = 'Pending';
        
        // Handle Image
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('concerns', 'public');
            $concern->photo_path = $path; 
        }

        $concern->save();

        // Automatically record this action in the Activity Logs
        $log = new \App\Models\ActivityLog();
        $log->user_id = Auth::id();
        $log->action = 'Submitted Concern';
        $log->description = 'Reported an issue: ' . $request->input('category');
        $log->ip_address = $request->ip();
        $log->user_agent = $request->userAgent();
        $log->save();

        // Redirect back with a success message
        return back()->with('success', 'Your community concern has been submitted successfully!');
    }
}