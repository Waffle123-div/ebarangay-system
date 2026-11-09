@extends('resident.layouts.resident')

@section('content')
@php
    $user = auth()->user();
    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ? substr($user->middle_name, 0, 1) . '. ' : '') . ($user->last_name ?? ''));
    $address = trim(($user->purok ? $user->purok . ', ' : '') . 'Brgy. Cogon East, Carmen, Cebu');
    
    // Auto-calculate age from birthdate if available, otherwise fallback to age column
    $calculatedAge = $user->age;
    if (empty($calculatedAge) && !empty($user->birthdate)) {
        $calculatedAge = \Carbon\Carbon::parse($user->birthdate)->age;
    } elseif (empty($calculatedAge) && !empty($user->date_of_birth)) {
        $calculatedAge = \Carbon\Carbon::parse($user->date_of_birth)->age;
    }

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

/* ---- Dynamic Interactive Guide Stepper ---- */
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
    background: #e2e8f0;
    z-index: 0;
}
.lifecycle-progress-fill {
    content: '';
    position: absolute;
    top: 22px;
    left: 40px;
    height: 3px;
    background: var(--color-primary);
    z-index: 0;
    transition: width 0.4s ease;
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
    font-weight: 800;
    font-size: 15px;
    margin-bottom: 8px;
    background: #e2e8f0;
    color: #94a3b8;
    border: 3px solid #fff;
    transition: all 0.3s ease;
}

/* Modal Header Compact Stepper Adjustments */
.modal-header .lifecycle-stepper {
    padding: 0;
}
.modal-header .lifecycle-stepper::before {
    top: 16px;
    left: 20px;
    right: 20px;
}
.modal-header .lifecycle-progress-fill {
    top: 16px;
    left: 20px;
}
.modal-header .stepper-circle {
    width: 32px; height: 32px;
    font-size: 12px;
    margin-bottom: 4px;
}
.modal-header .stepper-label {
    font-size: 10.5px;
}
.modal-header .stepper-sublabel {
    display: none;
}

/* Inactive / Default state */
.stepper-label { font-size: 12px; font-weight: 600; color: #94a3b8; text-align: center; transition: color 0.3s ease; }
.stepper-sublabel { font-size: 10.5px; color: #cbd5e1; text-align: center; transition: color 0.3s ease; }

/* Active Step state */
.stepper-step.active .stepper-circle {
    background: var(--color-primary);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(30,64,175,0.25);
    animation: pulse-step 1.8s infinite;
}
.stepper-step.active .stepper-label { color: var(--color-primary); font-weight: 700; }
.stepper-step.active .stepper-sublabel { color: #475569; }

/* Completed Step state */
.stepper-step.completed .stepper-circle {
    background: #10b981;
    color: #fff;
    box-shadow: 0 2px 8px rgba(16,185,129,0.25);
}
.stepper-step.completed .stepper-label { color: #0f766e; font-weight: 600; }
.stepper-step.completed .stepper-sublabel { color: #64748b; }

@keyframes pulse-step {
    0% { box-shadow: 0 0 0 0 rgba(30,64,175,0.35); }
    70% { box-shadow: 0 0 0 8px rgba(30,64,175,0); }
    100% { box-shadow: 0 0 0 0 rgba(30,64,175,0); }
}

/* ---- Section Cards ---- */
.req-section {
    background: #fff;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--color-border-light);
    overflow: hidden;
    margin-bottom: 24px;
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
.req-section-header h6 { margin: 0; font-size: 15px; font-weight: 700; color: var(--color-text); font-family: var(--font-heading); }
.req-section-body { padding: 20px 24px; }

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

/* ---- Upload Zone ---- */
.upload-zone {
    border: 2px dashed var(--color-primary-200);
    border-radius: var(--radius-lg);
    padding: 24px 16px;
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
    width: 44px; height: 44px;
    border-radius: 50%;
    background: var(--color-primary-100);
    color: var(--color-primary);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 18px;
    margin-bottom: 8px;
}

/* ---- File List ---- */
.file-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: #fff;
    margin-bottom: 8px;
}

/* ---- Payment Notice ---- */
.payment-notice {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #bfdbfe;
    border-radius: var(--radius-lg);
    padding: 14px 18px;
}

/* ---- Checklist ---- */
.req-checklist { list-style: none; padding: 0; margin: 0; }
.req-checklist li {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 5px 0;
    font-size: 12.5px;
    color: var(--color-text);
}
.req-checklist li .check-icon {
    width: 18px; height: 18px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 9.5px;
    flex-shrink: 0;
    margin-top: 1px;
}
.req-checklist li .check-icon.done { background: #d1fae5; color: #059669; }

/* ---- Custom Modal Adjustments ---- */
#genericPurposeModal .modal-dialog,
#bpPurposeModal .modal-dialog {
    max-width: 620px;
}

/* ---- Review Modal ---- */
#confirmSubmitModal .modal-dialog { max-width: 780px; width: 95%; }
#confirmSubmitModal .modal-content { border-radius: 16px; }
#confirmSubmitModal .review-row { word-break: break-word; overflow-wrap: anywhere; }
#confirmSubmitModal .review-display { word-break: break-word; overflow-wrap: anywhere; min-width: 0; }

.gp-chip.gp-selected {
    border-color: var(--color-primary) !important;
    background-color: var(--color-primary-50) !important;
    color: var(--color-primary) !important;
}

/* ---- Doc Type Grid Cards ---- */
.doc-type-card {
    cursor: pointer;
    transition: all 0.2s ease;
    background: #ffffff;
    border: 1px solid #e2e8f0;
}
.doc-type-card:hover {
    border-color: var(--color-primary) !important;
    background: var(--color-bg-subtle, #f0f7ff);
}
.doc-type-card.selected {
    border-color: var(--color-primary) !important;
    background: rgba(30, 64, 175, 0.08) !important;
    box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.15);
}
.doc-type-card.selected .doc-type-icon {
    background: var(--color-primary) !important;
    color: #ffffff !important;
}
.doc-type-card.selected span.doc-type-name {
    color: var(--color-primary) !important;
}

/* Dark Mode Overrides for Request Document */
html[data-theme="dark"] .doc-type-card,
html[data-bs-theme="dark"] .doc-type-card {
    background: #131d31 !important;
    border-color: #1e293b !important;
    color: #f1f5f9 !important;
}
html[data-theme="dark"] .doc-type-card:hover,
html[data-bs-theme="dark"] .doc-type-card:hover {
    background: #182238 !important;
    border-color: #38bdf8 !important;
}
html[data-theme="dark"] .doc-type-card.selected,
html[data-bs-theme="dark"] .doc-type-card.selected {
    background: rgba(43, 108, 176, 0.3) !important;
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25) !important;
}
html[data-theme="dark"] .doc-type-card .doc-type-name,
html[data-bs-theme="dark"] .doc-type-card .doc-type-name {
    color: #f8fafc !important;
}
html[data-theme="dark"] .doc-type-card.selected .doc-type-name,
html[data-bs-theme="dark"] .doc-type-card.selected .doc-type-name {
    color: #38bdf8 !important;
}
html[data-theme="dark"] .doc-type-card .doc-type-icon,
html[data-bs-theme="dark"] .doc-type-card .doc-type-icon {
    background: rgba(56, 189, 248, 0.12) !important;
    color: #38bdf8 !important;
}
html[data-theme="dark"] .doc-type-card.selected .doc-type-icon,
html[data-bs-theme="dark"] .doc-type-card.selected .doc-type-icon {
    background: #2563eb !important;
    color: #ffffff !important;
}
html[data-theme="dark"] .doc-type-card .text-muted,
html[data-bs-theme="dark"] .doc-type-card .text-muted {
    color: #94a3b8 !important;
}

/* Purpose Chips in Dark Mode */
html[data-theme="dark"] .gp-chip,
html[data-bs-theme="dark"] .gp-chip {
    background: #182238 !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}
html[data-theme="dark"] .gp-chip:hover,
html[data-bs-theme="dark"] .gp-chip:hover {
    background: #1e293b !important;
    border-color: #38bdf8 !important;
    color: #ffffff !important;
}
html[data-theme="dark"] .gp-chip.gp-selected,
html[data-bs-theme="dark"] .gp-chip.gp-selected {
    background: #1e40af !important;
    border-color: #38bdf8 !important;
    color: #ffffff !important;
}

/* Business Permit Modal Option in Dark Mode */
html[data-theme="dark"] .bp-modal-option,
html[data-bs-theme="dark"] .bp-modal-option {
    background: #182238 !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
html[data-theme="dark"] .bp-modal-option:hover,
html[data-bs-theme="dark"] .bp-modal-option:hover {
    background: #1e293b !important;
    border-color: #38bdf8 !important;
}
html[data-theme="dark"] .bp-modal-option.bp-selected,
html[data-bs-theme="dark"] .bp-modal-option.bp-selected {
    background: rgba(30, 64, 175, 0.3) !important;
    border-color: #38bdf8 !important;
}
html[data-theme="dark"] .bp-modal-option .text-muted,
html[data-bs-theme="dark"] .bp-modal-option .text-muted {
    color: #94a3b8 !important;
}

/* Payment Notice in Dark Mode */
html[data-theme="dark"] .payment-notice,
html[data-bs-theme="dark"] .payment-notice {
    background: rgba(37, 99, 235, 0.12) !important;
    border-color: rgba(59, 130, 246, 0.35) !important;
    color: #bfdbfe !important;
}
html[data-theme="dark"] .payment-notice h6,
html[data-bs-theme="dark"] .payment-notice h6,
html[data-theme="dark"] .payment-notice .text-dark,
html[data-bs-theme="dark"] .payment-notice .text-dark {
    color: #93c5fa !important;
}
html[data-theme="dark"] .payment-notice .text-muted,
html[data-bs-theme="dark"] .payment-notice .text-muted {
    color: #cbd5e1 !important;
}
html[data-theme="dark"] .payment-notice i,
html[data-bs-theme="dark"] .payment-notice i {
    color: #60a5fa !important;
}

/* Step Header Kickers, Titles & Icons in Dark Mode */
html[data-theme="dark"] .step-header-kicker,
html[data-bs-theme="dark"] .step-header-kicker,
html[data-theme="dark"] [style*="color: var(--color-primary)"],
html[data-bs-theme="dark"] [style*="color: var(--color-primary)"],
html[data-theme="dark"] [style*="color:var(--color-primary)"],
html[data-bs-theme="dark"] [style*="color:var(--color-primary)"] {
    color: #38bdf8 !important;
}
html[data-theme="dark"] .step-icon-box,
html[data-bs-theme="dark"] .step-icon-box,
html[data-theme="dark"] .icon-box,
html[data-bs-theme="dark"] .icon-box,
html[data-theme="dark"] [style*="background:rgba(30,64,175"],
html[data-bs-theme="dark"] [style*="background:rgba(30,64,175"],
html[data-theme="dark"] [style*="background: rgba(30,64,175"],
html[data-bs-theme="dark"] [style*="background: rgba(30,64,175"],
html[data-theme="dark"] [style*="background:#e8f0fe"],
html[data-bs-theme="dark"] [style*="background:#e8f0fe"] {
    background: rgba(56, 189, 248, 0.15) !important;
    color: #38bdf8 !important;
    border: 1px solid rgba(56, 189, 248, 0.3) !important;
}
html[data-theme="dark"] .step-icon-box i,
html[data-bs-theme="dark"] .step-icon-box i,
html[data-theme="dark"] .icon-box i,
html[data-bs-theme="dark"] .icon-box i {
    color: #38bdf8 !important;
}
html[data-theme="dark"] .step-header-title,
html[data-bs-theme="dark"] .step-header-title,
html[data-theme="dark"] .req-section h6,
html[data-bs-theme="dark"] .req-section h6,
html[data-theme="dark"] .req-section h5,
html[data-bs-theme="dark"] .req-section h5 {
    color: #f8fafc !important;
}
html[data-theme="dark"] .req-section .text-dark,
html[data-bs-theme="dark"] .req-section .text-dark,
html[data-theme="dark"] .modal .text-dark,
html[data-bs-theme="dark"] .modal .text-dark {
    color: #f8fafc !important;
}
html[data-theme="dark"] .req-section .text-muted,
html[data-bs-theme="dark"] .req-section .text-muted,
html[data-theme="dark"] .modal .text-muted,
html[data-bs-theme="dark"] .modal .text-muted {
    color: #94a3b8 !important;
}
</style>

<div class="container py-4">
    <div class="req-hero mb-4">
        <div>
            <h2>Request a Barangay Document</h2>
            <p>Submit your document request online and monitor its status until it is ready for pickup.</p>
        </div>
    </div>

    {{-- MAIN FORM WRAPPER --}}
    <form action="{{ route('resident.request.store') }}" method="POST" enctype="multipart/form-data" id="docRequestForm" novalidate>
        @csrf

        <input type="hidden" name="urgency" value="Normal">

        {{-- Hidden Personal Details --}}
        <input type="hidden" name="full_name" id="piFullName" value="{{ strtoupper(old('full_name', $fullName)) }}">
        <input type="hidden" name="contact_number" id="piContactNumber" value="{{ old('contact_number', $user->phone_number) }}">
        <input type="hidden" name="address" id="piAddress" value="{{ strtoupper(old('address', $address)) }}">
        <input type="hidden" name="email" id="piEmail" value="{{ old('email', $user->email) }}">
        <input type="hidden" name="age" id="piAge" value="{{ old('age', $calculatedAge) }}">

        <input type="hidden" name="first_name" id="piFirstName" value="{{ strtoupper(old('first_name', $user->first_name ?? '')) }}">
        <input type="hidden" name="middle_initial" id="piMiddleInitial" value="{{ old('middle_initial', $user->middle_name ? strtoupper(substr($user->middle_name, 0, 1)) : '') }}">
        <input type="hidden" name="last_name" id="piLastName" value="{{ strtoupper(old('last_name', $user->last_name ?? '')) }}">

        <input type="hidden" name="purok" id="piPurok" value="{{ strtoupper(old('purok', $user->purok ?? '')) }}">
        <input type="hidden" name="street" id="piStreet" value="{{ strtoupper(old('street', $user->sitio ?? $user->street ?? '')) }}">
        <input type="hidden" name="city" id="piCity" value="{{ old('city', 'COGON EAST, CARMEN, CEBU') }}">

        {{-- Payment Notice --}}
        <div class="payment-notice d-flex align-items-center gap-3 mb-4">
            <i class="fa-solid fa-circle-info text-primary fs-5"></i>
            <div>
                <div class="fw-bold text-dark" style="font-size:13px;">Important Payment Notice</div>
                <div class="text-muted" style="font-size:12px;">No online payment is required. Payment will be collected securely at the Barangay Office when you claim your physical document.</div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- COMBINED STEPPER & DOCUMENT SELECTION CONTAINER --}}
        {{-- ============================================ --}}
        @php
            $guideSteps = [
                1 => ['label' => 'Document Selection',           'sub' => 'Choose document type'],
                2 => ['label' => 'Purpose & Documents',         'sub' => 'Specify purpose details'],
                3 => ['label' => 'Supporting Docs & Upload',    'sub' => 'Attach valid ID & files'],
                4 => ['label' => 'Review Request',              'sub' => 'Click "Review Request"'],
                5 => ['label' => 'Submit Request',              'sub' => 'Confirm & send request'],
            ];
        @endphp
        <div class="req-section mb-4">
            {{-- Top: Interactive Stepper --}}
            <div class="req-section-body py-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0" style="font-size:13.5px;font-family:var(--font-heading);">
                        <i class="fa-solid fa-list-ol text-primary me-2"></i>How to Request a Document
                    </h6>
                    <span class="badge fw-bold bg-primary bg-opacity-10 text-primary guide-step-badge" style="font-size:10.5px;">Step 1 of 5</span>
                </div>

                <div class="lifecycle-stepper main-lifecycle-stepper">
                    <div class="lifecycle-progress-fill guide-progress-fill" style="width: 0%;"></div>

                    @foreach($guideSteps as $stepNum => $stepData)
                        <div class="stepper-step {{ $stepNum === 1 ? 'active' : '' }}" data-step="{{ $stepNum }}">
                            <div class="stepper-circle">{{ $stepNum }}</div>
                            <div class="stepper-label">{{ $stepData['label'] }}</div>
                            <div class="stepper-sublabel">{{ $stepData['sub'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Bottom: Document Selection Grid (STEP 1) --}}
            <div class="req-section-body py-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="icon-box step-icon-box" style="width:36px;height:36px;border-radius:10px;background:rgba(30,64,175,0.08);color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h6 class="mb-0 step-header-title" style="font-size: 15px; font-weight: 700;">
                        <span class="step-header-kicker" style="color: var(--color-primary); font-weight: 800; font-size: 20px; margin-right: 6px;">STEP 1:</span> Document Selection
                    </h6>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label small fw-semibold text-muted mb-0">Select Document Type <span class="text-danger">*</span></label>
                    <span class="text-muted small" style="font-size: 11px;">Click a document type to specify purpose & upload requirements</span>
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
                            $isBusiness = str_contains(strtolower($docName), 'business');
                            if (is_object($doc) && $doc->fee !== null && (float)$doc->fee > 0) {
                                $docFeeFormatted = '₱' . number_format((float)$doc->fee, 2);
                            } elseif ($isBusiness) {
                                $docFeeFormatted = 'TO BE SETTLED';
                            } else {
                                $docFeeFormatted = 'FREE';
                            }
                        @endphp
                        <div class="col-6 col-md-4 doc-type-item" data-value="{{ $docName }}" data-icon="{{ $docIcon }}">
                            <div class="doc-type-card d-flex align-items-center gap-2 p-2 border rounded-3 cursor-pointer {{ $selectedDoc === $docName ? 'selected' : '' }}"
                                 onclick="selectDocType('{{ addslashes($docName) }}')">
                                <div class="doc-type-icon" style="width:32px;height:32px;border-radius:8px;background:rgba(30,64,175,0.08);color:var(--color-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;">
                                    <i class="fa-solid {{ $docIcon }}"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0" style="min-width:0;">
                                    <span class="doc-type-name d-block text-truncate" style="font-size:12px;font-weight:600;color:var(--color-text);line-height:1.3;" title="{{ $docName }}">{{ $docName }}</span>
                                    <span class="{{ $isBusiness ? 'text-warning fw-bold' : 'text-muted' }}" style="font-size:10.5px;">{{ $docFeeFormatted }}</span>
                                </div>
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

                {{-- Selected Document Info Card --}}
                <div class="doc-info-card mt-3" id="docInfoCard">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-bold text-dark mb-1" id="docInfoTitle" style="font-size:14px;"></div>
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
                        <button type="button" class="btn btn-sm btn-outline-danger px-2.5 py-1 text-nowrap rounded-3" id="btnClearSelection" style="font-size:11.5px;" title="Cancel or change your document selection">
                            <i class="fa-solid fa-xmark me-1"></i> Change Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden Purpose Field --}}
        <input type="hidden" name="purpose" id="purposeField" value="{{ old('purpose') }}">

        {{-- ============================================ --}}
        {{-- SHARED REUSABLE UPLOAD SECTION (STEP 3) --}}
        {{-- ============================================ --}}
        <div id="sharedUploadSectionTemplate" class="d-none">
            <div id="sharedUploadSection" class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="step-icon-box" style="width:32px;height:32px;border-radius:8px;background:rgba(30,64,175,0.08);color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0 step-header-title" style="font-size:15px;">
                            <span class="step-header-kicker" style="color: var(--color-primary); font-weight: 800; font-size: 20px; margin-right: 6px;">STEP 3:</span> Supporting Documents &amp; Upload
                        </h6>
                        <p class="text-muted mb-0" style="font-size:11px;">Upload a valid ID or proof of residence as required.</p>
                    </div>
                </div>

                {{-- Reattach Notice --}}
                <div id="fileReattachNotice" class="alert alert-info align-items-center gap-2 py-2 px-3 mb-3 rounded-3" style="font-size:12px; display:none;">
                    <i class="fa-solid fa-circle-info fs-6"></i>
                    <div><strong>Welcome back!</strong> Please re-attach your supporting file(s) below to complete your submission.</div>
                </div>

                {{-- Valid ID Type Selection --}}
                <div class="mb-3 text-start">
                    <label class="form-label small fw-semibold text-muted mb-1" for="requestIdType">
                        <i class="fa-solid fa-id-card text-primary me-1"></i> Valid ID Type Presented <span class="text-danger">*</span>
                    </label>
                    <select name="id_type" id="requestIdType" class="form-select form-select-sm rounded-3">
                        <option value="">-- Select Valid ID Type --</option>
                        @foreach($standardIds as $sId)
                            <option value="{{ $sId }}" {{ $userCurrentId == $sId ? 'selected' : '' }}>{{ $sId }}</option>
                        @endforeach
                    </select>
                    <div class="text-muted mt-1" style="font-size: 10.5px;">Specify which valid ID is attached or presented to verify your request.</div>
                </div>

                {{-- Dynamic Requirements Checklist --}}
                <div id="docRequirementsChecklist" class="mb-3 text-start" style="display:none;">
                    <div class="text-muted small fw-bold mb-2" style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;">Required for this request</div>
                    <div id="reqChecklistItems"></div>
                </div>

                {{-- Drag & Drop Upload Zone --}}
                <div class="upload-zone mb-3" id="uploadZone">
                    <div class="upload-icon">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <p class="fw-bold text-dark mb-1" style="font-size:14px;">Drag &amp; drop files here</p>
                    <p class="text-muted small mb-2" style="font-size:11px;">Supports PDF, JPG, JPEG, PNG (Max 5MB per file)</p>
                    <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 fw-semibold rounded-3" onclick="document.getElementById('fileInput').click()">
                        <i class="fa-solid fa-folder-open me-1"></i> Browse Files
                    </button>
                    <input type="file" name="attachments[]" id="fileInput" class="d-none" multiple accept=".pdf,.jpg,.jpeg,.png">
                </div>

                {{-- Uploaded Files List --}}
                <div id="fileList" class="text-start"></div>
                <div id="uploadError" class="alert alert-danger p-2 px-3 small mt-2 text-start rounded-3" style="display:none; font-size:12px;"></div>
            </div>
        </div>

        {{-- SUBMIT BUTTON ON PAGE --}}
        <div class="text-center mb-4">
            <button type="button" class="btn btn-primary btn-lg px-4 py-3 fw-bold rounded-3 shadow" id="btnOpenConfirmModal" style="min-width:240px; font-size:16px;">
                Review Request
            </button>
            <div class="text-muted small mt-2">
                <i class="fa-solid fa-shield-halved me-1"></i> By submitting, you certify that all information provided is true and correct.
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- CONFIRM CHANGE DOCUMENT SELECTION MODAL --}}
        {{-- ============================================ --}}
        <div class="modal fade" id="changeDocConfirmModal" tabindex="-1" aria-labelledby="changeDocConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(239,68,68,0.1);color:#ef4444;font-size:1.1rem;flex-shrink:0;">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0" id="changeDocConfirmModalLabel" style="font-size:15px;">Change Document Selection?</h6>
                                <p class="text-muted mb-0" style="font-size:12px;">Confirm document switch</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 pt-3 pb-2">
                        <p class="text-muted small mb-0" style="font-size:13px; line-height: 1.5;">
                            Do you want to change your document selection? Switching to another document will clear your current purpose details and attached files.
                        </p>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-3 gap-2">
                        <button type="button" class="btn btn-light rounded-3 px-3 fw-semibold" data-bs-dismiss="modal" style="font-size:13px;">Cancel</button>
                        <button type="button" class="btn btn-danger rounded-3 px-4 fw-semibold flex-grow-1" id="btnConfirmChangeDoc" style="font-size:13px;">
                            <i class="fa-solid fa-check me-1"></i> Yes, Change Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- BP PURPOSE & UPLOAD MODAL (STEP 2 & 3) --}}
        {{-- ============================================ --}}
        <div class="modal fade" id="bpPurposeModal" tabindex="-1" aria-labelledby="bpPurposeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    {{-- MODAL HEADER: Progress Stepper Only --}}
                    <div class="modal-header border-bottom bg-light px-4 py-3 d-block">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0" style="font-size:12px;font-family:var(--font-heading);">
                                <i class="fa-solid fa-list-ol text-primary me-1.5"></i>How to Request a Document
                            </h6>
                            <span class="badge fw-bold bg-primary bg-opacity-10 text-primary guide-step-badge" style="font-size:10px;">Step 2 of 5</span>
                        </div>
                        <div class="lifecycle-stepper">
                            <div class="lifecycle-progress-fill guide-progress-fill" style="width: 25%;"></div>
                            @foreach($guideSteps as $stepNum => $stepData)
                                <div class="stepper-step {{ $stepNum === 2 ? 'active' : '' }}" data-step="{{ $stepNum }}">
                                    <div class="stepper-circle">{{ $stepNum }}</div>
                                    <div class="stepper-label">{{ $stepData['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- MODAL BODY CONTAINER: Step 2 Header + Options --}}
                    <div class="modal-body px-4 pt-4 pb-2">
                        {{-- STEP 2 Header Container inside Modal Body --}}
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-3 step-icon-box" style="width:42px;height:42px;background:rgba(30,64,175,0.1);color:var(--color-primary);font-size:1.1rem;flex-shrink:0;">
                                    <i class="fa-solid fa-store"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 step-header-title" id="bpPurposeModalLabel" style="font-size:15px;">
                                        <span class="step-header-kicker" style="color: var(--color-primary); font-weight: 800; font-size: 20px; margin-right: 6px;">STEP 2:</span> Purpose &amp; Documents
                                    </h6>
                                    <p class="text-muted mb-0" id="bpPurposeModalDocName" style="font-size:12px;">Brgy Clearance for Business Permit</p>
                                </div>
                            </div>
                            <span id="bpStep2StatusBadge" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1.5 ms-2 d-none" style="font-size:11px;">
                                <i class="fa-solid fa-circle-check me-1"></i>Purpose Added
                            </span>
                        </div>

                        <p class="text-muted small mb-3" style="font-size:12.5px;">Choose the primary purpose for this clearance and upload required files below.</p>
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
                            <textarea id="bpPurposeOther" class="form-control rounded-3" rows="2" placeholder="Add any additional details or a different purpose..." style="font-size:13px;text-transform:uppercase;"></textarea>
                        </div>
                        <div id="bpPurposeError" class="text-danger small mt-1" style="display:none;font-size:12px;">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>Please select a purpose or specify one below.
                        </div>

                        {{-- Slot for Shared Upload Section --}}
                        <div id="bpUploadSlot"></div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                        <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" id="bpModalCancel" style="font-size:13px;">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-3 px-4 fw-semibold flex-grow-1" id="bpModalConfirm" style="font-size:13px;">
                            <i class="fa-solid fa-check me-1"></i> Confirm &amp; Save Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- GENERIC PURPOSE & UPLOAD MODAL (STEP 2 & 3) --}}
        {{-- ============================================ --}}
        <div class="modal fade" id="genericPurposeModal" tabindex="-1" aria-labelledby="genericPurposeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    {{-- MODAL HEADER: Progress Stepper Only --}}
                    <div class="modal-header border-bottom bg-light px-4 py-3 d-block">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0" style="font-size:12px;font-family:var(--font-heading);">
                                <i class="fa-solid fa-list-ol text-primary me-1.5"></i>How to Request a Document
                            </h6>
                            <span class="badge fw-bold bg-primary bg-opacity-10 text-primary guide-step-badge" style="font-size:10px;">Step 2 of 5</span>
                        </div>
                        <div class="lifecycle-stepper">
                            <div class="lifecycle-progress-fill guide-progress-fill" style="width: 25%;"></div>
                            @foreach($guideSteps as $stepNum => $stepData)
                                <div class="stepper-step {{ $stepNum === 2 ? 'active' : '' }}" data-step="{{ $stepNum }}">
                                    <div class="stepper-circle">{{ $stepNum }}</div>
                                    <div class="stepper-label">{{ $stepData['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- MODAL BODY CONTAINER: Step 2 Header + Chips --}}
                    <div class="modal-body px-4 pt-4 pb-2">
                        {{-- STEP 2 Header Container inside Modal Body --}}
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-3 step-icon-box" style="width:42px;height:42px;background:rgba(30,64,175,0.1);color:var(--color-primary);font-size:1.1rem;flex-shrink:0;">
                                    <i class="fa-solid fa-bullseye"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 step-header-title" id="genericPurposeModalLabel" style="font-size:15px;">
                                        <span class="step-header-kicker" style="color: var(--color-primary); font-weight: 800; font-size: 20px; margin-right: 6px;">STEP 2:</span> Purpose &amp; Documents
                                    </h6>
                                    <p class="text-muted mb-0" id="genericPurposeModalDocName" style="font-size:12px;">—</p>
                                </div>
                            </div>
                            <span id="genericStep2StatusBadge" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1.5 ms-2 d-none" style="font-size:11px;">
                                <i class="fa-solid fa-circle-check me-1"></i>Purpose Added
                            </span>
                        </div>

                        <p class="text-muted small mb-3" style="font-size:12.5px;">Select a quick purpose or type your own below, then attach your required documents.</p>
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
                        <textarea id="genericPurposeText" class="form-control rounded-3" rows="2" placeholder="TYPE THE PURPOSE OF YOUR REQUEST..." style="font-size:13px;text-transform:uppercase;"></textarea>
                        <div id="genericPurposeError" class="text-danger small mt-2" style="display:none;font-size:12px;">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>Please enter the purpose of your request.
                        </div>

                        {{-- Slot for Shared Upload Section --}}
                        <div id="genericUploadSlot"></div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                        <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" id="genericModalCancel" style="font-size:13px;">Cancel</button>
                        <button type="button" class="btn btn-primary rounded-3 px-4 fw-semibold flex-grow-1" id="genericModalConfirm" style="font-size:13px;">
                            <i class="fa-solid fa-check me-1"></i> Confirm &amp; Save Details
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

                    {{-- MODAL HEADER: Progress Stepper Only --}}
                    <div class="modal-header border-bottom bg-light px-4 py-3 d-block position-relative">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 10;"></button>
                        <div class="d-flex justify-content-between align-items-center mb-2 pe-4">
                            <h6 class="fw-bold text-dark mb-0" style="font-size:12px;font-family:var(--font-heading);">
                                <i class="fa-solid fa-list-ol text-primary me-1.5"></i>How to Request a Document
                            </h6>
                            <span class="badge fw-bold bg-primary bg-opacity-10 text-primary guide-step-badge" style="font-size:10px;">Step 4 of 5</span>
                        </div>
                        <div class="lifecycle-stepper">
                            <div class="lifecycle-progress-fill guide-progress-fill" style="width: 75%;"></div>
                            @foreach($guideSteps as $stepNum => $stepData)
                                <div class="stepper-step {{ $stepNum === 4 ? 'active' : '' }}" data-step="{{ $stepNum }}">
                                    <div class="stepper-circle">{{ $stepNum }}</div>
                                    <div class="stepper-label">{{ $stepData['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- MODAL BODY --}}
                    <div class="modal-body px-4 py-3">
                        {{-- STEP 4 HEADER CONTAINER IN BODY --}}
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-3 step-icon-box"
                                     style="width:44px;height:44px;background:#e8f0fe;color:var(--color-primary);font-size:1.2rem;flex-shrink:0;">
                                    <i class="fa-solid fa-file-circle-check"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 step-header-title" id="confirmSubmitLabel" style="font-size:15px;">
                                        <span class="step-header-kicker" style="color: var(--color-primary); font-weight: 800; font-size: 20px; margin-right: 6px;">STEP 4:</span> Review Your Request
                                    </h6>
                                    <p class="text-muted mb-0" style="font-size:12px;">Check and edit your details before submitting</p>
                                </div>
                            </div>
                        </div>

                        {{-- Before You Submit Checklist Box --}}
                        <div class="rounded-3 p-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="d-flex align-items-center justify-content-center rounded-2 bg-primary bg-opacity-10 text-primary" style="width:28px;height:28px;font-size:13px;flex-shrink:0;">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                <span class="fw-bold text-dark" style="font-size:13px;">Before You Submit</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <ul class="req-checklist mb-0">
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
                                <div class="col-12 col-md-6">
                                    <ul class="req-checklist mb-0">
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

                        {{-- Full Name + Contact Number + Age --}}
                        <div class="row g-3 mb-3 pb-3 border-bottom">
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
                                        <input type="text" id="reviewNameInput" class="form-control form-control-sm rounded-3 mt-1 w-100" placeholder="Full name..." style="font-size:13px; text-transform: uppercase;">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
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
                                        <input type="text" id="reviewContactInput" class="form-control form-control-sm rounded-3 mt-1 w-100" placeholder="Contact number..." style="font-size:13px;" maxlength="20">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <div class="review-row h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">Age</span>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-primary review-edit-btn fw-semibold" data-target="reviewAge" style="font-size:12px;">
                                            <i class="fa-solid fa-pen me-1"></i>Edit
                                        </button>
                                    </div>
                                    <div class="review-display" id="reviewAgeDisplay">
                                        <span class="fw-semibold text-dark text-break" id="modalAge" style="font-size:14px; word-break: break-word;">—</span>
                                    </div>
                                    <div class="review-edit-field" id="reviewAgeEdit" style="display:none;">
                                        <input type="number" id="reviewAgeInput" class="form-control form-control-sm rounded-3 mt-1 w-100" placeholder="Age..." style="font-size:13px;" min="1" max="150">
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
                                <textarea id="reviewAddressInput" class="form-control form-control-sm rounded-3 mt-1 w-100" placeholder="Complete address..." rows="2" style="font-size:13px; text-transform: uppercase; word-break: break-word;"></textarea>
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
                                <textarea id="reviewPurposeInput" class="form-control form-control-sm rounded-3 mt-1 w-100" placeholder="Purpose..." rows="2" style="font-size:13px; text-transform: uppercase; word-break: break-word;"></textarea>
                            </div>
                        </div>

                        {{-- Uploaded Documents --}}
                        <div class="review-row mb-3 pb-3 border-bottom" id="modalAttachmentsSection">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-semibold" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">
                                    <i class="fa-solid fa-paperclip text-primary me-1"></i> Uploaded Documents &amp; Type (<span id="modalAttachmentCount">0</span>)
                                </span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary text-decoration-none fw-semibold" onclick="const modalEl = document.getElementById('confirmSubmitModal'); bootstrap.Modal.getInstance(modalEl)?.hide(); document.getElementById('docTypeGrid')?.scrollIntoView({behavior:'smooth', block:'center'});" style="font-size:12px;">
                                    <i class="fa-solid fa-pen me-1"></i>Edit Attachments
                                </button>
                            </div>
                            <div id="modalAttachmentsContainer" class="d-flex flex-column gap-2 mb-1"></div>
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

                        <div id="reviewSaveBar" class="alert alert-primary d-flex align-items-center gap-2 py-2 px-3 mt-3 rounded-3" style="font-size:12.5px;display:none;">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>You have unsaved edits. Click <strong>Save Changes</strong> to apply before submitting.</span>
                            <button type="button" class="btn btn-primary btn-sm ms-auto px-3 rounded-3 fw-semibold" id="btnSaveReviewEdits" style="font-size:12px;white-space:nowrap;">Save Changes</button>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                        <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" data-bs-dismiss="modal" style="font-size:13px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary rounded-3 px-4 fw-semibold flex-grow-1" id="btnConfirmSubmit" style="font-size:13px;">
                            <i class="fa-solid fa-paper-plane me-1"></i> Submit Document Request
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- SUBMISSION SUCCESS NOTIFICATION MODAL --}}
        {{-- ============================================ --}}
        <div class="modal fade" id="requestSuccessModal" tabindex="-1" aria-labelledby="requestSuccessLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                <div class="modal-content border-0 shadow-lg rounded-4 text-center overflow-hidden p-4">
                    <div class="modal-body p-2">
                        {{-- Success Icon --}}
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success mb-3" style="width: 72px; height: 72px; font-size: 2rem;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        {{-- Title & Subtitle --}}
                        <h5 class="fw-bold text-dark mb-2" id="requestSuccessLabel" style="font-size: 1.25rem;">Request Submitted Successfully!</h5>
                        <p class="text-muted small mb-3" style="font-size: 13px; line-height: 1.5;">
                            Your document request has been received by the Barangay Office and is now queued for processing.
                        </p>

                        {{-- Track Page Direct Action --}}
                        <div class="alert alert-light border rounded-3 p-3 text-start mb-4 bg-light">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                                <span class="fw-bold text-dark small">What's Next?</span>
                            </div>
                            <div class="text-muted small" style="font-size: 12px;">
                                You can view the real-time status, download receipts, and track approval updates on your <strong>Track Request</strong> page.
                            </div>
                        </div>

                        {{-- Modal Action Buttons --}}
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('resident.request.track') }}" class="btn btn-primary rounded-3 py-2.5 fw-bold shadow-sm" style="font-size: 14px;">
                                <i class="fa-solid fa-magnifying-glass-location me-2"></i>Go to Track Request Page
                            </a>
                            <a href="{{ route('resident.dashboard') }}" class="btn btn-light rounded-3 py-2 text-muted fw-semibold" style="font-size: 13px;">
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @php
        $docConfigMap = [];
        $docIconsMap = [];
        foreach ($docTypesList as $dt) {
            $dtName = is_object($dt) ? $dt->name : $dt;
            $isBus  = str_contains(strtolower($dtName), 'business');
            
            if (is_object($dt) && $dt->fee !== null && (float)$dt->fee > 0) {
                $feeText = '₱' . number_format((float)$dt->fee, 2);
                $feeAmount = (float)$dt->fee;
            } elseif ($isBus) {
                $feeText = 'TO BE SETTLED';
                $feeAmount = 0;
            } else {
                $feeText = 'FREE';
                $feeAmount = 0;
            }

            if (is_object($dt)) {
                $reqs = $dt->requirements_list ?: ['Valid Government-Issued ID'];
                $docConfigMap[$dtName] = [
                    'requirements'   => $reqs,
                    'processingTime' => $dt->processing_days ?: '1-2 Business Days',
                    'fee'            => $feeText,
                    'feeAmount'      => $feeAmount,
                    'description'    => $dt->description ?: '',
                ];
                $docIconsMap[$dtName] = $dt->icon;
            } else {
                $docConfigMap[$dtName] = [
                    'requirements'   => ['Valid Government-Issued ID'],
                    'processingTime' => '1-2 Business Days',
                    'fee'            => $feeText,
                    'feeAmount'      => $feeAmount,
                    'description'    => '',
                ];
                $docIconsMap[$dtName] = 'fa-file-lines';
            }
        }
    @endphp

    const docConfig = @json($docConfigMap);
    const docIcons = @json($docIconsMap);

    const docHiddenInput = document.getElementById('documentTypeSelect');
    const docsJsonInput  = document.getElementById('documentsJson');
    const purposeField   = document.getElementById('purposeField');
    const docInfoCard    = document.getElementById('docInfoCard');
    const docTypeSearch  = document.getElementById('docTypeSearch');
    const docTypeItems   = document.querySelectorAll('.doc-type-item');
    const docTypeNoResults = document.getElementById('docTypeNoResults');
    const requestIdTypeSelect = document.getElementById('requestIdType');
    const uploadError = document.getElementById('uploadError');

    const changeDocModalEl = document.getElementById('changeDocConfirmModal');
    const changeDocModal   = changeDocModalEl ? new bootstrap.Modal(changeDocModalEl) : null;
    let targetDocTypeToSwitch = null;

    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    let uploadedFiles = [];
    const MAX_FILE_SIZE = 5 * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['.pdf', '.jpg', '.jpeg', '.png'];

    let selectedDoc = null;
    let selectedPurpose = '';
    let pendingDocType = null;
    let currentActiveStep = 1;

    // Helper to clear uploaded files & sync inputs
    function clearUploadedFiles() {
        uploadedFiles = [];
        if (fileInput) fileInput.value = '';
        renderFileList();
        syncFileInput();
        if (requestIdTypeSelect) {
            requestIdTypeSelect.value = '{{ $userCurrentId }}' || '';
        }
    }

    // Dynamic Step Progress Controller across main page and modals
    function setGuideStep(step) {
        currentActiveStep = Math.max(1, Math.min(5, step));
        const percent = ((currentActiveStep - 1) / 4) * 100;

        document.querySelectorAll('.guide-progress-fill').forEach(fill => {
            fill.style.width = percent + '%';
        });

        document.querySelectorAll('.lifecycle-stepper').forEach(stepper => {
            stepper.querySelectorAll('.stepper-step').forEach(el => {
                const num = parseInt(el.dataset.step, 10);
                el.classList.remove('active', 'completed');
                const circle = el.querySelector('.stepper-circle');

                if (num < currentActiveStep) {
                    el.classList.add('completed');
                    if (circle) circle.innerHTML = '<i class="fa-solid fa-check"></i>';
                } else if (num === currentActiveStep) {
                    el.classList.add('active');
                    if (circle) circle.innerHTML = num;
                } else {
                    if (circle) circle.innerHTML = num;
                }
            });
        });

        document.querySelectorAll('.guide-step-badge').forEach(badge => {
            badge.textContent = `Step ${currentActiveStep} of 5`;
        });
    }

    // REAL-TIME STEP 2 PURPOSE EVALUATOR
    function evaluateStep2Completion() {
        let hasPurpose = false;
        const isBusiness = pendingDocType === BP_DOC || pendingDocType === 'Barangay Business Clearance';

        if (isBusiness) {
            const checkedRadio = document.querySelector('.bp-modal-radio:checked');
            const otherVal = bpPurposeOther ? bpPurposeOther.value.trim() : '';
            if (checkedRadio || otherVal) hasPurpose = true;

            const badge = document.getElementById('bpStep2StatusBadge');
            if (badge) badge.classList.toggle('d-none', !hasPurpose);
        } else {
            const val = genericPurposeText ? genericPurposeText.value.trim() : '';
            if (val) hasPurpose = true;

            const badge = document.getElementById('genericStep2StatusBadge');
            if (badge) badge.classList.toggle('d-none', !hasPurpose);
        }

        if (hasPurpose) {
            setGuideStep(3); // Purpose satisfied -> advance highlight to Step 3
        } else {
            setGuideStep(2); // Stay at Step 2
        }
    }

    if (requestIdTypeSelect) {
        requestIdTypeSelect.addEventListener('change', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

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



    const sharedUploadSection = document.getElementById('sharedUploadSection');
    const bpUploadSlot = document.getElementById('bpUploadSlot');
    const genericUploadSlot = document.getElementById('genericUploadSlot');

    function mountUploadSection(targetSlot) {
        if (sharedUploadSection && targetSlot) {
            targetSlot.appendChild(sharedUploadSection);
        }
    }

    function clearModalValidationErrors() {
        if (uploadError) uploadError.style.display = 'none';
        if (requestIdTypeSelect) requestIdTypeSelect.classList.remove('is-invalid');
        if (bpPurposeError) bpPurposeError.style.display = 'none';
        if (genericPurposeError) genericPurposeError.style.display = 'none';
    }

    const BP_DOC = 'Brgy Clearance for Business Permit';
    const bpPurposeError  = document.getElementById('bpPurposeError');
    const bpPurposeOther  = document.getElementById('bpPurposeOther');
    const bpModalEl       = document.getElementById('bpPurposeModal');
    const bpModal         = bpModalEl ? new bootstrap.Modal(bpModalEl) : null;

    document.querySelectorAll('.bp-modal-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.bp-modal-option').forEach(lbl => lbl.classList.remove('bp-selected'));
            this.closest('.bp-modal-option').classList.add('bp-selected');
            if (bpPurposeError) bpPurposeError.style.display = 'none';
            evaluateStep2Completion();
        });
    });

    if (bpPurposeOther) {
        bpPurposeOther.addEventListener('input', function() {
            if (bpPurposeError) bpPurposeError.style.display = 'none';
            evaluateStep2Completion();
        });
    }

    function resetBpModal() {
        document.querySelectorAll('.bp-modal-radio').forEach(r => r.checked = false);
        document.querySelectorAll('.bp-modal-option').forEach(lbl => lbl.classList.remove('bp-selected'));
        if (bpPurposeOther) bpPurposeOther.value = '';
        const badge = document.getElementById('bpStep2StatusBadge');
        if (badge) badge.classList.add('d-none');
        clearUploadedFiles();
        clearModalValidationErrors();
    }

    const bpConfirmBtn = document.getElementById('bpModalConfirm');
    if (bpConfirmBtn) {
        bpConfirmBtn.addEventListener('click', function() {
            const checkedRadio = document.querySelector('.bp-modal-radio:checked');
            const otherVal     = bpPurposeOther ? bpPurposeOther.value.trim().toUpperCase() : '';
            const idTypeVal    = requestIdTypeSelect ? requestIdTypeSelect.value.trim() : '';

            let hasErrors = false;
            let errMsgs   = [];

            // Step 2 Purpose Validation
            if (!checkedRadio && !otherVal) {
                if (bpPurposeError) bpPurposeError.style.display = 'block';
                hasErrors = true;
            } else {
                if (bpPurposeError) bpPurposeError.style.display = 'none';
            }

            // Step 3 ID Type Validation
            if (!idTypeVal) {
                if (requestIdTypeSelect) requestIdTypeSelect.classList.add('is-invalid');
                errMsgs.push('Please select the Valid ID Type Presented.');
                hasErrors = true;
            } else {
                if (requestIdTypeSelect) requestIdTypeSelect.classList.remove('is-invalid');
            }

            // Step 3 File Upload Validation
            if (uploadedFiles.length === 0) {
                errMsgs.push('Please upload at least one supporting document or valid ID file.');
                hasErrors = true;
            }

            if (hasErrors) {
                if (uploadError) {
                    uploadError.innerHTML = errMsgs.map(m => `<div><i class="fa-solid fa-circle-exclamation me-1"></i> ${m}</div>`).join('');
                    uploadError.style.display = 'block';
                    uploadError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            if (uploadError) uploadError.style.display = 'none';

            const radioVal = checkedRadio ? checkedRadio.value : '';
            const combined = otherVal ? (radioVal ? radioVal + ' — ' + otherVal : otherVal) : radioVal;

            selectedDoc = pendingDocType;
            selectedPurpose = combined;

            localStorage.setItem('saved_doc_type', selectedDoc);
            localStorage.setItem('saved_purpose', selectedPurpose);

            if (purposeField) purposeField.value = selectedPurpose;
            if (docHiddenInput) docHiddenInput.value = selectedDoc;
            if (docsJsonInput) docsJsonInput.value = JSON.stringify([{ type: selectedDoc, purpose: selectedPurpose }]);

            if (bpModal) bpModal.hide();
            updateSelectedDocUI();
            setGuideStep(4);
        });
    }

    const bpCancelBtn = document.getElementById('bpModalCancel');
    if (bpCancelBtn) {
        bpCancelBtn.addEventListener('click', function() {
            resetBpModal();
            if (bpModal) bpModal.hide();
            setGuideStep(1);
        });
    }

    const genericModalEl      = document.getElementById('genericPurposeModal');
    const genericModal        = genericModalEl ? new bootstrap.Modal(genericModalEl) : null;
    const genericPurposeText  = document.getElementById('genericPurposeText');
    const genericPurposeError = document.getElementById('genericPurposeError');
    const genericModalDocName = document.getElementById('genericPurposeModalDocName');

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
            if (genericPurposeError) genericPurposeError.style.display = 'none';
            evaluateStep2Completion();
        });
    });

    if (genericPurposeText) {
        genericPurposeText.addEventListener('input', function() {
            if (this.value.trim()) {
                document.querySelectorAll('.gp-chip').forEach(c => c.classList.remove('gp-selected'));
            }
            if (genericPurposeError) genericPurposeError.style.display = 'none';
            evaluateStep2Completion();
        });
    }

    function resetGenericModal() {
        document.querySelectorAll('.gp-chip').forEach(c => c.classList.remove('gp-selected'));
        if (genericPurposeText) genericPurposeText.value = '';
        const badge = document.getElementById('genericStep2StatusBadge');
        if (badge) badge.classList.add('d-none');
        clearUploadedFiles();
        clearModalValidationErrors();
    }

    const genericConfirmBtn = document.getElementById('genericModalConfirm');
    if (genericConfirmBtn) {
        genericConfirmBtn.addEventListener('click', function() {
            const val       = genericPurposeText ? genericPurposeText.value.trim() : '';
            const idTypeVal = requestIdTypeSelect ? requestIdTypeSelect.value.trim() : '';

            let hasErrors = false;
            let errMsgs   = [];

            // Step 2 Purpose Validation
            if (!val) {
                if (genericPurposeError) genericPurposeError.style.display = 'block';
                hasErrors = true;
            } else {
                if (genericPurposeError) genericPurposeError.style.display = 'none';
            }

            // Step 3 ID Type Validation
            if (!idTypeVal) {
                if (requestIdTypeSelect) requestIdTypeSelect.classList.add('is-invalid');
                errMsgs.push('Please select the Valid ID Type Presented.');
                hasErrors = true;
            } else {
                if (requestIdTypeSelect) requestIdTypeSelect.classList.remove('is-invalid');
            }

            // Step 3 File Upload Validation
            if (uploadedFiles.length === 0) {
                errMsgs.push('Please upload at least one supporting document or valid ID file.');
                hasErrors = true;
            }

            if (hasErrors) {
                if (uploadError) {
                    uploadError.innerHTML = errMsgs.map(m => `<div><i class="fa-solid fa-circle-exclamation me-1"></i> ${m}</div>`).join('');
                    uploadError.style.display = 'block';
                    uploadError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            if (uploadError) uploadError.style.display = 'none';

            const purposeVal = val.toUpperCase();
            selectedDoc = pendingDocType;
            selectedPurpose = purposeVal;

            localStorage.setItem('saved_doc_type', selectedDoc);
            localStorage.setItem('saved_purpose', selectedPurpose);

            if (purposeField) purposeField.value = selectedPurpose;
            if (docHiddenInput) docHiddenInput.value = selectedDoc;
            if (docsJsonInput) docsJsonInput.value = JSON.stringify([{ type: selectedDoc, purpose: selectedPurpose }]);

            if (genericModal) genericModal.hide();
            updateSelectedDocUI();
            setGuideStep(4);
        });
    }

    const genericCancelBtn = document.getElementById('genericModalCancel');
    if (genericCancelBtn) {
        genericCancelBtn.addEventListener('click', function() {
            resetGenericModal();
            if (genericModal) genericModal.hide();
            setGuideStep(1);
        });
    }

    function executeDocTypeSwitch(docType) {
        pendingDocType = docType;
        docTypeSearch.value = '';
        docTypeItems.forEach(item => item.style.display = '');
        docTypeNoResults.style.display = 'none';

        setGuideStep(2);

        const isBusiness = docType === BP_DOC || docType === 'Barangay Business Clearance';
        if (isBusiness) {
            mountUploadSection(bpUploadSlot);
            const bpSubtitle = document.getElementById('bpPurposeModalDocName');
            if (bpSubtitle) bpSubtitle.textContent = docType;
            resetBpModal();
            if (bpModal) bpModal.show();
        } else {
            mountUploadSection(genericUploadSlot);
            resetGenericModal();
            if (genericModalDocName) genericModalDocName.textContent = docType;
            if (genericModal) genericModal.show();
        }

        updateSelectedDocUI();
    }

    window.selectDocType = function(docType) {
        if (selectedDoc && selectedDoc !== docType) {
            targetDocTypeToSwitch = docType;
            if (changeDocModal) {
                changeDocModal.show();
            } else {
                executeDocTypeSwitch(docType);
            }
            return;
        }
        executeDocTypeSwitch(docType);
    };

    const btnConfirmChangeDoc = document.getElementById('btnConfirmChangeDoc');
    if (btnConfirmChangeDoc) {
        btnConfirmChangeDoc.addEventListener('click', function() {
            if (changeDocModal) changeDocModal.hide();
            if (targetDocTypeToSwitch) {
                executeDocTypeSwitch(targetDocTypeToSwitch);
                targetDocTypeToSwitch = null;
            }
        });
    }

    const btnClearSelection = document.getElementById('btnClearSelection');
    if (btnClearSelection) {
        btnClearSelection.addEventListener('click', function() {
            selectedDoc = null;
            selectedPurpose = '';
            if (docHiddenInput) docHiddenInput.value = '';
            if (purposeField) purposeField.value = '';
            if (docsJsonInput) docsJsonInput.value = '';
            localStorage.removeItem('saved_doc_type');
            localStorage.removeItem('saved_purpose');

            clearUploadedFiles();

            const notice = document.getElementById('fileReattachNotice');
            if (notice) notice.style.display = 'none';

            updateSelectedDocUI();
            setGuideStep(1);
        });
    }

    function updateSelectedDocUI() {
        if (!selectedDoc) {
            docTypeItems.forEach(item => {
                item.querySelector('.doc-type-card')?.classList.remove('selected');
            });
            if (docInfoCard) docInfoCard.classList.remove('show');
            const checklist = document.getElementById('docRequirementsChecklist');
            if (checklist) checklist.style.display = 'none';
            return;
        }

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

        if (docInfoCard) {
            const docTitleEl = document.getElementById('docInfoTitle');
            if (docTitleEl) docTitleEl.textContent = selectedDoc;
            const reqEl = document.getElementById('docInfoRequirements');
            const conf = docConfig[selectedDoc];
            if (reqEl) reqEl.innerHTML = (conf && conf.requirements && conf.requirements.length > 0) ? conf.requirements.join(', ') : 'Valid Government ID';
            const timeEl = document.getElementById('docInfoTime');
            if (timeEl) timeEl.textContent = (conf && conf.processingTime) ? conf.processingTime : '1-2 Business Days';
            const feeEl = document.getElementById('docInfoFee');
            if (feeEl) {
                if (conf && conf.fee && conf.fee === 'TO BE SETTLED') {
                    feeEl.innerHTML = `<span class="badge bg-warning bg-opacity-10 text-warning fw-bold px-2 py-0.5" style="font-size:11px;">TO BE SETTLED</span>`;
                } else {
                    feeEl.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-0.5" style="font-size:11px;">FREE</span>`;
                }
            }
            docInfoCard.classList.add('show');
        }

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
    }

    try {
        const isFromStorage = !docHiddenInput?.value && localStorage.getItem('saved_doc_type');
        const preselected = (docHiddenInput ? docHiddenInput.value.trim() : '') 
            || localStorage.getItem('saved_doc_type') 
            || (docsJsonInput && docsJsonInput.value ? JSON.parse(docsJsonInput.value)?.[0]?.type : '');
            
        if (preselected) {
            selectedDoc = preselected;
            selectedPurpose = (purposeField && purposeField.value ? purposeField.value.trim() : '') 
                || localStorage.getItem('saved_purpose') 
                || (docsJsonInput && docsJsonInput.value ? JSON.parse(docsJsonInput.value)?.[0]?.purpose : '');
                
            if (docHiddenInput) docHiddenInput.value = selectedDoc;
            if (purposeField) purposeField.value = selectedPurpose;
            updateSelectedDocUI();
            setGuideStep(4);

            if (isFromStorage) {
                const notice = document.getElementById('fileReattachNotice');
                if (notice) notice.style.display = 'flex';
            }
        } else {
            setGuideStep(1);
        }
    } catch(e) {
        setGuideStep(1);
    }

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
        handleFiles(e.dataTransfer.files);
    });

    uploadZone.addEventListener('click', (e) => {
        if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
        this.value = '';
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
        if (uploadError) uploadError.style.display = 'none';
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
            if (uploadedFiles.some(f => f.name === file.name && f.size === file.size)) {
                errors.push(`"${file.name}" — File already added.`);
                return;
            }

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

        if (errors.length > 0 && uploadError) {
            uploadError.innerHTML = errors.map(e => `<div><i class="fa-solid fa-circle-exclamation me-1"></i> ${e}</div>`).join('');
            uploadError.style.display = 'block';
        }

        renderFileList();
        syncFileInput();
    }

    window.updateFileDocType = function(index, newType) {
        if (uploadedFiles[index]) {
            uploadedFiles[index].docType = newType;
        }
    };

    function renderFileList() {
        if (uploadedFiles.length === 0) {
            fileList.innerHTML = '';
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
    }

    window.removeFile = function(index) {
        uploadedFiles.splice(index, 1);
        renderFileList();
        syncFileInput();
    };

    function syncFileInput() {
        const dt = new DataTransfer();
        uploadedFiles.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    const piFullName = document.getElementById('piFullName');
    const piContactNumber = document.getElementById('piContactNumber');
    const piAddress = document.getElementById('piAddress');
    const piAge = document.getElementById('piAge');

    const confirmModalEl = document.getElementById('confirmSubmitModal');
    const confirmModal = confirmModalEl ? new bootstrap.Modal(confirmModalEl) : null;
    let isSubmitting = false;

    if (confirmModalEl) {
        confirmModalEl.addEventListener('hidden.bs.modal', function () {
            if (!isSubmitting) {
                setGuideStep(selectedDoc ? 4 : 1);
            }
        });
    }

    document.getElementById('btnOpenConfirmModal').addEventListener('click', function() {
        let valid = true;

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

        setGuideStep(4);

        const modalResident = document.getElementById('modalResidentName');
        const modalDoc = document.getElementById('modalDocType');
        const modalContact = document.getElementById('modalContact');
        const modalAge = document.getElementById('modalAge');
        const modalAddr = document.getElementById('modalAddress');
        const modalPurpose = document.getElementById('modalPurpose');

        if (modalResident) modalResident.textContent = piFullName ? piFullName.value.trim().toUpperCase() : '—';
        if (modalDoc) modalDoc.textContent = selectedDoc || '—';
        if (modalContact) modalContact.textContent = piContactNumber ? piContactNumber.value.trim() : '—';
        if (modalAge) modalAge.textContent = piAge ? piAge.value.trim() : '—';
        if (modalAddr) modalAddr.textContent = piAddress ? piAddress.value.trim().toUpperCase() : '—';
        if (modalPurpose) modalPurpose.textContent = purposeField ? purposeField.value.trim().toUpperCase() : '—';

        const modalProcessingTime = document.getElementById('modalProcessingTime');
        const modalFee = document.getElementById('modalFee');
        const modalPaymentInstruction = document.getElementById('modalPaymentInstruction');

        const conf = (selectedDoc && typeof docConfig !== 'undefined') ? docConfig[selectedDoc] : null;
        if (modalProcessingTime) {
            modalProcessingTime.textContent = (conf && conf.processingTime) ? conf.processingTime : '1-2 Business Days';
        }
        if (modalFee) {
            if (conf && conf.fee === 'TO BE SETTLED') {
                modalFee.innerHTML = `<span class="badge bg-warning bg-opacity-10 text-warning fw-bold px-2 py-0.5" style="font-size:11px;">TO BE SETTLED</span>`;
            } else {
                modalFee.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-0.5" style="font-size:11px;">FREE</span>`;
            }
        }
        if (modalPaymentInstruction) {
            modalPaymentInstruction.textContent = 'Pay at Barangay Office upon pickup';
        }

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
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" onclick="const modalEl = document.getElementById('confirmSubmitModal'); bootstrap.Modal.getInstance(modalEl)?.hide(); document.getElementById('docTypeGrid')?.scrollIntoView({behavior:'smooth', block:'center'});" style="font-size:11px;">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload Files
                        </button>
                    </div>
                `;
            } else {
                modalAttachmentsContainer.innerHTML = uploadedFiles.map((f) => {
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
                                        <i class="fa-solid ${icon}${ext === 'pdf' ? 'text-danger' : 'text-primary'}" style="font-size:18px;"></i>
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

        const reviewNameInput    = document.getElementById('reviewNameInput');
        const reviewContactInput = document.getElementById('reviewContactInput');
        const reviewAgeInput     = document.getElementById('reviewAgeInput');
        const reviewAddressInput = document.getElementById('reviewAddressInput');
        const reviewPurposeInput = document.getElementById('reviewPurposeInput');
        if (reviewNameInput)    reviewNameInput.value    = piFullName      ? piFullName.value.trim().toUpperCase()      : '';
        if (reviewContactInput) reviewContactInput.value = piContactNumber ? piContactNumber.value.trim() : '';
        if (reviewAgeInput)     reviewAgeInput.value     = piAge           ? piAge.value.trim()             : '';
        if (reviewAddressInput) reviewAddressInput.value = piAddress       ? piAddress.value.trim().toUpperCase()       : '';
        if (reviewPurposeInput) reviewPurposeInput.value = purposeField    ? purposeField.value.trim().toUpperCase()    : '';

        ['reviewName','reviewContact','reviewAge','reviewAddress','reviewPurpose'].forEach(key => {
            const dispEl = document.getElementById(key + 'Display');
            const editEl = document.getElementById(key + 'Edit');
            const btn    = document.querySelector(`.review-edit-btn[data-target="${key}"]`);
            if (dispEl) dispEl.style.display = '';
            if (editEl) editEl.style.display  = 'none';
            if (btn)    btn.innerHTML = '<i class="fa-solid fa-pen me-1"></i>Edit';
        });

        const reviewSaveBar = document.getElementById('reviewSaveBar');
        if (reviewSaveBar) reviewSaveBar.style.display = 'none';

        if (confirmModal) {
            confirmModal.show();
        } else {
            setGuideStep(5);
            document.getElementById('docRequestForm').submit();
        }
    });

    const reviewFieldMap = {
        reviewName:    { display: 'modalResidentName', editBox: 'reviewNameEdit',    input: 'reviewNameInput',    formField: () => piFullName },
        reviewContact: { display: 'modalContact',      editBox: 'reviewContactEdit', input: 'reviewContactInput', formField: () => piContactNumber },
        reviewAge:     { display: 'modalAge',          editBox: 'reviewAgeEdit',     input: 'reviewAgeInput',     formField: () => piAge },
        reviewAddress: { display: 'modalAddress',      editBox: 'reviewAddressEdit', input: 'reviewAddressInput', formField: () => piAddress },
        reviewPurpose: { display: 'modalPurpose',      editBox: 'reviewPurposeEdit', input: 'reviewPurposeInput', formField: () => purposeField },
    };

    function showReviewSaveBar(show) {
        const bar = document.getElementById('reviewSaveBar');
        if (!bar) return;
        bar.style.display = show ? 'flex' : 'none';
    }

    document.querySelectorAll('.review-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const key      = this.dataset.target;
            const map      = reviewFieldMap[key];
            if (!map) return;

            const dispEl   = document.getElementById(key + 'Display');
            const editEl   = document.getElementById(map.editBox);
            const inputEl  = document.getElementById(map.input);
            const isEditing = editEl && editEl.style.display !== 'none';

            if (isEditing) {
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

                const spanEl = document.getElementById(map.display);
                if (spanEl) spanEl.textContent = newVal;

                if (editEl) editEl.style.display = 'none';
                if (dispEl) dispEl.style.display = '';

                this.innerHTML = '<i class="fa-solid fa-pen me-1"></i>Edit';
                showReviewSaveBar(true);
            } else {
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

    const btnSaveReviewEdits = document.getElementById('btnSaveReviewEdits');
    if (btnSaveReviewEdits) {
        btnSaveReviewEdits.addEventListener('click', function() {
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

            Object.entries(reviewFieldMap).forEach(([key, map]) => {
                const spanEl     = document.getElementById(map.display);
                const formField  = map.formField();
                if (spanEl && formField && spanEl.textContent.trim() !== '—') {
                    let v = spanEl.textContent.trim();
                    if (['reviewName', 'reviewAddress', 'reviewPurpose'].includes(key)) {
                        v = v.toUpperCase();
                    }
                    formField.value = v;
                }
            });

            this.innerHTML = '<i class="fa-solid fa-check me-1"></i>Saved!';
            this.classList.remove('btn-primary');
            this.classList.add('btn-success');

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

        setGuideStep(5);

        localStorage.removeItem('saved_doc_type');
        localStorage.removeItem('saved_purpose');

        Object.entries(reviewFieldMap).forEach(([key, map]) => {
            const editEl  = document.getElementById(map.editBox);
            const inputEl = document.getElementById(map.input);
            const spanEl  = document.getElementById(map.display);
            const formField = map.formField();
            if (!formField) return;
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
        });

        if (selectedDoc) {
            if (docHiddenInput) docHiddenInput.value = selectedDoc;
            if (purposeField && purposeField.value) {
                selectedPurpose = purposeField.value.trim().toUpperCase();
            }
            if (docsJsonInput) {
                docsJsonInput.value = JSON.stringify([{ type: selectedDoc, purpose: selectedPurpose }]);
            }
        }

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

        const mainBtn = document.getElementById('btnOpenConfirmModal');
        if (mainBtn) {
            mainBtn.disabled = true;
            mainBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting...';
        }

        if (confirmModal) confirmModal.hide();

        const form = document.getElementById('docRequestForm');
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.ok) {
                const successModalEl = document.getElementById('requestSuccessModal');
                if (successModalEl) {
                    const successModal = new bootstrap.Modal(successModalEl);
                    successModal.show();
                } else {
                    window.location.href = "{{ route('resident.request.track') }}";
                }
            } else {
                form.submit();
            }
        })
        .catch(() => {
            form.submit();
        });
    });

    document.getElementById('docTypeGrid').addEventListener('click', function() {
        this.style.outline = '';
    });
});
</script>
@endsection