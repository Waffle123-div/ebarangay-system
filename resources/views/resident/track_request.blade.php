@extends('resident.layouts.resident')

@section('content')

<!-- html2canvas library for downloading the Claim Stub as a PNG image -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
    /* ---- System Theme Animated Timeline Styles ---- */
    .timeline-vertical {
        position: relative;
        padding-left: 2.25rem;
        list-style: none;
        margin-bottom: 0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }

    /* Vertical Connecting Line Segments between nodes */
    .timeline-item::after {
        content: '';
        position: absolute;
        left: -1.35rem;
        top: 2rem;
        bottom: -2rem;
        width: 3px;
        background-color: #e2e8f0;
        z-index: 1;
        border-radius: 2px;
        transition: background-color 0.4s ease, box-shadow 0.4s ease;
    }
    .timeline-item:last-child::after {
        display: none;
    }

    /* Active Blue Animated Connecting Line Segment */
    .timeline-item.line-active::after {
        background: linear-gradient(180deg, var(--color-primary, #18365D) 0%, #2B6CB0 100%);
        box-shadow: 0 0 8px rgba(43, 108, 176, 0.35);
        animation: pulseLine 2.5s infinite alternate ease-in-out;
    }

    @keyframes pulseLine {
        0% { opacity: 0.85; filter: drop-shadow(0 0 2px rgba(43, 108, 176, 0.4)); }
        100% { opacity: 1; filter: drop-shadow(0 0 5px rgba(43, 108, 176, 0.7)); }
    }

    /* Timeline Nodes */
    .timeline-icon {
        position: absolute;
        left: -2.25rem;
        top: 0;
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 50%;
        background-color: #ffffff;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        color: #94a3b8;
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* Active Step Node (System Primary Blue) */
    .timeline-icon.active, .timeline-icon.completed-all {
        background: linear-gradient(135deg, var(--color-primary, #18365D) 0%, #2B6CB0 100%);
        border-color: var(--color-primary, #18365D);
        color: #ffffff;
        transform: scale(1.12);
        animation: pulseNode 2.2s infinite ease-in-out;
    }

    @keyframes pulseNode {
        0%, 100% { box-shadow: 0 0 0 4px rgba(24, 54, 93, 0.18), 0 4px 12px rgba(43, 108, 176, 0.35); }
        50% { box-shadow: 0 0 0 7px rgba(43, 108, 176, 0.28), 0 6px 16px rgba(43, 108, 176, 0.48); }
    }

    /* Accordion Custom Styling */
    .accordion-button {
        transition: background-color 0.25s ease, box-shadow 0.25s ease;
    }
    .accordion-button:not(.collapsed) {
        background-color: rgba(24, 54, 93, 0.03);
        color: var(--color-text, #1e293b);
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.08);
    }
    .accordion-button:focus {
        box-shadow: 0 0 0 3px rgba(24, 54, 93, 0.1);
    }

    /* Claim Stub Button (System Primary Blue) */
    .btn-claim-stub {
        background: linear-gradient(135deg, var(--color-primary, #18365D) 0%, #2B6CB0 100%);
        color: #ffffff;
        border: none;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 2px 6px rgba(24, 54, 93, 0.2);
    }
    .btn-claim-stub:hover {
        background: linear-gradient(135deg, #112744 0%, #1d4ed8 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(24, 54, 93, 0.32);
    }
    .btn-claim-stub:active {
        transform: translateY(0);
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

    <div id="trackRequestsListWrapper">
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

                        $docTypeLower = strtolower($req->document_type ?? '');
                        $isBusinessClearance = str_contains($docTypeLower, 'business');
                    @endphp

                    <div class="accordion-item mb-3 border rounded-4 overflow-hidden">
                        
                        <h2 class="accordion-header" id="heading{{ $req->id }}">
                            <button class="accordion-button collapsed px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $req->id }}" aria-expanded="false" aria-controls="collapse{{ $req->id }}">
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

                        <div id="collapse{{ $req->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $req->id }}" data-bs-parent="#requestsAccordion">
                            <div class="accordion-body bg-white p-4 border-top">
                                
                                @if($step == -1)
                                    <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 p-4 text-center mb-0">
                                        <i class="fa-solid fa-circle-xmark fs-2 mb-2"></i>
                                        <h5 class="fw-bold mb-1">Request Rejected</h5>
                                        <p class="small mb-0">Unfortunately, this request has been cancelled or rejected by the barangay staff. Please contact the office for more details.</p>
                                    </div>
                                @else
                                    <div class="row g-4">
                                        
                                        {{-- LEFT COLUMN: REQUEST DETAILS & ACTION BUTTONS --}}
                                        <div class="col-md-6 border-end-md">
                                            <h6 class="fw-bold text-dark mb-4">Request Details</h6>
                                            <div class="bg-light rounded-3 p-3 mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Document Type:</span>
                                                    <span class="fw-bold text-dark small">{{ $req->document_type }}</span>
                                                </div>
                                                @if($req->purpose)
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted small">Purpose:</span>
                                                        <span class="fw-bold text-dark small text-end" style="max-width: 60%;">{{ $req->purpose }}</span>
                                                    </div>
                                                @endif
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Submission Date:</span>
                                                    <span class="fw-bold text-dark small">{{ $req->created_at->format('F d, Y') }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small">Fee Amount:</span>
                                                    <div>
                                                        @if(!$isBusinessClearance)
                                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 small">FREE</span>
                                                        @elseif($req->fee !== null && (float)$req->fee > 0)
                                                            <span class="fw-bold text-success small">₱{{ number_format($req->fee, 2) }}</span>
                                                        @else
                                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 small">TO BE SETTLED</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted small">Current Status:</span>
                                                    <span class="fw-bold text-dark small">{{ $req->status }}</span>
                                                </div>
                                            </div>

                                            {{-- ACTION BUTTONS (CLAIM STUB & VIEW OFFICIAL RECEIPT) --}}
                                            <div class="d-flex flex-column gap-2 mt-3">
                                                {{-- Show Claim Stub ONLY when status is Ready for Pickup ($step == 4). Vanishes when Completed ($step == 5). --}}
                                                @if($step == 4)
                                                    @php
                                                        if (!$isBusinessClearance) {
                                                            $feeTextForStub = 'FREE';
                                                        } elseif ($req->fee !== null && (float)$req->fee > 0) {
                                                            $feeTextForStub = '₱' . number_format($req->fee, 2);
                                                        } else {
                                                            $feeTextForStub = 'To be settled';
                                                        }
                                                    @endphp
                                                    <button type="button" class="btn btn-claim-stub btn-sm rounded-3 w-100 fw-bold py-2 shadow-sm d-inline-flex align-items-center justify-content-center gap-2"
                                                            onclick="openClaimStubModal(
                                                                '{{ $req->tracking_number ?? ('REQ-' . $req->id) }}',
                                                                '{{ addslashes($req->document_type) }}',
                                                                '{{ addslashes(Auth::user()->name) }}',
                                                                '{{ $req->created_at->format('M d, Y') }}',
                                                                '{{ $feeTextForStub }}',
                                                                '{{ addslashes($req->purpose ?? 'N/A') }}'
                                                            )">
                                                        <i class="fa-solid fa-ticket"></i> Claim Stub
                                                    </button>
                                                @endif

                                                {{-- Show Official Receipt ONLY when request status is Completed ($step == 5) --}}
                                                @if($step == 5)
                                                    <a href="{{ route('resident.receipt.view', $req->id) }}" class="btn btn-outline-primary btn-sm rounded-3 w-100 fw-bold py-2">
                                                        <i class="fa-solid fa-receipt me-1"></i> View Official Receipt
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- RIGHT COLUMN: ANIMATED ACTIVITY TIMELINE --}}
                                        <div class="col-md-6 ps-md-4">
                                            <h6 class="fw-bold text-dark mb-4">Activity Timeline</h6>
                                            <ul class="timeline-vertical">
                                                
                                                {{-- STEP 1 --}}
                                                <li class="timeline-item {{ $step > 1 ? 'line-active' : '' }}">
                                                    <div class="timeline-icon {{ $step >= 1 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-check" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold text-dark small mb-1">Request Submitted</div>
                                                    <div class="text-muted" style="font-size: 11px;">Your document request was successfully received.</div>
                                                    <div class="text-muted mt-1" style="font-size: 10px;"><i class="fa-regular fa-clock me-1"></i>{{ $req->created_at->format('M d, Y - h:i A') }}</div>
                                                </li>

                                                {{-- STEP 2 --}}
                                                <li class="timeline-item {{ $step > 2 ? 'line-active' : '' }}">
                                                    <div class="timeline-icon {{ $step >= 2 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-magnifying-glass" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold {{ $step >= 2 ? 'text-dark' : 'text-muted' }} small mb-1">Under Review</div>
                                                    <div class="text-muted" style="font-size: 11px;">Barangay staff are reviewing your details.</div>
                                                </li>

                                                {{-- STEP 3 --}}
                                                <li class="timeline-item {{ $step > 3 ? 'line-active' : '' }}">
                                                    <div class="timeline-icon {{ $step >= 3 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-gear" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold {{ $step >= 3 ? 'text-dark' : 'text-muted' }} small mb-1">Processing</div>
                                                    <div class="text-muted" style="font-size: 11px;">Your document is currently being drafted/printed.</div>
                                                </li>

                                                {{-- STEP 4: READY FOR PICKUP --}}
                                                <li class="timeline-item {{ $step > 4 ? 'line-active' : '' }}">
                                                    <div class="timeline-icon {{ $step >= 4 ? 'active' : '' }}">
                                                        <i class="fa-solid fa-file-signature" style="font-size: 10px;"></i>
                                                    </div>
                                                    <div class="fw-bold {{ $step >= 4 ? 'text-dark' : 'text-muted' }} small mb-1">Ready for Pickup</div>
                                                    <div class="text-muted" style="font-size: 11px;">Your document is ready. Please visit the barangay hall.</div>
                                                </li>

                                                {{-- STEP 5: COMPLETED --}}
                                                <li class="timeline-item">
                                                    <div class="timeline-icon {{ $step == 5 ? 'completed-all' : '' }}">
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

</div>

<!-- ============================================ -->
<!-- CLAIM STUB MODAL                             -->
<!-- ============================================ -->
<div class="modal fade" id="claimStubModal" tabindex="-1" aria-labelledby="claimStubModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-dark fs-6" id="claimStubModalLabel">
                    <i class="fa-solid fa-ticket text-primary me-2"></i>Official Claim Stub
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                
                {{-- THE STUB CARD TO CAPTURE AS AN IMAGE --}}
                <div id="claimStubCard" class="bg-white border rounded-4 p-4 text-dark position-relative shadow-sm" 
                     style="background: #ffffff; border: 2px dashed #cbd5e1 !important;">
                    
                    {{-- Header Logo & Title --}}
                    <div class="text-center pb-3 mb-3 border-bottom">
                        <div class="fw-bold text-uppercase text-primary small mb-0" style="letter-spacing:1px; font-size: 11px;">
                            Republic of the Philippines
                        </div>
                        <div class="fw-bold text-dark" style="font-size: 15px;">BARANGAY COGON EAST</div>
                        <div class="text-muted small" style="font-size: 11px;">Municipality of Carmen, Province of Cebu</div>
                        
                        <div class="badge text-white px-3 py-1.5 rounded-pill fw-bold text-uppercase mt-2" style="font-size: 10px; letter-spacing: 0.5px; background: linear-gradient(135deg, var(--color-primary, #18365D) 0%, #2B6CB0 100%) !important;">
                            Document Claim Ticket
                        </div>
                    </div>

                    {{-- Tracking Barcode / Reference Banner --}}
                    <div class="text-center bg-light p-3 rounded-3 mb-3 border">
                        <div class="text-muted text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.5px;">Tracking Reference No.</div>
                        <div class="fw-bold font-monospace text-primary fs-5" id="stubReqId">REQ-2026-00000</div>
                    </div>

                    {{-- Stub Details --}}
                    <div class="row g-2 text-start" style="font-size: 13px;">
                        <div class="col-5 text-muted font-weight-medium">Resident Name:</div>
                        <div class="col-7 fw-bold text-dark text-end" id="stubResidentName">---</div>

                        <div class="col-5 text-muted font-weight-medium">Document Requested:</div>
                        <div class="col-7 fw-bold text-dark text-end" id="stubDocType">---</div>

                        <div class="col-5 text-muted font-weight-medium">Purpose:</div>
                        <div class="col-7 fw-semibold text-dark text-end" id="stubPurpose">---</div>

                        <div class="col-5 text-muted font-weight-medium">Date Requested:</div>
                        <div class="col-7 fw-semibold text-dark text-end" id="stubDate">---</div>

                        <div class="col-5 text-muted font-weight-medium">Fee Amount:</div>
                        <div class="col-7 fw-bold text-primary text-end" id="stubFee">---</div>
                    </div>

                    {{-- Instructions / Notes --}}
                    <div class="mt-3 pt-3 border-top text-center text-muted" style="font-size: 11px;">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                        Please present this claim stub and a <strong>Valid Government ID</strong> upon claiming your document at the Barangay Office.
                    </div>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex gap-2">
                <button type="button" class="btn btn-light rounded-3 fw-semibold flex-grow-1 py-2" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn btn-claim-stub rounded-3 fw-bold flex-grow-1 py-2 shadow-sm d-inline-flex align-items-center justify-content-center gap-2" id="btnDownloadStub" onclick="downloadClaimStubImage()">
                    <i class="fa-solid fa-download"></i> Download Image
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    // Open Claim Stub Modal and populate field data
    function openClaimStubModal(reqId, docType, residentName, dateReq, fee, purpose) {
        document.getElementById('stubReqId').textContent = reqId;
        document.getElementById('stubDocType').textContent = docType;
        document.getElementById('stubResidentName').textContent = residentName;
        document.getElementById('stubDate').textContent = dateReq;
        document.getElementById('stubFee').textContent = fee;
        document.getElementById('stubPurpose').textContent = purpose;

        const stubModal = new bootstrap.Modal(document.getElementById('claimStubModal'));
        stubModal.show();
    }

    // Capture Claim Stub card and download as PNG image
    function downloadClaimStubImage() {
        const stubElement = document.getElementById('claimStubCard');
        const reqId = document.getElementById('stubReqId').textContent.trim() || 'Claim-Stub';
        const downloadBtn = document.getElementById('btnDownloadStub');

        const originalBtnText = downloadBtn.innerHTML;
        downloadBtn.disabled = true;
        downloadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Generating...';

        html2canvas(stubElement, {
            scale: 3, // High-DPI crisp image export
            useCORS: true,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = `Claim-Stub-${reqId}.png`;
            link.href = canvas.toDataURL('image/png', 1.0);
            link.click();

            downloadBtn.disabled = false;
            downloadBtn.innerHTML = '<i class="fa-solid fa-check text-white me-1"></i> Downloaded!';
            setTimeout(() => {
                downloadBtn.innerHTML = originalBtnText;
            }, 2000);
        }).catch(err => {
            console.error('Claim stub download error:', err);
            alert('Could not generate claim stub image. Please try again.');
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = originalBtnText;
        });
    }

    // Real-time polling script
    document.addEventListener("DOMContentLoaded", function() {
        let currentLastUpdate = "{{ \App\Models\DocumentRequest::where('user_id', auth()->user()->id)->max('updated_at') }}";
        let currentTotalCount = {{ \App\Models\DocumentRequest::where('user_id', auth()->user()->id)->count() }};

        function pollForUpdates() {
            if (document.hidden) return;

            fetch("{{ route('resident.requests.check') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.active_count !== undefined) {
                        const trBadge = document.getElementById('rTrackRequestsBadge');
                        if (trBadge) {
                            trBadge.textContent = data.active_count;
                            if (data.active_count > 0) {
                                trBadge.classList.remove('d-none');
                            } else {
                                trBadge.classList.add('d-none');
                            }
                        }
                    }

                    const countChanged = data.total_count !== undefined && data.total_count !== currentTotalCount;
                    const updateChanged = data.last_update && data.last_update !== currentLastUpdate;

                    if (countChanged || updateChanged) {
                        currentTotalCount = data.total_count;
                        currentLastUpdate = data.last_update;
                        
                        fetch(window.location.href)
                            .then(res => res.text())
                            .then(html => {
                                let parser = new DOMParser();
                                let doc = parser.parseFromString(html, 'text/html');
                                let newWrapper = doc.getElementById('trackRequestsListWrapper');
                                let currentWrapper = document.getElementById('trackRequestsListWrapper');
                                
                                if (newWrapper && currentWrapper) {
                                    currentWrapper.innerHTML = newWrapper.innerHTML;
                                    console.log("Real-time update applied to track requests!");
                                }
                            });
                    }
                })
                .catch(error => console.error("Polling error:", error));
        }

        setInterval(pollForUpdates, 6000);

        // Instant refresh on WebSocket broadcast
        document.addEventListener('resident:realtime-event', function(e) {
            if (e.detail && (e.detail.event === 'request.status_changed' || e.detail.event === 'request.created')) {
                pollForUpdates();
            }
        });
    });
</script>

@endsection