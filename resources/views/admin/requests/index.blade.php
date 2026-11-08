@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">
    <div class="header-pipeline-bar d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-3">
        {{-- Left: Title & Subtitle --}}
        <div class="header-title-box">
            <h2 class="header-title fw-bold m-0" style="font-size: 1.5rem; letter-spacing: -0.025em; line-height: 1.2;">Request Management</h2>
            <p class="header-subtitle mb-0 mt-0.5 text-muted" style="font-size: 0.8125rem;">Manage and process document requests from residents</p>
        </div>

        {{-- Right: Live Indicator Badge (Hidden per user request) --}}
        <div class="d-none align-items-center gap-2">
            <span class="live-badge" id="live-status-indicator" style="display: none !important;">
                <span class="live-status-dot"></span>LIVE
            </span>
            <span class="text-muted" style="font-size: 11px; display: none !important;" id="last-refreshed-label">Auto-refreshes every 10s</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-3 shadow-sm d-flex align-items-center" role="alert" style="background-color: #f0fdf4; color: #166534; border: 1.5px solid #bbf7d0; margin-bottom: var(--spacing-gap, 14px) !important;">
            <i class="fa-solid fa-circle-check me-2 text-success fa-lg"></i>
            <span class="fw-medium">{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible show rounded-3 mb-3 shadow-sm d-flex align-items-start" role="alert" style="background-color: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; margin-bottom: var(--spacing-gap, 14px) !important;">
            <i class="fa-solid fa-triangle-exclamation me-2 text-danger fa-lg mt-1"></i>
            <div class="flex-grow-1">
                <div class="fw-bold mb-1">Please check the form for errors:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="stat-cards-grid" style="margin-bottom: var(--spacing-gap, 14px);">
        {{-- Card 1: Total & Active Workload Requests --}}
        <a href="{{ route('admin.requests', ['status' => 'All', 'quick_filter' => 'active']) }}" 
           class="stat-card request-stat-card clickable-kpi-card text-decoration-none {{ request('quick_filter') == 'active' ? 'active-kpi-card' : '' }}"
           title="Filter active workload (Pending + Processing + Ready)">
            <div class="stat-card-icon-box" style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                <i class="fa-solid fa-folder-tree"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Total Requests</div>
                <h3 class="stat-card-count" id="kpi-total">{{ $totalRequests }}</h3>
                <div class="stat-card-subtext">All-time: {{ $totalRequests }} records</div>
            </div>
        </a>

        {{-- Card 2: Pending Reviews --}}
        <a href="{{ route('admin.requests', ['status' => 'Pending_Review']) }}" 
           class="stat-card request-stat-card clickable-kpi-card text-decoration-none {{ $currentStatus == 'Pending_Review' ? 'active-kpi-card' : '' }}"
           title="Jump to Pending & Review stage">
            <div class="stat-card-icon-box" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Pending Reviews</div>
                <h3 class="stat-card-count" id="kpi-pending">{{ $tabCounts['Pending_Review'] ?? $pendingReviews }}</h3>
                <div class="stat-card-subtext">
                    @if(($tabCounts['Pending_Review'] ?? $pendingReviews) == 0)
                        <span class="text-success fw-semibold">✓ All caught up</span>
                    @else
                        <span class="text-warning fw-semibold">Requires attention</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Card 3: Approved Today --}}
        <a href="{{ route('admin.requests', ['quick_filter' => 'today']) }}" 
           class="stat-card request-stat-card clickable-kpi-card text-decoration-none {{ request('quick_filter') == 'today' ? 'active-kpi-card' : '' }}"
           title="View requests approved / processed today">
            <div class="stat-card-icon-box" style="background-color: #dcfce7; color: var(--color-green, #15803d); border: 1px solid #bbf7d0;">
                <i class="fa-solid fa-check"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Approved Today</div>
                <h3 class="stat-card-count" style="color: var(--color-green, #15803d);" id="kpi-approved">{{ $approvedToday }}</h3>
                <div class="stat-card-subtext">Processing complete</div>
            </div>
        </a>

        {{-- Card 4: Overdue Requests (Dynamic Red Alert State) --}}
        <a href="{{ route('admin.requests', ['quick_filter' => 'overdue']) }}" 
           class="stat-card request-stat-card clickable-kpi-card text-decoration-none {{ $overdueRequests > 0 ? 'overdue-alert-card' : '' }} {{ request('quick_filter') == 'overdue' ? 'active-kpi-card' : '' }}"
           title="Jump to overdue requests (older than 48 hours)">
            <div class="stat-card-icon-box" style="background-color: {{ $overdueRequests > 0 ? '#fee2e2' : '#f1f5f9' }}; color: {{ $overdueRequests > 0 ? '#b91c1c' : '#64748b' }}; border: 1px solid {{ $overdueRequests > 0 ? '#fca5a5' : '#e2e8f0' }};">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Overdue Requests</div>
                <h3 class="stat-card-count" style="color: {{ $overdueRequests > 0 ? 'var(--color-red, #d92d3a)' : 'var(--color-text)' }};" id="kpi-overdue">{{ $overdueRequests }}</h3>
                <div class="stat-card-subtext">
                    @if($overdueRequests == 0)
                        <span class="text-muted fw-medium"><i class="fa-solid fa-shield-check text-success me-1"></i>Within SLA ✓</span>
                    @else
                        <span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i>Past deadline (SLA)</span>
                    @endif
                </div>
            </div>
        </a>

        {{-- Card 5: Avg Turnaround --}}
        <div class="stat-card request-stat-card">
            <div class="stat-card-icon-box" style="background-color: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4;">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label">Avg Turnaround</div>
                <h3 class="stat-card-count" id="kpi-avg">{{ $avgProcessing > 0 ? $avgProcessing . ' d' : '—' }}</h3>
                <div class="stat-card-subtext">Days per request</div>
            </div>
        </div>
    </div>

    <style>
        /* ═══════════════════════════════════════════════════════════
           1. PROCESS PIPELINE WORKFLOW (FULL-WIDTH, LEADING ARROWS)
           ═══════════════════════════════════════════════════════════ */
        .process-pipeline-wrapper {
            width: 100%;
        }

        .process-pipeline-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            background: var(--color-bg-subtle, #f8fafc);
            padding: 5px 8px;
            border-radius: 10px;
            border: 1px solid var(--color-border, #e2e8f0);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            white-space: nowrap;
            scrollbar-width: thin;
        }

        .process-pipeline-stages {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: nowrap;
            flex-grow: 1;
        }

        .pipeline-step-item {
            display: inline-flex;
            align-items: center;
        }

        .pipeline-pill-link {
            display: inline-flex;
            align-items: center;
            padding: 4px 11px;
            font-size: 12px;
            font-weight: 600;
            border-radius: var(--radius-pill, 9999px);
            border: 1.5px solid var(--color-border, #e2e8f0);
            background-color: var(--color-surface, #ffffff);
            color: var(--color-text, #0f172a);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            transition: all var(--transition-bounce, 0.2s cubic-bezier(0.16, 1, 0.3, 1));
            text-decoration: none;
            letter-spacing: -0.01em;
            white-space: nowrap;
            cursor: pointer;
            min-height: 30px;
        }

        .pipeline-pill-link:hover {
            transform: translateY(-1.5px);
            border-color: var(--color-border-hover, #cbd5e1);
            background-color: var(--color-bg-subtle, #f8fafc);
            color: var(--color-text, #0f172a);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
        }

        .pipeline-pill-link:active {
            transform: translateY(0);
        }

        /* Sequential Step Number Badge (1, 2, 3, 4) */
        .step-badge-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            font-size: 9.5px;
            font-weight: 700;
            background-color: var(--color-bg-subtle, #f8fafc);
            color: var(--color-text-muted, #64748b);
            border: 1px solid var(--color-border, #e2e8f0);
            margin-right: 5px;
            flex-shrink: 0;
            transition: all var(--transition-fast, 0.15s ease);
        }

        .pipeline-pill-link .text-step-icon {
            font-size: 11px;
            margin-right: 5px;
            color: var(--color-text-muted, #64748b);
            transition: color var(--transition-fast, 0.15s ease);
        }

        /* ═══════════════════════════════════════════════════════════
           DIRECTIONAL ARROW CONNECTORS WITH LEADING LINES
           ═══════════════════════════════════════════════════════════ */
        .pipeline-arrow-connector {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
            padding: 0 4px;
            color: var(--color-text-light, #94a3b8);
            flex-shrink: 0;
            user-select: none;
            position: relative;
            transition: transform var(--transition-fast, 0.2s ease), color var(--transition-fast, 0.2s ease);
        }

        /* Leading Line (Shaft of the Leading Arrow) */
        .pipeline-arrow-connector::before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 2px;
            background: currentColor;
            border-radius: 999px;
            opacity: 0.65;
            transition: all var(--transition-fast, 0.2s ease);
        }

        .pipeline-arrow-connector svg {
            width: 8px;
            height: 8px;
            stroke-width: 2.8;
            transition: transform var(--transition-fast, 0.2s ease);
            position: relative;
            left: -1px;
        }

        /* Hover animation on leading line */
        .pipeline-arrow-connector:hover,
        .pipeline-step-item:hover + .pipeline-arrow-connector {
            color: var(--color-blue, #2563b8);
            transform: translateX(2px);
        }

        .pipeline-arrow-connector:hover::before,
        .pipeline-step-item:hover + .pipeline-arrow-connector::before {
            width: 22px;
            opacity: 1;
            box-shadow: 0 0 8px rgba(37, 99, 184, 0.4);
        }

        /* Dynamic Active Step Leading Lines */
        .pipeline-step-item:has(.tab-pending.active) + .pipeline-arrow-connector {
            color: #d97706;
        }
        .pipeline-step-item:has(.tab-pending.active) + .pipeline-arrow-connector::before {
            background: linear-gradient(90deg, #d97706, #2563eb);
            opacity: 0.95;
            box-shadow: 0 0 6px rgba(217, 119, 6, 0.35);
        }

        .pipeline-step-item:has(.tab-processing.active) + .pipeline-arrow-connector {
            color: #2563eb;
        }
        .pipeline-step-item:has(.tab-processing.active) + .pipeline-arrow-connector::before {
            background: linear-gradient(90deg, #2563eb, #0284c7);
            opacity: 0.95;
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.35);
        }

        .pipeline-step-item:has(.tab-ready.active) + .pipeline-arrow-connector {
            color: #0284c7;
        }
        .pipeline-step-item:has(.tab-ready.active) + .pipeline-arrow-connector::before {
            background: linear-gradient(90deg, #0284c7, #16a34a);
            opacity: 0.95;
            box-shadow: 0 0 6px rgba(2, 132, 199, 0.35);
        }

        /* Status Count Badge */
        .pipeline-pill-link .status-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6.5px;
            min-width: 19px;
            border-radius: var(--radius-pill, 9999px);
            margin-left: 5px;
            line-height: 1.3;
            background-color: var(--color-bg-subtle, #f8fafc);
            color: #334155;
            border: 1px solid var(--color-border, #e2e8f0);
            transition: all var(--transition-fast, 0.15s ease);
        }

        /* Step Personality Inactive State (Creative Color Coding) */
        .pipeline-pill-link.tab-pending {
            border-color: #fde68a;
            background-color: #fffdf5;
            color: #92400e;
        }
        .pipeline-pill-link.tab-pending:hover {
            border-color: #f59e0b;
            background-color: #fef3c7;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
        }
        .pipeline-pill-link.tab-pending .step-badge-number {
            background-color: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
        }
        .pipeline-pill-link.tab-pending .status-count-badge {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .pipeline-pill-link.tab-processing {
            border-color: #bfdbfe;
            background-color: #f8faff;
            color: #1e40af;
        }
        .pipeline-pill-link.tab-processing:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        .pipeline-pill-link.tab-processing .step-badge-number {
            background-color: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }
        .pipeline-pill-link.tab-processing .status-count-badge {
            background-color: #eff6ff;
            color: #1e40af;
            border-color: #bfdbfe;
        }

        .pipeline-pill-link.tab-ready {
            border-color: #a5f3fc;
            background-color: #f0fdfa;
            color: #0e7490;
        }
        .pipeline-pill-link.tab-ready:hover {
            border-color: #06b6d4;
            background-color: #ecfeff;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.15);
        }
        .pipeline-pill-link.tab-ready .step-badge-number {
            background-color: #ecfeff;
            color: #0891b2;
            border-color: #a5f3fc;
        }
        .pipeline-pill-link.tab-ready .status-count-badge {
            background-color: #ecfeff;
            color: #0e7490;
            border-color: #a5f3fc;
        }

        .pipeline-pill-link.tab-completed {
            border-color: #bbf7d0;
            background-color: #f7fee7;
            color: #166534;
        }
        .pipeline-pill-link.tab-completed:hover {
            border-color: #22c55e;
            background-color: #f0fdf4;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
        }
        .pipeline-pill-link.tab-completed .step-badge-number {
            background-color: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }
        .pipeline-pill-link.tab-completed .status-count-badge {
            background-color: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        /* Active Stage Tab (Vibrant Gradient per Stage with Crisp White Badges) */
        .pipeline-pill-link.active {
            font-weight: 700;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .pipeline-pill-link.active .step-badge-number {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
        }

        .pipeline-pill-link.active .status-count-badge {
            background-color: #ffffff !important;
            border-color: #ffffff !important;
            font-weight: 800;
        }

        .pipeline-pill-link.active .text-step-icon {
            color: #ffffff !important;
        }

        /* Specific Active Colors */
        .pipeline-pill-link.tab-pending.active {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
            border-color: #d97706 !important;
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.35) !important;
        }
        .pipeline-pill-link.tab-pending.active .status-count-badge {
            color: #b45309 !important;
        }

        .pipeline-pill-link.tab-processing.active {
            background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%) !important;
            border-color: #1d4ed8 !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35) !important;
        }
        .pipeline-pill-link.tab-processing.active .status-count-badge {
            color: #1e40af !important;
        }

        .pipeline-pill-link.tab-ready.active {
            background: linear-gradient(135deg, #0e7490 0%, #06b6d4 100%) !important;
            border-color: #0891b2 !important;
            box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35) !important;
        }
        .pipeline-pill-link.tab-ready.active .status-count-badge {
            color: #0e7490 !important;
        }

        .pipeline-pill-link.tab-completed.active {
            background: linear-gradient(135deg, #15803d 0%, #22c55e 100%) !important;
            border-color: #15803d !important;
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35) !important;
        }
        .pipeline-pill-link.tab-completed.active .status-count-badge {
            color: #15803d !important;
        }

        .pipeline-pill-link.tab-rejected.active {
            background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%) !important;
            border-color: #b91c1c !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35) !important;
        }
        .pipeline-pill-link.tab-rejected.active .status-count-badge {
            background-color: #ffffff !important;
            color: #b91c1c !important;
            border-color: #ffffff !important;
        }
        .pipeline-pill-link.tab-rejected.active .text-step-icon {
            color: #ffffff !important;
        }

        /* Ready Tab Waiting Dot with Pulse Animation */
        .stage-waiting-action {
            border-color: #f59e0b !important;
        }
        .waiting-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--color-amber, #f59e0b);
            margin-left: 5px;
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
            animation: dotPulse 1.8s infinite;
        }
        @keyframes dotPulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(245, 158, 11, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }

        /* Exception Branch: Rejected (Separated & Tinted Red) */
        .pipeline-exception-wrapper {
            display: inline-flex;
            align-items: center;
            margin-left: auto;
            flex-shrink: 0;
        }

        .exception-divider {
            width: 1px;
            height: 18px;
            background-color: var(--color-border, #e2e8f0);
            margin-right: 8px;
        }

        .pipeline-pill-link.tab-rejected {
            border-color: #fecaca;
            background-color: #fff5f5;
            color: var(--color-red, #d92d3a);
        }

        .pipeline-pill-link.tab-rejected:hover {
            border-color: var(--color-red, #d92d3a);
            background-color: #fee2e2;
            color: #991b1b;
        }

        .pipeline-pill-link.tab-rejected .status-count-badge {
            background-color: #fee2e2;
            color: var(--color-red, #d92d3a);
            border-color: #fca5a5;
        }

        .pipeline-pill-link.tab-rejected.active {
            background: var(--color-red, #d92d3a) !important;
            border-color: var(--color-red, #d92d3a) !important;
            color: #ffffff !important;
            box-shadow: 0 3px 10px rgba(217, 45, 58, 0.28) !important;
        }
        .pipeline-pill-link.tab-rejected.active .status-count-badge {
            background-color: #ffffff !important;
            color: var(--color-red, #d92d3a) !important;
            border-color: #ffffff !important;
        }
        .pipeline-pill-link.tab-rejected.active .text-step-icon {
            color: #ffffff !important;
        }

        /* ═══════════════════════════════════════════════════════════
           2. COMPACT STAT CARDS (SECOND IN LAYOUT)
           ═══════════════════════════════════════════════════════════ */
        .stat-cards-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: var(--spacing-gap, 14px);
            width: 100%;
        }

        @media (max-width: 1200px) {
            .stat-cards-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 768px) {
            .stat-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .stat-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: var(--color-surface, #ffffff);
            border: 1px solid var(--color-border, #e6ebf1);
            border-radius: var(--radius-card, 12px);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-card, 0 1px 3px rgba(0, 0, 0, 0.04));
            transition: all var(--transition-fast, 0.15s ease);
            min-height: 72px;
        }

        .clickable-kpi-card:hover {
            transform: translateY(-2px);
            border-color: var(--color-border-hover, #cbd5e1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            background-color: var(--color-bg-subtle, #f8fafc);
        }

        .stat-card.active-kpi-card {
            border-color: var(--color-blue, #2563b8) !important;
            background-color: #f0f7ff !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 184, 0.15) !important;
        }

        .stat-card-icon-box {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md, 8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .stat-card-body {
            min-width: 0;
            flex: 1;
        }

        .stat-card-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--color-text-muted, #64748b);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-card-count {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--color-text, #0f172a);
            line-height: 1.15;
            margin: 2px 0 1px 0;
            letter-spacing: -0.02em;
        }

        .stat-card-subtext {
            font-size: 11px;
            color: var(--color-text-muted, #64748b);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Dark Mode Overrides for Pipeline & Cards ── */
        html[data-theme="dark"] .process-pipeline-container,
        html[data-bs-theme="dark"] .process-pipeline-container {
            background: rgba(19, 29, 49, 0.85) !important;
            border-color: #1e293b !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.35) !important;
        }
        html[data-theme="dark"] .pipeline-pill-link,
        html[data-bs-theme="dark"] .pipeline-pill-link {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] .pipeline-arrow-connector,
        html[data-bs-theme="dark"] .pipeline-arrow-connector {
            color: #64748b !important;
        }
        html[data-theme="dark"] .pipeline-arrow-connector::before,
        html[data-bs-theme="dark"] .pipeline-arrow-connector::before {
            opacity: 0.75;
            background: #475569;
        }

        html[data-theme="dark"] .pipeline-step-item:has(.tab-pending.active) + .pipeline-arrow-connector,
        html[data-bs-theme="dark"] .pipeline-step-item:has(.tab-pending.active) + .pipeline-arrow-connector {
            color: #fbbf24 !important;
        }
        html[data-theme="dark"] .pipeline-step-item:has(.tab-pending.active) + .pipeline-arrow-connector::before,
        html[data-bs-theme="dark"] .pipeline-step-item:has(.tab-pending.active) + .pipeline-arrow-connector::before {
            background: linear-gradient(90deg, #fbbf24, #38bdf8) !important;
            opacity: 1;
            box-shadow: 0 0 8px rgba(251, 191, 36, 0.45);
        }

        html[data-theme="dark"] .pipeline-step-item:has(.tab-processing.active) + .pipeline-arrow-connector,
        html[data-bs-theme="dark"] .pipeline-step-item:has(.tab-processing.active) + .pipeline-arrow-connector {
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] .pipeline-step-item:has(.tab-processing.active) + .pipeline-arrow-connector::before,
        html[data-bs-theme="dark"] .pipeline-step-item:has(.tab-processing.active) + .pipeline-arrow-connector::before {
            background: linear-gradient(90deg, #38bdf8, #2dd4bf) !important;
            opacity: 1;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.45);
        }

        html[data-theme="dark"] .pipeline-step-item:has(.tab-ready.active) + .pipeline-arrow-connector,
        html[data-bs-theme="dark"] .pipeline-step-item:has(.tab-ready.active) + .pipeline-arrow-connector {
            color: #2dd4bf !important;
        }
        html[data-theme="dark"] .pipeline-step-item:has(.tab-ready.active) + .pipeline-arrow-connector::before,
        html[data-bs-theme="dark"] .pipeline-step-item:has(.tab-ready.active) + .pipeline-arrow-connector::before {
            background: linear-gradient(90deg, #2dd4bf, #4ade80) !important;
            opacity: 1;
            box-shadow: 0 0 8px rgba(45, 212, 191, 0.45);
        }

        html[data-theme="dark"] .pipeline-arrow-connector:hover,
        html[data-bs-theme="dark"] .pipeline-arrow-connector:hover,
        html[data-theme="dark"] .pipeline-step-item:hover + .pipeline-arrow-connector,
        html[data-bs-theme="dark"] .pipeline-step-item:hover + .pipeline-arrow-connector {
            color: #38bdf8 !important;
            transform: translateX(2px);
        }
        html[data-theme="dark"] .pipeline-arrow-connector:hover::before,
        html[data-bs-theme="dark"] .pipeline-arrow-connector:hover::before,
        html[data-theme="dark"] .pipeline-step-item:hover + .pipeline-arrow-connector::before,
        html[data-bs-theme="dark"] .pipeline-step-item:hover + .pipeline-arrow-connector::before {
            width: 22px;
            background: #38bdf8 !important;
            opacity: 1;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.55);
        }
        html[data-theme="dark"] .exception-divider,
        html[data-bs-theme="dark"] .exception-divider {
            background-color: #334155 !important;
        }
        html[data-theme="dark"] .tab-pending .step-badge-pending,
        html[data-bs-theme="dark"] .tab-pending .step-badge-pending {
            background-color: rgba(217, 119, 6, 0.25) !important;
            color: #fde047 !important;
            border-color: rgba(253, 224, 71, 0.35) !important;
        }
        html[data-theme="dark"] .tab-pending .count-badge-pending,
        html[data-bs-theme="dark"] .tab-pending .count-badge-pending {
            background-color: rgba(217, 119, 6, 0.2) !important;
            color: #fde047 !important;
            border-color: rgba(253, 224, 71, 0.35) !important;
        }
        html[data-theme="dark"] .tab-processing .step-badge-processing,
        html[data-bs-theme="dark"] .tab-processing .step-badge-processing {
            background-color: rgba(37, 99, 235, 0.25) !important;
            color: #93c5fd !important;
            border-color: rgba(96, 165, 250, 0.4) !important;
        }
        html[data-theme="dark"] .tab-processing .count-badge-processing,
        html[data-bs-theme="dark"] .tab-processing .count-badge-processing {
            background-color: rgba(37, 99, 235, 0.2) !important;
            color: #93c5fd !important;
            border-color: rgba(96, 165, 250, 0.4) !important;
        }
        html[data-theme="dark"] .tab-ready .step-badge-ready,
        html[data-bs-theme="dark"] .tab-ready .step-badge-ready {
            background-color: rgba(6, 182, 212, 0.25) !important;
            color: #67e8f9 !important;
            border-color: rgba(103, 232, 249, 0.4) !important;
        }
        html[data-theme="dark"] .tab-ready .count-badge-ready,
        html[data-bs-theme="dark"] .tab-ready .count-badge-ready {
            background-color: rgba(6, 182, 212, 0.2) !important;
            color: #67e8f9 !important;
            border-color: rgba(103, 232, 249, 0.4) !important;
        }
        html[data-theme="dark"] .tab-completed .step-badge-completed,
        html[data-bs-theme="dark"] .tab-completed .step-badge-completed {
            background-color: rgba(22, 163, 74, 0.25) !important;
            color: #86efac !important;
            border-color: rgba(134, 239, 172, 0.4) !important;
        }
        html[data-theme="dark"] .tab-completed .count-badge-completed,
        html[data-bs-theme="dark"] .tab-completed .count-badge-completed {
            background-color: rgba(22, 163, 74, 0.2) !important;
            color: #86efac !important;
            border-color: rgba(134, 239, 172, 0.4) !important;
        }
        html[data-theme="dark"] .pipeline-pill-link.tab-pending.active,
        html[data-bs-theme="dark"] .pipeline-pill-link.tab-pending.active {
            background: linear-gradient(135deg, #b45309 0%, #d97706 100%) !important;
            border-color: #f59e0b !important;
            box-shadow: 0 2px 14px rgba(245, 158, 11, 0.45) !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .pipeline-pill-link.tab-processing.active,
        html[data-bs-theme="dark"] .pipeline-pill-link.tab-processing.active {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 2px 14px rgba(37, 99, 235, 0.45) !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .pipeline-pill-link.tab-ready.active,
        html[data-bs-theme="dark"] .pipeline-pill-link.tab-ready.active {
            background: linear-gradient(135deg, #0e7490 0%, #06b6d4 100%) !important;
            border-color: #22d3ee !important;
            box-shadow: 0 2px 14px rgba(6, 182, 212, 0.45) !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .pipeline-pill-link.tab-completed.active,
        html[data-bs-theme="dark"] .pipeline-pill-link.tab-completed.active {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 100%) !important;
            border-color: #4ade80 !important;
            box-shadow: 0 2px 14px rgba(34, 197, 94, 0.45) !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .pipeline-pill-link.tab-rejected.active,
        html[data-bs-theme="dark"] .pipeline-pill-link.tab-rejected.active {
            background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%) !important;
            border-color: #f87171 !important;
            box-shadow: 0 2px 14px rgba(220, 38, 38, 0.45) !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .pipeline-pill-link.tab-rejected,
        html[data-bs-theme="dark"] .pipeline-pill-link.tab-rejected {
            background-color: rgba(220, 38, 38, 0.15) !important;
            border-color: rgba(239, 68, 68, 0.35) !important;
            color: #fca5a5 !important;
        }
        html[data-theme="dark"] .pipeline-pill-link.tab-rejected .status-count-badge,
        html[data-bs-theme="dark"] .pipeline-pill-link.tab-rejected .status-count-badge {
            background-color: rgba(239, 68, 68, 0.2) !important;
            color: #fca5a5 !important;
            border-color: rgba(239, 68, 68, 0.4) !important;
        }

        html[data-theme="dark"] .stat-card,
        html[data-bs-theme="dark"] .stat-card {
            background-color: var(--color-surface, #131d31) !important;
            border-color: var(--color-border, #1e293b) !important;
        }
        html[data-theme="dark"] .clickable-kpi-card:hover,
        html[data-bs-theme="dark"] .clickable-kpi-card:hover {
            border-color: #3b82f6 !important;
            background-color: #182238 !important;
        }
        html[data-theme="dark"] .stat-card.active-kpi-card,
        html[data-bs-theme="dark"] .stat-card.active-kpi-card {
            border-color: #3b82f6 !important;
            background-color: #1e2b45 !important;
        }
        html[data-theme="dark"] .stat-card-label,
        html[data-bs-theme="dark"] .stat-card-label {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .stat-card-count,
        html[data-bs-theme="dark"] .stat-card-count {
            color: #ffffff !important;
        }
        html[data-theme="dark"] .stat-card-subtext,
        html[data-bs-theme="dark"] .stat-card-subtext {
            color: #94a3b8 !important;
        }

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
            .request-row-clickable {
            cursor: pointer;
            transition: background-color var(--transition-fast);
        }
        .request-row-clickable:hover > td {
            background-color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .request-row-clickable:hover > td,
        html[data-bs-theme="dark"] .request-row-clickable:hover > td {
            background-color: #19253d !important;
        }
            /* ═══════════════════════════════════════════════════════════
           STATUS BADGES & HERO TRACKING NUMBER STYLING
           ═══════════════════════════════════════════════════════════ */
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3.5px 10px;
            border-radius: var(--radius-pill, 9999px);
            min-width: 108px;
            line-height: 1.2;
            white-space: nowrap;
        }
        .status-dot {
            width: 6.5px;
            height: 6.5px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }
        .status-badge.status-processing {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            border: 1px solid #bfdbfe !important;
        }
        .status-badge.status-processing .status-dot {
            background-color: #2563eb !important;
        }
        .status-badge.status-ready {
            background-color: #ecfeff !important;
            color: #0e7490 !important;
            border: 1px solid #a5f3fc !important;
        }
        .status-badge.status-ready .status-dot {
            background-color: #06b6d4 !important;
        }
        .status-badge.status-completed {
            background-color: #f0fdf4 !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
        }
        .status-badge.status-completed .status-dot {
            background-color: #16a34a !important;
        }
        .status-badge.status-pending,
        .status-badge.status-pending-review {
            background-color: #fffbeb !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }
        .status-badge.status-pending .status-dot,
        .status-badge.status-pending-review .status-dot {
            background-color: #d97706 !important;
        }
        .status-badge.status-under-review {
            background-color: #faf5ff !important;
            color: #7e22ce !important;
            border: 1px solid #e9d5ff !important;
        }
        .status-badge.status-under-review .status-dot {
            background-color: #9333ea !important;
        }
        .status-badge.status-rejected {
            background-color: #fef2f2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca !important;
        }
        .status-badge.status-rejected .status-dot {
            background-color: #dc2626 !important;
        }

        /* Hero Tracking Number (Big Highlight) */
        .hero-tracking-number {
            font-size: clamp(2rem, 3.8vw, 2.65rem) !important;
            font-weight: 800 !important;
            letter-spacing: 0.05em !important;
            line-height: 1.15 !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            color: var(--color-navy, #14284b) !important;
        }

        /* ============================================================
           MODAL PROGRESS STEPPER (Pending -> Processing -> Ready for Pick-up)
           ============================================================ */
        .modal-stepper-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
            animation: modalStepperSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            --stepper-pad: 38px;
        }

        @keyframes modalStepperSlideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-stepper-wrapper {
            position: relative;
            padding: 0 var(--stepper-pad);
        }

        .modal-stepper-track-bg {
            position: absolute;
            top: 17px;
            left: calc(var(--stepper-pad) + 18px);
            right: calc(var(--stepper-pad) + 18px);
            height: 3.5px;
            background: #e2e8f0;
            border-radius: 4px;
            z-index: 1;
        }

        .modal-stepper-track-fill {
            position: absolute;
            top: 17px;
            left: calc(var(--stepper-pad) + 18px);
            height: 3.5px;
            background: linear-gradient(90deg, #10b981 0%, #18365D 50%, #2563eb 100%);
            border-radius: 4px;
            z-index: 2;
            transition: width 0.65s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.4s ease;
            max-width: calc(100% - (var(--stepper-pad) * 2 + 36px));
        }

        .modal-stepper-nodes {
            display: flex;
            justify-content: space-between;
            position: relative;
            z-index: 3;
        }

        .modal-step-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 120px;
            margin: 0 -42px;
        }

        .modal-step-node {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13.5px;
            font-weight: 800;
            background: #f1f5f9;
            color: #94a3b8;
            border: 3.5px solid #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .modal-step-node .step-icon-check,
        .modal-step-node .step-icon-reject {
            display: none;
        }

        .modal-step-text {
            margin-top: 6px;
            font-size: 11.5px;
            font-weight: 600;
            color: #94a3b8;
            transition: color 0.3s ease, font-weight 0.3s ease;
            white-space: nowrap;
        }

        /* --- ACTIVE STATE --- */
        .modal-step-col.active .modal-step-node {
            background: #18365D;
            color: #ffffff;
            border-color: #ffffff;
            transform: scale(1.08);
            animation: modalStepPulse 2.2s infinite ease-in-out;
        }

        .modal-step-col.active .modal-step-text {
            color: #18365D;
            font-weight: 800;
        }

        @keyframes modalStepPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(24, 54, 93, 0.45), 0 3px 8px rgba(24, 54, 93, 0.25);
            }
            60% {
                box-shadow: 0 0 0 8px rgba(24, 54, 93, 0), 0 4px 12px rgba(24, 54, 93, 0.15);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(24, 54, 93, 0.45), 0 3px 8px rgba(24, 54, 93, 0.25);
            }
        }

        /* --- COMPLETED STATE --- */
        .modal-step-col.completed .modal-step-node {
            background: #10b981;
            color: #ffffff;
            border-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.22), 0 2px 6px rgba(16, 185, 129, 0.3);
            animation: stepSuccessPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .modal-step-col.completed .modal-step-node .step-num {
            display: none;
        }

        .modal-step-col.completed .modal-step-node .step-icon-check {
            display: inline-flex;
            font-size: 13px;
        }

        .modal-step-col.completed .modal-step-text {
            color: #059669;
            font-weight: 700;
        }

        @keyframes stepSuccessPop {
            0% { transform: scale(0.85); }
            60% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        /* --- REJECTED STATE --- */
        .modal-step-col.rejected .modal-step-node {
            background: #ef4444;
            color: #ffffff;
            border-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.25);
        }
        .modal-step-col.rejected .modal-step-node .step-num { display: none; }
        .modal-step-col.rejected .modal-step-node .step-icon-reject { display: inline-flex; }
        .modal-step-col.rejected .modal-step-text { color: #dc2626; font-weight: 700; }

        @media (max-width: 576px) {
            .modal-stepper-card {
                --stepper-pad: 18px;
                padding: 12px 14px 10px;
            }
            .modal-step-text {
                font-size: 10.5px;
            }
        }

        /* Dark Mode Overrides for Modal Stepper */
        html[data-theme="dark"] .modal-stepper-card,
        html[data-bs-theme="dark"] .modal-stepper-card {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        html[data-theme="dark"] .modal-stepper-track-bg,
        html[data-bs-theme="dark"] .modal-stepper-track-bg {
            background: #334155;
        }

        html[data-theme="dark"] .modal-step-node,
        html[data-bs-theme="dark"] .modal-step-node {
            background: #334155;
            color: #94a3b8;
            border-color: #1e293b;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        html[data-theme="dark"] .modal-step-col.active .modal-step-node,
        html[data-bs-theme="dark"] .modal-step-col.active .modal-step-node {
            background: #2563eb;
            color: #ffffff;
            border-color: #1e293b;
        }

        html[data-theme="dark"] .modal-step-col.active .modal-step-text,
        html[data-bs-theme="dark"] .modal-step-col.active .modal-step-text {
            color: #60a5fa;
        }

        html[data-theme="dark"] .modal-step-col.completed .modal-step-node,
        html[data-bs-theme="dark"] .modal-step-col.completed .modal-step-node {
            background: #10b981;
            border-color: #1e293b;
        }

        html[data-theme="dark"] .modal-step-col.completed .modal-step-text,
        html[data-bs-theme="dark"] .modal-step-col.completed .modal-step-text {
            color: #34d399;
        }

        html[data-theme="dark"] .modal-stepper-track-fill,
        html[data-bs-theme="dark"] .modal-stepper-track-fill {
            background: linear-gradient(90deg, #10b981 0%, #3b82f6 50%, #60a5fa 100%);
        }

        .tracking-hero-card {
            background: linear-gradient(135deg, rgba(37, 99, 184, 0.06) 0%, rgba(15, 196, 232, 0.08) 100%) !important;
            border: 1.5px solid rgba(37, 99, 184, 0.22) !important;
            border-radius: var(--radius-card, 12px) !important;
        }

        .tracking-kicker {
            background-color: rgba(37, 99, 184, 0.12) !important;
            color: #1e40af !important;
            border: 1px solid rgba(37, 99, 184, 0.28) !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
            border-radius: 9999px !important;
            display: inline-flex !important;
            align-items: center !important;
        }
        .tracking-kicker i {
            color: #2563eb !important;
        }

        /* Dark Mode Overrides for Status Badges & Tracking Hero */
        html[data-theme="dark"] .hero-tracking-number,
        html[data-bs-theme="dark"] .hero-tracking-number {
            color: #60a5fa !important;
            text-shadow: 0 0 20px rgba(96, 165, 250, 0.25);
        }

        html[data-theme="dark"] .tracking-hero-card,
        html[data-bs-theme="dark"] .tracking-hero-card {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.35) 0%, rgba(15, 23, 42, 0.8) 100%) !important;
            border-color: rgba(96, 165, 250, 0.4) !important;
        }

        html[data-theme="dark"] .tracking-kicker,
        html[data-bs-theme="dark"] .tracking-kicker {
            background-color: rgba(59, 130, 246, 0.22) !important;
            color: #93c5fd !important;
            border-color: rgba(96, 165, 250, 0.45) !important;
        }
        html[data-theme="dark"] .tracking-kicker i,
        html[data-bs-theme="dark"] .tracking-kicker i {
            color: #60a5fa !important;
        }

        html[data-theme="dark"] .status-badge.status-processing,
        html[data-bs-theme="dark"] .status-badge.status-processing {
            background-color: rgba(37, 99, 235, 0.25) !important;
            color: #93c5fd !important;
            border-color: rgba(96, 165, 250, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-processing .status-dot,
        html[data-bs-theme="dark"] .status-badge.status-processing .status-dot {
            background-color: #60a5fa !important;
        }

        html[data-theme="dark"] .status-badge.status-ready,
        html[data-bs-theme="dark"] .status-badge.status-ready {
            background-color: rgba(6, 182, 212, 0.25) !important;
            color: #67e8f9 !important;
            border-color: rgba(103, 232, 249, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-ready .status-dot,
        html[data-bs-theme="dark"] .status-badge.status-ready .status-dot {
            background-color: #22d3ee !important;
        }

        html[data-theme="dark"] .status-badge.status-completed,
        html[data-bs-theme="dark"] .status-badge.status-completed {
            background-color: rgba(22, 163, 74, 0.25) !important;
            color: #86efac !important;
            border-color: rgba(134, 239, 172, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-completed .status-dot,
        html[data-bs-theme="dark"] .status-badge.status-completed .status-dot {
            background-color: #4ade80 !important;
        }

        html[data-theme="dark"] .status-badge.status-pending,
        html[data-theme="dark"] .status-badge.status-pending-review,
        html[data-bs-theme="dark"] .status-badge.status-pending,
        html[data-bs-theme="dark"] .status-badge.status-pending-review {
            background-color: rgba(217, 119, 6, 0.25) !important;
            color: #fde047 !important;
            border-color: rgba(253, 224, 71, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-pending .status-dot,
        html[data-bs-theme="dark"] .status-badge.status-pending .status-dot {
            background-color: #facc15 !important;
        }

        html[data-theme="dark"] .status-badge.status-under-review,
        html[data-bs-theme="dark"] .status-badge.status-under-review {
            background-color: rgba(147, 51, 234, 0.25) !important;
            color: #d8b4fe !important;
            border-color: rgba(216, 180, 254, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-under-review .status-dot,
        html[data-bs-theme="dark"] .status-badge.status-under-review .status-dot {
            background-color: #c084fc !important;
        }

        html[data-theme="dark"] .status-badge.status-rejected,
        html[data-bs-theme="dark"] .status-badge.status-rejected {
            background-color: rgba(220, 38, 38, 0.25) !important;
            color: #fca5a5 !important;
            border-color: rgba(252, 165, 165, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-rejected .status-dot,
        html[data-bs-theme="dark"] .status-badge.status-rejected .status-dot {
            background-color: #f87171 !important;
        }
        html[data-theme="dark"] .btn-light,
        html[data-bs-theme="dark"] .btn-light {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] .btn-light:hover,
        html[data-bs-theme="dark"] .btn-light:hover {
            background-color: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .btn-light .text-muted,
        html[data-bs-theme="dark"] .btn-light .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] #searchForm .input-group,
        html[data-bs-theme="dark"] #searchForm .input-group {
            background-color: #182238 !important;
            border-color: #334155 !important;
        }
        html[data-theme="dark"] #searchForm input,
        html[data-bs-theme="dark"] #searchForm input {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] #filterDropdown + .dropdown-menu,
        html[data-bs-theme="dark"] #filterDropdown + .dropdown-menu {
            background-color: #131d31 !important;
            border-color: #334155 !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5) !important;
        }
        html[data-theme="dark"] #filterDropdown + .dropdown-menu .dropdown-item,
        html[data-bs-theme="dark"] #filterDropdown + .dropdown-menu .dropdown-item {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] #filterDropdown + .dropdown-menu .dropdown-item:hover,
        html[data-bs-theme="dark"] #filterDropdown + .dropdown-menu .dropdown-item:hover {
            background-color: #1e293b !important;
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] #filterDropdown + .dropdown-menu .dropdown-item.active,
        html[data-bs-theme="dark"] #filterDropdown + .dropdown-menu .dropdown-item.active {
            background-color: rgba(37, 99, 235, 0.25) !important;
            color: #93c5fd !important;
        }
        html[data-theme="dark"] .tag-free,
        html[data-bs-theme="dark"] .tag-free {
            background-color: rgba(34, 197, 94, 0.2) !important;
            color: #86efac !important;
            border-color: rgba(74, 222, 128, 0.35) !important;
        }

        /* ═══════════════════════════════════════════════════════════
           ANIMATIONS — REQUESTS PAGE
           ═══════════════════════════════════════════════════════════ */

        /* Modal stacking safety */
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }

        /* Pipeline bar fade-in */
        .header-pipeline-bar {
            animation: agy-fadeIn 0.35s ease-out;
        }

        /* Stat cards staggered entrance */
        .stat-cards-grid .stat-card:nth-child(1) { animation: agy-fadeSlideUp 0.35s cubic-bezier(0.22,1,0.36,1) 0.05s; }
        .stat-cards-grid .stat-card:nth-child(2) { animation: agy-fadeSlideUp 0.35s cubic-bezier(0.22,1,0.36,1) 0.10s; }
        .stat-cards-grid .stat-card:nth-child(3) { animation: agy-fadeSlideUp 0.35s cubic-bezier(0.22,1,0.36,1) 0.15s; }
        .stat-cards-grid .stat-card:nth-child(4) { animation: agy-fadeSlideUp 0.35s cubic-bezier(0.22,1,0.36,1) 0.20s; }
        .stat-cards-grid .stat-card:nth-child(5) { animation: agy-fadeSlideUp 0.35s cubic-bezier(0.22,1,0.36,1) 0.25s; }

        /* Table card fade in */
        .card.border-0.shadow-sm {
            animation: agy-fadeIn 0.35s ease-out;
        }

        /* Table rows cascade fade-in */
        #requests-tbody tr {
            animation: agy-fadeIn 0.28s ease both;
        }
        #requests-tbody tr:nth-child(1)  { animation-delay: 0.22s; }
        #requests-tbody tr:nth-child(2)  { animation-delay: 0.26s; }
        #requests-tbody tr:nth-child(3)  { animation-delay: 0.30s; }
        #requests-tbody tr:nth-child(4)  { animation-delay: 0.34s; }
        #requests-tbody tr:nth-child(5)  { animation-delay: 0.38s; }
        #requests-tbody tr:nth-child(6)  { animation-delay: 0.42s; }
        #requests-tbody tr:nth-child(7)  { animation-delay: 0.46s; }
        #requests-tbody tr:nth-child(8)  { animation-delay: 0.50s; }
        #requests-tbody tr:nth-child(n+9) { animation-delay: 0.54s; }

        /* Mobile cards stagger */
        #requests-mobile-list .mobile-request-card {
            animation: agy-fadeSlideUp 0.32s cubic-bezier(0.22,1,0.36,1) both;
        }
        #requests-mobile-list .mobile-request-card:nth-child(1) { animation-delay: 0.18s; }
        #requests-mobile-list .mobile-request-card:nth-child(2) { animation-delay: 0.24s; }
        #requests-mobile-list .mobile-request-card:nth-child(3) { animation-delay: 0.30s; }
        #requests-mobile-list .mobile-request-card:nth-child(n+4) { animation-delay: 0.36s; }

        /* Live badge in pipeline header (Hidden per user request) */
        .live-badge,
        #live-status-indicator,
        #last-refreshed-label {
            display: none !important;
        }

        /* Pipeline pill active glow pulse */
        .pipeline-pill-link.active {
            animation: agy-activePillGlow 3s ease-in-out infinite !important;
        }
        @keyframes agy-activePillGlow {
            0%, 100% { box-shadow: 0 3px 10px rgba(20, 40, 75, 0.28) !important; }
            50%       { box-shadow: 0 4px 18px rgba(20, 40, 75, 0.45) !important; }
        }

        /* KPI count-up number flash */
        .stat-count-flash {
            animation: agy-countFlash 0.5s ease;
        }
        @keyframes agy-countFlash {
            0%   { transform: scale(1.18); color: #2563eb; }
            100% { transform: scale(1); }
        }

        /* Table row new-data highlight flash */
        .row-new-flash {
            animation: agy-rowFlash 1.2s ease;
        }
        @keyframes agy-rowFlash {
            0%   { background-color: rgba(37, 99, 235, 0.12); }
            100% { background-color: transparent; }
        }

        /* Overdue card red pulse */
        .overdue-alert-card {
            animation: agy-overdueGlow 2.4s ease-in-out infinite !important;
        }
        @keyframes agy-overdueGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.3); }
            50%       { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0.0); }
        }

        /* ═══════════════════════════════════════════════════════════
           MODAL WORKFLOW ACTION BUTTONS (CENTERED, PROMINENT & HIGH-CONTRAST)
           ═══════════════════════════════════════════════════════════ */
        .btn-modal-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-size: 14.5px;
            font-weight: 700;
            line-height: 1.25;
            padding: 11px 28px;
            min-height: 44px;
            border-radius: 10px;
            letter-spacing: -0.01em;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
            user-select: none;
        }

        .btn-modal-action:hover {
            transform: translateY(-2px);
        }

        .btn-modal-action:active {
            transform: translateY(0);
        }

        /* 1. Reject Button */
        .btn-modal-reject {
            background-color: #ffffff;
            color: #dc2626;
            border: 1.5px solid #fca5a5;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
        }
        .btn-modal-reject:hover {
            background-color: #fef2f2;
            border-color: #ef4444;
            color: #b91c1c;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.22);
        }

        /* 2. Accept Request Button */
        .btn-modal-accept {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #ffffff !important;
            border: 1.5px solid #15803d;
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35);
        }
        .btn-modal-accept:hover {
            background: linear-gradient(135deg, #15803d 0%, #166534 100%);
            border-color: #166534;
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.48);
        }

        /* 3. Ready for Pickup Button */
        .btn-modal-ready {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff !important;
            border: 1.5px solid #0284c7;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        }
        .btn-modal-ready:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            border-color: #0369a1;
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.48);
        }

        /* 4. Complete Request Button */
        .btn-modal-complete {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff !important;
            border: 1.5px solid #047857;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
        }
        .btn-modal-complete:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            border-color: #047857;
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.48);
        }

        /* 5. Save Changes (Edit Mode) Button */
        .btn-modal-save {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff !important;
            border: 1.5px solid #1d4ed8;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        .btn-modal-save:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            border-color: #1e40af;
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.48);
        }

        /* Dark mode overrides for modal action buttons */
        html[data-theme="dark"] .btn-modal-reject,
        html[data-bs-theme="dark"] .btn-modal-reject {
            background-color: rgba(220, 38, 38, 0.15);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }
        html[data-theme="dark"] .btn-modal-reject:hover,
        html[data-bs-theme="dark"] .btn-modal-reject:hover {
            background-color: rgba(220, 38, 38, 0.3);
            border-color: #f87171;
            color: #ffffff;
        }

        /* Dark mode for Reject Confirmation Modal */
        html[data-theme="dark"] #rejectRequestConfirmModal .modal-content,
        html[data-bs-theme="dark"] #rejectRequestConfirmModal .modal-content {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border: 1px solid #1e293b !important;
        }
        html[data-theme="dark"] #rejectRequestConfirmModal .text-dark,
        html[data-bs-theme="dark"] #rejectRequestConfirmModal .text-dark {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #rejectRequestConfirmModal .text-muted,
        html[data-bs-theme="dark"] #rejectRequestConfirmModal .text-muted {
            color: #94a3b8 !important;
        }
    </style>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header border-0 bg-white py-3 px-3 px-md-4 rounded-top-4">
            {{-- Row 1: Title on left, Action tools on right --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2.5">
                <div class="d-flex align-items-center gap-2">
                    @php
                        $displayStatus = match($currentStatus) {
                            'Pending_Review' => 'Pending & Review',
                            'Processing' => 'Processing',
                            'Ready' => 'Ready for Pickup',
                            default => str_replace('_', ' ', $currentStatus)
                        };
                    @endphp
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                        <i class="fa-solid fa-folder-open text-primary me-2"></i>{{ $displayStatus }} Requests
                    </h5>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11.5px;">
                        {{ $allRequests->total() }} records
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.document-types.index') }}" class="btn btn-sm btn-light border px-3 fw-semibold text-decoration-none rounded-3" title="Manage Barangay Documents">
                        <i class="fa-solid fa-folder-open text-muted me-1"></i> Document Management
                    </a>
                    <button class="btn btn-sm btn-light border px-3 fw-semibold rounded-3"><i class="fa-solid fa-download text-muted me-1"></i> Export</button>
                    <button type="button" class="btn btn-sm btn-light border px-2.5 fw-semibold rounded-3 text-muted" id="btnToggleDensity" title="Adjust table density for convenient viewing when zoomed">
                        <i class="fa-solid fa-compress me-1" id="densityIcon"></i>
                        <span id="densityText" class="d-none d-sm-inline">Compact</span>
                    </button>
                </div>
            </div>

            {{-- Row 2: Searchbar with Filters Dropdown --}}
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2.5" id="requestsSearchBarRow">
                <form action="{{ route('admin.requests') }}" method="GET" class="m-0 flex-grow-1" id="searchForm">
                    @if(request('document_type'))
                        <input type="hidden" name="document_type" value="{{ request('document_type') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    <div class="input-group input-group-sm rounded-3 border" style="background-color: #f8fafc;">
                        <button type="submit" class="input-group-text border-0 bg-transparent text-muted"><i class="fa fa-search"></i></button>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent shadow-none py-1.5" placeholder="Search by ID, name, or type..." autocomplete="off">
                    </div>
                </form>

                <div class="dropdown">
                    <button class="btn btn-sm btn-light border px-3 fw-semibold dropdown-toggle rounded-3" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-filter text-muted me-1"></i> 
                        {{ $currentDocType == 'All' ? 'Filters' : $currentDocType }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border" aria-labelledby="filterDropdown" style="font-size: 13.5px; min-width: 280px; max-width: min(92vw, 360px); max-height: 380px; overflow-y: auto; z-index: 1060; border-color: #cbd5e1;">
                        <li><a class="dropdown-item {{ $currentDocType == 'All' ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => null]) }}">All Documents</a></li>
                        <li><hr class="dropdown-divider"></li>
                        @if(isset($validTypes) && count($validTypes) > 0)
                            @foreach($validTypes as $vType)
                                <li><a class="dropdown-item {{ $currentDocType == $vType ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => $vType]) }}">{{ $vType }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Row 3: Process Pipeline Stepper (PLACED UNDER THE SEARCHBAR, SIZE ADJUSTED) --}}
            <div class="process-pipeline-wrapper w-100">
                <div class="process-pipeline-container" role="tablist" aria-label="Request Status Stages">
                    
                    {{-- Sequential Workflow Steps (1 -> 2 -> 3 -> 4) --}}
                    <div class="process-pipeline-stages">
                        
                        {{-- Step 1: Pending & Review --}}
                        <div class="pipeline-step-item">
                            <a class="pipeline-pill-link tab-pending {{ $currentStatus == 'Pending_Review' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['status' => 'Pending_Review']) }}"
                               role="tab"
                               aria-selected="{{ $currentStatus == 'Pending_Review' ? 'true' : 'false' }}"
                               title="Step 1: Review incoming applications">
                                <span class="step-badge-number step-badge-pending">1</span>
                                <i class="fa-solid fa-inbox text-step-icon"></i>
                                <span class="step-title">Pending &amp; Review</span>
                                <span class="status-count-badge count-badge-pending" id="tab-badge-pending">{{ $tabCounts['Pending_Review'] }}</span>
                            </a>
                        </div>

                        {{-- Leading Arrow 1 -> 2 --}}
                        <div class="pipeline-arrow-connector" aria-hidden="true" title="Leads to Processing">
                            <svg viewBox="0 0 24 24" width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>

                        {{-- Step 2: Processing --}}
                        <div class="pipeline-step-item">
                            <a class="pipeline-pill-link tab-processing {{ $currentStatus == 'Processing' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['status' => 'Processing']) }}"
                               role="tab"
                               aria-selected="{{ $currentStatus == 'Processing' ? 'true' : 'false' }}"
                               title="Step 2: Processing, drafting and captain signature">
                                <span class="step-badge-number step-badge-processing">2</span>
                                <i class="fa-solid fa-gear text-step-icon gear-icon-spin"></i>
                                <span class="step-title">Processing</span>
                                <span class="status-count-badge count-badge-processing" id="tab-badge-processing">{{ $tabCounts['Processing'] }}</span>
                            </a>
                        </div>

                        {{-- Leading Arrow 2 -> 3 --}}
                        <div class="pipeline-arrow-connector" aria-hidden="true" title="Leads to Ready for Pick-up">
                            <svg viewBox="0 0 24 24" width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>

                        {{-- Step 3: Ready for Pick-up --}}
                        <div class="pipeline-step-item">
                            <a class="pipeline-pill-link tab-ready {{ $currentStatus == 'Ready' ? 'active' : '' }} {{ ($tabCounts['Ready'] ?? 0) > 0 ? 'stage-waiting-action' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['status' => 'Ready']) }}"
                               role="tab"
                               aria-selected="{{ $currentStatus == 'Ready' ? 'true' : 'false' }}"
                               title="Step 3: Document printed and ready at releasing counter">
                                <span class="step-badge-number step-badge-ready">3</span>
                                <i class="fa-solid fa-file-signature text-step-icon"></i>
                                <span class="step-title">Ready for Pick-up</span>
                                <span class="status-count-badge count-badge-ready" id="tab-badge-ready">{{ $tabCounts['Ready'] }}</span>
                                @if(($tabCounts['Ready'] ?? 0) > 0)
                                    <span class="waiting-dot ms-1" title="{{ $tabCounts['Ready'] }} resident(s) waiting at counter"></span>
                                @endif
                            </a>
                        </div>

                        {{-- Leading Arrow 3 -> 4 --}}
                        <div class="pipeline-arrow-connector" aria-hidden="true" title="Leads to Completed">
                            <svg viewBox="0 0 24 24" width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>

                        {{-- Step 4: Completed --}}
                        <div class="pipeline-step-item">
                            <a class="pipeline-pill-link tab-completed {{ $currentStatus == 'Completed' ? 'active' : '' }}" 
                               href="{{ request()->fullUrlWithQuery(['status' => 'Completed']) }}"
                               role="tab"
                               aria-selected="{{ $currentStatus == 'Completed' ? 'true' : 'false' }}"
                               title="Step 4: Claimed by resident and archived">
                                <span class="step-badge-number step-badge-completed">4</span>
                                <i class="fa-solid fa-check-double text-step-icon"></i>
                                <span class="step-title">Completed</span>
                                <span class="status-count-badge count-badge-completed" id="tab-badge-completed">{{ $tabCounts['Completed'] }}</span>
                            </a>
                        </div>

                    </div>

                    {{-- Exception Branch: Rejected (Docked to the Right) --}}
                    <div class="pipeline-exception-wrapper">
                        <div class="exception-divider" aria-hidden="true"></div>
                        <a class="pipeline-pill-link tab-rejected {{ $currentStatus == 'Rejected' ? 'active' : '' }}" 
                           href="{{ request()->fullUrlWithQuery(['status' => 'Rejected']) }}"
                           role="tab"
                           aria-selected="{{ $currentStatus == 'Rejected' ? 'true' : 'false' }}"
                           title="Disapproved / Incomplete applications">
                            <i class="fa-solid fa-ban text-step-icon text-danger"></i>
                            <span class="step-title">Rejected</span>
                            <span class="status-count-badge badge-rejected" id="tab-badge-rejected">{{ $tabCounts['Rejected'] }}</span>
                        </a>
                    </div>

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
                        
                    </tr>
                </thead>
                <tbody class="border-top-0" id="requests-tbody">
                    @forelse($allRequests as $request)

                    @php
                        $isUrgent = ($currentStatus == 'Pending_Review' && $request->status == 'Pending' && $request->created_at->diffInHours(\Carbon\Carbon::now()) > 48);
                        $docTypeLower = strtolower($request->document_type ?? '');
                        $isBusinessClearance = str_contains($docTypeLower, 'business');
                    @endphp

                    <tr class="request-row-clickable {{ $isUrgent ? 'bg-danger bg-opacity-10' : '' }}" id="req-row-{{ $request->id }}" data-modal-target="#viewModal{{ $request->id }}" title="Click to view request details" role="button" tabindex="0">
                        <td class="ps-3 text-center col-select" style="width: 40px;" onclick="event.stopPropagation();">
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
                        
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-inbox fa-3x mb-3 text-light"></i>
                            <h5 class="fw-bold text-dark">No Requests Found</h5>
                            <div>There are currently no documents in the <b>{{ $displayStatus }}</b> tab.</div>
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
                <div class="mobile-request-card request-row-clickable mb-3 p-3 rounded-3 border bg-white shadow-xs {{ $isUrgent ? 'border-danger border-opacity-50' : '' }}" 
                     data-modal-target="#viewModal{{ $request->id }}"
                     role="button"
                     tabindex="0"
                     title="Tap to view request details"
                     style="{{ $isUrgent ? 'background-color: #fff5f5;' : '' }} cursor: pointer; transition: transform var(--transition-fast), box-shadow var(--transition-fast);">
                    {{-- Header: Tracking ID + Status Badge --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input req-checkbox mt-0" type="checkbox" value="{{ $request->id }}" data-tracking="{{ $request->tracking_number }}" onclick="event.stopPropagation();">
                            <span class="font-monospace fw-bold text-dark font-mono-id" style="font-size: 13.5px;">{{ $request->tracking_number }}</span>
                            @if($isUrgent)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-0.5 px-1.5" style="font-size: 10px;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Overdue
                                </span>
                            @endif
                        </div>
                        <div id="status-badge-mobile-{{ $request->id }}">
                            @if($request->status == 'Completed')
                                <span class="status-badge status-completed"><span class="status-dot"></span><span>Completed</span></span>
                            @elseif($request->status == 'Ready')
                                <span class="status-badge status-ready"><span class="status-dot"></span><span>Ready</span></span>
                            @elseif($request->status == 'Processing')
                                <span class="status-badge status-processing"><span class="status-dot"></span><span>Processing</span></span>
                            @elseif($request->status == 'Under Review')
                                <span class="status-badge status-under-review"><span class="status-dot"></span><span>Under Review</span></span>
                            @elseif($request->status == 'Rejected')
                                <span class="status-badge status-rejected"><span class="status-dot"></span><span>Rejected</span></span>
                            @else
                                <span class="status-badge status-pending"><span class="status-dot"></span><span>Pending</span></span>
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
                            <span class="text-dark font-tabular-date">{{ $request->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 pt-1 border-top border-light-subtle">
                            @if($idType)
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-0.5 fw-semibold" style="font-size: 10.5px;">
                                    <i class="fa-solid fa-id-card me-1"></i>{{ $idType }}
                                </span>
                            @endif
                            
                            @if(!$isBusinessClearance)
                                <span class="tag-free ms-auto">FREE</span>
                            @elseif($request->fee !== null && (float)$request->fee > 0)
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold ms-auto" style="font-size: 11px;">Fee: ₱{{ number_format($request->fee, 2) }}</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning ms-auto" style="font-size: 11px;">To be settled</span>
                            @endif
                        </div>
                    </div>

                    {{-- Footer: Tap Hint & Time --}}
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top text-muted" style="font-size: 11.5px;">
                        <span><i class="fa-regular fa-clock me-1"></i> {{ $request->created_at->format('M d, Y h:i A') }}</span>
                        <span class="text-primary fw-semibold d-inline-flex align-items-center gap-1">
                            <span>View Details</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-inbox fa-3x mb-3 text-light"></i>
                    <h5 class="fw-bold text-dark">No Requests Found</h5>
                    <div class="small">There are currently no documents in the <b>{{ $displayStatus }}</b> tab.</div>
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
                                    class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1.5 fw-semibold shadow-sm btn-edit-request"
                                    id="btn-edit-{{ $request->id }}"
                                    data-request-id="{{ $request->id }}"
                                    onclick="toggleEditMode({{ $request->id }}, true)">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                            </button>
                            <button type="submit" form="edit-form-{{ $request->id }}"
                                    class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 fw-semibold shadow-sm btn-save-request d-none"
                                    id="btn-header-save-{{ $request->id }}"
                                    data-request-id="{{ $request->id }}">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-1.5 fw-semibold btn-cancel-request d-none"
                                    id="btn-cancel-edit-{{ $request->id }}"
                                    data-request-id="{{ $request->id }}"
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
                    <div id="view-mode-{{ $request->id }}" class="p-3 p-md-4">

                        @php
                            $isUrgent = ($currentStatus == 'Pending_Review' && $request->status == 'Pending' && $request->created_at->diffInHours(\Carbon\Carbon::now()) > 48);
                            $docTypeLower = strtolower($request->document_type ?? '');
                            $isBusinessClearance = str_contains($docTypeLower, 'business');
                            $idType = $request->resolved_id_type ?? $request->id_type ?? $request->user?->id_type;
                            $modalDocsList = $request->documents_list;
                            $docsCount = count($modalDocsList);

                            $rawStatus = strtolower(trim($request->status ?? 'pending'));
                            $currentStep = 1;
                            if (in_array($rawStatus, ['processing', 'approved', 'in progress'])) {
                                $currentStep = 2;
                            } elseif (in_array($rawStatus, ['ready', 'ready for pickup', 'ready for pick-up'])) {
                                $currentStep = 3;
                            } elseif (in_array($rawStatus, ['completed', 'claimed'])) {
                                $currentStep = 4;
                            } elseif (in_array($rawStatus, ['rejected', 'cancelled'])) {
                                $currentStep = -1;
                            }
                            $initialFillWidth = $currentStep == 1 ? '0%' : ($currentStep == 2 ? '50%' : '100%');
                        @endphp

                        {{-- Progress Pipeline Stepper (Pending -> Processing -> Ready for Pick-up) --}}
                        <div class="modal-stepper-card mb-3" id="modal-stepper-card-{{ $request->id }}">
                            <div class="modal-stepper-wrapper">
                                <div class="modal-stepper-track-bg"></div>
                                <div class="modal-stepper-track-fill" 
                                     id="modal-stepper-fill-{{ $request->id }}" 
                                     data-target-width="{{ $initialFillWidth }}"
                                     style="width: {{ $initialFillWidth }}; {{ $currentStep >= 4 ? 'background: #10b981 !important;' : '' }}"></div>
                                <div class="modal-stepper-nodes">
                                    {{-- Step 1: Pending --}}
                                    <div class="modal-step-col {{ $currentStep > 1 ? 'completed' : ($currentStep == 1 ? 'active' : ($currentStep == -1 ? 'rejected' : '')) }}" 
                                         id="step-col-1-{{ $request->id }}">
                                        <div class="modal-step-node">
                                            <i class="fa-solid fa-check step-icon-check"></i>
                                            <i class="fa-solid fa-xmark step-icon-reject"></i>
                                            <span class="step-num">1</span>
                                        </div>
                                        <div class="modal-step-text">Pending</div>
                                    </div>

                                    {{-- Step 2: Processing --}}
                                    <div class="modal-step-col {{ $currentStep > 2 ? 'completed' : ($currentStep == 2 ? 'active' : '') }}" 
                                         id="step-col-2-{{ $request->id }}">
                                        <div class="modal-step-node">
                                            <i class="fa-solid fa-check step-icon-check"></i>
                                            <span class="step-num">2</span>
                                        </div>
                                        <div class="modal-step-text">Processing</div>
                                    </div>

                                    {{-- Step 3: Ready for Pick-up --}}
                                    <div class="modal-step-col {{ $currentStep >= 4 ? 'completed' : ($currentStep == 3 ? 'active' : '') }}" 
                                         id="step-col-3-{{ $request->id }}">
                                        <div class="modal-step-node">
                                            <i class="fa-solid fa-check step-icon-check"></i>
                                            <span class="step-num">3</span>
                                        </div>
                                        <div class="modal-step-text">Ready for Pick-up</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 1. Tracking Number at the Top (Big Highlight & Status) --}}
                        <div class="tracking-hero-card mb-3 p-3 p-sm-4 rounded-3 border d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 shadow-xs">
                            <div class="min-w-0">
                                <div class="d-flex align-items-center gap-1.5 mb-2 flex-wrap">
                                    <span class="badge tracking-kicker px-2.5 py-1 fw-bold rounded-pill">
                                        <i class="fa-solid fa-receipt me-1"></i> OFFICIAL TRACKING NUMBER
                                    </span>
                                    @if($isUrgent)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-0.5 fw-bold" style="font-size: 10px;">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> OVERDUE
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2.5 flex-wrap">
                                    <span class="hero-tracking-number font-monospace fw-bold" style="font-size: clamp(2rem, 3.8vw, 2.65rem) !important; font-weight: 800 !important; letter-spacing: 0.05em !important; line-height: 1.15 !important;">
                                        {{ $request->tracking_number }}
                                    </span>
                                    <button type="button" class="btn btn-sm btn-light border py-1.5 px-3 text-muted copy-tracking-btn rounded-2 d-inline-flex align-items-center gap-1.5 shadow-xs" 
                                            onclick="copyTrackingNumber('{{ $request->tracking_number }}', this)" 
                                            title="Copy tracking number to clipboard"
                                            style="font-size: 12px; font-weight: 600;">
                                        <i class="fa-regular fa-copy"></i>
                                        <span class="copy-label">Copy</span>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-sm-end justify-content-center flex-shrink-0">
                                <span class="text-muted small mb-1.5 fw-semibold text-uppercase tracking-status-label" style="font-size: 11px; letter-spacing: 0.06em;">Current Status</span>
                                <div id="modal-status-badge-{{ $request->id }}">
                                    <span id="modal-status-{{ $request->id }}" class="status-badge status-{{ strtolower(str_replace(' ', '-', $request->status)) }} px-3 py-1.5 fw-bold shadow-xs" style="font-size: 13px;">
                                        <span class="status-dot"></span>
                                        <span>{{ $request->status }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- 2. "The Rest" (Applicant & Submission Details Grid) --}}
                        <div class="details-grid-card mb-3 border rounded-3 overflow-hidden">
                            {{-- Row A: Name + Age --}}
                            <div class="row g-0">
                                <div class="col-sm-6 p-3 grid-cell grid-cell-border-r grid-cell-border-b">
                                    <p class="text-muted mb-1 details-cell-label">Applicant Name</p>
                                    <p class="fw-bold text-dark mb-0 details-cell-value" id="modal-view-applicant-{{ $request->id }}">{{ strtoupper($request->first_name . ' ' . $request->middle_initial . ' ' . $request->last_name) }}</p>
                                </div>
                                <div class="col-sm-6 p-3 grid-cell grid-cell-border-b">
                                    <p class="text-muted mb-1 details-cell-label">Age</p>
                                    <p class="fw-bold text-dark mb-0 details-cell-value" id="modal-view-age-{{ $request->id }}">{{ $request->age ?? '—' }}</p>
                                </div>
                            </div>

                            {{-- Row B: Contact + ID Type --}}
                            <div class="row g-0">
                                <div class="col-sm-6 p-3 grid-cell grid-cell-border-r grid-cell-border-b">
                                    <p class="text-muted mb-1 details-cell-label">Contact Info</p>
                                    <p class="fw-semibold text-dark mb-0 details-cell-value" id="modal-view-contact-{{ $request->id }}" style="font-size:14px; word-break: break-all;">{{ $request->resident_email ?? $request->contact_number ?? '—' }}</p>
                                </div>
                                <div class="col-sm-6 p-3 grid-cell grid-cell-border-b">
                                    <p class="text-muted mb-1 details-cell-label">ID Type</p>
                                    <p class="fw-semibold text-dark mb-0 details-cell-value" id="modal-view-idtype-{{ $request->id }}" style="font-size:14px;">
                                        @if($idType)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 fw-semibold">
                                                <i class="fa-solid fa-id-card me-1"></i> {{ $idType }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Row C: Fee Amount + Date Submitted --}}
                            <div class="row g-0">
                                <div class="col-sm-6 p-3 grid-cell grid-cell-border-r">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p class="text-muted mb-0 details-cell-label">Fee Amount</p>
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
                                <div class="col-sm-6 p-3 grid-cell">
                                    <p class="text-muted mb-1 details-cell-label">Date Submitted</p>
                                    <p class="fw-semibold text-dark mb-0 font-tabular-date details-cell-value" style="font-size:14px;">{{ $request->created_at->format('F d, Y — h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Attached Documents and Type (Positioned BEFORE Requested Document) --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="text-muted mb-0 details-section-title">
                                    <i class="fa-solid fa-paperclip text-primary me-1.5"></i> Attached Documents &amp; Type
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
                                            <div class="bg-light rounded-3 border d-flex align-items-center gap-2 h-100 modal-doc-item" style="padding: 10px 12px;">
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
                                                    <div class="fw-bold text-dark text-truncate details-cell-value" style="font-size: 12.5px;" title="{{ $att['file_name'] }}">
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
                                <div id="modal-no-attachments-{{ $request->id }}" class="bg-light rounded-3 p-3 border text-center text-muted {{ in_array($request->status, ['Ready', 'Completed']) ? 'd-none' : '' }}" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-file-circle-question text-secondary me-1"></i> No uploaded supporting documents attached to this request.
                                </div>
                            @endif

                            {{-- Official Word Document Card Attached in Final Step Before Complete (Ready / Completed) --}}
                            <div class="modal-attached-word-card-{{ $request->id }} {{ in_array($request->status, ['Ready', 'Completed']) ? '' : 'd-none' }} {{ count($request->attachments_list) > 0 ? 'mt-2' : '' }}">
                                <div class="rounded-3 border d-flex justify-content-between align-items-center gap-2 p-2.5 shadow-xs" style="background-color: #f0fdf4; border-color: #86efac !important;">
                                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                                        <div class="rounded-3 bg-white border d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 44px; height: 44px;">
                                            <i class="fa-solid fa-file-word text-primary fs-4"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center gap-1.5 mb-0.5">
                                                <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 10px;">
                                                    <i class="fa-solid fa-paperclip me-1"></i> Official Document
                                                </span>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 10px;">
                                                    Attached &amp; Ready
                                                </span>
                                            </div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 13px;">
                                                {{ $request->document_type }}.docx
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="{{ route('admin.requests.generate', $request->id) }}" class="btn btn-sm btn-success rounded-3 px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1" title="Download attached official file">
                                            <i class="fa-solid fa-download"></i> <span class="d-none d-sm-inline">Download</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Requested Document (Positioned AFTER Attached Documents) --}}
                        <div class="pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="text-muted mb-0 details-section-title">
                                    <i class="fa-solid fa-file-lines text-primary me-1.5"></i> Requested {{ $docsCount > 1 ? 'Documents (' . $docsCount . ')' : 'Document' }}
                                </p>
                                @if($docsCount > 1)
                                    {{-- In Processing: Can generate document --}}
                                    <div class="modal-generate-doc-btn-{{ $request->id }} {{ $request->status === 'Processing' ? '' : 'd-none' }}">
                                        <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                           class="btn btn-sm btn-primary rounded-3 px-3 py-1 fw-semibold shadow-sm d-inline-flex align-items-center gap-1"
                                           title="Download All Documents in a Single ZIP Archive">
                                            <i class="fa-solid fa-file-zipper"></i> <span>Download All as ZIP</span>
                                        </a>
                                    </div>
                                    {{-- Final step (Ready/Completed): Document is attached and can no longer be generated --}}
                                    <div class="modal-attached-doc-btn-{{ $request->id }} {{ in_array($request->status, ['Ready', 'Completed']) ? '' : 'd-none' }}">
                                        <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                           class="btn btn-sm btn-light border border-success border-opacity-50 text-success rounded-3 px-3 py-1 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5"
                                           title="Download Attached ZIP Archive">
                                            <i class="fa-solid fa-paperclip text-success"></i> <span>Attached ZIP File</span>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            @if($docsCount > 1)
                                <div class="d-flex flex-column gap-3 mb-2">
                                    @foreach($modalDocsList as $docItem)
                                        @php
                                            $itemDocLower = strtolower($docItem['type'] ?? '');
                                            $isItemBusiness = str_contains($itemDocLower, 'business');
                                        @endphp
                                        <div class="bg-light rounded-3 p-3.5 border modal-doc-item">
                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="fw-bold text-dark details-cell-value" style="font-size: 15px;">{{ $docItem['type'] }}</span>
                                                    @if(!$isItemBusiness)
                                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 11px;">FREE</span>
                                                    @elseif($docItem['fee'] !== null && (float)$docItem['fee'] > 0)
                                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 11px;">Fee: ₱{{ number_format($docItem['fee'], 2) }}</span>
                                                    @else
                                                        <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 11px;">Fee: To be settled</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-muted small mb-2.5">
                                                <span class="fw-semibold text-secondary">Purpose:</span> <span class="text-dark">{{ $docItem['purpose'] ?: ($request->purpose ?? 'No purpose specified') }}</span>
                                            </div>

                                            {{-- Processing stage: Generate Word Doc (Placed at bottom, Big and Visible) --}}
                                            <div class="modal-generate-doc-btn-{{ $request->id }} {{ $request->status === 'Processing' ? '' : 'd-none' }} pt-2.5 border-top">
                                                <a href="{{ route('admin.requests.generate', [$request->id, 'doc_type' => $docItem['type']]) }}" 
                                                   class="btn btn-primary w-100 rounded-3 py-2.5 px-4 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 text-white" 
                                                   style="font-size: 14px; min-height: 44px; background-color: #0f2240; border-color: #0f2240;" 
                                                   title="Generate Word Document for {{ $docItem['type'] }}">
                                                    <i class="fa-solid fa-file-word fs-5"></i> <span>Generate Word Doc ({{ $docItem['type'] }})</span>
                                                </a>
                                            </div>
                                            {{-- Final step (Ready/Completed): Attached Word File (Placed at bottom, Big and Visible) --}}
                                            <div class="modal-attached-doc-btn-{{ $request->id }} {{ in_array($request->status, ['Ready', 'Completed']) ? '' : 'd-none' }} pt-2.5 border-top">
                                                <a href="{{ route('admin.requests.generate', [$request->id, 'doc_type' => $docItem['type']]) }}" 
                                                   class="btn btn-light border border-success text-dark w-100 rounded-3 py-2.5 px-4 fw-bold shadow-xs d-flex align-items-center justify-content-center gap-2" 
                                                   style="font-size: 14px; min-height: 44px; background-color: #f0fdf4; border-color: #86efac !important;" 
                                                   title="Download Attached Word Document for {{ $docItem['type'] }}">
                                                    <i class="fa-solid fa-file-word text-primary fs-5"></i> <span>Attached File ({{ $docItem['type'] }})</span>
                                                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5 ms-1" style="font-size: 10px;"><i class="fa-solid fa-paperclip me-1"></i> Attached</span>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-light rounded-3 p-3.5 border mb-2 modal-doc-item">
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-bold text-dark details-cell-value" id="modal-view-doctype-{{ $request->id }}" style="font-size: 15px;">{{ $request->document_type }}</span>
                                            @if(!$isBusinessClearance)
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 11px;">FREE</span>
                                            @elseif($request->fee !== null && (float)$request->fee > 0)
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 11px;">Fee: ₱{{ number_format($request->fee, 2) }}</span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 11px;">Fee: To be settled</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-muted small mb-2.5">
                                        <span class="fw-semibold text-secondary">Purpose:</span> <span id="modal-view-purpose-{{ $request->id }}" class="text-dark">{{ $request->purpose ?? 'No purpose provided.' }}</span>
                                    </div>

                                    {{-- Processing stage: Generate Word Doc (Placed at bottom, Big and Visible) --}}
                                    <div class="modal-generate-doc-btn-{{ $request->id }} {{ $request->status === 'Processing' ? '' : 'd-none' }} pt-2.5 border-top">
                                        <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                           class="btn btn-primary w-100 rounded-3 py-2.5 px-4 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 text-white" 
                                           style="font-size: 14.5px; min-height: 46px; background-color: #0f2240; border-color: #0f2240;" 
                                           title="Generate Word Document">
                                            <i class="fa-solid fa-file-word fs-5"></i> <span>Generate Word Doc</span>
                                        </a>
                                    </div>
                                    {{-- Final step (Ready/Completed): Attached Word File (Placed at bottom, Big and Visible) --}}
                                    <div class="modal-attached-doc-btn-{{ $request->id }} {{ in_array($request->status, ['Ready', 'Completed']) ? '' : 'd-none' }} pt-2.5 border-top">
                                        <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                           class="btn btn-light border border-success text-dark w-100 rounded-3 py-2.5 px-4 fw-bold shadow-xs d-flex align-items-center justify-content-center gap-2" 
                                           style="font-size: 14.5px; min-height: 46px; background-color: #f0fdf4; border-color: #86efac !important;" 
                                           title="Download Attached Word Document">
                                            <i class="fa-solid fa-file-word text-primary fs-5"></i> <span>Attached Word File</span>
                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 ms-1" style="font-size: 10.5px;"><i class="fa-solid fa-paperclip me-1"></i> Attached</span>
                                        </a>
                                    </div>
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
                              class="edit-request-form"
                              data-request-id="{{ $request->id }}"
                              action="{{ route('admin.requests.update', $request->id) }}"
                              method="POST">
                            @csrf
                            @method('PATCH')

                            <div id="edit-form-alert-{{ $request->id }}" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3"></div>

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
                                        <i class="fa-solid fa-bullseye text-primary me-1"></i> Purpose
                                    </label>
                                    <input type="text" class="form-control form-control-sm rounded-3" name="purpose" 
                                           style="text-transform: uppercase;"
                                           value="{{ $request->purpose }}" 
                                           placeholder="e.g. SCHOLARSHIP APPLICATION, EMPLOYMENT">
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif

                </div>{{-- /modal-body --}}

                {{-- ======================== --}}
                {{-- FOOTER                   --}}
                {{-- ======================== --}}
                <div class="modal-footer bg-light border-top px-4 py-3 position-relative d-flex justify-content-center align-items-center">

                    {{-- Left: Delete (only for terminal states — Rejected or Completed) --}}
                    @if($editable && in_array($request->status, ['Rejected', 'Completed']))
                        <div class="position-absolute start-0 ms-4" id="modal-left-actions-{{ $request->id }}">
                            <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" class="m-0"
                                  onsubmit="return confirm('Permanently delete {{ $request->tracking_number }}? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-3 py-1.5 fw-semibold">
                                    <i class="fa-solid fa-trash me-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="position-absolute start-0 ms-4" id="modal-left-actions-{{ $request->id }}"></div>
                    @endif

                    {{-- Center: Prominent Centered Workflow Action Buttons --}}
                    <div class="d-flex gap-3 justify-content-center align-items-center flex-wrap">
                        @if($editable)
                            <button type="submit" form="edit-form-{{ $request->id }}"
                                    class="btn-modal-action btn-modal-save btn-save-request d-none"
                                    id="btn-save-{{ $request->id }}"
                                    data-request-id="{{ $request->id }}">
                                <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Changes
                            </button>

                            @if(in_array($request->status, ['Pending', 'Under Review']))
                            <div id="accept-reject-{{ $request->id }}" class="d-flex gap-3 justify-content-center align-items-center flex-wrap">
                                <button type="button" class="btn-modal-action btn-modal-reject"
                                        onclick="openRejectRequestModal({{ $request->id }}, '{{ addslashes($request->tracking_number) }}', '{{ addslashes($request->resident_name) }}')">
                                    <i class="fa-solid fa-xmark me-1.5"></i> Reject
                                </button>
                                {{-- Accept button: AJAX so the modal auto-flows to the next step --}}
                                <button type="button"
                                        class="btn-modal-action btn-modal-accept btn-accept-request"
                                        data-accept-url="{{ route('admin.requests.accept', $request->id) }}"
                                        data-ready-url="{{ route('admin.requests.ready', $request->id) }}"
                                        data-complete-url="{{ route('admin.requests.complete', $request->id) }}"
                                        data-csrf="{{ csrf_token() }}"
                                        data-request-id="{{ $request->id }}">
                                    <i class="fa-solid fa-check me-1.5"></i> Accept Request
                                </button>
                            </div>
                            @elseif($request->status == 'Processing')
                            <div id="accept-reject-{{ $request->id }}" class="d-flex gap-3 justify-content-center align-items-center flex-wrap">
                                <button type="button"
                                        class="btn-modal-action btn-modal-ready btn-ready-request"
                                        data-ready-url="{{ route('admin.requests.ready', $request->id) }}"
                                        data-complete-url="{{ route('admin.requests.complete', $request->id) }}"
                                        data-csrf="{{ csrf_token() }}"
                                        data-request-id="{{ $request->id }}">
                                    <i class="fa-solid fa-bell me-1.5"></i> Ready for Pickup
                                </button>
                            </div>
                            @elseif($request->status == 'Ready')
                            <div id="accept-reject-{{ $request->id }}" class="d-flex gap-3 justify-content-center align-items-center flex-wrap">
                                <button type="button"
                                        class="btn-modal-action btn-modal-complete btn-complete-request"
                                        data-complete-url="{{ route('admin.requests.complete', $request->id) }}"
                                        data-csrf="{{ csrf_token() }}"
                                        data-request-id="{{ $request->id }}">
                                    <i class="fa-solid fa-check-double me-1.5"></i> Mark Claimed / Completed
                                </button>
                            </div>
                            @elseif($request->status == 'Completed')
                            <div id="accept-reject-{{ $request->id }}" class="d-flex gap-3 justify-content-center align-items-center flex-wrap">
                                <span class="badge bg-success bg-opacity-10 text-success py-2.5 px-4 fw-bold rounded-pill" style="font-size: 14.5px; border: 1.5px solid #86efac;">
                                    <i class="fa-solid fa-circle-check me-1.5 text-success"></i> Completed
                                </span>
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
        id = String(id);
        const viewMode      = document.getElementById('view-mode-' + id);
        const editMode      = document.getElementById('edit-mode-' + id);
        const btnEdit       = document.getElementById('btn-edit-' + id);
        const btnCancel     = document.getElementById('btn-cancel-edit-' + id);
        const btnSave       = document.getElementById('btn-save-' + id);
        const btnHeaderSave = document.getElementById('btn-header-save-' + id);
        const acceptReject  = document.getElementById('accept-reject-' + id);
        const titleLabel    = document.getElementById('modal-title-label-' + id);

        if (editing) {
            if (viewMode) viewMode.classList.add('d-none');
            if (editMode) editMode.classList.remove('d-none');
            if (btnEdit) btnEdit.classList.add('d-none');
            if (btnCancel) btnCancel.classList.remove('d-none');
            if (btnSave) btnSave.classList.remove('d-none');
            if (btnHeaderSave) btnHeaderSave.classList.remove('d-none');
            if (acceptReject) acceptReject.classList.add('d-none');
            if (titleLabel) titleLabel.textContent = 'Edit Request';
        } else {
            if (viewMode) viewMode.classList.remove('d-none');
            if (editMode) editMode.classList.add('d-none');
            if (btnEdit) btnEdit.classList.remove('d-none');
            if (btnCancel) btnCancel.classList.add('d-none');
            if (btnSave) btnSave.classList.add('d-none');
            if (btnHeaderSave) btnHeaderSave.classList.add('d-none');
            if (acceptReject) acceptReject.classList.remove('d-none');
            if (titleLabel) titleLabel.textContent = 'Request Details';
        }
    }
    window.toggleEditMode = toggleEditMode;

    // Delegated click handler for Edit and Cancel Edit buttons
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit-request');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            const id = editBtn.dataset.requestId;
            if (id && typeof window.toggleEditMode === 'function') {
                window.toggleEditMode(id, true);
            }
            return;
        }

        const cancelBtn = e.target.closest('.btn-cancel-request');
        if (cancelBtn) {
            e.preventDefault();
            e.stopPropagation();
            const id = cancelBtn.dataset.requestId;
            if (id && typeof window.toggleEditMode === 'function') {
                window.toggleEditMode(id, false);
            }
            return;
        }
    });

    // Seamless AJAX submission for edit-request-form (keeps modal open, updates view dynamically)
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.edit-request-form');
        if (!form) return;

        e.preventDefault();

        const id = form.dataset.requestId;
        const btnSave = document.getElementById('btn-save-' + id);
        const btnHeaderSave = document.getElementById('btn-header-save-' + id);
        const alertBox = document.getElementById('edit-form-alert-' + id);

        if (alertBox) {
            alertBox.classList.add('d-none');
            alertBox.textContent = '';
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const origSaveHtml = btnSave ? btnSave.innerHTML : '';
        const origHeaderHtml = btnHeaderSave ? btnHeaderSave.innerHTML : '';

        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Saving…';
        }
        if (btnHeaderSave) {
            btnHeaderSave.disabled = true;
            btnHeaderSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving…';
        }

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errData => {
                    const msg = errData.message || (errData.errors ? Object.values(errData.errors).flat().join(' ') : 'Failed to update request.');
                    throw new Error(msg);
                });
            }
            return response.json();
        })
        .then(res => {
            const reqData = res.request;
            if (reqData) {
                // Update view mode elements
                const elApp = document.getElementById('modal-view-applicant-' + id);
                if (elApp) elApp.textContent = reqData.full_name;

                const elAge = document.getElementById('modal-view-age-' + id);
                if (elAge) elAge.textContent = reqData.age || '—';

                const elContact = document.getElementById('modal-view-contact-' + id);
                if (elContact) elContact.textContent = reqData.contact_display || '—';

                const elIdType = document.getElementById('modal-view-idtype-' + id);
                if (elIdType) {
                    if (reqData.id_type) {
                        elIdType.innerHTML = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 fw-semibold"><i class="fa-solid fa-id-card me-1"></i> ' + reqData.id_type + '</span>';
                    } else {
                        elIdType.innerHTML = '<span class="text-muted">—</span>';
                    }
                }

                const elDocType = document.getElementById('modal-view-doctype-' + id);
                if (elDocType) elDocType.textContent = reqData.document_type;

                const elPurpose = document.getElementById('modal-view-purpose-' + id);
                if (elPurpose) elPurpose.textContent = reqData.purpose || 'No purpose provided.';

                // Also update background table row
                const rowApplicant = document.querySelector('#req-row-' + id + ' .col-applicant .fw-bold');
                if (rowApplicant) rowApplicant.textContent = (reqData.first_name + ' ' + reqData.last_name).trim();

                const rowAvatar = document.querySelector('#req-row-' + id + ' .col-applicant .rounded-circle');
                if (rowAvatar) {
                    const initials = ((reqData.first_name ? reqData.first_name.charAt(0) : '') + (reqData.last_name ? reqData.last_name.charAt(0) : '')).toUpperCase();
                    rowAvatar.textContent = initials || 'R';
                }

                const rowContact = document.querySelector('#req-row-' + id + ' .col-applicant .text-muted');
                if (rowContact) rowContact.textContent = reqData.contact_display;

                const rowDoc = document.querySelector('#req-row-' + id + ' .col-doctype .fw-semibold');
                if (rowDoc) rowDoc.textContent = reqData.document_type;
            }

            // Smoothly switch back to view mode without closing modal
            toggleEditMode(id, false);

            if (typeof window.agToast === 'function') {
                window.agToast('Changes Saved', 'Document request details have been updated.', 'success', 3500);
            }
        })
        .catch(err => {
            console.error('Save request error:', err);
            if (alertBox) {
                alertBox.textContent = err.message || 'An error occurred while saving changes.';
                alertBox.classList.remove('d-none');
            } else {
                alert(err.message || 'Could not save changes. Please try again.');
            }
        })
        .finally(() => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = origSaveHtml;
            }
            if (btnHeaderSave) {
                btnHeaderSave.disabled = false;
                btnHeaderSave.innerHTML = origHeaderHtml;
            }
        });
    });
        // Copy tracking number helper
    window.copyTrackingNumber = function(text, btnEl) {
        function showFeedback() {
            if (!btnEl) return;
            const originalHtml = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="fa-solid fa-check text-success"></i> <span class="text-success fw-bold">Copied!</span>';
            setTimeout(() => {
                btnEl.innerHTML = originalHtml;
            }, 1800);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(showFeedback).catch(() => {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }

        function fallbackCopy(val) {
            const temp = document.createElement('input');
            temp.value = val;
            document.body.appendChild(temp);
            temp.select();
            try {
                document.execCommand('copy');
                showFeedback();
            } catch (err) {}
            document.body.removeChild(temp);
        }
    };

    window.syncDocsJson = function(id) { return true; };

    // ── Global Flag for Seamless Modal Workflow ──
    window._modalStatusChanged = false;

    function silentUpdateCounts() {
        fetch(window.location.href)
            .then(r => r.text())
            .then(html => {
                const doc2 = new DOMParser().parseFromString(html, 'text/html');
                const ids = ['kpi-total','kpi-pending','kpi-approved','kpi-overdue','kpi-avg',
                             'tab-badge-pending','tab-badge-processing','tab-badge-ready','tab-badge-completed','tab-badge-rejected'];
                ids.forEach(id => {
                    const nEl = doc2.getElementById(id);
                    const oEl = document.getElementById(id);
                    if (nEl && oEl) oEl.innerHTML = nEl.innerHTML;
                });
            })
            .catch(() => {});
    }

    // ── 1. AJAX Accept Request: Pending -> Processing (modal stays open) ──
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-accept-request');
        if (!btn) return;

        const acceptUrl   = btn.dataset.acceptUrl;
        const readyUrl    = btn.dataset.readyUrl;
        const completeUrl = btn.dataset.completeUrl;
        const csrfToken   = btn.dataset.csrf;
        const requestId   = btn.dataset.requestId;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Accepting…';

        fetch(acceptUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: '_method=PATCH&_token=' + encodeURIComponent(csrfToken),
            redirect: 'follow',
        })
        .then(response => {
            if (!response.ok) throw new Error('Server returned ' + response.status);
            return response;
        })
        .then(() => {
            window._modalStatusChanged = true;

            // 1. Update status badge inside the modal
            const modalStatusBadge = document.getElementById('modal-status-badge-' + requestId);
            if (modalStatusBadge) {
                modalStatusBadge.innerHTML =
                    '<span class="status-badge status-processing px-3 py-1.5 fw-bold shadow-xs" style="font-size:13px;">' +
                    '<span class="status-dot"></span><span>Processing</span></span>';
            }
            if (typeof updateModalStepper === 'function') {
                updateModalStepper(requestId, 2);
            }

            // 2. Also update row badge in the background table
            const rowBadge = document.getElementById('status-badge-' + requestId);
            if (rowBadge) {
                rowBadge.innerHTML = '<span class="status-badge status-processing" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Processing</span>';
            }
            const rowBadgeMobile = document.getElementById('status-badge-mobile-' + requestId);
            if (rowBadgeMobile) {
                rowBadgeMobile.innerHTML = '<span class="status-badge status-processing"><span class="status-dot"></span><span>Processing</span></span>';
            }

            // 3. Reveal "Generate Word Doc" button in modal (can generate during Processing)
            document.querySelectorAll('.modal-generate-doc-btn-' + requestId).forEach(el => {
                el.classList.remove('d-none');
                el.style.animation = 'agy-fadeIn 0.4s ease';
            });
            document.querySelectorAll('.modal-attached-doc-btn-' + requestId).forEach(el => {
                el.classList.add('d-none');
            });
            document.querySelectorAll('.modal-attached-word-card-' + requestId).forEach(el => {
                el.classList.add('d-none');
            });

            // 4. Swap footer buttons → show AJAX "Ready for Pickup" button
            const footerDiv = document.getElementById('accept-reject-' + requestId);
            if (footerDiv) {
                footerDiv.innerHTML =
                    '<button type="button" class="btn-modal-action btn-modal-ready btn-ready-request" ' +
                    'data-ready-url="' + readyUrl + '" ' +
                    'data-complete-url="' + completeUrl + '" ' +
                    'data-csrf="' + csrfToken + '" ' +
                    'data-request-id="' + requestId + '">' +
                    '<i class="fa-solid fa-bell me-1.5"></i> Ready for Pickup' +
                    '</button>';
            }

            if (typeof window.agToast === 'function') {
                window.agToast('Request Accepted', 'Request moved to Processing. Word document generation is now available.', 'success', 4000);
            }

            silentUpdateCounts();
        })
        .catch(err => {
            console.error('Accept AJAX error:', err);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Accept Request';
            alert('Could not accept the request. Please try again.');
        });
    });

    // ── 2. AJAX Ready for Pickup: Processing -> Ready (modal stays open, Generate Word appears!) ──
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-ready-request');
        if (!btn) return;

        const readyUrl    = btn.dataset.readyUrl;
        const completeUrl = btn.dataset.completeUrl;
        const csrfToken   = btn.dataset.csrf;
        const requestId   = btn.dataset.requestId;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Updating…';

        fetch(readyUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: '_method=PATCH&_token=' + encodeURIComponent(csrfToken),
            redirect: 'follow',
        })
        .then(response => {
            if (!response.ok) throw new Error('Server returned ' + response.status);
            return response;
        })
        .then(() => {
            window._modalStatusChanged = true;

            // 1. Update status badge inside the modal
            const modalStatusBadge = document.getElementById('modal-status-badge-' + requestId);
            if (modalStatusBadge) {
                modalStatusBadge.innerHTML =
                    '<span class="status-badge status-ready px-3 py-1.5 fw-bold shadow-xs" style="font-size:13px;">' +
                    '<span class="status-dot"></span><span>Ready for Pickup</span></span>';
            }
            if (typeof updateModalStepper === 'function') {
                updateModalStepper(requestId, 3);
            }

            // 2. Also update row badge in the background table
            const rowBadge = document.getElementById('status-badge-' + requestId);
            if (rowBadge) {
                rowBadge.innerHTML = '<span class="status-badge status-ready" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Ready for Pickup</span>';
            }
            const rowBadgeMobile = document.getElementById('status-badge-mobile-' + requestId);
            if (rowBadgeMobile) {
                rowBadgeMobile.innerHTML = '<span class="status-badge status-ready"><span class="status-dot"></span><span>Ready</span></span>';
            }

            // 3. Final step before complete: Word document is attached and can no longer be generated
            document.querySelectorAll('.modal-generate-doc-btn-' + requestId).forEach(el => {
                el.classList.add('d-none');
            });
            document.querySelectorAll('.modal-attached-doc-btn-' + requestId).forEach(el => {
                el.classList.remove('d-none');
                el.style.animation = 'agy-fadeIn 0.4s ease';
            });
            document.querySelectorAll('.modal-attached-word-card-' + requestId).forEach(el => {
                el.classList.remove('d-none');
                el.style.animation = 'agy-fadeIn 0.4s ease';
            });
            const emptyAtt = document.getElementById('modal-no-attachments-' + requestId);
            if (emptyAtt) emptyAtt.classList.add('d-none');

            // 4. Swap footer buttons → show AJAX "Mark Claimed / Completed" button
            const footerDiv = document.getElementById('accept-reject-' + requestId);
            if (footerDiv) {
                footerDiv.innerHTML =
                    '<button type="button" class="btn-modal-action btn-modal-complete btn-complete-request" ' +
                    'data-complete-url="' + completeUrl + '" ' +
                    'data-csrf="' + csrfToken + '" ' +
                    'data-request-id="' + requestId + '">' +
                    '<i class="fa-solid fa-check-double me-1.5"></i> Mark Claimed / Completed' +
                    '</button>';
            }

            if (typeof window.agToast === 'function') {
                window.agToast('Ready for Pickup', 'Document marked Ready. The Word file is now attached for pickup.', 'info', 4000);
            }

            silentUpdateCounts();
        })
        .catch(err => {
            console.error('Ready AJAX error:', err);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-bell me-1"></i> Ready for Pickup';
            alert('Could not update request to Ready. Please try again.');
        });
    });

    // ── 3. AJAX Complete Request: Ready -> Completed (modal stays open) ──
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-complete-request');
        if (!btn) return;

        const completeUrl = btn.dataset.completeUrl;
        const csrfToken   = btn.dataset.csrf;
        const requestId   = btn.dataset.requestId;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Completing…';

        fetch(completeUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: '_method=PATCH&_token=' + encodeURIComponent(csrfToken),
            redirect: 'follow',
        })
        .then(response => {
            if (!response.ok) throw new Error('Server returned ' + response.status);
            return response;
        })
        .then(() => {
            window._modalStatusChanged = true;

            // 1. Update status badge inside the modal
            const modalStatusBadge = document.getElementById('modal-status-badge-' + requestId);
            if (modalStatusBadge) {
                modalStatusBadge.innerHTML =
                    '<span class="status-badge status-completed px-3 py-1.5 fw-bold shadow-xs" style="font-size:13px;">' +
                    '<span class="status-dot"></span><span>Completed</span></span>';
            }
            if (typeof updateModalStepper === 'function') {
                updateModalStepper(requestId, 4);
            }

            // 2. Also update row badge in the background table
            const rowBadge = document.getElementById('status-badge-' + requestId);
            if (rowBadge) {
                rowBadge.innerHTML = '<span class="status-badge status-completed" style="width: 112px; font-size: 11.5px; padding: 4px 6px; display: inline-flex; align-items: center; justify-content: center;">Completed</span>';
            }
            const rowBadgeMobile = document.getElementById('status-badge-mobile-' + requestId);
            if (rowBadgeMobile) {
                rowBadgeMobile.innerHTML = '<span class="status-badge status-completed"><span class="status-dot"></span><span>Completed</span></span>';
            }

            // 3. Swap footer buttons → show Completed badge
            const footerDiv = document.getElementById('accept-reject-' + requestId);
            if (footerDiv) {
                footerDiv.innerHTML =
                    '<span class="badge bg-success bg-opacity-10 text-success py-2.5 px-4 fw-bold rounded-pill" style="font-size: 14.5px; border: 1.5px solid #86efac;">' +
                    '<i class="fa-solid fa-circle-check me-1.5 text-success"></i> Completed</span>';
            }

            if (typeof window.agToast === 'function') {
                window.agToast('Request Completed', 'Document marked as Completed and claimed.', 'success', 4000);
            }

            silentUpdateCounts();
        })
        .catch(err => {
            console.error('Complete AJAX error:', err);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check-double me-1"></i> Mark Claimed / Completed';
            alert('Could not complete the request. Please try again.');
        });
    });

    // Automatically reset modal to view mode when closed & refresh table if status changed
    document.addEventListener('hidden.bs.modal', function(e) {
        if (e.target && e.target.id && e.target.id.startsWith('viewModal')) {
            const id = e.target.id.replace('viewModal', '');
            if (typeof window.toggleEditMode === 'function') {
                window.toggleEditMode(id, false);
            }
            if (window._modalStatusChanged) {
                window._modalStatusChanged = false;
                if (typeof window.loadPageUrl === 'function') {
                    window.loadPageUrl(window.location.href, false);
                }
            }
        }
    });

    // ── Function to update Modal Stepper Progress Bar & Nodes ──
    function updateModalStepper(requestId, step) {
        const fill = document.getElementById('modal-stepper-fill-' + requestId);
        const col1 = document.getElementById('step-col-1-' + requestId);
        const col2 = document.getElementById('step-col-2-' + requestId);
        const col3 = document.getElementById('step-col-3-' + requestId);

        if (!fill || !col1 || !col2 || !col3) return;

        // Reset classes
        [col1, col2, col3].forEach(c => c.classList.remove('active', 'completed', 'rejected'));

        if (step === 1) {
            fill.dataset.targetWidth = '0%';
            fill.style.width = '0%';
            fill.style.background = '';
            col1.classList.add('active');
        } else if (step === 2) {
            fill.dataset.targetWidth = '50%';
            fill.style.width = '50%';
            fill.style.background = '';
            col1.classList.add('completed');
            col2.classList.add('active');
        } else if (step === 3) {
            fill.dataset.targetWidth = '100%';
            fill.style.width = '100%';
            fill.style.background = '';
            col1.classList.add('completed');
            col2.classList.add('completed');
            col3.classList.add('active');
        } else if (step >= 4) {
            fill.dataset.targetWidth = '100%';
            fill.style.width = '100%';
            fill.style.background = '#10b981';
            col1.classList.add('completed');
            col2.classList.add('completed');
            col3.classList.add('completed');
        }
    }
    window.updateModalStepper = updateModalStepper;

    // Trigger smooth entrance fill animation when modal opens
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target && e.target.id && e.target.id.startsWith('viewModal')) {
            const fill = e.target.querySelector('.modal-stepper-track-fill');
            if (fill && fill.dataset.targetWidth) {
                const target = fill.dataset.targetWidth;
                fill.style.width = '0%';
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        fill.style.width = target;
                    });
                });
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
                        if (newTbody && currentTbody && newTbody.innerHTML.trim() !== currentTbody.innerHTML.trim()) {
                            currentTbody.innerHTML = newTbody.innerHTML;
                        }

                        let newMobile = doc.getElementById('requests-mobile-list');
                        let currentMobile = document.getElementById('requests-mobile-list');
                        if (newMobile && currentMobile && newMobile.innerHTML.trim() !== currentMobile.innerHTML.trim()) {
                            currentMobile.innerHTML = newMobile.innerHTML;
                        }

                        let newModals = doc.getElementById('modals-container');
                        let currentModals = document.getElementById('modals-container');
                        if (newModals && currentModals && newModals.innerHTML.trim() !== currentModals.innerHTML.trim()) {
                            currentModals.innerHTML = newModals.innerHTML;
                        }

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

                        if (typeof window.updateBulkBar === 'function') window.updateBulkBar();
                        if (typeof window.initRowClickHandlers === 'function') window.initRowClickHandlers();
                    })
                    .catch(error => console.error("Live search error:", error));
            }, 300);
        });

        // 2. Real-Time Auto-Refresh Polling (10 Seconds, paused when tab is inactive)
        let _prevTabCounts = null;
        let _liveIndicator = document.getElementById('live-status-indicator');

        function _parseTabCounts(doc) {
            const ids = ['tab-badge-pending','tab-badge-processing','tab-badge-ready','tab-badge-completed','tab-badge-rejected'];
            const counts = {};
            ids.forEach(id => {
                const el = doc.getElementById(id);
                counts[id] = el ? el.textContent.trim() : '0';
            });
            return counts;
        }

        function _countsChanged(a, b) {
            if (!a) return false;
            return JSON.stringify(a) !== JSON.stringify(b);
        }

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
                    if (newTbody && currentTbody && newTbody.innerHTML.trim() !== currentTbody.innerHTML.trim()) {
                        currentTbody.innerHTML = newTbody.innerHTML;
                    }

                    let newMobile = doc.getElementById('requests-mobile-list');
                    let currentMobile = document.getElementById('requests-mobile-list');
                    if (newMobile && currentMobile && newMobile.innerHTML.trim() !== currentMobile.innerHTML.trim()) {
                        currentMobile.innerHTML = newMobile.innerHTML;
                    }

                    let newModals = doc.getElementById('modals-container');
                    let currentModals = document.getElementById('modals-container');
                    if (newModals && currentModals && newModals.innerHTML.trim() !== currentModals.innerHTML.trim()) {
                        currentModals.innerHTML = newModals.innerHTML;
                    }

                    let newPag = doc.getElementById('pagination-container');
                    let currentPag = document.getElementById('pagination-container');
                    if (newPag && currentPag && newPag.innerHTML.trim() !== currentPag.innerHTML.trim()) {
                        currentPag.innerHTML = newPag.innerHTML;
                    } else if (newPag && !currentPag) {
                        let mobileEl = document.getElementById('requests-mobile-list');
                        if (mobileEl) mobileEl.after(newPag);
                    } else if (!newPag && currentPag) {
                        currentPag.remove();
                    }

                    if (typeof window.updateBulkBar === 'function') window.updateBulkBar();
                    if (typeof window.initRowClickHandlers === 'function') window.initRowClickHandlers();

            const elementIdsToUpdate = [
                'kpi-total', 'kpi-pending', 'kpi-approved', 'kpi-overdue', 'kpi-avg',
                'tab-badge-pending', 'tab-badge-processing', 'tab-badge-ready', 'tab-badge-completed', 'tab-badge-rejected'
            ];

            elementIdsToUpdate.forEach(id => {
                let newEl = doc.getElementById(id);
                let oldEl = document.getElementById(id);
                if (newEl && oldEl) oldEl.innerHTML = newEl.innerHTML;
            });

            // Detect count changes → show toast
            const newCounts = _parseTabCounts(doc);
            if (_countsChanged(_prevTabCounts, newCounts)) {
                if (_prevTabCounts) {
                    const statusNames = {
                        'tab-badge-pending':    'Pending & Review',
                        'tab-badge-processing': 'Processing',
                        'tab-badge-ready':      'Ready for Pickup',
                        'tab-badge-completed':  'Completed',
                        'tab-badge-rejected':   'Rejected',
                    };
                    Object.keys(newCounts).forEach(key => {
                        const prev = parseInt(_prevTabCounts[key]) || 0;
                        const curr = parseInt(newCounts[key]) || 0;
                        if (curr > prev && typeof window.agToast === 'function') {
                            window.agToast(
                                'Request Count Updated',
                                `${statusNames[key]}: ${curr} request${curr !== 1 ? 's' : ''}`,
                                key === 'tab-badge-rejected' ? 'warning' : 'info',
                                5000
                            );
                        }
                    });
                }
                _prevTabCounts = newCounts;
            } else if (!_prevTabCounts) {
                _prevTabCounts = newCounts;
            }

            // Update timestamp
            const lbl = document.getElementById('last-refreshed-label');
            if (lbl) {
                const now = new Date();
                lbl.textContent = `Auto-refreshed ${now.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', second:'2-digit'})}`;
            }
        })
        .catch(error => console.error("Auto-refresh error:", error));

        }, 10000); 

        // 3. Real-Time WebSocket Event Listener
        document.addEventListener('admin:realtime-event', function(e) {
            const payload = e.detail;
            console.log('⚡ Admin requests received realtime event:', payload);
            const isModalOpen = document.body.classList.contains('modal-open') || 
                                document.querySelector('.modal.show') !== null ||
                                document.querySelector('.modal-backdrop') !== null;
            if (isModalOpen) {
                // Keep the open modal completely intact; only update stat counts
                if (typeof silentUpdateCounts === 'function') silentUpdateCounts();
                return;
            }
            if (typeof window.loadPageUrl === 'function') {
                window.loadPageUrl(window.location.href, false, true);
            }
        });

        // 4. AJAX Tab / Pipeline Switching Animation
        function loadPageUrl(url, pushState = true, isRealtime = false) {
            const tableCard = document.querySelector('.card.border-0.shadow-sm');
            if (tableCard && !isRealtime) {
                tableCard.style.opacity = '0.55';
                tableCard.style.transition = 'opacity 0.15s ease';
            }

            fetch(url)
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // 1. Update pipeline tabs
                    const newPipeline = doc.querySelector('.process-pipeline-container');
                    const currPipeline = document.querySelector('.process-pipeline-container');
                    if (newPipeline && currPipeline) {
                        currPipeline.innerHTML = newPipeline.innerHTML;
                    }

                    // 2. Update table body & animate rows
                    const newTbody = doc.getElementById('requests-tbody');
                    const currTbody = document.getElementById('requests-tbody');
                    if (newTbody && currTbody) {
                        currTbody.innerHTML = newTbody.innerHTML;
                    }

                    // 3. Update mobile list
                    const newMobile = doc.getElementById('requests-mobile-list');
                    const currMobile = document.getElementById('requests-mobile-list');
                    if (newMobile && currMobile) {
                        currMobile.innerHTML = newMobile.innerHTML;
                    }

                    // 4. Update modals container (never replace if a modal is currently open on screen!)
                    const isAnyModalOpen = document.body.classList.contains('modal-open') || 
                                          document.querySelector('.modal.show') !== null;
                    const newModals = doc.getElementById('modals-container');
                    const currModals = document.getElementById('modals-container');
                    if (newModals && currModals && !isAnyModalOpen) {
                        currModals.innerHTML = newModals.innerHTML;
                    }

                    // 5. Update pagination
                    const newPag = doc.getElementById('pagination-container');
                    const currPag = document.getElementById('pagination-container');
                    if (newPag && currPag) {
                        currPag.innerHTML = newPag.innerHTML;
                    } else if (newPag && !currPag) {
                        const m = document.getElementById('requests-mobile-list');
                        if (m) m.after(newPag);
                    } else if (!newPag && currPag) {
                        currPag.remove();
                    }

                    // 6. Update card header title & counts
                    const newCardHeader = doc.querySelector('.card-header');
                    const currCardHeader = document.querySelector('.card-header');
                    if (newCardHeader && currCardHeader) {
                        const newTitle = newCardHeader.querySelector('h5');
                        const currTitle = currCardHeader.querySelector('h5');
                        if (newTitle && currTitle) currTitle.innerHTML = newTitle.innerHTML;
                        const newBadge = newCardHeader.querySelector('.badge');
                        const currBadge = currCardHeader.querySelector('.badge');
                        if (newBadge && currBadge) currBadge.innerHTML = newBadge.innerHTML;
                    }

                    // 7. Update KPIs with count animation
                    const elementIds = [
                        'kpi-total', 'kpi-pending', 'kpi-approved', 'kpi-overdue', 'kpi-avg',
                        'tab-badge-pending', 'tab-badge-processing', 'tab-badge-ready', 'tab-badge-completed', 'tab-badge-rejected'
                    ];
                    elementIds.forEach(id => {
                        const newEl = doc.getElementById(id);
                        const oldEl = document.getElementById(id);
                        if (newEl && oldEl && newEl.textContent !== oldEl.textContent) {
                            oldEl.innerHTML = newEl.innerHTML;
                            oldEl.classList.add('stat-count-flash');
                            setTimeout(() => oldEl.classList.remove('stat-count-flash'), 600);
                        }
                    });

                    // 8. Re-initialize interactive row & bulk handlers
                    if (typeof window.initRowClickHandlers === 'function') window.initRowClickHandlers();
                    if (typeof window.initCheckboxHandlers === 'function') window.initCheckboxHandlers();

                    if (pushState) {
                        window.history.pushState({ url }, '', url);
                    }

                    if (tableCard) {
                        tableCard.style.opacity = '1';
                    }
                })
                .catch(err => {
                    console.error('AJAX page load error:', err);
                    if (tableCard) tableCard.style.opacity = '1';
                });
        }
        window.loadPageUrl = loadPageUrl;

        // Delegate pipeline click events for seamless panel switching
        document.addEventListener('click', function(e) {
            const pillLink = e.target.closest('.pipeline-pill-link');
            if (pillLink && pillLink.getAttribute('href') && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
                e.preventDefault();
                const url = pillLink.getAttribute('href');
                document.querySelectorAll('.pipeline-pill-link').forEach(p => p.classList.remove('active'));
                pillLink.classList.add('active');
                loadPageUrl(url, true);
            }
        });

        window.addEventListener('popstate', function() {
            loadPageUrl(window.location.href, false);
        });

        // 5. Table Density / Zoom Adjustment Mode
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

{{-- MODAL: CONFIRM REJECT & DELETE REQUEST --}}
<div class="modal fade" id="rejectRequestConfirmModal" tabindex="-1" aria-labelledby="rejectRequestConfirmLabel" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 58px; height: 58px;">
                    <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1" id="rejectRequestConfirmLabel">Reject this request?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to reject request <strong id="rejectModalTrackingNumber" class="text-danger font-monospace"></strong> for <strong id="rejectModalResidentName"></strong>? The request will be deleted and the resident will be notified.
                </p>
                <form id="rejectRequestForm" action="" method="POST" class="m-0">
                    @csrf
                    @method('PATCH')
                    <div class="d-flex justify-content-center gap-2 pt-2">
                        <button type="button" class="btn btn-light border px-4 py-2 rounded-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 py-2 rounded-3 fw-semibold shadow-sm" id="confirmRejectSubmitBtn">
                            <i class="fa-solid fa-xmark me-1.5"></i> Reject & Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.openRejectRequestModal = function(requestId, trackingNumber, residentName) {
    const modalEl = document.getElementById('rejectRequestConfirmModal');
    if (!modalEl) return;

    const form = document.getElementById('rejectRequestForm');
    const trackingEl = document.getElementById('rejectModalTrackingNumber');
    const nameEl = document.getElementById('rejectModalResidentName');

    if (form) {
        form.action = '/admin/requests/' + requestId + '/reject';
    }
    if (trackingEl) {
        trackingEl.textContent = trackingNumber || ('#' + requestId);
    }
    if (nameEl) {
        nameEl.textContent = residentName || 'Resident';
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
};

document.addEventListener('DOMContentLoaded', function () {
    const rejectModalEl = document.getElementById('rejectRequestConfirmModal');
    const rejectForm = document.getElementById('rejectRequestForm');
    const rejectSubmitBtn = document.getElementById('confirmRejectSubmitBtn');

    if (rejectModalEl) {
        rejectModalEl.addEventListener('hidden.bs.modal', function () {
            if (document.querySelectorAll('.modal.show').length > 0) {
                document.body.classList.add('modal-open');
            }
        });
    }

    if (rejectForm && rejectSubmitBtn) {
        rejectForm.addEventListener('submit', function () {
            rejectSubmitBtn.disabled = true;
            rejectSubmitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Rejecting...';
        });
    }
});
</script>

<script>
/* ─── Delegated Interactive Row Click Handler (Always active across AJAX, Live Search, & Auto-Refresh) ─── */
document.addEventListener("click", function(e) {
    const row = e.target.closest(".request-row-clickable");
    if (!row) return;

    // Prevent opening modal when clicking checkboxes, links, forms, buttons, or inputs
    if (e.target.closest(".req-checkbox") || 
        e.target.closest(".col-select") || 
        e.target.closest("input") || 
        e.target.closest("button") || 
        e.target.closest("a") || 
        e.target.closest("form") ||
        e.target.closest(".dropdown-menu")) {
        return;
    }

    const targetId = row.getAttribute("data-modal-target");
    if (targetId) {
        const modalEl = document.querySelector(targetId);
        if (modalEl) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }
    }
});

document.addEventListener("keydown", function(e) {
    if (e.key === "Enter" || e.key === " ") {
        const row = e.target.closest(".request-row-clickable");
        if (row && (e.target === row || row.contains(e.target)) && !e.target.closest("input, button, a, form, select, textarea")) {
            e.preventDefault();
            row.click();
        }
    }
});

function initRowClickHandlers() {
    // Retained for backward-compatibility; delegated document listener handles all rows automatically
}
window.initRowClickHandlers = initRowClickHandlers;

/* ─── Bulk Checkbox Selection & Bar Update ─── */
function updateBulkBar() {
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const selectAllCheckbox = document.getElementById('selectAllRequests');

    const checkedBoxes = Array.from(document.querySelectorAll('.req-checkbox:checked'));
    const count = checkedBoxes.length;

    if (count > 0) {
        bulkActionsBar?.classList.remove('d-none');
        if (selectedCountBadge) selectedCountBadge.textContent = `${count} Selected`;
        if (bulkDeleteCount) bulkDeleteCount.textContent = count;
    } else {
        bulkActionsBar?.classList.add('d-none');
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
window.updateBulkBar = updateBulkBar;

// Delegated checkbox change events
document.addEventListener('change', function (e) {
    if (e.target && e.target.classList.contains('req-checkbox')) {
        updateBulkBar();
    } else if (e.target && e.target.id === 'selectAllRequests') {
        const checked = e.target.checked;
        document.querySelectorAll('.req-checkbox').forEach(cb => cb.checked = checked);
        updateBulkBar();
    }
});

// Delegated click for deselect all button
document.addEventListener('click', function (e) {
    if (e.target && (e.target.id === 'deselectAllBtn' || e.target.closest('#deselectAllBtn'))) {
        document.querySelectorAll('.req-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAllRequests');
        if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
        updateBulkBar();
    }
});

// Delegated click for bulk delete button
document.addEventListener('click', function (e) {
    const btn = e.target.closest('#bulkDeleteBtn');
    if (!btn) return;

    const bulkDeleteModalEl = document.getElementById('requestsBulkDeleteModal');
    const bulkDeleteModal = bulkDeleteModalEl ? bootstrap.Modal.getOrCreateInstance(bulkDeleteModalEl) : null;
    const bulkHiddenIds = document.getElementById('bulk_hidden_ids');
    const bulkModalCount = document.getElementById('bulk_modal_count');
    const bulkSelectedNamesList = document.getElementById('bulk_selected_names_list');

    const checkedBoxes = Array.from(document.querySelectorAll('.req-checkbox:checked'));
    if (checkedBoxes.length === 0 || !bulkDeleteModal) return;

    if (bulkHiddenIds) bulkHiddenIds.innerHTML = '';
    if (bulkSelectedNamesList) bulkSelectedNamesList.innerHTML = '';
    if (bulkModalCount) bulkModalCount.textContent = `${checkedBoxes.length}`;

    const seenIds = new Set();
    checkedBoxes.forEach(cb => {
        if (seenIds.has(cb.value)) return;
        seenIds.add(cb.value);

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'ids[]';
        hiddenInput.value = cb.value;
        bulkHiddenIds?.appendChild(hiddenInput);

        const li = document.createElement('li');
        li.textContent = `Tracking #${cb.getAttribute('data-tracking') || cb.value}`;
        bulkSelectedNamesList?.appendChild(li);
    });

    bulkDeleteModal.show();
});

function initCheckboxHandlers() {
    updateBulkBar();
}
window.initCheckboxHandlers = initCheckboxHandlers;

document.addEventListener('DOMContentLoaded', function () {
    initRowClickHandlers();
    initCheckboxHandlers();

    /* ─── Count-Up Animation for KPI Numbers on page load ─── */
    function animateCountUp(el, target, duration) {
        if (!el) return;
        const start = 0;
        const range = target - start;
        const startTime = performance.now();
        function step(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(start + range * ease);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    const kpiIds = ['kpi-total', 'kpi-pending', 'kpi-approved', 'kpi-overdue'];
    kpiIds.forEach((id, i) => {
        const el = document.getElementById(id);
        if (el) {
            const target = parseInt(el.textContent.replace(/\D/g, ''));
            if (!isNaN(target) && target > 0) {
                el.textContent = '0';
                setTimeout(() => animateCountUp(el, target, 700), 120 + i * 70);
            }
        }
    });
});
</script>
@endsection