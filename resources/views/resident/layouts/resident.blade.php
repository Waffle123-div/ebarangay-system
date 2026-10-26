<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Resident Portal') – E-Barangay</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    
    <style>
        /* ─── Resident Sidebar Variables — matches Admin dark theme ─── */
        :root {
            --r-sidebar-width:    260px;
            --r-sidebar-bg:       #0F2240;
            --r-sidebar-bg-alt:   #0A1829;
            --r-sidebar-border:   rgba(255, 255, 255, 0.07);
            --r-sidebar-text:     #A0B5CC;
            --r-sidebar-text-hover: #ffffff;
            --r-sidebar-category: #5A7A9A;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--color-bg, #f8fafb);
            overflow-x: hidden;
        }

        /* ─── Wrapper ─── */
        #resident-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            overflow-x: clip;
            position: relative;
        }

        /* ─── Sidebar ─── */
        #resident-sidebar-wrapper {
            min-height: 100vh;
            height: 100%;
            width: var(--r-sidebar-width);
            background: var(--r-sidebar-bg);
            border-right: none; 
            box-shadow: none; 
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                        margin-left 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        /* ─── Sidebar Brand Header ─── */
        .r-sidebar-heading {
            padding: 0 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--r-sidebar-border);
            background: rgba(0, 0, 0, 0.18);
            flex-shrink: 0;
            height: 84px;
            transition: padding 0.25s ease;
        }

        .r-sidebar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            min-width: 0;
        }

        .r-sidebar-brand-icon {
            width: 56px; height: 56px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            background: transparent;
            transition: width 0.25s ease, height 0.25s ease;
        }

        .r-sidebar-brand-text h4 {
            font-size: 1.05rem; font-weight: 700;
            margin: 0; color: #ffffff;
            font-family: var(--font-heading);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            line-height: 1.25;
        }

        .r-sidebar-brand-text p {
            font-size: 0.76rem; margin: 0;
            color: #4FD1C5; font-weight: 500;
            letter-spacing: 0.02em;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        /* ─── Toggle Button Inside Sidebar ─── */
        .r-sidebar-header-toggle {
            width: 38px; height: 38px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0; padding: 0;
        }
        .r-sidebar-header-toggle:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #4FD1C5;
            transform: scale(1.05);
        }

        /* ─── Scrollable Nav ─── */
        .r-sidebar-nav-scroll {
            flex: 1 1 auto;
            overflow-y: auto; overflow-x: hidden;
            padding: 0.65rem 0 0.85rem 0.65rem; 
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.12) transparent;
            transition: padding 0.25s ease;
        }
        .r-sidebar-nav-scroll::-webkit-scrollbar { width: 4px; }
        .r-sidebar-nav-scroll::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.12); border-radius: 4px;
        }

        /* ─── Category Labels ─── */
        .r-sidebar-category {
            font-size: 0.68rem; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
            color: var(--r-sidebar-category);
            padding: 0.85rem 0.75rem 0.35rem 0.6rem;
            margin-top: 0.2rem;
            display: flex; align-items: center;
            user-select: none;
        }
        .r-sidebar-category:first-child { margin-top: 0; padding-top: 0.25rem; }

        /* ─── Nav Item (Inverse Curve Base) ─── */
        .r-sidebar-nav-item {
            display: flex; align-items: center;
            padding: 0.75rem 1rem 0.75rem 1.25rem;
            margin-bottom: 4px;
            color: var(--r-sidebar-text);
            font-weight: 500; font-size: 0.86rem;
            border-radius: 30px 0 0 30px; 
            text-decoration: none;
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .r-sidebar-nav-item i.r-nav-icon {
            width: 22px; font-size: 0.95rem;
            margin-right: 0.65rem; text-align: center;
            color: #5A7A9A;
            transition: color 0.15s ease, transform 0.15s ease, margin 0.15s ease;
            flex-shrink: 0;
            z-index: 2;
        }

        .r-sidebar-nav-item .r-nav-text {
            flex: 1 1 auto;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            z-index: 2;
        }

        .r-sidebar-nav-item:hover {
            background-color: rgba(255, 255, 255, 0.07);
            color: #ffffff;
        }
        .r-sidebar-nav-item:hover i.r-nav-icon {
            color: #4FD1C5;
            transform: translateX(1px);
        }

        /* ─── Active Nav Item (The Cutout Effect) ─── */
        .r-sidebar-nav-item.active {
            background-color: var(--color-bg, #f8fafb); 
            color: #18365D; 
            font-weight: 700;
            box-shadow: none; 
            margin-right: -1px; 
            padding-right: calc(1rem + 1px);
        }
        .r-sidebar-nav-item.active i.r-nav-icon { 
            color: #2B6CB0; 
        }

        .r-sidebar-nav-item.active::before,
        .r-sidebar-nav-item.active::after {
            content: "";
            position: absolute;
            right: 0;
            width: 30px;
            height: 30px;
            background-color: transparent;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }
        
        .r-sidebar-nav-item.active::before {
            top: -30px;
            box-shadow: 15px 15px 0 var(--color-bg, #f8fafb); 
        }

        .r-sidebar-nav-item.active::after {
            bottom: -30px;
            box-shadow: 15px -15px 0 var(--color-bg, #f8fafb);
        }

        /* ─── User Footer ─── */
        .r-sidebar-user-footer {
            flex-shrink: 0;
            border-top: 1px solid var(--r-sidebar-border);
            padding: 0.85rem 1rem;
            background: rgba(0, 0, 0, 0.22);
            display: flex; align-items: center;
            justify-content: space-between; gap: 0.65rem;
            transition: padding 0.25s ease;
        }

        .r-sidebar-user-card {
            display: flex; align-items: center; gap: 0.65rem;
            flex: 1 1 0;
            min-width: 0;
            overflow: hidden;
        }

        .r-sidebar-user-info {
            min-width: 0;
            flex: 1 1 auto;
            overflow: hidden;
        }

        .r-sidebar-user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #18365D 0%, #2B6CB0 100%);
            color: #ffffff; font-weight: 700; font-size: 0.85rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.15);
        }

        .r-sidebar-user-name {
            font-size: 0.82rem; font-weight: 600;
            color: #ffffff; line-height: 1.25;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .r-sidebar-user-role {
            font-size: 0.72rem; color: #A0B5CC; line-height: 1.2;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .r-sidebar-logout-btn {
            width: 32px; height: 32px; border-radius: 8px;
            background: transparent; border: none;
            color: #A0B5CC;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.15s ease;
            flex-shrink: 0; padding: 0;
        }
        .r-sidebar-logout-btn:hover {
            background: rgba(229, 62, 62, 0.15);
            color: #FC8181;
        }

        /* ─── Content Wrapper ─── */
        #resident-content-wrapper {
            margin-left: var(--r-sidebar-width);
            width: calc(100% - var(--r-sidebar-width));
            max-width: calc(100% - var(--r-sidebar-width));
            min-width: 0;
            flex: 1 1 0;
            display: flex; flex-direction: column;
            transition: margin-left 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                        width 0.25s cubic-bezier(0.16, 1, 0.3, 1),
                        max-width 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: inset 4px 0 16px rgba(0, 0, 0, 0.02);
        }

        /* ─── Desktop Collapsed State (Icon-Only Mini Sidebar) ─── */
        @media (min-width: 992px) {
            body.r-sidebar-collapsed { 
                --r-sidebar-width: 78px; 
            }
            body.r-sidebar-collapsed #resident-sidebar-wrapper { 
                transform: none !important; 
                width: 78px; 
            }
            body.r-sidebar-collapsed #resident-content-wrapper {
                margin-left: 78px !important;
                width: calc(100% - 78px) !important;
                max-width: calc(100% - 78px) !important;
            }

            /* Header Mini State: Hide Brand completely & Center Burger Button */
            body.r-sidebar-collapsed .r-sidebar-heading {
                padding: 0 !important;
                justify-content: center !important;
            }
            body.r-sidebar-collapsed .r-sidebar-brand-wrapper {
                display: none !important;
            }

            /* Hide category labels */
            body.r-sidebar-collapsed .r-sidebar-category {
                display: none !important;
            }

            /* Mini Nav Items */
            body.r-sidebar-collapsed .r-sidebar-nav-scroll {
                padding: 0.85rem 0.4rem;
            }
            body.r-sidebar-collapsed .r-sidebar-nav-item {
                justify-content: center;
                padding: 0.8rem 0;
                margin-right: 0;
                border-radius: 14px;
            }
            body.r-sidebar-collapsed .r-sidebar-nav-item i.r-nav-icon {
                margin-right: 0 !important;
                font-size: 1.15rem;
            }
            body.r-sidebar-collapsed .r-sidebar-nav-item .r-nav-text {
                display: none !important;
            }

            /* Active State Overlay Adjustment for Mini Mode */
            body.r-sidebar-collapsed .r-sidebar-nav-item.active::before,
            body.r-sidebar-collapsed .r-sidebar-nav-item.active::after {
                display: none !important;
            }
            body.r-sidebar-collapsed .r-sidebar-nav-item.active {
                background-color: rgba(255, 255, 255, 0.15);
                color: #ffffff;
                margin-right: 0;
                padding-right: 0;
            }
            body.r-sidebar-collapsed .r-sidebar-nav-item.active i.r-nav-icon {
                color: #4FD1C5;
            }

            /* Mini User Footer */
            body.r-sidebar-collapsed .r-sidebar-user-footer {
                padding: 0.85rem 0.4rem;
                justify-content: center;
            }
            body.r-sidebar-collapsed .r-sidebar-user-info,
            body.r-sidebar-collapsed .r-sidebar-logout-btn {
                display: none !important;
            }
        }

        /* ─── Medium screens ─── */
        @media (min-width: 992px) and (max-width: 1280px) {
            :root { --r-sidebar-width: 230px; }
            .r-sidebar-nav-item { padding: 0.65rem 0.65rem 0.65rem 1rem; font-size: 0.82rem; }
        }

        /* ─── Mobile Drawer ─── */
        @media (max-width: 991.98px) {
            :root { --r-sidebar-width: 260px; }

            #resident-sidebar-wrapper {
                margin-left: calc(-1 * var(--r-sidebar-width));
                transition: margin-left 0.28s cubic-bezier(0.16, 1, 0.3, 1);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.35);
                z-index: 1050;
            }
            #resident-sidebar-wrapper.toggled { margin-left: 0; }

            #resident-content-wrapper {
                margin-left: 0 !important; width: 100% !important; max-width: 100% !important;
                box-shadow: none;
            }

            .r-sidebar-backdrop {
                position: fixed; top: 0; left: 0;
                width: 100vw; height: 100vh;
                background: rgba(15, 23, 42, 0.55);
                backdrop-filter: blur(2px);
                z-index: 1040;
                display: none; opacity: 0;
                transition: opacity 0.2s ease-in-out;
            }
            .r-sidebar-backdrop.show { display: block; opacity: 1; }
        }

        /* ─── Top Header ─── */
        .resident-header {
            background-color: var(--color-surface, #ffffff);
            padding: 0 1.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            border-bottom: 1px solid var(--color-border);
            height: 84px;
            position: sticky; top: 0; z-index: 990;
        }

        @media (max-width: 991.98px) {
            .resident-header { padding: 0 1.25rem !important; }
        }

        /* Mobile navbar trigger */
        .r-navbar-toggle-btn {
            width: 38px; height: 38px;
            border-radius: 9px;
            background: var(--color-surface, #ffffff);
            border: 1px solid var(--color-border, #E2E8F0);
            color: var(--color-text, #1A202C);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 0; flex-shrink: 0;
        }

        .r-header-page-title {
            font-size: 1.05rem; font-weight: 700;
            color: #1A202C; font-family: var(--font-heading);
            margin: 0; line-height: 1.25;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .r-header-breadcrumb {
            font-size: 0.74rem; color: #718096;
            display: flex; align-items: center; gap: 0.35rem;
            margin-top: 2px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .r-header-breadcrumb a { color: #718096; text-decoration: none; transition: color 0.15s; }
        .r-header-breadcrumb a:hover { color: var(--color-primary); }
        .r-header-breadcrumb .bc-sep { font-size: 0.65rem; opacity: 0.6; }
        .r-header-breadcrumb .bc-cur { color: var(--color-primary); font-weight: 600; }

        /* ─── Notification Bell ─── */
        .r-notif-bell-btn {
            position: relative; width: 42px; height: 42px;
            border-radius: 50%;
            border: 1px solid var(--color-border, #E2E8F0);
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background 0.15s;
            color: #718096; text-decoration: none;
        }
        .r-notif-bell-btn:hover { background: #F7FAFC; color: var(--color-primary); }

        .r-notif-badge {
            position: absolute; top: -3px; right: -3px;
            background: #E53E3E; color: #fff;
            font-size: 10px; font-weight: 700;
            width: 18px; height: 18px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff; line-height: 1;
        }

        /* ─── Main Content ─── */
        .main-content { padding: 20px; flex-grow: 1; }

        /* ─── Headings ─── */
        h1,h2,h3,h4,h5,h6,
        .header-title { font-family: var(--font-heading); }
        .header-title { font-weight: 700; color: var(--color-text); }
        .header-subtitle { color: var(--color-text-light); font-size: 14px; }

        .card-custom { border: 0; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); }
    </style>
</head>
<body>
    {{-- Synchronous state check executed before first paint to prevent layout flash --}}
    <script>
        if (window.innerWidth >= 992 && localStorage.getItem('residentSidebarCollapsed') === 'true') {
            document.body.classList.add('r-sidebar-collapsed');
        }
    </script>

@php
$currentRoute = Route::currentRouteName();
$pageTitles = [
'welcome' => 'Welcome',
'resident.dashboard' => 'Dashboard',
'resident.request.create' => 'Request Document',
'resident.request.track' => 'Track Requests',
'resident.concerns' => 'Community Concerns',
'resident.activity-logs' => 'Activity Logs',
'resident.settings' => 'Account Settings',
];
$currentPageTitle = $pageTitles[$currentRoute] ?? 'Resident Portal';
$residentName = auth()->user()?->name ?? 'Resident';
$nameParts = explode(' ', trim($residentName));
$residentInitials = isset($nameParts[1]) ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($nameParts[0], 0, 2));
@endphp

    <div id="resident-wrapper">

        {{-- ═══════════════════════════════════════════ --}}
        {{-- RESIDENT SIDEBAR                           --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div id="resident-sidebar-wrapper">

            {{-- Brand Heading --}}
            <div class="r-sidebar-heading">
                <a href="{{ route('resident.dashboard') }}" class="r-sidebar-brand-wrapper">
                    <div class="r-sidebar-brand-icon">
                        <img src="{{ asset('images/logo.png') }}" alt="E-Barangay Logo" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div class="r-sidebar-brand-text">
                        <h4>E-Barangay</h4>
                        <p>Resident Portal</p>
                    </div>
                </a>
                
                {{-- Toggle Sidebar Button Inside Sidebar --}}
                <button class="r-sidebar-header-toggle" id="rSidebarToggleBtn" aria-label="Toggle Sidebar" title="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            {{-- Scrollable Navigation --}}
            <div class="r-sidebar-nav-scroll">

                {{-- PORTAL --}}
                <div class="r-sidebar-category">Portal</div>
                <a href="{{ route('resident.dashboard') }}"
                   class="r-sidebar-nav-item {{ Route::is('resident.dashboard') ? 'active' : '' }}" title="Dashboard">
                    <i class="fa-solid fa-th-large r-nav-icon"></i>
                    <span class="r-nav-text">Dashboard</span>
                </a>

                {{-- SERVICES --}}
                <div class="r-sidebar-category">Services</div>
                <a href="{{ route('resident.request.create') }}"
                   class="r-sidebar-nav-item {{ Route::is('resident.request.create') ? 'active' : '' }}" title="Request Document">
                    <i class="fa-solid fa-file-circle-plus r-nav-icon"></i>
                    <span class="r-nav-text">Request Document</span>
                </a>
                <a href="{{ route('resident.request.track') }}"
                   class="r-sidebar-nav-item {{ Route::is('resident.request.track') ? 'active' : '' }}" title="Track Requests">
                    <i class="fa-solid fa-magnifying-glass r-nav-icon"></i>
                    <span class="r-nav-text">Track Requests</span>
                </a>
                <a href="{{ route('resident.concerns') }}"
                   class="r-sidebar-nav-item {{ Route::is('resident.concerns') ? 'active' : '' }}" title="Community Concern">
                    <i class="fa-solid fa-bullhorn r-nav-icon"></i>
                    <span class="r-nav-text">Community Concern</span>
                </a>

                {{-- ACCOUNT --}}
                <div class="r-sidebar-category">Account</div>
                <a href="{{ route('resident.activity-logs') }}"
                   class="r-sidebar-nav-item {{ Route::is('resident.activity-logs') ? 'active' : '' }}" title="Activity Logs">
                    <i class="fa-solid fa-clock-rotate-left r-nav-icon"></i>
                    <span class="r-nav-text">Activity Logs</span>
                </a>
                <a href="{{ route('resident.settings') }}"
                   class="r-sidebar-nav-item {{ Route::is('resident.settings') ? 'active' : '' }}" title="Settings">
                    <i class="fa-solid fa-sliders r-nav-icon"></i>
                    <span class="r-nav-text">Settings</span>
                </a>

            </div>

            {{-- User Footer --}}
            <div class="r-sidebar-user-footer">
                <div class="r-sidebar-user-card">
                    <div class="r-sidebar-user-avatar">{{ $residentInitials }}</div>
                    <div class="r-sidebar-user-info">
                        <div class="r-sidebar-user-name">{{ $residentName }}</div>
                        <div class="r-sidebar-user-role">Resident</div>
                    </div>
                </div>
                <form action="{{ route('resident.logout') }}" method="POST" class="m-0 flex-shrink-0">
                    @csrf
                    <button type="submit" class="r-sidebar-logout-btn" title="Logout" aria-label="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>

        </div>

        {{-- Mobile Backdrop --}}
        <div class="r-sidebar-backdrop" id="rSidebarBackdrop"></div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- MAIN CONTENT WRAPPER                       --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div id="resident-content-wrapper">

            {{-- Sticky Top Header --}}
            <nav class="navbar resident-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    {{-- Mobile-only drawer trigger --}}
                    <button class="r-navbar-toggle-btn d-lg-none" id="rMobileNavToggle" aria-label="Open Navigation" title="Open Navigation">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div>
                        <h5 class="r-header-page-title">
                            {{ View::hasSection('page_title') ? $__env->yieldContent('page_title') : $currentPageTitle }}
                        </h5>
                        <div class="r-header-breadcrumb">
                            <a href="{{ route('resident.dashboard') }}">E-Barangay</a>
                            <span class="bc-sep">›</span>
                            <span class="bc-cur">{{ View::hasSection('page_title') ? $__env->yieldContent('page_title') : $currentPageTitle }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">

                    {{-- Notification Bell --}}
                    <div class="dropdown">
                        <a class="r-notif-bell-btn text-decoration-none"
                           href="#" id="rNotifDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false"
                           onclick="markResidentNotificationsRead()">
                            <i class="fa-solid fa-bell" style="font-size:16px;"></i>
                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                <span class="r-notif-badge" id="r-notif-badge">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow p-0 mt-2"
                            style="width:330px; border-radius:14px; overflow:hidden;">
                            <div style="background: linear-gradient(135deg, #0F2240, #2B6CB0); padding:14px 18px;"
                                 class="d-flex align-items-center justify-content-between">
                                <span class="text-white fw-bold" style="font-size:14px;">
                                    <i class="fa-solid fa-bell me-2"></i>Notifications
                                </span>
                            </div>
                            <div style="max-height:300px; overflow-y:auto;">
                                @if(auth()->check())
@php
$userNotifs = auth()->user()->notifications ? auth()->user()->notifications->values() : collect();
@endphp
                                    @if($userNotifs->count() > 0)
                                        @for($n = 0; $n < $userNotifs->count(); $n++)
@php
$notification = $userNotifs[$n];
@endphp
                                            <div class="px-3 py-3 border-bottom {{ $notification->read_at === null ? '' : 'bg-white' }}"
                                                 style="{{ $notification->read_at === null ? 'background:#EBF4FF;' : '' }}">
                                                <div class="fw-bold text-dark" style="font-size:14px;">
                                                    <i class="fa-solid fa-circle-check text-success me-1"></i>
                                                    {{ $notification->data['document_type'] ?? 'Document Update' }}
                                                </div>
                                                <div class="text-muted mt-1" style="font-size:13px;">
                                                    {{ $notification->data['message'] ?? 'The status of your document has been updated.' }}
                                                </div>
                                                <div class="mt-1" style="font-size:11px; color:#2B6CB0; font-weight:600;">
                                                    TRK: {{ $notification->data['tracking_number'] ?? 'N/A' }} • {{ $notification->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        @endfor
                                    @else
                                        <div class="px-3 py-4 text-center text-muted small">
                                            <i class="fa-regular fa-bell-slash fa-2x mb-2 d-block" style="color:#CBD5E0;"></i>
                                            No new notifications
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </ul>
                    </div>

                </div>
            </nav>

            {{-- Page Content --}}
            <div class="main-content">
                @yield('content')
            </div>

            <footer class="text-center py-4 text-muted">
                <small>&copy; 2026 E-Barangay System</small>
            </footer>

        </div>
    </div>

    {{-- ═══ SESSION LOCK SCREEN ═══ --}}
    @if(session('is_locked'))
        <div class="modal d-flex align-items-center justify-content-center"
             style="background:rgba(15,23,42,0.78);backdrop-filter:blur(10px);position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:10000;"
             tabindex="-1">
            <div class="modal-dialog m-0" style="max-width:420px;width:100%;padding:20px;">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div style="height:5px;background:linear-gradient(90deg,#18365D,#2B6CB0,#319795);width:100%;"></div>
                    <div class="modal-body p-5 text-center bg-white">
                        <div class="d-flex justify-content-center mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:76px;height:76px;background:#EBF4FF;border:4px solid #fff;box-shadow:0 0 0 4px rgba(24,54,93,0.15);">
                                <i class="fa-solid fa-lock" style="font-size:28px;color:#18365D;"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark mb-2" style="letter-spacing:-0.5px;">Session Locked</h4>
                        <p class="text-muted mb-4 px-2" style="font-size:14px;line-height:1.5;">
                            Your session timed out due to inactivity. Please enter your 6-digit PIN to resume.
                        </p>
                        @error('security_pin')
                            <div class="alert border-0 small py-2 px-3 rounded-3 mb-4 fw-semibold d-flex align-items-center justify-content-center gap-2"
                                 style="background:#FFF5F5;color:#9B2C2C;font-size:13px;">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </div>
                        @enderror
                        <form action="{{ route('resident.session.unlock') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <input type="password" name="security_pin"
                                       class="form-control text-center fw-bold"
                                       maxlength="6" inputmode="numeric" pattern="[0-9]*"
                                       placeholder="••••••"
                                       style="letter-spacing:16px;font-size:1.75rem;background:#F7FAFC;border:2px solid #E2E8F0;border-radius:12px;height:64px;color:#18365D;transition:all 0.2s;"
                                       required autofocus
                                       onfocus="this.style.borderColor='#2B6CB0';this.style.background='#fff';this.style.boxShadow='0 0 0 4px rgba(43,108,176,0.15)';"
                                       onblur="this.style.borderColor='#E2E8F0';this.style.background='#F7FAFC';this.style.boxShadow='none';">
                            </div>
                            <button type="submit" class="btn w-100 py-3 rounded-3 fw-bold mb-3 text-white"
                                    style="font-size:15px;background:linear-gradient(90deg,#18365D,#2B6CB0);box-shadow:0 4px 12px rgba(24,54,93,0.25);">
                                <i class="fa-solid fa-unlock-keyhole me-2"></i> Unlock Session
                            </button>
                        </form>
                        <form action="{{ route('resident.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light w-100 py-2 rounded-3 fw-bold text-muted"
                                    style="border:1.5px solid #E2E8F0;font-size:14px;">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>document.body.style.overflow = 'hidden';</script>
    @else
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const timeoutThreshold = 600000;
                let lockTimer;
                let lockRequestSent = false;
                let lastActivity = 0;

                function lockSession() {
                    if (lockRequestSent) return;
                    lockRequestSent = true;
                    fetch("{{ route('resident.session.lock') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }).then(response => {
                        if (response.ok) window.location.reload();
                    }).catch(() => {
                        lockRequestSent = false;
                        resetIdleTimer();
                    });
                }

                function resetIdleTimer() {
                    const now = Date.now();
                    if (now - lastActivity < 1000) return;
                    lastActivity = now;
                    clearTimeout(lockTimer);
                    lockTimer = setTimeout(lockSession, timeoutThreshold);
                }

                ['mousemove','click','keydown','scroll','touchstart'].forEach(ev => {
                    document.addEventListener(ev, resetIdleTimer, { passive: ev === 'scroll' || ev === 'touchstart' });
                });
                resetIdleTimer();
            });
        </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function markResidentNotificationsRead() {
            const badge = document.getElementById('r-notif-badge');
            if (badge) badge.style.display = 'none';
            fetch('{{ route("resident.notifications.read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).catch(() => {});
        }
    </script>

    {{-- ═══ RESIDENT SIDEBAR TOGGLE JS ═══ --}}
    <script>
    (function() {
        const sidebarToggleBtn = document.getElementById('rSidebarToggleBtn');
        const mobileNavToggle  = document.getElementById('rMobileNavToggle');
        const sidebar          = document.getElementById('resident-sidebar-wrapper');
        const backdrop         = document.getElementById('rSidebarBackdrop');

        function toggleDesktopSidebar() {
            document.body.classList.toggle('r-sidebar-collapsed');
            const collapsed = document.body.classList.contains('r-sidebar-collapsed');
            localStorage.setItem('residentSidebarCollapsed', collapsed ? 'true' : 'false');
        }

        function openMobile() {
            if (sidebar) sidebar.classList.add('toggled');
            if (backdrop) backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeMobile() {
            if (sidebar) sidebar.classList.remove('toggled');
            if (backdrop) backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        sidebarToggleBtn && sidebarToggleBtn.addEventListener('click', e => {
            e.stopPropagation();
            if (window.innerWidth < 992) {
                sidebar && sidebar.classList.contains('toggled') ? closeMobile() : openMobile();
            } else {
                toggleDesktopSidebar();
            }
        });

        mobileNavToggle && mobileNavToggle.addEventListener('click', e => {
            e.stopPropagation();
            openMobile();
        });

        backdrop && backdrop.addEventListener('click', closeMobile);

        // Close mobile drawer on nav link click
        sidebar && sidebar.querySelectorAll('.r-sidebar-nav-item').forEach(link => {
            link.addEventListener('click', () => { if (window.innerWidth < 992) closeMobile(); });
        });
    })();
    </script>

    {{-- ═══ AI CHATBOT ═══ --}}
    @if(!session('is_locked'))
    <div id="ai-chat-container">
        <div id="ai-chat-window">
            <div class="chat-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white rounded-circle d-flex justify-content-center align-items-center p-1 shadow-sm" style="width:55px;height:55px;">
                        <img src="{{ asset('images/fisherman.png') }}" alt="Istong" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    <div class="header-titles">
                        <h5 class="m-0 fw-bold">Istong</h5>
                        <small style="opacity:0.9;">Barangay Fisherman Assistant</small>
                    </div>
                </div>
                <button id="close-chat-btn">&times;</button>
            </div>
            <div class="chat-disclaimer">
                <small><i class="fa-solid fa-circle-info me-1"></i> Information only. No official documents issued here.</small>
            </div>
            <div id="chat-body">
                <div class="message bot-message">
                    Kumusta! Ako ang inyong Fisherman Assistant nga si Istong. Unsa may matabang nako nimo karon kabahin sa atong barangay?
                </div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="chat-input" placeholder="Pangutana dinhi..." autocomplete="off">
                <button id="send-chat-btn"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
        <button id="ai-toggle-btn">
            <div class="ai-badge"><i class="fa-solid fa-robot"></i> AI Chat</div>
            <img src="{{ asset('images/fisherman.png') }}" alt="Fisherman Mascot" class="mascot-img">
        </button>
    </div>

    <style>
        #ai-chat-container {
            position:fixed;bottom:25px;right:25px;z-index:9999;
            font-family:var(--font-body);
            display:flex;flex-direction:column;align-items:flex-end;
        }
        #ai-toggle-btn {
            background:transparent;border:none;border-radius:0;
            width:110px;height:110px;cursor:grab;box-shadow:none;
            transition:transform 0.1s;
            display:flex;align-items:center;justify-content:center;
            padding:0;overflow:visible;position:relative;
            animation:floatMascot 3s ease-in-out infinite;
            flex-shrink:0;
        }
        #ai-toggle-btn:active { cursor:grabbing;animation:none; }
        #ai-toggle-btn:hover  { transform:scale(1.05); }
        .ai-badge {
            position:absolute;top:-5px;right:-10px;
            background:var(--gradient-primary);color:white;
            font-size:11px;font-weight:700;padding:4px 10px;
            border-radius:20px;border:2px solid white;
            box-shadow:0 4px 6px rgba(0,0,0,0.15);
            display:flex;align-items:center;gap:5px;
            pointer-events:none;z-index:10;
            white-space:nowrap;
        }
        .mascot-img { width:100%;height:100%;object-fit:contain;pointer-events:none;filter:drop-shadow(0 6px 6px rgba(0,0,0,0.25)); }
        @keyframes floatMascot {
            0%   { transform:translateY(0px); }
            50%  { transform:translateY(-8px); }
            100% { transform:translateY(0px); }
        }
        #ai-chat-window {
            display:none;
            width:min(360px, calc(100vw - 20px));
            height:min(520px, calc(100vh - 150px));
            background:#F7FAFC;border-radius:1.2rem;
            box-shadow:0 10px 30px rgba(0,0,0,0.15);
            margin-bottom:12px;flex-direction:column;
            border:1px solid rgba(0,0,0,0.05);overflow:hidden;
        }
        .chat-header {
            background:var(--gradient-primary);color:#fff;
            padding:15px 20px;display:flex;justify-content:space-between;align-items:center;
            flex-shrink:0;
        }
        .header-titles h5 { font-size:16px;color:#fff;margin-bottom:2px; }
        .header-titles small { color:#fff;font-size:12px; }
        #close-chat-btn {
            background:rgba(255,255,255,0.2);border:none;color:#fff;
            font-size:20px;width:32px;height:32px;border-radius:50%;cursor:pointer;
            display:flex;align-items:center;justify-content:center;transition:background 0.2s;
            flex-shrink:0;
        }
        #close-chat-btn:hover { background:rgba(255,255,255,0.4); }
        .chat-disclaimer {
            background:#fff;color:var(--color-text-light);font-size:11px;
            padding:8px 20px;border-bottom:1px solid #E2E8F0;text-align:center;
            flex-shrink:0;
        }
        #chat-body {
            flex:1;padding:20px;overflow-y:auto;
            display:flex;flex-direction:column;gap:15px;
            min-height:0;
        }
        .message { max-width:85%;padding:12px 16px;font-size:14px;line-height:1.5;box-shadow:0 1px 2px rgba(0,0,0,0.08); }
        .bot-message { background:#fff;color:var(--color-text);align-self:flex-start;border-radius:12px 12px 12px 4px;border:1px solid #E2E8F0; }
        .user-message { background:linear-gradient(135deg,#18365D,#2B6CB0);color:#fff;align-self:flex-end;border-radius:12px 12px 4px 12px; }
        .chat-input-area { padding:15px;background:#fff;border-top:1px solid #E2E8F0;display:flex;gap:10px;flex-shrink:0; }
        #chat-input {
            flex-grow:1;background:#F7FAFC;border:1px solid transparent;color:var(--color-text);
            border-radius:25px;padding:12px 20px;outline:none;font-size:14px;transition:all 0.3s;
        }
        #chat-input:focus {
            background:#fff;border-color:var(--color-primary-light);
            box-shadow:0 0 0 4px rgba(43,108,176,0.12);
        }
        #chat-input::placeholder { color:#A0AEC0; }
        #send-chat-btn {
            background:var(--gradient-primary);color:#fff;border:none;border-radius:50%;
            width:45px;height:45px;display:flex;align-items:center;justify-content:center;
            cursor:pointer;transition:transform 0.2s,box-shadow 0.2s;
            box-shadow:0 4px 10px rgba(24,54,93,0.3);font-size:16px;flex-shrink:0;
        }
        #send-chat-btn:hover { transform:scale(1.05);box-shadow:0 6px 12px rgba(24,54,93,0.4); }

        @media (max-width: 480px) {
            #ai-chat-container { bottom:15px;right:10px; }
            #ai-toggle-btn { width:85px;height:85px; }
            #ai-chat-window {
                width:calc(100vw - 20px);
                height:calc(100vh - 120px);
                border-radius:1rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn    = document.getElementById('ai-toggle-btn');
            const closeBtn     = document.getElementById('close-chat-btn');
            const chatWindow   = document.getElementById('ai-chat-window');
            const sendBtn      = document.getElementById('send-chat-btn');
            const chatInput    = document.getElementById('chat-input');
            const chatBody     = document.getElementById('chat-body');
            const chatContainer= document.getElementById('ai-chat-container');

            let isDragging = false, hasDragged = false;
            let startX, startY, initialLeft, initialTop;

            function clampPosition() {
                const rect = chatContainer.getBoundingClientRect();
                const maxLeft = window.innerWidth - rect.width;
                const maxTop  = window.innerHeight - rect.height;
                if (rect.left < 0) chatContainer.style.left = '0px';
                if (rect.top  < 0) chatContainer.style.top  = '0px';
                if (rect.left > maxLeft) chatContainer.style.left = maxLeft + 'px';
                if (rect.top  > maxTop)  chatContainer.style.top  = maxTop  + 'px';
            }

            function dragStart(e) {
                isDragging = true; hasDragged = false;
                startX = e.type==='touchstart' ? e.touches[0].clientX : e.clientX;
                startY = e.type==='touchstart' ? e.touches[0].clientY : e.clientY;
                const rect = chatContainer.getBoundingClientRect();
                initialLeft = rect.left; initialTop = rect.top;
                chatContainer.style.bottom = 'auto'; chatContainer.style.right = 'auto';
                chatContainer.style.left = initialLeft+'px'; chatContainer.style.top = initialTop+'px';
            }
            function drag(e) {
                if (!isDragging) return;
                let cx = e.type==='touchmove' ? e.touches[0].clientX : e.clientX;
                let cy = e.type==='touchmove' ? e.touches[0].clientY : e.clientY;
                if (e.type==='touchmove') e.preventDefault();
                if (Math.abs(cx-startX)>3||Math.abs(cy-startY)>3) hasDragged=true;
                let nl = Math.max(10, Math.min(initialLeft+cx-startX, window.innerWidth  - chatContainer.offsetWidth  - 10));
                let nt = Math.max(10, Math.min(initialTop +cy-startY, window.innerHeight - chatContainer.offsetHeight - 10));
                chatContainer.style.left = nl+'px'; chatContainer.style.top = nt+'px';
            }
            function dragEnd() { isDragging = false; }

            toggleBtn.addEventListener('mousedown', dragStart);
            toggleBtn.addEventListener('touchstart', dragStart, {passive:false});
            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag, {passive:false});
            document.addEventListener('mouseup', dragEnd);
            document.addEventListener('touchend', dragEnd);

            function openChat() {
                chatWindow.style.display = 'flex';
                toggleBtn.style.display  = 'none';

                requestAnimationFrame(() => {
                    const vw = window.innerWidth;
                    const vh = window.innerHeight;
                    const rect = chatContainer.getBoundingClientRect();

                    if (chatContainer.style.left && chatContainer.style.left !== 'auto') {
                        let l = parseFloat(chatContainer.style.left);
                        let t = parseFloat(chatContainer.style.top);

                        l = Math.max(10, Math.min(l, vw - chatContainer.offsetWidth - 10));
                        t = Math.max(10, Math.min(t, vh - chatContainer.offsetHeight - 10));

                        chatContainer.style.left = l + 'px';
                        chatContainer.style.top  = t + 'px';
                    } else {
                        if (rect.top < 10) {
                            chatContainer.style.bottom = 'auto';
                            chatContainer.style.right  = 'auto';
                            chatContainer.style.left   = (vw - chatContainer.offsetWidth - 25) + 'px';
                            chatContainer.style.top    = '10px';
                        }
                    }
                    chatInput.focus();
                });
            }

            toggleBtn.addEventListener('click', e => {
                if (hasDragged) { e.preventDefault(); return; }
                openChat();
            });
            closeBtn.addEventListener('click', () => {
                chatWindow.style.display = 'none';
                toggleBtn.style.display  = 'flex';
                chatContainer.style.left   = 'auto';
                chatContainer.style.top    = 'auto';
                chatContainer.style.bottom = '25px';
                chatContainer.style.right  = '25px';
            });

            let conversationHistory = [];
            async function sendMessage() {
                const text = chatInput.value.trim();
                if (!text) return;
                appendMessage(text, 'user-message');
                chatInput.value = '';
                conversationHistory.push({role:'user',content:text});
                if (conversationHistory.length > 10) conversationHistory = conversationHistory.slice(-10);
                const loadingId = appendLoading();
                try {
                    const resp = await fetch("{{ route('chatbot.message') }}", {
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                        body: JSON.stringify({history:conversationHistory})
                    });
                    const data = await resp.json();
                    document.getElementById(loadingId).remove();
                    appendMessage(data.reply, 'bot-message');
                    if (!data.reply.includes('Pasensya')) conversationHistory.push({role:'assistant',content:data.reply});
                } catch(err) {
                    document.getElementById(loadingId).remove();
                    appendMessage('Pasensya, naputol ang koneksyon. Palihug sulayi usab.', 'bot-message');
                    conversationHistory.pop();
                }
            }
            sendBtn.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', e => { if (e.key==='Enter') sendMessage(); });

            function appendMessage(text, cls) {
                const d = document.createElement('div');
                d.className = 'message '+cls; d.innerText = text;
                chatBody.appendChild(d); chatBody.scrollTop = chatBody.scrollHeight;
            }
            function appendLoading() {
                const d = document.createElement('div');
                d.className = 'message bot-message'; d.id = 'loading-'+Date.now();
                d.innerText = 'Naghunahuna...';
                chatBody.appendChild(d); chatBody.scrollTop = chatBody.scrollHeight;
                return d.id;
            }
        });
    </script>
    @endif

</body>
</html>