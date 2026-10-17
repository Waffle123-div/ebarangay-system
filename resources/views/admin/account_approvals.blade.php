@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">

    <style>
        /* Responsive Table & Sticky Actions */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .table-responsive::-webkit-scrollbar { height: 7px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .table-responsive::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }

        .table-approvals {
            width: 100%;
            min-width: 900px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-approvals thead th.col-actions,
        .table-approvals tbody td.col-actions {
            white-space: nowrap;
            text-align: right;
            padding-right: 1.25rem !important;
        }

        /* Mobile Status Pills Scroll & Card Layout */
        @media (max-width: 767.98px) {
            .approvals-nav-pills {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding-bottom: 8px;
                -webkit-overflow-scrolling: touch;
            }
            .approvals-nav-pills .nav-link {
                white-space: nowrap;
                padding: 7px 15px;
                font-size: 13px;
                flex-shrink: 0;
            }
            .mobile-approval-card {
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                margin-bottom: 12px;
                padding: 14px;
            }
        }
    </style>

    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> &rsaquo; <span class="text-dark">Approvals</span></div>
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <h2 class="header-title mb-0">Account Approvals</h2>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill small fw-semibold">
                User Verification
            </span>
        </div>
        <p class="header-subtitle mb-0">Verify resident identity documents and approve accounts for portal access.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-4 mb-4 shadow-sm border-0" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- THE HORIZONTAL TABS (Scrollable on mobile) -->
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap approvals-nav-pills">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 {{ $currentStatus == 'pending_approval' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'pending_approval']) }}">
                <i class="fa-solid fa-user-clock me-1"></i> Pending Approvals 
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'pending_approval' ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark' }}">{{ $tabCounts['pending_approval'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 {{ $currentStatus == 'active' ? 'active fw-bold shadow-sm bg-success border-success text-white' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}">
                <i class="fa-solid fa-user-check me-1"></i> Approved Accounts 
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'active' ? 'bg-white text-success' : 'bg-secondary bg-opacity-25 text-dark' }}">{{ $tabCounts['active'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 {{ $currentStatus == 'rejected' ? 'active fw-bold shadow-sm bg-danger border-danger text-white' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}">
                <i class="fa-solid fa-user-xmark me-1"></i> Rejected Accounts 
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'rejected' ? 'bg-white text-danger' : 'bg-secondary bg-opacity-25 text-dark' }}">{{ $tabCounts['rejected'] }}</span>
            </a>
        </li>
    </ul>

    <!-- FILTER & TOOLBAR -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="fw-semibold text-muted small">
            Showing {{ count($pendingUsers) }} {{ str_replace('_', ' ', $currentStatus) }} record(s)
        </div>
        <form action="{{ route('admin.approvals') }}" method="GET" class="d-flex align-items-center gap-2 m-0 w-100 w-sm-auto justify-content-end">
            <!-- Preserve the current status tab when filtering by ID -->
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            
            <label for="id_type" class="fw-bold text-muted small mb-0 text-nowrap">Filter by ID:</label>
            <select name="id_type" id="id_type" class="form-select form-select-sm shadow-sm border w-auto" onchange="this.form.submit()">
                <option value="">All Documents</option>
                <option value="National ID" {{ request('id_type') == 'National ID' ? 'selected' : '' }}>National ID</option>
                <option value="Voter's ID" {{ request('id_type') == "Voter's ID" ? 'selected' : '' }}>Voter's ID</option>
                <option value="Driver's License" {{ request('id_type') == "Driver's License" ? 'selected' : '' }}>Driver's License</option>
                <option value="Passport" {{ request('id_type') == 'Passport' ? 'selected' : '' }}>Passport</option>
            </select>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        {{-- Desktop Table View (Screens >= 768px) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0 table-approvals text-center">
                <thead class="text-muted small" style="background-color: var(--color-bg);">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-start" style="min-width: 170px;">NAME</th>
                        <th class="py-3 border-0" style="min-width: 180px;">EMAIL</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 140px;">DATE SUBMITTED</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 140px;">ID TYPE</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 130px;">DOCUMENT</th>
                        <th class="pe-4 py-3 border-0 text-end col-actions">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($pendingUsers as $user)
                    <tr>
                        <td class="ps-4 fw-bold text-dark text-start">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2 fw-bold text-uppercase flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">
                                    {{ substr(strip_tags($user->name), 0, 2) }}
                                </div>
                                <div class="text-truncate" style="max-width: 160px;">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="text-muted text-truncate" style="max-width: 180px; font-size: 13px;">{{ $user->email }}</td>
                        <td class="text-nowrap text-muted" style="font-size: 12.5px;">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-nowrap">
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">{{ $user->id_type ?? 'N/A' }}</span>
                        </td>
                        <td class="text-nowrap">
                            @php
                                $docUrl = $user->id_document_url;
                                $selfieUrl = $user->selfie_photo_url;
                                $isPdf = $user->id_front_path && strtolower(pathinfo($user->id_front_path, PATHINFO_EXTENSION)) === 'pdf';
                            @endphp
                            <div class="d-inline-flex align-items-center gap-1.5 flex-wrap justify-content-center">
                                @if($docUrl || $selfieUrl)
                                    <button type="button" 
                                            class="btn btn-sm btn-light border text-primary rounded-2 px-2.5 py-1 fw-semibold shadow-sm" 
                                            style="font-size: 12px;"
                                            onclick="previewApprovalDocument('{{ $docUrl }}', '{{ $selfieUrl }}', '{{ addslashes($user->name) }}', '{{ addslashes($user->id_type ?? 'Resident ID') }}', '{{ $user->id }}', '{{ $currentStatus }}')">
                                        <i class="fa-solid fa-user-shield text-primary me-1"></i> View ID & Face
                                    </button>
                                @else
                                    <span class="text-muted small">No Files</span>
                                @endif

                                @if($selfieUrl)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill" style="font-size: 11px;" title="Live face selfie verified">
                                        <i class="fa-solid fa-camera me-1"></i> Face
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-1 rounded-pill" style="font-size: 10px;" title="No live selfie">
                                        No Face
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="pe-4 text-end col-actions">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                @if($currentStatus == 'pending_approval')
                                    <!-- ONLY SHOW ACTIONS IN THE PENDING TAB -->
                                    <form action="{{ route('admin.approvals.approve', $user->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm rounded-2 shadow-sm px-2.5 py-1" title="Approve">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.approvals.reject', $user->id) }}" method="POST" class="m-0" onsubmit="return confirm('Reject this account?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger btn-sm rounded-2 shadow-sm px-2.5 py-1" title="Reject">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </form>
                                @elseif($currentStatus == 'active')
                                    <span class="status-badge status-approved">Approved</span>
                                @elseif($currentStatus == 'rejected')
                                    <span class="status-badge status-rejected">Rejected</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-check fa-3x mb-2 text-light d-block"></i>
                            <span class="d-block mb-1 fw-bold text-dark">No Accounts Found</span>
                            <span class="small">No {{ str_replace('_', ' ', $currentStatus) }} accounts to review at this time.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (Screens < 768px, matching Request tab) --}}
        <div class="d-md-none p-3" id="approvals-mobile-list">
            @forelse($pendingUsers as $user)
                <div class="mobile-approval-card">
                    {{-- Header: Resident Name + Status --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">
                                {{ substr(strip_tags($user->name), 0, 2) }}
                            </div>
                            <span class="fw-bold text-dark" style="font-size: 14px;">{{ $user->name }}</span>
                        </div>
                        <div>
                            @if($currentStatus == 'pending_approval')
                                <span class="status-badge status-pending-approval" style="font-size: 11px;">Pending</span>
                            @elseif($currentStatus == 'active')
                                <span class="status-badge status-approved" style="font-size: 11px;">Approved</span>
                            @elseif($currentStatus == 'rejected')
                                <span class="status-badge status-rejected" style="font-size: 11px;">Rejected</span>
                            @endif
                        </div>
                    </div>

                    {{-- Details --}}
                    <div class="my-2">
                        <div class="text-muted small mb-1"><i class="fa-regular fa-envelope me-1"></i> {{ $user->email }}</div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-muted small mt-2">
                            <span><i class="fa-regular fa-calendar me-1"></i> Submitted: {{ $user->created_at->format('M d, Y') }}</span>
                            <span class="badge bg-light text-dark border px-2 py-1"><i class="fa-solid fa-id-card me-1"></i> {{ $user->id_type ?? 'No ID Type' }}</span>
                        </div>
                    </div>

                    {{-- Document & Face Link --}}
                    <div class="my-2">
                        @php
                            $docUrl = $user->id_document_url;
                            $selfieUrl = $user->selfie_photo_url;
                            $isPdf = $user->id_front_path && strtolower(pathinfo($user->id_front_path, PATHINFO_EXTENSION)) === 'pdf';
                        @endphp
                        @if($docUrl || $selfieUrl)
                            <div class="d-flex gap-2 align-items-center">
                                <button type="button" 
                                        class="btn btn-sm btn-light border text-primary flex-fill rounded-3 py-1.5 fw-semibold shadow-sm"
                                        onclick="previewApprovalDocument('{{ $docUrl }}', '{{ $selfieUrl }}', '{{ addslashes($user->name) }}', '{{ addslashes($user->id_type ?? 'Resident ID') }}', '{{ $user->id }}', '{{ $currentStatus }}')">
                                    <i class="fa-solid fa-user-shield me-1"></i> View ID & Face
                                </button>
                                @if($selfieUrl)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-2 px-2.5 rounded-3" title="Live Face Photo Available">
                                        <i class="fa-solid fa-camera"></i>
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="text-muted small fst-italic py-1 text-center bg-light rounded-3">No ID Uploaded</div>
                        @endif
                    </div>

                    {{-- Actions for Mobile --}}
                    @if($currentStatus == 'pending_approval')
                        <div class="d-flex gap-2 pt-2 border-top mt-2">
                            <form action="{{ route('admin.approvals.approve', $user->id) }}" method="POST" class="m-0 flex-fill">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm w-100 rounded-3 shadow-sm py-2 fw-semibold">
                                    <i class="fa-solid fa-check me-1"></i> Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.approvals.reject', $user->id) }}" method="POST" class="m-0 flex-fill" onsubmit="return confirm('Reject this account?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm w-100 rounded-3 shadow-sm py-2 fw-semibold">
                                    <i class="fa-solid fa-xmark me-1"></i> Reject
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-user-check fa-3x mb-2 text-light d-block"></i>
                    <h6 class="fw-bold text-dark">No Accounts Found</h6>
                    <div class="small">No {{ str_replace('_', ' ', $currentStatus) }} accounts to review at this time.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Document & Face Verification Modal (Side-by-Side Comparison) --}}
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-labelledby="docPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-white">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-user-shield fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="docPreviewName">Resident Verification</h5>
                        <span class="text-muted small" id="docPreviewType">Biometric & Identity Cross-Check</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="docPreviewExternalLink" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open ID in New Tab
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-3 small py-2.5 px-3 mb-3 d-flex align-items-center gap-2 text-primary">
                    <i class="fa-solid fa-shield-halved fs-5 flex-shrink-0"></i>
                    <span><strong>Facial Verification Cross-Check:</strong> Compare the face on the resident's government-issued ID (Left) against their live camera selfie (Right) before approving.</span>
                </div>

                <div class="row g-3">
                    {{-- Left Panel: Government ID --}}
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <span class="fw-bold small text-dark"><i class="fa-solid fa-id-card text-primary me-1"></i> Government-Issued ID</span>
                                <span class="badge bg-light text-secondary border px-2 py-0.5" id="docPreviewBadgeId">Valid ID</span>
                            </div>
                            <div class="card-body p-2 d-flex align-items-center justify-content-center bg-dark bg-opacity-10" style="min-height: 380px; max-height: 55vh; overflow: auto;">
                                <div id="docPreviewImgWrapper" class="w-100 text-center">
                                    <img id="docPreviewImg" src="" alt="ID Document Preview" class="img-fluid rounded-2 shadow-sm" style="max-height: 52vh; object-fit: contain;">
                                </div>
                                <div id="docPreviewPdfWrapper" class="w-100 d-none" style="height: 52vh;">
                                    <iframe id="docPreviewPdf" src="" class="w-100 h-100 border-0 rounded-2"></iframe>
                                </div>
                                <div id="docPreviewNoId" class="text-muted small d-none py-5">
                                    <i class="fa-solid fa-image-slash fa-2x mb-2 d-block"></i> No ID document available
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Panel: Live Facial Verification Photo --}}
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                                <span class="fw-bold small text-dark"><i class="fa-solid fa-camera text-success me-1"></i> Live Facial Selfie</span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5" id="selfieBadge">
                                    <i class="fa-solid fa-circle-check me-1"></i> Live Capture
                                </span>
                            </div>
                            <div class="card-body p-2 d-flex align-items-center justify-content-center bg-dark bg-opacity-10" style="min-height: 380px; max-height: 55vh; overflow: auto;">
                                <div id="selfiePreviewWrapper" class="w-100 text-center">
                                    <img id="selfiePreviewImg" src="" alt="Live Selfie Preview" class="img-fluid rounded-2 shadow-sm" style="max-height: 52vh; object-fit: contain;">
                                </div>
                                <div id="selfiePreviewNone" class="text-muted small d-none py-5">
                                    <i class="fa-solid fa-user-slash fa-2x mb-2 d-block text-secondary"></i>
                                    <span>No live selfie captured for this account</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top py-3 px-4 bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small"><i class="fa-solid fa-circle-info me-1 text-primary"></i> Confirm facial likeness to maintain registry security.</span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-light border btn-sm rounded-3 px-3 fw-semibold" data-bs-dismiss="modal">Close</button>
                    <div id="modalActionButtons" class="d-inline-flex gap-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewApprovalDocument(docUrl, selfieUrl, name, idType, userId, status) {
        const isPdf = docUrl && docUrl.toLowerCase().split('?')[0].endsWith('.pdf');
        const nameEl = document.getElementById('docPreviewName');
        const typeEl = document.getElementById('docPreviewType');
        const badgeIdEl = document.getElementById('docPreviewBadgeId');
        const externalLink = document.getElementById('docPreviewExternalLink');
        const imgWrapper = document.getElementById('docPreviewImgWrapper');
        const imgEl = document.getElementById('docPreviewImg');
        const pdfWrapper = document.getElementById('docPreviewPdfWrapper');
        const pdfEl = document.getElementById('docPreviewPdf');
        const noIdEl = document.getElementById('docPreviewNoId');

        const selfieImg = document.getElementById('selfiePreviewImg');
        const selfieWrapper = document.getElementById('selfiePreviewWrapper');
        const selfieNone = document.getElementById('selfiePreviewNone');
        const selfieBadge = document.getElementById('selfieBadge');
        const actionButtons = document.getElementById('modalActionButtons');

        if (nameEl) nameEl.textContent = name || 'Resident Identity Verification';
        if (typeEl) typeEl.textContent = (idType || 'Resident') + ' — Identity Cross-Check';
        if (badgeIdEl) badgeIdEl.textContent = idType || 'Valid ID';
        if (externalLink) {
            if (docUrl) {
                externalLink.href = docUrl;
                externalLink.classList.remove('d-none');
            } else {
                externalLink.classList.add('d-none');
            }
        }

        // Handle ID Document Display
        if (docUrl) {
            if (noIdEl) noIdEl.classList.add('d-none');
            if (isPdf) {
                if (imgWrapper) imgWrapper.classList.add('d-none');
                if (pdfWrapper) pdfWrapper.classList.remove('d-none');
                if (pdfEl) pdfEl.src = docUrl;
            } else {
                if (pdfWrapper) pdfWrapper.classList.add('d-none');
                if (imgWrapper) imgWrapper.classList.remove('d-none');
                if (imgEl) imgEl.src = docUrl;
            }
        } else {
            if (imgWrapper) imgWrapper.classList.add('d-none');
            if (pdfWrapper) pdfWrapper.classList.add('d-none');
            if (noIdEl) noIdEl.classList.remove('d-none');
        }

        // Handle Selfie Display
        if (selfieUrl) {
            if (selfieNone) selfieNone.classList.add('d-none');
            if (selfieWrapper) selfieWrapper.classList.remove('d-none');
            if (selfieImg) selfieImg.src = selfieUrl;
            if (selfieBadge) {
                selfieBadge.className = 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5';
                selfieBadge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Live Capture';
            }
        } else {
            if (selfieWrapper) selfieWrapper.classList.add('d-none');
            if (selfieNone) selfieNone.classList.remove('d-none');
            if (selfieBadge) {
                selfieBadge.className = 'badge bg-secondary bg-opacity-10 text-secondary border px-2 py-0.5';
                selfieBadge.textContent = 'No Live Selfie';
            }
        }

        // Handle Quick Action Buttons in Modal
        if (actionButtons) {
            if (status === 'pending_approval' && userId) {
                actionButtons.innerHTML = `
                    <form action="/admin/account-approvals/${userId}/reject" method="POST" class="m-0" onsubmit="return confirm('Reject this account?');">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'}">
                        <input type="hidden" name="_method" value="PATCH">
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 px-3 fw-semibold">
                            <i class="fa-solid fa-xmark me-1"></i> Reject
                        </button>
                    </form>
                    <form action="/admin/account-approvals/${userId}/approve" method="POST" class="m-0">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'}">
                        <input type="hidden" name="_method" value="PATCH">
                        <button type="submit" class="btn btn-success btn-sm rounded-3 px-3 fw-semibold shadow-sm">
                            <i class="fa-solid fa-check me-1"></i> Approve Resident
                        </button>
                    </form>
                `;
            } else {
                actionButtons.innerHTML = '';
            }
        }

        const modalEl = document.getElementById('docPreviewModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
</script>
@endsection