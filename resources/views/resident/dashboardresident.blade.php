@extends('resident.layouts.resident')

@section('content')
<div class="container py-4">
    
    <div class="mb-4">
        @if($isNewUser)
            <h2 class="header-title">Welcome, {{ $firstName }}!</h2>
        @else
            <h2 class="header-title">Welcome back, {{ $firstName }}!</h2>
        @endif
        <p class="header-subtitle">Manage your document requests and track their progress</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- OFFICIAL BARANGAY ANNOUNCEMENT BANNER      --}}
    {{-- ========================================== --}}
    @if(!empty($announcement) && !empty($announcement['message']))
        @php
            $type = $announcement['type'] ?? 'info';
            $config = [
                'info' => [
                    'bg'     => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
                    'border' => '#93c5fd',
                    'color'  => '#1e40af',
                    'badge'  => 'bg-primary text-white',
                    'icon'   => 'fa-solid fa-circle-info',
                    'iconBg' => 'bg-primary text-white',
                    'btn'    => 'btn-primary',
                ],
                'warning' => [
                    'bg'     => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)',
                    'border' => '#fcd34d',
                    'color'  => '#92400e',
                    'badge'  => 'bg-warning text-dark',
                    'icon'   => 'fa-solid fa-triangle-exclamation',
                    'iconBg' => 'bg-warning text-dark',
                    'btn'    => 'btn-warning text-dark',
                ],
                'danger' => [
                    'bg'     => 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)',
                    'border' => '#fca5a5',
                    'color'  => '#991b1b',
                    'badge'  => 'bg-danger text-white',
                    'icon'   => 'fa-solid fa-circle-exclamation',
                    'iconBg' => 'bg-danger text-white',
                    'btn'    => 'btn-danger text-white',
                ],
                'success' => [
                    'bg'     => 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)',
                    'border' => '#86efac',
                    'color'  => '#166534',
                    'badge'  => 'bg-success text-white',
                    'icon'   => 'fa-solid fa-bullhorn',
                    'iconBg' => 'bg-success text-white',
                    'btn'    => 'btn-success text-white',
                ],
            ][$type] ?? [
                'bg'     => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
                'border' => '#93c5fd',
                'color'  => '#1e40af',
                'badge'  => 'bg-primary text-white',
                'icon'   => 'fa-solid fa-circle-info',
                'iconBg' => 'bg-primary text-white',
                'btn'    => 'btn-primary',
            ];
        @endphp

        <div id="barangay-announcement-card" 
             class="card border shadow-sm rounded-4 mb-4 overflow-hidden position-relative"
             style="background: {{ $config['bg'] }}; border-color: {{ $config['border'] }} !important;">
            
            @if($type === 'danger')
                <div style="height: 4px; background: linear-gradient(90deg, #ef4444, #b91c1c); animation: pulse 2s infinite;"></div>
            @endif

            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge {{ $config['badge'] }} px-3 py-1.5 rounded-pill fw-bold text-uppercase shadow-sm" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="{{ $config['icon'] }} me-1"></i>
                            {{ $announcement['category'] ?? 'Barangay Advisory' }}
                        </span>

                        @if($type === 'danger')
                            <span class="badge bg-danger rounded-pill px-2.5 py-1 text-white small fw-bold" style="font-size: 10px; animation: blinker 1.5s linear infinite;">
                                <i class="fa-solid fa-circle-dot me-1"></i> URGENT ALERT
                            </span>
                        @endif

                        @if(!empty($announcement['updated_at']))
                            <span class="small" style="color: {{ $config['color'] }}; opacity: 0.85; font-size: 12px;">
                                <i class="fa-regular fa-clock me-1"></i> {{ \Carbon\Carbon::parse($announcement['updated_at'])->format('M d, Y · h:i A') }}
                            </span>
                        @endif
                    </div>

                    @if(!empty($announcement['is_dismissible']))
                        <button type="button" class="btn-close ms-auto" aria-label="Close" onclick="dismissAnnouncementBanner()" style="font-size: 11px;"></button>
                    @endif
                </div>

                <div class="d-flex align-items-start gap-3 mt-1">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0 {{ $config['iconBg'] }}"
                         style="width: 48px; height: 48px; font-size: 20px;">
                        <i class="{{ $config['icon'] }}"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1" style="color: {{ $config['color'] }}; font-family: var(--font-heading, sans-serif);">
                            {{ $announcement['title'] }}
                        </h5>
                        <p class="mb-0" style="color: {{ $config['color'] }}; opacity: 0.95; font-size: 14.5px; line-height: 1.55; white-space: pre-line;">
                            {{ $announcement['message'] }}
                        </p>

                        @if(!empty($announcement['action_url']))
                            <div class="mt-3">
                                <a href="{{ $announcement['action_url'] }}" 
                                   class="btn {{ $config['btn'] }} btn-sm px-3.5 py-1.5 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5">
                                    <span>{{ $announcement['action_text'] ?: 'Learn More' }}</span>
                                    <i class="fa-solid fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($announcement['is_dismissible']))
            <script>
            (function() {
                const bannerKey = 'brgy_announcement_dismissed_{{ md5($announcement['title'] . ($announcement['message'] ?? '')) }}';
                if (sessionStorage.getItem(bannerKey)) {
                    const card = document.getElementById('barangay-announcement-card');
                    if (card) card.style.display = 'none';
                }
                window.dismissAnnouncementBanner = function() {
                    const card = document.getElementById('barangay-announcement-card');
                    if (card) {
                        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(-10px)';
                        setTimeout(() => card.style.display = 'none', 300);
                    }
                    sessionStorage.setItem(bannerKey, 'true');
                };
            })();
            </script>
        @endif
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <a href="{{ route('resident.request.create') }}" class="text-decoration-none">
                <div class="card card-custom card-hover p-4">
                    <div class="bg-primary rounded-3 d-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-file-circle-plus text-white"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Request Document</h6>
                    <p class="text-muted small mb-0">Submit a new document request</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('resident.request.track') }}" class="text-decoration-none">
                <div class="card card-custom card-hover p-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Track Request</h6>
                    <p class="text-muted small mb-0">Check your request status</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('resident.request.track') }}" class="text-decoration-none">
                <div class="card card-custom card-hover p-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Request History</h6>
                    <p class="text-muted small mb-0">View all your requests</p>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold">Pending Requests</div>
                        <h3 class="fw-bold text-warning mb-0">{{ $pendingCount }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle text-warning"><i class="fa-regular fa-clock"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold">Approved Requests</div>
                        <h3 class="fw-bold text-success mb-0">{{ $approvedCount }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-2 rounded-circle text-success"><i class="fa-regular fa-circle-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-secondary border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold">Total Requests</div>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalCount }}</h3>
                    </div>
                    <div class="bg-light p-2 rounded-circle text-muted"><i class="fa-regular fa-file-lines"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="fw-bold mb-0">Recent Activity</h6>
            <a href="{{ route('resident.activity-logs') }}" class="text-primary text-decoration-none small fw-bold">
                View Full Log <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-borderless align-middle">
                <tbody>
                    @forelse($recentRequests as $request)
                    <tr>
                        <td style="width: 50px;">
                            @if($request->status == 'Approved' || $request->status == 'Completed' || $request->status == 'Ready')
                                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="fa-solid fa-file-lines"></i></div>
                            @elseif($request->status == 'Pending')
                                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="fa-solid fa-file-lines"></i></div>
                            @else
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="fa-solid fa-file-lines"></i></div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $request->document_type }}</div>
                            <div class="text-muted" style="font-size: 11px;">Submitted on {{ $request->created_at->format('M d, Y') }} • ID: {{ $request->tracking_number }}</div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                @if($request->status == 'Completed')
                                    <span class="status-badge status-completed">Completed</span>
                                @elseif($request->status == 'Ready')
                                    <span class="status-badge status-ready">Ready for Pickup</span>
                                @elseif($request->status == 'Approved')
                                    <span class="status-badge status-approved">Approved</span>
                                @elseif($request->status == 'Pending')
                                    <span class="status-badge status-pending">Pending</span>
                                @else
                                    <span class="status-badge status-processing">{{ $request->status }}</span>
                                @endif
                                
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $request->id }}" title="View Receipt">
                                    <i class="fa-solid fa-receipt me-1"></i> Receipt
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            <i class="fa-regular fa-folder-open mb-2 fa-2x text-light"></i>
                            <div>No recent activity found.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODALS LOOP: Generate a receipt modal for each recent request --}}
@foreach($recentRequests as $request)
@php
    $receiptNumber = str_replace('REQ', 'REC', $request->tracking_number);
    $feeAmount = ($request->fee !== null && (float)$request->fee > 0) ? (float)$request->fee : 0;
@endphp

<div class="modal fade" id="receiptModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
    <!-- THE FIX: Restricted max-width to 650px instead of modal-lg so it looks like a real receipt -->
    <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- THE FIX: A proper modal header keeping the buttons neatly aligned inside the modal -->
            <div class="modal-header bg-light border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0">
                    <i class="fa-solid fa-file-invoice text-primary me-2"></i> Document Receipt
                </h6>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" onclick="downloadReceiptAsImage('receiptCard{{ $request->id }}', '{{ $receiptNumber }}')" class="btn btn-primary btn-sm rounded-3 shadow-sm px-3 fw-semibold">
                        <i class="fa-solid fa-download me-1"></i> Save Image
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- THE RECEIPT DESIGN (Wrapped in ID for html2canvas) -->
            <!-- THE FIX: Set background to white so the downloaded image has a solid background -->
            <div id="receiptCard{{ $request->id }}" class="bg-white">
                
                {{-- Header --}}
                <div class="p-4 text-center border-bottom position-relative" style="border-bottom: 2px solid #000 !important; background-color: #f8f9fa;">
                    <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 50px; height: 50px; font-size: 22px;">
                            <i class="fa-solid fa-landmark"></i>
                        </div>
                        <div class="text-start">
                            <div class="text-uppercase text-muted fw-bold small" style="letter-spacing: 1px; font-size: 11px;">Republic of the Philippines</div>
                            <h5 class="fw-bold text-dark mb-0">Barangay Cogon East</h5>
                            <div class="text-muted small">E-Barangay System — Official Transaction Receipt</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 text-start">
                    
                    {{-- Metadata --}}
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">RECEIPT NUMBER</span>
                            <h5 class="fw-bold text-primary mb-0 font-monospace">{{ $receiptNumber }}</h5>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small d-block">DATE & TIME</span>
                            <span class="fw-bold text-dark">{{ $request->created_at->format('F d, Y — h:i A') }}</span>
                        </div>
                    </div>

                    {{-- Resident Info --}}
                    <div class="bg-light rounded-3 p-3 mb-4 border">
                        <h6 class="fw-bold text-dark mb-3 text-uppercase small" style="letter-spacing: 0.5px;">Resident Information</h6>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted small">Full Name:</span>
                                <div class="fw-bold text-dark">
                                    {{ trim($request->first_name . ' ' . $request->middle_initial . ' ' . $request->last_name) ?: auth()->user()->name }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small">Contact Info:</span>
                                <div class="fw-bold text-dark">{{ $request->resident_email ?? $request->contact_number ?? 'N/A' }}</div>
                            </div>
                            <div class="col-sm-6 mt-2">
                                <span class="text-muted small">User ID:</span>
                                <div class="fw-bold text-dark font-monospace">{{ $request->user_id ?? 'N/A' }}</div>
                            </div>
                            <div class="col-sm-6 mt-2">
                                <span class="text-muted small">Barangay:</span>
                                <div class="fw-bold text-dark">Barangay Cogon East</div>
                            </div>
                        </div>
                    </div>

                    {{-- Transaction Details --}}
                    <h6 class="fw-bold text-dark mb-3 text-uppercase small" style="letter-spacing: 0.5px;">Transaction Details</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-2 text-muted small fw-bold">TRACKING NO.</th>
                                    <th class="py-2 text-muted small fw-bold">DOCUMENT TYPE</th>
                                    <th class="py-2 text-muted small fw-bold text-end">FEE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold text-dark font-monospace py-3">{{ $request->tracking_number }}</td>
                                    <td class="py-3">
                                        <div class="fw-bold text-primary">{{ $request->document_type }}</div>
                                        @if(!empty($request->purpose))
                                            <div class="text-muted small" style="font-size: 11px;">Purpose: {{ $request->purpose }}</div>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-dark text-end py-3">
                                        @if($feeAmount > 0)
                                            ₱{{ number_format($feeAmount, 2) }}
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">TO BE SETTLED</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Status --}}
                    <div class="row align-items-center mb-4">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <div class="p-3 rounded-3 border bg-light">
                                <span class="text-muted small d-block mb-1">DOCUMENT STATUS</span>
                                @if($request->status == 'Completed')
                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i> Completed / Claimed</span>
                                @elseif($request->status == 'Ready')
                                    <span class="badge bg-info text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-bell me-1"></i> Ready for Pickup</span>
                                @elseif($request->status == 'Rejected')
                                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-xmark me-1"></i> Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fa-solid fa-clock me-1"></i> {{ $request->status }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <div class="p-3 rounded-3 border bg-light">
                                <span class="text-muted small d-block mb-1">TOTAL AMOUNT</span>
                                <h4 class="fw-bold text-success mb-0">
                                    @if($feeAmount > 0)
                                        ₱{{ number_format($feeAmount, 2) }}
                                    @else
                                        TO BE SETTLED
                                    @endif
                                </h4>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Verification --}}
                    <div class="border-top pt-4 mt-4 text-center">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="text-start">
                                <div class="fw-bold text-dark small"><i class="fa-solid fa-shield-halved text-primary me-1"></i> Verified E-Barangay Transaction</div>
                                <div class="text-muted" style="font-size: 11px;">Generated automatically.</div>
                            </div>
                            <div class="text-end font-monospace text-muted small" style="font-size: 10px;">
                                SYS-VERIFIED: {{ md5($receiptNumber . $request->tracking_number) }}
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Bottom Banner --}}
                <div class="bg-primary text-white text-center py-2 small fw-semibold">
                    Thank you for using the E-Barangay System — Barangay Cogon East
                </div>

            </div>
        </div>
    </div>
</div>
@endforeach

@if(is_null(auth()->user()->security_pin))
    <div class="modal" id="setupPinModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 bg-primary bg-opacity-10 text-primary py-4 px-4 justify-content-center flex-column">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-shield-halved fs-4"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0">Set Up Your Security PIN</h5>
                    <p class="small text-muted mb-0 mt-2 text-center">Create a 6-digit PIN for faster, secure logins to the E-Barangay system.</p>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('resident.pin.setup') }}" method="POST" id="pinSetupForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Enter 6-Digit PIN</label>
                            <input type="password" name="security_pin" class="form-control form-control-lg text-center fw-bold rounded-3 bg-light border-0" 
                                   maxlength="6" inputmode="numeric" pattern="[0-9]*" 
                                   placeholder="● ● ● ● ● ●" style="letter-spacing: 8px;" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Confirm 6-Digit PIN</label>
                            <input type="password" name="security_pin_confirmation" class="form-control form-control-lg text-center fw-bold rounded-3 bg-light border-0" 
                                   maxlength="6" inputmode="numeric" pattern="[0-9]*" 
                                   placeholder="● ● ● ● ● ●" style="letter-spacing: 8px;" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm">
                            <i class="fa-solid fa-lock me-2"></i> Save Secure PIN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var pinModal = new bootstrap.Modal(document.getElementById('setupPinModal'));
            pinModal.show();
        });
    </script>
@endif

<!-- html2canvas library for screenshot capability -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function downloadReceiptAsImage(targetId, receiptNumber) {
    const receiptElement = document.getElementById(targetId);
    
    html2canvas(receiptElement, {
        scale: 2, 
        useCORS: true 
    }).then(canvas => {
        const image = canvas.toDataURL("image/png");
        const link = document.createElement('a');
        link.download = 'Receipt-' + receiptNumber + '.png';
        link.href = image;
        link.click();
    });
}
</script>

@endsection