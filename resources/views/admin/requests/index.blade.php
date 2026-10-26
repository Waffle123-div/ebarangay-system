@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">
    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> > Requests</div>
        <h2 class="header-title">Request Management</h2>
        <p class="header-subtitle mb-0">Manage and process document requests from residents</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Total Requests</div>
                        <h3 class="fw-bold mb-0 text-dark" id="kpi-total">{{ $totalRequests }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">All-time records</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Pending Reviews</div>
                        <h3 class="fw-bold mb-0 text-warning" id="kpi-pending">{{ $pendingReviews }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">Requires attention</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Approved Today</div>
                        <h3 class="fw-bold mb-0 text-success" id="kpi-approved">{{ $approvedToday }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">Processing complete</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Overdue Requests</div>
                        <h3 class="fw-bold mb-0 text-danger" id="kpi-overdue">{{ $overdueRequests }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">Past deadline</div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-xl">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Avg Processing</div>
                        <h3 class="fw-bold mb-0 text-dark" id="kpi-avg">{{ $avgProcessing }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">Days per request</div>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills mb-4 nav-pills-status">
        <li class="nav-item">
            <a class="nav-link rounded-pill tab-pending {{ $currentStatus == 'Pending_Review' ? 'active' : '' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Pending_Review']) }}">
                <i class="fa-solid fa-inbox"></i> <span>Pending & Review</span>
                <span class="status-count-badge" id="tab-badge-pending">{{ $tabCounts['Pending_Review'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill tab-processing {{ $currentStatus == 'Processing' ? 'active' : '' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Processing']) }}">
                <i class="fa-solid fa-gear"></i> <span>Processing</span>
                <span class="status-count-badge" id="tab-badge-processing">{{ $tabCounts['Processing'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill tab-ready {{ $currentStatus == 'Ready' ? 'active' : '' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Ready']) }}">
                <i class="fa-solid fa-file-signature"></i> <span>Ready for Pick-up</span>
                <span class="status-count-badge" id="tab-badge-ready">{{ $tabCounts['Ready'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill tab-completed {{ $currentStatus == 'Completed' ? 'active' : '' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Completed']) }}">
                <i class="fa-solid fa-check-double"></i> <span>Completed</span>
                <span class="status-count-badge" id="tab-badge-completed">{{ $tabCounts['Completed'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill tab-rejected {{ $currentStatus == 'Rejected' ? 'active' : '' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Rejected']) }}">
                <i class="fa-solid fa-ban"></i> <span>Rejected</span>
                <span class="status-count-badge" id="tab-badge-rejected">{{ $tabCounts['Rejected'] }}</span>
            </a>
        </li>
    </ul>

    <style>
        /* High-Visibility Status Pill Tabs */
        .nav-pills-status {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .nav-pills-status .nav-link {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 50rem;
            border: 1.5px solid #e2e8f0;
            background-color: #ffffff;
            color: #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            letter-spacing: -0.01em;
            white-space: nowrap;
        }

        @media (min-width: 1400px) {
            .nav-pills-status {
                gap: 12px;
            }
            .nav-pills-status .nav-link {
                padding: 10px 22px;
                font-size: 14.5px;
            }
        }

        .nav-pills-status .nav-link:hover {
            transform: translateY(-2px);
            border-color: #94a3b8;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
            color: #0f172a;
        }

        .nav-pills-status .nav-link i {
            font-size: 15px;
            margin-right: 8px;
        }

        .nav-pills-status .status-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 9px;
            min-width: 28px;
            border-radius: 9999px;
            margin-left: 8px;
            line-height: 1.3;
            transition: all 0.2s ease-in-out;
        }

        /* Inactive Count Badge */
        .nav-pills-status .nav-link:not(.active) .status-count-badge {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        /* Active State Enhancements */
        .nav-pills-status .nav-link.active {
            font-weight: 700;
            border-color: transparent;
        }

        /* Active: Pending & Review */
        .nav-pills-status .nav-link.tab-pending.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
        }
        .nav-pills-status .nav-link.tab-pending.active .status-count-badge {
            background: #ffffff;
            color: #1d4ed8;
        }

        /* Active: Processing */
        .nav-pills-status .nav-link.tab-processing.active {
            background: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(29, 78, 216, 0.35);
        }
        .nav-pills-status .nav-link.tab-processing.active .status-count-badge {
            background: #ffffff;
            color: #1e40af;
        }

        /* Active: Ready for Pick-up */
        .nav-pills-status .nav-link.tab-ready.active {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(2, 132, 199, 0.35);
        }
        .nav-pills-status .nav-link.tab-ready.active .status-count-badge {
            background: #ffffff;
            color: #0369a1;
        }

        /* Active: Completed */
        .nav-pills-status .nav-link.tab-completed.active {
            background: #16a34a;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(22, 163, 74, 0.35);
        }
        .nav-pills-status .nav-link.tab-completed.active .status-count-badge {
            background: #ffffff;
            color: #15803d;
        }

        /* Active: Rejected */
        .nav-pills-status .nav-link.tab-rejected.active {
            background: #dc2626;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(220, 38, 38, 0.35);
        }
        .nav-pills-status .nav-link.tab-rejected.active .status-count-badge {
            background: #ffffff;
            color: #b91c1c;
        }

        /* Mobile Responsiveness for Status Pills & Table Controls */
        @media (max-width: 767.98px) {
            .nav-pills-status {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 8px;
                margin-bottom: 1rem;
                -webkit-overflow-scrolling: touch;
            }
            .nav-pills-status .nav-link {
                white-space: nowrap;
                padding: 7px 15px;
                font-size: 13px;
            }
            .nav-pills-status .status-count-badge {
                font-size: 11.5px;
                padding: 2px 7px;
            }
        }

        /* Table Layout & Responsive Scroll Container */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }

        .table-responsive::-webkit-scrollbar {
            height: 7px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }

        .table-requests {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-requests > :not(caption) > * > * {
            padding: 0.65rem 0.55rem !important;
            vertical-align: middle;
        }

        /* Natural Action Column (Never overlays or hides Status or other details under any zoom level) */
        .table-requests thead th.col-actions,
        .table-requests tbody td.col-actions {
            white-space: nowrap;
            text-align: right;
            padding-right: 1.25rem !important;
        }

        /* Convenient Compact / Zoom Density Mode */
        .table-requests.table-compact > :not(caption) > * > * {
            padding: 0.42rem 0.4rem !important;
        }
        .table-requests.table-compact .col-id span {
            font-size: 11.5px !important;
        }
        .table-requests.table-compact .col-applicant .fw-bold {
            font-size: 12px !important;
        }
        .table-requests.table-compact .col-doctype .fw-semibold {
            font-size: 12px !important;
        }
        .table-requests.table-compact .status-badge {
            font-size: 11px !important;
            padding: 2.5px 8px !important;
        }
        .table-requests.table-compact .actions-group .btn-action {
            height: 26px;
            font-size: 10.5px;
            padding: 0 6px;
        }
        .table-requests.table-compact .actions-group .btn-group.btn-action-gen,
        .table-requests.table-compact .actions-group .btn-group.btn-action-gen > a.btn,
        .table-requests.table-compact .actions-group .btn-group.btn-action-gen > .dropdown-toggle-split {
            height: 26px;
            line-height: 26px;
        }

        /* Action Buttons Standardized Grid Alignment (Pixel-Perfect Alignment Across All Rows) */
        .actions-group {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px !important;
            white-space: nowrap;
        }

        .actions-group .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 11.5px;
            font-weight: 600;
            height: 30px;
            border-radius: 7px;
            white-space: nowrap;
            line-height: 1;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.15s ease-in-out;
            flex-shrink: 0;
            margin: 0 !important;
        }

        .actions-group .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        /* Fixed-Slot Button Widths: Slot 1 (View) */
        .actions-group .btn-action-view {
            width: 68px !important;
            min-width: 68px !important;
            max-width: 68px !important;
            flex: 0 0 68px !important;
            padding: 0 6px !important;
            justify-content: center !important;
        }

        /* Fixed-Slot Button Widths: Slot 2 (Generate Word / Generate All) */
        .actions-group .btn-action-gen,
        .actions-group a.btn-action-gen,
        .actions-group .btn-group.btn-action-gen {
            width: 136px !important;
            min-width: 136px !important;
            max-width: 136px !important;
            flex: 0 0 136px !important;
            padding: 0 !important;
        }

        .actions-group a.btn-action-gen {
            justify-content: center !important;
            padding: 0 6px !important;
        }

        .actions-group .btn-group.btn-action-gen {
            height: 30px;
            display: inline-flex;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 7px;
        }

        .actions-group .btn-group.btn-action-gen > a.btn {
            height: 30px;
            flex: 1 1 auto !important;
            min-width: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 0 4px !important;
            line-height: 30px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .actions-group .btn-group.btn-action-gen > .dropdown-toggle-split {
            height: 30px;
            width: 24px !important;
            min-width: 24px !important;
            max-width: 24px !important;
            flex: 0 0 24px !important;
            padding: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Fixed-Slot Button Widths: Slot 3 (Ready for Pickup) */
        .actions-group .btn-action-ready,
        .actions-group div[id^="ready-btn-container-"] {
            width: 78px !important;
            min-width: 78px !important;
            max-width: 78px !important;
            flex: 0 0 78px !important;
        }

        .actions-group div[id^="ready-btn-container-"] form {
            width: 100% !important;
            display: inline-flex !important;
            margin: 0 !important;
        }

        .actions-group div[id^="ready-btn-container-"] form button {
            width: 100% !important;
            min-width: 78px !important;
            max-width: 78px !important;
            padding: 0 6px !important;
            justify-content: center !important;
        }

        /* Other Action Buttons */
        .actions-group .btn-action-complete {
            width: 86px !important;
            min-width: 86px !important;
            max-width: 86px !important;
            flex: 0 0 86px !important;
            padding: 0 6px !important;
            justify-content: center !important;
        }

        .actions-group .btn-action-accept {
            width: 76px !important;
            min-width: 76px !important;
            max-width: 76px !important;
            flex: 0 0 76px !important;
            padding: 0 6px !important;
            justify-content: center !important;
        }

        .actions-group .btn-action-reject {
            width: 76px !important;
            min-width: 76px !important;
            max-width: 76px !important;
            flex: 0 0 76px !important;
            padding: 0 6px !important;
            justify-content: center !important;
        }

        /* Mobile Cards & Responsive Tweaks */
        @media (max-width: 767.98px) {
            .mobile-request-card {
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                margin-bottom: 12px;
                padding: 14px;
            }
            .mobile-actions-grid {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }
            .mobile-actions-grid .btn-action-mobile {
                flex: 1 1 auto;
                min-height: 36px;
                font-size: 12.5px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
                padding: 6px 12px;
                border-radius: 8px;
                white-space: nowrap;
                box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            }
            .mobile-actions-grid .btn-group {
                flex: 1 1 auto;
                display: inline-flex;
                min-height: 36px;
            }
            .mobile-actions-grid .btn-group > .btn {
                min-height: 36px;
                font-size: 12.5px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
                padding: 6px 10px;
            }
        }
    </style>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header border-0 bg-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2 gap-md-3 rounded-top-4">
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                    <i class="fa-solid fa-folder-open text-primary me-2"></i>{{ str_replace('_', ' ', $currentStatus) }} Requests
                </h5>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11.5px;">
                    {{ $allRequests->total() }} records
                </span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-xl-auto">
                <form action="{{ route('admin.requests') }}" method="GET" class="m-0 flex-grow-1 flex-xl-grow-0 w-100 w-sm-auto" id="searchForm">
                    @if(request('document_type'))
                        <input type="hidden" name="document_type" value="{{ request('document_type') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    <div class="input-group input-group-sm rounded-3 border" style="min-width: 200px; width: 100%; background-color: #f8fafc;">
                        <button type="submit" class="input-group-text border-0 bg-transparent text-muted"><i class="fa fa-search"></i></button>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent shadow-none" placeholder="Search by ID, name, or type..." autocomplete="off">
                    </div>
                </form>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border px-3 fw-semibold dropdown-toggle rounded-3" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-filter text-muted me-1"></i> 
                            {{ $currentDocType == 'All' ? 'Filters' : $currentDocType }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="filterDropdown" style="font-size: 14px; max-height: 360px; overflow-y: auto;">
                            <li><a class="dropdown-item {{ $currentDocType == 'All' ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => null]) }}">All Documents</a></li>
                            <li><hr class="dropdown-divider"></li>
                            @if(isset($validTypes) && count($validTypes) > 0)
                                @foreach($validTypes as $vType)
                                    <li><a class="dropdown-item {{ $currentDocType == $vType ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => $vType]) }}">{{ $vType }}</a></li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    <a href="{{ route('admin.document-types.index') }}" class="btn btn-sm btn-light border px-3 fw-semibold text-decoration-none text-dark rounded-3" title="Manage Barangay Documents">
                        <i class="fa-solid fa-folder-open text-muted me-1"></i> Document Management
                    </a>
                    <button class="btn btn-sm btn-light border px-3 fw-semibold rounded-3"><i class="fa-solid fa-download text-muted me-1"></i> Export</button>
                    <button type="button" class="btn btn-sm btn-light border px-2.5 fw-semibold rounded-3 text-muted" id="btnToggleDensity" title="Adjust table density for convenient viewing when zoomed">
                        <i class="fa-solid fa-compress me-1" id="densityIcon"></i>
                        <span id="densityText" class="d-none d-sm-inline">Compact</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Bulk Selection Bar (Shows when 1 or more items are selected) --}}
        <div id="bulkActionsBar" class="d-none bg-primary bg-opacity-10 border-bottom px-3 px-md-4 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold" id="selectedCountBadge">0 Selected</span>
                <span class="text-muted small d-none d-sm-inline">Select multiple requests to delete in bulk.</span>
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

        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0 table-requests" id="requestsMasterTable">
                <thead class="text-muted small" style="background-color: #f8f9fe;">
                    <tr>
                        <th class="ps-3 py-2.5 border-0 text-center col-select" style="width: 40px;">
                            <input class="form-check-input" type="checkbox" id="selectAllRequests" title="Select All">
                        </th>
                        <th class="py-2.5 border-0 text-nowrap col-id" style="width: 140px;">REQUEST ID</th>
                        <th class="py-2.5 border-0 col-applicant" style="min-width: 175px;">APPLICANT</th>
                        <th class="py-2.5 border-0 col-doctype" style="min-width: 185px;">DOCUMENT TYPE</th>
                        <th class="py-2.5 border-0 text-nowrap col-date" style="width: 120px;">SUBMISSION DATE</th>
                        <th class="py-2.5 border-0 text-center text-nowrap col-status" style="width: 130px;">STATUS</th>
                        <th class="pe-3 py-2.5 border-0 text-end text-nowrap col-actions" style="width: 310px; min-width: 310px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0" id="requests-tbody">
                    @forelse($allRequests as $request)

                    @php
                        $isUrgent = ($currentStatus == 'Pending_Review' && $request->status == 'Pending' && $request->created_at->diffInHours(\Carbon\Carbon::now()) > 48);
                        $docTypeLower = strtolower($request->document_type ?? '');
                        $isBusinessClearance = str_contains($docTypeLower, 'business');
                    @endphp

                    <tr class="{{ $isUrgent ? 'bg-danger bg-opacity-10' : '' }}" id="req-row-{{ $request->id }}">
                        <td class="ps-3 text-center col-select" style="width: 40px;">
                            <input class="form-check-input req-checkbox" type="checkbox" value="{{ $request->id }}" data-tracking="{{ $request->tracking_number }}">
                        </td>
                        <td class="fw-bold text-dark text-nowrap col-id" style="width: 140px;">
                            <span class="font-monospace" style="font-size: 12.5px;">{{ $request->tracking_number }}</span>
                            @if($isUrgent)
                                <div class="text-danger small" style="font-size: 11px;"><i class="fa-solid fa-triangle-exclamation"></i> Overdue</div>
                            @endif
                        </td>
                        <td class="col-applicant" style="min-width: 175px;">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2 fw-bold text-uppercase flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">
                                    {{ substr($request->first_name, 0, 1) }}{{ substr($request->last_name, 0, 1) }}
                                </div>
                                <div class="min-w-0" style="max-width: 170px;">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 13px;">{{ $request->first_name }} {{ $request->last_name }}</div>
                                    <div class="text-muted text-truncate" style="font-size: 11.5px;">{{ $request->resident_email ?? $request->contact_number ?? 'No contact info provided' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="col-doctype" style="min-width: 185px;">
                            <div class="fw-semibold text-dark" style="font-size: 13px;">{{ $request->document_type }}</div>
                            <div class="d-flex flex-wrap align-items-center gap-1 mt-0.5">
                                @php
                                    $idType = $request->resolved_id_type ?? $request->id_type ?? $request->user?->id_type;
                                @endphp
                                @if($idType)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-1.5 py-0.5 fw-semibold" style="font-size: 10px;" title="Applicant Valid ID to Identify">
                                        <i class="fa-solid fa-id-card me-1"></i>{{ $idType }}
                                    </span>
                                @endif
                                
                                @if(!$isBusinessClearance)
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 10px;">FREE</span>
                                @elseif($request->fee !== null && (float)$request->fee > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 10px;">Fee: ₱{{ number_format($request->fee, 2) }}</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 10px;">To be settled</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-nowrap text-muted col-date" style="width: 120px; font-size: 12.5px;">{{ $request->created_at->format('M d, Y') }}</td>
                        <td class="text-center text-nowrap col-status" style="width: 130px;">
                            <div id="status-badge-{{ $request->id }}" class="d-flex justify-content-center">
                                @if($request->status == 'Completed')
                                    <span class="status-badge status-completed" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Completed</span>
                                @elseif($request->status == 'Ready')
                                    <span class="status-badge status-ready" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Ready for Pickup</span>
                                @elseif($request->status == 'Processing')
                                    <span class="status-badge status-processing" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Processing</span>
                                @elseif($request->status == 'Under Review')
                                    <span class="status-badge status-under-review" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Under Review</span>
                                @elseif($request->status == 'Rejected')
                                    <span class="status-badge status-rejected" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Rejected</span>
                                @else
                                    <span class="status-badge status-pending" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Pending</span>
                                @endif
                            </div>
                        </td>
                        <td class="pe-3 text-end text-nowrap col-actions" style="width: 310px; min-width: 310px;">
                            <div class="actions-group">

                                <button type="button" class="btn btn-sm btn-outline-secondary btn-action btn-action-view" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewModal{{ $request->id }}" 
                                        title="View Details">
                                    <i class="fa-solid fa-eye"></i> <span>View</span>
                                </button>

                                @if($currentStatus == 'Pending_Review')
                                    <form action="{{ route('admin.requests.accept', $request->id) }}" method="POST" class="m-0 d-inline-flex flex-shrink-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success btn-action btn-action-accept" title="Accept & Move to Processing">
                                            <i class="fa-solid fa-check"></i> <span>Accept</span>
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action btn-action-reject"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewModal{{ $request->id }}" 
                                            title="Review & Reject Request">
                                            <i class="fa-solid fa-xmark"></i> <span>Reject</span>
                                    </button>

                                    <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" class="m-0 d-inline-flex flex-shrink-0" onsubmit="return confirm('Delete this request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger btn-action px-2" title="Delete Request">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>

                                @elseif($currentStatus == 'Processing')
                                    @php
                                        $docsCount = count($request->documents_list);
                                    @endphp

                                    @if($docsCount > 1)
                                        <div class="btn-group shadow-sm btn-action-gen">
                                            <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                               class="btn btn-sm btn-primary rounded-start-3 px-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1" 
                                               onclick="markAsProcessing({{ $request->id }})" 
                                               title="Generate All Documents (ZIP)">
                                                <i class="fa-solid fa-file-zipper"></i> <span>Generate (All)</span>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split rounded-end-3 px-2" data-bs-toggle="dropdown" aria-expanded="false" title="Generate individual document"></button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 13px;">
                                                @foreach($request->documents_list as $docItem)
                                                    <li>
                                                        <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('admin.requests.generate', [$request->id, 'doc_type' => $docItem['type']]) }}">
                                                            <i class="fa-solid fa-file-word text-primary"></i> <span>{{ $docItem['type'] }}</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                           class="btn btn-sm btn-primary btn-action btn-action-gen" 
                                           onclick="markAsProcessing({{ $request->id }})" 
                                           title="Generate Document">
                                            <i class="fa-solid fa-file-word"></i> <span>Generate Word</span>
                                        </a>
                                    @endif

                                    <div id="ready-btn-container-{{ $request->id }}" class="d-inline-flex flex-shrink-0">
                                        <form action="{{ route('admin.requests.ready', $request->id) }}" method="POST" class="m-0 d-inline-flex flex-shrink-0">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-info text-white btn-action btn-action-ready" title="Mark Ready for Pickup">
                                                <i class="fa-solid fa-bell"></i> <span>Ready</span>
                                            </button>
                                        </form>
                                    </div>

                                @elseif($currentStatus == 'Ready')
                                    @if(count($request->documents_list) > 1)
                                        <div class="btn-group shadow-sm btn-action-gen">
                                            <a href="{{ route('admin.requests.generate', $request->id) }}" class="btn btn-sm btn-outline-primary rounded-start-3 px-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1" title="Re-download All (ZIP)">
                                                <i class="fa-solid fa-file-zipper"></i> <span>Word Doc</span>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split rounded-end-3 px-2" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 13px;">
                                                @foreach($request->documents_list as $docItem)
                                                    <li>
                                                        <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('admin.requests.generate', [$request->id, 'doc_type' => $docItem['type']]) }}">
                                                            <i class="fa-solid fa-file-word text-primary"></i> <span>{{ $docItem['type'] }}</span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <a href="{{ route('admin.requests.generate', $request->id) }}" class="btn btn-sm btn-outline-primary btn-action btn-action-gen" title="Re-download Document">
                                            <i class="fa-solid fa-file-word"></i> <span>Word Doc</span>
                                        </a>
                                    @endif

                                    <form action="{{ route('admin.requests.complete', $request->id) }}" method="POST" class="m-0 d-inline-flex flex-shrink-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success btn-action btn-action-complete" title="Mark Claimed / Completed">
                                            <i class="fa-solid fa-check-double"></i> <span>Complete</span>
                                        </button>
                                    </form>

                                @elseif($currentStatus == 'Completed')
                                    <a href="{{ route('admin.requests.generate', $request->id) }}" class="btn btn-sm btn-outline-primary btn-action btn-action-gen" title="Download Document Archive">
                                        <i class="fa-solid fa-file-word"></i> <span>Word Doc</span>
                                    </a>

                                @elseif($currentStatus == 'Rejected')
                                    <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" class="m-0 d-inline-flex flex-shrink-0" onsubmit="return confirm('Permanently delete this rejected request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action btn-action-reject" title="Delete Permanently">
                                            <i class="fa-solid fa-trash"></i> <span>Delete</span>
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-inbox fa-3x mb-3 text-light"></i>
                            <h5 class="fw-bold text-dark">No Requests Found</h5>
                            <div>There are currently no documents in the <b>{{ str_replace('_', ' ', $currentStatus) }}</b> tab.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (Visible on screens < 768px) --}}
        <div class="d-md-none p-3" id="requests-mobile-list">
            @forelse($allRequests as $request)
                @php
                    $isUrgent = ($currentStatus == 'Pending_Review' && $request->status == 'Pending' && $request->created_at->diffInHours(\Carbon\Carbon::now()) > 48);
                    $idType = $request->resolved_id_type ?? $request->id_type ?? $request->user?->id_type;
                    $docTypeLower = strtolower($request->document_type ?? '');
                    $isBusinessClearance = str_contains($docTypeLower, 'business');
                @endphp
                <div class="mobile-request-card {{ $isUrgent ? 'border-danger border-opacity-50' : '' }}" style="{{ $isUrgent ? 'background-color: #fff5f5;' : '' }}">
                    {{-- Header: Tracking ID + Status Badge --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input req-checkbox mt-0" type="checkbox" value="{{ $request->id }}" data-tracking="{{ $request->tracking_number }}">
                            <span class="font-monospace fw-bold text-dark" style="font-size: 13.5px;">{{ $request->tracking_number }}</span>
                            @if($isUrgent)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-0.5 px-1.5" style="font-size: 10px;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Overdue
                                </span>
                            @endif
                        </div>
                        <div id="status-badge-mobile-{{ $request->id }}">
                            @if($request->status == 'Completed')
                                <span class="status-badge status-completed">Completed</span>
                            @elseif($request->status == 'Ready')
                                <span class="status-badge status-ready">Ready</span>
                            @elseif($request->status == 'Processing')
                                <span class="status-badge status-processing">Processing</span>
                            @elseif($request->status == 'Under Review')
                                <span class="status-badge status-under-review">Under Review</span>
                            @elseif($request->status == 'Rejected')
                                <span class="status-badge status-rejected">Rejected</span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </div>
                    </div>

                    {{-- Applicant Info --}}
                    <div class="d-flex align-items-center my-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2.5 fw-bold text-uppercase flex-shrink-0" style="width: 38px; height: 38px; font-size: 13px;">
                            {{ substr($request->first_name, 0, 1) }}{{ substr($request->last_name, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 14.5px;">{{ $request->first_name }} {{ $request->last_name }}</div>
                            <div class="text-muted text-truncate" style="font-size: 12px;">{{ $request->resident_email ?? $request->contact_number ?? 'No contact info provided' }}</div>
                        </div>
                    </div>

                    {{-- Document Details Box --}}
                    <div class="bg-light rounded-3 p-2.5 my-2" style="font-size: 12.5px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Document:</span>
                            <span class="fw-bold text-dark text-end">{{ $request->document_type }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted">Date:</span>
                            <span class="text-dark">{{ $request->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 pt-1 border-top border-light-subtle">
                            @if($idType)
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-0.5 fw-semibold" style="font-size: 10.5px;">
                                    <i class="fa-solid fa-id-card me-1"></i>{{ $idType }}
                                </span>
                            @endif
                            
                            @if(!$isBusinessClearance)
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold ms-auto" style="font-size: 11px;">FREE</span>
                            @elseif($request->fee !== null && (float)$request->fee > 0)
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold ms-auto" style="font-size: 11px;">Fee: ₱{{ number_format($request->fee, 2) }}</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning ms-auto" style="font-size: 11px;">To be settled</span>
                            @endif
                        </div>
                    </div>

                    {{-- Mobile Action Buttons Grid --}}
                    <div class="mobile-actions-grid pt-1">
                        <button type="button" class="btn btn-outline-secondary btn-action-mobile" 
                                data-bs-toggle="modal" 
                                data-bs-target="#viewModal{{ $request->id }}" 
                                title="View Details">
                            <i class="fa-solid fa-eye"></i> <span>View</span>
                        </button>

                        @if($currentStatus == 'Pending_Review')
                            <form action="{{ route('admin.requests.accept', $request->id) }}" method="POST" class="m-0 flex-grow-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-action-mobile w-100" title="Accept & Move to Processing">
                                    <i class="fa-solid fa-check"></i> <span>Accept</span>
                                </button>
                            </form>

                            <button type="button" class="btn btn-outline-danger btn-action-mobile"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewModal{{ $request->id }}"
                                    title="Review & Reject Request">
                                <i class="fa-solid fa-xmark"></i> <span>Reject</span>
                            </button>

                            <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this request?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light border text-danger btn-action-mobile px-2.5" title="Delete Request">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        @elseif($currentStatus == 'Processing')
                            @php
                                $docsCount = count($request->documents_list);
                            @endphp

                            @if($docsCount > 1)
                                <div class="btn-group shadow-sm flex-grow-1">
                                    <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                       class="btn btn-primary rounded-start-3 px-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1" 
                                       onclick="markAsProcessing({{ $request->id }})" 
                                       title="Generate All Documents (ZIP)">
                                        <i class="fa-solid fa-file-zipper"></i> <span>Generate (All)</span>
                                    </a>
                                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split rounded-end-3 px-2" data-bs-toggle="dropdown" aria-expanded="false" title="Generate individual document"></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 13px;">
                                        @foreach($request->documents_list as $docItem)
                                            <li>
                                                <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('admin.requests.generate', [$request->id, 'doc_type' => $docItem['type']]) }}">
                                                    <i class="fa-solid fa-file-word text-primary"></i> <span>{{ $docItem['type'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                   class="btn btn-primary btn-action-mobile flex-grow-1" 
                                   onclick="markAsProcessing({{ $request->id }})" 
                                   title="Generate Document">
                                    <i class="fa-solid fa-file-word"></i> <span>Generate Word</span>
                                </a>
                            @endif

                            <div id="ready-btn-container-mobile-{{ $request->id }}" class="flex-grow-1">
                                <form action="{{ route('admin.requests.ready', $request->id) }}" method="POST" class="m-0 w-100">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-info text-white btn-action-mobile w-100" title="Mark Ready for Pickup">
                                        <i class="fa-solid fa-bell"></i> <span>Ready</span>
                                    </button>
                                </form>
                            </div>

                        @elseif($currentStatus == 'Ready')
                            @if(count($request->documents_list) > 1)
                                <div class="btn-group shadow-sm flex-grow-1">
                                    <a href="{{ route('admin.requests.generate', $request->id) }}" class="btn btn-outline-primary rounded-start-3 px-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1" title="Re-download All (ZIP)">
                                        <i class="fa-solid fa-file-zipper"></i> <span>Word Doc</span>
                                    </a>
                                    <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split rounded-end-3 px-2" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 13px;">
                                        @foreach($request->documents_list as $docItem)
                                            <li>
                                                <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('admin.requests.generate', [$request->id, 'doc_type' => $docItem['type']]) }}">
                                                    <i class="fa-solid fa-file-word text-primary"></i> <span>{{ $docItem['type'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('admin.requests.generate', $request->id) }}" class="btn btn-outline-primary btn-action-mobile flex-grow-1" title="Re-download Document">
                                    <i class="fa-solid fa-file-word"></i> <span>Word Doc</span>
                                </a>
                            @endif

                            <form action="{{ route('admin.requests.complete', $request->id) }}" method="POST" class="m-0 flex-grow-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-action-mobile w-100" title="Mark Claimed / Completed">
                                    <i class="fa-solid fa-check-double"></i> <span>Complete</span>
                                </button>
                            </form>

                        @elseif($currentStatus == 'Completed')
                            <a href="{{ route('admin.requests.generate', $request->id) }}" class="btn btn-outline-primary btn-action-mobile flex-grow-1" title="Download Document Archive">
                                <i class="fa-solid fa-file-word"></i> <span>Word Doc</span>
                            </a>

                        @elseif($currentStatus == 'Rejected')
                            <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" class="m-0 flex-grow-1" onsubmit="return confirm('Permanently delete this rejected request?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-action-mobile w-100" title="Delete Permanently">
                                    <i class="fa-solid fa-trash"></i> <span>Delete</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-inbox fa-3x mb-3 text-light"></i>
                    <h5 class="fw-bold text-dark">No Requests Found</h5>
                    <div class="small">There are currently no documents in the <b>{{ str_replace('_', ' ', $currentStatus) }}</b> tab.</div>
                </div>
            @endforelse
        </div>

        @if($allRequests->hasPages())
        <div class="px-3 px-md-4 py-3 border-top bg-light bg-opacity-50 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 rounded-bottom-4" id="pagination-container">
            <div class="text-muted small">
                Showing <strong>{{ $allRequests->firstItem() ?? 0 }}</strong> to <strong>{{ $allRequests->lastItem() ?? 0 }}</strong> of <strong>{{ $allRequests->total() }}</strong> records
            </div>
            <div class="pagination-wrapper">
                {{ $allRequests->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

<div id="modals-container">
    @foreach($allRequests as $request)
    @php 
        $editable = true;
        $docTypeLower = strtolower($request->document_type ?? '');
        $isBusinessClearance = str_contains($docTypeLower, 'business');
    @endphp
    <div class="modal fade" id="viewModal{{ $request->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $request->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">

                {{-- HEADER --}}
                <div class="modal-header bg-light border-bottom-0 d-flex align-items-center py-3 px-4">
                    <h5 class="modal-title fw-bold mb-0" id="viewModalLabel{{ $request->id }}">
                        <i class="fa-solid fa-file-invoice text-primary me-2"></i>
                        <span id="modal-title-label-{{ $request->id }}">Request Details</span>
                    </h5>

                    {{-- Right-side controls: Edit/Cancel toggle + Modal Close --}}
                    <div class="ms-auto d-flex align-items-center gap-2">
                        @if($editable)
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1.5 fw-semibold shadow-sm"
                                    id="btn-edit-{{ $request->id }}"
                                    onclick="toggleEditMode({{ $request->id }}, true)">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                            </button>
                            <button type="submit" form="edit-form-{{ $request->id }}"
                                    class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 fw-semibold shadow-sm d-none"
                                    id="btn-header-save-{{ $request->id }}">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-1.5 fw-semibold d-none"
                                    id="btn-cancel-edit-{{ $request->id }}"
                                    onclick="toggleEditMode({{ $request->id }}, false)">
                                <i class="fa-solid fa-xmark me-1"></i> Cancel Edit
                            </button>
                        @endif
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-0 text-start">

                    {{-- ======================== --}}
                    {{-- VIEW MODE              --}}
                    {{-- ======================== --}}
                    <div id="view-mode-{{ $request->id }}" class="p-4">

                        {{-- Row 1: Tracking + Status --}}
                        <div class="row g-0 mb-0">
                            <div class="col-6 pe-3 pb-3" style="border-bottom: 1px solid #f0f0f0;">
                                <p class="text-muted mb-1" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">Tracking Number</p>
                                <p class="fw-bold text-dark mb-0 font-monospace" style="font-size:15px;">{{ $request->tracking_number }}</p>
                            </div>
                            <div class="col-6 ps-3 pb-3" style="border-bottom: 1px solid #f0f0f0; border-left: 1px solid #f0f0f0;">
                                <p class="text-muted mb-1" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">Status</p>
                                <span id="modal-status-{{ $request->id }}" class="fw-bold"
                                    style="font-size:14px; color:
                                        @if($request->status == 'Completed') #198754
                                        @elseif($request->status == 'Ready') #0dcaf0
                                        @elseif($request->status == 'Rejected') #dc3545
                                        @elseif($request->status == 'Processing') #0d6efd
                                        @elseif($request->status == 'Under Review') #6f42c1
                                        @else #f0ad4e @endif
                                    ;">{{ $request->status }}</span>
                            </div>
                        </div>

                        {{-- Row 2: Name + Age --}}
                        <div class="row g-0 mb-0">
                            <div class="col-6 pe-3 py-3" style="border-bottom: 1px solid #f0f0f0;">
                                <p class="text-muted mb-1" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">Applicant Name</p>
                                <p class="fw-bold text-dark mb-0" style="font-size:15px;">{{ strtoupper($request->first_name . ' ' . $request->middle_initial . ' ' . $request->last_name) }}</p>
                            </div>
                            <div class="col-6 ps-3 py-3" style="border-bottom: 1px solid #f0f0f0; border-left: 1px solid #f0f0f0;">
                                <p class="text-muted mb-1" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">Age</p>
                                <p class="fw-bold text-dark mb-0" style="font-size:15px;">{{ $request->age ?? '—' }}</p>
                            </div>
                        </div>

                        {{-- Row 3: Contact + ID Type --}}
                        <div class="row g-0 mb-0">
                            <div class="col-6 pe-3 py-3" style="border-bottom: 1px solid #f0f0f0;">
                                <p class="text-muted mb-1" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">Contact Info</p>
                                <p class="fw-semibold text-dark mb-0" style="font-size:14px;">{{ $request->resident_email ?? $request->contact_number ?? '—' }}</p>
                            </div>
                            <div class="col-6 ps-3 py-3" style="border-bottom: 1px solid #f0f0f0; border-left: 1px solid #f0f0f0;">
                                @php
                                    $idType = $request->resolved_id_type ?? $request->id_type ?? $request->user?->id_type;
                                @endphp
                                <p class="text-muted mb-1" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">ID Type</p>
                                <p class="fw-semibold text-dark mb-0" style="font-size:14px;">
                                    @if($idType)
                                        <i class="fa-solid fa-id-card text-primary me-1"></i> {{ $idType }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Row 4: Fee Amount + Date Submitted --}}
                        <div class="row g-0 mb-0">
                            <div class="col-6 pe-3 py-3" style="border-bottom: 1px solid #f0f0f0;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <p class="text-muted mb-0" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">Fee Amount</p>
                                    @if(!$isBusinessClearance)
                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size:11px;">
                                            <i class="fa-solid fa-circle-check me-1"></i>FREE
                                        </span>
                                    @else
                                        @if($request->fee !== null && (float)$request->fee > 0)
                                            <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size:11px;">SET: ₱{{ number_format($request->fee, 2) }}</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning fw-bold" style="font-size:11px;">TO BE SETTLED</span>
                                        @endif
                                    @endif
                                </div>

                                @if($isBusinessClearance)
                                    <form action="{{ route('admin.requests.updateFee', $request->id) }}" method="POST" class="d-flex align-items-center gap-1 mt-1">
                                        @csrf
                                        @method('PATCH')
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-muted fw-bold" style="font-size: 13px;">₱</span>
                                            <input type="number" step="0.01" min="0" name="fee" 
                                                   class="form-control form-control-sm font-monospace fw-bold" 
                                                   placeholder="0.00" 
                                                   value="{{ $request->fee !== null ? number_format($request->fee, 2, '.', '') : '' }}"
                                                   style="font-size:13px;">
                                            <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold" title="Save Fee Amount">
                                                <i class="fa-solid fa-check me-1"></i> Save
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="bg-light border rounded-3 p-2 text-center text-success fw-bold small mt-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Free of Charge
                                    </div>
                                @endif
                            </div>
                            <div class="col-6 ps-3 py-3" style="border-bottom: 1px solid #f0f0f0; border-left: 1px solid #f0f0f0;">
                                <p class="text-muted mb-1" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">Date Submitted</p>
                                <p class="fw-semibold text-dark mb-0" style="font-size:14px;">{{ $request->created_at->format('F d, Y — h:i A') }}</p>
                            </div>
                        </div>

                        {{-- Row 5: Stated Purpose / Requested Documents --}}
                        <div class="pt-3">
                            @php
                                $modalDocsList = $request->documents_list;
                                $docsCount = count($modalDocsList);
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="text-muted mb-0" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                                    <i class="fa-solid fa-file-lines text-primary me-1"></i> Requested {{ $docsCount > 1 ? 'Documents (' . $docsCount . ')' : 'Document' }}
                                </p>
                                @if($docsCount > 1)
                                    <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                       class="btn btn-sm btn-primary rounded-3 px-3 py-1 fw-semibold shadow-sm d-inline-flex align-items-center gap-1"
                                       title="Download All Documents in a Single ZIP Archive">
                                        <i class="fa-solid fa-file-zipper"></i> <span>Download All as ZIP</span>
                                    </a>
                                @endif
                            </div>

                            @if($docsCount > 1)
                                <div class="d-flex flex-column gap-2 mb-2">
                                    @foreach($modalDocsList as $docItem)
                                        @php
                                            $itemDocLower = strtolower($docItem['type'] ?? '');
                                            $isItemBusiness = str_contains($itemDocLower, 'business');
                                        @endphp
                                        <div class="bg-light rounded-3 p-3 border d-flex justify-content-between align-items-center gap-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="fw-bold text-dark" style="font-size: 14px;">{{ $docItem['type'] }}</span>
                                                    @if(!$isItemBusiness)
                                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 10.5px;">FREE</span>
                                                    @elseif($docItem['fee'] !== null && (float)$docItem['fee'] > 0)
                                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 10.5px;">Fee: ₱{{ number_format($docItem['fee'], 2) }}</span>
                                                    @else
                                                        <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 10.5px;">Fee: To be settled</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted small mt-1">
                                                    <span class="fw-semibold text-secondary">Purpose:</span> {{ $docItem['purpose'] ?: ($request->purpose ?? 'No purpose specified') }}
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.requests.generate', [$request->id, 'doc_type' => $docItem['type']]) }}" 
                                               class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1 fw-semibold shadow-sm flex-shrink-0 d-inline-flex align-items-center gap-1" 
                                               title="Generate Word Document for {{ $docItem['type'] }}">
                                                <i class="fa-solid fa-file-word"></i> <span>Word Doc</span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-light rounded-3 p-3 border mb-2 d-flex justify-content-between align-items-center gap-3">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-bold text-dark" style="font-size: 14px;">{{ $request->document_type }}</span>
                                            @if(!$isBusinessClearance)
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 10.5px;">FREE</span>
                                            @elseif($request->fee !== null && (float)$request->fee > 0)
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 10.5px;">Fee: ₱{{ number_format($request->fee, 2) }}</span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 10.5px;">Fee: To be settled</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small mt-1"><span class="fw-semibold text-secondary">Purpose:</span> {{ $request->purpose ?? 'No purpose provided.' }}</div>
                                    </div>
                                    <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                       class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 fw-semibold shadow-sm flex-shrink-0 d-inline-flex align-items-center gap-1" 
                                       title="Generate Word Document">
                                        <i class="fa-solid fa-file-word"></i> <span>Generate Word Doc</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Row 6: Uploaded Supporting Documents & Document Type --}}
                        <div class="pt-3 border-top mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="text-muted mb-0" style="font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                                    <i class="fa-solid fa-paperclip text-primary me-1"></i> Attached Documents &amp; Type
                                    @if(count($request->attachments_list) > 0)
                                        <span class="badge bg-primary bg-opacity-10 text-primary ms-1 rounded-pill" style="font-size:10.5px;">{{ count($request->attachments_list) }}</span>
                                    @endif
                                </p>
                            </div>

                            @if(count($request->attachments_list) > 0)
                                <div class="row g-2">
                                    @foreach($request->attachments_list as $attIndex => $att)
                                        @php
                                            $isImage = in_array(strtolower($att['file_type']), ['image', 'png', 'jpg', 'jpeg', 'webp', 'gif']);
                                            $fileUrl = asset('storage/' . $att['file_path']);
                                            $jsFileUrl = e($fileUrl, false);
                                            $jsFileName = e($att['file_name'], false);
                                            $jsDocType = e($att['doc_type'], false);
                                        @endphp
                                        <div class="col-md-6">
                                            <div class="bg-light rounded-3 border d-flex align-items-center gap-2 h-100" style="padding: 10px 12px;">
                                                {{-- Thumbnail / Icon preview with click to view --}}
                                                <div class="rounded-3 border bg-white d-flex align-items-center justify-content-center overflow-hidden position-relative shadow-sm" 
                                                     style="width: 52px; height: 52px; flex-shrink: 0; cursor: pointer; transition: transform 0.15s ease;"
                                                     onclick="previewAdminAttachment('{{ $jsFileUrl }}', '{{ addslashes($jsFileName) }}', '{{ addslashes($jsDocType) }}', {{ $isImage ? 'true' : 'false' }})"
                                                     title="Click to preview image/document">
                                                    @if($isImage)
                                                        <i class="fa-solid fa-image text-primary" style="font-size: 22px;"></i>
                                                        <div class="position-absolute inset-0 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center opacity-0 hover-opacity-100" style="transition: opacity 0.2s;">
                                                            <i class="fa-solid fa-magnifying-glass-plus text-primary" style="font-size: 14px;"></i>
                                                        </div>
                                                    @else
                                                        <i class="fa-solid fa-file-pdf text-danger" style="font-size: 24px;"></i>
                                                    @endif
                                                </div>

                                                {{-- Details --}}
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="d-flex align-items-center gap-1 mb-1">
                                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 10px;">
                                                            <i class="fa-solid fa-tag me-1"></i>{{ $att['doc_type'] }}
                                                        </span>
                                                    </div>
                                                    <div class="fw-bold text-dark text-truncate" style="font-size: 12.5px;" title="{{ $att['file_name'] }}">
                                                        {{ $att['file_name'] }}
                                                    </div>
                                                    @if(!empty($att['file_size']))
                                                        <div class="text-muted" style="font-size: 11px;">
                                                            {{ round($att['file_size'] / 1024, 1) }} KB
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Actions --}}
                                                <div class="d-flex flex-column gap-1 flex-shrink-0">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-primary rounded-3 px-2 py-1 shadow-sm" 
                                                            style="font-size: 11px;"
                                                            title="View Image / Document"
                                                            onclick="previewAdminAttachment('{{ $jsFileUrl }}', '{{ addslashes($jsFileName) }}', '{{ addslashes($jsDocType) }}', {{ $isImage ? 'true' : 'false' }})">
                                                        <i class="fa-solid fa-eye me-1"></i> View
                                                    </button>
                                                    <a href="{{ $fileUrl }}" target="_blank" download="{{ $att['file_name'] }}" 
                                                       class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" 
                                                       style="font-size: 11px;"
                                                       title="Open in new tab / download">
                                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-light rounded-3 p-3 border text-center text-muted" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-file-circle-question text-secondary me-1"></i> No uploaded supporting documents attached to this request.
                                </div>
                            @endif
                        </div>

                    </div>{{-- /view-mode --}}

                    {{-- ======================== --}}
                    {{-- EDIT MODE              --}}
                    {{-- ======================== --}}
                    @if($editable)
                    <div id="edit-mode-{{ $request->id }}" class="d-none p-4">
                        <form id="edit-form-{{ $request->id }}"
                              action="{{ route('admin.requests.update', $request->id) }}"
                              method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="alert alert-primary border-0 small rounded-3 mb-4 py-2 px-3 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-pen-to-square"></i>
                                Editing <strong>{{ $request->tracking_number }}</strong> — you can add or remove documents and modify details below.
                            </div>

                            {{-- Name row --}}
                            <div class="row g-3 mb-3">
                                <div class="col-5">
                                    <label class="form-label small fw-semibold text-muted mb-1">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control rounded-3" style="text-transform: uppercase;" value="{{ $request->first_name }}" required>
                                </div>
                                <div class="col-2">
                                    <label class="form-label small fw-semibold text-muted mb-1">M.I.</label>
                                    <input type="text" name="middle_initial" class="form-control rounded-3" style="text-transform: uppercase;" value="{{ $request->middle_initial }}" maxlength="5">
                                </div>
                                <div class="col-5">
                                    <label class="form-label small fw-semibold text-muted mb-1">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control rounded-3" style="text-transform: uppercase;" value="{{ $request->last_name }}" required>
                                </div>
                            </div>

                            {{-- Age + Fee (aligned 50/50) --}}
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Age</label>
                                    <input type="number" name="age" class="form-control rounded-3" value="{{ $request->age }}" min="0" max="150" placeholder="e.g. 25">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Fee Amount (₱)</label>
                                    @if($isBusinessClearance)
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted fw-bold">₱</span>
                                            <input type="number" step="0.01" min="0" name="fee" class="form-control rounded-end-3 font-monospace fw-bold" 
                                                   value="{{ $request->fee !== null ? number_format($request->fee, 2, '.', '') : '' }}" 
                                                   placeholder="0.00 (Leave blank for 'To be settled')">
                                        </div>
                                    @else
                                        <input type="text" class="form-control rounded-3 bg-light text-success fw-bold" value="FREE" readonly>
                                        <input type="hidden" name="fee" value="0">
                                    @endif
                                </div>
                            </div>

                            {{-- Email + Contact (aligned 50/50) --}}
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Email</label>
                                    <input type="email" name="resident_email" class="form-control rounded-3" value="{{ $request->resident_email }}" placeholder="resident@example.com">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-muted mb-1">Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control rounded-3" value="{{ $request->contact_number }}" placeholder="09xxxxxxxxx">
                                </div>
                            </div>

                            {{-- ID Type (for Identification) --}}
                            <div class="row g-3 mb-3">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-muted mb-1">
                                        <i class="fa-solid fa-id-card text-primary me-1"></i> ID Type (for Identification)
                                    </label>
                                    <select name="id_type" class="form-select rounded-3">
                                        <option value="">-- Not Specified --</option>
                                        @php
                                            $currentIdType = $request->id_type ?? $request->resolved_id_type ?? $request->user?->id_type;
                                            $commonIdTypes = [
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
                                        @foreach($commonIdTypes as $typeOption)
                                            <option value="{{ $typeOption }}" {{ $currentIdType == $typeOption ? 'selected' : '' }}>{{ $typeOption }}</option>
                                        @endforeach
                                        @if($currentIdType && !in_array($currentIdType, $commonIdTypes))
                                            <option value="{{ $currentIdType }}" selected>{{ $currentIdType }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            {{-- REQUESTED DOCUMENT --}}
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">
                                        <i class="fa-solid fa-file-lines text-primary me-1"></i> Document Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-sm rounded-3" name="document_type" required>
                                        @php
                                            $allDocOptions = !empty($validTypes) ? $validTypes : ['Barangay Clearance', 'Certificate of Residency', 'Certificate of Indigency'];
                                            if (!empty($request->document_type) && !in_array($request->document_type, $allDocOptions)) {
                                                $allDocOptions[] = $request->document_type;
                                            }
                                        @endphp
                                        @foreach($allDocOptions as $optType)
                                            <option value="{{ $optType }}" {{ $request->document_type === $optType ? 'selected' : '' }}>{{ $optType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">
                                        <i class="fa-solid fa-bullseye text-primary me-1"></i> Purpose <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-sm rounded-3" name="purpose" 
                                           style="text-transform: uppercase;"
                                           value="{{ $request->purpose }}" 
                                           placeholder="e.g. SCHOLARSHIP APPLICATION, EMPLOYMENT" required>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif

                </div>{{-- /modal-body --}}

                {{-- ======================== --}}
                {{-- FOOTER                --}}
                {{-- ======================== --}}
                <div class="modal-footer bg-light border-top px-4 py-3 d-flex justify-content-between align-items-center gap-2">

                    {{-- Left: Delete (only for terminal states — Rejected or Completed) --}}
                    <div>
                        @if($editable && in_array($request->status, ['Rejected', 'Completed']))
                            <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" class="m-0"
                                  onsubmit="return confirm('Permanently delete {{ $request->tracking_number }}? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-3">
                                    <i class="fa-solid fa-trash me-1"></i> Delete Permanently
                                </button>
                            </form>
                        @else
                            <span></span>
                        @endif
                    </div>

                    {{-- Right: Close + Save Changes + Workflow Action Buttons --}}
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-light border rounded-3 px-3.5 py-1.5 fw-semibold shadow-sm" data-bs-dismiss="modal">Close</button>

                        @if($editable)
                            <button type="submit" form="edit-form-{{ $request->id }}"
                                    class="btn btn-sm btn-primary rounded-3 px-3.5 py-1.5 fw-semibold shadow-sm d-none"
                                    id="btn-save-{{ $request->id }}">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>

                            @if(in_array($request->status, ['Pending', 'Under Review']))
                            <div id="accept-reject-{{ $request->id }}" class="d-flex gap-2">
                                <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST" class="m-0"
                                      onsubmit="return confirm('Reject this request? The resident will be notified.');">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-3 py-1.5 fw-semibold">
                                        <i class="fa-solid fa-xmark me-1"></i> Reject
                                    </button>
                                </form>
                                <form action="{{ route('admin.requests.accept', $request->id) }}" method="POST" class="m-0">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success rounded-3 px-3.5 py-1.5 fw-semibold shadow-sm">
                                        <i class="fa-solid fa-check me-1"></i> Accept Request
                                    </button>
                                </form>
                            </div>
                            @elseif($request->status == 'Processing')
                            <div id="accept-reject-{{ $request->id }}" class="d-flex gap-2">
                                <form action="{{ route('admin.requests.ready', $request->id) }}" method="POST" class="m-0">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-info text-white rounded-3 px-3.5 py-1.5 fw-semibold shadow-sm">
                                        <i class="fa-solid fa-bell me-1"></i> Ready for Pickup
                                    </button>
                                </form>
                            </div>
                            @elseif($request->status == 'Ready')
                            <div id="accept-reject-{{ $request->id }}" class="d-flex gap-2">
                                <form action="{{ route('admin.requests.complete', $request->id) }}" method="POST" class="m-0">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success rounded-3 px-3.5 py-1.5 fw-semibold shadow-sm">
                                        <i class="fa-solid fa-check-double me-1"></i> Mark Claimed / Completed
                                    </button>
                                </form>
                            </div>
                            @else
                            <div id="accept-reject-{{ $request->id }}"></div>
                            @endif
                        @endif
                    </div>

                </div>{{-- /modal-footer --}}

            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ============================================ --}}
{{-- ADMIN ATTACHMENT PREVIEW LIGHTBOX MODAL     --}}
{{-- ============================================ --}}
<div class="modal fade" id="adminAttachmentPreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 px-4 pt-3 d-flex align-items-center justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1" id="previewModalDocType" style="font-size: 11.5px;">
                            <i class="fa-solid fa-tag me-1"></i> Document Type
                        </span>
                    </div>
                    <h6 class="fw-bold text-dark mb-0 text-truncate" id="previewModalFileName" style="font-size: 14px; max-width: 480px;">Filename</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="previewModalDownloadBtn" class="btn btn-sm btn-outline-primary rounded-3 px-3" target="_blank" download>
                        <i class="fa-solid fa-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4 text-center">
                <div id="previewModalImageContainer" class="d-none">
                    <img id="previewModalImage" src="" alt="Attachment Preview" class="img-fluid rounded-3 border shadow-sm" style="max-height: 72vh; object-fit: contain; width: auto;">
                </div>
                <div id="previewModalDocContainer" class="d-none">
                    <iframe id="previewModalFrame" src="" class="w-100 rounded-3 border" style="height: 68vh;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.previewAdminAttachment = function(url, fileName, docType, isImage) {
        const previewModalEl = document.getElementById('adminAttachmentPreviewModal');
        if (!previewModalEl) return;

        const docTypeBadge = document.getElementById('previewModalDocType');
        const fileNameEl = document.getElementById('previewModalFileName');
        const downloadBtn = document.getElementById('previewModalDownloadBtn');
        const imgContainer = document.getElementById('previewModalImageContainer');
        const docContainer = document.getElementById('previewModalDocContainer');
        const imgEl = document.getElementById('previewModalImage');
        const frameEl = document.getElementById('previewModalFrame');

        if (docTypeBadge) docTypeBadge.innerHTML = '<i class="fa-solid fa-tag me-1"></i> ' + (docType || 'Supporting Document');
        if (fileNameEl) fileNameEl.textContent = fileName || 'Attachment';
        if (downloadBtn) {
            downloadBtn.href = url;
            downloadBtn.setAttribute('download', fileName || 'attachment');
        }

        // Auto-detect image vs document if not strictly boolean
        if (typeof isImage === 'undefined' || isImage === null) {
            const cleanUrl = (url || '').split('?')[0].split('#')[0].toLowerCase();
            isImage = /\.(jpg|jpeg|png|webp|gif|svg)$/i.test(cleanUrl);
        } else {
            isImage = Boolean(isImage);
        }

        if (isImage) {
            if (imgEl) imgEl.src = url;
            if (imgContainer) imgContainer.classList.remove('d-none');
            if (docContainer) docContainer.classList.add('d-none');
            if (frameEl) frameEl.src = '';
        } else {
            if (frameEl) frameEl.src = url;
            if (docContainer) docContainer.classList.remove('d-none');
            if (imgContainer) imgContainer.classList.add('d-none');
            if (imgEl) imgEl.src = '';
        }

        const modal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
        modal.show();
    };

    function toggleEditMode(id, editing) {
        const viewMode      = document.getElementById('view-mode-' + id);
        const editMode      = document.getElementById('edit-mode-' + id);
        const btnEdit       = document.getElementById('btn-edit-' + id);
        const btnCancel     = document.getElementById('btn-cancel-edit-' + id);
        const btnSave       = document.getElementById('btn-save-' + id);
        const btnHeaderSave = document.getElementById('btn-header-save-' + id);
        const acceptReject  = document.getElementById('accept-reject-' + id);
        const titleLabel    = document.getElementById('modal-title-label-' + id);

        if (editing) {
            viewMode?.classList.add('d-none');
            editMode?.classList.remove('d-none');
            btnEdit?.classList.add('d-none');
            btnCancel?.classList.remove('d-none');
            btnSave?.classList.remove('d-none');
            btnHeaderSave?.classList.remove('d-none');
            acceptReject?.classList.add('d-none');
            if (titleLabel) titleLabel.textContent = 'Edit Request';
        } else {
            viewMode?.classList.remove('d-none');
            editMode?.classList.add('d-none');
            btnEdit?.classList.remove('d-none');
            btnCancel?.classList.add('d-none');
            btnSave?.classList.add('d-none');
            btnHeaderSave?.classList.add('d-none');
            acceptReject?.classList.remove('d-none');
            if (titleLabel) titleLabel.textContent = 'Request Details';
        }
    }
    window.toggleEditMode = toggleEditMode;
    window.syncDocsJson = function(id) { return true; };

    // Automatically reset modal to view mode when closed
    document.addEventListener('hidden.bs.modal', function(e) {
        if (e.target && e.target.id && e.target.id.startsWith('viewModal')) {
            const id = e.target.id.replace('viewModal', '');
            if (typeof window.toggleEditMode === 'function') {
                window.toggleEditMode(id, false);
            }
        }
    });

    // Support nested preview modal over viewModal (adjust z-index and keep body modal-open)
    document.addEventListener('DOMContentLoaded', function() {
        const previewModalEl = document.getElementById('adminAttachmentPreviewModal');
        if (previewModalEl) {
            previewModalEl.addEventListener('show.bs.modal', function() {
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops[backdrops.length - 1].style.zIndex = '1065';
                    }
                }, 10);
            });
            previewModalEl.addEventListener('hidden.bs.modal', function() {
                if (document.querySelector('.modal.show')) {
                    document.body.classList.add('modal-open');
                }
            });
        }
    });

    function markAsProcessing(id) {
        let badgeContainer = document.getElementById('status-badge-' + id);
        if(badgeContainer) {
            badgeContainer.innerHTML = '<span class="status-badge status-processing">Processing</span>';
        }

        let mobileBadgeContainer = document.getElementById('status-badge-mobile-' + id);
        if(mobileBadgeContainer) {
            mobileBadgeContainer.innerHTML = '<span class="status-badge status-processing">Processing</span>';
        }
        
        let modalStatus = document.getElementById('modal-status-' + id);
        if(modalStatus) {
            modalStatus.innerHTML = 'Processing';
            modalStatus.className = 'fw-bold text-primary';
        }

        let readyBtnContainer = document.getElementById('ready-btn-container-' + id);
        if (readyBtnContainer) {
            readyBtnContainer.classList.remove('d-none');
        }

        let mobileReadyBtnContainer = document.getElementById('ready-btn-container-mobile-' + id);
        if (mobileReadyBtnContainer) {
            mobileReadyBtnContainer.classList.remove('d-none');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Enforce CapsLock / uppercase typing for editable request fields
        document.addEventListener('input', function(e) {
            if (e.target && e.target.matches('input[name="first_name"], input[name="last_name"], input[name="middle_initial"], input[name="purpose"], .doc-row-purpose')) {
                const start = e.target.selectionStart;
                const end = e.target.selectionEnd;
                if (typeof e.target.value === 'string') {
                    e.target.value = e.target.value.toUpperCase();
                }
                if (start !== null && end !== null) {
                    e.target.setSelectionRange(start, end);
                }
            }
        });

        const searchInput = document.getElementById('searchInput');
        let debounceTimer;

        // 1. Live Search Logic
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                const searchTerm = this.value;
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('search', searchTerm); 
                
                const fetchUrl = window.location.pathname + '?' + urlParams.toString();
                window.history.pushState({}, '', fetchUrl);

                fetch(fetchUrl)
                    .then(response => response.text())
                    .then(html => {
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(html, 'text/html');
                        
                        let newTbody = doc.getElementById('requests-tbody');
                        let currentTbody = document.getElementById('requests-tbody');
                        if (newTbody && currentTbody) currentTbody.innerHTML = newTbody.innerHTML;

                        let newMobile = doc.getElementById('requests-mobile-list');
                        let currentMobile = document.getElementById('requests-mobile-list');
                        if (newMobile && currentMobile) currentMobile.innerHTML = newMobile.innerHTML;

                        let newModals = doc.getElementById('modals-container');
                        let currentModals = document.getElementById('modals-container');
                        if (newModals && currentModals) currentModals.innerHTML = newModals.innerHTML;

                        let newPag = doc.getElementById('pagination-container');
                        let currentPag = document.getElementById('pagination-container');
                        if (newPag && currentPag) {
                            currentPag.innerHTML = newPag.innerHTML;
                        } else if (newPag && !currentPag) {
                            let mobileEl = document.getElementById('requests-mobile-list');
                            if (mobileEl) mobileEl.after(newPag);
                        } else if (!newPag && currentPag) {
                            currentPag.remove();
                        }
                    })
                    .catch(error => console.error("Live search error:", error));
            }, 300);
        });

        // 2. Real-Time Auto-Refresh Polling (30 Seconds, paused when tab is inactive)
        setInterval(function() {
            if (document.hidden) return;

            // Stricter checks to ensure we NEVER refresh while a modal is opening/open
            let isModalOpen = document.body.classList.contains('modal-open') || 
                              document.querySelector('.modal.show') !== null ||
                              document.querySelector('.modal-backdrop') !== null;

            if (document.activeElement === searchInput || isModalOpen) {
                return; 
            }

            const fetchUrl = window.location.href;

            fetch(fetchUrl)
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');

                    let newTbody = doc.getElementById('requests-tbody');
                    let currentTbody = document.getElementById('requests-tbody');
                    if (newTbody && currentTbody) currentTbody.innerHTML = newTbody.innerHTML;

                    let newMobile = doc.getElementById('requests-mobile-list');
                    let currentMobile = document.getElementById('requests-mobile-list');
                    if (newMobile && currentMobile) currentMobile.innerHTML = newMobile.innerHTML;

                    let newModals = doc.getElementById('modals-container');
                    let currentModals = document.getElementById('modals-container');
                    if (newModals && currentModals) currentModals.innerHTML = newModals.innerHTML;

                    let newPag = doc.getElementById('pagination-container');
                    let currentPag = document.getElementById('pagination-container');
                    if (newPag && currentPag) {
                        currentPag.innerHTML = newPag.innerHTML;
                    } else if (newPag && !currentPag) {
                        let mobileEl = document.getElementById('requests-mobile-list');
                        if (mobileEl) mobileEl.after(newPag);
                    } else if (!newPag && currentPag) {
                        currentPag.remove();
                    }

            const elementIdsToUpdate = [
                'kpi-total', 'kpi-pending', 'kpi-approved', 'kpi-overdue', 'kpi-avg',
                'tab-badge-pending', 'tab-badge-processing', 'tab-badge-ready', 'tab-badge-completed', 'tab-badge-rejected'
            ];

            elementIdsToUpdate.forEach(id => {
                let newEl = doc.getElementById(id);
                let oldEl = document.getElementById(id);
                if (newEl && oldEl) oldEl.innerHTML = newEl.innerHTML;
            });
        })
        .catch(error => console.error("Auto-refresh error:", error));

        }, 30000); 

        // Table Density / Zoom Adjustment Mode
        const btnToggleDensity = document.getElementById('btnToggleDensity');
        const reqTable = document.querySelector('.table-requests');
        const densityIcon = document.getElementById('densityIcon');
        const densityText = document.getElementById('densityText');

        if (btnToggleDensity && reqTable) {
            function updateDensity(isCompact) {
                if (isCompact) {
                    reqTable.classList.add('table-compact');
                    if (densityIcon) densityIcon.className = 'fa-solid fa-expand me-1';
                    if (densityText) densityText.textContent = 'Normal';
                    btnToggleDensity.classList.add('btn-primary', 'text-white');
                    btnToggleDensity.classList.remove('btn-light', 'text-muted');
                    localStorage.setItem('adminRequestTableDensity', 'compact');
                } else {
                    reqTable.classList.remove('table-compact');
                    if (densityIcon) densityIcon.className = 'fa-solid fa-compress me-1';
                    if (densityText) densityText.textContent = 'Compact';
                    btnToggleDensity.classList.remove('btn-primary', 'text-white');
                    btnToggleDensity.classList.add('btn-light', 'text-muted');
                    localStorage.setItem('adminRequestTableDensity', 'normal');
                }
            }

            if (localStorage.getItem('adminRequestTableDensity') === 'compact') {
                updateDensity(true);
            }

            btnToggleDensity.addEventListener('click', function() {
                const isCurrentlyCompact = reqTable.classList.contains('table-compact');
                updateDensity(!isCurrentlyCompact);
            });
        }
    });
</script>

{{-- MODAL: BULK DELETE CONFIRMATION FOR REQUESTS --}}
<div class="modal fade" id="requestsBulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-trash-can fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Delete Selected Requests?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to permanently delete <strong id="bulk_modal_count" class="text-danger"></strong> selected document request(s)? This action cannot be undone.
                </p>
                <div class="bg-light rounded-3 p-3 text-start small mb-4 border" style="max-height: 140px; overflow-y: auto;">
                    <div class="fw-semibold text-muted mb-1 text-uppercase" style="font-size: 11px;">Selected Requests:</div>
                    <ul id="bulk_selected_names_list" class="mb-0 ps-3 text-dark font-monospace"></ul>
                </div>
                <form id="requestsBulkDeleteForm" action="{{ route('admin.requests.bulk-delete') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <div id="bulk_hidden_ids"></div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light border px-4 fw-semibold rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 fw-semibold rounded-pill shadow-sm">
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
    const selectAllCheckbox = document.getElementById('selectAllRequests');
    const reqCheckboxes = document.querySelectorAll('.req-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const bulkDeleteModalEl = document.getElementById('requestsBulkDeleteModal');
    const bulkDeleteModal = bulkDeleteModalEl ? new bootstrap.Modal(bulkDeleteModalEl) : null;
    const bulkHiddenIds = document.getElementById('bulk_hidden_ids');
    const bulkModalCount = document.getElementById('bulk_modal_count');
    const bulkSelectedNamesList = document.getElementById('bulk_selected_names_list');

    function updateBulkBar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.req-checkbox:checked'));
        const count = checkedBoxes.length;

        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            selectedCountBadge.textContent = `${count} Selected`;
            bulkDeleteCount.textContent = count;
        } else {
            bulkActionsBar.classList.add('d-none');
        }

        // Highlight table rows and mobile cards
        document.querySelectorAll('.req-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                if (cb.checked) {
                    row.classList.add('table-primary');
                } else {
                    row.classList.remove('table-primary');
                }
            }
            const card = cb.closest('.mobile-request-card');
            if (card) {
                if (cb.checked) {
                    card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                } else {
                    card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                }
            }
        });

        if (selectAllCheckbox) {
            const allTableBoxes = document.querySelectorAll('#requestsMasterTable .req-checkbox');
            const checkedTableBoxes = document.querySelectorAll('#requestsMasterTable .req-checkbox:checked');
            selectAllCheckbox.checked = (allTableBoxes.length > 0 && checkedTableBoxes.length === allTableBoxes.length);
            selectAllCheckbox.indeterminate = (checkedTableBoxes.length > 0 && checkedTableBoxes.length < allTableBoxes.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            reqCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkBar();
        });
    }

    reqCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            reqCheckboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    if (bulkDeleteBtn && bulkDeleteModal) {
        bulkDeleteBtn.addEventListener('click', function () {
            const checkedBoxes = Array.from(document.querySelectorAll('.req-checkbox:checked'));
            if (checkedBoxes.length === 0) return;

            bulkHiddenIds.innerHTML = '';
            bulkSelectedNamesList.innerHTML = '';
            bulkModalCount.textContent = `${checkedBoxes.length}`;

            const seenIds = new Set();
            checkedBoxes.forEach(cb => {
                if (seenIds.has(cb.value)) return;
                seenIds.add(cb.value);

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'ids[]';
                hiddenInput.value = cb.value;
                bulkHiddenIds.appendChild(hiddenInput);

                const li = document.createElement('li');
                li.textContent = `Tracking #${cb.getAttribute('data-tracking') || cb.value}`;
                bulkSelectedNamesList.appendChild(li);
            });

            bulkDeleteModal.show();
        });
    }
});
</script>
@endsection