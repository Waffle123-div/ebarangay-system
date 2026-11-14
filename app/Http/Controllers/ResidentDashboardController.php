<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\BarangaySetting;
use App\Models\CommunityConcern;
use App\Services\StoredFileService;
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

        // Match documents using the unique USER_ID
        $pendingCount = DocumentRequest::where('user_id', $user->id)->where('status', 'Pending')->count();
        $approvedCount = DocumentRequest::where('user_id', $user->id)->where('status', 'Approved')->count();
        $totalCount = DocumentRequest::where('user_id', $user->id)->count();

        // Fetch recent requests using USER_ID
        $recentRequests = DocumentRequest::where('user_id', $user->id)
                                         ->orderBy('created_at', 'desc')
                                         ->take(5)
                                         ->get();

        // WELCOME LOGIC: Grab their first name, and check if they are "New"
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

    public function getNotifications()
    {
        $user = auth()->user();
        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->notifications()->latest()->take(15)->get()->map(function ($n) {
            $data = is_array($n->data) ? $n->data : [];

            return [
                'id'              => $n->id,
                'document_type'   => $data['document_type'] ?? ($data['title'] ?? 'Document Update'),
                'status'          => $data['status'] ?? '',
                'tracking_number' => $data['tracking_number'] ?? ($data['concern_id'] ?? 'N/A'),
                'message'         => $data['message'] ?? 'The status of your document has been updated.',
                'link'            => $data['link'] ?? route('resident.request.track', [], false),
                'read'            => !is_null($n->read_at),
                'created_at'      => $n->created_at ? $n->created_at->diffForHumans() : '',
            ];
        });

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
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

        // Extract single document from documents_json if present
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
                    $path = StoredFileService::storeUploadedFile($file, 'attachments', 'public');
                    
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

        // Automatically record action in Activity Logs
        $log = new \App\Models\ActivityLog();
        $log->user_id = Auth::id();
        $log->action = 'Document Request';
        $log->description = 'Requested: ' . $newRequest->document_type;
        $log->ip_address = $request->ip();
        $log->user_agent = $request->userAgent();
        $log->save();

        // Broadcast real-time event to Admin WebSocket
        \App\Services\WebSocketService::broadcast('request.created', [
            'id'              => $newRequest->id,
            'tracking_number' => $newRequest->tracking_number,
            'status'          => $newRequest->status,
            'applicant'       => $newRequest->first_name . ' ' . $newRequest->last_name,
            'document_type'   => $newRequest->document_type,
            'message'         => "New Request #{$newRequest->tracking_number} ({$newRequest->document_type}) submitted by {$newRequest->first_name} {$newRequest->last_name}.",
        ]);

        // Redirect directly to the Track Request page instead of the Dashboard
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Document request submitted successfully!',
                'redirect' => route('resident.request.track')
            ]);
        }

        return redirect()->route('resident.request.track')->with('success', 'Document request submitted successfully!');
    }

    public function trackRequest()
    {
        // THE BOUNCER
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        // Fetch ALL requests by USER_ID
        $allRequests = \App\Models\DocumentRequest::where('user_id', auth()->id())
                    ->latest()
                    ->get();

        // The single most recent request goes to the big timeline tracker
        $myRequest = $allRequests->first();

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

        $latest = DocumentRequest::where('user_id', auth()->id())
            ->latest()
            ->first(['id', 'status', 'tracking_number', 'updated_at']);

        $totalCount = DocumentRequest::where('user_id', auth()->id())->count();

        $activeCount = DocumentRequest::where('user_id', auth()->id())
            ->whereNotIn('status', ['Completed', 'Rejected', 'Cancelled'])
            ->count();

        return response()->json([
            'last_update'      => $latest?->updated_at,
            'latest_status'    => $latest?->status,
            'tracking_number'  => $latest?->tracking_number,
            'active_count'     => $activeCount,
            'total_count'      => $totalCount,
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

        // Fetch unique action types for filter
        $actionTypes = \App\Models\ActivityLog::where('user_id', Auth::id())
                                              ->select('action')
                                              ->distinct()
                                              ->pluck('action');

        $query = \App\Models\ActivityLog::where('user_id', Auth::id());

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->paginate(10)->withQueryString();

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

        if ((string)$docRequest->user_id !== (string)Auth::id()) {
            abort(403, 'Unauthorized access to this receipt.');
        }

        $receiptNumber = str_replace('REQ', 'REC', $docRequest->tracking_number);
        $feeAmount = ($docRequest->fee !== null && (float)$docRequest->fee > 0) ? (float)$docRequest->fee : 0;

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
        $residentAddress = trim("{$authUser->house_number} {$authUser->street}, {$authUser->barangay_address}");

        $categories = [
            'Infrastructure & Roads',
            'Sanitation & Garbage',
            'Safety & Security',
            'Noise & Disturbances',
            'Others'
        ];

        $concerns = \App\Models\CommunityConcern::where('user_id', Auth::id())
                                                ->latest()
                                                ->get();

        return view('resident.community_concerns', compact('concerns', 'authUser', 'categories', 'residentAddress'));
    }

    /**
     * Save a new community concern submitted by the resident.
     */
    public function storeConcern(Request $request)
    {
        if (trim(strtolower(Auth::user()->status)) !== 'active') {
            return redirect()->route('resident.pending');
        }

        $request->validate([
            'category' => 'required|string',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();
        $concern = new \App\Models\CommunityConcern();
        
        $concern->concern_id = 'CON-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $concern->user_id = $user->id;
        $concern->resident_name = $user->name;
        $concern->resident_email = $user->email;
        $concern->contact_number = $user->contact_number ?? 'N/A';
        $concern->address = trim("{$user->house_number} {$user->street}, {$user->barangay_address}");

        $concern->category = $request->input('category');
        $concern->description = $request->input('description');
        $concern->title = $request->input('title', $request->input('category') . ' Report'); 
        $concern->location = $request->input('location', 'Not specified');
        $concern->incident_date = $request->input('incident_date', \Carbon\Carbon::now());
        $concern->status = 'Pending';
        
        if ($request->hasFile('photo')) {
            $path = StoredFileService::storeUploadedFile($request->file('photo'), 'concerns', 'public');
            $concern->photo_path = $path; 
        }

        $concern->save();

        $log = new \App\Models\ActivityLog();
        $log->user_id = Auth::id();
        $log->action = 'Submitted Concern';
        $log->description = 'Reported an issue: ' . $request->input('category');
        $log->ip_address = $request->ip();
        $log->user_agent = $request->userAgent();
        $log->save();

        return back()->with('success', 'Your community concern has been submitted successfully!');
    }

    /**
     * Stream the authenticated resident's own community concern attachment without relying on public/storage symlinks.
     */
    public function showConcernPhoto(CommunityConcern $concern, ?string $filename = null, StoredFileService $storedFileService = null)
    {
        if ((int) $concern->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $storedFileService = $storedFileService ?: app(StoredFileService::class);

        return $storedFileService->serve(
            $concern->photo_path,
            ['concerns'],
            'Concern photo not found.'
        );
    }
}