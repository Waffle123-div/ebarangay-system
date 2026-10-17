<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\BarangaySetting;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        // Fetch active announcement banner if enabled
        $announcement = null;
        if (BarangaySetting::get('announcement_enabled', '0') == '1') {
            $expiry = BarangaySetting::get('announcement_expiry_date');
            if (!$expiry || Carbon::parse($expiry)->endOfDay()->isFuture()) {
                $msgRecord = BarangaySetting::where('key', 'announcement_message')->first();
                $announcement = [
                    'type'           => BarangaySetting::get('announcement_type', 'info'),
                    'category'       => BarangaySetting::get('announcement_category', 'General Advisory'),
                    'title'          => BarangaySetting::get('announcement_title', 'Public Advisory'),
                    'message'        => BarangaySetting::get('announcement_message', ''),
                    'action_url'     => BarangaySetting::get('announcement_action_url'),
                    'action_text'    => BarangaySetting::get('announcement_action_text', 'Learn More'),
                    'is_dismissible' => BarangaySetting::get('announcement_is_dismissible', '1') == '1',
                    'updated_at'     => $msgRecord ? $msgRecord->updated_at : null,
                ];
            }
        }

        return view('resident.dashboardresident', compact(
            'pendingCount', 
            'approvedCount', 
            'totalCount', 
            'recentRequests',
            'firstName',
            'isNewUser',
            'announcement'
        ));
    }

    public function markNotificationsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }  

    public function createRequest()
    {
        // THE BOUNCER
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        // Fetch the user's most recent document request to drive the lifecycle tracker
        $latestRequest = \App\Models\DocumentRequest::where('user_id', Auth::id())
            ->latest()
            ->first();

        // Map request status → stepper step (1-based)
        $statusStepMap = [
            'Pending'      => 1,
            'Under Review' => 2,
            'Processing'   => 3,
            'Ready'        => 4,
            'Completed'    => 5,
            'Rejected'     => 0, // special: rejected state
        ];

        $currentStep = $latestRequest
            ? ($statusStepMap[$latestRequest->status] ?? 1)
            : 0; // 0 = no request yet

        $activeDocTypes = \App\Models\DocumentType::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('resident.request_document', compact('latestRequest', 'currentStep', 'activeDocTypes'));
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
        do {
            $trackingNumber = 'REQ-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (DocumentRequest::where('tracking_number', $trackingNumber)->exists());
        $newRequest->tracking_number = $trackingNumber;
        
        // Personal Info: support both editable full_name and separated name fields
        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $middleInitial = $request->input('middle_initial');

        if ((empty($firstName) || empty($lastName)) && $request->filled('full_name')) {
            $parts = preg_split('/\s+/', trim($request->input('full_name')));
            if (count($parts) === 1) {
                $firstName = $parts[0];
                $lastName = $parts[0];
                $middleInitial = '';
            } elseif (count($parts) === 2) {
                $firstName = $parts[0];
                $lastName = $parts[1];
                $middleInitial = '';
            } else {
                $firstName = $parts[0];
                $second = trim($parts[1], '.');
                if (strlen($second) <= 2) {
                    $middleInitial = $second;
                    $lastName = implode(' ', array_slice($parts, 2));
                } else {
                    $middleInitial = substr($parts[1], 0, 1);
                    $lastName = implode(' ', array_slice($parts, 2));
                }
            }
        }

        $newRequest->first_name = strtoupper($firstName ?: (Auth::user()->first_name ?? ''));
        $newRequest->middle_initial = strtoupper($middleInitial ?: (Auth::user()->middle_name ? substr(Auth::user()->middle_name, 0, 1) : ''));
        $newRequest->last_name = strtoupper($lastName ?: (Auth::user()->last_name ?? ''));
        $newRequest->age = $request->input('age') ?? Auth::user()->age;

        // Contact Number & Email
        $newRequest->contact_number = $request->input('contact_number') ?? Auth::user()->phone_number;
        $inputEmail = $request->input('email') ?? Auth::user()->email;
        $newRequest->resident_email = $inputEmail ? strtolower($inputEmail) : null;

        // Address fields: support both complete address and separated purok/street
        $purok = $request->input('purok');
        $street = $request->input('street');
        if (empty($purok) && $request->filled('address')) {
            $addr = trim($request->input('address'));
            $street = $addr;
            if (preg_match('/purok\s*[\w\d]+/i', $addr, $m)) {
                $purok = $m[0];
            } else {
                $purok = Auth::user()->purok;
            }
        }
        $newRequest->purok = strtoupper($purok ?: (Auth::user()->purok ?? ''));
        $newRequest->street = strtoupper($street ?: (Auth::user()->sitio ?? Auth::user()->street ?? ''));
        $newRequest->city = strtoupper($request->input('city', 'COGON EAST, CARMEN, CEBU'));
        
        // Document Info: Single document per request
        $docType = $request->input('document_type');
        $purpose = strtoupper(trim($request->input('purpose', '')));

        // If documents_json was submitted, extract the single document from it as fallback
        if (empty($docType) && $request->filled('documents_json')) {
            $decoded = json_decode($request->input('documents_json'), true);
            if (is_array($decoded) && count($decoded) > 0) {
                $docType = $decoded[0]['type'] ?? '';
                if (empty($purpose) && !empty($decoded[0]['purpose'])) {
                    $purpose = strtoupper(trim($decoded[0]['purpose']));
                }
            }
        } elseif (empty($docType) && $request->filled('documents') && is_array($request->input('documents'))) {
            $firstDoc = $request->input('documents')[0] ?? null;
            if ($firstDoc) {
                $docType = is_array($firstDoc) ? ($firstDoc['type'] ?? '') : (string)$firstDoc;
                if (empty($purpose) && is_array($firstDoc) && !empty($firstDoc['purpose'])) {
                    $purpose = strtoupper(trim($firstDoc['purpose']));
                }
            }
        }

        // Auto-assign fee from DocumentType catalog if configured
        $docTypeRecord = \App\Models\DocumentType::where('name', $docType)->first();
        if ($docTypeRecord && $docTypeRecord->fee !== null) {
            $newRequest->fee = $docTypeRecord->fee;
        }

        $newRequest->document_type = $docType;
        $newRequest->document_type_id = $docTypeRecord?->id;
        $newRequest->purpose = $purpose;
        $newRequest->documents = [
            [
                'type'         => $docType,
                'purpose'      => $purpose,
                'fee'          => $newRequest->fee,
                'is_generated' => false,
            ]
        ];

        $newRequest->priority = $request->input('urgency', 'Normal');
        $newRequest->status = 'Pending';
        $newRequest->user_id = Auth::id();
        $selectedIdType = $request->input('id_type') ?: (Auth::user()->id_type ?? null);
        $newRequest->id_type = $selectedIdType;
        if (!empty($request->input('id_type')) && Auth::user() && empty(Auth::user()->id_type)) {
            Auth::user()->update(['id_type' => $request->input('id_type')]);
        }
        
        $newRequest->save();

        $attachmentsData = [];
        $metaList = [];
        if ($request->filled('attachment_meta')) {
            $decoded = json_decode($request->input('attachment_meta'), true);
            if (is_array($decoded)) {
                $metaList = $decoded;
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $idx => $file) {
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $path = $file->store('attachments', 'public');
                    
                    $matchedMeta = null;
                    foreach ($metaList as $m) {
                        if (is_array($m) && ($m['name'] ?? '') === $originalName) {
                            $matchedMeta = $m;
                            break;
                        }
                    }
                    if (!$matchedMeta && isset($metaList[$idx])) {
                        $matchedMeta = $metaList[$idx];
                    }

                    $docType = $matchedMeta['doc_type'] ?? 'Supporting Document';
                    $ext = strtolower($file->getClientOriginalExtension());
                    $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'image' : 'document';

                    $attachmentsData[] = [
                        'file_path' => $path,
                        'file_name' => $originalName,
                        'doc_type'  => $docType,
                        'file_type' => $fileType,
                        'file_size' => $file->getSize(),
                    ];
                }
            }
        }

        if (!empty($attachmentsData)) {
            $newRequest->attachments = $attachmentsData;
            $newRequest->save();
        }

        // Automatically record this action in the Activity Logs
        $log = new \App\Models\ActivityLog();
        $log->user_id = Auth::id();
        $log->action = 'Document Request';
        $log->description = 'Requested: ' . $newRequest->document_type;
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
        $latest = DocumentRequest::where('user_id', auth()->id())
            ->latest()
            ->first(['id', 'status', 'tracking_number', 'updated_at']);

        return response()->json([
            'last_update'      => $latest?->updated_at,
            'latest_status'    => $latest?->status,
            'tracking_number'  => $latest?->tracking_number,
        ]);
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

        // Define the fee amount from the database record (if updated by admin)
        $feeAmount = ($docRequest->fee !== null && (float)$docRequest->fee > 0) ? (float)$docRequest->fee : 0;

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