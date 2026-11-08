@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">

    <style>
        /* Responsive Table & Custom Scrollbars */
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

        .table-concerns {
            width: 100%;
            min-width: 900px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-concerns thead th {
            font-size: 0.73rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
            white-space: nowrap;
        }
        .table-concerns tbody td {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
            font-size: 0.86rem;
        }

        /* ── Clickable Selection Rows and Cards ── */
        .concern-row-clickable {
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        .concern-row-clickable:hover {
            background-color: #f8fafc !important;
        }
        .concern-row-clickable:hover .row-chevron {
            transform: translateX(3px);
            opacity: 1 !important;
            color: #2563eb !important;
        }
        .concern-row-clickable:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: -2px;
        }

        .row-chevron {
            transition: transform 0.15s ease, opacity 0.15s ease, color 0.15s ease;
        }

        /* Category Badges */
        .badge-cat-infra {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe !important;
        }
        .badge-cat-peace {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca !important;
        }
        .badge-cat-sanit {
            background-color: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0 !important;
        }
        .badge-cat-util {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a !important;
        }
        .badge-cat-other {
            background-color: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0 !important;
        }

        /* Mobile Status Pills Scroll & Card Layout */
        @media (max-width: 767.98px) {
            .concerns-nav-pills {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding-bottom: 8px;
                -webkit-overflow-scrolling: touch;
            }
            .concerns-nav-pills .nav-link {
                white-space: nowrap;
                padding: 7px 15px;
                font-size: 13px;
                flex-shrink: 0;
            }
            .mobile-concern-card {
                border: 1.5px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                margin-bottom: 12px;
                padding: 16px;
                transition: all 0.18s ease;
            }
            .mobile-concern-card:hover,
            .mobile-concern-card:active {
                border-color: #93c5fd;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
                transform: translateY(-1px);
            }
        }
    
        /* ═══════════════════════════════════════════════════════════
           DARK MODE OVERRIDES FOR COMMUNITY CONCERNS
           ═══════════════════════════════════════════════════════════ */
        html[data-theme="dark"] .table-concerns,
        html[data-bs-theme="dark"] .table-concerns,
        html[data-theme="dark"] .table-concerns > :not(caption) > * > *,
        html[data-bs-theme="dark"] .table-concerns > :not(caption) > * > *,
        html[data-theme="dark"] .table-concerns tbody td,
        html[data-bs-theme="dark"] .table-concerns tbody td {
            background-color: #131d31 !important;
            color: #e2e8f0 !important;
            border-color: #1e293b !important;
            box-shadow: none !important;
        }
        html[data-theme="dark"] .table-concerns thead th,
        html[data-bs-theme="dark"] .table-concerns thead th {
            background-color: #10192a !important;
            color: #cbd5e1 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .concern-row-clickable:hover > td,
        html[data-bs-theme="dark"] .concern-row-clickable:hover > td {
            background-color: #19253d !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .concern-row-clickable:hover .row-chevron,
        html[data-bs-theme="dark"] .concern-row-clickable:hover .row-chevron {
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .mobile-concern-card,
        html[data-bs-theme="dark"] .mobile-concern-card {
            background: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .mobile-concern-card:hover,
        html[data-bs-theme="dark"] .mobile-concern-card:active {
            border-color: #3b82f6 !important;
            background: #17233d !important;
        }
        html[data-theme="dark"] .modal-content,
        html[data-bs-theme="dark"] .modal-content {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .modal-header,
        html[data-bs-theme="dark"] .modal-header {
            background-color: #182238 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .modal-body .bg-light,
        html[data-bs-theme="dark"] .modal-body .bg-light {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .modal-body .text-dark,
        html[data-bs-theme="dark"] .modal-body .text-dark {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .modal-body .text-muted,
        html[data-bs-theme="dark"] .modal-body .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .modal-body .form-control,
        html[data-bs-theme="dark"] .modal-body .form-control,
        html[data-theme="dark"] .modal-body .form-select,
        html[data-bs-theme="dark"] .modal-body .form-select {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .badge-cat-infra {
            background-color: rgba(37, 99, 235, 0.18) !important;
            color: #93c5fd !important;
            border-color: rgba(59, 130, 246, 0.35) !important;
        }
        html[data-theme="dark"] .badge-cat-peace {
            background-color: rgba(239, 68, 68, 0.18) !important;
            color: #fca5a5 !important;
            border-color: rgba(239, 68, 68, 0.35) !important;
        }
        html[data-theme="dark"] .badge-cat-sanit {
            background-color: rgba(34, 197, 94, 0.18) !important;
            color: #86efac !important;
            border-color: rgba(34, 197, 94, 0.35) !important;
        }
        html[data-theme="dark"] .badge-cat-util {
            background-color: rgba(245, 158, 11, 0.18) !important;
            color: #fde047 !important;
            border-color: rgba(245, 158, 11, 0.35) !important;
        }
        html[data-theme="dark"] .badge-cat-other {
            background-color: rgba(100, 116, 139, 0.18) !important;
            color: #cbd5e1 !important;
            border-color: rgba(100, 116, 139, 0.35) !important;
        }
    </style>

    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <h2 class="header-title mb-0">Community Reports</h2>
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill small fw-semibold">
                Reports Management
            </span>
        </div>
        <p class="header-subtitle mb-0">Review, update, and respond to resident-submitted community reports and concerns.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-4 mb-4 shadow-sm border-0 d-flex align-items-center" role="alert" style="background-color: #f0fdf4; border: 1.5px solid #bbf7d0; color: #166534;">
            <i class="fa-solid fa-circle-check me-2 fa-lg text-success"></i>
            <span class="fw-medium">{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Status Filters (Scrollable on mobile) --}}
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap concerns-nav-pills">
        @foreach($statusCounts as $status => $count)
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3.5 py-2 {{ $currentStatus === $status ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}"
                   href="{{ request()->fullUrlWithQuery(['status' => $status === 'All' ? null : $status, 'search' => request('search')]) }}">
                    {{ $status }}
                    <span class="badge rounded-pill ms-1 {{ $currentStatus === $status ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark' }}">{{ $count }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header border-0 bg-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-top-4">
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                    <i class="fa-solid fa-shield-halved text-primary me-2"></i>{{ $currentStatus }} Reports
                </h5>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11.5px;">
                    {{ count($concerns) }} {{ count($concerns) === 1 ? 'record' : 'records' }}
                </span>
            </div>

            <form action="{{ route('admin.concerns') }}" method="GET" class="m-0 w-100 w-sm-auto flex-grow-1 flex-sm-grow-0">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="input-group input-group-sm rounded w-100" style="max-width: 320px; background-color: #f6f9fc;">
                    <button type="submit" class="input-group-text border-0 bg-transparent text-muted"><i class="fa fa-search"></i></button>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent shadow-none" placeholder="Search by ID, name, title, category..." autocomplete="off">
                    @if(request('search'))
                        <a href="{{ route('admin.concerns', ['status' => request('status')]) }}" class="btn btn-sm btn-transparent border-0 text-muted">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Desktop Table View (Screens >= 768px) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0 table-concerns">
                <thead class="text-muted small" style="background-color: #f8f9fe;">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-nowrap" style="width: 140px;">CONCERN ID</th>
                        <th class="py-3 border-0" style="min-width: 160px;">RESIDENT</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 130px;">CONTACT</th>
                        <th class="py-3 border-0" style="min-width: 180px;">TITLE</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 130px;">CATEGORY</th>
                        <th class="py-3 border-0" style="min-width: 140px;">LOCATION</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 120px;">SUBMITTED</th>
                        <th class="py-3 border-0 text-center text-nowrap" style="width: 125px;">STATUS</th>
                        <th class="pe-4 py-3 border-0 text-end" style="width: 44px;" aria-label="Details"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($concerns as $concern)
                        @php
                            $statusClass = match($concern->status) {
                                'Resolved'     => 'status-badge status-resolved',
                                'Rejected'     => 'status-badge status-rejected',
                                'In Progress'  => 'status-badge status-in-progress',
                                'Under Review' => 'status-badge status-under-review',
                                default        => 'status-badge status-pending',
                            };

                            $catLower = strtolower($concern->category ?? '');
                            $catStyle = match(true) {
                                str_contains($catLower, 'infra') || str_contains($catLower, 'road') => [
                                    'icon' => 'fa-solid fa-road',
                                    'class' => 'badge-cat-infra'
                                ],
                                str_contains($catLower, 'peace') || str_contains($catLower, 'order') => [
                                    'icon' => 'fa-solid fa-shield-halved',
                                    'class' => 'badge-cat-peace'
                                ],
                                str_contains($catLower, 'sanit') || str_contains($catLower, 'health') => [
                                    'icon' => 'fa-solid fa-leaf',
                                    'class' => 'badge-cat-sanit'
                                ],
                                str_contains($catLower, 'util') || str_contains($catLower, 'electric') || str_contains($catLower, 'water') => [
                                    'icon' => 'fa-solid fa-lightbulb',
                                    'class' => 'badge-cat-util'
                                ],
                                default => [
                                    'icon' => 'fa-solid fa-tag',
                                    'class' => 'badge-cat-other'
                                ],
                            };
                        @endphp
                        <tr class="concern-row-clickable align-middle"
                            data-bs-toggle="modal"
                            data-bs-target="#manageConcernModal{{ $concern->id }}"
                            tabindex="0"
                            role="button"
                            aria-label="View details for concern {{ $concern->concern_id }}">
                            {{-- Concern ID --}}
                            <td class="ps-4 py-3 text-nowrap">
                                <span class="badge border font-monospace fw-bold px-2.5 py-1.5 rounded-3 shadow-xs" style="font-size: 12.5px; background-color: #f1f5f9; color: #1e293b; border-color: #cbd5e1 !important;">
                                    <i class="fa-solid fa-hashtag text-secondary me-0.5" style="font-size: 11px;"></i>{{ $concern->concern_id }}
                                </span>
                            </td>

                            {{-- Resident --}}
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex justify-content-center align-items-center me-2.5 fw-bold text-uppercase flex-shrink-0 shadow-xs" 
                                         style="width: 36px; height: 36px; font-size: 12.5px; background: linear-gradient(135deg, #0f2240, #1e3a8a); color: #ffffff;">
                                        {{ substr($concern->resident_name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0" style="max-width: 180px;">
                                        <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px;">{{ $concern->resident_name }}</div>
                                        <div class="text-muted text-truncate" style="font-size: 11.5px;">{{ $concern->resident_email ?: 'No email provided' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="py-3 text-nowrap">
                                @if($concern->contact_number)
                                    <span class="text-dark fw-medium" style="font-size: 12.5px;">
                                        <i class="fa-solid fa-phone text-primary me-1" style="font-size: 10.5px;"></i>{{ $concern->contact_number }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size: 12.5px;">—</span>
                                @endif
                            </td>

                            {{-- Title --}}
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-1.5 min-w-0" style="max-width: 250px;">
                                    <span class="fw-bold text-dark text-truncate" style="font-size: 13.5px;" title="{{ $concern->title }}">
                                        {{ $concern->title }}
                                    </span>
                                    @if($concern->photo_path)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-1.5 py-0.5 flex-shrink-0" style="font-size: 9.5px;" title="Includes photo attachment">
                                            <i class="fa-solid fa-image"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Category --}}
                            <td class="py-3 text-nowrap">
                                <span class="badge rounded-pill px-2.5 py-1.5 border d-inline-flex align-items-center gap-1.5 fw-semibold {{ $catStyle['class'] }}" style="font-size: 11.5px;">
                                    <i class="{{ $catStyle['icon'] }}" style="font-size: 10px;"></i>
                                    <span>{{ $concern->category }}</span>
                                </span>
                            </td>

                            {{-- Location --}}
                            <td class="py-3 text-muted" style="font-size: 12.5px;">
                                <div class="d-flex align-items-center gap-1.5 text-truncate" style="max-width: 170px;" title="{{ $concern->location }}">
                                    <i class="fa-solid fa-location-dot text-danger flex-shrink-0" style="font-size: 11px;"></i>
                                    <span class="text-dark fw-medium text-truncate" style="font-size: 12.5px;">{{ $concern->location ?: '—' }}</span>
                                </div>
                            </td>

                            {{-- Submitted --}}
                            <td class="py-3 text-muted text-nowrap" style="font-size: 12.5px;">
                                <i class="fa-regular fa-calendar text-secondary me-1" style="font-size: 11px;"></i>{{ $concern->created_at->format('M d, Y') }}
                            </td>

                            {{-- Status --}}
                            <td class="py-3 text-center text-nowrap">
                                <span class="{{ $statusClass }}" style="min-width: 96px; font-size: 11.5px; padding: 4px 10px; display: inline-flex; align-items: center; justify-content: center;">
                                    <span class="status-dot"></span>
                                    <span>{{ $concern->status }}</span>
                                </span>
                            </td>

                            {{-- Click Affordance Chevron --}}
                            <td class="pe-4 py-3 text-end" style="width: 44px;">
                                <i class="fa-solid fa-chevron-right text-muted opacity-30 row-chevron" style="font-size: 11.5px;"></i>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 text-light"></i>
                                <h5 class="fw-bold text-dark">No Concerns Found</h5>
                                <div>There are currently no community concerns in this view.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (Screens < 768px) --}}
        <div class="d-md-none p-3" id="concerns-mobile-list">
            @forelse($concerns as $concern)
                @php
                    $statusClass = match($concern->status) {
                        'Resolved'     => 'status-badge status-resolved',
                        'Rejected'     => 'status-badge status-rejected',
                        'In Progress'  => 'status-badge status-in-progress',
                        'Under Review' => 'status-badge status-under-review',
                        default        => 'status-badge status-pending',
                    };

                    $catLower = strtolower($concern->category ?? '');
                    $catStyle = match(true) {
                        str_contains($catLower, 'infra') || str_contains($catLower, 'road') => [
                            'icon' => 'fa-solid fa-road',
                            'class' => 'badge-cat-infra'
                        ],
                        str_contains($catLower, 'peace') || str_contains($catLower, 'order') => [
                            'icon' => 'fa-solid fa-shield-halved',
                            'class' => 'badge-cat-peace'
                        ],
                        str_contains($catLower, 'sanit') || str_contains($catLower, 'health') => [
                            'icon' => 'fa-solid fa-leaf',
                            'class' => 'badge-cat-sanit'
                        ],
                        str_contains($catLower, 'util') || str_contains($catLower, 'electric') || str_contains($catLower, 'water') => [
                            'icon' => 'fa-solid fa-lightbulb',
                            'class' => 'badge-cat-util'
                        ],
                        default => [
                            'icon' => 'fa-solid fa-tag',
                            'class' => 'badge-cat-other'
                        ],
                    };
                @endphp
                <div class="mobile-concern-card concern-row-clickable"
                     data-bs-toggle="modal"
                     data-bs-target="#manageConcernModal{{ $concern->id }}"
                     tabindex="0"
                     role="button"
                     aria-label="View details for concern {{ $concern->concern_id }}">
                    {{-- Header: Concern ID + Category + Status --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom flex-wrap gap-1.5">
                        <div class="d-flex align-items-center gap-1.5 min-w-0">
                            <span class="badge border font-monospace fw-bold px-2 py-1 rounded-3" style="font-size: 12px; background-color: #f1f5f9; color: #1e293b; border-color: #cbd5e1 !important;">
                                <i class="fa-solid fa-hashtag text-secondary me-0.5"></i>{{ $concern->concern_id }}
                            </span>
                            <span class="badge rounded-pill px-2 py-0.5 border d-inline-flex align-items-center gap-1 fw-semibold {{ $catStyle['class'] }}" style="font-size: 10.5px;">
                                <i class="{{ $catStyle['icon'] }}"></i>
                                <span>{{ $concern->category }}</span>
                            </span>
                        </div>
                        <span class="{{ $statusClass }}" style="font-size: 11px; padding: 3px 8px;">
                            <span class="status-dot"></span>
                            <span>{{ $concern->status }}</span>
                        </span>
                    </div>

                    {{-- Resident Information --}}
                    <div class="d-flex align-items-center my-2">
                        <div class="rounded-circle d-flex justify-content-center align-items-center me-2.5 fw-bold text-uppercase flex-shrink-0 shadow-xs" 
                             style="width: 38px; height: 38px; font-size: 13px; background: linear-gradient(135deg, #0f2240, #1e3a8a); color: #ffffff;">
                            {{ substr($concern->resident_name, 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 14px;">{{ $concern->resident_name }}</div>
                            <div class="text-muted text-truncate" style="font-size: 11.5px;">
                                {{ $concern->contact_number ?: $concern->resident_email ?: 'No contact details' }}
                            </div>
                        </div>
                    </div>

                    {{-- Concern Title & Details Box --}}
                    <div class="bg-light rounded-3 p-2.5 my-2 border">
                        <div class="d-flex align-items-center gap-1.5 mb-1">
                            <span class="fw-bold text-dark text-truncate" style="font-size: 13.5px;">{{ $concern->title }}</span>
                            @if($concern->photo_path)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-1.5 py-0.5 flex-shrink-0" style="font-size: 9px;" title="Includes photo attachment">
                                    <i class="fa-solid fa-image"></i>
                                </span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 text-muted" style="font-size: 11.5px;">
                            @if($concern->location)
                                <span><i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $concern->location }}</span>
                            @endif
                            <span><i class="fa-regular fa-calendar text-primary me-1"></i>{{ $concern->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    {{-- Tap Hint Indicator --}}
                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top text-muted" style="font-size: 11.5px;">
                        <span class="d-inline-flex align-items-center gap-1.5 text-primary fw-medium">
                            <i class="fa-regular fa-folder-open"></i> Tap to view details &amp; update
                        </span>
                        <i class="fa-solid fa-chevron-right text-primary opacity-60 row-chevron"></i>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-inbox fa-3x mb-2 text-light d-block"></i>
                    <h6 class="fw-bold text-dark">No Concerns Found</h6>
                    <div class="small">There are currently no community concerns in this view.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===================== CONCERN DETAILS MODALS ===================== --}}
@foreach($concerns as $concern)
    @php
        $modalStatusClass = match($concern->status) {
            'Resolved'     => 'status-badge status-resolved',
            'Rejected'     => 'status-badge status-rejected',
            'In Progress'  => 'status-badge status-in-progress',
            'Under Review' => 'status-badge status-under-review',
            default        => 'status-badge status-pending',
        };
    @endphp
    <div class="modal fade" id="manageConcernModal{{ $concern->id }}" tabindex="-1" aria-labelledby="manageConcernModalLabel{{ $concern->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Modal Header --}}
                <div class="modal-header border-bottom py-3 px-4" style="background-color: #f8fafc;">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-shield-halved fs-5"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <h5 class="modal-title fw-bold text-dark mb-0" id="manageConcernModalLabel{{ $concern->id }}" style="font-size: 1.15rem;">
                                    Concern Details
                                </h5>
                                <span class="badge border font-monospace px-2 py-0.5 rounded-3 fw-bold" style="font-size: 11.5px; background: #f1f5f9; color: #1e293b; border-color: #cbd5e1 !important;">
                                    <i class="fa-solid fa-hashtag text-secondary me-0.5"></i>{{ $concern->concern_id }}
                                </span>
                                <span class="{{ $modalStatusClass }}" style="font-size: 11px; padding: 2.5px 8px;">
                                    <span class="status-dot"></span>
                                    <span>{{ $concern->status }}</span>
                                </span>
                            </div>
                            <div class="text-muted small">
                                Submitted on {{ $concern->created_at->format('F d, Y \a\t h:i A') }}
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4">
                    {{-- Section 1: Resident & Contact Information --}}
                    <div class="mb-4">
                        <div class="text-uppercase text-secondary fw-bold small mb-2 d-flex align-items-center gap-1.5" style="letter-spacing: 0.05em; font-size: 11px;">
                            <i class="fa-solid fa-user-circle text-primary"></i> Resident Information
                        </div>
                        <div class="bg-light rounded-3 p-3 border">
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-muted small">Resident Name</div>
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px;">{{ $concern->resident_name }}</div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-muted small">Contact Number</div>
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px;">
                                        {{ $concern->contact_number ? $concern->contact_number : 'N/A' }}
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-muted small">Email Address</div>
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px;">
                                        {{ $concern->resident_email ? $concern->resident_email : 'N/A' }}
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="text-muted small">Address / Purok</div>
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px;">
                                        {{ $concern->address ? $concern->address : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Concern Overview --}}
                    <div class="mb-4">
                        <div class="text-uppercase text-secondary fw-bold small mb-2 d-flex align-items-center gap-1.5" style="letter-spacing: 0.05em; font-size: 11px;">
                            <i class="fa-solid fa-circle-info text-primary"></i> Report Overview
                        </div>
                        <div class="bg-light rounded-3 p-3 border">
                            <div class="mb-2.5">
                                <div class="text-muted small">Concern Title</div>
                                <div class="fw-bold text-dark fs-6">{{ $concern->title }}</div>
                            </div>
                            <div class="row g-2.5 pt-2 border-top">
                                <div class="col-sm-4">
                                    <div class="text-muted small">Category</div>
                                    <div class="fw-semibold text-dark d-flex align-items-center gap-1.5 mt-0.5">
                                        <i class="fa-solid fa-tag text-primary" style="font-size: 11px;"></i>
                                        <span>{{ $concern->category }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-muted small">Location</div>
                                    <div class="fw-semibold text-dark d-flex align-items-center gap-1.5 mt-0.5">
                                        <i class="fa-solid fa-location-dot text-danger" style="font-size: 11px;"></i>
                                        <span>{{ $concern->location ?: 'Barangay Cogon East' }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="text-muted small">Date of Incident</div>
                                    <div class="fw-semibold text-dark d-flex align-items-center gap-1.5 mt-0.5">
                                        <i class="fa-regular fa-calendar-check text-secondary" style="font-size: 11px;"></i>
                                        <span>{{ $concern->incident_date ? $concern->incident_date->format('F d, Y') : 'Not specified' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Description --}}
                    <div class="mb-4">
                        <div class="text-uppercase text-secondary fw-bold small mb-2 d-flex align-items-center gap-1.5" style="letter-spacing: 0.05em; font-size: 11px;">
                            <i class="fa-solid fa-align-left text-primary"></i> Detailed Description
                        </div>
                        <div class="bg-white rounded-3 p-3 border text-dark shadow-xs" style="font-size: 13.5px; line-height: 1.6; white-space: pre-wrap;">{{ $concern->description }}</div>
                    </div>

                    {{-- Section 4: Uploaded Photo Attachment --}}
                    @if($concern->photo_path)
                        <div class="mb-4">
                            <div class="text-uppercase text-secondary fw-bold small mb-2 d-flex align-items-center justify-content-between" style="letter-spacing: 0.05em; font-size: 11px;">
                                <span><i class="fa-solid fa-image text-primary me-1"></i> Uploaded Photo</span>
                                <a href="{{ asset('storage/' . $concern->photo_path) }}" target="_blank" rel="noopener" class="text-primary text-decoration-none fw-semibold">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Full Image
                                </a>
                            </div>
                            <div class="p-2 border rounded-3 bg-light text-center">
                                <a href="{{ asset('storage/' . $concern->photo_path) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/' . $concern->photo_path) }}" 
                                         alt="Community concern evidence photo" 
                                         class="img-fluid rounded-3 border shadow-xs" 
                                         style="max-height: 260px; object-fit: cover;">
                                </a>
                            </div>
                        </div>
                    @endif

                    <hr class="my-4">

                    {{-- Section 5: Action Form (Status & Remarks) --}}
                    <form action="{{ route('admin.concerns.update', $concern->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="text-uppercase text-secondary fw-bold small mb-3 d-flex align-items-center gap-1.5" style="letter-spacing: 0.05em; font-size: 11px;">
                            <i class="fa-solid fa-pen-to-square text-primary"></i> Update Report Status &amp; Remarks
                        </div>

                        <div class="mb-3">
                            <label for="status{{ $concern->id }}" class="form-label fw-bold text-dark small">Update Status <span class="text-danger">*</span></label>
                            <select name="status" id="status{{ $concern->id }}" class="form-select rounded-3 py-2" required>
                                @foreach(\App\Models\CommunityConcern::statuses() as $status)
                                    <option value="{{ $status }}" {{ $concern->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin_remarks{{ $concern->id }}" class="form-label fw-bold text-dark small">Admin Remarks &amp; Updates</label>
                            <textarea name="admin_remarks" 
                                      id="admin_remarks{{ $concern->id }}" 
                                      rows="3" 
                                      class="form-control rounded-3" 
                                      placeholder="e.g. Barangay personnel inspected the area and coordinated with municipal engineers.">{{ old('admin_remarks', $concern->admin_remarks) }}</textarea>
                            <div class="form-text text-muted small">Residents will see the latest remark in their dashboard. Historical remarks are preserved.</div>
                        </div>

                        @if($concern->remarks->isNotEmpty())
                            <div class="mb-3">
                                <div class="text-muted small fw-semibold mb-2">Previous Remarks Timeline:</div>
                                <div class="bg-light rounded-3 p-3 border" style="max-height: 160px; overflow-y: auto;">
                                    @foreach($concern->remarks as $remark)
                                        <div class="mb-2 pb-2 border-bottom last-border-0">
                                            <div class="text-dark" style="font-size: 13px;">{{ $remark->remark }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <i class="fa-solid fa-user-shield me-1"></i> {{ $remark->admin?->name ?? 'Admin' }} &bull; {{ $remark->created_at->format('M d, Y h:i A') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($concern->updated_by)
                            <div class="text-muted small mb-3">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> Last updated by {{ $concern->updatedByAdmin?->name ?? 'Admin' }} on {{ $concern->updated_at->format('M d, Y h:i A') }}
                            </div>
                        @endif

                        <div class="d-flex justify-content-center gap-2 pt-2">
                            <button type="button" class="btn btn-light border rounded-3 px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold" style="background-color: #0f2240; border-color: #0f2240;">
                                <i class="fa-solid fa-save me-1.5"></i> Save Updates
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Enable click and keyboard activation on all concern selection rows and cards
    document.addEventListener('click', function (e) {
        const row = e.target.closest('.concern-row-clickable');
        if (!row) return;

        // Ignore clicks on form controls or links inside row if any exist
        if (e.target.closest('input, button, a, select, textarea, .btn-close')) return;

        const targetModalId = row.dataset.bsTarget;
        if (targetModalId) {
            const modalEl = document.querySelector(targetModalId);
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            const activeEl = document.activeElement;
            if (activeEl && activeEl.classList.contains('concern-row-clickable')) {
                if (e.target.closest('input, button, a, select, textarea')) return;
                e.preventDefault();
                const targetModalId = activeEl.dataset.bsTarget;
                if (targetModalId) {
                    const modalEl = document.querySelector(targetModalId);
                    if (modalEl) {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                }
            }
        }
    });
});
</script>
@endsection
