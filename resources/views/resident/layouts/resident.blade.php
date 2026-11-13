<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Resident Portal') – E-Barangay</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ \App\Providers\AppServiceProvider::versionedAsset('css/ebarangay-theme.css') }}">
    
    {{-- Synchronous Theme Check to Prevent Flash of Unstyled Theme --}}
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('ebarangay_theme');
                if (theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    document.documentElement.removeAttribute('data-bs-theme');
                }
            } catch (e) {}
        })();
    </script>
    
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

        /* ─── Sidebar Counter Badge ─── */
        .r-sidebar-badge {
            margin-left: auto;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            line-height: 1.25;
            flex-shrink: 0;
            z-index: 2;
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
            letter-spacing: 0.02em;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
        }

        .r-sidebar-nav-item:hover .r-sidebar-badge {
            background: #3b82f6;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.45);
        }

        .r-sidebar-nav-item.active .r-sidebar-badge {
            background: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(29, 78, 216, 0.35);
        }

        body.r-sidebar-collapsed .r-sidebar-badge {
            display: none !important;
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
            body.r-sidebar-collapsed .r-sidebar-nav-item .r-nav-text,
            body.r-sidebar-collapsed .r-sidebar-badge {
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

        /* ═══════════════════════════════════════════════════════
           RESIDENT GLOBAL ANIMATION SYSTEM
           ═══════════════════════════════════════════════════════ */
        @keyframes agy-fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes agy-slideInLeft {
            from { opacity: 0; transform: translateX(-16px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes agy-scaleIn {
            from { opacity: 0; transform: scale(0.96); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes agy-liveBlip {
            0%   { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
            60%  { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        @keyframes agy-toastIn {
            from { opacity: 0; transform: translateX(28px) scale(0.95); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes agy-toastOut {
            from { opacity: 1; transform: translateX(0) scale(1); }
            to   { opacity: 0; transform: translateX(28px) scale(0.95); }
        }

        /* Page content wrapper fade in (pure opacity to avoid trapping Bootstrap modals) */
        #resident-content-wrapper {
            animation: agy-fadeIn 0.32s ease-out;
        }

        /* Sidebar nav items stagger on load */
        .r-sidebar-nav-item {
            animation: agy-slideInLeft 0.32s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .r-sidebar-nav-item:nth-child(1)  { animation-delay: 0.04s; }
        .r-sidebar-nav-item:nth-child(2)  { animation-delay: 0.08s; }
        .r-sidebar-nav-item:nth-child(3)  { animation-delay: 0.12s; }
        .r-sidebar-nav-item:nth-child(4)  { animation-delay: 0.16s; }
        .r-sidebar-nav-item:nth-child(5)  { animation-delay: 0.20s; }
        .r-sidebar-nav-item:nth-child(6)  { animation-delay: 0.24s; }
        .r-sidebar-nav-item:nth-child(n+7) { animation-delay: 0.28s; }

        /* Micro-interactions: Cards lift on hover */
        .card, .dash-card, .master-sidebar-card {
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease !important;
        }
        .dash-card:hover, .master-sidebar-card:hover, .card:not(.no-hover-lift):hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.07) !important;
        }

        /* Buttons click press micro-interaction */
        .btn, .r-sidebar-header-toggle, .r-navbar-toggle-btn, .r-notif-bell-btn, .r-theme-toggle-btn {
            transition: transform 0.15s ease, background 0.18s ease, color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease !important;
        }
        .btn:active, .r-sidebar-header-toggle:active, .r-navbar-toggle-btn:active, .r-notif-bell-btn:active, .r-theme-toggle-btn:active {
            transform: scale(0.96) !important;
        }

        /* Modals: Clean scale-in and elevated z-index */
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .modal.fade .modal-dialog {
            transform: scale(0.95) translateY(-8px);
            transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.28s ease !important;
        }
        .modal.show .modal-dialog {
            transform: scale(1) translateY(0);
        }

        /* Toast Notifications */
        #agy-toast-stack {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column-reverse;
            gap: 10px;
            pointer-events: none;
        }
        .agy-toast {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 13px 18px 13px 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.14), 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            align-items: flex-start;
            gap: 11px;
            min-width: 300px;
            max-width: 380px;
            pointer-events: auto;
            animation: agy-toastIn 0.38s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .agy-toast.hiding {
            animation: agy-toastOut 0.28s ease forwards;
        }
        .agy-toast-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; flex-shrink: 0;
        }
        .agy-toast-icon.success  { background: #dcfce7; color: #16a34a; }
        .agy-toast-icon.info     { background: #dbeafe; color: #2563eb; }
        .agy-toast-icon.warning  { background: #fef9c3; color: #ca8a04; }
        .agy-toast-icon.danger   { background: #fee2e2; color: #dc2626; }
        .agy-toast-body { flex: 1; min-width: 0; }
        .agy-toast-title { font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.3; }
        .agy-toast-msg   { font-size: 12px; color: #64748b; margin-top: 2px; line-height: 1.4; }
        .agy-toast-close {
            background: none; border: none; padding: 0;
            color: #94a3b8; cursor: pointer; font-size: 13px;
            flex-shrink: 0; margin-top: 2px;
            transition: color 0.15s;
        }
        .agy-toast-close:hover { color: #0f172a; }

        /* ─── Notification Bell ─── */
        .r-notif-bell-btn {
            position: relative; width: 42px; height: 42px;
            border-radius: 50%;
            border: 1px solid var(--color-border, #E2E8F0);
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: #718096; text-decoration: none;
        }
        .r-notif-bell-btn:hover { background: #F7FAFC; color: var(--color-primary); transform: scale(1.08); }

        .r-notif-badge {
            position: absolute; top: -4px; right: -6px;
            background: #E53E3E; color: #fff;
            font-size: 10px; font-weight: 700;
            min-width: 20px; height: 20px; padding: 0 5px;
            border-radius: 9999px;
            display: inline-flex; align-items: center; justify-content: center;
            border: 2px solid #fff; line-height: 1;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            pointer-events: none;
            z-index: 2;
        }
        .r-notif-item.unread { background: #EBF4FF; }
        .r-notif-item.read { background: #ffffff; }
        .r-notif-item:hover { background: #f8fafc; }
        .r-notif-item.unread:hover { background: #deedff; }
        .r-notif-title { color: #1e293b; }
        .r-notif-trk { color: #2B6CB0; }

        /* ─── Theme Toggle Button in Header ─── */
        .r-theme-toggle-btn {
            position: relative;
            width: 42px; height: 42px;
            border-radius: 50%;
            border: 1px solid var(--color-border, #E2E8F0);
            background: #ffffff;
            display: inline-flex;
            align-items: center; justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: 16px;
            padding: 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            text-decoration: none;
        }
        .r-theme-toggle-btn:hover {
            background: #f1f5f9;
            color: #f59e0b;
            transform: scale(1.08);
        }

        /* ─── Main Content ─── */
        .main-content { padding: 20px; flex-grow: 1; }

        /* ─── Headings ─── */
        h1,h2,h3,h4,h5,h6,
        .header-title { font-family: var(--font-heading); }
        .header-title { font-weight: 700; color: var(--color-text); }
        .header-subtitle { color: var(--color-text-light); font-size: 14px; }
        .card-custom { border: 0; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); }

        /* ═══════════════════════════════════════════════════════════
           RESIDENT GLOBAL DARK MODE & ACCESSIBLE HIGH CONTRAST
           ═══════════════════════════════════════════════════════════ */
        html[data-theme="dark"],
        html[data-bs-theme="dark"] {
            /* Custom Design Tokens */
            --color-bg: #0b1120;
            --color-surface: #131d31;
            --color-surface-glass: rgba(19, 29, 49, 0.85);
            --color-border: #1e293b;
            --color-border-light: #283548;
            --color-border-hover: #334155;
            --color-text: #f1f5f9;
            --color-text-light: #94a3b8;
            --color-text-muted: #94a3b8;
            --color-bg-alt: #182238;
            --color-bg-subtle: #182238;
            --color-table-hover: #19253d;
            --color-table-selected: #1e3152;
            --color-th-bg: #10192a;

            /* Bootstrap 5.3 Overrides */
            --bs-body-bg: #0b1120 !important;
            --bs-body-color: #f1f5f9 !important;
            --bs-card-bg: #131d31 !important;
            --bs-card-cap-bg: #182238 !important;
            --bs-card-color: #f1f5f9 !important;
            --bs-card-border-color: #1e293b !important;
            --bs-table-bg: #131d31 !important;
            --bs-table-color: #f1f5f9 !important;
            --bs-table-striped-bg: #162238 !important;
            --bs-table-hover-bg: #19253d !important;
            --bs-table-border-color: #1e293b !important;
            --bs-tertiary-bg: #182238 !important;
            --bs-secondary-bg: #1e293b !important;
            --bs-border-color: #1e293b !important;
            --bs-light-rgb: 24, 34, 56 !important;
            --bs-white-rgb: 19, 29, 49 !important;
        }

        /* ── Dark Mode Text White Force Fix ── */
        html[data-theme="dark"] .text-white,
        html[data-bs-theme="dark"] .text-white,
        html[data-theme="dark"] .text-white *,
        html[data-bs-theme="dark"] .text-white * {
            color: #ffffff !important;
        }

        /* ── Body & Full Layout Wrappers ── */
        html[data-theme="dark"] body,
        html[data-bs-theme="dark"] body {
            background-color: #0b1120 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #resident-wrapper,
        html[data-bs-theme="dark"] #resident-wrapper,
        html[data-theme="dark"] #resident-content-wrapper,
        html[data-bs-theme="dark"] #resident-content-wrapper,
        html[data-theme="dark"] .dashboard-wrapper,
        html[data-bs-theme="dark"] .dashboard-wrapper,
        html[data-theme="dark"] .main-content,
        html[data-bs-theme="dark"] .main-content {
            background-color: #0b1120 !important;
            color: #f1f5f9 !important;
        }

        /* ── Sidebar Active Nav Item Cutout ── */
        html[data-theme="dark"] .r-sidebar-nav-item.active,
        html[data-bs-theme="dark"] .r-sidebar-nav-item.active {
            background-color: #0b1120 !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .r-sidebar-nav-item.active i.r-nav-icon,
        html[data-bs-theme="dark"] .r-sidebar-nav-item.active i.r-nav-icon {
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] .r-sidebar-nav-item.active .r-nav-text,
        html[data-bs-theme="dark"] .r-sidebar-nav-item.active .r-nav-text {
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        html[data-theme="dark"] .r-sidebar-nav-item.active::before,
        html[data-bs-theme="dark"] .r-sidebar-nav-item.active::before {
            box-shadow: 15px 15px 0 #0b1120 !important;
        }
        html[data-theme="dark"] .r-sidebar-nav-item.active::after,
        html[data-bs-theme="dark"] .r-sidebar-nav-item.active::after {
            box-shadow: 15px -15px 0 #0b1120 !important;
        }

        html[data-theme="dark"] .r-sidebar-badge,
        html[data-bs-theme="dark"] .r-sidebar-badge {
            background: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4) !important;
        }
        html[data-theme="dark"] .r-sidebar-nav-item.active .r-sidebar-badge,
        html[data-bs-theme="dark"] .r-sidebar-nav-item.active .r-sidebar-badge {
            background: #2563eb !important;
            color: #ffffff !important;
        }

        /* ── Top Header & Navigation ── */
        html[data-theme="dark"] .resident-header,
        html[data-bs-theme="dark"] .resident-header {
            background-color: #0f172a !important;
            border-bottom-color: #1e293b !important;
        }
        html[data-theme="dark"] .r-header-page-title,
        html[data-bs-theme="dark"] .r-header-page-title {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .r-header-breadcrumb a,
        html[data-bs-theme="dark"] .r-header-breadcrumb a {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .r-header-breadcrumb a:hover,
        html[data-bs-theme="dark"] .r-header-breadcrumb a:hover {
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .r-header-breadcrumb .bc-cur,
        html[data-bs-theme="dark"] .r-header-breadcrumb .bc-cur {
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] .r-header-breadcrumb .bc-sep,
        html[data-bs-theme="dark"] .r-header-breadcrumb .bc-sep {
            color: #475569 !important;
        }
        html[data-theme="dark"] .r-navbar-toggle-btn,
        html[data-bs-theme="dark"] .r-navbar-toggle-btn {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }

        /* ── Top Buttons (Theme Toggle & Notif Bell) ── */
        html[data-theme="dark"] .r-theme-toggle-btn,
        html[data-bs-theme="dark"] .r-theme-toggle-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #fbbf24 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4) !important;
        }
        html[data-theme="dark"] .r-theme-toggle-btn:hover,
        html[data-bs-theme="dark"] .r-theme-toggle-btn:hover {
            background: #283548 !important;
            color: #fde047 !important;
        }
        html[data-theme="dark"] .r-notif-bell-btn,
        html[data-bs-theme="dark"] .r-notif-bell-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .r-notif-bell-btn:hover,
        html[data-bs-theme="dark"] .r-notif-bell-btn:hover {
            background: #283548 !important;
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .r-notif-badge,
        html[data-bs-theme="dark"] .r-notif-badge {
            border-color: #0f172a !important;
        }

        /* ─── Resident Notifications Dropdown in Dark Mode ─── */
        html[data-theme="dark"] .r-notif-item.unread,
        html[data-bs-theme="dark"] .r-notif-item.unread {
            background: #16243d !important;
            border-bottom-color: #1e293b !important;
        }
        html[data-theme="dark"] .r-notif-item.read,
        html[data-bs-theme="dark"] .r-notif-item.read {
            background: #131d31 !important;
            border-bottom-color: #1e293b !important;
        }
        html[data-theme="dark"] .r-notif-item:hover,
        html[data-bs-theme="dark"] .r-notif-item:hover {
            background: #182238 !important;
        }
        html[data-theme="dark"] .r-notif-title,
        html[data-bs-theme="dark"] .r-notif-title {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .r-notif-msg,
        html[data-bs-theme="dark"] .r-notif-msg {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .r-notif-trk,
        html[data-bs-theme="dark"] .r-notif-trk {
            color: #60a5fa !important;
        }

        /* ── Cards, Containers, and Modals ── */
        html[data-theme="dark"] .card,
        html[data-bs-theme="dark"] .card,
        html[data-theme="dark"] .card-body,
        html[data-bs-theme="dark"] .card-body,
        html[data-theme="dark"] .bg-white,
        html[data-bs-theme="dark"] .bg-white,
        html[data-theme="dark"] .dash-card,
        html[data-bs-theme="dark"] .dash-card,
        html[data-theme="dark"] .master-sidebar-card,
        html[data-bs-theme="dark"] .master-sidebar-card,
        html[data-theme="dark"] .modal-content,
        html[data-bs-theme="dark"] .modal-content,
        html[data-theme="dark"] .modal-body,
        html[data-bs-theme="dark"] .modal-body {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .card-header,
        html[data-bs-theme="dark"] .card-header,
        html[data-theme="dark"] .card-footer,
        html[data-bs-theme="dark"] .card-footer,
        html[data-theme="dark"] .modal-header,
        html[data-bs-theme="dark"] .modal-header,
        html[data-theme="dark"] .modal-footer,
        html[data-bs-theme="dark"] .modal-footer {
            background-color: #182238 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }

        /* ── Tables ── */
        html[data-theme="dark"] .table,
        html[data-bs-theme="dark"] .table,
        html[data-theme="dark"] .dash-table,
        html[data-bs-theme="dark"] .dash-table {
            --bs-table-bg: #131d31 !important;
            background-color: #131d31 !important;
            color: #f1f5f9 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .table thead th,
        html[data-bs-theme="dark"] .table thead th,
        html[data-theme="dark"] .dash-table th,
        html[data-bs-theme="dark"] .dash-table th {
            background-color: #10192a !important;
            color: #cbd5e1 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .table tbody td,
        html[data-bs-theme="dark"] .table tbody td,
        html[data-theme="dark"] .dash-table td,
        html[data-bs-theme="dark"] .dash-table td {
            background-color: #131d31 !important;
            color: #e2e8f0 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .table-hover tbody tr:hover > *,
        html[data-bs-theme="dark"] .table-hover tbody tr:hover > * {
            background-color: #19253d !important;
            color: #ffffff !important;
        }

        /* ── Forms, Inputs, and Selects ── */
        html[data-theme="dark"] .form-control,
        html[data-bs-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select,
        html[data-bs-theme="dark"] .form-select,
        html[data-theme="dark"] .input-group-text,
        html[data-bs-theme="dark"] .input-group-text {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .form-control:focus,
        html[data-bs-theme="dark"] .form-control:focus,
        html[data-theme="dark"] .form-select:focus,
        html[data-bs-theme="dark"] .form-select:focus {
            background-color: #1e2b45 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        }
        html[data-theme="dark"] .form-control::placeholder,
        html[data-bs-theme="dark"] .form-control::placeholder {
            color: #64748b !important;
        }

        /* ── Dropdowns ── */
        html[data-theme="dark"] .dropdown-menu,
        html[data-bs-theme="dark"] .dropdown-menu {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45) !important;
        }
        html[data-theme="dark"] .dropdown-item,
        html[data-bs-theme="dark"] .dropdown-item {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .dropdown-item:hover,
        html[data-bs-theme="dark"] .dropdown-item:hover,
        html[data-theme="dark"] .dropdown-item:focus,
        html[data-bs-theme="dark"] .dropdown-item:focus {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }

        /* ── Headings, Typography & Accents ── */
        html[data-theme="dark"] h1, html[data-bs-theme="dark"] h1,
        html[data-theme="dark"] h2, html[data-bs-theme="dark"] h2,
        html[data-theme="dark"] h3, html[data-bs-theme="dark"] h3,
        html[data-theme="dark"] h4, html[data-bs-theme="dark"] h4,
        html[data-theme="dark"] h5, html[data-bs-theme="dark"] h5,
        html[data-theme="dark"] h6, html[data-bs-theme="dark"] h6,
        html[data-theme="dark"] .text-dark, html[data-bs-theme="dark"] .text-dark,
        html[data-theme="dark"] .fw-bold, html[data-bs-theme="dark"] .fw-bold,
        html[data-theme="dark"] strong, html[data-bs-theme="dark"] strong,
        html[data-theme="dark"] b, html[data-bs-theme="dark"] b {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .text-muted,
        html[data-bs-theme="dark"] .text-muted,
        html[data-theme="dark"] .text-secondary,
        html[data-bs-theme="dark"] .text-secondary {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .bg-light,
        html[data-bs-theme="dark"] .bg-light {
            background-color: #182238 !important;
            color: #cbd5e1 !important;
        }

        /* ── Status Badges in Dark Mode (High-Contrast System) ── */
        html[data-theme="dark"] .status-badge,
        html[data-bs-theme="dark"] .status-badge {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
        }

        /* Amber: Pending / Pending Approval / Pending Review */
        html[data-theme="dark"] .status-badge.status-pending,
        html[data-bs-theme="dark"] .status-badge.status-pending,
        html[data-theme="dark"] .status-badge.status-pending-approval,
        html[data-bs-theme="dark"] .status-badge.status-pending-approval,
        html[data-theme="dark"] .status-badge.status-pending-review,
        html[data-bs-theme="dark"] .status-badge.status-pending-review,
        html[data-theme="dark"] .status-pending,
        html[data-bs-theme="dark"] .status-pending,
        html[data-theme="dark"] .status-pending-approval,
        html[data-bs-theme="dark"] .status-pending-approval {
            background-color: rgba(245, 158, 11, 0.18) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(245, 158, 11, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-pending *,
        html[data-bs-theme="dark"] .status-badge.status-pending *,
        html[data-theme="dark"] .status-badge.status-pending-approval *,
        html[data-bs-theme="dark"] .status-badge.status-pending-approval *,
        html[data-theme="dark"] .status-badge.status-pending-review *,
        html[data-bs-theme="dark"] .status-badge.status-pending-review *,
        html[data-theme="dark"] .status-pending *,
        html[data-bs-theme="dark"] .status-pending *,
        html[data-theme="dark"] .status-pending-approval *,
        html[data-bs-theme="dark"] .status-pending-approval * {
            color: #fbbf24 !important;
        }

        /* Emerald Green: Active / Approved / Resolved */
        html[data-theme="dark"] .status-badge.status-active,
        html[data-bs-theme="dark"] .status-badge.status-active,
        html[data-theme="dark"] .status-badge.status-approved,
        html[data-bs-theme="dark"] .status-badge.status-approved,
        html[data-theme="dark"] .status-badge.status-resolved,
        html[data-bs-theme="dark"] .status-badge.status-resolved,
        html[data-theme="dark"] .status-active,
        html[data-bs-theme="dark"] .status-active,
        html[data-theme="dark"] .status-approved,
        html[data-bs-theme="dark"] .status-approved,
        html[data-theme="dark"] .status-resolved,
        html[data-bs-theme="dark"] .status-resolved {
            background-color: rgba(34, 197, 94, 0.18) !important;
            color: #4ade80 !important;
            border: 1px solid rgba(34, 197, 94, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-active *,
        html[data-bs-theme="dark"] .status-badge.status-active *,
        html[data-theme="dark"] .status-badge.status-approved *,
        html[data-bs-theme="dark"] .status-badge.status-approved *,
        html[data-theme="dark"] .status-badge.status-resolved *,
        html[data-bs-theme="dark"] .status-badge.status-resolved *,
        html[data-theme="dark"] .status-active *,
        html[data-bs-theme="dark"] .status-active *,
        html[data-theme="dark"] .status-approved *,
        html[data-bs-theme="dark"] .status-approved *,
        html[data-theme="dark"] .status-resolved *,
        html[data-bs-theme="dark"] .status-resolved * {
            color: #4ade80 !important;
        }

        /* Sky / Blue: Under Review / In Progress / Processing */
        html[data-theme="dark"] .status-badge.status-under-review,
        html[data-bs-theme="dark"] .status-badge.status-under-review,
        html[data-theme="dark"] .status-badge.status-in-progress,
        html[data-bs-theme="dark"] .status-badge.status-in-progress,
        html[data-theme="dark"] .status-badge.status-processing,
        html[data-bs-theme="dark"] .status-badge.status-processing,
        html[data-theme="dark"] .status-under-review,
        html[data-bs-theme="dark"] .status-under-review,
        html[data-theme="dark"] .status-in-progress,
        html[data-bs-theme="dark"] .status-in-progress,
        html[data-theme="dark"] .status-processing,
        html[data-bs-theme="dark"] .status-processing {
            background-color: rgba(56, 189, 248, 0.18) !important;
            color: #38bdf8 !important;
            border: 1px solid rgba(56, 189, 248, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-under-review *,
        html[data-bs-theme="dark"] .status-badge.status-under-review *,
        html[data-theme="dark"] .status-badge.status-in-progress *,
        html[data-bs-theme="dark"] .status-badge.status-in-progress *,
        html[data-theme="dark"] .status-badge.status-processing *,
        html[data-bs-theme="dark"] .status-badge.status-processing *,
        html[data-theme="dark"] .status-under-review *,
        html[data-bs-theme="dark"] .status-under-review *,
        html[data-theme="dark"] .status-in-progress *,
        html[data-bs-theme="dark"] .status-in-progress *,
        html[data-theme="dark"] .status-processing *,
        html[data-bs-theme="dark"] .status-processing * {
            color: #38bdf8 !important;
        }

        /* Teal: Ready / Completed */
        html[data-theme="dark"] .status-badge.status-ready,
        html[data-bs-theme="dark"] .status-badge.status-ready,
        html[data-theme="dark"] .status-badge.status-completed,
        html[data-bs-theme="dark"] .status-badge.status-completed,
        html[data-theme="dark"] .status-ready,
        html[data-bs-theme="dark"] .status-ready,
        html[data-theme="dark"] .status-completed,
        html[data-bs-theme="dark"] .status-completed {
            background-color: rgba(20, 184, 166, 0.18) !important;
            color: #2dd4bf !important;
            border: 1px solid rgba(20, 184, 166, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-ready *,
        html[data-bs-theme="dark"] .status-badge.status-ready *,
        html[data-theme="dark"] .status-badge.status-completed *,
        html[data-bs-theme="dark"] .status-badge.status-completed *,
        html[data-theme="dark"] .status-ready *,
        html[data-bs-theme="dark"] .status-ready *,
        html[data-theme="dark"] .status-completed *,
        html[data-bs-theme="dark"] .status-completed * {
            color: #2dd4bf !important;
        }

        /* Red: Rejected / Cancelled / Failed */
        html[data-theme="dark"] .status-badge.status-rejected,
        html[data-bs-theme="dark"] .status-badge.status-rejected,
        html[data-theme="dark"] .status-badge.status-cancelled,
        html[data-bs-theme="dark"] .status-badge.status-cancelled,
        html[data-theme="dark"] .status-badge.status-failed,
        html[data-bs-theme="dark"] .status-badge.status-failed,
        html[data-theme="dark"] .status-rejected,
        html[data-bs-theme="dark"] .status-rejected,
        html[data-theme="dark"] .status-cancelled,
        html[data-bs-theme="dark"] .status-cancelled,
        html[data-theme="dark"] .status-failed,
        html[data-bs-theme="dark"] .status-failed {
            background-color: rgba(239, 68, 68, 0.18) !important;
            color: #f87171 !important;
            border: 1px solid rgba(239, 68, 68, 0.45) !important;
        }
        html[data-theme="dark"] .status-badge.status-rejected *,
        html[data-bs-theme="dark"] .status-badge.status-rejected *,
        html[data-theme="dark"] .status-badge.status-cancelled *,
        html[data-bs-theme="dark"] .status-badge.status-cancelled *,
        html[data-theme="dark"] .status-badge.status-failed *,
        html[data-bs-theme="dark"] .status-badge.status-failed *,
        html[data-theme="dark"] .status-rejected *,
        html[data-bs-theme="dark"] .status-rejected *,
        html[data-theme="dark"] .status-cancelled *,
        html[data-bs-theme="dark"] .status-cancelled *,
        html[data-theme="dark"] .status-failed *,
        html[data-bs-theme="dark"] .status-failed * {
            color: #f87171 !important;
        }

        /* Slate: Inactive / Deactivated */
        html[data-theme="dark"] .status-badge.status-inactive,
        html[data-bs-theme="dark"] .status-badge.status-inactive,
        html[data-theme="dark"] .status-badge.status-deactivated,
        html[data-bs-theme="dark"] .status-badge.status-deactivated,
        html[data-theme="dark"] .status-inactive,
        html[data-bs-theme="dark"] .status-inactive,
        html[data-theme="dark"] .status-deactivated,
        html[data-bs-theme="dark"] .status-deactivated {
            background-color: rgba(148, 163, 184, 0.18) !important;
            color: #cbd5e1 !important;
            border: 1px solid rgba(148, 163, 184, 0.35) !important;
        }
        html[data-theme="dark"] .status-badge.status-inactive *,
        html[data-bs-theme="dark"] .status-badge.status-inactive *,
        html[data-theme="dark"] .status-badge.status-deactivated *,
        html[data-bs-theme="dark"] .status-badge.status-deactivated *,
        html[data-theme="dark"] .status-inactive *,
        html[data-bs-theme="dark"] .status-inactive *,
        html[data-theme="dark"] .status-deactivated *,
        html[data-bs-theme="dark"] .status-deactivated * {
            color: #cbd5e1 !important;
        }

        /* ── Resident Dashboard Widgets (Calendar & Reminders) ── */
        html[data-theme="dark"] .section-title,
        html[data-bs-theme="dark"] .section-title,
        html[data-theme="dark"] .reminders-section-inner .sub-title,
        html[data-bs-theme="dark"] .reminders-section-inner .sub-title,
        html[data-theme="dark"] .reminder-info h6,
        html[data-bs-theme="dark"] .reminder-info h6 {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .calendar-section-inner,
        html[data-bs-theme="dark"] .calendar-section-inner {
            border-bottom-color: #1e293b !important;
        }
        html[data-theme="dark"] .calendar-day,
        html[data-bs-theme="dark"] .calendar-day {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .calendar-day:hover,
        html[data-bs-theme="dark"] .calendar-day:hover {
            background: #1e293b !important;
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] .reminder-icon,
        html[data-bs-theme="dark"] .reminder-icon {
            background: #182238 !important;
            border-color: #283548 !important;
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .dash-badge.badge-completed,
        html[data-bs-theme="dark"] .dash-badge.badge-completed {
            background: #1e293b !important;
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .dash-badge.badge-pending,
        html[data-bs-theme="dark"] .dash-badge.badge-pending {
            background: rgba(245, 158, 11, 0.18) !important;
            color: #fbbf24 !important;
        }
        html[data-theme="dark"] .dash-badge.badge-approved,
        html[data-bs-theme="dark"] .dash-badge.badge-approved {
            background: rgba(34, 197, 94, 0.18) !important;
            color: #4ade80 !important;
        }
        html[data-theme="dark"] .dash-badge.badge-ready,
        html[data-bs-theme="dark"] .dash-badge.badge-ready {
            background: rgba(56, 189, 248, 0.18) !important;
            color: #38bdf8 !important;
        }

        /* ── Settings Page Tabs ── */
        html[data-theme="dark"] .settings-nav .nav-link,
        html[data-bs-theme="dark"] .settings-nav .nav-link {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .settings-nav .nav-link:hover,
        html[data-bs-theme="dark"] .settings-nav .nav-link:hover {
            background: #182238 !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .settings-nav .nav-link.active,
        html[data-bs-theme="dark"] .settings-nav .nav-link.active {
            background: #1e3152 !important;
            color: #38bdf8 !important;
            font-weight: 700 !important;
        }

        /* ── Track Request & Accordion in Dark Mode ── */
        html[data-theme="dark"] .accordion-item,
        html[data-bs-theme="dark"] .accordion-item {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .accordion-body,
        html[data-bs-theme="dark"] .accordion-body,
        html[data-theme="dark"] .accordion-body.bg-white,
        html[data-bs-theme="dark"] .accordion-body.bg-white {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .timeline-item::after,
        html[data-bs-theme="dark"] .timeline-item::after {
            background-color: #1e293b !important;
        }
        html[data-theme="dark"] .timeline-icon,
        html[data-bs-theme="dark"] .timeline-icon {
            background-color: #131d31 !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .accordion-button,
        html[data-bs-theme="dark"] .accordion-button {
            background-color: #131d31 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .accordion-button:not(.collapsed),
        html[data-bs-theme="dark"] .accordion-button:not(.collapsed) {
            background-color: #182238 !important;
            color: #38bdf8 !important;
        }

        /* ── Document Type Grid Cards (User Request Document) ── */
        html[data-theme="dark"] .doc-type-card,
        html[data-bs-theme="dark"] .doc-type-card {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .doc-type-card:hover,
        html[data-bs-theme="dark"] .doc-type-card:hover {
            background-color: #182238 !important;
            border-color: #38bdf8 !important;
        }
        html[data-theme="dark"] .doc-type-card.selected,
        html[data-bs-theme="dark"] .doc-type-card.selected {
            background-color: rgba(43, 108, 176, 0.3) !important;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25) !important;
        }
        html[data-theme="dark"] .doc-type-card .doc-type-name,
        html[data-bs-theme="dark"] .doc-type-card .doc-type-name,
        html[data-theme="dark"] .doc-type-name,
        html[data-bs-theme="dark"] .doc-type-name {
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
        html[data-theme="dark"] .doc-type-card .text-warning,
        html[data-bs-theme="dark"] .doc-type-card .text-warning {
            color: #fbbf24 !important;
        }

        /* ── Purpose Chips & Modal Options ── */
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

        /* ── Fix Light-Tinted Inline Elements across Resident Sub-views ── */
        html[data-theme="dark"] [style*="background:#f9fafb"],
        html[data-bs-theme="dark"] [style*="background:#f9fafb"],
        html[data-theme="dark"] [style*="background: #f9fafb"],
        html[data-bs-theme="dark"] [style*="background: #f9fafb"],
        html[data-theme="dark"] [style*="background:#f8fafc"],
        html[data-bs-theme="dark"] [style*="background:#f8fafc"],
        html[data-theme="dark"] [style*="background: #f8fafc"],
        html[data-bs-theme="dark"] [style*="background: #f8fafc"],
        html[data-theme="dark"] [style*="background:#fafbff"],
        html[data-bs-theme="dark"] [style*="background:#fafbff"],
        html[data-theme="dark"] [style*="background: #fafbff"],
        html[data-bs-theme="dark"] [style*="background: #fafbff"],
        html[data-theme="dark"] [style*="background-color: #f8f9fe"],
        html[data-bs-theme="dark"] [style*="background-color: #f8f9fe"],
        html[data-theme="dark"] [style*="background-color: #f8f9fa"],
        html[data-bs-theme="dark"] [style*="background-color: #f8f9fa"] {
            background-color: #182238 !important;
            border-color: #283548 !important;
            color: #f1f5f9 !important;
        }

        /* ── Claim Stub & Printable Document Modals in Dark Mode ── */
        html[data-theme="dark"] #claimStubCard,
        html[data-bs-theme="dark"] #claimStubCard,
        html[data-theme="dark"] [id^="receiptCard"],
        html[data-bs-theme="dark"] [id^="receiptCard"],
        html[data-theme="dark"] #receiptCard,
        html[data-bs-theme="dark"] #receiptCard {
            background-color: #131d31 !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #claimStubCard .border-bottom,
        html[data-bs-theme="dark"] #claimStubCard .border-bottom,
        html[data-theme="dark"] [id^="receiptCard"] .border-bottom,
        html[data-bs-theme="dark"] [id^="receiptCard"] .border-bottom,
        html[data-theme="dark"] #receiptCard .border-bottom,
        html[data-bs-theme="dark"] #receiptCard .border-bottom {
            border-bottom-color: #1e293b !important;
        }
        html[data-theme="dark"] [id^="receiptCard"] .bg-primary,
        html[data-bs-theme="dark"] [id^="receiptCard"] .bg-primary,
        html[data-theme="dark"] #receiptCard .bg-primary,
        html[data-bs-theme="dark"] #receiptCard .bg-primary {
            background-color: #18365D !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] [id^="receiptCard"] .bg-primary *,
        html[data-bs-theme="dark"] [id^="receiptCard"] .bg-primary *,
        html[data-theme="dark"] #receiptCard .bg-primary *,
        html[data-bs-theme="dark"] #receiptCard .bg-primary * {
            color: #ffffff !important;
        }

        /* ── Request Document Specifics ── */
        html[data-theme="dark"] .req-section,
        html[data-bs-theme="dark"] .req-section {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .req-section-header,
        html[data-bs-theme="dark"] .req-section-header {
            border-bottom-color: #1e293b !important;
        }
        html[data-theme="dark"] .doc-info-card,
        html[data-bs-theme="dark"] .doc-info-card {
            background: #182238 !important;
            border-color: #283548 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .upload-zone,
        html[data-bs-theme="dark"] .upload-zone {
            background: #182238 !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .upload-zone:hover,
        html[data-bs-theme="dark"] .upload-zone:hover {
            background: #1e293b !important;
            border-color: #38bdf8 !important;
        }
        html[data-theme="dark"] .lifecycle-stepper::before,
        html[data-bs-theme="dark"] .lifecycle-stepper::before {
            background: #1e293b !important;
        }
        html[data-theme="dark"] .stepper-circle,
        html[data-bs-theme="dark"] .stepper-circle {
            background: #1e293b !important;
            color: #94a3b8 !important;
            border-color: #131d31 !important;
        }
        html[data-theme="dark"] .stepper-label,
        html[data-bs-theme="dark"] .stepper-label {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .stepper-sublabel,
        html[data-bs-theme="dark"] .stepper-sublabel {
            color: #64748b !important;
        }
        html[data-theme="dark"] .stepper-step.active .stepper-label,
        html[data-bs-theme="dark"] .stepper-step.active .stepper-label {
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] .stepper-step.completed .stepper-label,
        html[data-bs-theme="dark"] .stepper-step.completed .stepper-label {
            color: #34d399 !important;
        }

        html[data-theme="dark"] .step-header-kicker,
        html[data-bs-theme="dark"] .step-header-kicker {
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] .step-icon-box,
        html[data-bs-theme="dark"] .step-icon-box {
            background: rgba(56, 189, 248, 0.15) !important;
            color: #38bdf8 !important;
            border: 1px solid rgba(56, 189, 248, 0.3) !important;
        }
        html[data-theme="dark"] .step-icon-box i,
        html[data-bs-theme="dark"] .step-icon-box i {
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] .step-header-title,
        html[data-bs-theme="dark"] .step-header-title {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .file-item,
        html[data-bs-theme="dark"] .file-item {
            background: #182238 !important;
            border-color: #283548 !important;
            color: #f1f5f9 !important;
        }

        /* ── Status Badges in Dark Mode ── */
        html[data-theme="dark"] .status-pending,
        html[data-bs-theme="dark"] .status-pending,
        html[data-theme="dark"] .badge-pending,
        html[data-bs-theme="dark"] .badge-pending,
        html[data-theme="dark"] .status-pending-approval,
        html[data-bs-theme="dark"] .status-pending-approval {
            background-color: rgba(245, 158, 11, 0.18) !important;
            color: #fbbf24 !important;
            border-color: rgba(245, 158, 11, 0.35) !important;
        }
        html[data-theme="dark"] .status-under-review,
        html[data-bs-theme="dark"] .status-under-review,
        html[data-theme="dark"] .status-in-progress,
        html[data-bs-theme="dark"] .status-in-progress,
        html[data-theme="dark"] .status-processing,
        html[data-bs-theme="dark"] .status-processing {
            background-color: rgba(56, 189, 248, 0.18) !important;
            color: #38bdf8 !important;
            border-color: rgba(56, 189, 248, 0.35) !important;
        }
        html[data-theme="dark"] .status-ready,
        html[data-bs-theme="dark"] .status-ready,
        html[data-theme="dark"] .badge-ready,
        html[data-bs-theme="dark"] .badge-ready {
            background-color: rgba(20, 184, 166, 0.18) !important;
            color: #2dd4bf !important;
            border-color: rgba(20, 184, 166, 0.35) !important;
        }
        html[data-theme="dark"] .status-approved,
        html[data-bs-theme="dark"] .status-approved,
        html[data-theme="dark"] .badge-approved,
        html[data-bs-theme="dark"] .badge-approved,
        html[data-theme="dark"] .status-resolved,
        html[data-bs-theme="dark"] .status-resolved,
        html[data-theme="dark"] .status-active,
        html[data-bs-theme="dark"] .status-active {
            background-color: rgba(34, 197, 94, 0.18) !important;
            color: #4ade80 !important;
            border-color: rgba(34, 197, 94, 0.35) !important;
        }
        html[data-theme="dark"] .status-completed,
        html[data-bs-theme="dark"] .status-completed,
        html[data-theme="dark"] .badge-completed,
        html[data-bs-theme="dark"] .badge-completed {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }
        html[data-theme="dark"] .status-rejected,
        html[data-bs-theme="dark"] .status-rejected {
            background-color: rgba(239, 68, 68, 0.18) !important;
            color: #f87171 !important;
            border-color: rgba(239, 68, 68, 0.35) !important;
        }
        html[data-theme="dark"] .status-inactive,
        html[data-bs-theme="dark"] .status-inactive {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        /* ── Toast in Dark Mode ── */
        html[data-theme="dark"] .agy-toast,
        html[data-bs-theme="dark"] .agy-toast {
            background: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.45) !important;
        }
        html[data-theme="dark"] .agy-toast-title,
        html[data-bs-theme="dark"] .agy-toast-title { color: #f1f5f9 !important; }
        html[data-theme="dark"] .agy-toast-msg,
        html[data-bs-theme="dark"] .agy-toast-msg   { color: #94a3b8 !important; }
        html[data-theme="dark"] .agy-toast-close,
        html[data-bs-theme="dark"] .agy-toast-close { color: #64748b !important; }

        /* ── Istong AI Chatbot in Dark Mode ── */
        html[data-theme="dark"] #ai-chat-window,
        html[data-bs-theme="dark"] #ai-chat-window {
            background: #131d31 !important;
            border-color: #1e293b !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6) !important;
        }
        html[data-theme="dark"] .chat-disclaimer,
        html[data-bs-theme="dark"] .chat-disclaimer {
            background: #0f172a !important;
            border-bottom-color: #1e293b !important;
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .bot-message,
        html[data-bs-theme="dark"] .bot-message {
            background: #182238 !important;
            color: #f1f5f9 !important;
            border-color: #283548 !important;
        }
        html[data-theme="dark"] .chat-input-area,
        html[data-bs-theme="dark"] .chat-input-area {
            background: #0f172a !important;
            border-top-color: #1e293b !important;
        }
        html[data-theme="dark"] #chat-input,
        html[data-bs-theme="dark"] #chat-input {
            background: #182238 !important;
            color: #f1f5f9 !important;
            border-color: #283548 !important;
        }

        /* ── Dark Mode Pagination & Controls ── */
        html[data-theme="dark"] .pagination,
        html[data-bs-theme="dark"] .pagination {
            --bs-pagination-bg: #1e293b;
            --bs-pagination-border-color: #334155;
            --bs-pagination-color: #cbd5e1;
            --bs-pagination-hover-bg: #334155;
            --bs-pagination-hover-border-color: #475569;
            --bs-pagination-hover-color: #38bdf8;
            --bs-pagination-focus-bg: #334155;
            --bs-pagination-focus-color: #38bdf8;
            --bs-pagination-active-bg: #2563eb;
            --bs-pagination-active-border-color: #3b82f6;
            --bs-pagination-active-color: #ffffff;
            --bs-pagination-disabled-bg: #0f172a;
            --bs-pagination-disabled-border-color: #1e293b;
            --bs-pagination-disabled-color: #64748b;
        }

        html[data-theme="dark"] .pagination .page-link,
        html[data-bs-theme="dark"] .pagination .page-link {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        html[data-theme="dark"] .pagination .page-link:hover,
        html[data-bs-theme="dark"] .pagination .page-link:hover {
            background-color: #334155 !important;
            border-color: #60a5fa !important;
            color: #38bdf8 !important;
        }

        html[data-theme="dark"] .pagination .page-item.active .page-link,
        html[data-bs-theme="dark"] .pagination .page-item.active .page-link {
            background-color: #2563eb !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.45) !important;
        }

        html[data-theme="dark"] .pagination .page-item.disabled .page-link,
        html[data-bs-theme="dark"] .pagination .page-item.disabled .page-link {
            background-color: #0f172a !important;
            border-color: #1e293b !important;
            color: #64748b !important;
            opacity: 0.65 !important;
        }

        html[data-theme="dark"] #pagination-container,
        html[data-bs-theme="dark"] #pagination-container,
        html[data-theme="dark"] .pagination-wrapper,
        html[data-bs-theme="dark"] .pagination-wrapper {
            background-color: #111c2e !important;
            border-color: #1e293b !important;
            color: #cbd5e1 !important;
        }

        html[data-theme="dark"] #pagination-container .text-muted,
        html[data-bs-theme="dark"] #pagination-container .text-muted,
        html[data-theme="dark"] .pagination-wrapper .text-muted,
        html[data-bs-theme="dark"] .pagination-wrapper .text-muted,
        html[data-theme="dark"] nav p.small.text-muted,
        html[data-bs-theme="dark"] nav p.small.text-muted {
            color: #94a3b8 !important;
        }

        html[data-theme="dark"] #pagination-container strong,
        html[data-bs-theme="dark"] #pagination-container strong,
        html[data-theme="dark"] .pagination-wrapper strong,
        html[data-bs-theme="dark"] .pagination-wrapper strong,
        html[data-theme="dark"] nav p.small.text-muted .fw-semibold,
        html[data-bs-theme="dark"] nav p.small.text-muted .fw-semibold {
            color: #f8fafc !important;
        }

        /* ── Page Transition Loader ── */
        #page-loader-bar {
            position: fixed; top: 0; left: 0; width: 100%; height: 3px;
            z-index: 99999; pointer-events: none; opacity: 0;
            transition: opacity 0.2s ease;
        }
        #page-loader-bar.loading { opacity: 1; }
        #page-loader-fill {
            height: 100%; width: 0%;
            background: linear-gradient(90deg, #2563eb, #38bdf8, #2563eb);
            background-size: 200% 100%;
            border-radius: 0 3px 3px 0;
            transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            animation: shimmer-loader 1.5s infinite linear;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.6), 0 0 20px rgba(56, 189, 248, 0.3);
        }
        @keyframes shimmer-loader {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        html[data-theme="dark"] #page-loader-fill,
        html[data-bs-theme="dark"] #page-loader-fill {
            background: linear-gradient(90deg, #38bdf8, #60a5fa, #38bdf8);
            background-size: 200% 100%;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.6);
        }

        /* ── Sidebar Nav Loading Spinner ── */
        .sidebar-nav-spinner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            z-index: 5;
            animation: agy-fadeIn 0.15s ease forwards;
        }
        .sidebar-nav-spinner::before {
            content: '';
            width: 13px;
            height: 13px;
            border-radius: 50%;
            border: 2px solid rgba(56, 189, 248, 0.25);
            border-top-color: #38bdf8;
            border-right-color: #38bdf8;
            box-sizing: border-box;
            animation: sidebar-spin 0.65s linear infinite;
        }
        .r-sidebar-nav-item.active .sidebar-nav-spinner::before,
        .sidebar-nav-item.active .sidebar-nav-spinner::before {
            border: 2px solid rgba(37, 99, 235, 0.2);
            border-top-color: #2563eb;
            border-right-color: #2563eb;
        }
        html[data-theme="dark"] .r-sidebar-nav-item.active .sidebar-nav-spinner::before,
        html[data-bs-theme="dark"] .r-sidebar-nav-item.active .sidebar-nav-spinner::before,
        html[data-theme="dark"] .sidebar-nav-item.active .sidebar-nav-spinner::before,
        html[data-bs-theme="dark"] .sidebar-nav-item.active .sidebar-nav-spinner::before {
            border: 2px solid rgba(56, 189, 248, 0.25);
            border-top-color: #38bdf8;
            border-right-color: #38bdf8;
        }
        @keyframes sidebar-spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        body.r-sidebar-collapsed .sidebar-nav-spinner,
        body.sidebar-collapsed .sidebar-nav-spinner {
            display: none !important;
        }

        /* ── Page Transition Blur Overlay & Load Animation ── */
        #page-blur-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1025;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
            background-color: rgba(248, 250, 251, 0);
            transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                        backdrop-filter 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                        -webkit-backdrop-filter 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                        visibility 0.22s ease,
                        background-color 0.22s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (min-width: 992px) {
            #page-blur-overlay {
                left: var(--r-sidebar-width, 260px);
                width: calc(100vw - var(--r-sidebar-width, 260px));
            }
            body.r-sidebar-collapsed #page-blur-overlay {
                left: 78px;
                width: calc(100vw - 78px);
            }
        }

        #page-blur-overlay.loading {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            background-color: rgba(248, 250, 251, 0.5);
        }

        html[data-theme="dark"] #page-blur-overlay.loading,
        html[data-bs-theme="dark"] #page-blur-overlay.loading {
            background-color: rgba(11, 17, 32, 0.55);
        }

        .page-blur-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.65rem 1.25rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 9999px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            transform: scale(0.92);
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        #page-blur-overlay.loading .page-blur-indicator {
            transform: scale(1);
        }

        html[data-theme="dark"] .page-blur-indicator,
        html[data-bs-theme="dark"] .page-blur-indicator {
            background: rgba(19, 29, 49, 0.9);
            border-color: rgba(51, 65, 85, 0.7);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.45);
        }

        .page-blur-spinner {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2.5px solid rgba(37, 99, 235, 0.2);
            border-top-color: #2563eb;
            border-right-color: #2563eb;
            box-sizing: border-box;
            animation: sidebar-spin 0.65s linear infinite;
            flex-shrink: 0;
        }

        html[data-theme="dark"] .page-blur-spinner,
        html[data-bs-theme="dark"] .page-blur-spinner {
            border-color: rgba(56, 189, 248, 0.25);
            border-top-color: #38bdf8;
            border-right-color: #38bdf8;
        }

        .page-blur-text {
            font-size: 0.84rem;
            font-weight: 600;
            color: #1e293b;
            letter-spacing: 0.02em;
        }

        html[data-theme="dark"] .page-blur-text,
        html[data-bs-theme="dark"] .page-blur-text {
            color: #f1f5f9 !important;
        }
    </style>
</head>
<body>
    <!-- Page Transition Loader -->
    <div id="page-loader-bar" aria-hidden="true"><div id="page-loader-fill"></div></div>
    <!-- Page Transition Blur Overlay -->
    <div id="page-blur-overlay" aria-hidden="true">
        <div class="page-blur-indicator">
            <div class="page-blur-spinner"></div>
            <span class="page-blur-text">Loading...</span>
        </div>
    </div>
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
$residentActiveRequestsCount = 0;
if (auth()->check()) {
    $residentActiveRequestsCount = \App\Models\DocumentRequest::where('user_id', auth()->id())
        ->whereNotIn('status', ['Completed', 'Rejected', 'Cancelled'])
        ->count();
}
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
                    <span class="r-sidebar-badge {{ $residentActiveRequestsCount > 0 ? '' : 'd-none' }}" id="rTrackRequestsBadge">{{ $residentActiveRequestsCount }}</span>
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

                    {{-- Theme Toggle Button --}}
                    <button type="button" class="r-theme-toggle-btn" id="resident-theme-toggle-btn" aria-label="Toggle dark / light mode" title="Toggle dark mode">
                        <i class="fa-solid fa-moon icon-moon"></i>
                        <i class="fa-solid fa-sun icon-sun d-none"></i>
                    </button>

                    {{-- Notification Bell --}}
                    @php
                        $unreadNotifCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
                        $userNotifs = auth()->check() && auth()->user()->notifications ? auth()->user()->notifications->take(15)->values() : collect();
                    @endphp
                    <div class="dropdown">
                        <a class="r-notif-bell-btn text-decoration-none"
                           href="#" id="rNotifDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false"
                           onclick="markResidentNotificationsRead()">
                            <i class="fa-solid fa-bell" style="font-size:16px;"></i>
                            <span class="r-notif-badge {{ $unreadNotifCount > 0 ? '' : 'd-none' }}" id="r-notif-badge">
                                {{ $unreadNotifCount }}
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow p-0 mt-2"
                            style="width:330px; border-radius:14px; overflow:hidden;">
                            <div style="background: linear-gradient(135deg, #0F2240, #2B6CB0); padding:14px 18px;"
                                 class="d-flex align-items-center justify-content-between">
                                <span class="text-white fw-bold" style="font-size:14px;">
                                    <i class="fa-solid fa-bell me-2"></i>Notifications
                                </span>
                            </div>
                            <div id="r-notif-list" style="max-height:300px; overflow-y:auto;">
                                @if(auth()->check())
                                    @if($userNotifs->count() > 0)
                                        @for($n = 0; $n < $userNotifs->count(); $n++)
                                            @php
                                                $notification = $userNotifs[$n];
                                                $notifStatus = $notification->data['status'] ?? '';
                                                $notifLink = $notification->data['link'] ?? route('resident.request.track');
                                            @endphp
                                            <a href="{{ $notifLink }}" class="d-block text-decoration-none px-3 py-3 border-bottom r-notif-item {{ $notification->read_at === null ? 'unread' : 'read' }}">
                                                <div class="fw-bold r-notif-title" style="font-size:14px;">
                                                    @if($notifStatus === 'Rejected')
                                                        <i class="fa-solid fa-circle-xmark text-danger me-1"></i>
                                                    @elseif($notifStatus === 'Processing')
                                                        <i class="fa-solid fa-circle-check text-primary me-1"></i>
                                                    @else
                                                        <i class="fa-solid fa-circle-check text-success me-1"></i>
                                                    @endif
                                                    {{ $notification->data['document_type'] ?? ($notification->data['title'] ?? 'Document Update') }}
                                                </div>
                                                <div class="r-notif-msg text-muted mt-1" style="font-size:13px;">
                                                    {{ $notification->data['message'] ?? 'The status of your document has been updated.' }}
                                                </div>
                                                <div class="r-notif-trk mt-1" style="font-size:11px; font-weight:600;">
                                                    TRK: {{ $notification->data['tracking_number'] ?? ($notification->data['concern_id'] ?? 'N/A') }} • {{ $notification->created_at->diffForHumans() }}
                                                </div>
                                            </a>
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
        (function initResidentNotifications() {
            let latestNotifId = @json(auth()->check() && auth()->user()->notifications ? optional(auth()->user()->notifications->first())->id : null);
            let currentUnreadCount = {{ auth()->check() ? (int) auth()->user()->unreadNotifications()->count() : 0 }};

            function escapeNotifHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function renderResidentNotificationIcon(status) {
                if (status === 'Rejected') {
                    return '<i class="fa-solid fa-circle-xmark text-danger me-1"></i>';
                }
                if (status === 'Processing') {
                    return '<i class="fa-solid fa-circle-check text-primary me-1"></i>';
                }
                return '<i class="fa-solid fa-circle-check text-success me-1"></i>';
            }

            function renderResidentNotifications(data, silentToast) {
                const badge = document.getElementById('r-notif-badge');
                const list  = document.getElementById('r-notif-list');
                const unread = Number(data.unread_count || 0);

                if (badge) {
                    badge.textContent = unread;
                    badge.classList.toggle('d-none', unread <= 0);
                }

                const items = Array.isArray(data.notifications) ? data.notifications : [];
                if (list) {
                    if (items.length === 0) {
                        list.innerHTML =
                            '<div class="px-3 py-4 text-center text-muted small">' +
                            '<i class="fa-regular fa-bell-slash fa-2x mb-2 d-block" style="color:#CBD5E0;"></i>' +
                            'No new notifications</div>';
                    } else {
                        list.innerHTML = items.map(function (n) {
                            const unreadCls = n.read ? 'read' : 'unread';
                            const linkHref  = escapeNotifHtml(n.link || '{{ route("resident.request.track") }}');
                            return '<a href="' + linkHref + '" class="d-block text-decoration-none px-3 py-3 border-bottom r-notif-item ' + unreadCls + '">' +
                                '<div class="fw-bold r-notif-title" style="font-size:14px;">' +
                                    renderResidentNotificationIcon(n.status) +
                                    escapeNotifHtml(n.document_type || 'Document Update') +
                                '</div>' +
                                '<div class="r-notif-msg text-muted mt-1" style="font-size:13px;">' +
                                    escapeNotifHtml(n.message || 'The status of your document has been updated.') +
                                '</div>' +
                                '<div class="r-notif-trk mt-1" style="font-size:11px; font-weight:600;">' +
                                    'TRK: ' + escapeNotifHtml(n.tracking_number || 'N/A') + ' • ' + escapeNotifHtml(n.created_at || '') +
                                '</div>' +
                            '</a>';
                        }).join('');
                    }
                }

                // Trigger a live toast if a newer unread notification arrived
                if (items.length > 0 && !items[0].read && items[0].id && items[0].id !== latestNotifId) {
                    const newest = items[0];
                    latestNotifId = newest.id;
                    if (!silentToast && (unread > currentUnreadCount || unread > 0)) {
                        const toastType = newest.status === 'Rejected' ? 'danger' : (newest.status === 'Ready' || newest.status === 'Completed' ? 'success' : 'info');
                        if (typeof window.agToast === 'function') {
                            window.agToast(newest.document_type || 'Request Update', newest.message || 'Your request status was updated.', toastType, 6000);
                        }
                    }
                } else if (items.length > 0 && items[0].id) {
                    latestNotifId = items[0].id;
                }

                currentUnreadCount = unread;
            }

            window.fetchResidentNotifications = function (silentToast) {
                @if(auth()->check() && !session('is_locked'))
                fetch('{{ route("resident.notifications.get") }}', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (data) renderResidentNotifications(data, Boolean(silentToast));
                })
                .catch(function () {});
                @endif
            };

            window.markResidentNotificationsRead = function () {
                const badge = document.getElementById('r-notif-badge');
                if (badge) {
                    badge.textContent = '0';
                    badge.classList.add('d-none');
                }
                currentUnreadCount = 0;

                fetch('{{ route("resident.notifications.read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(function () {
                    document.querySelectorAll('#r-notif-list .r-notif-item.unread').forEach(function (el) {
                        el.classList.remove('unread');
                        el.classList.add('read');
                    });
                })
                .catch(function () {});
            };

            @if(auth()->check() && !session('is_locked'))
            setInterval(function () {
                if (!document.hidden) {
                    window.fetchResidentNotifications();
                }
            }, 12000);
            @endif
        })();
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
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="reset-chat-btn" title="New conversation" aria-label="Reset conversation">
                        <i class="fa-solid fa-rotate-left" style="font-size:13px;"></i>
                    </button>
                    <button type="button" id="close-chat-btn" title="Close" aria-label="Close chat">&times;</button>
                </div>
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
        #close-chat-btn,
        #reset-chat-btn {
            background:rgba(255,255,255,0.2);border:none;color:#fff;
            font-size:20px;width:32px;height:32px;border-radius:50%;cursor:pointer;
            display:flex;align-items:center;justify-content:center;transition:background 0.2s;
            flex-shrink:0;
        }
        #close-chat-btn:hover,
        #reset-chat-btn:hover { background:rgba(255,255,255,0.4); }
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
            const resetBtn     = document.getElementById('reset-chat-btn');
            const chatWindow   = document.getElementById('ai-chat-window');
            const sendBtn      = document.getElementById('send-chat-btn');
            const chatInput    = document.getElementById('chat-input');
            const chatBody     = document.getElementById('chat-body');
            const chatContainer= document.getElementById('ai-chat-container');

            const chatUserId = @json(auth()->check() ? (string) auth()->id() : 'guest');
            const CHAT_STORAGE_KEY = 'ebarangay_resident_istong_v1_' + chatUserId;
            const DEFAULT_GREETING = 'Kumusta! Ako ang inyong Fisherman Assistant nga si Istong. Unsa may matabang nako nimo karon kabahin sa atong barangay?';

            let conversationHistory = [];
            let savedMessages = [];
            let isChatOpen = false;
            let isSending = false;

            function readStoredChat() {
                const raw = (function () {
                    try {
                        return localStorage.getItem(CHAT_STORAGE_KEY);
                    } catch (e) {
                        try {
                            return sessionStorage.getItem(CHAT_STORAGE_KEY);
                        } catch (err) {
                            return null;
                        }
                    }
                })();

                if (!raw) return null;
                try {
                    const parsed = JSON.parse(raw);
                    return parsed && typeof parsed === 'object' ? parsed : null;
                } catch (e) {
                    return null;
                }
            }

            function saveChatState() {
                const payload = JSON.stringify({
                    messages: savedMessages.slice(-50),
                    history: conversationHistory.slice(-10),
                    isOpen: isChatOpen
                });
                try {
                    localStorage.setItem(CHAT_STORAGE_KEY, payload);
                } catch (e) {
                    try {
                        sessionStorage.setItem(CHAT_STORAGE_KEY, payload);
                    } catch (err) {}
                }
            }

            function clearChatState() {
                conversationHistory = [];
                savedMessages = [];
                chatBody.innerHTML = '';
                appendMessage(DEFAULT_GREETING, 'bot-message', false);
                saveChatState();
            }

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

            function openChat(skipFocus) {
                isChatOpen = true;
                chatWindow.style.display = 'flex';
                toggleBtn.style.display  = 'none';
                saveChatState();

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
                    chatBody.scrollTop = chatBody.scrollHeight;
                    if (!skipFocus) chatInput.focus();
                });
            }

            toggleBtn.addEventListener('click', e => {
                if (hasDragged) { e.preventDefault(); return; }
                openChat(false);
            });
            closeBtn.addEventListener('click', () => {
                isChatOpen = false;
                chatWindow.style.display = 'none';
                toggleBtn.style.display  = 'flex';
                chatContainer.style.left   = 'auto';
                chatContainer.style.top    = 'auto';
                chatContainer.style.bottom = '25px';
                chatContainer.style.right  = '25px';
                saveChatState();
            });
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    clearChatState();
                    chatInput.focus();
                });
            }

            function restoreStoredChat() {
                const stored = readStoredChat();
                if (!stored) return;

                if (Array.isArray(stored.messages) && stored.messages.length > 0) {
                    savedMessages = stored.messages
                        .filter(m => m && (m.role === 'user' || m.role === 'assistant') && typeof m.content === 'string')
                        .slice(-50);

                    if (savedMessages.length > 0) {
                        chatBody.innerHTML = '';
                        appendMessage(DEFAULT_GREETING, 'bot-message', false);
                        savedMessages.forEach(msg => {
                            appendMessage(msg.content, msg.role === 'user' ? 'user-message' : 'bot-message', false);
                        });
                    }
                }

                if (Array.isArray(stored.history)) {
                    conversationHistory = stored.history
                        .filter(m => m && (m.role === 'user' || m.role === 'assistant') && typeof m.content === 'string')
                        .slice(-10);
                }

                if (stored.isOpen === true) {
                    openChat(true);
                }
            }

            async function sendMessage() {
                if (isSending) return;
                const text = chatInput.value.trim();
                if (!text) return;

                isSending = true;
                sendBtn.disabled = true;
                appendMessage(text, 'user-message', true);
                chatInput.value = '';
                conversationHistory.push({role:'user',content:text});
                if (conversationHistory.length > 10) conversationHistory = conversationHistory.slice(-10);
                saveChatState();

                const loadingId = appendLoading();
                try {
                    const resp = await fetch("{{ route('chatbot.message') }}", {
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                        body: JSON.stringify({history:conversationHistory})
                    });
                    const data = await resp.json();
                    const loadingEl = document.getElementById(loadingId);
                    if (loadingEl) loadingEl.remove();

                    const replyText = (data && typeof data.reply === 'string' && data.reply.trim() !== '')
                        ? data.reply
                        : 'Pasensya, naputol ang koneksyon. Palihug sulayi usab.';

                    appendMessage(replyText, 'bot-message', true);
                    if (!replyText.includes('Pasensya')) {
                        conversationHistory.push({role:'assistant',content:replyText});
                        if (conversationHistory.length > 10) conversationHistory = conversationHistory.slice(-10);
                    }
                    saveChatState();
                } catch(err) {
                    const loadingEl = document.getElementById(loadingId);
                    if (loadingEl) loadingEl.remove();
                    appendMessage('Pasensya, naputol ang koneksyon. Palihug sulayi usab.', 'bot-message', true);
                    conversationHistory.pop();
                    saveChatState();
                } finally {
                    isSending = false;
                    sendBtn.disabled = false;
                    chatInput.focus();
                }
            }
            sendBtn.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', e => { if (e.key==='Enter') sendMessage(); });

            function appendMessage(text, cls, shouldPersist) {
                const d = document.createElement('div');
                d.className = 'message '+cls; d.innerText = text;
                chatBody.appendChild(d); chatBody.scrollTop = chatBody.scrollHeight;
                if (shouldPersist) {
                    savedMessages.push({
                        role: cls === 'user-message' ? 'user' : 'assistant',
                        content: text
                    });
                    if (savedMessages.length > 50) savedMessages = savedMessages.slice(-50);
                }
            }
            function appendLoading() {
                const d = document.createElement('div');
                d.className = 'message bot-message'; d.id = 'loading-'+Date.now();
                d.innerText = 'Naghunahuna...';
                chatBody.appendChild(d); chatBody.scrollTop = chatBody.scrollHeight;
                return d.id;
            }

            restoreStoredChat();
        });
    </script>
    @endif

    {{-- Toast Stack Container --}}
    <div id="agy-toast-stack"></div>

    {{-- Interactive Toast Service --}}
    <script>
    (function() {
        window.agToast = function(title, message, type = 'info', duration = 5000) {
            const stack = document.getElementById('agy-toast-stack');
            if (!stack) return;

            const iconMap = {
                success: 'fa-check',
                info:    'fa-circle-info',
                warning: 'fa-triangle-exclamation',
                danger:  'fa-circle-xmark',
            };
            const icon = iconMap[type] || 'fa-bell';

            const toast = document.createElement('div');
            toast.className = 'agy-toast';
            toast.innerHTML = `
                <div class="agy-toast-icon ${type}">
                    <i class="fa-solid ${icon}"></i>
                </div>
                <div class="agy-toast-body">
                    <div class="agy-toast-title">${title}</div>
                    <div class="agy-toast-msg">${message}</div>
                </div>
                <button class="agy-toast-close" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            function dismiss() {
                if (toast.classList.contains('hiding')) return;
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 280);
            }

            toast.querySelector('.agy-toast-close').addEventListener('click', dismiss);
            stack.appendChild(toast);

            if (duration > 0) {
                setTimeout(dismiss, duration);
            }
        };
    })();
    </script>

    {{-- Theme Switcher Logic --}}
    <script>
    (function() {
        const toggleBtn = document.getElementById('resident-theme-toggle-btn');
        const moonIcon = toggleBtn ? toggleBtn.querySelector('.icon-moon') : null;
        const sunIcon = toggleBtn ? toggleBtn.querySelector('.icon-sun') : null;

        function applyThemeState(isDark) {
            if (moonIcon && sunIcon) {
                moonIcon.classList.toggle('d-none', isDark);
                sunIcon.classList.toggle('d-none', !isDark);
            }
            if (toggleBtn) {
                toggleBtn.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                toggleBtn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
            }
        }

        const isDarkNow = document.documentElement.getAttribute('data-theme') === 'dark';
        applyThemeState(isDarkNow);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const currentlyDark = document.documentElement.getAttribute('data-theme') === 'dark';
                const nextDark = !currentlyDark;
                if (nextDark) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    document.documentElement.removeAttribute('data-bs-theme');
                }
                try {
                    localStorage.setItem('ebarangay_theme', nextDark ? 'dark' : 'light');
                } catch(err) {}
                applyThemeState(nextDark);
            });
        }
    })();
    </script>

    {{-- Modal Stacking Context & Body Portal Fix --}}
    <script>
    document.addEventListener('show.bs.modal', function(e) {
        const modal = e.target;
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
    </script>

    {{-- Real-Time WebSocket Client Connection --}}
    <script>
    (function initResidentWebSocket() {
        const currentUserId = {{ auth()->check() ? (int) auth()->id() : 'null' }};
        const host = window.location.hostname || '127.0.0.1';
        const isLocalHost = host === 'localhost' || host === '127.0.0.1';
        // Avoid Mixed Content ws:// connections when served over HTTPS in production
        if (window.location.protocol === 'https:' && !isLocalHost) {
            return;
        }

        const wsUrl = `ws://${host}:8080`;
        let socket = null;
        let reconnectDelay = 2000;

        function connect() {
            try {
                socket = new WebSocket(wsUrl);

                socket.onopen = function () {
                    reconnectDelay = 2000;
                    console.log('⚡ Resident Connected to e-Barangay WebSocket Server');
                    document.dispatchEvent(new CustomEvent('resident:ws-connected'));
                };

                socket.onmessage = function (event) {
                    try {
                        const payload = JSON.parse(event.data);
                        if (payload.type === 'pong' || payload.event === 'connected') return;

                        console.log('📡 Real-time WebSocket event received:', payload);

                        // Broadcast custom event so resident sub-views (e.g. track_request, dashboard) can auto-refresh
                        document.dispatchEvent(new CustomEvent('resident:realtime-event', { detail: payload }));

                        if (payload.event === 'request.status_changed') {
                            const d = payload.data || {};
                            if (d.user_id && currentUserId && Number(d.user_id) !== currentUserId) {
                                return;
                            }

                            const type = d.status === 'Rejected' ? 'danger' : (d.status === 'Ready' || d.status === 'Completed' ? 'success' : 'info');

                            // Toast notification for status change
                            if (window.agToast) {
                                window.agToast(
                                    `Request #${d.tracking_number} Updated`,
                                    d.message || `Your document request is now ${d.status}.`,
                                    type,
                                    6000
                                );
                            }

                            if (typeof window.fetchResidentNotifications === 'function') {
                                window.fetchResidentNotifications(true);
                            }

                            // Refresh Track Requests counter badge
                            const trBadge = document.getElementById('rTrackRequestsBadge');
                            if (trBadge) {
                                fetch("{{ route('resident.requests.check') }}")
                                    .then(r => r.json())
                                    .then(res => {
                                        if (res && res.active_count !== undefined) {
                                            trBadge.textContent = res.active_count;
                                            if (res.active_count > 0) {
                                                trBadge.classList.remove('d-none');
                                            } else {
                                                trBadge.classList.add('d-none');
                                            }
                                        }
                                    })
                                    .catch(() => {});
                            }
                        }
                    } catch (e) {
                        console.error('Error handling WebSocket message:', e);
                    }
                };

                socket.onclose = function () {
                    document.dispatchEvent(new CustomEvent('resident:ws-disconnected'));
                    setTimeout(connect, reconnectDelay);
                    reconnectDelay = Math.min(reconnectDelay * 1.5, 30000);
                };

                socket.onerror = function () {
                    socket.close();
                };
            } catch (err) {
                setTimeout(connect, reconnectDelay);
            }
        }

        connect();

        setInterval(function () {
            if (socket && socket.readyState === WebSocket.OPEN) {
                socket.send(JSON.stringify({ type: 'ping' }));
            }
        }, 30000);
    })();
    </script>

    <!-- Page Transition Loader Script -->
    <script>
    (function () {
        var bar         = document.getElementById('page-loader-bar');
        var fill        = document.getElementById('page-loader-fill');
        var blurOverlay = document.getElementById('page-blur-overlay');
        var loadTimer   = null;
        var blurTimer   = null;

        function startLoader(link) {
            if (bar && fill) {
                clearTimeout(loadTimer);
                fill.style.transition = 'none';
                fill.style.width = '0%';
                bar.classList.add('loading');
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        fill.style.transition = 'width 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
                        fill.style.width = '75%';
                        loadTimer = setTimeout(function () {
                            fill.style.width = '90%';
                        }, 1500);
                    });
                });
            }

            // Screen blur overlay with slight load animation
            if (blurOverlay) {
                clearTimeout(blurTimer);
                blurOverlay.classList.add('loading');
                blurTimer = setTimeout(function () {
                    blurOverlay.classList.remove('loading');
                }, 8000);
            }

            // Sidebar loading circle at the right
            if (link) {
                var sidebarItem = link.closest('.r-sidebar-nav-item, .sidebar-nav-item');
                if (sidebarItem) {
                    document.querySelectorAll('.sidebar-nav-spinner').forEach(function (el) { el.remove(); });
                    document.querySelectorAll('.sidebar-badge-loading-hidden').forEach(function (el) {
                        el.classList.remove('sidebar-badge-loading-hidden');
                        el.style.display = '';
                    });

                    var badge = sidebarItem.querySelector('.r-sidebar-badge, .sidebar-badge');
                    if (badge && window.getComputedStyle(badge).display !== 'none') {
                        badge.classList.add('sidebar-badge-loading-hidden');
                        badge.style.display = 'none';
                    }

                    var spinner = document.createElement('span');
                    spinner.className = 'sidebar-nav-spinner';
                    spinner.setAttribute('aria-hidden', 'true');
                    sidebarItem.appendChild(spinner);
                }
            }
        }

        function finishLoader() {
            if (bar && fill) {
                clearTimeout(loadTimer);
                fill.style.transition = 'width 0.2s ease';
                fill.style.width = '100%';
                setTimeout(function () {
                    bar.classList.remove('loading');
                    fill.style.transition = 'none';
                    fill.style.width = '0%';
                }, 300);
            }

            if (blurOverlay) {
                clearTimeout(blurTimer);
                blurOverlay.classList.remove('loading');
            }

            document.querySelectorAll('.sidebar-nav-spinner').forEach(function (el) { el.remove(); });
            document.querySelectorAll('.sidebar-badge-loading-hidden').forEach(function (el) {
                el.classList.remove('sidebar-badge-loading-hidden');
                el.style.display = '';
            });
        }

        // Intercept navigation clicks
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[href]');
            if (!link) return;
            var href = link.getAttribute('href');
            if (!href || href === '' || href.startsWith('#') ||
                href.startsWith('javascript') || link.target === '_blank' ||
                e.ctrlKey || e.metaKey || e.shiftKey) return;
            try {
                var url = new URL(href, window.location.origin);
                if (url.origin !== window.location.origin) return;
                if (url.pathname === window.location.pathname &&
                    url.search  === window.location.search) return;
            } catch (err) { return; }
            startLoader(link);
        });

        // Finish when new page DOM is ready
        window.addEventListener('pageshow', finishLoader);
        document.addEventListener('DOMContentLoaded', finishLoader);
    })();
    </script>

</body>
</html>