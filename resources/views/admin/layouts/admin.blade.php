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
        /* Sidebar Variables — Dark Theme */
        :root {
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 78px;
            --sidebar-bg:        #0F2240;
            --sidebar-bg-alt:    #0A1829;
            --sidebar-border:    rgba(255, 255, 255, 0.07);
            --sidebar-text:      #A0B5CC;
            --sidebar-text-hover: #ffffff;
            --sidebar-text-active: #ffffff;
            --sidebar-category:  #5A7A9A;
        }

        /* ═══════════════════════════════════════════════════════
           GLOBAL ANIMATION SYSTEM
           ═══════════════════════════════════════════════════════ */

        /* Keyframe library */
        @keyframes agy-fadeSlideUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes agy-fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes agy-scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes agy-slideInLeft {
            from { opacity: 0; transform: translateX(-18px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes agy-slideInRight {
            from { opacity: 0; transform: translateX(18px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes agy-pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.5); }
            70%  { box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
        }
        @keyframes agy-shimmer {
            0%   { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Page content fade-in on load (pure opacity to avoid stacking context traps for modals) */
        #page-content-wrapper {
            animation: agy-fadeIn 0.32s ease-out;
        }

        /* Guarantee Bootstrap modals sit cleanly above backdrop and are 100% interactive */
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }
        body.modal-open #page-content-wrapper,
        body.modal-open #wrapper {
            transform: none !important;
            filter: none !important;
        }

        /* Sidebar nav items stagger on load */
        .sidebar-nav-item {
            animation: agy-slideInLeft 0.32s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .sidebar-nav-item:nth-child(1)  { animation-delay: 0.04s; }
        .sidebar-nav-item:nth-child(2)  { animation-delay: 0.07s; }
        .sidebar-nav-item:nth-child(3)  { animation-delay: 0.10s; }
        .sidebar-nav-item:nth-child(4)  { animation-delay: 0.13s; }
        .sidebar-nav-item:nth-child(5)  { animation-delay: 0.16s; }
        .sidebar-nav-item:nth-child(6)  { animation-delay: 0.19s; }
        .sidebar-nav-item:nth-child(7)  { animation-delay: 0.22s; }
        .sidebar-nav-item:nth-child(8)  { animation-delay: 0.25s; }
        .sidebar-nav-item:nth-child(9)  { animation-delay: 0.28s; }
        .sidebar-nav-item:nth-child(n+10) { animation-delay: 0.30s; }

        /* Sidebar hover transition */
        #sidebar-wrapper {
            transition: width 0.28s cubic-bezier(0.22, 1, 0.36, 1),
                        transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }

        /* Top-bar nav buttons pop on hover */
        .notif-bell-btn,
        #admin-theme-toggle-btn {
            transition: transform 0.18s ease, background 0.18s ease, color 0.18s ease !important;
        }
        .notif-bell-btn:hover,
        #admin-theme-toggle-btn:hover {
            transform: scale(1.1) !important;
        }

        /* Bootstrap modal enhanced: scale + fade */
        .modal.fade .modal-dialog {
            transform: scale(0.95) translateY(-8px);
            transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.28s ease !important;
        }
        .modal.show .modal-dialog {
            transform: scale(1) translateY(0);
        }

        /* Card generic hover lift */
        .card {
            transition: box-shadow 0.22s ease, transform 0.22s ease;
        }
        .card:not(.no-hover-lift):hover {
            box-shadow: 0 6px 24px rgba(0,0,0,0.09) !important;
        }

        /* Live status indicator dot */
        .live-status-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6);
            animation: agy-liveBlip 2.2s infinite;
        }
        @keyframes agy-liveBlip {
            0%   { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
            60%  { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        /* Toast notification */
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
        @keyframes agy-toastIn {
            from { opacity: 0; transform: translateX(28px) scale(0.95); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes agy-toastOut {
            from { opacity: 1; transform: translateX(0) scale(1); }
            to   { opacity: 0; transform: translateX(28px) scale(0.95); }
        }
        .agy-toast-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
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

        html[data-theme="dark"] .agy-toast,
        html[data-bs-theme="dark"] .agy-toast {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 8px 32px rgba(0,0,0,0.45);
        }
        html[data-theme="dark"] .agy-toast-title,
        html[data-bs-theme="dark"] .agy-toast-title { color: #f1f5f9; }
        html[data-theme="dark"] .agy-toast-msg,
        html[data-bs-theme="dark"] .agy-toast-msg   { color: #94a3b8; }
        html[data-theme="dark"] .agy-toast-close,
        html[data-bs-theme="dark"] .agy-toast-close { color: #64748b; }

        /* Prevent Transition Flash / Animation Lag on Page Load */
        .no-transition, 
        .no-transition *, 
        .no-transition #sidebar-wrapper, 
        .no-transition #page-content-wrapper {
            transition: none !important;
            animation: none !important;
        }

        body {
            background-color: var(--color-bg);
            font-family: var(--font-body);
            overflow-x: hidden;
        }

        /* Wrapper Setup */
        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            overflow-x: clip;
            position: relative;
        }

        /* Sidebar Base Styling */
        #sidebar-wrapper {
            min-height: 100vh;
            height: 100%;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: none;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1), transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            box-shadow: none;
        }

        /* Sidebar Brand Header with Burger inside */
        .sidebar-heading {
            padding: 1.15rem 1rem 1.15rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--sidebar-border);
            background: rgba(0, 0, 0, 0.15);
            flex-shrink: 0;
            height: 72px;
            gap: 0.5rem;
            transition: padding 0.25s ease, justify-content 0.25s ease;
        }

        .sidebar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            min-width: 0;
        }

        .sidebar-brand-icon {
            width: 45px;
            height: 45px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            contain: strict;
        }

        .sidebar-brand-img {
            width: 45px;
            height: 45px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .sidebar-brand-text {
            min-width: 0;
            transition: opacity 0.2s ease, width 0.2s ease;
        }

        .sidebar-brand-text h4 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            color: #ffffff;
            font-family: var(--font-heading);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .sidebar-brand-text p {
            font-size: 0.72rem;
            margin: 0;
            color: #38bdf8;
            font-weight: 500;
            letter-spacing: 0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-header-toggle {
            width: 36px;
            height: 36px;
            border-radius: 99px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
            padding: 0;
        }

        .sidebar-header-toggle:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
        }

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
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-category-label:first-child {
            margin-top: 0;
            padding-top: 0.25rem;
        }

        .sidebar-nav-item {
            display: flex;
            align-items: center;
            padding: 0.62rem 0.75rem;
            margin-bottom: 4px;
            color: var(--sidebar-text);
            font-weight: 500;
            font-size: 0.86rem;
            border-radius: 12px;
            text-decoration: none;
            transition: background-color 0.15s ease, color 0.15s ease;
            position: relative;
            white-space: nowrap;
        }

        .sidebar-nav-item i.nav-icon {
            width: 24px;
            font-size: 0.98rem;
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
            transition: opacity 0.2s ease;
        }

        .sidebar-nav-item:hover {
            background-color: rgba(255, 255, 255, 0.07);
            color: var(--sidebar-text-hover);
        }

        .sidebar-nav-item:hover i.nav-icon {
            color: #4FD1C5;
            transform: scale(1.08);
        }

        .sidebar-nav-item.active {
            background-color: var(--color-bg, #F8FAFC);
            color: #0F2240 !important;
            font-weight: 700;
            border-radius: 20px 0 0 20px;
            margin-right: -0.55rem;
            padding-right: 1rem;
            box-shadow: none;
            z-index: 2;
        }

        .sidebar-nav-item.active i.nav-icon {
            color: #2563EB !important;
            transform: scale(1.05);
        }

        .sidebar-nav-item.active .nav-text {
            color: #0F2240 !important;
        }

        .sidebar-nav-item.active::before {
            content: '';
            position: absolute;
            top: -16px;
            right: 0;
            width: 16px;
            height: 16px;
            border-bottom-right-radius: 16px;
            box-shadow: 6px 6px 0 6px var(--color-bg, #F8FAFC);
            background: transparent;
            pointer-events: none;
        }

        .sidebar-nav-item.active::after {
            content: '';
            position: absolute;
            bottom: -16px;
            right: 0;
            width: 16px;
            height: 16px;
            border-top-right-radius: 16px;
            box-shadow: 6px -6px 0 6px var(--color-bg, #F8FAFC);
            background: transparent;
            pointer-events: none;
        }

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

        .sidebar-badge.badge-warning {
            background: #D69E2E;
            color: #ffffff;
        }

        /* SIDEBAR USER FOOTER (EXACT MATCH TO RESIDENT PORTAL) */
        .sidebar-user-footer {
            flex-shrink: 0;
            border-top: 1px solid var(--sidebar-border);
            padding: 0.85rem 1rem;
            background: rgba(0, 0, 0, 0.22);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
            transition: padding 0.25s ease;
            margin-top: auto;
        }

        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex: 1 1 0;
            min-width: 0;
            overflow: hidden;
        }

        .sidebar-user-info {
            min-width: 0;
            flex: 1 1 auto;
            overflow: hidden;
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

        .sidebar-user-avatar.img-cover {
            object-fit: cover;
        }

        .sidebar-user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.72rem;
            color: #A0B5CC;
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
            color: #A0B5CC;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
            padding: 0;
        }

        .sidebar-logout-btn:hover {
            background: rgba(229, 62, 62, 0.15);
            color: #FC8181;
        }

        body.sidebar-collapsed {
            --sidebar-width: var(--sidebar-width-collapsed);
        }

        body.sidebar-collapsed #sidebar-wrapper {
            width: var(--sidebar-width-collapsed);
        }

        body.sidebar-collapsed #page-content-wrapper {
            margin-left: var(--sidebar-width-collapsed) !important;
            width: calc(100% - var(--sidebar-width-collapsed)) !important;
            max-width: calc(100% - var(--sidebar-width-collapsed)) !important;
        }

        body.sidebar-collapsed .sidebar-brand-wrapper,
        body.sidebar-collapsed .sidebar-brand-icon,
        body.sidebar-collapsed .sidebar-brand-text,
        body.sidebar-collapsed .nav-text,
        body.sidebar-collapsed .sidebar-category-label,
        body.sidebar-collapsed .sidebar-badge {
            display: none !important;
        }

        body.sidebar-collapsed .sidebar-heading {
            padding: 1.15rem 0.5rem;
            justify-content: center;
        }

        body.sidebar-collapsed .sidebar-nav-scroll {
            padding: 0.65rem 0.4rem;
        }

        body.sidebar-collapsed .sidebar-nav-item {
            justify-content: center;
            padding: 0.65rem 0;
            border-radius: 10px;
        }

        body.sidebar-collapsed .sidebar-nav-item.active {
            margin-right: -0.4rem;
            border-radius: 16px 0 0 16px;
            padding-right: 0;
        }

        body.sidebar-collapsed .sidebar-nav-item i.nav-icon {
            margin-right: 0 !important;
            font-size: 1.1rem;
        }

        body.sidebar-collapsed .sidebar-user-footer {
            padding: 0.85rem 0.4rem;
            justify-content: center;
        }

        body.sidebar-collapsed .sidebar-user-info,
        body.sidebar-collapsed .sidebar-logout-btn {
            display: none !important;
        }

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

        /* Topbar Sidebar Toggle Button (Hidden on web/desktop, visible on responsive mode < 992px) */
        .topbar-sidebar-toggle-btn {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #ffffff;
            border: 1px solid var(--color-border, #e2e8f0);
            color: #475569;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            font-size: 16px;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .topbar-sidebar-toggle-btn:hover {
            background: #f1f5f9;
            color: #2563eb;
            border-color: #cbd5e1;
            transform: scale(1.04);
        }
        .topbar-sidebar-toggle-btn:active {
            transform: scale(0.96);
        }

        html[data-theme="dark"] .topbar-sidebar-toggle-btn,
        html[data-bs-theme="dark"] .topbar-sidebar-toggle-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
        }
        html[data-theme="dark"] .topbar-sidebar-toggle-btn:hover,
        html[data-bs-theme="dark"] .topbar-sidebar-toggle-btn:hover {
            background: #283548 !important;
            color: #60a5fa !important;
            border-color: #475569 !important;
        }

        @media (max-width: 991.98px) {
            :root {
                --sidebar-width: 260px;
            }

            .topbar-sidebar-toggle-btn {
                display: inline-flex !important;
            }

            body.sidebar-collapsed #sidebar-wrapper,
            #sidebar-wrapper {
                width: 260px !important;
                margin-left: -260px !important;
                transition: margin-left 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
                z-index: 1050;
            }

            body.sidebar-collapsed #sidebar-wrapper.toggled,
            #sidebar-wrapper.toggled {
                margin-left: 0 !important;
            }

            /* Ensure all sidebar elements are fully visible in mobile drawer even if collapsed was stored */
            body.sidebar-collapsed #sidebar-wrapper .sidebar-brand-text,
            body.sidebar-collapsed #sidebar-wrapper .nav-text,
            body.sidebar-collapsed #sidebar-wrapper .sidebar-category-label,
            body.sidebar-collapsed #sidebar-wrapper .sidebar-user-info,
            body.sidebar-collapsed #sidebar-wrapper .sidebar-badge {
                display: initial !important;
            }

            /* Change burger icon in sidebar heading to X mark on mobile when opened */
            #sidebar-wrapper.toggled .sidebar-header-toggle i::before {
                content: "\f00d"; /* font-awesome xmark */
            }

            #page-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
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

            .admin-header {
                padding: 0.85rem 1rem !important;
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

        /* General Card & Table Styling */
        .card { border: none; box-shadow: var(--shadow-lg); border-radius: var(--radius-lg); }
        .card-header { background-color: var(--color-surface); border-bottom: 1px solid var(--color-border); padding: 1.5rem; font-weight: 700; color: var(--color-text); font-family: var(--font-heading); }
        .table > :not(caption) > * > * { padding: 1rem 1.5rem; }
        .table thead th { font-size: 0.75rem; font-weight: 700; color: var(--color-text-light); background-color: var(--color-bg-alt); border-bottom: none; text-transform: uppercase; letter-spacing: 0.5px; }
        .table tbody td { font-size: 0.9rem; color: var(--color-text); vertical-align: middle; }
        .badge { font-weight: 700; padding: 0.5em 0.75em; border-radius: 30px; }

        h1, h2, h3, h4, h5, h6, .header-title { font-family: var(--font-heading); }

        /* NOTIFICATION BELL */
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
            top: -4px;
            right: -6px;
            background: #ef4444;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            padding: 0 5px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            line-height: 1;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            pointer-events: none;
            z-index: 2;
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
        .notif-item.unread:hover { background: #e0e0ff; }

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
    
        /* ═══════════════════════════════════════════════════════════
           ADMIN DASHBOARD GLOBAL DARK MODE & ACCESSIBLE CONTRAST
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

            /* Bootstrap 5.3 Overrides to Kill Off-Whites & Fix Text */
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

        /* ── Sidebar Active Nav Item (High Contrast Fix) ── */
        html[data-theme="dark"] .sidebar-nav-item.active,
        html[data-bs-theme="dark"] .sidebar-nav-item.active {
            background-color: #0b1120 !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .sidebar-nav-item.active .nav-text,
        html[data-bs-theme="dark"] .sidebar-nav-item.active .nav-text {
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        html[data-theme="dark"] .sidebar-nav-item.active i.nav-icon,
        html[data-bs-theme="dark"] .sidebar-nav-item.active i.nav-icon {
            color: #38bdf8 !important; /* Vivid sky blue */
        }
        html[data-theme="dark"] .sidebar-nav-item.active::before,
        html[data-bs-theme="dark"] .sidebar-nav-item.active::before {
            box-shadow: 6px 6px 0 6px #0b1120 !important;
        }
        html[data-theme="dark"] .sidebar-nav-item.active::after,
        html[data-bs-theme="dark"] .sidebar-nav-item.active::after {
            box-shadow: 6px -6px 0 6px #0b1120 !important;
        }

        /* ── Top Bar Theme Toggle Button ── */
        .theme-toggle-top-btn {
            position: relative;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid var(--color-border, #e5e7eb);
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            color: #64748b;
            font-size: 16px;
            padding: 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            text-decoration: none;
        }
        .theme-toggle-top-btn:hover {
            background: #f1f5f9;
            color: #f59e0b;
            transform: scale(1.05);
        }
        .theme-toggle-top-btn:active {
            transform: scale(0.95);
        }
        html[data-theme="dark"] .theme-toggle-top-btn,
        html[data-bs-theme="dark"] .theme-toggle-top-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #fbbf24 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4) !important;
        }
        html[data-theme="dark"] .theme-toggle-top-btn:hover,
        html[data-bs-theme="dark"] .theme-toggle-top-btn:hover {
            background: #283548 !important;
            color: #fde047 !important;
        }

        /* ── Admin Header & Navigation ── */
        html[data-theme="dark"] .admin-header,
        html[data-bs-theme="dark"] .admin-header {
            background: #0f172a !important;
            border-bottom-color: #1e293b !important;
        }
        html[data-theme="dark"] .header-page-title,
        html[data-bs-theme="dark"] .header-page-title {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .header-breadcrumb a,
        html[data-bs-theme="dark"] .header-breadcrumb a {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .header-breadcrumb a:hover,
        html[data-bs-theme="dark"] .header-breadcrumb a:hover {
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .breadcrumb-current,
        html[data-bs-theme="dark"] .breadcrumb-current {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .breadcrumb-separator,
        html[data-bs-theme="dark"] .breadcrumb-separator {
            color: #475569 !important;
        }

        /* ── Notification Bell & Dropdown ── */
        html[data-theme="dark"] .notif-bell-btn,
        html[data-bs-theme="dark"] .notif-bell-btn {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .notif-bell-btn:hover,
        html[data-bs-theme="dark"] .notif-bell-btn:hover {
            background: #283548 !important;
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .notif-badge,
        html[data-bs-theme="dark"] .notif-badge {
            border-color: #1e293b !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.5) !important;
        }
        html[data-theme="dark"] .notif-dropdown,
        html[data-bs-theme="dark"] .notif-dropdown {
            background: #131d31 !important;
            border-color: #1e293b !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6) !important;
        }
        html[data-theme="dark"] .notif-header,
        html[data-bs-theme="dark"] .notif-header {
            border-bottom-color: #1e293b !important;
            background: #182238 !important;
        }
        html[data-theme="dark"] .notif-header h6,
        html[data-bs-theme="dark"] .notif-header h6 {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] #mark-all-read,
        html[data-bs-theme="dark"] #mark-all-read {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] #mark-all-read:hover,
        html[data-bs-theme="dark"] #mark-all-read:hover {
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .notif-item,
        html[data-bs-theme="dark"] .notif-item {
            background: #131d31 !important;
            border-bottom-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .notif-item:hover,
        html[data-bs-theme="dark"] .notif-item:hover {
            background: #182238 !important;
        }
        html[data-theme="dark"] .notif-item.unread,
        html[data-bs-theme="dark"] .notif-item.unread {
            background: #16243d !important;
            border-bottom-color: #1e293b !important;
        }
        html[data-theme="dark"] .notif-item.unread:hover,
        html[data-bs-theme="dark"] .notif-item.unread:hover {
            background: #1d2e4e !important;
        }
        html[data-theme="dark"] .notif-msg,
        html[data-bs-theme="dark"] .notif-msg {
            color: #f1f5f9 !important;
            font-weight: 500;
        }
        html[data-theme="dark"] .notif-msg strong,
        html[data-bs-theme="dark"] .notif-msg strong {
            color: #ffffff !important;
        }
        html[data-theme="dark"] .notif-time,
        html[data-bs-theme="dark"] .notif-time {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .notif-icon.type-new_registration,
        html[data-bs-theme="dark"] .notif-icon.type-new_registration {
            background: rgba(34, 197, 94, 0.22) !important;
            color: #4ade80 !important;
        }
        html[data-theme="dark"] .notif-icon.type-new_request,
        html[data-bs-theme="dark"] .notif-icon.type-new_request {
            background: rgba(37, 99, 235, 0.25) !important;
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .notif-icon.type-new_concern,
        html[data-bs-theme="dark"] .notif-icon.type-new_concern {
            background: rgba(245, 158, 11, 0.22) !important;
            color: #fbbf24 !important;
        }
        html[data-theme="dark"] .notif-icon.type-info,
        html[data-bs-theme="dark"] .notif-icon.type-info {
            background: rgba(148, 163, 184, 0.2) !important;
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .notif-item .notif-unread-dot,
        html[data-bs-theme="dark"] .notif-item .notif-unread-dot {
            background: #38bdf8 !important;
            box-shadow: 0 0 6px rgba(56, 189, 248, 0.6) !important;
        }
        html[data-theme="dark"] .notif-footer,
        html[data-bs-theme="dark"] .notif-footer {
            border-top-color: #1e293b !important;
            background: #182238 !important;
        }
        html[data-theme="dark"] .notif-footer a,
        html[data-bs-theme="dark"] .notif-footer a {
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .notif-footer a:hover,
        html[data-bs-theme="dark"] .notif-footer a:hover {
            color: #93c5fd !important;
        }
        html[data-theme="dark"] .notif-empty,
        html[data-bs-theme="dark"] .notif-empty {
            color: #94a3b8 !important;
        }

        /* ── Global Cards & Containers (NO MORE OFF-WHITES) ── */
        html[data-theme="dark"] body,
        html[data-bs-theme="dark"] body {
            background-color: var(--color-bg) !important;
            color: var(--color-text) !important;
        }

        html[data-theme="dark"] .card,
        html[data-bs-theme="dark"] .card,
        html[data-theme="dark"] .card-body,
        html[data-bs-theme="dark"] .card-body,
        html[data-theme="dark"] .bg-white,
        html[data-bs-theme="dark"] .bg-white,
        html[data-theme="dark"] .modal-content,
        html[data-bs-theme="dark"] .modal-content,
        html[data-theme="dark"] .offcanvas,
        html[data-bs-theme="dark"] .offcanvas {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }

        html[data-theme="dark"] .card-header,
        html[data-bs-theme="dark"] .card-header,
        html[data-theme="dark"] .card-header.bg-white,
        html[data-bs-theme="dark"] .card-header.bg-white,
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

        /* ── Global Table Dark Mode (Eliminates ALL white rows across all panels) ── */
        html[data-theme="dark"] .table,
        html[data-bs-theme="dark"] .table,
        html[data-theme="dark"] table,
        html[data-bs-theme="dark"] table {
            --bs-table-bg: #131d31 !important;
            --bs-table-accent-bg: transparent !important;
            background-color: #131d31 !important;
            color: #f1f5f9 !important;
            border-color: #1e293b !important;
        }

        html[data-theme="dark"] .table > :not(caption) > * > *,
        html[data-bs-theme="dark"] .table > :not(caption) > * > *,
        html[data-theme="dark"] .table tbody td,
        html[data-bs-theme="dark"] .table tbody td,
        html[data-theme="dark"] .table tbody th,
        html[data-bs-theme="dark"] .table tbody th,
        html[data-theme="dark"] table tbody td,
        html[data-bs-theme="dark"] table tbody td {
            background-color: #131d31 !important;
            color: #e2e8f0 !important;
            border-color: #1e293b !important;
            box-shadow: none !important;
        }

        html[data-theme="dark"] .table thead th,
        html[data-bs-theme="dark"] .table thead th,
        html[data-theme="dark"] table thead th,
        html[data-bs-theme="dark"] table thead th,
        html[data-theme="dark"] .table thead,
        html[data-bs-theme="dark"] .table thead {
            background-color: #10192a !important;
            color: #cbd5e1 !important;
            border-color: #1e293b !important;
        }

        html[data-theme="dark"] .table-hover tbody tr:hover > *,
        html[data-bs-theme="dark"] .table-hover tbody tr:hover > *,
        html[data-theme="dark"] .table-hover > tbody > tr:hover > *,
        html[data-bs-theme="dark"] .table-hover > tbody > tr:hover > * {
            background-color: #19253d !important;
            color: #ffffff !important;
            --bs-table-accent-bg: #19253d !important;
        }

        html[data-theme="dark"] .table-striped tbody tr:nth-of-type(odd) > *,
        html[data-bs-theme="dark"] .table-striped tbody tr:nth-of-type(odd) > * {
            background-color: #162238 !important;
        }

        html[data-theme="dark"] .table-responsive,
        html[data-bs-theme="dark"] .table-responsive {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
        }

        /* ── Empty States (Fixes blinding white rectangle on empty tables) ── */
        html[data-theme="dark"] .table tbody tr td[colspan],
        html[data-bs-theme="dark"] .table tbody tr td[colspan],
        html[data-theme="dark"] .table td.text-center.py-5,
        html[data-bs-theme="dark"] .table td.text-center.py-5 {
            background-color: #131d31 !important;
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .table td.text-center.py-5 i.text-light,
        html[data-bs-theme="dark"] .table td.text-center.py-5 i.text-light {
            color: #475569 !important;
        }

        /* ── Metric / Filter Cards in User Management & Document Management ── */
        html[data-theme="dark"] .user-filter-card,
        html[data-bs-theme="dark"] .user-filter-card,
        html[data-theme="dark"] .mobile-user-card,
        html[data-bs-theme="dark"] .mobile-user-card,
        html[data-theme="dark"] .mobile-doc-card,
        html[data-bs-theme="dark"] .mobile-doc-card,
        html[data-theme="dark"] .mobile-concern-card,
        html[data-bs-theme="dark"] .mobile-concern-card {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }

        html[data-theme="dark"] .user-filter-card:hover,
        html[data-bs-theme="dark"] .user-filter-card:hover {
            background-color: #182238 !important;
            border-color: #3b82f6 !important;
        }

        html[data-theme="dark"] .user-filter-card.active,
        html[data-bs-theme="dark"] .user-filter-card.active {
            background-color: rgba(37, 99, 235, 0.2) !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3) !important;
        }

        html[data-theme="dark"] .card-label-text,
        html[data-bs-theme="dark"] .card-label-text {
            color: #cbd5e1 !important;
        }

        html[data-theme="dark"] .card-count-text,
        html[data-bs-theme="dark"] .card-count-text {
            color: #ffffff !important;
        }

        /* ── Form Controls, Search Inputs & Filters ── */
        html[data-theme="dark"] .control-search-input,
        html[data-bs-theme="dark"] .control-search-input,
        html[data-theme="dark"] .form-control,
        html[data-bs-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select,
        html[data-bs-theme="dark"] .form-select {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .control-search-input:focus,
        html[data-bs-theme="dark"] .control-search-input:focus,
        html[data-theme="dark"] .form-control:focus,
        html[data-bs-theme="dark"] .form-control:focus,
        html[data-theme="dark"] .form-select:focus,
        html[data-bs-theme="dark"] .form-select:focus {
            background-color: #1e2b45 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        }

        html[data-theme="dark"] .control-search-input::placeholder,
        html[data-bs-theme="dark"] .control-search-input::placeholder,
        html[data-theme="dark"] .form-control::placeholder,
        html[data-bs-theme="dark"] .form-control::placeholder {
            color: #64748b !important;
        }

        html[data-theme="dark"] .btn-filter-trigger,
        html[data-bs-theme="dark"] .btn-filter-trigger {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }

        html[data-theme="dark"] .btn-filter-trigger:hover,
        html[data-bs-theme="dark"] .btn-filter-trigger:hover {
            background-color: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }

        html[data-theme="dark"] .input-group-text,
        html[data-bs-theme="dark"] .input-group-text {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        html[data-theme="dark"] .dropdown-menu,
        html[data-bs-theme="dark"] .dropdown-menu {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.45) !important;
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

        html[data-theme="dark"] .dropdown-divider,
        html[data-bs-theme="dark"] .dropdown-divider {
            border-color: #1e293b !important;
        }

        /* ── Accessible High-Contrast Typography ── */
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

        html[data-theme="dark"] p, html[data-bs-theme="dark"] p,
        html[data-theme="dark"] span, html[data-bs-theme="dark"] span,
        html[data-theme="dark"] label, html[data-bs-theme="dark"] label {
            color: inherit;
        }

        html[data-theme="dark"] .text-muted,
        html[data-bs-theme="dark"] .text-muted,
        html[data-theme="dark"] .text-secondary,
        html[data-bs-theme="dark"] .text-secondary,
        html[data-theme="dark"] small.text-muted,
        html[data-bs-theme="dark"] small.text-muted {
            color: #94a3b8 !important;
        }

        html[data-theme="dark"] .font-monospace,
        html[data-bs-theme="dark"] .font-monospace,
        html[data-theme="dark"] .font-mono-id,
        html[data-bs-theme="dark"] .font-mono-id {
            color: #93c5fd !important;
        }

        /* ── Action Buttons in Tables ── */
        html[data-theme="dark"] .btn-table-action,
        html[data-bs-theme="dark"] .btn-table-action {
            color: #93c5fd !important;
        }
        html[data-theme="dark"] .btn-table-action:hover,
        html[data-bs-theme="dark"] .btn-table-action:hover {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .btn-edit-user,
        html[data-bs-theme="dark"] .btn-edit-user {
            color: #f59e0b !important;
        }
        html[data-theme="dark"] .btn-edit-user i,
        html[data-bs-theme="dark"] .btn-edit-user i {
            color: #f59e0b !important;
        }
        html[data-theme="dark"] .btn-view-user,
        html[data-bs-theme="dark"] .btn-view-user {
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .btn-view-user i,
        html[data-bs-theme="dark"] .btn-view-user i {
            color: #60a5fa !important;
        }

        /* ── Global Badges & Buttons in Dark Mode ── */
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
        html[data-theme="dark"] .btn-light.text-dark,
        html[data-bs-theme="dark"] .btn-light.text-dark,
        html[data-theme="dark"] .btn-light .text-dark,
        html[data-bs-theme="dark"] .btn-light .text-dark {
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] .btn-light i,
        html[data-bs-theme="dark"] .btn-light i,
        html[data-theme="dark"] .btn-light .text-muted,
        html[data-bs-theme="dark"] .btn-light .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .bg-light,
        html[data-bs-theme="dark"] .bg-light {
            background-color: #182238 !important;
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .badge.bg-light,
        html[data-bs-theme="dark"] .badge.bg-light {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        /* Contextual Badges with Opacity */
        html[data-theme="dark"] .bg-primary.bg-opacity-10,
        html[data-bs-theme="dark"] .bg-primary.bg-opacity-10 {
            background-color: rgba(59, 130, 246, 0.2) !important;
        }
        html[data-theme="dark"] .bg-success.bg-opacity-10,
        html[data-bs-theme="dark"] .bg-success.bg-opacity-10 {
            background-color: rgba(34, 197, 94, 0.2) !important;
        }
        html[data-theme="dark"] .bg-warning.bg-opacity-10,
        html[data-bs-theme="dark"] .bg-warning.bg-opacity-10 {
            background-color: rgba(245, 158, 11, 0.2) !important;
        }
        html[data-theme="dark"] .bg-info.bg-opacity-10,
        html[data-bs-theme="dark"] .bg-info.bg-opacity-10 {
            background-color: rgba(14, 165, 233, 0.2) !important;
        }
        html[data-theme="dark"] .bg-danger.bg-opacity-10,
        html[data-bs-theme="dark"] .bg-danger.bg-opacity-10 {
            background-color: rgba(239, 68, 68, 0.2) !important;
        }
        html[data-theme="dark"] .text-success,
        html[data-bs-theme="dark"] .text-success {
            color: #4ade80 !important;
        }
        html[data-theme="dark"] .text-primary,
        html[data-bs-theme="dark"] .text-primary {
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .text-warning,
        html[data-bs-theme="dark"] .text-warning {
            color: #fbbf24 !important;
        }
        html[data-theme="dark"] .text-info,
        html[data-bs-theme="dark"] .text-info {
            color: #38bdf8 !important;
        }
        html[data-theme="dark"] .text-danger,
        html[data-bs-theme="dark"] .text-danger {
            color: #f87171 !important;
        }

        /* ── Dark Mode Outline Buttons ── */
        html[data-theme="dark"] .btn-outline-primary,
        html[data-bs-theme="dark"] .btn-outline-primary {
            color: #60a5fa !important;
            border-color: #3b82f6 !important;
            background-color: rgba(59, 130, 246, 0.1) !important;
        }
        html[data-theme="dark"] .btn-outline-primary:hover,
        html[data-bs-theme="dark"] .btn-outline-primary:hover,
        html[data-theme="dark"] .btn-outline-primary:focus,
        html[data-bs-theme="dark"] .btn-outline-primary:focus {
            color: #ffffff !important;
            border-color: #60a5fa !important;
            background-color: #2563eb !important;
        }
        html[data-theme="dark"] .btn-outline-secondary,
        html[data-bs-theme="dark"] .btn-outline-secondary {
            color: #94a3b8 !important;
            border-color: #475569 !important;
            background-color: rgba(148, 163, 184, 0.08) !important;
        }
        html[data-theme="dark"] .btn-outline-secondary:hover,
        html[data-bs-theme="dark"] .btn-outline-secondary:hover {
            color: #ffffff !important;
            border-color: #64748b !important;
            background-color: #334155 !important;
        }
        html[data-theme="dark"] .btn-outline-success,
        html[data-bs-theme="dark"] .btn-outline-success {
            color: #4ade80 !important;
            border-color: #22c55e !important;
            background-color: rgba(34, 197, 94, 0.1) !important;
        }
        html[data-theme="dark"] .btn-outline-success:hover,
        html[data-bs-theme="dark"] .btn-outline-success:hover {
            color: #ffffff !important;
            border-color: #4ade80 !important;
            background-color: #16a34a !important;
        }
        html[data-theme="dark"] .btn-outline-danger,
        html[data-bs-theme="dark"] .btn-outline-danger {
            color: #f87171 !important;
            border-color: #ef4444 !important;
            background-color: rgba(239, 68, 68, 0.1) !important;
        }
        html[data-theme="dark"] .btn-outline-danger:hover,
        html[data-bs-theme="dark"] .btn-outline-danger:hover {
            color: #ffffff !important;
            border-color: #f87171 !important;
            background-color: #dc2626 !important;
        }
        /* ── Official Tracking Kicker ── */
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

        /* Role Badges in Dark Mode */
        html[data-theme="dark"] .badge.bg-secondary.bg-opacity-10,
        html[data-bs-theme="dark"] .badge.bg-secondary.bg-opacity-10 {
            background-color: rgba(148, 163, 184, 0.18) !important;
            color: #cbd5e1 !important;
        }

        /* Document fee badges global support */
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
            background-color: rgba(30, 58, 138, 0.5) !important;
            color: #93c5fd !important;
            border: 1px solid rgba(96, 165, 250, 0.5) !important;
        }
        html[data-theme="dark"] .doc-fee-badge.fee-free,
        html[data-bs-theme="dark"] .doc-fee-badge.fee-free {
            background-color: rgba(22, 101, 52, 0.5) !important;
            color: #86efac !important;
            border: 1px solid rgba(74, 222, 128, 0.5) !important;
        }
        html[data-theme="dark"] .border,
        html[data-bs-theme="dark"] .border {
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] footer.text-muted small,
        html[data-bs-theme="dark"] footer.text-muted small {
            color: #64748b !important;
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

        .pagination-wrapper nav > div.d-none.d-sm-flex > div:first-child {
            display: none !important;
        }
        .pagination-wrapper nav > div.d-none.d-sm-flex {
            justify-content: flex-end !important;
        }
        .pagination-wrapper nav .pagination {
            margin-bottom: 0 !important;
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
    </style>
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
</head>
<body>
    <!-- Page Transition Loader -->
    <div id="page-loader-bar" aria-hidden="true"><div id="page-loader-fill"></div></div>
    <script>
        (function() {
            document.body.classList.add('no-transition');
            if (window.innerWidth >= 992 && localStorage.getItem('adminSidebarCollapsed') === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
        })();
    </script>

    @php
        $currentRoute = Route::currentRouteName();
        $pageTitles = [
            'admin.dashboard' => 'Dashboard',
            'admin.requests' => 'Document Requests',
            'admin.document-types.index' => 'Document Management',
            'admin.documents.index' => 'Document Management',
            'admin.concerns' => 'Community Reports',
            'admin.users' => 'User Management',
            'admin.approvals' => 'User Approvals',
            'admin.activity-logs' => 'Activity Logs',
            'admin.settings' => 'System Settings',
            'admin.profile' => 'Admin Profile',
        ];
        $currentPageTitle = $pageTitles[$currentRoute] ?? 'Admin Portal';

        $pendingRequestsCount = \App\Models\DocumentRequest::whereIn('status', ['Pending', 'Under Review'])->count();
        $pendingApprovalsCount = \App\Models\User::whereIn('status', ['pending_approval', 'Pending', 'pending'])->where('role', 'Resident')->count();
        $pendingConcernsCount = \App\Models\CommunityConcern::where('status', 'Pending')->count();

        $barangayLogo = \App\Models\BarangaySetting::get('barangay_logo');
        $logoUrl = ($barangayLogo && \Illuminate\Support\Facades\Storage::exists($barangayLogo))
            ? \Illuminate\Support\Facades\Storage::url($barangayLogo)
            : asset('images/cogon_east_logo.png');

        $adminUser = auth()->user();
        $adminNameParts = explode(' ', trim($adminUser->name ?? 'Admin'));
        $adminInitials = isset($adminNameParts[1]) 
            ? strtoupper(substr($adminNameParts[0], 0, 1) . substr($adminNameParts[1], 0, 1)) 
            : strtoupper(substr($adminNameParts[0], 0, 2));
    @endphp

    <div id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand-wrapper">
                    <div class="sidebar-brand-icon">
                        <img src="{{ $logoUrl }}" alt="Barangay Logo" class="sidebar-brand-img" width="45" height="45" loading="eager" decoding="sync" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
                    </div>
                    <div class="sidebar-brand-text">
                        <h4>E-Barangay</h4>
                        <p>Digital Services Portal</p>
                    </div>
                </a>
                <button class="sidebar-header-toggle" id="sidebarToggleBtn" aria-label="Toggle Sidebar" title="Toggle Sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <div class="sidebar-nav-scroll">
                <div class="sidebar-category-label">Overview</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-item {{ Route::is('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                    <i class="fa-solid fa-th-large nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>

                <div class="sidebar-category-label">Records</div>
                <a href="{{ route('admin.requests') }}" class="sidebar-nav-item {{ Route::is('admin.requests*') ? 'active' : '' }}" title="Document Requests">
                    <i class="fa-solid fa-file-lines nav-icon"></i>
                    <span class="nav-text">Document Requests</span>
                    @if($pendingRequestsCount > 0)
                        <span class="sidebar-badge badge-danger">{{ $pendingRequestsCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.document-types.index') }}" class="sidebar-nav-item {{ Route::is('admin.document-types.*') || Route::is('admin.documents.*') ? 'active' : '' }}" title="Document Management">
                    <i class="fa-solid fa-folder-open nav-icon"></i>
                    <span class="nav-text">Document Management</span>
                </a>
                <a href="{{ route('admin.concerns') }}" class="sidebar-nav-item {{ Route::is('admin.concerns*') ? 'active' : '' }}" title="Community Reports">
                    <i class="fa-solid fa-shield-halved nav-icon"></i>
                    <span class="nav-text">Community Reports</span>
                    @if($pendingConcernsCount > 0)
                        <span class="sidebar-badge badge-warning">{{ $pendingConcernsCount }}</span>
                    @endif
                </a>

                <div class="sidebar-category-label">System Operations</div>
                <a href="{{ route('admin.approvals') }}" class="sidebar-nav-item {{ Route::is('admin.approvals*') ? 'active' : '' }}" title="User Approvals">
                    <i class="fa-solid fa-user-check nav-icon"></i>
                    <span class="nav-text">User Approvals</span>
                    @if($pendingApprovalsCount > 0)
                        <span class="sidebar-badge badge-warning">{{ $pendingApprovalsCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.users') }}" class="sidebar-nav-item {{ Route::is('admin.users*') ? 'active' : '' }}" title="User Management">
                    <i class="fa-solid fa-user-gear nav-icon"></i>
                    <span class="nav-text">User Management</span>
                </a>
                <a href="{{ route('admin.activity-logs') }}" class="sidebar-nav-item {{ Route::is('admin.activity-logs*') ? 'active' : '' }}" title="Activity Logs">
                    <i class="fa-solid fa-clock-rotate-left nav-icon"></i>
                    <span class="nav-text">Activity Logs</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="sidebar-nav-item {{ Route::is('admin.settings*') ? 'active' : '' }}" title="System Settings">
                    <i class="fa-solid fa-sliders nav-icon"></i>
                    <span class="nav-text">System Settings</span>
                </a>
            </div>

            {{-- SIDEBAR BOTTOM ACCOUNT & LOGOUT FOOTER --}}
            <div class="sidebar-user-footer">
                <div class="sidebar-user-card">
                    @if($adminUser && $adminUser->profile_picture)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($adminUser->profile_picture) }}" 
                             alt="{{ $adminUser->name }}" 
                             class="sidebar-user-avatar img-cover">
                    @else
                        <div class="sidebar-user-avatar">
                            {{ $adminInitials }}
                        </div>
                    @endif
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name" title="{{ $adminUser->name ?? 'Admin' }}">{{ $adminUser->name ?? 'Admin' }}</div>
                        <div class="sidebar-user-role">Admin</div>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST" class="m-0 flex-shrink-0">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn" title="Logout" aria-label="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg admin-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5 min-w-0">
                    <button type="button" class="topbar-sidebar-toggle-btn d-lg-none" id="topbarSidebarToggle" aria-label="Toggle Navigation Sidebar" title="Toggle Sidebar Menu">
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

                {{-- TOP RIGHT HEADER: ONLY NOTIFICATION BELL --}}
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <button type="button" class="theme-toggle-top-btn" id="admin-theme-toggle-btn" aria-label="Toggle dark / light mode" title="Toggle dark mode">
                        <i class="fa-solid fa-moon icon-moon"></i>
                        <i class="fa-solid fa-sun icon-sun d-none"></i>
                    </button>

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
    window.addEventListener('DOMContentLoaded', function() {
        requestAnimationFrame(function() {
            setTimeout(function() {
                document.body.classList.remove('no-transition');
            }, 80);
        });
    });

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
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }

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
                    ${!n.read ? '<div class="flex-shrink-0"><span class="notif-unread-dot" style="width:8px;height:8px;background:#4f46e5;border-radius:50%;display:inline-block;margin-top:4px;"></span></div>' : ''}
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

        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('open');
            if (dropdown.classList.contains('open')) {
                fetchNotifications();
            }
        });

        document.addEventListener('click', function(e) {
            if (!document.getElementById('notif-wrapper').contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        window.fetchAdminNotifications = fetchNotifications;
        fetchNotifications();
        setInterval(fetchNotifications, 30000);
    })();

    (function() {
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const topbarSidebarToggle = document.getElementById('topbarSidebarToggle');
        const sidebar = document.getElementById('sidebar-wrapper');
        const backdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            if (window.innerWidth < 992) {
                if (sidebar && sidebar.classList.contains('toggled')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
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

        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }

        if (topbarSidebarToggle) {
            topbarSidebarToggle.addEventListener('click', function(e) {
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

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                closeMobileSidebar();
            }
        });
    })();
    </script>
    <script>
    (function() {
        const toggleBtn = document.getElementById('admin-theme-toggle-btn');
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
            // Sync with local page button if present (e.g. requests toolbar)
            const subToggleIcon = document.getElementById('themeToggleIcon');
            if (subToggleIcon) {
                subToggleIcon.textContent = isDark ? '☀️' : '🌙';
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

        // Allow any sub-page theme toggle to trigger the same action
        const subToggle = document.getElementById('btnToggleTheme');
        if (subToggle) {
            subToggle.addEventListener('click', function(e) {
                e.preventDefault();
                if (toggleBtn) {
                    toggleBtn.click();
                }
            });
        }
    })();
    </script>

    <script>
    /* ─── Ensure Bootstrap Modals are never trapped behind backdrop ─── */
    document.addEventListener('show.bs.modal', function(e) {
        const modal = e.target;
        if (modal && modal.classList && modal.classList.contains('modal')) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        }
    });

    /* ─── Global AGY Toast System ─── */
    window.agToast = function(title, msg, type, duration) {
        type     = type     || 'info';
        duration = duration || 5000;
        const icons = {
            success: 'fa-circle-check',
            info:    'fa-bell',
            warning: 'fa-triangle-exclamation',
            danger:  'fa-xmark-circle',
        };
        const stack = document.getElementById('agy-toast-stack');
        if (!stack) return;
        const t = document.createElement('div');
        t.className = 'agy-toast';
        t.innerHTML = `
            <div class="agy-toast-icon ${type}"><i class="fa-solid ${icons[type] || icons.info}"></i></div>
            <div class="agy-toast-body">
                <div class="agy-toast-title">${title}</div>
                ${msg ? `<div class="agy-toast-msg">${msg}</div>` : ''}
            </div>
            <button class="agy-toast-close" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>`;
        t.querySelector('.agy-toast-close').onclick = () => dismissToast(t);
        stack.appendChild(t);
        const timer = setTimeout(() => dismissToast(t), duration);
        function dismissToast(el) {
            clearTimeout(timer);
            el.classList.add('hiding');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        }
    };

    /* ─── Real-time WebSocket Client Connection ─── */
    (function initRealtimeWebSocket() {
        const host = window.location.hostname || '127.0.0.1';
        const wsUrl = `ws://${host}:8080`;
        let socket = null;
        let reconnectDelay = 2000;

        function connect() {
            try {
                socket = new WebSocket(wsUrl);

                socket.onopen = function () {
                    reconnectDelay = 2000;
                    console.log('⚡ Connected to e-Barangay WebSocket Server');
                    document.dispatchEvent(new CustomEvent('admin:ws-connected'));
                    const liveIndicator = document.getElementById('live-status-indicator');
                    if (liveIndicator) {
                        liveIndicator.classList.remove('offline');
                        liveIndicator.title = 'Real-time WebSocket active';
                    }
                };

                socket.onmessage = function (event) {
                    try {
                        const payload = JSON.parse(event.data);
                        if (payload.type === 'pong' || payload.event === 'connected') return;

                        console.log('📡 Real-time WebSocket event received:', payload);

                        // Broadcast custom event so any page listener can update dynamically
                        document.dispatchEvent(new CustomEvent('admin:realtime-event', { detail: payload }));

                        // Show interactive toast
                        if (payload.event === 'request.created') {
                            const d = payload.data || {};
                            window.agToast(
                                'New Document Request',
                                d.message || `Request #${d.tracking_number} received from ${d.applicant || 'Resident'}`,
                                'info',
                                6000
                            );
                        } else if (payload.event === 'request.status_changed') {
                            const d = payload.data || {};
                            const type = d.status === 'Rejected' ? 'danger' : (d.status === 'Ready' ? 'success' : 'info');
                            window.agToast(
                                'Request Status Updated',
                                d.message || `Request #${d.tracking_number} is now ${d.status}`,
                                type,
                                5000
                            );
                        }

                        // Trigger notifications refresh if function exists
                        if (typeof window.fetchAdminNotifications === 'function') {
                            window.fetchAdminNotifications();
                        }
                    } catch (e) {
                        console.error('Error handling WebSocket message:', e);
                    }
                };

                socket.onclose = function () {
                    document.dispatchEvent(new CustomEvent('admin:ws-disconnected'));
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

        // Heartbeat ping every 25 seconds
        setInterval(() => {
            if (socket && socket.readyState === WebSocket.OPEN) {
                socket.send(JSON.stringify({ type: 'ping' }));
            }
        }, 25000);

        // Start connection
        connect();
    })();
    </script>

    <!-- Global AGY Toast Stack -->
    <div id="agy-toast-stack"></div>

    <!-- Page Transition Loader Script -->
    <script>
    (function () {
        var bar  = document.getElementById('page-loader-bar');
        var fill = document.getElementById('page-loader-fill');
        var loadTimer = null;

        function startLoader() {
            if (!bar || !fill) return;
            clearTimeout(loadTimer);
            fill.style.transition = 'none';
            fill.style.width = '0%';
            bar.classList.add('loading');
            // rAF trick: allow the browser to register width=0 before animating
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

        function finishLoader() {
            if (!bar || !fill) return;
            clearTimeout(loadTimer);
            fill.style.transition = 'width 0.2s ease';
            fill.style.width = '100%';
            setTimeout(function () {
                bar.classList.remove('loading');
                fill.style.transition = 'none';
                fill.style.width = '0%';
            }, 300);
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
            startLoader();
        });

        // Finish when new page DOM is ready
        window.addEventListener('pageshow', finishLoader);
        document.addEventListener('DOMContentLoaded', finishLoader);
    })();
    </script>

</body>
</html>