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

        .table-doc-types {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
        }
        .table-doc-types thead th {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 0.75rem 0.75rem;
            white-space: nowrap;
        }
        .table-doc-types tbody td {
            padding: 0.75rem 0.75rem;
            vertical-align: middle;
        }
        .table-doc-types thead th.col-actions,
        .table-doc-types tbody td.col-actions {
            position: sticky;
            right: 0;
            z-index: 5;
            white-space: nowrap;
            text-align: right;
            padding-right: 1.25rem !important;
            background-color: #ffffff;
            box-shadow: -4px 0 8px rgba(0, 0, 0, 0.04);
        }
        .table-doc-types thead th.col-actions {
            background-color: #f8f9fe;
        }
        .table-doc-types tbody tr:hover > td.col-actions {
            background-color: #f8fafc;
        }

        @media (max-width: 767.98px) {
            .doc-status-pills {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding-bottom: 6px;
                -webkit-overflow-scrolling: touch;
            }
            .doc-status-pills .btn {
                white-space: nowrap;
                flex-shrink: 0;
            }
            .mobile-doc-card {
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                margin-bottom: 12px;
                padding: 14px;
            }
        }

        /* Scrollable modal body styling */
        .custom-modal-scroll {
            overflow-y: auto !important;
            max-height: calc(85vh - 170px) !important;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .custom-modal-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .custom-modal-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .custom-modal-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }
        .custom-modal-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }
    
        /* ═══════════════════════════════════════════════════════════
           DARK MODE OVERRIDES FOR DOCUMENT TYPES (ACCESSIBILITY & CONTRAST)
           ═══════════════════════════════════════════════════════════ */
        html[data-theme="dark"] .table-doc-types,
        html[data-bs-theme="dark"] .table-doc-types,
        html[data-theme="dark"] .table-doc-types > :not(caption) > * > *,
        html[data-bs-theme="dark"] .table-doc-types > :not(caption) > * > *,
        html[data-theme="dark"] .table-doc-types tbody td,
        html[data-bs-theme="dark"] .table-doc-types tbody td {
            background-color: #131d31 !important;
            color: #e2e8f0 !important;
            border-color: #1e293b !important;
            box-shadow: none !important;
        }
        html[data-theme="dark"] .table-doc-types thead th,
        html[data-bs-theme="dark"] .table-doc-types thead th {
            background-color: #10192a !important;
            color: #cbd5e1 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .table-doc-types tbody tr:hover > td,
        html[data-bs-theme="dark"] .table-doc-types tbody tr:hover > td {
            background-color: #19253d !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .table-doc-types thead th.col-actions,
        html[data-bs-theme="dark"] .table-doc-types thead th.col-actions {
            background-color: #10192a !important;
            box-shadow: -4px 0 10px rgba(0, 0, 0, 0.35);
        }
        html[data-theme="dark"] .table-doc-types tbody td.col-actions,
        html[data-bs-theme="dark"] .table-doc-types tbody td.col-actions {
            background-color: #131d31 !important;
            box-shadow: -4px 0 10px rgba(0, 0, 0, 0.35);
        }
        html[data-theme="dark"] .table-doc-types tbody tr:hover > td.col-actions,
        html[data-bs-theme="dark"] .table-doc-types tbody tr:hover > td.col-actions {
            background-color: #19253d !important;
        }
        html[data-theme="dark"] .mobile-doc-card,
        html[data-bs-theme="dark"] .mobile-doc-card {
            background: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }

            .doc-fee-badge.fee-paid {
            background-color: #ebf4ff !important;
            color: #1e40af !important;
            border: 1px solid #bfdbfe !important;
        }
        .doc-fee-badge.fee-free {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
        }
        html[data-theme="dark"] .doc-fee-badge.fee-paid,
        html[data-bs-theme="dark"] .doc-fee-badge.fee-paid {
            background-color: rgba(30, 58, 138, 0.4) !important;
            color: #93c5fd !important;
            border: 1px solid rgba(96, 165, 250, 0.45) !important;
        }
        html[data-theme="dark"] .doc-fee-badge.fee-free,
        html[data-bs-theme="dark"] .doc-fee-badge.fee-free {
            background-color: rgba(22, 101, 52, 0.4) !important;
            color: #86efac !important;
            border: 1px solid rgba(74, 222, 128, 0.45) !important;
        }

        /* Current Template Box in Dark Mode */
        html[data-theme="dark"] #current_template_box,
        html[data-bs-theme="dark"] #current_template_box {
            background-color: #172338 !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #current_template_box .text-dark,
        html[data-bs-theme="dark"] #current_template_box .text-dark,
        html[data-theme="dark"] #current_template_filename,
        html[data-bs-theme="dark"] #current_template_filename {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] #current_template_box .text-muted,
        html[data-bs-theme="dark"] #current_template_box .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] #current_template_box .fa-file-word,
        html[data-bs-theme="dark"] #current_template_box .fa-file-word {
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] #current_template_download_link,
        html[data-bs-theme="dark"] #current_template_download_link {
            color: #38bdf8 !important;
            border-color: #38bdf8 !important;
            background-color: rgba(56, 189, 248, 0.12) !important;
        }
        html[data-theme="dark"] #current_template_download_link:hover,
        html[data-bs-theme="dark"] #current_template_download_link:hover {
            color: #ffffff !important;
            background-color: #0284c7 !important;
            border-color: #0284c7 !important;
        }
        html[data-theme="dark"] #current_template_box .form-check-input,
        html[data-bs-theme="dark"] #current_template_box .form-check-input {
            background-color: #0b1120 !important;
            border: 1.5px solid #64748b !important;
        }
        html[data-theme="dark"] #current_template_box .form-check-input:checked,
        html[data-bs-theme="dark"] #current_template_box .form-check-input:checked {
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
        }
        html[data-theme="dark"] #current_template_box .form-check-label.text-danger,
        html[data-bs-theme="dark"] #current_template_box .form-check-label.text-danger {
            color: #f87171 !important;
        }
</style>

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h2 class="header-title mb-0">Document Management</h2>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill small fw-semibold">
                    Document Types Catalog
                </span>
            </div>
            <p class="header-subtitle mb-0">Manage barangay certificates, fees, required attachments, add, edit, or delete documents.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap w-100 w-sm-auto justify-content-start justify-content-sm-end">
            <a href="{{ route('admin.requests') }}" class="btn btn-outline-secondary px-3 fw-semibold flex-fill flex-sm-grow-0">
                <i class="fa-solid fa-list-check me-1"></i> View Requests
            </a>
            <button type="button" class="btn btn-primary px-4 fw-semibold shadow-sm flex-fill flex-sm-grow-0" data-bs-toggle="modal" data-bs-target="#addDocumentTypeModal">
                <i class="fa-solid fa-plus me-1"></i> Add Document
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start">
                <i class="fa-solid fa-triangle-exclamation fs-5 me-2 mt-1 text-danger"></i>
                <div>
                    <div class="fw-bold mb-1">Please correct the following errors:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KPI Cards (2 on mobile, 4 on desktop) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Certificates</div>
                        <h3 class="fw-bold mb-0 text-dark mt-1">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-folder-open fs-5"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2" style="font-size: 11.5px;">Offered services</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Active</div>
                        <h3 class="fw-bold mb-0 text-success mt-1">{{ $stats['active'] }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2" style="font-size: 11.5px;">Open for requests</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Paid Services</div>
                        <h3 class="fw-bold mb-0 text-warning mt-1">{{ $stats['paid'] }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-peso-sign fs-5"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2" style="font-size: 11.5px;">With barangay fee</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 11px;">Free / Indigent</div>
                        <h3 class="fw-bold mb-0 text-info mt-1">{{ $stats['free'] }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-4 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-heart-pulse fs-5"></i>
                    </div>
                </div>
                <div class="text-muted small mt-2" style="font-size: 11.5px;">Zero fee documents</div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2.5">
            {{-- Status Filters (Scrollable on small mobile screens) --}}
            <div class="d-flex gap-2 doc-status-pills">
                <a href="{{ route('admin.document-types.index') }}" 
                   class="btn btn-sm rounded-pill px-3 fw-semibold {{ !request('status') ? 'btn-primary' : 'btn-light border text-muted' }}">
                    All ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.document-types.index', ['status' => 'active', 'search' => request('search')]) }}" 
                   class="btn btn-sm rounded-pill px-3 fw-semibold {{ request('status') === 'active' ? 'btn-success text-white' : 'btn-light border text-muted' }}">
                    Active ({{ $stats['active'] }})
                </a>
                <a href="{{ route('admin.document-types.index', ['status' => 'inactive', 'search' => request('search')]) }}" 
                   class="btn btn-sm rounded-pill px-3 fw-semibold {{ request('status') === 'inactive' ? 'btn-secondary text-white' : 'btn-light border text-muted' }}">
                    Inactive ({{ $stats['inactive'] }})
                </a>
            </div>

            {{-- Search Box --}}
            <form action="{{ route('admin.document-types.index') }}" method="GET" class="m-0 ms-sm-auto w-100 w-sm-auto" style="max-width: 300px;">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="input-group input-group-sm w-100">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa fa-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control bg-light border-start-0 shadow-none" 
                           placeholder="Search document title..." autocomplete="off">
                    @if(request('search'))
                        <a href="{{ route('admin.document-types.index', ['status' => request('status')]) }}" class="btn btn-light border text-muted">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bulk Selection Bar (Shows when 1 or more items are selected) --}}
        <div id="bulkActionsBar" class="d-none bg-primary bg-opacity-10 border-bottom px-3 px-md-4 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold" id="selectedCountBadge">0 Selected</span>
                <span class="text-muted small d-none d-sm-inline">Select multiple documents to delete in bulk.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold bg-white" id="deselectAllBtn">
                    <i class="fa-solid fa-xmark me-1"></i> Deselect
                </button>
                <button type="button" class="btn btn-sm btn-danger rounded-3 px-3 fw-semibold shadow-sm" id="bulkDeleteBtn">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete Selected (<span id="bulkDeleteCount">0</span>)
                </button>
            </div>
        </div>

        {{-- Desktop Table View (Visible on screens >= 768px) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0 table-doc-types">
                <thead class="text-muted small" style="background-color: #f8f9fe;">
                    <tr>
                        <th class="ps-3 ps-md-4 py-3 border-0 text-center" style="width: 44px; min-width: 44px;">
                            <input class="form-check-input" type="checkbox" id="selectAllDocs" title="Select All">
                        </th>
                        <th class="py-3 border-0" style="min-width: 200px;">DOCUMENT NAME & DETAILS</th>
                        <th class="py-3 border-0 text-center" style="width: 100px; min-width: 90px;">STANDARD FEE</th>
                        <th class="py-3 border-0 text-center" style="width: 120px; min-width: 110px;">PROCESSING TIME</th>
                        <th class="py-3 border-0" style="min-width: 190px;">WORD TEMPLATE</th>
                        <th class="py-3 border-0" style="width: 160px; min-width: 140px;">REQUIRED ATTACHMENTS</th>
                        <th class="py-3 border-0 text-center" style="width: 100px; min-width: 90px;">AVAILABILITY</th>
                        <th class="pe-3 pe-md-4 py-3 border-0 text-end col-actions" style="width: 115px; min-width: 115px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($documentTypes as $doc)
                        <tr id="doc-row-{{ $doc->id }}">
                            <td class="ps-3 ps-md-4 py-3 text-center" style="width: 44px;">
                                <input class="form-check-input doc-checkbox" type="checkbox" value="{{ $doc->id }}" data-name="{{ $doc->name }}">
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                         style="width: 38px; height: 38px; background: {{ $doc->is_active ? 'rgba(43, 108, 176, 0.12)' : '#f3f4f6' }}; color: {{ $doc->is_active ? '#18365D' : '#9ca3af' }};">
                                        <i class="fa-solid fa-file-lines"></i>
                                    </div>
                                    <div class="min-w-0" style="max-width: 260px;">
                                        <div class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-1.5 flex-wrap">
                                            <span class="text-truncate" title="{{ $doc->name }}">{{ $doc->name }}</span>
                                            @if($doc->template_file)
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-1.5 py-0" style="font-size: 9.5px;" title="Template: {{ basename($doc->template_file) }}">
                                                    <i class="fa-solid fa-file-word me-0.5"></i> Template
                                                </span>
                                            @endif
                                            @if(!$doc->is_active)
                                                <span class="badge bg-secondary text-white px-1.5 py-0" style="font-size: 9.5px;">Disabled</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small text-truncate" style="font-size: 11.5px;" title="{{ $doc->description }}">
                                            {{ $doc->description ?: 'No detailed description provided.' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center py-3" style="width: 100px;">
                                @if((float)$doc->fee > 0)
                                    <span class="badge px-2.5 py-1 rounded-pill fw-bold doc-fee-badge fee-paid" style="font-size: 12px;">
                                        ₱{{ number_format($doc->fee, 2) }}
                                    </span>
                                @else
                                    <span class="badge px-2.5 py-1 rounded-pill fw-bold doc-fee-badge fee-free" style="font-size: 12px;">
                                        FREE
                                    </span>
                                @endif
                            </td>

                            <td class="text-center py-3" style="width: 120px;">
                                <span class="badge px-2.5 py-1 rounded-pill bg-light text-dark border fw-semibold text-truncate d-inline-block" style="font-size: 11.5px; max-width: 120px;">
                                    <i class="fa-regular fa-clock me-1 text-muted"></i> {{ $doc->processing_days ?: '1-2 Days' }}
                                </span>
                            </td>

                            {{-- Dedicated Word Template Column --}}
                            <td class="py-3" style="min-width: 190px;">
                                @if($doc->template_file)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 32px; height: 32px; background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                                            <i class="fa-solid fa-file-word fs-6"></i>
                                        </div>
                                        <div class="min-w-0" style="max-width: 160px;">
                                            <a href="{{ route('admin.document-types.download-template', $doc->id) }}" 
                                               class="text-decoration-none fw-semibold text-dark text-truncate d-block small" 
                                               style="font-size: 12px;"
                                               title="Download {{ basename($doc->template_file) }}">
                                                {{ basename($doc->template_file) }}
                                            </a>
                                            <span class="badge bg-light text-muted border px-1.5 py-0" style="font-size: 9px;">.DOCX TEMPLATE</span>
                                        </div>
                                        <a href="{{ route('admin.document-types.download-template', $doc->id) }}" 
                                           class="btn btn-sm btn-light border p-1 text-primary rounded-2 ms-auto flex-shrink-0" 
                                           style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;" 
                                           title="Download {{ basename($doc->template_file) }}">
                                            <i class="fa-solid fa-download" style="font-size: 10px;"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 11px;">
                                        <i class="fa-regular fa-file-circle-question me-1"></i> No Template
                                    </span>
                                @endif
                            </td>

                            <td class="py-3" style="width: 160px;">
                                @php
                                    $reqs = $doc->requirements_list;
                                @endphp
                                @if(!empty($reqs) && count($reqs) > 0)
                                    <div class="d-flex align-items-center gap-1 flex-nowrap" style="max-width: 160px;">
                                        <span class="badge bg-light text-secondary border px-2 py-1 text-truncate d-inline-block align-middle" style="max-width: 115px; font-size: 11px; font-weight: 500;" title="{{ $reqs[0] }}">
                                            <i class="fa-solid fa-paperclip me-1 text-muted"></i> {{ $reqs[0] }}
                                        </span>
                                        @if(count($reqs) > 1)
                                            <span class="badge bg-light text-muted border px-1.5 py-1 flex-shrink-0" style="font-size: 10.5px; cursor: help;" title="{{ implode(', ', array_slice($reqs, 1)) }}">
                                                +{{ count($reqs) - 1 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic" style="font-size: 11px;">None specified</span>
                                @endif
                            </td>

                            <td class="text-center py-3" style="width: 105px;">
                                <form action="{{ route('admin.document-types.toggle-status', $doc->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm border-0 bg-transparent p-0" title="Click to toggle status">
                                        @if($doc->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                <i class="fa-solid fa-circle-check me-1"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                <i class="fa-solid fa-ban me-1"></i> Disabled
                                            </span>
                                        @endif
                                    </button>
                                </form>
                            </td>

                            <td class="pe-3 pe-md-4 py-3 text-end col-actions" style="width: 115px;">
                                <div class="d-inline-flex align-items-center gap-1">
                                    {{-- Download Template Button --}}
                                    @if($doc->template_file)
                                        <a href="{{ route('admin.document-types.download-template', $doc->id) }}" class="btn btn-sm btn-light border p-1 text-success rounded-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;" title="Download Word Template (.docx)">
                                            <i class="fa-solid fa-file-word" style="font-size: 11px;"></i>
                                        </a>
                                    @endif

                                    {{-- Edit Button --}}
                                    <button type="button" class="btn btn-sm btn-light border p-1 text-primary edit-doc-btn rounded-2" 
                                            data-id="{{ $doc->id }}"
                                            data-name="{{ $doc->name }}"
                                            data-fee="{{ $doc->fee }}"
                                            data-days="{{ $doc->processing_days }}"
                                            data-description="{{ $doc->description }}"
                                            data-requirements="{{ is_array($doc->requirements) ? implode("\n", $doc->requirements) : $doc->requirements }}"
                                            data-active="{{ $doc->is_active ? 1 : 0 }}"
                                            data-template="{{ $doc->template_file }}"
                                            style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;"
                                            title="Edit Document Type">
                                        <i class="fa-regular fa-pen-to-square" style="font-size: 11px;"></i>
                                    </button>

                                    {{-- Delete Button --}}
                                    <button type="button" class="btn btn-sm btn-light border p-1 text-danger delete-doc-btn rounded-2" 
                                            data-id="{{ $doc->id }}"
                                            data-name="{{ $doc->name }}"
                                            style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;"
                                            title="Delete or Deactivate">
                                        <i class="fa-regular fa-trash-can" style="font-size: 11px;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fa-regular fa-folder-open display-4 text-light"></i>
                                </div>
                                <h5 class="fw-bold text-dark">No Document Types Found</h5>
                                <p class="small text-muted mb-3">Add certificates to make them available for resident requests.</p>
                                <button type="button" class="btn btn-primary btn-sm px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#addDocumentTypeModal">
                                    <i class="fa-solid fa-plus me-1"></i> Add First Document Type
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (Visible on screens < 768px, exactly like Request tab) --}}
        <div class="d-md-none p-3" id="doc-types-mobile-list">
            @forelse($documentTypes as $doc)
                <div class="mobile-doc-card {{ !$doc->is_active ? 'bg-light bg-opacity-50' : 'bg-white' }}" id="doc-card-{{ $doc->id }}">
                    {{-- Header: Selection checkbox, icon, title, and status toggle --}}
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                            <input class="form-check-input doc-checkbox mt-0 flex-shrink-0" type="checkbox" value="{{ $doc->id }}" data-name="{{ $doc->name }}">
                            <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                 style="width: 36px; height: 36px; background: {{ $doc->is_active ? 'rgba(43, 108, 176, 0.12)' : '#e2e8f0' }}; color: {{ $doc->is_active ? '#18365D' : '#718096' }};">
                                <i class="fa-solid fa-file-lines"></i>
                            </div>
                            <div class="min-w-0">
                                <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 14px;">{{ $doc->name }}</h6>
                                <span class="text-muted" style="font-size: 11.5px;">Turnaround: {{ $doc->processing_days ?: '1-2 Days' }}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <form action="{{ route('admin.document-types.toggle-status', $doc->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm border-0 bg-transparent p-0" title="Click to toggle status">
                                    @if($doc->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                            <i class="fa-solid fa-circle-check me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                            <i class="fa-solid fa-ban me-1"></i> Disabled
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Fee & Turnaround Badges --}}
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        @if((float)$doc->fee > 0)
                            <span class="badge px-2.5 py-1 rounded-pill fw-bold doc-fee-badge fee-paid" style="font-size: 12px;">
                                Fee: ₱{{ number_format($doc->fee, 2) }}
                            </span>
                        @else
                            <span class="badge px-2.5 py-1 rounded-pill fw-bold doc-fee-badge fee-free" style="font-size: 12px;">
                                FREE
                            </span>
                        @endif

                        @if($doc->description)
                            <span class="text-muted small text-truncate flex-grow-1" style="font-size: 12px;">
                                {{ $doc->description }}
                            </span>
                        @endif
                    </div>

                    {{-- Requirements list --}}
                    @php $reqs = $doc->requirements_list; @endphp
                    @if(!empty($reqs) && count($reqs) > 0)
                        <div class="d-flex flex-wrap gap-1 mb-2.5">
                            @foreach($reqs as $req)
                                <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 10.5px; font-weight: 500;">
                                    <i class="fa-solid fa-paperclip me-1 text-muted"></i> {{ $req }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Word Template for Mobile --}}
                    @if($doc->template_file)
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border mb-2.5">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <i class="fa-solid fa-file-word text-primary fs-5"></i>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-dark text-truncate small" style="font-size: 12px;">{{ basename($doc->template_file) }}</div>
                                    <div class="text-muted" style="font-size: 10px;">Word Template (.docx)</div>
                                </div>
                            </div>
                            <a href="{{ route('admin.document-types.download-template', $doc->id) }}" class="btn btn-sm btn-white border px-2.5 py-1 text-primary fw-semibold" style="font-size: 11px;">
                                <i class="fa-solid fa-download me-1"></i> Get
                            </a>
                        </div>
                    @endif

                    {{-- Actions Grid for Mobile --}}
                    <div class="d-flex gap-2 pt-2 border-top">
                        @if($doc->template_file)
                            <a href="{{ route('admin.document-types.download-template', $doc->id) }}" class="btn btn-sm btn-outline-success rounded-3 flex-fill fw-semibold py-1.5 text-center" title="Download Word Template">
                                <i class="fa-solid fa-file-word me-1"></i> Template
                            </a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 flex-fill fw-semibold edit-doc-btn py-1.5"
                                data-id="{{ $doc->id }}"
                                data-name="{{ $doc->name }}"
                                data-fee="{{ $doc->fee }}"
                                data-days="{{ $doc->processing_days }}"
                                data-description="{{ $doc->description }}"
                                data-requirements="{{ is_array($doc->requirements) ? implode("\n", $doc->requirements) : $doc->requirements }}"
                                data-active="{{ $doc->is_active ? 1 : 0 }}"
                                data-template="{{ $doc->template_file }}">
                            <i class="fa-regular fa-pen-to-square me-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3 flex-fill fw-semibold delete-doc-btn py-1.5"
                                data-id="{{ $doc->id }}"
                                data-name="{{ $doc->name }}">
                            <i class="fa-regular fa-trash-can me-1"></i> Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fa-regular fa-folder-open display-5 text-light mb-2 d-block"></i>
                    <h6 class="fw-bold text-dark">No Document Types Found</h6>
                    <p class="small text-muted mb-3">Add certificates to make them available for resident requests.</p>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#addDocumentTypeModal">
                        <i class="fa-solid fa-plus me-1"></i> Add First Document
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL: ADD DOCUMENT TYPE                   --}}
{{-- ========================================== --}}
<div class="modal fade" id="addDocumentTypeModal" tabindex="-1" aria-labelledby="addDocLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form action="{{ route('admin.document-types.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow overflow-hidden">
            @csrf
            <div class="modal-header border-bottom px-4 pt-4 pb-3 flex-shrink-0 bg-white">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                        <i class="fa-solid fa-file-circle-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="addDocLabel">Add New Document Type</h5>
                        <div class="text-muted small">Configure a certificate or clearance offered by the Barangay.</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 custom-modal-scroll">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted small fw-semibold">Document Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control px-3 py-2" placeholder="e.g. Barangay ID, Clearance for Employment" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-semibold">Standard Fee (₱) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">₱</span>
                            <input type="number" step="0.01" min="0" name="fee" class="form-control px-3 py-2" value="50.00" required>
                        </div>
                        <div class="form-text small">Enter 0.00 for free documents.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Processing Turnaround Time</label>
                        <input type="text" name="processing_days" class="form-control px-3 py-2" placeholder="e.g. Same Day, 1-2 Business Days" value="1-2 Business Days">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Initial Status</label>
                        <select name="is_active" class="form-select px-3 py-2">
                            <option value="1" selected>Active (Available for requests)</option>
                            <option value="0">Disabled (Hidden from portal)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Description / Purpose</label>
                        <textarea name="description" rows="2" class="form-control px-3 py-2" placeholder="Briefly describe what this document is used for..."></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Required Attachments / Documents (One per line)</label>
                        <textarea name="requirements" rows="3" class="form-control px-3 py-2 font-monospace" placeholder="Valid Government-Issued ID&#10;Proof of Residence&#10;2x2 ID Picture">Valid Government-Issued ID&#10;Proof of Residence</textarea>
                        <div class="form-text small">Residents will be asked to upload these supporting files when requesting this document.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Word Document Template (.docx) <span class="badge bg-light text-muted border">Optional</span></label>
                        @if(isset($availableTemplates) && count($availableTemplates) > 0)
                            <div class="mb-2">
                                <label class="form-label text-muted small mb-1" style="font-size: 11.5px;">Choose from template library:</label>
                                <select name="existing_template" class="form-select px-3 py-2 small">
                                    <option value="">-- Or leave blank to upload a new file below --</option>
                                    @foreach($availableTemplates as $tName)
                                        <option value="{{ $tName }}">{{ $tName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <label class="form-label text-muted small mb-1" style="font-size: 11.5px;">Or upload custom file from computer:</label>
                        <input type="file" name="template_file" class="form-control px-3 py-2" accept=".docx,.doc">
                        <div class="form-text small">Upload an official Microsoft Word (.docx) template with placeholders like <code>${name}</code>, <code>${date}</code>, <code>${purpose}</code>.</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-3 flex-shrink-0 bg-white">
                <button type="button" class="btn btn-light border px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="fa-solid fa-check me-1"></i> Save Document Type
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL: EDIT DOCUMENT TYPE                  --}}
{{-- ========================================== --}}
<div class="modal fade" id="editDocumentTypeModal" tabindex="-1" aria-labelledby="editDocLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form id="editDocumentTypeForm" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow overflow-hidden">
            @csrf
            @method('PATCH')
            <div class="modal-header border-bottom px-4 pt-4 pb-3 flex-shrink-0 bg-white">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2">
                        <i class="fa-solid fa-pen-to-square fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="editDocLabel">Edit Document Type</h5>
                        <div class="text-muted small">Update fee, processing time, and requirements.</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 custom-modal-scroll">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label text-muted small fw-semibold">Document Title <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control px-3 py-2" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-semibold">Standard Fee (₱) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">₱</span>
                            <input type="number" step="0.01" min="0" name="fee" id="edit_fee" class="form-control px-3 py-2" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Processing Turnaround Time</label>
                        <input type="text" name="processing_days" id="edit_processing_days" class="form-control px-3 py-2">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Status</label>
                        <select name="is_active" id="edit_is_active" class="form-select px-3 py-2">
                            <option value="1">Active (Available for requests)</option>
                            <option value="0">Disabled (Hidden from portal)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Description / Purpose</label>
                        <textarea name="description" id="edit_description" rows="2" class="form-control px-3 py-2"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Required Attachments (One per line)</label>
                        <textarea name="requirements" id="edit_requirements" rows="3" class="form-control px-3 py-2 font-monospace"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Word Document Template (.docx)</label>
                        <div id="current_template_box" class="d-none alert alert-light border py-2 px-3 d-flex align-items-center justify-content-between mb-2 rounded-3">
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="fa-solid fa-file-word text-primary fs-5"></i>
                                <div>
                                    <div class="fw-semibold text-dark" id="current_template_filename">template.docx</div>
                                    <div class="text-muted" style="font-size: 11px;">Current active template for this document</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="#" id="current_template_download_link" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-2 small fw-semibold">
                                    <i class="fa-solid fa-download me-1"></i> Download
                                </a>
                                <div class="form-check m-0 ms-2">
                                    <input class="form-check-input" type="checkbox" name="remove_template" value="1" id="edit_remove_template">
                                    <label class="form-check-label text-danger small fw-semibold" for="edit_remove_template">Remove</label>
                                </div>
                            </div>
                        </div>
                        @if(isset($availableTemplates) && count($availableTemplates) > 0)
                            <div class="mb-2">
                                <label class="form-label text-muted small mb-1" style="font-size: 11.5px;">Switch to template from library:</label>
                                <select name="existing_template" id="edit_existing_template" class="form-select px-3 py-2 small">
                                    <option value="">-- Keep current template / upload new file --</option>
                                    @foreach($availableTemplates as $tName)
                                        <option value="{{ $tName }}">{{ $tName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <label class="form-label text-muted small mb-1" style="font-size: 11.5px;">Or upload a new file from computer:</label>
                        <input type="file" name="template_file" id="edit_template_file" class="form-control px-3 py-2" accept=".docx,.doc">
                        <div class="form-text small">Upload a new <code>.docx</code> file to replace the existing template.</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-3 flex-shrink-0 bg-white">
                <button type="button" class="btn btn-light border px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Update Document Type
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL: DELETE CONFIRMATION                 --}}
{{-- ========================================== --}}
<div class="modal fade" id="deleteDocumentTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-trash-can fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Delete Document Type?</h5>
                <p class="text-muted small mb-4">
                    Are you sure you want to permanently delete <strong id="delete_doc_name" class="text-dark"></strong>? 
                    This will remove the document type from the catalog. Existing historical requests will retain their records.
                </p>
                <form id="deleteDocumentTypeForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light border px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 fw-semibold">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL: BULK DELETE CONFIRMATION           --}}
{{-- ========================================== --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-trash-can fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Delete Selected Documents?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to permanently delete <strong id="bulk_modal_count" class="text-danger"></strong> selected document type(s)? 
                    They will be removed from the catalog. Existing historical requests will retain their records.
                </p>
                <div class="bg-light rounded-3 p-3 text-start small mb-4 border" style="max-height: 140px; overflow-y: auto;">
                    <div class="fw-semibold text-muted mb-1 text-uppercase" style="font-size: 11px;">Selected Documents:</div>
                    <ul id="bulk_selected_names_list" class="mb-0 ps-3 text-dark"></ul>
                </div>
                <form id="bulkDeleteForm" action="{{ route('admin.document-types.bulk-delete') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <div id="bulk_hidden_ids"></div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light border px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 fw-semibold shadow-sm">
                            <i class="fa-solid fa-trash-can me-1"></i> Yes, Delete All Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal handler
    const editModal = new bootstrap.Modal(document.getElementById('editDocumentTypeModal'));
    const editForm = document.getElementById('editDocumentTypeForm');

    document.querySelectorAll('.edit-doc-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const fee = this.getAttribute('data-fee');
            const days = this.getAttribute('data-days');
            const description = this.getAttribute('data-description');
            const requirements = this.getAttribute('data-requirements');
            const active = this.getAttribute('data-active');
            const template = this.getAttribute('data-template');

            editForm.action = `/admin/document-types/${id}`;
            document.getElementById('edit_name').value = name || '';
            document.getElementById('edit_fee').value = parseFloat(fee).toFixed(2);
            document.getElementById('edit_processing_days').value = days || '';
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit_requirements').value = requirements || '';
            document.getElementById('edit_is_active').value = active;

            const templateBox = document.getElementById('current_template_box');
            const templateDownloadLink = document.getElementById('current_template_download_link');
            const templateFilename = document.getElementById('current_template_filename');
            const removeTemplateCheckbox = document.getElementById('edit_remove_template');
            const editFileInput = document.getElementById('edit_template_file');

            if (editFileInput) editFileInput.value = '';
            if (removeTemplateCheckbox) removeTemplateCheckbox.checked = false;

            const editExistingSelect = document.getElementById('edit_existing_template');
            if (editExistingSelect) {
                editExistingSelect.value = template ? template.split('/').pop() : '';
            }

            if (template) {
                templateBox.classList.remove('d-none');
                templateFilename.textContent = template.split('/').pop();
                templateDownloadLink.href = `/admin/document-types/${id}/template`;
            } else {
                templateBox.classList.add('d-none');
            }

            editModal.show();
        });
    });

    // Delete Modal handler
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteDocumentTypeModal'));
    const deleteForm = document.getElementById('deleteDocumentTypeForm');

    document.querySelectorAll('.delete-doc-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');

            deleteForm.action = `/admin/document-types/${id}`;
            document.getElementById('delete_doc_name').textContent = `"${name}"`;

            deleteModal.show();
        });
    });

    // Multi-select & Bulk Delete Handler
    const selectAllDocs = document.getElementById('selectAllDocs');
    const docCheckboxes = document.querySelectorAll('.doc-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
    const bulkHiddenIds = document.getElementById('bulk_hidden_ids');
    const bulkModalCount = document.getElementById('bulk_modal_count');
    const bulkSelectedNamesList = document.getElementById('bulk_selected_names_list');

    function updateBulkBar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.doc-checkbox:checked'));
        const count = checkedBoxes.length;

        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            selectedCountBadge.textContent = `${count} Selected`;
            bulkDeleteCount.textContent = count;
        } else {
            bulkActionsBar.classList.add('d-none');
        }

        // Highlight selected rows and mobile cards
        document.querySelectorAll('.doc-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                if (cb.checked) {
                    row.classList.add('table-primary');
                } else {
                    row.classList.remove('table-primary');
                }
            }
            const card = cb.closest('.mobile-doc-card');
            if (card) {
                if (cb.checked) {
                    card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                } else {
                    card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                }
            }
        });

        // Update Select All checkbox state
        if (selectAllDocs) {
            const allBoxes = document.querySelectorAll('.table-doc-types .doc-checkbox');
            const checkedInTable = document.querySelectorAll('.table-doc-types .doc-checkbox:checked');
            selectAllDocs.checked = (allBoxes.length > 0 && checkedInTable.length === allBoxes.length);
            selectAllDocs.indeterminate = (checkedInTable.length > 0 && checkedInTable.length < allBoxes.length);
        }
    }

    if (selectAllDocs) {
        selectAllDocs.addEventListener('change', function () {
            docCheckboxes.forEach(cb => cb.checked = selectAllDocs.checked);
            updateBulkBar();
        });
    }

    docCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            docCheckboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            const checkedBoxes = Array.from(document.querySelectorAll('.doc-checkbox:checked'));
            if (checkedBoxes.length === 0) return;

            bulkHiddenIds.innerHTML = '';
            bulkSelectedNamesList.innerHTML = '';
            bulkModalCount.textContent = `${checkedBoxes.length}`;

            checkedBoxes.forEach(cb => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'ids[]';
                hiddenInput.value = cb.value;
                bulkHiddenIds.appendChild(hiddenInput);

                const li = document.createElement('li');
                li.textContent = cb.getAttribute('data-name');
                bulkSelectedNamesList.appendChild(li);
            });

            bulkDeleteModal.show();
        });
    }
});
</script>

@endsection
