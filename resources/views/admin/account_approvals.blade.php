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

        /* Clickable Selection Rows and Cards */
        .approval-row-clickable {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .approval-row-clickable:hover {
            background-color: #f8fafc !important;
        }
        html[data-theme="dark"] .approval-row-clickable:hover,
        html[data-bs-theme="dark"] .approval-row-clickable:hover {
            background-color: rgba(59, 130, 246, 0.08) !important;
        }

        /* Dark Mode for Verification Modal */
        html[data-theme="dark"] #docPreviewModal .modal-content,
        html[data-bs-theme="dark"] #docPreviewModal .modal-content {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border: 1px solid #1e293b !important;
        }
        html[data-theme="dark"] #docPreviewModal .modal-header,
        html[data-bs-theme="dark"] #docPreviewModal .modal-header,
        html[data-theme="dark"] #docPreviewModal .modal-footer,
        html[data-bs-theme="dark"] #docPreviewModal .modal-footer {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #docPreviewModal .modal-body,
        html[data-bs-theme="dark"] #docPreviewModal .modal-body {
            background-color: #0b1120 !important;
        }
        html[data-theme="dark"] #docPreviewModal .card,
        html[data-bs-theme="dark"] #docPreviewModal .card {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #docPreviewModal .card-header,
        html[data-bs-theme="dark"] #docPreviewModal .card-header {
            background-color: #182238 !important;
            border-color: #1e293b !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] #docPreviewModal .text-dark,
        html[data-bs-theme="dark"] #docPreviewModal .text-dark {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #docPreviewModal .text-muted,
        html[data-bs-theme="dark"] #docPreviewModal .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] #docPreviewModal .btn-close,
        html[data-bs-theme="dark"] #docPreviewModal .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
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

        html[data-bs-theme="dark"] .mobile-approval-card,
        html[data-theme="dark"] .mobile-approval-card {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
        }

        html[data-bs-theme="dark"] #bulkActionsBar,
        html[data-theme="dark"] #bulkActionsBar {
            background-color: rgba(37, 99, 235, 0.15) !important;
            border-color: #334155 !important;
        }
        html[data-bs-theme="dark"] #deselectAllBtn,
        html[data-theme="dark"] #deselectAllBtn {
            background: #1e293b !important;
            color: #cbd5e1 !important;
            border-color: #475569 !important;
        }
    </style>

    <div class="mb-4">
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
            Showing {{ count($pendingUsers) }} {{ str_replace('_', ' ',$currentStatus) }} record(s)
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
        {{-- Bulk Selection Bar --}}
        <div id="bulkActionsBar" class="d-none bg-primary bg-opacity-10 border-bottom px-3 px-md-4 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold" id="selectedCountBadge">0 Selected</span>
                <span class="text-muted small d-none d-sm-inline">Select multiple resident accounts to decide whether to approve or reject.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold bg-white" id="deselectAllBtn">
                    <i class="fa-solid fa-xmark me-1"></i> Deselect
                </button>
                <button type="button" class="btn btn-sm btn-success rounded-3 px-3 fw-semibold shadow-sm" id="bulkApproveBtn">
                    <i class="fa-solid fa-check me-1"></i> Approve Selected (<span id="bulkApproveCount">0</span>)
                </button>
                <button type="button" class="btn btn-sm btn-danger rounded-3 px-3 fw-semibold shadow-sm" id="bulkRejectBtn">
                    <i class="fa-solid fa-xmark me-1"></i> Reject Selected (<span id="bulkRejectCount">0</span>)
                </button>
            </div>
        </div>

        {{-- Desktop Table View (Screens >= 768px) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0 table-approvals text-center">
                <thead class="text-muted small" style="background-color: var(--color-bg);">
                    <tr>
                        <th class="ps-3 py-3 border-0 text-center" style="width: 42px;">
                            <input class="form-check-input" type="checkbox" id="selectAllApprovals" title="Select All">
                        </th>
                        <th class="ps-2 py-3 border-0 text-start" style="min-width: 170px;">NAME</th>
                        <th class="py-3 border-0 text-start" style="min-width: 180px;">EMAIL</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 140px;">DATE SUBMITTED</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 150px;">ID TYPE</th>
                        <th class="py-3 border-0 text-center text-nowrap" style="width: 130px;">BIOMETRICS</th>
                        <th class="pe-4 py-3 border-0 text-end text-nowrap" style="width: 120px;">STATUS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($pendingUsers as $user)
                    @php
                        $docUrl = $user->id_document_url;
                        $rawSelfie = trim($user->selfie_photo ?? '');
                        $selfieUrl = null;
                        if ($rawSelfie !== '') {
                            if (str_starts_with($rawSelfie, 'data:image/') || filter_var($rawSelfie, FILTER_VALIDATE_URL)) {
                                $selfieUrl = $rawSelfie;
                            } else {
                                $selfieUrl = route('admin.resident.selfie', basename($rawSelfie));
                            }
                        }
                    @endphp
                    <tr id="approval-row-{{ $user->id }}"
                        class="approval-row-clickable"
                        role="button"
                        tabindex="0"
                        title="Click to View ID & face verification for {{ $user->name }}"
                        data-id="{{ $user->id }}"
                        data-name="{{ $user->name }}"
                        data-id-type="{{ $user->id_type ?? 'Resident ID' }}"
                        data-doc-url="{{ $docUrl ?? '' }}"
                        data-selfie-url="{{ $selfieUrl ?? '' }}"
                        data-status="{{ $currentStatus }}">
                        <td class="ps-3 text-center" style="width: 42px;" onclick="event.stopPropagation()">
                            <input class="form-check-input approval-select-checkbox" type="checkbox" value="{{ $user->id }}" data-name="{{ $user->name }}" onclick="event.stopPropagation()">
                        </td>
                        <td class="ps-2 fw-bold text-dark text-start">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2 fw-bold text-uppercase flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">
                                    {{ substr(strip_tags($user->name), 0, 2) }}
                                </div>
                                <div class="text-truncate" style="max-width: 160px;">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="text-muted text-start text-truncate" style="max-width: 180px; font-size: 13px;">{{ $user->email }}</td>
                        <td class="text-nowrap text-muted" style="font-size: 12.5px;">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-nowrap">
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">{{ $user->id_type ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center text-nowrap">
                            @if($selfieUrl)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill" style="font-size: 11px;" title="Live face selfie verified">
                                    <i class="fa-solid fa-camera me-1"></i> Face
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-1 rounded-pill" style="font-size: 10px;" title="No live selfie">
                                    No Face
                                </span>
                            @endif
                        </td>
                        <td class="pe-4 text-end text-nowrap">
                            @if($currentStatus == 'pending_approval')
                                <span class="status-badge status-pending-approval" style="font-size: 11px;">Pending</span>
                            @elseif($currentStatus == 'active')
                                <span class="status-badge status-approved" style="font-size: 11px;">Approved</span>
                            @elseif($currentStatus == 'rejected')
                                <span class="status-badge status-rejected" style="font-size: 11px;">Rejected</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-check fa-3x mb-2 text-light d-block"></i>
                            <span class="d-block mb-1 fw-bold text-dark">No Accounts Found</span>
                            <span class="small">No {{ str_replace('_', ' ', $currentStatus) }} accounts to review at this time.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (Screens < 768px) --}}
        <div class="d-md-none p-3" id="approvals-mobile-list">
            @forelse($pendingUsers as $user)
                @php
                    $docUrl = $user->id_document_url;
                    $rawSelfie = trim($user->selfie_photo ?? '');
                    $selfieUrl = null;
                    if ($rawSelfie !== '') {
                        if (str_starts_with($rawSelfie, 'data:image/') || filter_var($rawSelfie, FILTER_VALIDATE_URL)) {
                            $selfieUrl = $rawSelfie;
                        } else {
                            $selfieUrl = route('admin.resident.selfie', basename($rawSelfie));
                        }
                    }
                @endphp
                <div class="mobile-approval-card approval-row-clickable"
                     id="mobile-approval-card-{{ $user->id }}"
                     role="button"
                     tabindex="0"
                     title="Click to view ID & face verification for {{ $user->name }}"
                     data-id="{{ $user->id }}"
                     data-name="{{ $user->name }}"
                     data-id-type="{{ $user->id_type ?? 'Resident ID' }}"
                     data-doc-url="{{ $docUrl ?? '' }}"
                     data-selfie-url="{{ $selfieUrl ?? '' }}"
                     data-status="{{ $currentStatus }}">
                    {{-- Header: Resident Name + Status --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input approval-select-checkbox flex-shrink-0"
                                   type="checkbox"
                                   value="{{ $user->id }}"
                                   data-name="{{ $user->name }}"
                                   onclick="event.stopPropagation()">
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

                    {{-- Biometrics & Review Indicator Footer --}}
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                        <div>
                            @if($selfieUrl)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2 rounded-pill" style="font-size: 11px;" title="Live face selfie verified">
                                    <i class="fa-solid fa-camera me-1"></i> Face Captured
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border py-1 px-2 rounded-pill" style="font-size: 10px;">
                                    No Face
                                </span>
                            @endif
                        </div>
                        <span class="text-primary small fw-semibold d-inline-flex align-items-center gap-1">
                            Review <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                        </span>
                    </div>
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
                                <div class="d-flex align-items-center gap-2">
                                    <a href="#" id="selfieExternalLink" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 small d-none" style="font-size: 11px;" title="Open selfie in new tab">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open Selfie
                                    </a>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5" id="selfieBadge">
                                        <i class="fa-solid fa-circle-check me-1"></i> Live Capture
                                    </span>
                                </div>
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
                <div id="modalActionButtons" class="d-inline-flex gap-2"></div>
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
        const selfieExternalLink = document.getElementById('selfieExternalLink');
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
                if (imgEl) {
                    imgEl.onerror = function () {
                        if (imgWrapper) imgWrapper.classList.add('d-none');
                        if (noIdEl) {
                            noIdEl.classList.remove('d-none');
                            noIdEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation fa-2x mb-2 d-block text-warning"></i> Unable to display ID document';
                        }
                    };
                    imgEl.onload = function () {
                        if (noIdEl) noIdEl.classList.add('d-none');
                        if (imgWrapper) imgWrapper.classList.remove('d-none');
                    };
                    imgEl.src = docUrl;
                }
            }
        } else {
            if (imgWrapper) imgWrapper.classList.add('d-none');
            if (pdfWrapper) pdfWrapper.classList.add('d-none');
            if (noIdEl) {
                noIdEl.classList.remove('d-none');
                noIdEl.innerHTML = '<i class="fa-solid fa-image-slash fa-2x mb-2 d-block"></i> No ID document available';
            }
        }

        // Handle Selfie Display
        if (selfieUrl) {
            if (selfieExternalLink) {
                selfieExternalLink.href = selfieUrl;
                selfieExternalLink.classList.remove('d-none');
            }
            if (selfieBadge) {
                selfieBadge.className = 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5';
                selfieBadge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Live Capture';
            }
            if (selfieImg) {
                selfieImg.onerror = function () {
                    if (selfieWrapper) selfieWrapper.classList.add('d-none');
                    if (selfieNone) {
                        selfieNone.classList.remove('d-none');
                        const label = selfieNone.querySelector('span');
                        if (label) label.textContent = 'Unable to display live selfie file';
                    }
                    if (selfieBadge) {
                        selfieBadge.className = 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-0.5';
                        selfieBadge.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> File Unreadable';
                    }
                };
                selfieImg.onload = function () {
                    if (selfieNone) selfieNone.classList.add('d-none');
                    if (selfieWrapper) selfieWrapper.classList.remove('d-none');
                };
                selfieImg.src = selfieUrl;
            }
            if (selfieNone) selfieNone.classList.add('d-none');
            if (selfieWrapper) selfieWrapper.classList.remove('d-none');
        } else {
            if (selfieExternalLink) {
                selfieExternalLink.classList.add('d-none');
            }
            if (selfieWrapper) selfieWrapper.classList.add('d-none');
            if (selfieNone) {
                selfieNone.classList.remove('d-none');
                const label = selfieNone.querySelector('span');
                if (label) label.textContent = 'No live selfie captured for this account';
            }
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
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    // ---- MULTI-SELECT & BULK APPROVE/REJECT ----
    document.addEventListener('DOMContentLoaded', function () {
        // Clickable Approval Selection Rows and Cards
        document.addEventListener('click', function (e) {
            const clickable = e.target.closest('.approval-row-clickable');
            if (!clickable) return;

            // Prevent opening modal when clicking on checkbox, forms, buttons, or links
            if (e.target.closest('input, button, a, form, .form-check')) {
                return;
            }

            const d = clickable.dataset;
            if (d) {
                previewApprovalDocument(d.docUrl, d.selfieUrl, d.name, d.idType, d.id, d.status);
            }
        });

        // Accessible Keyboard Activation (Enter or Space)
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const clickable = document.activeElement && document.activeElement.classList.contains('approval-row-clickable') ? document.activeElement : null;
                if (clickable && !['INPUT', 'BUTTON', 'A', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    const d = clickable.dataset;
                    if (d) {
                        previewApprovalDocument(d.docUrl, d.selfieUrl, d.name, d.idType, d.id, d.status);
                    }
                }
            }
        });

        const bulkActionsBar = document.getElementById('bulkActionsBar');
        const selectedCountBadge = document.getElementById('selectedCountBadge');
        const bulkApproveCount = document.getElementById('bulkApproveCount');
        const bulkRejectCount = document.getElementById('bulkRejectCount');
        const selectAllCheckbox = document.getElementById('selectAllApprovals');
        const deselectAllBtn = document.getElementById('deselectAllBtn');
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');
        const bulkRejectBtn = document.getElementById('bulkRejectBtn');

        const bulkApproveModalEl = document.getElementById('approvalsBulkApproveModal');
        const bulkApproveModal = bulkApproveModalEl ? new bootstrap.Modal(bulkApproveModalEl) : null;
        const bulkApproveHiddenIds = document.getElementById('bulk_approve_hidden_ids');
        const bulkApproveModalCount = document.getElementById('bulk_approve_modal_count');
        const bulkApproveNamesList = document.getElementById('bulk_approve_names_list');
        const approvalsBulkApproveForm = document.getElementById('approvalsBulkApproveForm');
        const confirmBulkApproveSubmitBtn = document.getElementById('confirmBulkApproveSubmitBtn');

        const bulkRejectModalEl = document.getElementById('approvalsBulkRejectModal');
        const bulkRejectModal = bulkRejectModalEl ? new bootstrap.Modal(bulkRejectModalEl) : null;
        const bulkRejectHiddenIds = document.getElementById('bulk_reject_hidden_ids');
        const bulkRejectModalCount = document.getElementById('bulk_reject_modal_count');
        const bulkRejectNamesList = document.getElementById('bulk_reject_names_list');
        const approvalsBulkRejectForm = document.getElementById('approvalsBulkRejectForm');
        const confirmBulkRejectSubmitBtn = document.getElementById('confirmBulkRejectSubmitBtn');

        function getCheckboxes() {
            return Array.from(document.querySelectorAll('.approval-select-checkbox'));
        }

        function updateBulkBar() {
            const checkedBoxes = Array.from(document.querySelectorAll('.approval-select-checkbox:checked'));
            const count = checkedBoxes.length;

            if (count > 0) {
                bulkActionsBar?.classList.remove('d-none');
                if (selectedCountBadge) selectedCountBadge.textContent = `${count} Selected`;
                if (bulkApproveCount) bulkApproveCount.textContent = count;
                if (bulkRejectCount) bulkRejectCount.textContent = count;
            } else {
                bulkActionsBar?.classList.add('d-none');
            }

            // Highlight table rows and mobile cards
            getCheckboxes().forEach(cb => {
                const row = cb.closest('tr');
                if (row) {
                    if (cb.checked) {
                        row.classList.add('table-primary');
                    } else {
                        row.classList.remove('table-primary');
                    }
                }
                const card = cb.closest('.mobile-approval-card');
                if (card) {
                    if (cb.checked) {
                        card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                    } else {
                        card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                    }
                }
            });

            if (selectAllCheckbox) {
                const tableBoxes = Array.from(document.querySelectorAll('table .approval-select-checkbox'));
                const checkedTableBoxes = Array.from(document.querySelectorAll('table .approval-select-checkbox:checked'));
                selectAllCheckbox.checked = (tableBoxes.length > 0 && checkedTableBoxes.length === tableBoxes.length);
                selectAllCheckbox.indeterminate = (checkedTableBoxes.length > 0 && checkedTableBoxes.length < tableBoxes.length);
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                getCheckboxes().forEach(cb => {
                    cb.checked = selectAllCheckbox.checked;
                });
                updateBulkBar();
            });
        }

        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('approval-select-checkbox')) {
                const val = e.target.value;
                const checked = e.target.checked;
                document.querySelectorAll(`.approval-select-checkbox[value="${val}"]`).forEach(cb => {
                    cb.checked = checked;
                });
                updateBulkBar();
            }
        });

        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function () {
                getCheckboxes().forEach(cb => cb.checked = false);
                updateBulkBar();
            });
        }

        // BULK APPROVE BUTTON CLICK
        if (bulkApproveBtn && bulkApproveModal) {
            bulkApproveBtn.addEventListener('click', function () {
                const checkedBoxes = Array.from(document.querySelectorAll('.approval-select-checkbox:checked'));
                if (checkedBoxes.length === 0) return;

                if (bulkApproveHiddenIds) bulkApproveHiddenIds.innerHTML = '';
                if (bulkApproveNamesList) bulkApproveNamesList.innerHTML = '';

                const seenIds = new Set();
                checkedBoxes.forEach(cb => {
                    if (seenIds.has(cb.value)) return;
                    seenIds.add(cb.value);

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = cb.value;
                    bulkApproveHiddenIds?.appendChild(hidden);

                    const li = document.createElement('li');
                    li.textContent = cb.getAttribute('data-name') || `User ID #${cb.value}`;
                    bulkApproveNamesList?.appendChild(li);
                });

                if (bulkApproveModalCount) bulkApproveModalCount.textContent = `${seenIds.size}`;
                bulkApproveModal.show();
            });
        }

        // BULK REJECT BUTTON CLICK
        if (bulkRejectBtn && bulkRejectModal) {
            bulkRejectBtn.addEventListener('click', function () {
                const checkedBoxes = Array.from(document.querySelectorAll('.approval-select-checkbox:checked'));
                if (checkedBoxes.length === 0) return;

                if (bulkRejectHiddenIds) bulkRejectHiddenIds.innerHTML = '';
                if (bulkRejectNamesList) bulkRejectNamesList.innerHTML = '';

                const seenIds = new Set();
                checkedBoxes.forEach(cb => {
                    if (seenIds.has(cb.value)) return;
                    seenIds.add(cb.value);

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ids[]';
                    hidden.value = cb.value;
                    bulkRejectHiddenIds?.appendChild(hidden);

                    const li = document.createElement('li');
                    li.textContent = cb.getAttribute('data-name') || `User ID #${cb.value}`;
                    bulkRejectNamesList?.appendChild(li);
                });

                if (bulkRejectModalCount) bulkRejectModalCount.textContent = `${seenIds.size}`;
                bulkRejectModal.show();
            });
        }

        // Form submit spinner protection
        if (approvalsBulkApproveForm && confirmBulkApproveSubmitBtn) {
            approvalsBulkApproveForm.addEventListener('submit', function () {
                confirmBulkApproveSubmitBtn.disabled = true;
                confirmBulkApproveSubmitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Approving...';
            });
        }
        if (approvalsBulkRejectForm && confirmBulkRejectSubmitBtn) {
            approvalsBulkRejectForm.addEventListener('submit', function () {
                confirmBulkRejectSubmitBtn.disabled = true;
                confirmBulkRejectSubmitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Rejecting...';
            });
        }
    });
</script>

{{-- MODAL: BULK APPROVE CONFIRMATION --}}
<div class="modal fade" id="approvalsBulkApproveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0">Approve Multiple Accounts</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3">
                    Are you sure you want to approve <strong id="bulk_approve_modal_count" class="text-success"></strong> selected resident account(s)? These accounts will be granted active portal access and sent an approval notification.
                </p>
                <div class="p-3 bg-body-tertiary rounded-3 border mb-2" style="max-height: 180px; overflow-y: auto;">
                    <ul id="bulk_approve_names_list" class="mb-0 ps-3 small"></ul>
                </div>
            </div>
            <div class="modal-footer border-top py-3 px-4">
                <button type="button" class="btn btn-light border rounded-3 px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <form id="approvalsBulkApproveForm" action="{{ route('admin.approvals.bulk-approve') }}" method="POST" class="d-inline">
                    @csrf
                    <div id="bulk_approve_hidden_ids"></div>
                    <button type="submit" class="btn btn-success rounded-3 px-4 fw-semibold shadow-sm" id="confirmBulkApproveSubmitBtn">
                        <i class="fa-solid fa-check me-1"></i> Confirm Approval
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: BULK REJECT CONFIRMATION --}}
<div class="modal fade" id="approvalsBulkRejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0">Reject Multiple Accounts</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3">
                    Are you sure you want to reject <strong id="bulk_reject_modal_count" class="text-danger"></strong> selected resident account(s)?
                </p>
                <div class="p-3 bg-body-tertiary rounded-3 border mb-2" style="max-height: 180px; overflow-y: auto;">
                    <ul id="bulk_reject_names_list" class="mb-0 ps-3 small"></ul>
                </div>
            </div>
            <div class="modal-footer border-top py-3 px-4">
                <button type="button" class="btn btn-light border rounded-3 px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <form id="approvalsBulkRejectForm" action="{{ route('admin.approvals.bulk-reject') }}" method="POST" class="d-inline">
                    @csrf
                    <div id="bulk_reject_hidden_ids"></div>
                    <button type="submit" class="btn btn-danger rounded-3 px-4 fw-semibold shadow-sm" id="confirmBulkRejectSubmitBtn">
                        <i class="fa-solid fa-xmark me-1"></i> Confirm Rejection
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection