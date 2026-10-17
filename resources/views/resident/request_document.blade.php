@extends('resident.layouts.resident')

@section('content')
@php
    $user = auth()->user();
    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ? substr($user->middle_name, 0, 1) . '. ' : '') . ($user->last_name ?? ''));
    $address = trim(($user->purok ? $user->purok . ', ' : '') . 'Brgy. Cogon East, Carmen, Cebu');
@endphp

<style>
/* ---- Page-Scoped Styles ---- */
.req-hero {
    background: var(--gradient-primary);
    border-radius: var(--radius-xl);
    padding: 28px 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.req-hero::after {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
    pointer-events: none;
}
.req-hero h2 { font-family: var(--font-heading); font-weight: 700; font-size: 1.5rem; margin-bottom: 4px; }
.req-hero p { opacity: 0.85; font-size: 13px; margin: 0; }
.req-hero .badge-processing { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); border-radius: 10px; padding: 8px 14px; }

/* ---- Lifecycle Stepper ---- */
.lifecycle-stepper {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding: 0 10px;
}
.lifecycle-stepper::before {
    content: '';
    position: absolute;
    top: 22px;
    left: 40px;
    right: 40px;
    height: 3px;
    background: #e5e7eb;
    z-index: 0;
}
.lifecycle-progress-fill {
    content: '';
    position: absolute;
    top: 22px;
    left: 40px;
    height: 3px;
    background: linear-gradient(90deg, #10b981, var(--color-primary));
    z-index: 0;
    transition: width 0.5s ease;
    border-radius: 3px;
}
.stepper-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    flex: 1;
}
.stepper-circle {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 8px;
    background: #e5e7eb;
    color: #9ca3af;
    transition: all 0.4s ease;
    border: 3px solid transparent;
}
.stepper-step.active .stepper-circle {
    background: var(--color-primary);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(30,64,175,0.15);
    animation: pulse-ring 1.8s ease infinite;
}
.stepper-step.completed .stepper-circle {
    background: #10b981;
    color: #fff;
}
.stepper-step.rejected .stepper-circle {
    background: #ef4444;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(239,68,68,0.15);
}
@keyframes pulse-ring {
    0%   { box-shadow: 0 0 0 0 rgba(30,64,175,0.25); }
    70%  { box-shadow: 0 0 0 8px rgba(30,64,175,0); }
    100% { box-shadow: 0 0 0 0 rgba(30,64,175,0); }
}
.stepper-label { font-size: 11px; font-weight: 600; color: #9ca3af; text-align: center; }
.stepper-sublabel { font-size: 10px; color: #d1d5db; text-align: center; }
.stepper-step.active .stepper-label { color: var(--color-primary); font-weight: 700; }
.stepper-step.active .stepper-sublabel { color: var(--color-primary); opacity: 0.7; }
.stepper-step.completed .stepper-label { color: #10b981; }
.stepper-step.completed .stepper-sublabel { color: #10b981; opacity: 0.7; }
.stepper-step.rejected .stepper-label { color: #ef4444; font-weight: 700; }
.stepper-step.rejected .stepper-sublabel { color: #ef4444; opacity: 0.7; }

/* ---- Section Cards ---- */
.req-section {
    background: #fff;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--color-border-light);
    overflow: hidden;
    margin-bottom: 20px;
}
.req-section-header {
    padding: 18px 24px 14px;
    border-bottom: 1px solid #f1f3f5;
    display: flex;
    align-items: center;
    gap: 12px;
}
.req-section-header .icon-box {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.req-section-header h6 { margin: 0; font-size: 14px; font-weight: 700; color: var(--color-text); font-family: var(--font-heading); }
.req-section-header p { margin: 0; font-size: 11.5px; color: var(--color-text-light); }
.req-section-body { padding: 20px 24px; }

/* ---- Info Display ---- */
.info-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--color-text-light); margin-bottom: 3px; }
.info-value { font-size: 14px; font-weight: 600; color: var(--color-text); word-break: break-word; }
.info-value.editable { color: var(--color-primary); }

/* ---- Document Info Card ---- */
.doc-info-card {
    background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
    border: 1px solid var(--color-primary-200);
    border-radius: var(--radius-lg);
    padding: 16px 20px;
    display: none;
    animation: slideDown 0.3s ease;
}
.doc-info-card.show { display: block; }
/* ---- Multiple Selected Documents ---- */
.selected-doc-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: var(--radius-lg, 12px);
    transition: all 0.2s ease;
}
.selected-doc-card:hover {
    border-color: var(--color-primary-200, #93c5fd);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
}
.doc-icon-badge {
    width: 36px; height: 36px;
    border-radius: 8px;
    background: rgba(30,64,175,0.08);
    color: var(--color-primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}
.doc-type-card.selected {
    border-color: var(--color-primary) !important;
    background: var(--color-primary-50) !important;
    box-shadow: 0 0 0 3px rgba(30,64,175,0.12) !important;
}
.doc-card-badge {
    display: inline-flex;
    align-items: center;
    margin-left: auto;
}

.purpose-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 14px;
    border: 1.5px solid #e5e7eb;
    border-radius: var(--radius-full);
    font-size: 12.5px;
    font-weight: 600;
    color: var(--color-text-light);
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}
.purpose-chip:hover {
    border-color: var(--color-primary-200);
    color: var(--color-primary);
    background: var(--color-primary-50);
}
.purpose-chip.selected {
    border-color: var(--color-primary);
    color: var(--color-primary);
    background: var(--color-primary-50);
}

/* ---- Upload Zone ---- */
.upload-zone {
    border: 2px dashed var(--color-primary-200);
    border-radius: var(--radius-lg);
    padding: 32px 20px;
    text-align: center;
    background: #fafbff;
    transition: all 0.2s ease;
    cursor: pointer;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: var(--color-primary);
    background: var(--color-primary-50);
}
.upload-zone .upload-icon {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: var(--color-primary-100);
    color: var(--color-primary);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin-bottom: 12px;
}

/* ---- File List ---- */
.file-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: #fff;
    margin-bottom: 8px;
    animation: slideDown 0.3s ease;
}
.file-item .file-icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    background: var(--color-primary-50);
    color: var(--color-primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.file-item .file-info { flex: 1; min-width: 0; }
.file-item .file-name { font-size: 13px; font-weight: 600; color: var(--color-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-item .file-meta { font-size: 11px; color: var(--color-text-light); }
.file-item .file-remove { color: #ef4444; cursor: pointer; font-size: 14px; padding: 4px; transition: transform 0.15s; }
.file-item .file-remove:hover { transform: scale(1.2); }

/* ---- Summary Card ---- */
.summary-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #eff6ff 100%);
    border: 1px solid var(--color-primary-200);
    border-radius: var(--radius-xl);
    padding: 22px 24px;
}
.summary-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(30,64,175,0.08); }
.summary-row:last-child { border-bottom: none; }
.summary-row .s-label { font-size: 12.5px; font-weight: 600; color: var(--color-text-light); }
.summary-row .s-value { font-size: 12.5px; font-weight: 700; color: var(--color-text); text-align: right; max-width: 60%; }

/* ---- Payment Notice ---- */
.payment-notice {
    background: linear-gradient(135deg, #fef3c7, #fffbeb);
    border: 1px solid #fcd34d;
    border-radius: var(--radius-lg);
    padding: 14px 18px;
}

/* ---- Checklist ---- */
.req-checklist { list-style: none; padding: 0; margin: 0; }
.req-checklist li {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 6px 0;
    font-size: 13px;
    color: var(--color-text);
}
.req-checklist li .check-icon {
    width: 20px; height: 20px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px;
    flex-shrink: 0;
    margin-top: 1px;
}
.req-checklist li .check-icon.done { background: #d1fae5; color: #059669; }
.req-checklist li .check-icon.pending { background: #e5e7eb; color: #9ca3af; }

/* ---- Responsive Stepper ---- */
@media (max-width: 768px) {
    .lifecycle-stepper { flex-wrap: wrap; gap: 8px; }
    .lifecycle-stepper::before { display: none; }
    .stepper-step { flex: 0 0 auto; }
    .req-hero { padding: 20px; }
}

/* ---- Review Request Confirmation Modal ---- */
#confirmSubmitModal .modal-dialog {
    max-width: 780px;
    width: 95%;
}
#confirmSubmitModal .modal-content {
    border-radius: 16px;
}
#confirmSubmitModal .review-row {
    word-break: break-word;
    overflow-wrap: anywhere;
}
#confirmSubmitModal .review-display {
    word-break: break-word;
    overflow-wrap: anywhere;
    min-width: 0;
}
#confirmSubmitModal .review-edit-field {
    width: 100%;
}
#confirmSubmitModal .review-edit-field input,
#confirmSubmitModal .review-edit-field textarea {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    word-break: break-word;
    overflow-wrap: anywhere;
}
#confirmSubmitModal .attachment-preview-card {
    transition: background-color 0.15s ease;
}
#confirmSubmitModal .attachment-preview-card:hover {
    background-color: #f1f5f9 !important;
}
</style>

<div class="container py-4">
    {{-- ============================================ --}}
    {{-- 1. PAGE HEADER --}}
    {{-- ============================================ --}}
    <div class="mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('resident.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold">Request Document</li>
            </ol>
        </nav>
    </div>

    <div class="req-hero mb-4">
        <div>
            <h2>Request a Barangay Document</h2>
            <p>Submit your document request online and monitor its status until it is ready for pickup.</p>
        </div>
    </div>

    {{-- Request Lifecycle Tracker --}}
    @php
        $steps = [
            1 => ['label' => 'Submit Request',   'sub' => 'Initial Submission'],
            2 => ['label' => 'Verification',      'sub' => 'Staff Review'],
            3 => ['label' => 'Processing',        'sub' => 'Document Generation'],
            4 => ['label' => 'Ready for Pickup',  'sub' => 'SMS Notification'],
            5 => ['label' => 'Claim Document',    'sub' => 'Office Counter'],
        ];
        $isRejected = $latestRequest && $latestRequest->status === 'Rejected';

        // Badge label
        if (!$latestRequest || $currentStep === 0) {
            $badgeLabel = 'No active request';
        } elseif ($isRejected) {
            $badgeLabel = 'Request Rejected';
        } else {
            $badgeLabel = 'Step ' . $currentStep . ' of 5: ' . $steps[$currentStep]['label'];
        }
    @endphp
    <div class="req-section mb-4" id="lifecycle-tracker-card">
        <div class="req-section-body py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0" style="font-size:13px;font-family:var(--font-heading);">Request Lifecycle Tracker</h6>
                <span class="badge fw-bold {{ $isRejected ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary' }}"
                      style="font-size:10.5px;"
                      id="lifecycle-badge">{{ $badgeLabel }}</span>
            </div>

            {{-- Tracker tracking number sub-label --}}
            @if($latestRequest)
            <p class="text-muted mb-3" style="font-size:11px;">
                Tracking: <strong class="font-monospace">{{ $latestRequest->tracking_number }}</strong>
                &nbsp;&mdash;&nbsp;
                <span class="{{ $isRejected ? 'text-danger' : 'text-primary' }} fw-semibold">{{ $latestRequest->status }}</span>
            </p>
            @endif

            <div class="lifecycle-stepper" id="lifecycle-stepper">
                {{-- Animated progress fill bar --}}
                @php
                    $fillPercent = $isRejected ? 0 : max(0, (($currentStep - 1) / 4) * 100);
                @endphp
                <div class="lifecycle-progress-fill" id="lifecycle-fill"
                     style="width: calc({{ $fillPercent }}% * (100% - 80px) / 100 + {{ $fillPercent > 0 ? '0px' : '0px' }});
                            left: 40px; width: {{ $fillPercent }}%;"></div>

                @foreach($steps as $stepNum => $stepData)
                    @php
                        if ($isRejected && $stepNum === $currentStep) {
                            $cls = 'rejected';
                        } elseif ($latestRequest && $stepNum < $currentStep) {
                            $cls = 'completed';
                        } elseif ($latestRequest && $stepNum === $currentStep && !$isRejected) {
                            $cls = 'active';
                        } else {
                            $cls = '';
                        }
                        // Icon: completed = checkmark, rejected = x, active/pending = number
                        $icon = ($cls === 'completed') ? '<i class="fa-solid fa-check"></i>'
                              : ($cls === 'rejected'   ? '<i class="fa-solid fa-xmark"></i>'
                              : $stepNum);
                    @endphp
                    <div class="stepper-step {{ $cls }}" data-step="{{ $stepNum }}">
                        <div class="stepper-circle">{!! $icon !!}</div>
                        <div class="stepper-label">{{ $stepData['label'] }}</div>
                        <div class="stepper-sublabel">{{ $stepData['sub'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Payment Notice --}}
    <div class="payment-notice d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-circle-info text-warning fs-5"></i>
        <div>
            <div class="fw-bold text-dark" style="font-size:13px;">Important Payment Notice</div>
            <div class="text-muted" style="font-size:12px;">No online payment is required. Payment will be collected securely at the Barangay Office when you claim your physical document.</div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- 2. BEFORE YOU SUBMIT --}}
    {{-- ============================================ --}}
    <div class="req-section mb-4">
        <div class="req-section-header">
            <div class="icon-box" style="background:rgba(30,64,175,0.08);color:var(--color-primary);">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <h6>Before You Submit</h6>
            </div>
        </div>
        <div class="req-section-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <ul class="req-checklist">
                        <li>
                            <span class="check-icon done"><i class="fa-solid fa-check"></i></span>
                            <span>Ensure all personal information matches your valid government-issued <strong>ID</strong>.</span>
                        </li>
                        <li>
                            <span class="check-icon done"><i class="fa-solid fa-check"></i></span>
                            <span>Prepare clear digital copies of supporting documents for fast verification.</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="req-checklist">
                        <li>
                            <span class="check-icon done"><i class="fa-solid fa-check"></i></span>
                            <span>Select the exact document type required by your requesting institution.</span>
                        </li>
                        <li>
                            <span class="check-icon done"><i class="fa-solid fa-check"></i></span>
                            <span>Bring exact cash payment and receipt upon office pickup.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('resident.request.store') }}" method="POST" enctype="multipart/form-data" id="docRequestForm" novalidate>
        @csrf

        <input type="hidden" name="urgency" value="Normal">

        {{-- ============================================ --}}
        {{-- 3. PERSONAL INFORMATION --}}
        {{-- ============================================ --}}
        <div class="req-section mb-4">
            <div class="req-section-header">
                <div class="icon-box" style="background:rgba(30,64,175,0.08);color:var(--color-primary);">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="flex-grow-1">
                    <h6>Personal Information</h6>
                    <p class="mb-0 text-muted" style="font-size: 11.5px;">Auto-filled from your profile. You can edit any details below for this specific request.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnResetAccountDetails" style="font-size: 11.5px;" title="Reset fields to match your account profile">
                        <i class="fa-solid fa-rotate-left me-1"></i> <span id="resetBtnLabel">Reset to Account</span>
                    </button>
                    <a href="{{ route('resident.settings') }}" class="text-muted text-decoration-none small d-none d-md-inline" style="font-size: 11.5px;" target="_blank" title="Edit your account profile">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Profile Settings
                    </a>
                </div>
            </div>
            <div class="req-section-body">
                <div class="row g-3">
                    {{-- Full Name --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1" for="piFullName">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                            <input type="text" 
                                   name="full_name" 
                                   id="piFullName" 
                                   class="form-control border-start-0 ps-1" 
                                   style="text-transform: uppercase;"
                                   value="{{ strtoupper(old('full_name', $fullName)) }}" 
                                   placeholder="e.g. JUAN P. DELA CRUZ"
                                   required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted" style="font-size: 10.5px;">Name to appear on the official document</span>
                            <a href="javascript:void(0);" id="toggleNameBreakdown" class="text-decoration-none small text-primary" style="font-size: 11px;">
                                <i class="fa-solid fa-sliders me-1"></i> <span id="toggleNameText">Separate Name Fields</span>
                            </a>
                        </div>
                    </div>

                    {{-- Contact Number --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1" for="piContactNumber">
                            Contact Number <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" 
                                   name="contact_number" 
                                   id="piContactNumber" 
                                   class="form-control border-start-0 ps-1" 
                                   value="{{ old('contact_number', $user->phone_number) }}" 
                                   placeholder="09XXXXXXXXX"
                                   required>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 10.5px;">For SMS notification when document is ready for pickup</div>
                    </div>

                    {{-- Collapsible Separate Name Fields --}}
                    <div class="col-12" id="nameBreakdownContainer" style="display: none;">
                        <div class="p-3 rounded-3 border bg-light">
                            <div class="text-muted fw-bold mb-2 small" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-id-card me-1"></i> Detailed Name Breakdown
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="piFirstName">First Name</label>
                                    <input type="text" name="first_name" id="piFirstName" class="form-control form-control-sm" style="text-transform: uppercase;" value="{{ strtoupper(old('first_name', $user->first_name ?? '')) }}" placeholder="FIRST NAME">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="piMiddleInitial">Middle Initial</label>
                                    <input type="text" name="middle_initial" id="piMiddleInitial" class="form-control form-control-sm text-uppercase" style="text-transform: uppercase;" value="{{ old('middle_initial', $user->middle_name ? strtoupper(substr($user->middle_name, 0, 1)) : '') }}" maxlength="5" placeholder="M.I.">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="piLastName">Last Name</label>
                                    <input type="text" name="last_name" id="piLastName" class="form-control form-control-sm" style="text-transform: uppercase;" value="{{ strtoupper(old('last_name', $user->last_name ?? '')) }}" placeholder="LAST NAME">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Complete Address --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1" for="piAddress">
                            Complete Address <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" 
                                   name="address" 
                                   id="piAddress" 
                                   class="form-control border-start-0 ps-1" 
                                   style="text-transform: uppercase;"
                                   value="{{ strtoupper(old('address', $address)) }}" 
                                   placeholder="PUROK, STREET, BRGY. COGON EAST, CARMEN, CEBU"
                                   required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted" style="font-size: 10.5px;">Address to appear on official document</span>
                            <a href="javascript:void(0);" id="toggleAddressBreakdown" class="text-decoration-none small text-primary" style="font-size: 11px;">
                                <i class="fa-solid fa-sliders me-1"></i> <span id="toggleAddressText">Separate Purok/Sitio</span>
                            </a>
                        </div>
                    </div>

                    {{-- Email Address --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1" for="piEmail">
                            Email Address <span class="text-muted">(Optional)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" 
                                   name="email" 
                                   id="piEmail" 
                                   class="form-control border-start-0 ps-1" 
                                   value="{{ old('email', $user->email) }}" 
                                   placeholder="name@example.com">
                        </div>
                        <div class="text-muted mt-1" style="font-size: 10.5px;">For email confirmations and tracking receipts</div>
                    </div>

                    {{-- Collapsible Separate Address / Age Fields --}}
                    <div class="col-12" id="addressBreakdownContainer" style="display: none;">
                        <div class="p-3 rounded-3 border bg-light">
                            <div class="text-muted fw-bold mb-2 small" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-map-location-dot me-1"></i> Detailed Address & Age
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="piPurok">Purok</label>
                                    <input type="text" name="purok" id="piPurok" class="form-control form-control-sm" style="text-transform: uppercase;" value="{{ strtoupper(old('purok', $user->purok ?? '')) }}" placeholder="e.g. PUROK NANGKA">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="piStreet">Sitio / Street</label>
                                    <input type="text" name="street" id="piStreet" class="form-control form-control-sm" style="text-transform: uppercase;" value="{{ strtoupper(old('street', $user->sitio ?? $user->street ?? '')) }}" placeholder="e.g. SITIO LUBI">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="piCity">Barangay & City</label>
                                    <input type="text" name="city" id="piCity" class="form-control form-control-sm bg-white" value="{{ old('city', 'COGON EAST, CARMEN, CEBU') }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="piAge">Age</label>
                                    <input type="number" name="age" id="piAge" class="form-control form-control-sm" value="{{ old('age', $user->age) }}" placeholder="Age" min="1" max="130">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- 4. DOCUMENT INFORMATION --}}
        {{-- ============================================ --}}
        <div class="req-section mb-4">
            <div class="req-section-header">
                <div class="icon-box" style="background:rgba(30,64,175,0.08);color:var(--color-primary);">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div>
                    <h6>Document Selection</h6>
                </div>
            </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label small fw-semibold text-muted mb-0">Select Document Type <span class="text-danger">*</span></label>
                    <span class="text-muted small" style="font-size: 11px;">Click a document type to specify purpose</span>
                </div>

                {{-- Searchable Document Type Selector --}}
                <div class="doc-search-wrapper position-relative mb-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" id="docTypeSearch" class="form-control border-start-0 rounded-end-3" placeholder="Search document type..." autocomplete="off">
                    </div>
                </div>

                <input type="hidden" name="documents_json" id="documentsJson" value="{{ old('documents_json') }}">
                <input type="hidden" name="attachment_meta" id="attachmentMetaJson">
                <input type="hidden" name="document_type" id="documentTypeSelect" value="{{ old('document_type', request('document_type')) }}">

                <div id="docTypeGrid" class="row g-2 mt-1">
                    @php
                        $docTypesList = collect();
                        try {
                            if (isset($activeDocTypes)) {
                                $docTypesList = $activeDocTypes;
                            } elseif (\Illuminate\Support\Facades\Schema::hasTable('document_types')) {
                                $docTypesList = \App\Models\DocumentType::active()->orderBy('name')->get();
                            }
                        } catch (\Throwable $e) {
                            $docTypesList = collect();
                        }
                        $selectedDoc = old('document_type', request('document_type'));
                    @endphp
                    @forelse($docTypesList as $doc)
                        @php
                            $docName = is_string($doc) ? $doc : $doc->name;
                            $docIcon = is_object($doc) ? $doc->icon : 'fa-file-lines';
                            $docFeeFormatted = is_object($doc) ? $doc->formatted_fee : 'FREE';
                            $docProcessing = is_object($doc) ? ($doc->processing_days ?: '1-2 Business Days') : '1-2 Business Days';
                        @endphp
                        <div class="col-6 col-md-4 doc-type-item" data-value="{{ $docName }}" data-icon="{{ $docIcon }}">
                            <div class="doc-type-card d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer {{ $selectedDoc === $docName ? 'selected' : '' }}"
                                 onclick="selectDocType('{{ addslashes($docName) }}')">
                                <div class="doc-type-icon" style="width:32px;height:32px;border-radius:8px;background:rgba(30,64,175,0.08);color:var(--color-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;">
                                    <i class="fa-solid {{ $docIcon }}"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0" style="min-width:0;">
                                    <span class="doc-type-name d-block text-truncate" style="font-size:12px;font-weight:600;color:var(--color-text);line-height:1.3;" title="{{ $docName }}">{{ $docName }}</span>
                                    <span class="text-muted" style="font-size:10.5px;">{{ $docFeeFormatted }}</span>
                                </div>
                                <span class="doc-card-badge" style="display:none;"></span>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            <i class="fa-solid fa-file-circle-xmark fa-2x mb-2 text-muted"></i>
                            <p class="mb-0 small">No document types currently available.</p>
                        </div>
                    @endforelse
                </div>
                <div id="docTypeNoResults" class="text-muted text-center py-3 small" style="display:none;"><i class="fa-solid fa-circle-xmark me-1"></i>No document type found.</div>

                {{-- Dynamic Document Info Card --}}
                <div class="doc-info-card mt-3" id="docInfoCard">
                    <div>
                        <div class="fw-bold text-dark mb-2" id="docInfoTitle" style="font-size:14px;"></div>
                        <div class="text-muted small mb-1" style="font-size:11.5px;">
                            <span class="text-uppercase fw-bold" style="font-size:10px;letter-spacing:0.5px;color:var(--color-text-light);">Requirements &amp; Specs</span>
                        </div>
                        <div id="docInfoRequirements" class="text-dark" style="font-size:13px;"></div>
                        <div class="text-muted mt-1" style="font-size:12px;">
                            <i class="fa-regular fa-clock me-1"></i> Processing Time: <strong id="docInfoTime" class="text-dark"></strong>
                            <span class="mx-2">•</span>
                            <i class="fa-solid fa-tag me-1 text-primary"></i> Fee: <strong id="docInfoFee" class="text-dark"></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden purpose field — populated by modal --}}
        <input type="hidden" name="purpose" id="purposeField" value="{{ old('purpose') }}">

        {{-- ============================================ --}}
        {{-- 6. SUPPORTING DOCUMENTS --}}
        {{-- ============================================ --}}
        <div class="req-section mb-4">
            <div class="req-section-header">
                <div class="icon-box" style="background:rgba(30,64,175,0.08);color:var(--color-primary);">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div>
                    <h6>Supporting Documents &amp; Upload</h6>
                    <p>Please upload a valid ID or proof of residence as required.</p>
                </div>
            </div>
            <div class="req-section-body">
                {{-- ID Type Selection --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1" for="requestIdType">
                        <i class="fa-solid fa-id-card text-primary me-1"></i> Valid ID Type Presented
                    </label>
                    <select name="id_type" id="requestIdType" class="form-select rounded-3">
                        <option value="">-- Select Valid ID Type --</option>
                        @php
                            $userCurrentId = old('id_type', $user->id_type ?? '');
                            $standardIds = [
                                'National ID',
                                'Driver\'s License',
                                'Voter\'s ID',
                                'Student ID',
                                'Passport',
                                'UMID',
                                'PhilHealth',
                                'Postal ID',
                                'Senior Citizen ID',
                                'Barangay ID',
                                'Others'
                            ];
                        @endphp
                        @foreach($standardIds as $sId)
                            <option value="{{ $sId }}" {{ $userCurrentId == $sId ? 'selected' : '' }}>{{ $sId }}</option>
                        @endforeach
                        @if($userCurrentId && !in_array($userCurrentId, $standardIds))
                            <option value="{{ $userCurrentId }}" selected>{{ $userCurrentId }}</option>
                        @endif
                    </select>
                    <div class="text-muted mt-1" style="font-size: 10.5px;">Specify which valid ID is attached or presented to identify and verify your request.</div>
                </div>

                {{-- Dynamic Requirements Checklist --}}
                <div id="docRequirementsChecklist" class="mb-3" style="display:none;">
                    <div class="text-muted small fw-bold mb-2" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Required for this request</div>
                    <div id="reqChecklistItems"></div>
                </div>

                {{-- Upload Zone --}}
                <div class="upload-zone mb-3" id="uploadZone">
                    <div class="upload-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <p class="fw-bold text-dark mb-1" style="font-size:15px;">Drag &amp; drop files here</p>
                    <p class="text-muted small mb-3">Supports PDF, JPG, JPEG, PNG (Max 5MB per file)</p>
                    <button type="button" class="btn btn-primary px-4 py-2 fw-semibold rounded-3" onclick="document.getElementById('fileInput').click()">
                        <i class="fa-solid fa-folder-open me-1"></i> Browse Files
                    </button>
                    <input type="file" name="attachments[]" id="fileInput" class="d-none" multiple accept=".pdf,.jpg,.jpeg,.png">
                </div>

                {{-- Uploaded Files List --}}
                <div id="fileList"></div>
                <div id="uploadError" class="text-danger small mt-2" style="display:none;"></div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- SUBMIT BUTTON --}}
        {{-- ============================================ --}}
        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-3 shadow" id="btnOpenConfirmModal" style="min-width:320px;font-size:15px;">
                Submit Document Request <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>
            <div class="text-muted small mt-2">
                <i class="fa-solid fa-shield-halved me-1"></i> By submitting, you certify that all information provided is true and correct.
            </div>
        </div>
    </form>

{{-- ============================================ --}}
{{-- BP PURPOSE MODAL --}}
{{-- ============================================ --}}
<div class="modal fade" id="bpPurposeModal" tabindex="-1" aria-labelledby="bpPurposeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(30,64,175,0.1);color:var(--color-primary);font-size:1.1rem;flex-shrink:0;">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0" id="bpPurposeModalLabel" style="font-size:15px;">Select Purpose</h6>
                        <p class="text-muted mb-0" style="font-size:12px;">Brgy Clearance for Business Permit</p>
                    </div>
                </div>
            </div>

            <div class="modal-body px-4 pt-3 pb-2">
                <p class="text-muted small mb-3" style="font-size:12.5px;">Choose the primary purpose for this clearance. You may also add additional details below.</p>

                <div class="d-flex flex-column gap-2 mb-3" id="bpModalOptions">
                    <label class="bp-modal-option d-flex align-items-center gap-3 p-3 border rounded-3" style="cursor:pointer;">
                        <input type="radio" name="bp_modal_radio" value="FOR LOAN PURPOSES" class="bp-modal-radio form-check-input mt-0 flex-shrink-0" style="width:18px;height:18px;">
                        <span>
                            <span class="fw-semibold d-block" style="font-size:13px;">For Loan Purposes</span>
                            <span class="text-muted" style="font-size:11.5px;">Certification required for loan applications.</span>
                        </span>
                    </label>
                    <label class="bp-modal-option d-flex align-items-center gap-3 p-3 border rounded-3" style="cursor:pointer;">
                        <input type="radio" name="bp_modal_radio" value="BUSINESS PERMIT" class="bp-modal-radio form-check-input mt-0 flex-shrink-0" style="width:18px;height:18px;">
                        <span>
                            <span class="fw-semibold d-block" style="font-size:13px;">Business Permit</span>
                            <span class="text-muted" style="font-size:11.5px;">For obtaining a new business permit from the municipality.</span>
                        </span>
                    </label>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Other / Further Specify <span class="fw-normal">(optional)</span></label>
                    <textarea id="bpPurposeOther" class="form-control rounded-3" rows="2"
                        placeholder="Add any additional details or a different purpose..."
                        style="font-size:13px;text-transform:uppercase;"></textarea>
                </div>
                <div id="bpPurposeError" class="text-danger small mt-1" style="display:none;font-size:12px;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>Please select a purpose or specify one below.
                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" id="bpModalCancel" style="font-size:13px;">Cancel</button>
                <button type="button" class="btn btn-primary rounded-3 px-4 fw-semibold flex-grow-1" id="bpModalConfirm" style="font-size:13px;">
                    <i class="fa-solid fa-check me-1"></i> Confirm Purpose
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- GENERIC PURPOSE MODAL (all other docs) --}}
{{-- ============================================ --}}
<div class="modal fade" id="genericPurposeModal" tabindex="-1" aria-labelledby="genericPurposeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(30,64,175,0.1);color:var(--color-primary);font-size:1.1rem;flex-shrink:0;">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0" id="genericPurposeModalLabel" style="font-size:15px;">Purpose of Request</h6>
                        <p class="text-muted mb-0" id="genericPurposeModalDocName" style="font-size:12px;">—</p>
                    </div>
                </div>
            </div>

            <div class="modal-body px-4 pt-3 pb-2">
                <p class="text-muted small mb-3" style="font-size:12.5px;">Select a quick purpose or type your own below.</p>

                {{-- Quick chips --}}
                <div class="d-flex flex-wrap gap-2 mb-3" id="genericPurposeChips">
                    <span class="gp-chip" data-purpose="Employment" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Employment</span>
                    <span class="gp-chip" data-purpose="School" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">School</span>
                    <span class="gp-chip" data-purpose="Scholarship" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Scholarship</span>
                    <span class="gp-chip" data-purpose="Business" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Business</span>
                    <span class="gp-chip" data-purpose="Government Requirement" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Government Requirement</span>
                    <span class="gp-chip" data-purpose="Travel" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Travel</span>
                    <span class="gp-chip" data-purpose="Medical" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Medical</span>
                    <span class="gp-chip" data-purpose="Senior Citizen Benefits" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Senior Citizen Benefits</span>
                    <span class="gp-chip" data-purpose="Burial Assistance" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Burial Assistance</span>
                    <span class="gp-chip" data-purpose="Electric Meter Connection" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Electric Meter Connection</span>
                    <span class="gp-chip" data-purpose="First Time Jobseeker" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">First Time Jobseeker</span>
                    <span class="gp-chip" data-purpose="Cohabitation Proof" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Cohabitation Proof</span>
                    <span class="gp-chip" data-purpose="Probation & Bail Application" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Probation &amp; Bail</span>
                    <span class="gp-chip" data-purpose="" style="cursor:pointer;padding:6px 14px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:12.5px;font-weight:600;background:#fff;color:#374151;transition:all 0.15s;">Other</span>
                </div>

                {{-- Free-text purpose --}}
                <textarea id="genericPurposeText" class="form-control rounded-3" rows="3"
                    placeholder="Type the purpose of your request..."
                    style="font-size:13px;text-transform:uppercase;"></textarea>

                <div id="genericPurposeError" class="text-danger small mt-2" style="display:none;font-size:12px;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>Please enter the purpose of your request.
                </div>
            </div>

            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" id="genericModalCancel" style="font-size:13px;">Cancel</button>
                <button type="button" class="btn btn-primary rounded-3 px-4 fw-semibold flex-grow-1" id="genericModalConfirm" style="font-size:13px;">
                    <i class="fa-solid fa-check me-1"></i> Confirm Purpose
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- REVIEW & EDIT CONFIRMATION MODAL --}}
{{-- ============================================ --}}
<div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width:780px; width:95%;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            {{-- Header --}}
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3"
                         style="width:44px;height:44px;background:#e8f0fe;color:var(--color-primary);font-size:1.2rem;flex-shrink:0;">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0" id="confirmSubmitLabel" style="font-size:16px;">Review Your Request</h6>
                        <p class="text-muted mb-0" style="font-size:12px;">Check and edit your details before submitting</p>
                    </div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3">

                {{-- Requested Document --}}
                <div class="review-row mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">
                            <i class="fa-solid fa-file-lines text-primary me-1"></i> Requested Document
                        </span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-primary text-decoration-none fw-semibold" onclick="const modalEl = document.getElementById('confirmSubmitModal'); bootstrap.Modal.getInstance(modalEl)?.hide(); document.getElementById('docTypeGrid')?.scrollIntoView({behavior:'smooth', block:'center'});" style="font-size:12px;">
                            <i class="fa-solid fa-pen me-1"></i>Change
                        </button>
                    </div>
                    <div class="review-display">
                        <span class="fw-semibold text-dark text-break" id="modalDocType" style="font-size:14px; word-break: break-word;">—</span>
                    </div>
                </div>

                {{-- 2-Column: Full Name + Contact Number --}}
                <div class="row g-3 mb-3 pb-3 border-bottom">
                    {{-- Full Name --}}
                    <div class="col-12 col-md-6">
                        <div class="review-row h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Full Name</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary review-edit-btn fw-semibold" data-target="reviewName" style="font-size:12px;">
                                    <i class="fa-solid fa-pen me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-display" id="reviewNameDisplay">
                                <span class="fw-semibold text-dark text-break" id="modalResidentName" style="font-size:14px; word-break: break-word;">—</span>
                            </div>
                            <div class="review-edit-field" id="reviewNameEdit" style="display:none;">
                                <input type="text" id="reviewNameInput" class="form-control form-control-sm rounded-3 mt-1 w-100"
                                       placeholder="Full name..." style="font-size:13px; text-transform: uppercase;">
                            </div>
                        </div>
                    </div>

                    {{-- Contact Number --}}
                    <div class="col-12 col-md-6">
                        <div class="review-row h-100">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Contact Number</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary review-edit-btn fw-semibold" data-target="reviewContact" style="font-size:12px;">
                                    <i class="fa-solid fa-pen me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-display" id="reviewContactDisplay">
                                <span class="fw-semibold text-dark text-break" id="modalContact" style="font-size:14px; word-break: break-word;">—</span>
                            </div>
                            <div class="review-edit-field" id="reviewContactEdit" style="display:none;">
                                <input type="text" id="reviewContactInput" class="form-control form-control-sm rounded-3 mt-1 w-100"
                                       placeholder="Contact number..." style="font-size:13px;" maxlength="20">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Address --}}
                <div class="review-row mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Complete Address</span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-primary review-edit-btn fw-semibold" data-target="reviewAddress" style="font-size:12px;">
                            <i class="fa-solid fa-pen me-1"></i>Edit
                        </button>
                    </div>
                    <div class="review-display" id="reviewAddressDisplay">
                        <span class="fw-semibold text-dark text-break" id="modalAddress" style="font-size:14px; word-break: break-word; line-height: 1.5;">—</span>
                    </div>
                    <div class="review-edit-field" id="reviewAddressEdit" style="display:none;">
                        <textarea id="reviewAddressInput" class="form-control form-control-sm rounded-3 mt-1 w-100"
                                  placeholder="Complete address..." rows="2" style="font-size:13px; text-transform: uppercase; word-break: break-word;"></textarea>
                    </div>
                </div>

                {{-- Purpose --}}
                <div class="review-row mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Purpose</span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-primary review-edit-btn fw-semibold" data-target="reviewPurpose" style="font-size:12px;">
                            <i class="fa-solid fa-pen me-1"></i>Edit
                        </button>
                    </div>
                    <div class="review-display" id="reviewPurposeDisplay">
                        <span class="fw-semibold text-dark text-break" id="modalPurpose" style="font-size:14px; word-break: break-word; line-height: 1.5;">—</span>
                    </div>
                    <div class="review-edit-field" id="reviewPurposeEdit" style="display:none;">
                        <textarea id="reviewPurposeInput" class="form-control form-control-sm rounded-3 mt-1 w-100"
                                  placeholder="Purpose..." rows="2" style="font-size:13px; text-transform: uppercase; word-break: break-word;"></textarea>
                    </div>
                </div>

                {{-- Uploaded Documents (Attachments & Types) --}}
                <div class="review-row mb-3 pb-3 border-bottom" id="modalAttachmentsSection">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">
                            <i class="fa-solid fa-paperclip text-primary me-1"></i> Uploaded Documents &amp; Type (<span id="modalAttachmentCount">0</span>)
                        </span>
                        <button type="button" class="btn btn-link btn-sm p-0 text-primary text-decoration-none fw-semibold" onclick="const modalEl = document.getElementById('confirmSubmitModal'); bootstrap.Modal.getInstance(modalEl)?.hide(); document.getElementById('uploadZone')?.scrollIntoView({behavior:'smooth', block:'center'});" style="font-size:12px;">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i>Add / Manage
                        </button>
                    </div>
                    <div id="modalAttachmentsContainer" class="d-flex flex-column gap-2 mb-1">
                        {{-- Populated dynamically --}}
                    </div>
                </div>

                {{-- Processing Time & Payment Details --}}
                <div class="rounded-3 p-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary" style="width:28px;height:28px;font-size:13px;flex-shrink:0;">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Processing Time</span>
                            </div>
                            <div class="fw-bold text-dark ps-1 text-break" id="modalProcessingTime" style="font-size:13.5px; word-break: break-word;">—</div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="d-flex align-items-center justify-content-center rounded-2 bg-warning bg-opacity-10 text-warning" style="width:28px;height:28px;font-size:13px;flex-shrink:0;">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Payment Details</span>
                            </div>
                            <div class="ps-1">
                                <div class="fw-bold text-dark text-break" id="modalFee" style="font-size:13.5px; word-break: break-word;">FREE</div>
                                <div class="text-warning fw-bold text-break" id="modalPaymentInstruction" style="font-size:11.5px; word-break: break-word;">Pay at Barangay Office upon pickup</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save edits note --}}
                <div id="reviewSaveBar" class="alert alert-primary d-flex align-items-center gap-2 py-2 px-3 mt-3 rounded-3" style="font-size:12.5px;display:none;">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>You have unsaved edits. Click <strong>Save Changes</strong> to apply before submitting.</span>
                    <button type="button" class="btn btn-primary btn-sm ms-auto px-3 rounded-3 fw-semibold" id="btnSaveReviewEdits" style="font-size:12px;white-space:nowrap;">Save Changes</button>
                </div>

            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" data-bs-dismiss="modal" style="font-size:13px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary rounded-3 fw-semibold flex-grow-1" id="btnConfirmSubmit" style="font-size:13px;">
                    <i class="fa-solid fa-paper-plane me-1"></i> Submit Request
                </button>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // DOCUMENT TYPE CONFIGURATION (DYNAMIC FROM ADMIN)
    // ==========================================
    @php
        $docConfigMap = [];
        $docIconsMap = [];
        foreach ($docTypesList as $dt) {
            if (is_object($dt)) {
                $reqs = $dt->requirements_list ?: ['Valid Government-Issued ID'];
                $docConfigMap[$dt->name] = [
                    'requirements'   => $reqs,
                    'processingTime' => $dt->processing_days ?: '1-2 Business Days',
                    'fee'            => $dt->formatted_fee,
                    'feeAmount'      => (float)($dt->fee ?? 0),
                    'description'    => $dt->description ?: '',
                ];
                $docIconsMap[$dt->name] = $dt->icon;
            } else {
                $docConfigMap[$dt] = [
                    'requirements'   => ['Valid Government-Issued ID'],
                    'processingTime' => '1-2 Business Days',
                    'fee'            => 'FREE',
                    'feeAmount'      => 0,
                    'description'    => '',
                ];
                $docIconsMap[$dt] = 'fa-file-lines';
            }
        }
    @endphp

    const docConfig = @json($docConfigMap);
    const docIcons = @json($docIconsMap);

    // ==========================================
    // MULTIPLE DOCUMENTS STATE & MANAGEMENT
    // ==========================================
    const docHiddenInput = document.getElementById('documentTypeSelect');
    const docsJsonInput  = document.getElementById('documentsJson');
    const purposeField   = document.getElementById('purposeField');
    const docInfoCard    = document.getElementById('docInfoCard');
    const docTypeSearch  = document.getElementById('docTypeSearch');
    const docTypeItems   = document.querySelectorAll('.doc-type-item');
    const docTypeNoResults = document.getElementById('docTypeNoResults');
    let selectedDoc = null;
    let selectedPurpose = '';
    let pendingDocType = null;

    function getDocIcon(type) {
        if (docIcons && docIcons[type]) return docIcons[type];
        const t = (type || '').toLowerCase();
        if (t.includes('business') || t.includes('permit')) return 'fa-store';
        if (t.includes('residency') || t.includes('resident')) return 'fa-house-chimney-user';
        if (t.includes('medical') || t.includes('health') || t.includes('hospital')) return 'fa-kit-medical';
        if (t.includes('indigen')) return 'fa-hand-holding-heart';
        if (t.includes('cohabitation')) return 'fa-people-roof';
        if (t.includes('bail') || t.includes('court') || t.includes('legal')) return 'fa-gavel';
        if (t.includes('electric') || t.includes('power')) return 'fa-bolt';
        if (t.includes('burial') || t.includes('death') || t.includes('funeral')) return 'fa-cross';
        if (t.includes('senior') && (t.includes('cash') || t.includes('assistance') || t.includes('subsidy'))) return 'fa-hand-holding-dollar';
        if (t.includes('senior')) return 'fa-user-tie';
        if (t.includes('job') || t.includes('employment') || t.includes('seeker')) return 'fa-briefcase';
        if (t.includes('clearance')) return 'fa-file-shield';
        if (t.includes('scholarship') || t.includes('school')) return 'fa-graduation-cap';
        if (t.includes('id') || t.includes('card')) return 'fa-id-card';
        return 'fa-file-lines';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Search/filter
    docTypeSearch.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        let visibleCount = 0;
        docTypeItems.forEach(item => {
            const label = item.dataset.value.toLowerCase();
            const show = !q || label.includes(q);
            item.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
        docTypeNoResults.style.display = visibleCount === 0 ? 'block' : 'none';
    });

    // Style for doc cards
    const style = document.createElement('style');
    style.textContent = `
        .doc-type-card { cursor:pointer; transition: all 0.2s ease; background:#fff; }
        .doc-type-card:hover { border-color: var(--color-primary) !important; background: var(--color-primary-50); }
        .doc-type-card.selected { border-color: var(--color-primary) !important; background: var(--color-primary-50); box-shadow: 0 0 0 3px rgba(30,64,175,0.1); }
        .doc-type-card.selected .doc-type-icon { background: var(--color-primary) !important; color: #fff !important; }
        .doc-type-card.selected span.doc-type-name { color: var(--color-primary) !important; }
    `;
    document.head.appendChild(style);

    // ==========================================
    // BRGY CLEARANCE FOR BUSINESS PERMIT — MODAL PURPOSE SELECTION
    // ==========================================
    const BP_DOC = 'Brgy Clearance for Business Permit';
    const bpPurposeError  = document.getElementById('bpPurposeError');
    const bpPurposeOther  = document.getElementById('bpPurposeOther');
    const bpModalEl       = document.getElementById('bpPurposeModal');
    const bpModal         = bpModalEl ? new bootstrap.Modal(bpModalEl) : null;

    // Inject radio hover / selected styles
    const bpStyle = document.createElement('style');
    bpStyle.textContent = `
        .bp-modal-option { background:#fff; transition: all 0.18s ease; }
        .bp-modal-option:hover { border-color: var(--color-primary) !important; background: var(--color-primary-50, #eff6ff); }
        .bp-modal-option.bp-selected { border-color: var(--color-primary) !important; background: var(--color-primary-50, #eff6ff); box-shadow: 0 0 0 3px rgba(30,64,175,0.1); }
        .bp-modal-option.bp-selected .fw-semibold { color: var(--color-primary); }
    `;
    document.head.appendChild(bpStyle);

    // Radio card highlight inside modal
    document.querySelectorAll('.bp-modal-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.bp-modal-option').forEach(lbl => lbl.classList.remove('bp-selected'));
            this.closest('.bp-modal-option').classList.add('bp-selected');
            bpPurposeError.style.display = 'none';
        });
    });

    // Reset modal state
    function resetBpModal() {
        document.querySelectorAll('.bp-modal-radio').forEach(r => r.checked = false);
        document.querySelectorAll('.bp-modal-option').forEach(lbl => lbl.classList.remove('bp-selected'));
        if (bpPurposeOther) bpPurposeOther.value = '';
        if (bpPurposeError) bpPurposeError.style.display = 'none';
    }

    // Confirm BP button
    const bpConfirmBtn = document.getElementById('bpModalConfirm');
    if (bpConfirmBtn) {
        bpConfirmBtn.addEventListener('click', function() {
            const checkedRadio = document.querySelector('.bp-modal-radio:checked');
            const otherVal     = bpPurposeOther ? bpPurposeOther.value.trim().toUpperCase() : '';

            if (!checkedRadio && !otherVal) {
                bpPurposeError.style.display = 'block';
                return;
            }
            bpPurposeError.style.display = 'none';

            const radioVal = checkedRadio ? checkedRadio.value : '';
            const combined = otherVal ? (radioVal ? radioVal + ' — ' + otherVal : otherVal) : radioVal;

            selectedDoc = pendingDocType;
            selectedPurpose = combined;

            if (purposeField) purposeField.value = selectedPurpose;
            if (docHiddenInput) docHiddenInput.value = selectedDoc;
            if (docsJsonInput) docsJsonInput.value = JSON.stringify([{ type: selectedDoc, purpose: selectedPurpose }]);

            if (bpModal) bpModal.hide();
            updateSelectedDocUI();
        });
    }

    // Cancel BP modal
    const bpCancelBtn = document.getElementById('bpModalCancel');
    if (bpCancelBtn) {
        bpCancelBtn.addEventListener('click', function() {
            resetBpModal();
            if (bpModal) bpModal.hide();
        });
    }

    // ==========================================
    // GENERIC PURPOSE MODAL (all docs except BP)
    // ==========================================
    const genericModalEl      = document.getElementById('genericPurposeModal');
    const genericModal        = genericModalEl ? new bootstrap.Modal(genericModalEl) : null;
    const genericPurposeText  = document.getElementById('genericPurposeText');
    const genericPurposeError = document.getElementById('genericPurposeError');
    const genericModalDocName = document.getElementById('genericPurposeModalDocName');

    const gpStyle = document.createElement('style');
    gpStyle.textContent = `
        .gp-chip { user-select:none; }
        .gp-chip:hover { border-color: var(--color-primary) !important; color: var(--color-primary) !important; background: var(--color-primary-50, #eff6ff) !important; }
        .gp-chip.gp-selected { border-color: var(--color-primary) !important; background: var(--color-primary) !important; color: #fff !important; }
    `;
    document.head.appendChild(gpStyle);

    document.querySelectorAll('.gp-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            document.querySelectorAll('.gp-chip').forEach(c => c.classList.remove('gp-selected'));
            this.classList.add('gp-selected');
            const purpose = this.dataset.purpose;
            if (purpose) {
                genericPurposeText.value = purpose.toUpperCase();
            } else {
                genericPurposeText.value = '';
                genericPurposeText.focus();
            }
            genericPurposeError.style.display = 'none';
        });
    });

    genericPurposeText.addEventListener('input', function() {
        if (this.value.trim()) {
            document.querySelectorAll('.gp-chip').forEach(c => c.classList.remove('gp-selected'));
        }
        genericPurposeError.style.display = 'none';
    });

    function resetGenericModal() {
        document.querySelectorAll('.gp-chip').forEach(c => c.classList.remove('gp-selected'));
        if (genericPurposeText) genericPurposeText.value = '';
        if (genericPurposeError) genericPurposeError.style.display = 'none';
    }

    const genericConfirmBtn = document.getElementById('genericModalConfirm');
    if (genericConfirmBtn) {
        genericConfirmBtn.addEventListener('click', function() {
            const val = genericPurposeText ? genericPurposeText.value.trim() : '';
            if (!val) {
                genericPurposeError.style.display = 'block';
                return;
            }
            genericPurposeError.style.display = 'none';

            const purposeVal = val.toUpperCase();
            selectedDoc = pendingDocType;
            selectedPurpose = purposeVal;

            if (purposeField) purposeField.value = selectedPurpose;
            if (docHiddenInput) docHiddenInput.value = selectedDoc;
            if (docsJsonInput) docsJsonInput.value = JSON.stringify([{ type: selectedDoc, purpose: selectedPurpose }]);

            if (genericModal) genericModal.hide();
            updateSelectedDocUI();
        });
    }

    const genericCancelBtn = document.getElementById('genericModalCancel');
    if (genericCancelBtn) {
        genericCancelBtn.addEventListener('click', function() {
            resetGenericModal();
            if (genericModal) genericModal.hide();
        });
    }

    // ==========================================
    // DOCUMENT SELECTION
    // ==========================================
    window.selectDocType = function(docType) {
        pendingDocType = docType;

        docTypeSearch.value = '';
        docTypeItems.forEach(item => item.style.display = '');
        docTypeNoResults.style.display = 'none';

        const isBusiness = docType === BP_DOC || docType === 'Barangay Business Clearance';
        if (isBusiness) {
            const bpSubtitle = document.querySelector('#bpPurposeModal .modal-header p');
            if (bpSubtitle) bpSubtitle.textContent = docType;
            resetBpModal();
            const wasBusiness = selectedDoc === BP_DOC || selectedDoc === 'Barangay Business Clearance';
            if (wasBusiness && selectedPurpose) {
                let matched = false;
                document.querySelectorAll('.bp-modal-radio').forEach(r => {
                    if (selectedPurpose.startsWith(r.value)) {
                        r.checked = true;
                        r.closest('.bp-modal-option')?.classList.add('bp-selected');
                        matched = true;
                        const remainder = selectedPurpose.replace(r.value, '').replace(/^[\s—\-]+/, '');
                        if (bpPurposeOther) bpPurposeOther.value = remainder;
                    }
                });
                if (!matched && bpPurposeOther) {
                    bpPurposeOther.value = selectedPurpose;
                }
            }
            if (bpModal) bpModal.show();
        } else {
            resetGenericModal();
            if (genericModalDocName) genericModalDocName.textContent = docType;
            if (selectedDoc === docType && selectedPurpose) {
                if (genericPurposeText) genericPurposeText.value = selectedPurpose;
                document.querySelectorAll('.gp-chip').forEach(c => {
                    if (c.dataset.purpose && c.dataset.purpose.toUpperCase() === selectedPurpose) {
                        c.classList.add('gp-selected');
                    }
                });
            }
            if (genericModal) genericModal.show();
        }
    };

    function updateSelectedDocUI() {
        if (!selectedDoc) {
            docTypeItems.forEach(item => {
                item.querySelector('.doc-type-card')?.classList.remove('selected');
            });
            if (docInfoCard) docInfoCard.classList.remove('show');
            const checklist = document.getElementById('docRequirementsChecklist');
            if (checklist) checklist.style.display = 'none';
            const summary = document.getElementById('summarySection');
            if (summary) summary.style.display = 'none';
            return;
        }

        // 1. Highlight selected doc card only
        docTypeItems.forEach(item => {
            const card = item.querySelector('.doc-type-card');
            if (card) {
                if (item.dataset.value === selectedDoc) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
        });

        // 2. Update Info card
        if (docInfoCard) {
            const docTitleEl = document.getElementById('docInfoTitle');
            if (docTitleEl) docTitleEl.textContent = selectedDoc;
            const reqEl = document.getElementById('docInfoRequirements');
            const conf = docConfig[selectedDoc];
            if (reqEl) reqEl.innerHTML = (conf && conf.requirements && conf.requirements.length > 0) ? conf.requirements.join(', ') : 'Valid Government ID';
            const timeEl = document.getElementById('docInfoTime');
            if (timeEl) timeEl.textContent = (conf && conf.processingTime) ? conf.processingTime : '1-2 Business Days';
            const feeEl = document.getElementById('docInfoFee');
            if (feeEl) feeEl.textContent = (conf && conf.fee) ? conf.fee : 'FREE';
            docInfoCard.classList.add('show');
        }

        // 3. Update Checklist
        const checklist = document.getElementById('docRequirementsChecklist');
        const items = document.getElementById('reqChecklistItems');
        const conf = docConfig[selectedDoc];
        if (checklist && items) {
            const reqList = (conf && conf.requirements && conf.requirements.length > 0) ? conf.requirements : ['Valid Government-Issued ID'];
            items.innerHTML = reqList.map(r => `
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" disabled checked>
                    <label class="form-check-label small">${escapeHtml(r)}</label>
                </div>
            `).join('');
            checklist.style.display = 'block';
        }

        // 4. Update Modal Processing Time and Fee
        const modalProcessingTime = document.getElementById('modalProcessingTime');
        if (modalProcessingTime) {
            modalProcessingTime.textContent = (conf && conf.processingTime) ? conf.processingTime : '1-2 Business Days';
        }
        const modalFee = document.getElementById('modalFee');
        if (modalFee) {
            if (conf && conf.fee && conf.fee !== 'FREE') {
                modalFee.innerHTML = `<span class="text-primary fw-bold">${conf.fee}</span>`;
            } else {
                modalFee.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size:11px;">FREE</span>`;
            }
        }
    }

    // Initialize from old() input or URL parameters
    try {
        const preselected = (docHiddenInput ? docHiddenInput.value.trim() : '') || (docsJsonInput && docsJsonInput.value ? JSON.parse(docsJsonInput.value)?.[0]?.type : '');
        if (preselected) {
            selectedDoc = preselected;
            selectedPurpose = (purposeField && purposeField.value ? purposeField.value.trim() : '') || (docsJsonInput && docsJsonInput.value ? JSON.parse(docsJsonInput.value)?.[0]?.purpose : '');
            if (docHiddenInput) docHiddenInput.value = selectedDoc;
            if (purposeField) purposeField.value = selectedPurpose;
            updateSelectedDocUI();
        }
    } catch(e) {}


    // ==========================================
    // FILE UPLOAD HANDLING
    // ==========================================
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const uploadError = document.getElementById('uploadError');
    let uploadedFiles = [];
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_TYPES = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    const ALLOWED_EXTENSIONS = ['.pdf', '.jpg', '.jpeg', '.png'];

    // Drag and drop
    ['dragenter', 'dragover'].forEach(evt => {
        uploadZone.addEventListener(evt, (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        uploadZone.addEventListener(evt, (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
        });
    });

    uploadZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    uploadZone.addEventListener('click', (e) => {
        if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
        this.value = ''; // Allow re-selecting same file
    });

    function getDocTypeOptionList() {
        const standardTypes = [
            'Valid Government ID',
            'Proof of Residence',
            'Medical Certificate or Prescription',
            'Barangay Certificate',
            'Senior Citizen ID',
            'School ID / Enrollment Form',
            'Supporting Document'
        ];
        if (selectedDoc) {
            const conf = docConfig[selectedDoc];
            if (conf && conf.requirements) {
                conf.requirements.forEach(r => {
                    if (!standardTypes.includes(r)) {
                        standardTypes.unshift(r);
                    }
                });
            }
        }
        return standardTypes;
    }

    function handleFiles(files) {
        uploadError.style.display = 'none';
        const errors = [];

        Array.from(files).forEach(file => {
            const ext = '.' + file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXTENSIONS.includes(ext)) {
                errors.push(`"${file.name}" — Invalid file type. Only PDF, JPG, JPEG, PNG allowed.`);
                return;
            }
            if (file.size > MAX_FILE_SIZE) {
                errors.push(`"${file.name}" — File too large (${formatFileSize(file.size)}). Max 5MB.`);
                return;
            }
            // Check duplicate
            if (uploadedFiles.some(f => f.name === file.name && f.size === file.size)) {
                errors.push(`"${file.name}" — File already added.`);
                return;
            }

            // Assign smart default docType
            const availableReqs = [];
            if (selectedDoc) {
                const conf = docConfig[selectedDoc];
                if (conf && conf.requirements) {
                    conf.requirements.forEach(r => {
                        if (!availableReqs.includes(r)) availableReqs.push(r);
                    });
                }
            }
            let defaultType = 'Valid Government ID';
            if (availableReqs[uploadedFiles.length]) {
                defaultType = availableReqs[uploadedFiles.length];
            } else if (availableReqs.length > 0) {
                defaultType = availableReqs[0];
            }
            file.docType = defaultType;

            uploadedFiles.push(file);
        });

        if (errors.length > 0) {
            uploadError.innerHTML = errors.map(e => `<div><i class="fa-solid fa-circle-exclamation me-1"></i> ${e}</div>`).join('');
            uploadError.style.display = 'block';
        }

        renderFileList();
        syncFileInput();
    }

    window.updateFileDocType = function(index, newType) {
        if (uploadedFiles[index]) {
            uploadedFiles[index].docType = newType;
            updateSummaryAttachments();
        }
    };

    function renderFileList() {
        if (uploadedFiles.length === 0) {
            fileList.innerHTML = '';
            updateSummaryAttachments();
            return;
        }

        const typeOptions = getDocTypeOptionList();

        fileList.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Attached Files (${uploadedFiles.length})</span>
                <span class="text-muted small" style="font-size:11px;">Select document type for each attachment</span>
            </div>
        `;

        uploadedFiles.forEach((file, index) => {
            const ext = file.name.split('.').pop().toLowerCase();
            const isImg = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);
            const icon = ext === 'pdf' ? 'fa-file-pdf' : 'fa-file-image';
            const typeLabel = ext.toUpperCase() + (ext === 'pdf' ? ' Document' : ' Image');
            const thumbUrl = isImg ? URL.createObjectURL(file) : '';
            const currentDocType = file.docType || typeOptions[0];

            const optionsHtml = typeOptions.map(t => `
                <option value="${escapeHtml(t)}" ${t === currentDocType ? 'selected' : ''}>${escapeHtml(t)}</option>
            `).join('');

            fileList.innerHTML += `
                <div class="file-item d-flex align-items-center gap-3 p-2 px-3 border rounded-3 bg-white mb-2" data-index="${index}">
                    <div class="d-flex align-items-center justify-content-center rounded-2 border ${isImg ? '' : 'bg-light'}" style="width:42px;height:42px;flex-shrink:0;overflow:hidden;">
                        ${isImg ? `
                            <img src="${thumbUrl}" alt="Preview" style="width:100%;height:100%;object-fit:cover;">
                        ` : `
                            <i class="fa-solid ${icon} ${ext === 'pdf' ? 'text-danger' : 'text-primary'}" style="font-size:18px;"></i>
                        `}
                    </div>
                    <div class="file-info flex-grow-1 min-w-0">
                        <div class="file-name text-truncate fw-bold text-dark" style="font-size:13px;" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0.5" style="font-size:10.5px;">
                                ${typeLabel} · ${formatFileSize(file.size)}
                            </span>
                            <div class="d-inline-flex align-items-center gap-1">
                                <label class="text-muted small mb-0" style="font-size:11px;"><i class="fa-solid fa-tag text-primary me-1"></i>Type:</label>
                                <select class="form-select form-select-sm py-0 ps-2 pe-4 rounded-pill border-primary border-opacity-25" style="font-size:11.5px;height:24px;width:auto;display:inline-block;background-color:#f0f7ff;" onchange="updateFileDocType(${index}, this.value)">
                                    ${optionsHtml}
                                </select>
                            </div>
                        </div>
                    </div>
                    <span class="file-remove btn btn-sm btn-link text-danger p-1" onclick="removeFile(${index})" title="Remove file">
                        <i class="fa-solid fa-xmark" style="font-size:16px;"></i>
                    </span>
                </div>`;
        });

        updateSummaryAttachments();
    }

    function updateSummaryAttachments() {
        const summaryAttachmentsSection = document.getElementById('summaryAttachmentsSection');
        const summaryAttachmentCount = document.getElementById('summaryAttachmentCount');
        const summaryAttachmentsList = document.getElementById('summaryAttachmentsList');

        if (!summaryAttachmentsSection || !summaryAttachmentsList) return;

        if (summaryAttachmentCount) {
            summaryAttachmentCount.textContent = uploadedFiles.length;
        }

        if (uploadedFiles.length === 0) {
            summaryAttachmentsList.innerHTML = `<span class="text-muted small fst-italic">None uploaded yet (optional)</span>`;
        } else {
            summaryAttachmentsList.innerHTML = uploadedFiles.map(f => {
                const ext = f.name.split('.').pop().toLowerCase();
                const icon = ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-image text-primary';
                const typeLabel = ext.toUpperCase() + (ext === 'pdf' ? ' Document' : ' Image');
                const docType = f.docType || 'Supporting Document';
                return `
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-white border" style="font-size:12px;">
                        <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                            <i class="fa-solid ${icon}"></i>
                            <div class="min-w-0 flex-grow-1">
                                <span class="fw-semibold text-dark text-truncate d-block" style="font-size:12px;" title="${escapeHtml(f.name)}">${escapeHtml(f.name)}</span>
                                <div class="text-muted" style="font-size:11px;">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5 me-1" style="font-size:9.5px;">${escapeHtml(docType)}</span>
                                    <span>${typeLabel} · ${formatFileSize(f.size)}</span>
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" style="font-size:9.5px;"><i class="fa-solid fa-check me-1"></i>Attached</span>
                    </div>
                `;
            }).join('');
        }
    }

    window.removeFile = function(index) {
        uploadedFiles.splice(index, 1);
        renderFileList();
        syncFileInput();
    };

    function syncFileInput() {
        // Rebuild the file input with a DataTransfer object
        const dt = new DataTransfer();
        uploadedFiles.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    // ==========================================
    // SUMMARY VISIBILITY
    // ==========================================
    function updateSummaryVisibility() {
        const summary = document.getElementById('summarySection');
        if (!summary) return;

        const sumPurpose = document.getElementById('summaryPurpose');
        if (sumPurpose && purposeField) {
            sumPurpose.textContent = purposeField.value.trim();
        }

        const hasDocs = !!selectedDoc || (docHiddenInput && docHiddenInput.value.trim());
        if (hasDocs) {
            summary.style.display = 'block';
        } else {
            summary.style.display = 'none';
        }
    }

    // ==========================================
    // PERSONAL INFORMATION LOGIC & TWO-WAY SYNC
    // ==========================================
    const piFullName = document.getElementById('piFullName');
    const piContactNumber = document.getElementById('piContactNumber');
    const piAddress = document.getElementById('piAddress');
    const piEmail = document.getElementById('piEmail');

    const piFirstName = document.getElementById('piFirstName');
    const piMiddleInitial = document.getElementById('piMiddleInitial');
    const piLastName = document.getElementById('piLastName');

    const piPurok = document.getElementById('piPurok');
    const piStreet = document.getElementById('piStreet');
    const piCity = document.getElementById('piCity');
    const piAge = document.getElementById('piAge');

    // Toggles for Detailed Breakdown
    const toggleNameBtn = document.getElementById('toggleNameBreakdown');
    const nameContainer = document.getElementById('nameBreakdownContainer');
    const toggleNameText = document.getElementById('toggleNameText');

    if (toggleNameBtn && nameContainer) {
        toggleNameBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isHidden = nameContainer.style.display === 'none';
            nameContainer.style.display = isHidden ? 'block' : 'none';
            toggleNameText.textContent = isHidden ? 'Hide Name Fields' : 'Separate Name Fields';
            if (isHidden && piFirstName) piFirstName.focus();
        });
    }

    const toggleAddressBtn = document.getElementById('toggleAddressBreakdown');
    const addressContainer = document.getElementById('addressBreakdownContainer');
    const toggleAddressText = document.getElementById('toggleAddressText');

    if (toggleAddressBtn && addressContainer) {
        toggleAddressBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isHidden = addressContainer.style.display === 'none';
            addressContainer.style.display = isHidden ? 'block' : 'none';
            toggleAddressText.textContent = isHidden ? 'Hide Address Fields' : 'Separate Purok/Sitio';
            if (isHidden && piPurok) piPurok.focus();
        });
    }

    // Sync from Full Name to First / Middle / Last
    let isInternalNameSync = false;
    if (piFullName) {
        piFullName.addEventListener('input', function() {
            if (isInternalNameSync) return;
            this.classList.remove('is-invalid');
            const nameVal = this.value.trim();
            const summaryResident = document.getElementById('summaryResident');
            if (summaryResident) summaryResident.textContent = nameVal || '—';

            const parts = nameVal.split(/\s+/).filter(Boolean);
            if (parts.length === 1) {
                if (piFirstName) piFirstName.value = parts[0];
                if (piMiddleInitial) piMiddleInitial.value = '';
                if (piLastName) piLastName.value = '';
            } else if (parts.length === 2) {
                if (piFirstName) piFirstName.value = parts[0];
                if (piMiddleInitial) piMiddleInitial.value = '';
                if (piLastName) piLastName.value = parts[1];
            } else if (parts.length >= 3) {
                if (piFirstName) piFirstName.value = parts[0];
                const second = parts[1].replace(/\./g, '');
                if (second.length <= 2) {
                    if (piMiddleInitial) piMiddleInitial.value = second.toUpperCase();
                    if (piLastName) piLastName.value = parts.slice(2).join(' ');
                } else {
                    if (piMiddleInitial) piMiddleInitial.value = parts[1].charAt(0).toUpperCase();
                    if (piLastName) piLastName.value = parts.slice(2).join(' ');
                }
            }
        });
    }

    // Enforce CapsLock / uppercase on name and address inputs
    [
        piFullName, piFirstName, piMiddleInitial, piLastName, piAddress, piPurok, piStreet,
        document.getElementById('reviewNameInput'),
        document.getElementById('reviewAddressInput'),
        document.getElementById('reviewPurposeInput')
    ].forEach(el => {
        if (!el) return;
        el.addEventListener('input', function() {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            if (typeof this.value === 'string') {
                this.value = this.value.toUpperCase();
            }
            if (start !== null && end !== null) {
                this.setSelectionRange(start, end);
            }
        });
    });

    // Sync from First / Middle / Last to Full Name
    function syncToFullName() {
        if (!piFirstName || !piLastName || !piFullName) return;
        isInternalNameSync = true;
        const f = piFirstName.value.trim().toUpperCase();
        const m = piMiddleInitial ? piMiddleInitial.value.trim().toUpperCase() : '';
        const l = piLastName.value.trim().toUpperCase();
        const miStr = m ? (m.endsWith('.') ? m : m + '.') : '';
        const combined = [f, miStr, l].filter(Boolean).join(' ').toUpperCase();
        piFullName.value = combined;
        piFullName.classList.remove('is-invalid');
        const summaryResident = document.getElementById('summaryResident');
        if (summaryResident) summaryResident.textContent = combined || '—';
        isInternalNameSync = false;
    }

    if (piFirstName) piFirstName.addEventListener('input', syncToFullName);
    if (piMiddleInitial) piMiddleInitial.addEventListener('input', syncToFullName);
    if (piLastName) piLastName.addEventListener('input', syncToFullName);

    // Sync Address to Breakdown & Summary
    if (piAddress) {
        piAddress.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const addr = this.value.trim().toUpperCase();
            const match = addr.match(/purok\s*[\w\d]+/i);
            if (match && piPurok) {
                piPurok.value = match[0].toUpperCase();
            }
            if (piStreet) {
                piStreet.value = addr;
            }
        });
    }

    function syncToAddress() {
        if (!piAddress) return;
        const p = piPurok ? piPurok.value.trim().toUpperCase() : '';
        const s = piStreet ? piStreet.value.trim().toUpperCase() : '';
        const parts = [];
        if (p) parts.push(p);
        if (s && s.toLowerCase() !== p.toLowerCase()) parts.push(s);
        parts.push('BRGY. COGON EAST, CARMEN, CEBU');
        piAddress.value = parts.join(', ').toUpperCase();
        piAddress.classList.remove('is-invalid');
    }

    if (piPurok) piPurok.addEventListener('input', syncToAddress);
    if (piStreet) piStreet.addEventListener('input', syncToAddress);

    // Reset to Account Details
    const accountDetails = {
        fullName: @json(strtoupper($fullName ?? '')),
        contactNumber: @json($user->phone_number ?? ''),
        address: @json(strtoupper($address ?? '')),
        email: @json($user->email ?? ''),
        firstName: @json(strtoupper($user->first_name ?? '')),
        middleInitial: @json($user->middle_name ? strtoupper(substr($user->middle_name, 0, 1)) : ''),
        lastName: @json(strtoupper($user->last_name ?? '')),
        purok: @json(strtoupper($user->purok ?? '')),
        street: @json(strtoupper($user->sitio ?? $user->street ?? '')),
        age: @json($user->age ?? '')
    };

    const btnReset = document.getElementById('btnResetAccountDetails');
    const resetBtnLabel = document.getElementById('resetBtnLabel');
    if (btnReset) {
        btnReset.addEventListener('click', function() {
            if (piFullName) piFullName.value = accountDetails.fullName;
            if (piContactNumber) piContactNumber.value = accountDetails.contactNumber;
            if (piAddress) piAddress.value = accountDetails.address;
            if (piEmail) piEmail.value = accountDetails.email;

            if (piFirstName) piFirstName.value = accountDetails.firstName;
            if (piMiddleInitial) piMiddleInitial.value = accountDetails.middleInitial;
            if (piLastName) piLastName.value = accountDetails.lastName;

            if (piPurok) piPurok.value = accountDetails.purok;
            if (piStreet) piStreet.value = accountDetails.street;
            if (piAge) piAge.value = accountDetails.age;

            [piFullName, piContactNumber, piAddress, piEmail].forEach(el => {
                if (el) el.classList.remove('is-invalid');
            });
            const summaryResident = document.getElementById('summaryResident');
            if (summaryResident) summaryResident.textContent = accountDetails.fullName || '—';

            // Visual confirmation animation
            btnReset.classList.remove('btn-outline-primary');
            btnReset.classList.add('btn-success');
            if (resetBtnLabel) resetBtnLabel.innerHTML = '<i class="fa-solid fa-check me-1"></i> Reset!';
            setTimeout(() => {
                btnReset.classList.remove('btn-success');
                btnReset.classList.add('btn-outline-primary');
                if (resetBtnLabel) resetBtnLabel.textContent = 'Reset to Account';
            }, 1800);
        });
    }

    // Remove invalid state on typing
    if (piContactNumber) {
        piContactNumber.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    }

    // ==========================================
    // FORM VALIDATION & SUBMIT
    // ==========================================
    const confirmModalEl = document.getElementById('confirmSubmitModal');
    const confirmModal = confirmModalEl ? new bootstrap.Modal(confirmModalEl) : null;
    let isSubmitting = false;

    document.getElementById('btnOpenConfirmModal').addEventListener('click', function() {
        let valid = true;

        // Validate Personal Info
        if (piFullName && !piFullName.value.trim()) {
            piFullName.classList.add('is-invalid');
            piFullName.focus();
            valid = false;
        } else if (piFullName) {
            piFullName.classList.remove('is-invalid');
        }

        if (piContactNumber && !piContactNumber.value.trim()) {
            piContactNumber.classList.add('is-invalid');
            if (valid) piContactNumber.focus();
            valid = false;
        } else if (piContactNumber) {
            piContactNumber.classList.remove('is-invalid');
        }

        if (piAddress && !piAddress.value.trim()) {
            piAddress.classList.add('is-invalid');
            if (valid) piAddress.focus();
            valid = false;
        } else if (piAddress) {
            piAddress.classList.remove('is-invalid');
        }

        // Validate Document Selection (require 1 document)
        const docTypeGrid = document.getElementById('docTypeGrid');
        if (!selectedDoc) {
            docTypeGrid.style.outline = '2px solid #ef4444';
            docTypeGrid.style.borderRadius = '12px';
            if (valid) docTypeGrid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            valid = false;
        } else {
            docTypeGrid.style.outline = '';
        }

        if (!valid) return;

        // Populate confirmation modal preview
        const modalResident = document.getElementById('modalResidentName');
        const modalDoc = document.getElementById('modalDocType');
        const modalContact = document.getElementById('modalContact');
        const modalAddr = document.getElementById('modalAddress');
        const modalPurpose = document.getElementById('modalPurpose');

        if (modalResident) modalResident.textContent = piFullName ? piFullName.value.trim().toUpperCase() : '—';
        if (modalDoc) modalDoc.textContent = selectedDoc || '—';
        if (modalContact) modalContact.textContent = piContactNumber ? piContactNumber.value.trim() : '—';
        if (modalAddr) modalAddr.textContent = piAddress ? piAddress.value.trim().toUpperCase() : '—';
        if (modalPurpose) modalPurpose.textContent = purposeField ? purposeField.value.trim().toUpperCase() : '—';

        // Populate Processing Time & Payment Details preview in modal
        const modalProcessingTime = document.getElementById('modalProcessingTime');
        const modalFee = document.getElementById('modalFee');
        const modalPaymentInstruction = document.getElementById('modalPaymentInstruction');

        const conf = (selectedDoc && typeof docConfig !== 'undefined') ? docConfig[selectedDoc] : null;
        if (modalProcessingTime) {
            modalProcessingTime.textContent = (conf && conf.processingTime) ? conf.processingTime : '1-2 Business Days';
        }
        if (modalFee) {
            if (conf && conf.fee && conf.fee !== 'FREE') {
                modalFee.innerHTML = `<span class="text-primary fw-bold">${conf.fee}</span>`;
            } else {
                modalFee.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size:11px;">FREE</span>`;
            }
        }
        if (modalPaymentInstruction) {
            modalPaymentInstruction.textContent = 'Pay at Barangay Office upon pickup';
        }

        // Populate Uploaded Documents preview
        const modalAttachmentsContainer = document.getElementById('modalAttachmentsContainer');
        const modalAttachmentCount = document.getElementById('modalAttachmentCount');
        if (modalAttachmentCount) {
            modalAttachmentCount.textContent = uploadedFiles.length;
        }

        if (modalAttachmentsContainer) {
            if (uploadedFiles.length === 0) {
                modalAttachmentsContainer.innerHTML = `
                    <div class="p-2 px-3 rounded-3 border bg-light text-muted small d-flex align-items-center justify-content-between">
                        <span><i class="fa-solid fa-circle-info me-1 text-secondary"></i> No supporting documents uploaded.</span>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" onclick="const modalEl = document.getElementById('confirmSubmitModal'); bootstrap.Modal.getInstance(modalEl)?.hide(); document.getElementById('uploadZone')?.scrollIntoView({behavior:'smooth', block:'center'});" style="font-size:11px;">
                            <i class="fa-solid fa-folder-open me-1"></i>Upload Files
                        </button>
                    </div>
                `;
            } else {
                modalAttachmentsContainer.innerHTML = uploadedFiles.map((f, i) => {
                    const ext = f.name.split('.').pop().toLowerCase();
                    const isImg = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);
                    const icon = ext === 'pdf' ? 'fa-file-pdf' : 'fa-file-image';
                    const typeLabel = ext.toUpperCase() + (ext === 'pdf' ? ' Document' : ' Image');
                    const thumbUrl = isImg ? URL.createObjectURL(f) : '';
                    const docType = f.docType || 'Supporting Document';

                    return `
                        <div class="attachment-preview-card p-2.5 px-3 rounded-3 border bg-light d-flex justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                                <div class="d-flex align-items-center justify-content-center rounded-2 border bg-white" style="width:42px;height:42px;flex-shrink:0;overflow:hidden;">
                                    ${isImg && thumbUrl ? `
                                        <img src="${thumbUrl}" alt="Preview" style="width:100%;height:100%;object-fit:cover;">
                                    ` : `
                                        <i class="fa-solid ${icon} ${ext === 'pdf' ? 'text-danger' : 'text-primary'}" style="font-size:18px;"></i>
                                    `}
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold text-dark text-break" style="font-size:13.5px; word-break:break-word; overflow-wrap:anywhere; line-height:1.35;" title="${escapeHtml(f.name)}">
                                        ${escapeHtml(f.name)}
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1" style="font-size:11.5px;">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5 text-break" style="max-width:100%; white-space:normal; text-align:left; word-break:break-word;">
                                            <i class="fa-solid fa-tag me-1"></i>${escapeHtml(docType)}
                                        </span>
                                        <span class="text-muted text-nowrap">${typeLabel} · ${formatFileSize(f.size)}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1.5 flex-shrink-0" style="font-size:11px;">
                                <i class="fa-solid fa-check me-1"></i>Ready
                            </span>
                        </div>
                    `;
                }).join('');
            }
        }

        // Pre-fill the edit inputs with current values so they are ready
        const reviewNameInput    = document.getElementById('reviewNameInput');
        const reviewContactInput = document.getElementById('reviewContactInput');
        const reviewAddressInput = document.getElementById('reviewAddressInput');
        const reviewPurposeInput = document.getElementById('reviewPurposeInput');
        if (reviewNameInput)    reviewNameInput.value    = piFullName      ? piFullName.value.trim().toUpperCase()      : '';
        if (reviewContactInput) reviewContactInput.value = piContactNumber ? piContactNumber.value.trim() : '';
        if (reviewAddressInput) reviewAddressInput.value = piAddress       ? piAddress.value.trim().toUpperCase()       : '';
        if (reviewPurposeInput) reviewPurposeInput.value = purposeField    ? purposeField.value.trim().toUpperCase()    : '';

        // Reset all edit rows to display mode when modal re-opens
        ['reviewName','reviewContact','reviewAddress','reviewPurpose'].forEach(key => {
            const dispEl = document.getElementById(key + 'Display');
            const editEl = document.getElementById(key + 'Edit');
            const btn    = document.querySelector(`.review-edit-btn[data-target="${key}"]`);
            if (dispEl) dispEl.style.display = '';
            if (editEl) editEl.style.display  = 'none';
            if (btn)    btn.innerHTML = '<i class="fa-solid fa-pen me-1"></i>Edit';
        });
        // Hide save bar
        const reviewSaveBar = document.getElementById('reviewSaveBar');
        if (reviewSaveBar) reviewSaveBar.style.display = 'none';

        // Update summary visibility
        updateSummaryVisibility();

        // Open confirmation modal
        if (confirmModal) {
            confirmModal.show();
        } else {
            const modalEl = document.getElementById('confirmSubmitModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
                inst.show();
            } else {
                document.getElementById('docRequestForm').submit();
            }
        }
    });

    // ==========================================
    // REVIEW MODAL — EDIT BUTTON LOGIC
    // ==========================================
    // Map each field key → its display span, edit container, and input element
    const reviewFieldMap = {
        reviewName:    { display: 'modalResidentName', editBox: 'reviewNameEdit',    input: 'reviewNameInput',    formField: () => piFullName },
        reviewContact: { display: 'modalContact',      editBox: 'reviewContactEdit', input: 'reviewContactInput', formField: () => piContactNumber },
        reviewAddress: { display: 'modalAddress',      editBox: 'reviewAddressEdit', input: 'reviewAddressInput', formField: () => piAddress },
        reviewPurpose: { display: 'modalPurpose',      editBox: 'reviewPurposeEdit', input: 'reviewPurposeInput', formField: () => purposeField },
    };

    let reviewHasDirty = false;

    function showReviewSaveBar(show) {
        const bar = document.getElementById('reviewSaveBar');
        if (!bar) return;
        bar.style.display = show ? 'flex' : 'none';
    }

    // Attach click handlers to each Edit button
    document.querySelectorAll('.review-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const key      = this.dataset.target;                          // e.g. "reviewName"
            const map      = reviewFieldMap[key];
            if (!map) return;

            const dispEl   = document.getElementById(key + 'Display');
            const editEl   = document.getElementById(map.editBox);
            const inputEl  = document.getElementById(map.input);
            const isEditing = editEl && editEl.style.display !== 'none';

            if (isEditing) {
                // "Done" clicked — validate & apply
                let newVal = inputEl ? inputEl.value.trim() : '';
                if (!newVal) {
                    if (inputEl) {
                        inputEl.classList.add('is-invalid');
                        inputEl.focus();
                    }
                    return;
                }
                if (['reviewName', 'reviewAddress', 'reviewPurpose'].includes(key)) {
                    newVal = newVal.toUpperCase();
                    if (inputEl) inputEl.value = newVal;
                }
                if (inputEl) inputEl.classList.remove('is-invalid');

                // Update the display span
                const spanEl = document.getElementById(map.display);
                if (spanEl) spanEl.textContent = newVal;

                // Hide edit, show display
                if (editEl) editEl.style.display = 'none';
                if (dispEl) dispEl.style.display = '';

                // Flip button back to Edit
                this.innerHTML = '<i class="fa-solid fa-pen me-1"></i>Edit';

                // Mark as dirty so save bar shows
                reviewHasDirty = true;
                showReviewSaveBar(true);
            } else {
                // "Edit" clicked — show the edit field
                if (dispEl) dispEl.style.display = 'none';
                if (editEl) editEl.style.display = '';
                if (inputEl) {
                    inputEl.focus();
                    inputEl.select();
                }
                this.innerHTML = '<i class="fa-solid fa-check me-1"></i>Done';
            }
        });
    });

    // "Save Changes" button — write all edited values back to the real form fields
    const btnSaveReviewEdits = document.getElementById('btnSaveReviewEdits');
    if (btnSaveReviewEdits) {
        btnSaveReviewEdits.addEventListener('click', function() {
            // For any row still in "edit" mode, finalize it first
            document.querySelectorAll('.review-edit-btn').forEach(btn => {
                const key   = btn.dataset.target;
                const map   = reviewFieldMap[key];
                if (!map) return;
                const editEl  = document.getElementById(map.editBox);
                const inputEl = document.getElementById(map.input);
                const dispEl  = document.getElementById(key + 'Display');
                const spanEl  = document.getElementById(map.display);
                if (editEl && editEl.style.display !== 'none') {
                    let val = inputEl ? inputEl.value.trim() : '';
                    if (val) {
                        if (['reviewName', 'reviewAddress', 'reviewPurpose'].includes(key)) {
                            val = val.toUpperCase();
                            if (inputEl) inputEl.value = val;
                        }
                        if (spanEl) spanEl.textContent = val;
                        if (editEl) editEl.style.display = 'none';
                        if (dispEl) dispEl.style.display = '';
                        btn.innerHTML = '<i class="fa-solid fa-pen me-1"></i>Edit';
                    }
                }
            });

            // Write display span values back to the real form inputs
            Object.entries(reviewFieldMap).forEach(([key, map]) => {
                const spanEl     = document.getElementById(map.display);
                const formField  = map.formField();
                if (spanEl && formField && spanEl.textContent.trim() !== '—') {
                    let v = spanEl.textContent.trim();
                    if (['reviewName', 'reviewAddress', 'reviewPurpose'].includes(key)) {
                        v = v.toUpperCase();
                    }
                    formField.value = v;
                    if (formField === piFullName) {
                        piFullName.dispatchEvent(new Event('input'));
                    } else if (formField === piAddress) {
                        piAddress.dispatchEvent(new Event('input'));
                    }
                }
            });

            // Confirm animation
            this.innerHTML = '<i class="fa-solid fa-check me-1"></i>Saved!';
            this.classList.remove('btn-primary');
            this.classList.add('btn-success');
            reviewHasDirty = false;

            setTimeout(() => {
                showReviewSaveBar(false);
                this.innerHTML = 'Save Changes';
                this.classList.remove('btn-success');
                this.classList.add('btn-primary');
            }, 1800);
        });
    }

    document.getElementById('btnConfirmSubmit').addEventListener('click', function() {
        if (isSubmitting) return;

        // Flush any pending review edits (including rows still in "edit" mode) to the real form
        Object.entries(reviewFieldMap).forEach(([key, map]) => {
            const editEl  = document.getElementById(map.editBox);
            const inputEl = document.getElementById(map.input);
            const spanEl  = document.getElementById(map.display);
            const formField = map.formField();
            if (!formField) return;
            // If still in edit mode, grab directly from input
            if (editEl && editEl.style.display !== 'none' && inputEl && inputEl.value.trim()) {
                let v = inputEl.value.trim();
                if (['reviewName', 'reviewAddress', 'reviewPurpose'].includes(key)) {
                    v = v.toUpperCase();
                }
                formField.value = v;
            } else if (spanEl && spanEl.textContent.trim() && spanEl.textContent.trim() !== '—') {
                let v = spanEl.textContent.trim();
                if (['reviewName', 'reviewAddress', 'reviewPurpose'].includes(key)) {
                    v = v.toUpperCase();
                }
                formField.value = v;
            }
            if (formField === piFullName) {
                piFullName.dispatchEvent(new Event('input'));
            } else if (formField === piAddress) {
                piAddress.dispatchEvent(new Event('input'));
            }
        });

        // Ensure single document and purpose syncs back to hidden inputs
        if (selectedDoc) {
            if (docHiddenInput) docHiddenInput.value = selectedDoc;
            if (purposeField && purposeField.value) {
                selectedPurpose = purposeField.value.trim().toUpperCase();
            }
            if (docsJsonInput) {
                docsJsonInput.value = JSON.stringify([{ type: selectedDoc, purpose: selectedPurpose }]);
            }
        }

        // Serialize attachment metadata
        const attachmentMeta = uploadedFiles.map(f => ({
            name: f.name,
            doc_type: f.docType || 'Supporting Document',
            file_type: f.type || ('.' + f.name.split('.').pop().toLowerCase()),
            size: f.size
        }));
        const metaInput = document.getElementById('attachmentMetaJson');
        if (metaInput) metaInput.value = JSON.stringify(attachmentMeta);

        isSubmitting = true;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting...';

        // Also disable the main button
        const mainBtn = document.getElementById('btnOpenConfirmModal');
        if (mainBtn) {
            mainBtn.disabled = true;
            mainBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting...';
        }

        if (confirmModal) confirmModal.hide();
        document.getElementById('docRequestForm').submit();
    });

    // Clear red outline on doc selection
    document.getElementById('docTypeGrid').addEventListener('click', function() {
        this.style.outline = '';
    });
});

// =============================================
// LIFECYCLE TRACKER — Live polling updater
// =============================================
(function() {
    const STATUS_STEP_MAP = {
        'Pending':      1,
        'Under Review': 2,
        'Processing':   3,
        'Ready':        4,
        'Completed':    5,
        'Rejected':     0,
    };
    const STEP_LABELS = {
        1: { label: 'Submit Request',  sub: 'Initial Submission' },
        2: { label: 'Verification',    sub: 'Staff Review' },
        3: { label: 'Processing',      sub: 'Document Generation' },
        4: { label: 'Ready for Pickup',sub: 'SMS Notification' },
        5: { label: 'Claim Document',  sub: 'Office Counter' },
    };
    const STEP_BADGE_LABELS = {
        0: 'Request Rejected',
        1: 'Step 1 of 5: Submit Request',
        2: 'Step 2 of 5: Verification',
        3: 'Step 3 of 5: Processing',
        4: 'Step 4 of 5: Ready for Pickup',
        5: 'Step 5 of 5: Claim Document',
    };

    let lastKnownStatus = @json($latestRequest?->status ?? null);

    function updateTracker(status, trackingNumber) {
        const isRejected = status === 'Rejected';
        const step = STATUS_STEP_MAP[status] ?? 1;
        const fillPercent = isRejected ? 0 : Math.max(0, ((step - 1) / 4) * 100);

        // Update progress fill bar
        const fill = document.getElementById('lifecycle-fill');
        if (fill) fill.style.width = fillPercent + '%';

        // Update each stepper step
        document.querySelectorAll('#lifecycle-stepper .stepper-step').forEach(el => {
            const stepNum = parseInt(el.dataset.step, 10);
            el.classList.remove('active', 'completed', 'rejected');

            const circle = el.querySelector('.stepper-circle');
            if (!circle) return;

            if (isRejected && stepNum === step) {
                el.classList.add('rejected');
                circle.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            } else if (stepNum < step) {
                el.classList.add('completed');
                circle.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else if (stepNum === step && !isRejected) {
                el.classList.add('active');
                circle.innerHTML = stepNum;
            } else {
                circle.innerHTML = stepNum;
            }
        });

        // Update badge
        const badge = document.getElementById('lifecycle-badge');
        if (badge) {
            badge.textContent = isRejected ? 'Request Rejected' : (STEP_BADGE_LABELS[step] || '');
            badge.className = 'badge fw-bold ' + (isRejected
                ? 'bg-danger bg-opacity-10 text-danger'
                : 'bg-primary bg-opacity-10 text-primary');
            badge.style.fontSize = '10.5px';
        }
    }

    @if($latestRequest)
    // Poll every 8 seconds
    setInterval(function() {
        fetch('{{ route("resident.requests.check") }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            const newStatus = data.latest_status ?? data.status ?? null;
            if (newStatus && newStatus !== lastKnownStatus) {
                lastKnownStatus = newStatus;
                updateTracker(newStatus, data.tracking_number ?? '');
            }
        })
        .catch(() => {}); // silent fail — tracker updates on next successful poll
    }, 8000);
    @endif
})();
</script>
@endsection