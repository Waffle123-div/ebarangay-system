<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Barangay System | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    
    <style>
        /* Sidebar Variables — Dark Theme Matching Reference */
        :root {
            --sidebar-width: 260px;
            --sidebar-bg:        #0F2240;
            --sidebar-bg-alt:    #0A1829;
            --sidebar-border:    rgba(255, 255, 255, 0.07);
            --sidebar-text:      #A0B5CC;
            --sidebar-text-hover: #ffffff;
            --sidebar-text-active: #ffffff;
            --sidebar-category:  #5A7A9A;
        }

        body {
            background-color: var(--color-bg);
            font-family: var(--font-body);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            overflow-x: clip;
            position: relative;
        }

        #sidebar-wrapper {
            min-height: 100vh;
            height: 100%;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), margin-left 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            box-shadow: 4px 0 16px rgba(0, 0, 0, 0.12);
        }

        /* Sidebar Brand Header */
        .sidebar-heading {
            padding: 1.15rem 1.15rem 1.15rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--sidebar-border);
            background: rgba(0, 0, 0, 0.15);
            flex-shrink: 0;
            height: 72px;
        }

        .sidebar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            min-width: 0;
        }

        .sidebar-brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2B6CB0 0%, #319795 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.15rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(49, 151, 149, 0.35);
        }

        .sidebar-brand-text {
            min-width: 0;
        }

        .sidebar-brand-text h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin: 0;
            color: #ffffff;
            font-family: var(--font-heading);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        .sidebar-brand-text p {
            font-size: 0.72rem;
            margin: 0;
            color: #4FD1C5;
            font-weight: 500;
            letter-spacing: 0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Sidebar Toggle inside Sidebar Header */
        .sidebar-header-toggle {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
            padding: 0;
        }

        .sidebar-header-toggle:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
        }

        /* Scrollable Category Nav List */
        .sidebar-nav-scroll {
            flex: 1 1 auto;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.65rem 0.55rem 0.85rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }

        .sidebar-nav-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-nav-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        /* Category Header Label */
        .sidebar-category-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--sidebar-category);
            padding: 0.85rem 0.75rem 0.35rem;
            margin-top: 0.2rem;
            display: flex;
            align-items: center;
            user-select: none;
        }

        .sidebar-category-label:first-child {
            margin-top: 0;
            padding-top: 0.25rem;
        }

        /* Nav Item Link */
        .sidebar-nav-item {
            display: flex;
            align-items: center;
            padding: 0.52rem 0.75rem;
            margin-bottom: 2px;
            color: var(--sidebar-text);
            font-weight: 500;
            font-size: 0.86rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .sidebar-nav-item i.nav-icon {
            width: 22px;
            font-size: 0.95rem;
            margin-right: 0.65rem;
            text-align: center;
            color: #64748b;
            transition: color 0.15s ease, transform 0.15s ease;
            flex-shrink: 0;
        }

        .sidebar-nav-item .nav-text {
            flex: 1 1 auto;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-nav-item:hover {
            background-color: rgba(255, 255, 255, 0.07);
            color: var(--sidebar-text-hover);
        }

        .sidebar-nav-item:hover i.nav-icon {
            color: #4FD1C5;
            transform: translateX(1px);
        }

        .sidebar-nav-item.active {
            background: linear-gradient(90deg, #18365D 0%, #2B6CB0 100%);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(24, 54, 93, 0.45);
        }

        .sidebar-nav-item.active i.nav-icon {
            color: #4FD1C5;
        }

        /* Sidebar Badges */
        .sidebar-badge {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            line-height: 1.2;
            flex-shrink: 0;
            margin-left: auto;
        }

        .sidebar-badge.badge-danger {
            background: #E53E3E;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(229, 62, 62, 0.35);
        }

        .sidebar-badge.badge-primary {
            background: #2B6CB0;
            color: #ffffff;
        }

        .sidebar-badge.badge-warning {
            background: #D69E2E;
            color: #ffffff;
        }

        /* User Profile Footer in Sidebar */
        .sidebar-user-footer {
            flex-shrink: 0;
            border-top: 1px solid var(--sidebar-border);
            padding: 0.85rem 1rem;
            background: rgba(0, 0, 0, 0.22);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: 0;
            text-decoration: none;
            color: inherit;
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #18365D 0%, #2B6CB0 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar-user-info {
            min-width: 0;
        }

        .sidebar-user-name {
            font-size: 0.84rem;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.72rem;
            color: #94a3b8;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-logout-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
            padding: 0;
        }

        .sidebar-logout-btn:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        /* Main Content Styling - Strict constraint prevents flex blowout */
        #page-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            max-width: calc(100% - var(--sidebar-width));
            min-width: 0;
            flex: 1 1 0;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.25s cubic-bezier(0.16, 1, 0.3, 1), width 0.25s cubic-bezier(0.16, 1, 0.3, 1), max-width 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Desktop / Zoom Collapsed Sidebar Mode */
        body.sidebar-collapsed {
            --sidebar-width: 0px;
        }
        body.sidebar-collapsed #sidebar-wrapper {
            transform: translateX(-100%);
        }
        body.sidebar-collapsed #page-content-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Medium screens / 125% zoom adaptation */
        @media (min-width: 992px) and (max-width: 1280px) {
            :root {
                --sidebar-width: 230px;
            }
            .sidebar-nav-item {
                padding: 0.48rem 0.65rem;
                font-size: 0.82rem;
            }
        }

        /* Responsive Sidebar & Mobile Overlays */
        @media (max-width: 991.98px) {
            :root {
                --sidebar-width: 260px;
            }

            #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
                transition: margin-left 0.28s cubic-bezier(0.16, 1, 0.3, 1);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
                z-index: 1050;
            }

            #sidebar-wrapper.toggled {
                margin-left: 0;
            }

            #page-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .admin-header {
                padding: 0.75rem 1.25rem !important;
            }

            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(15, 23, 42, 0.55);
                backdrop-filter: blur(2px);
                z-index: 1040;
                display: none;
                opacity: 0;
                transition: opacity 0.2s ease-in-out;
            }

            .sidebar-backdrop.show {
                display: block;
                opacity: 1;
            }
        }

        /* Top Header Styling */
        .admin-header {
            background-color: var(--color-surface);
            padding: 0.85rem 1.75rem;
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--color-border);
            height: 72px;
            position: sticky;
            top: 0;
            z-index: 990;
        }

        /* Sleek Navbar Toggle Button */
        .navbar-toggle-btn {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: var(--color-surface, #ffffff);
            border: 1px solid var(--color-border, #e5e7eb);
            color: var(--color-text, #1f2937);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            padding: 0;
            flex-shrink: 0;
        }

        .navbar-toggle-btn:hover {
            background: #f8fafc;
            color: var(--color-primary, #1e40af);
            border-color: #cbd5e1;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        .navbar-toggle-btn:active {
            transform: translateY(0);
        }

        .header-titles {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .header-page-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
            font-family: var(--font-heading);
            margin: 0;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .header-breadcrumb {
            font-size: 0.74rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .header-breadcrumb a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .header-breadcrumb a:hover {
            color: var(--color-primary);
        }

        .header-breadcrumb .breadcrumb-separator {
            font-size: 0.65rem;
            opacity: 0.6;
        }

        .header-breadcrumb .breadcrumb-current {
            color: var(--color-primary);
            font-weight: 600;
        }

        .user-dropdown { font-weight: 600; color: var(--color-text-light); }

        /* General Card & Table Styling */
        .card { border: none; box-shadow: var(--shadow-lg); border-radius: var(--radius-lg); }
        .card-header { background-color: var(--color-surface); border-bottom: 1px solid var(--color-border); padding: 1.5rem; font-weight: 700; color: var(--color-text); font-family: var(--font-heading); }
        .table > :not(caption) > * > * { padding: 1rem 1.5rem; }
        .table thead th { font-size: 0.75rem; font-weight: 700; color: var(--color-text-light); background-color: var(--color-bg-alt); border-bottom: none; text-transform: uppercase; letter-spacing: 0.5px; }
        .table tbody td { font-size: 0.9rem; color: var(--color-text); vertical-align: middle; }
        .badge { font-weight: 700; padding: 0.5em 0.75em; border-radius: 30px; }

        /* Headings use Outfit */
        h1, h2, h3, h4, h5, h6, .header-title { font-family: var(--font-heading); }

        /* ============================= */
        /* NOTIFICATION BELL             */
        /* ============================= */
        .notif-bell-btn {
            position: relative;
            width: 42px; height: 42px;
            border-radius: 50%;
            border: 1px solid var(--color-border, #e5e7eb);
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background 0.15s;
            color: #6b7280;
            text-decoration: none;
        }
        .notif-bell-btn:hover { background: #f3f4f6; color: #4f46e5; }

        .notif-badge {
            position: absolute;
            top: -3px; right: -3px;
            background: #ef4444;
            color: #fff;
            font-size: 10px; font-weight: 700;
            width: 18px; height: 18px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff;
            line-height: 1;
        }
        .notif-badge.d-none { display: none !important; }

        .notif-dropdown {
            position: absolute;
            top: calc(100% + 12px); right: 0;
            width: 360px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            z-index: 2000;
            display: none;
            overflow: hidden;
        }
        .notif-dropdown.open { display: block; }

        .notif-header {
            padding: 16px 20px 12px;
            border-bottom: 1px solid #f3f4f6;
            display: flex; align-items: center; justify-content: space-between;
        }
        .notif-header h6 { font-size: 14px; font-weight: 700; margin: 0; color: #111827; }

        .notif-list { max-height: 340px; overflow-y: auto; }

        .notif-item {
            padding: 14px 20px;
            border-bottom: 1px solid #f9fafb;
            display: flex; align-items: flex-start; gap: 12px;
            transition: background 0.1s;
            text-decoration: none;
            color: inherit;
        }
        .notif-item:hover { background: #f9fafb; }
        .notif-item.unread { background: #eef2ff; }
        .notif-item.unread:hover { background: #e0e7ff; }

        .notif-icon {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 14px;
        }
        .notif-icon.type-new_request    { background: #dbeafe; color: #2563eb; }
        .notif-icon.type-new_registration { background: #dcfce7; color: #16a34a; }
        .notif-icon.type-new_concern    { background: #fef3c7; color: #d97706; }
        .notif-icon.type-info           { background: #f3f4f6; color: #6b7280; }

        .notif-msg { font-size: 13px; color: #374151; line-height: 1.5; }
        .notif-time { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        .notif-footer {
            padding: 12px 20px;
            border-top: 1px solid #f3f4f6;
            text-align: center;
        }
        .notif-footer a { font-size: 13px; font-weight: 600; color: var(--color-primary); text-decoration: none; }
        .notif-footer a:hover { color: var(--color-primary-dark); }

        .notif-empty {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
        }
        .notif-empty i { font-size: 32px; display: block; margin-bottom: 10px; }
    </style>
</head>
<body>
    @php
        $currentRoute = Route::currentRouteName();
        $pageTitles = [
            'admin.dashboard' => 'Dashboard',
            'admin.requests' => 'Document Requests',
            'admin.document-types.index' => 'Document Management',
            'admin.documents.index' => 'Document Management',
            'admin.concerns' => 'Community Reports',
            'admin.users' => 'Residents & Users',
            'admin.approvals' => 'User Approvals',
            'admin.reports' => 'Analytics & Reports',
            'admin.activity-logs' => 'Activity Logs',
            'admin.settings' => 'System Settings',
            'admin.profile' => 'Admin Profile',
        ];
        $currentPageTitle = $pageTitles[$currentRoute] ?? 'Admin Portal';

        $pendingRequestsCount = \App\Models\DocumentRequest::whereIn('status', ['Pending', 'Under Review'])->count();
        $pendingApprovalsCount = \App\Models\User::whereIn('status', ['pending_approval', 'Pending', 'pending'])->where('role', 'Resident')->count();
        $pendingConcernsCount = \App\Models\CommunityConcern::where('status', 'Pending')->count();
        $barangayName = \App\Models\BarangaySetting::get('barangay_name', 'Barangay Kapitolyo');

        $nameParts = explode(' ', trim(auth()->user()->name ?? 'Admin'));
        $adminInitials = isset($nameParts[1]) 
            ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) 
            : strtoupper(substr($nameParts[0], 0, 2));
    @endphp

    <div id="wrapper">
        <div id="sidebar-wrapper">
            {{-- BRAND HEADING --}}
            <div class="sidebar-heading">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-wrapper">
                    <div class="sidebar-brand-icon">
                        <i class="fa-solid fa-circle-nodes"></i>
                    </div>
                    <div class="sidebar-brand-text">
                        <h4>{{ $barangayName }}</h4>
                        <p>Digital Services Portal</p>
                    </div>
                </a>
                {{-- Mobile-only close button (X). Desktop collapse is handled by the ≡ in the top bar --}}
                <button class="sidebar-header-toggle d-flex d-lg-none" id="sidebarCollapseBtn"
                        aria-label="Close Sidebar" title="Close Sidebar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- SCROLLABLE CATEGORIZED NAVIGATION --}}
            <div class="sidebar-nav-scroll">
                {{-- OVERVIEW CATEGORY --}}
                <div class="sidebar-category-label">Overview</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-th-large nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>

                {{-- RECORDS CATEGORY --}}
                <div class="sidebar-category-label">Records</div>
                <a href="{{ route('admin.users', ['role' => 'Resident']) }}" class="sidebar-nav-item {{ Route::is('admin.users') && request('role') === 'Resident' ? 'active' : '' }}">
                    <i class="fa-solid fa-users nav-icon"></i>
                    <span class="nav-text">Residents</span>
                </a>
                <a href="{{ route('admin.requests') }}" class="sidebar-nav-item {{ Route::is('admin.requests*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-lines nav-icon"></i>
                    <span class="nav-text">Document Requests</span>
                    @if($pendingRequestsCount > 0)
                        <span class="sidebar-badge badge-danger">{{ $pendingRequestsCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.document-types.index') }}" class="sidebar-nav-item {{ Route::is('admin.document-types.*') || Route::is('admin.documents.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-open nav-icon"></i>
                    <span class="nav-text">Document Management</span>
                </a>
                <a href="{{ route('admin.concerns') }}" class="sidebar-nav-item {{ Route::is('admin.concerns*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved nav-icon"></i>
                    <span class="nav-text">Community Reports</span>
                    @if($pendingConcernsCount > 0)
                        <span class="sidebar-badge badge-warning">{{ $pendingConcernsCount }}</span>
                    @endif
                </a>

                {{-- OPERATIONS CATEGORY --}}
                <div class="sidebar-category-label">Operations</div>
                <a href="{{ route('admin.approvals') }}" class="sidebar-nav-item {{ Route::is('admin.approvals*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-check nav-icon"></i>
                    <span class="nav-text">User Approvals</span>
                    @if($pendingApprovalsCount > 0)
                        <span class="sidebar-badge badge-warning">{{ $pendingApprovalsCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.reports') }}" class="sidebar-nav-item {{ Route::is('admin.reports*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-column nav-icon"></i>
                    <span class="nav-text">Reports</span>
                </a>
                <a href="{{ route('admin.activity-logs') }}" class="sidebar-nav-item {{ Route::is('admin.activity-logs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left nav-icon"></i>
                    <span class="nav-text">Activity Logs</span>
                </a>

                {{-- SYSTEM CATEGORY --}}
                <div class="sidebar-category-label">System</div>
                <a href="{{ route('admin.users') }}" class="sidebar-nav-item {{ Route::is('admin.users') && request('role') !== 'Resident' ? 'active' : '' }}">
                    <i class="fa-solid fa-user-gear nav-icon"></i>
                    <span class="nav-text">User Management</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="sidebar-nav-item {{ Route::is('admin.settings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-sliders nav-icon"></i>
                    <span class="nav-text">System Settings</span>
                </a>

            </div>

        </div>

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg admin-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="navbar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar" title="Toggle Sidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="header-titles">
                        <h5 class="header-page-title">
                            {{ View::hasSection('page_title') ? $__env->yieldContent('page_title') : $currentPageTitle }}
                        </h5>
                        <div class="header-breadcrumb">
                            <a href="{{ route('admin.dashboard') }}">E-Barangay</a>
                            <span class="breadcrumb-separator">›</span>
                            <span class="breadcrumb-current">{{ View::hasSection('page_title') ? $__env->yieldContent('page_title') : $currentPageTitle }}</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">

                    {{-- ===== NOTIFICATION BELL ===== --}}
                    <div class="position-relative" id="notif-wrapper">
                        <button class="notif-bell-btn" id="notif-bell" aria-label="Notifications" title="Notifications">
                            <i class="fa-solid fa-bell" style="font-size:16px;"></i>
                            <span class="notif-badge d-none" id="notif-badge">0</span>
                        </button>

                        <div class="notif-dropdown" id="notif-dropdown">
                            <div class="notif-header">
                                <h6><i class="fa-solid fa-bell me-2 text-primary" style="font-size:13px;"></i> Notifications</h6>
                                <button class="btn btn-link btn-sm text-muted text-decoration-none p-0" id="mark-all-read" style="font-size:12px;">
                                    Mark all as read
                                </button>
                            </div>
                            <div class="notif-list" id="notif-list">
                                <div class="notif-empty">
                                    <i class="fa-regular fa-bell-slash text-light"></i>
                                    You're all caught up!
                                </div>
                            </div>
                            <div class="notif-footer">
                                <a href="{{ route('admin.activity-logs') }}">View Activity Logs →</a>
                            </div>
                        </div>
                    </div>
                    {{-- ===== END NOTIFICATION BELL ===== --}}

                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-3 text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-end d-none d-md-block">
                                <div class="fw-bold text-dark" style="font-size: 14px; line-height: 1.2;">{{ auth()->user()->name }}</div>
                                <div class="text-muted" style="font-size: 12px;">Admin</div>
                            </div>
                            @php
                                $nameParts = explode(' ', trim(auth()->user()->name));
                                $initials = isset($nameParts[1]) 
                                    ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) 
                                    : strtoupper(substr($nameParts[0], 0, 2));
                            @endphp

                            @if(auth()->user()->profile_picture)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->profile_picture) }}" 
                                     alt="{{ auth()->user()->name }}" 
                                     class="rounded-circle shadow-sm object-fit-cover" 
                                     style="width:45px;height:45px;">
                            @else
                                <div class="d-flex justify-content-center align-items-center fw-bold rounded-circle shadow-sm"
                                     style="width:45px;height:45px;font-size:16px;background:linear-gradient(135deg,#EBF4FF,#BEE3F8);color:#18365D;">
                                    {{ $initials }}
                                </div>
                            @endif
                        </a>
                        
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-4 mt-3 px-2 py-2" style="min-width: 220px;">
                            <li><h6 class="dropdown-header text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 1px;">My Account</h6></li>
                            <li>
                                <a class="dropdown-item rounded-3 d-flex align-items-center gap-3 py-2 fw-semibold" href="{{ route('admin.profile') }}">
                                    <i class="fa-regular fa-user text-primary" style="width: 20px;"></i> Profile Settings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 d-flex align-items-center gap-3 py-2 fw-semibold" href="{{ route('admin.settings') }}">
                                    <i class="fa-solid fa-cog text-secondary" style="width: 20px;"></i> Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <form action="{{ route('admin.logout') }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded-3 d-flex align-items-center gap-3 py-2 fw-semibold text-danger bg-transparent border-0 w-100 text-start">
                                        <i class="fa-solid fa-arrow-right-from-bracket" style="width: 20px;"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            @yield('content')

            <footer class="text-center py-4 text-muted">
                <small>&copy; 2026 E-Barangay System</small>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // =============================================
    // ADMIN NOTIFICATION BELL SYSTEM
    // =============================================
    (function () {
        const bell         = document.getElementById('notif-bell');
        const dropdown     = document.getElementById('notif-dropdown');
        const badge        = document.getElementById('notif-badge');
        const list         = document.getElementById('notif-list');
        const markAllBtn   = document.getElementById('mark-all-read');
        const csrfToken    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const typeIcons = {
            'new_request':      'fa-file-lines',
            'new_registration': 'fa-user-plus',
            'new_concern':      'fa-bullhorn',
            'info':             'fa-info',
        };

        function renderNotifications(data) {
            // Badge
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }

            // List
            if (!data.notifications || data.notifications.length === 0) {
                list.innerHTML = `
                    <div class="notif-empty">
                        <i class="fa-regular fa-bell-slash text-light"></i>
                        You're all caught up!
                    </div>`;
                return;
            }

            list.innerHTML = data.notifications.map(n => {
                const icon = typeIcons[n.type] || 'fa-bell';
                return `
                <a href="${n.link}" class="notif-item ${n.read ? '' : 'unread'}" data-id="${n.id}" onclick="markOneRead(event, '${n.id}', '${n.link}')">
                    <div class="notif-icon type-${n.type}">
                        <i class="fa-solid ${icon}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="notif-msg">${n.message}</div>
                        <div class="notif-time">${n.created_at}</div>
                    </div>
                    ${!n.read ? '<div class="flex-shrink-0"><span style="width:8px;height:8px;background:#4f46e5;border-radius:50%;display:inline-block;margin-top:4px;"></span></div>' : ''}
                </a>`;
            }).join('');
        }

        function fetchNotifications() {
            fetch('{{ route("admin.notifications.get") }}')
                .then(r => r.json())
                .then(renderNotifications)
                .catch(() => {});
        }

        window.markOneRead = function(e, id, link) {
            e.preventDefault();
            fetch('{{ route("admin.notifications.read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ notification_id: id }),
            }).then(() => {
                window.location.href = link;
            });
        };

        markAllBtn.addEventListener('click', function() {
            fetch('{{ route("admin.notifications.read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({}),
            }).then(() => {
                fetchNotifications();
            });
        });

        // Toggle dropdown
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('open');
            if (dropdown.classList.contains('open')) {
                fetchNotifications();
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!document.getElementById('notif-wrapper').contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        // Initial fetch + poll every 30 seconds
        fetchNotifications();
        setInterval(fetchNotifications, 30000);
    })();

    // Responsive Sidebar Toggle (Mobile Drawer & Desktop Collapse)
    (function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
        const sidebar = document.getElementById('sidebar-wrapper');
        const backdrop = document.getElementById('sidebarBackdrop');

        // Restore saved desktop collapse state
        if (window.innerWidth >= 992 && localStorage.getItem('adminSidebarCollapsed') === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }

        function toggleSidebar() {
            if (window.innerWidth < 992) {
                // Mobile behavior
                if (sidebar && sidebar.classList.contains('toggled')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
                // Desktop / zoom behavior
                document.body.classList.toggle('sidebar-collapsed');
                const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                localStorage.setItem('adminSidebarCollapsed', isCollapsed ? 'true' : 'false');
            }
        }

        function openMobileSidebar() {
            if (sidebar) sidebar.classList.add('toggled');
            if (backdrop) backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            if (sidebar) sidebar.classList.remove('toggled');
            if (backdrop) backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }

        if (sidebarCollapseBtn) {
            sidebarCollapseBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }

        if (backdrop) backdrop.addEventListener('click', closeMobileSidebar);

        if (sidebar) {
            sidebar.querySelectorAll('.sidebar-nav-item').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 992) closeMobileSidebar();
                });
            });
        }
    })();
    </script>
</body>
</html>