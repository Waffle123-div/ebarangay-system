@extends('resident.layouts.resident')

@section('content')

<style>
    /* Strict override to achieve a purely static appearance */
    * {
        transition: none !important;
        animation: none !important;
    }

    /* Clean, Minimalist Vertical Timeline */
    .timeline-vertical {
        position: relative;
        padding-left: 2rem;
        list-style: none;
        margin-bottom: 0;
    }
    .timeline-vertical::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-icon {
        position: absolute;
        left: -2rem;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        background-color: #f8f9fa;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        color: #adb5bd;
    }
    .timeline-icon.active {
        background-color: var(--color-status-approved);
        border-color: var(--color-status-approved);
        color: white;
    }
    .timeline-icon.danger {
        background-color: var(--color-status-rejected);
        border-color: var(--color-status-rejected);
        color: white;
    }
    .accordion-button:not(.collapsed) {
        background-color: var(--color-bg);
        color: var(--color-text);
        box-shadow: none;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
</style>

<div class="container-fluid px-4 py-4">
    <div class="mb-5">
        <a href="{{ route('resident.dashboard') }}" class="text-muted text-decoration-none small fw-bold mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
        </a>
        <h3 class="fw-bold text-dark mb-1">Track Your Requests</h3>
        <p class="text-muted small">Monitor the progress of all your document requests</p>
    </div>

    @if($allRequests->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <div class="text-muted mb-3"><i class="fa-solid fa-folder-open fs-1"></i></div>
            <h5 class="fw-bold text-dark">No Active Requests</h5>
            <p class="text-muted small mb-0">You haven't submitted any document requests yet.</p>
            <div class="mt-4">
                <a href="{{ route('resident.request.create') }}" class="btn btn-primary rounded-3 px-4">Request a Document</a>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="accordion" id="requestsAccordion">
                
                @foreach($allRequests as $index => $req)
                    @php
                        $status = $req->status;
                        $step = 1; // Default is Submitted/Pending
                        
                        if($status == 'Under Review') $step = 2;
                        elseif($status == 'Approved' || $status == 'Processing') $step = 3;
                        elseif($status == 'Ready') $step = 4;
                        elseif($status == 'Completed') $step = 5;
                        elseif($status == 'Rejected' || $status == 'Cancelled') $step = -1;
                    @endphp

                    <div class="accordion-item mb-3 border rounded-4 overflow-hidden">
                        
                        <h2 class="accordion-header" id="heading{{ $req->id }}">
                            <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }} px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $req->id }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $req->id }}">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark mb-1" style="font-size: 15px;">{{ $req->document_type }}</div>
                                            <div class="text-muted" style="font-size: 12px;">ID: {{ $req->tracking_number ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="text-muted d-none d-md-block" style="font-size: 12px;">
                                            {{ $req->created_at->format('M d, Y') }}
                                        </div>
                                        <div>
                                            @if($step == -1)
                                                <span class="status-badge status-rejected">Rejected</span>
                                            @elseif($step == 5)
                                                <span class="status-badge status-completed">Completed</span>
                                            @elseif($step == 4)
                                                <span class="status-badge status-ready">Ready for Pickup</span>
                                            @elseif($step == 1)
                                                <span class="status-badge status-pending">Pending</span>
                                            @else
                                                <span class="status-badge status-processing">Processing</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </h2>

                        <div id="collapse{{ $req->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $req->id }}" data-bs-parent="#requestsAccordion">
                            <div class="accordion-body bg-white p-4 border-top">
                                
                                @if($step == -1)
                                    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 p-4 text-center mb-0">
                                        <i class="fa-solid fa-circle-xmark fs-2 mb-2"></i>
                                        <h5 class="fw-bold mb-1">Request Rejected</h5>
                                        <p class="small mb-0">Unfortunately, this request has been cancelled or rejected by the barangay staff. Please contact the office for more details.</p>
                                    </div>
                                @else
                                    <div class="row g-4">
                                        
                                        <div class="col-md-6 border-end-md">
                                            <h6 class="fw-bold text-dark mb-4">Request Details</h6>
                                            <div class="bg-light rounded-3 p-3 mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Document Type:</span>
                                                    <span class="fw-bold text-dark small">{{ $req->document_type }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Submission Date:</span>
                                                    <span class="fw-bold text-dark small">{{ $req->created_at->format('F d, Y') }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted small">Current Status:</span>
                                                    <span class="fw-bold text-dark small">{{ $req->status }}</span>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <a href="{{ route('resident.receipt.view', $req->id) }}" class="btn btn-outline-primary btn-sm rounded-3 w-100 fw-bold">
                                                    <i class="fa-solid fa-receipt me-1"></i> View Official Receipt
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-md-6 ps-md-4">
                                            <h6 class="fw-bold text-dark mb-4">Activity Timeline</h6>
                                            <ul class="timeline-vertical">
                                                
                                                <li class="timeline-item">
                                                    <div class="timeline-icon {{ $step >= 1 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-check" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold text-dark small mb-1">Request Submitted</div>
                                                    <div class="text-muted" style="font-size: 11px;">Your document request was successfully received.</div>
                                                    <div class="text-muted mt-1" style="font-size: 10px;"><i class="fa-regular fa-clock me-1"></i>{{ $req->created_at->format('M d, Y - h:i A') }}</div>
                                                </li>

                                                <li class="timeline-item">
                                                    <div class="timeline-icon {{ $step >= 2 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-magnifying-glass" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold {{ $step >= 2 ? 'text-dark' : 'text-muted' }} small mb-1">Under Review</div>
                                                    <div class="text-muted" style="font-size: 11px;">Barangay staff are reviewing your details.</div>
                                                </li>

                                                <li class="timeline-item">
                                                    <div class="timeline-icon {{ $step >= 3 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-gear" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold {{ $step >= 3 ? 'text-dark' : 'text-muted' }} small mb-1">Processing</div>
                                                    <div class="text-muted" style="font-size: 11px;">Your document is currently being drafted/printed.</div>
                                                </li>

                                                <li class="timeline-item">
                                                    <div class="timeline-icon {{ $step >= 4 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-file-signature" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold {{ $step >= 4 ? 'text-dark' : 'text-muted' }} small mb-1">Ready for Pickup</div>
                                                    <div class="text-muted" style="font-size: 11px;">Your document is ready. Please visit the barangay hall.</div>
                                                </li>

                                                <li class="timeline-item">
                                                    <div class="timeline-icon {{ $step == 5 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-check-double" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold {{ $step == 5 ? 'text-dark' : 'text-muted' }} small mb-1">Completed</div>
                                                    <div class="text-muted" style="font-size: 11px;">Document has been claimed by the resident.</div>
                                                </li>
                                                
                                            </ul>
                                        </div>

                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    @endif

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Store the absolute newest timestamp when the page first loads
        let currentLastUpdate = "{{ \App\Models\DocumentRequest::where('resident_email', auth()->user()->email)->max('updated_at') }}";

        function pollForUpdates() {
            // SAFETY CHECK: If the user is on another browser tab, pause polling to save server resources!
            if (document.hidden) return;

            // Ping our new route to check the latest timestamp
            fetch("{{ route('resident.requests.check') }}")
                .then(response => response.json())
                .then(data => {
                    // If the server's timestamp is newer than our local timestamp... WE HAVE AN UPDATE!
                    if (data.last_update && data.last_update !== currentLastUpdate) {
                        currentLastUpdate = data.last_update; // Sync our tracker
                        
                        // Silently fetch this exact page again in the background
                        fetch(window.location.href)
                            .then(res => res.text())
                            .then(html => {
                                // Extract ONLY the updated Accordion HTML and swap it instantly
                                let parser = new DOMParser();
                                let doc = parser.parseFromString(html, 'text/html');
                                let newAccordion = doc.getElementById('requestsAccordion');
                                let currentAccordion = document.getElementById('requestsAccordion');
                                
                                if (newAccordion && currentAccordion) {
                                    currentAccordion.innerHTML = newAccordion.innerHTML;
                                    console.log("Real-time update applied!");
                                }
                            });
                    }
                })
                .catch(error => console.error("Polling error:", error));
        }

        // Start polling every 10 seconds (10000 milliseconds)
        setInterval(pollForUpdates, 10000);
    });
</script>

@endsection