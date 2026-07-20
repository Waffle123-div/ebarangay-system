<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use Illuminate\Support\Facades\Auth; // <-- Added this so we can check Auth::user()

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
}