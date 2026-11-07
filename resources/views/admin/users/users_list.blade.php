@extends('admin.layouts.admin')

@section('page_title', 'User Management')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">

    <style>
        /* Responsive Table & Accessibility Improvements */
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

        .table-users {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
        }
        .table-users thead th {
            font-size: 0.72rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            padding: 0.75rem 0.6rem;
            vertical-align: middle;
            white-space: nowrap;
        }
        .table-users tbody td {
            padding: 0.65rem 0.6rem;
            vertical-align: middle;
            font-size: 0.84rem;
        }
        .user-row-clickable {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .user-row-clickable:hover {
            background-color: #f8fafc !important;
        }
        html[data-theme="dark"] .user-row-clickable:hover,
        html[data-bs-theme="dark"] .user-row-clickable:hover {
            background-color: rgba(59, 130, 246, 0.08) !important;
        }
        .table-users thead th.col-actions,
        .table-users tbody td.col-actions {
            white-space: nowrap;
            text-align: right;
            padding-right: 1rem !important;
            min-width: 125px;
            width: 125px;
            position: sticky;
            right: 0;
            z-index: 2;
        }
        .table-users thead th.col-actions {
            background-color: var(--color-bg, #f8fafc);
        }
        .table-users tbody td.col-actions {
            background-color: #ffffff;
        }
        .table-users tbody tr:hover > td.col-actions {
            background-color: #f8fafc;
        }

        /* Action Buttons Standardized Inline Group */
        .user-actions-group {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
            white-space: nowrap;
        }
        .btn-table-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 3.5px;
            padding: 4px 7.5px;
            border-radius: 6px;
            font-size: 0.75rem; /* 12px */
            font-weight: 600;
            line-height: 1.2;
            transition: all 0.15s ease-in-out;
            text-decoration: none !important;
            border: 1px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            height: 28px;
        }
        .btn-table-action:hover {
            transform: translateY(-1px);
        }
        .btn-toggle-status.action-deactivate {
            color: #dc2626 !important;
            background: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.2);
        }
        .btn-toggle-status.action-deactivate:hover {
            background: rgba(239, 68, 68, 0.18);
            color: #b91c1c !important;
        }
        .btn-toggle-status.action-activate {
            color: #16a34a !important;
            background: rgba(22, 163, 74, 0.08);
            border-color: rgba(22, 163, 74, 0.2);
        }
        .btn-toggle-status.action-activate:hover {
            background: rgba(22, 163, 74, 0.18);
            color: #15803d !important;
        }

        /* ── Metric / Filter Cards ── */
        .user-filter-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1rem 1.125rem;
            background-color: #ffffff;
            border: 1.5px solid #e2e8f0; /* border-slate-200 */
            border-radius: 0.75rem; /* rounded-xl */
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            cursor: pointer;
            min-height: 96px;
            height: 100%;
        }
        .user-filter-card:hover {
            border-color: #cbd5e1; /* border-slate-300 */
            background-color: #f8fafc; /* bg-slate-50 */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
            transform: translateY(-1px);
        }
        /* Active State: Clean dark blue ring/border (border-blue-600, ring-2 ring-blue-100, bg-blue-50/30) */
        .user-filter-card.active {
            border-color: #2563eb !important; /* border-blue-600 */
            background-color: rgba(239, 246, 255, 0.5) !important; /* bg-blue-50/30 */
            box-shadow: 0 0 0 3px #dbeafe !important; /* ring-2 ring-blue-100 */
        }
        .user-filter-card:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
        .card-label-text {
            font-size: 0.875rem; /* 14px */
            font-weight: 600; /* font-semibold */
            color: #334155; /* text-slate-700 */
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }
        .card-count-text {
            font-size: 1.5rem; /* 24px - text-2xl */
            font-weight: 700; /* font-bold */
            color: #0f172a; /* text-slate-900 */
            line-height: 1.2;
        }
        .card-active-pill {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            background-color: #1e3a8a; /* deep navy */
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }
        .card-subtext-total { color: #64748b; font-size: 0.75rem; }
        .card-subtext-residents { color: #1d4ed8; font-size: 0.75rem; }
        .card-subtext-admins { color: #6d28d9; font-size: 0.75rem; }
        .card-pending-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.55rem;
            border-radius: 9999px;
            background-color: #fef3c7;
            color: #92400e !important;
            border: 1px solid #fde68a;
            display: inline-block;
            line-height: 1.2;
            transition: all 0.15s ease;
        }

        /* ── Header Action Button ── */
        .btn-create-user-header {
            min-height: 44px;
            min-width: 44px;
            padding: 0.5rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #ffffff !important;
            background-color: #0f2240 !important; /* dark navy / bg-blue-900 */
            border: 1px solid #0f2240;
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
            transition: all 0.15s ease-in-out;
            text-decoration: none !important;
        }
        .btn-create-user-header:hover {
            background-color: #18365d !important;
            border-color: #18365d;
            box-shadow: 0 4px 6px -1px rgba(15, 34, 64, 0.18);
        }
        .btn-create-user-header:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }

        /* ── Table Control Bar ── */
        .control-search-input {
            min-height: 44px; /* >= 42px requirement */
            padding-left: 2.85rem;
            padding-right: 2.75rem;
            font-size: 0.9375rem;
            color: #1e293b; /* text-slate-800 */
            background-color: #ffffff;
            border: 1.5px solid #cbd5e1; /* border-slate-300 */
            border-radius: 0.5rem; /* rounded-lg */
            transition: all 0.15s ease-in-out;
        }
        .control-search-input::placeholder {
            color: #94a3b8; /* placeholder:text-slate-400 */
        }
        .control-search-input:focus {
            background-color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px #dbeafe;
            color: #1e293b;
            outline: none;
        }
        .search-icon-adornment {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b; /* text-slate-500 prominent icon */
            pointer-events: none;
            font-size: 1rem;
        }
        .btn-filter-trigger {
            min-height: 44px;
            min-width: 44px;
            padding: 0.5rem 1.125rem;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #334155; /* text-slate-700 */
            background-color: #ffffff;
            border: 1.5px solid #cbd5e1; /* border-slate-300 */
            border-radius: 0.5rem; /* rounded-lg */
            transition: all 0.15s ease-in-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .btn-filter-trigger:hover,
        .btn-filter-trigger:focus,
        .btn-filter-trigger[aria-expanded="true"] {
            background-color: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        /* Accessible Action Link Buttons */
        .btn-table-action {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13.5px;
            text-decoration: none;
            transition: background-color 0.15s ease;
        }
        .btn-table-action:hover {
            background-color: #f1f5f9;
        }

        @media (max-width: 767.98px) {
            .mobile-user-card {
                border: 1.5px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                margin-bottom: 12px;
                padding: 16px;
            }
        }
    
        /* ═══════════════════════════════════════════════════════════
           DARK MODE OVERRIDES FOR USER MANAGEMENT (ACCESSIBILITY & CONTRAST)
           ═══════════════════════════════════════════════════════════ */
        html[data-theme="dark"] .user-filter-card,
        html[data-bs-theme="dark"] .user-filter-card {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .user-filter-card:hover,
        html[data-bs-theme="dark"] .user-filter-card:hover {
            border-color: #3b82f6 !important;
            background-color: #182238 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
        }
        html[data-theme="dark"] .user-filter-card.active,
        html[data-bs-theme="dark"] .user-filter-card.active {
            border-color: #3b82f6 !important;
            background-color: rgba(37, 99, 235, 0.22) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.35) !important;
        }
        html[data-theme="dark"] .card-label-text,
        html[data-bs-theme="dark"] .card-label-text {
            color: #cbd5e1 !important;
        }
        html[data-theme="dark"] .card-count-text,
        html[data-bs-theme="dark"] .card-count-text {
            color: #ffffff !important;
        }
        html[data-theme="dark"] .card-active-pill,
        html[data-bs-theme="dark"] .card-active-pill {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        /* Search input & Filter dropdown button */
        html[data-theme="dark"] .control-search-input,
        html[data-bs-theme="dark"] .control-search-input {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .control-search-input:focus,
        html[data-bs-theme="dark"] .control-search-input:focus {
            background-color: #1e2b45 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
        }
        html[data-theme="dark"] .control-search-input::placeholder,
        html[data-bs-theme="dark"] .control-search-input::placeholder {
            color: #64748b !important;
        }
        html[data-theme="dark"] .search-icon-adornment,
        html[data-bs-theme="dark"] .search-icon-adornment {
            color: #94a3b8 !important;
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

        /* Table Card and Table Rows in Dark Mode */
        html[data-theme="dark"] .table-users,
        html[data-bs-theme="dark"] .table-users,
        html[data-theme="dark"] .table-users > :not(caption) > * > *,
        html[data-bs-theme="dark"] .table-users > :not(caption) > * > *,
        html[data-theme="dark"] .table-users tbody td,
        html[data-bs-theme="dark"] .table-users tbody td {
            background-color: #131d31 !important;
            color: #e2e8f0 !important;
            border-color: #1e293b !important;
            box-shadow: none !important;
        }
        html[data-theme="dark"] .table-users thead th,
        html[data-bs-theme="dark"] .table-users thead th {
            background-color: #10192a !important;
            color: #cbd5e1 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] .table-users tbody tr:hover > td,
        html[data-bs-theme="dark"] .table-users tbody tr:hover > td {
            background-color: #19253d !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .font-monospace,
        html[data-bs-theme="dark"] .font-monospace {
            color: #93c5fd !important;
        }
        html[data-theme="dark"] .mobile-user-card,
        html[data-bs-theme="dark"] .mobile-user-card {
            background: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }

        /* ── High-Contrast Status Badges in Dark Mode ── */
        html[data-theme="dark"] .status-badge.status-pending-approval,
        html[data-bs-theme="dark"] .status-badge.status-pending-approval,
        html[data-theme="dark"] .status-badge.status-pending,
        html[data-bs-theme="dark"] .status-badge.status-pending {
            background-color: rgba(245, 158, 11, 0.22) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(245, 158, 11, 0.5) !important;
        }
        html[data-theme="dark"] .status-badge.status-pending-approval *,
        html[data-bs-theme="dark"] .status-badge.status-pending-approval *,
        html[data-theme="dark"] .status-badge.status-pending *,
        html[data-bs-theme="dark"] .status-badge.status-pending * {
            color: #fbbf24 !important;
        }

        html[data-theme="dark"] .status-badge.status-active,
        html[data-bs-theme="dark"] .status-badge.status-active {
            background-color: rgba(34, 197, 94, 0.22) !important;
            color: #4ade80 !important;
            border: 1px solid rgba(34, 197, 94, 0.5) !important;
        }
        html[data-theme="dark"] .status-badge.status-active *,
        html[data-bs-theme="dark"] .status-badge.status-active * {
            color: #4ade80 !important;
        }

        html[data-theme="dark"] .status-badge.status-inactive,
        html[data-bs-theme="dark"] .status-badge.status-inactive {
            background-color: rgba(148, 163, 184, 0.22) !important;
            color: #cbd5e1 !important;
            border: 1px solid rgba(148, 163, 184, 0.4) !important;
        }
        html[data-theme="dark"] .status-badge.status-inactive *,
        html[data-bs-theme="dark"] .status-badge.status-inactive * {
            color: #cbd5e1 !important;
        }

        /* ── Metric / Filter Cards Dark Mode Overrides ── */
        html[data-theme="dark"] .card-subtext-total,
        html[data-bs-theme="dark"] .card-subtext-total {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .card-subtext-residents,
        html[data-bs-theme="dark"] .card-subtext-residents {
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .card-subtext-admins,
        html[data-bs-theme="dark"] .card-subtext-admins {
            color: #c084fc !important;
        }
        html[data-theme="dark"] .card-pending-badge,
        html[data-bs-theme="dark"] .card-pending-badge {
            background-color: rgba(245, 158, 11, 0.22) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(245, 158, 11, 0.45) !important;
        }

        /* ── Action buttons in Table (Dark Mode) ── */
        html[data-theme="dark"] .btn-toggle-status.action-deactivate,
        html[data-bs-theme="dark"] .btn-toggle-status.action-deactivate,
        html[data-theme="dark"] .btn-toggle-status.action-deactivate i,
        html[data-bs-theme="dark"] .btn-toggle-status.action-deactivate i {
            color: #f87171 !important;
            background: rgba(239, 68, 68, 0.14) !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
        }
        html[data-theme="dark"] .btn-toggle-status.action-deactivate:hover,
        html[data-bs-theme="dark"] .btn-toggle-status.action-deactivate:hover {
            background: rgba(239, 68, 68, 0.25) !important;
            color: #fca5a5 !important;
        }
        html[data-theme="dark"] .btn-toggle-status.action-activate,
        html[data-bs-theme="dark"] .btn-toggle-status.action-activate,
        html[data-theme="dark"] .btn-toggle-status.action-activate i,
        html[data-bs-theme="dark"] .btn-toggle-status.action-activate i {
            color: #4ade80 !important;
            background: rgba(34, 197, 94, 0.14) !important;
            border-color: rgba(34, 197, 94, 0.3) !important;
        }
        html[data-theme="dark"] .btn-toggle-status.action-activate:hover,
        html[data-bs-theme="dark"] .btn-toggle-status.action-activate:hover {
            background: rgba(34, 197, 94, 0.25) !important;
            color: #86efac !important;
        }

        /* ── Sticky Actions Column in Dark Mode ── */
        html[data-theme="dark"] .table-users thead th.col-actions,
        html[data-bs-theme="dark"] .table-users thead th.col-actions {
            background-color: #10192a !important;
        }
        html[data-theme="dark"] .table-users tbody td.col-actions,
        html[data-bs-theme="dark"] .table-users tbody td.col-actions {
            background-color: #131d31 !important;
            box-shadow: -4px 0 8px rgba(0, 0, 0, 0.25);
        }
        html[data-theme="dark"] .table-users tbody tr:hover > td.col-actions,
        html[data-bs-theme="dark"] .table-users tbody tr:hover > td.col-actions {
            background-color: #19253d !important;
        }

        /* ── Role Badges in Table (Dark Mode) ── */
        html[data-theme="dark"] .badge-admin-role,
        html[data-bs-theme="dark"] .badge-admin-role {
            background-color: rgba(147, 51, 234, 0.2) !important;
            color: #c084fc !important;
            border: 1px solid rgba(147, 51, 234, 0.4) !important;
        }
        html[data-theme="dark"] .badge-resident-role,
        html[data-bs-theme="dark"] .badge-resident-role {
            background-color: rgba(148, 163, 184, 0.18) !important;
            color: #cbd5e1 !important;
            border: 1px solid rgba(148, 163, 184, 0.3) !important;
        }

        /* ── User Details Modal Dark Mode ── */
        html[data-theme="dark"] #viewUserModal .modal-content,
        html[data-bs-theme="dark"] #viewUserModal .modal-content {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border: 1px solid #1e293b !important;
        }
        html[data-theme="dark"] #viewUserModal .modal-header,
        html[data-bs-theme="dark"] #viewUserModal .modal-header,
        html[data-theme="dark"] #viewUserModal .modal-footer,
        html[data-bs-theme="dark"] #viewUserModal .modal-footer {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] #viewUserModal .card,
        html[data-bs-theme="dark"] #viewUserModal .card {
            background-color: #131d31 !important;
            border-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #viewUserModal .card-header,
        html[data-bs-theme="dark"] #viewUserModal .card-header {
            background-color: #182238 !important;
            border-color: #1e293b !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] #viewUserModal .hero-user-card,
        html[data-bs-theme="dark"] #viewUserModal .hero-user-card {
            background: linear-gradient(135deg, #131d31 0%, #1e293b 100%) !important;
            border-color: #1e293b !important;
        }
        html[data-theme="dark"] #viewUserModal .form-control,
        html[data-bs-theme="dark"] #viewUserModal .form-control,
        html[data-theme="dark"] #viewUserModal .form-select,
        html[data-bs-theme="dark"] #viewUserModal .form-select {
            background-color: #182238 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html[data-theme="dark"] #viewUserModal .form-control:focus,
        html[data-bs-theme="dark"] #viewUserModal .form-control:focus,
        html[data-theme="dark"] #viewUserModal .form-select:focus,
        html[data-bs-theme="dark"] #viewUserModal .form-select:focus {
            background-color: #1e2b45 !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] #viewUserModal .text-dark,
        html[data-bs-theme="dark"] #viewUserModal .text-dark {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #viewUserModal .text-muted,
        html[data-bs-theme="dark"] #viewUserModal .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] #viewUserModal .btn-close,
        html[data-bs-theme="dark"] #viewUserModal .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

    </style>

    {{-- Header Section --}}
    <div class="mb-4">
        {{-- Title + Subtitle on Left, "Create New User" on Top Right --}}
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0" style="color: #1e293b; font-size: 1.5rem; font-weight: 700; line-height: 1.25; letter-spacing: -0.02em;">
                    User Management
                </h1>
                <p class="text-sm text-slate-500 m-0 mt-1" style="color: #64748b; font-size: 0.875rem;">
                    Manage resident accounts, permissions, and system roles
                </p>
            </div>
            <div class="d-flex align-items-center flex-shrink-0">
                <button type="button" 
                        class="btn btn-create-user-header d-inline-flex align-items-center justify-content-center"
                        data-bs-toggle="modal" 
                        data-bs-target="#createUserModal"
                        aria-label="Create New User">
                    <span>Create New User</span>
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-4 shadow-sm d-flex align-items-center" role="alert" style="color: #166534; background-color: #f0fdf4; border: 1.5px solid #bbf7d0;">
            <i class="fa-solid fa-circle-check me-2 fa-lg text-success"></i>
            <span class="fw-medium">{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible show rounded-3 mb-4 shadow-sm" role="alert" style="color: #991b1b; background-color: #fef2f2; border: 1.5px solid #fecaca;">
            <div class="d-flex align-items-center mb-1">
                <i class="fa-solid fa-circle-exclamation me-2 fa-lg text-danger"></i>
                <span class="fw-bold">Please correct the following errors:</span>
            </div>
            <ul class="mb-0 mt-1 ps-4 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Metric / Filter Cards (Merged Stat Cards + Filter Tabs) --}}
    <div class="row g-3 mb-4" role="tablist" aria-label="User Filter Tabs">
        {{-- Card 1: All Users --}}
        <div class="col-6 col-lg-3">
            <a href="{{ request()->fullUrlWithQuery(['role' => 'All']) }}" 
               class="user-filter-card {{ in_array($currentRole, ['All', '']) ? 'active' : '' }}"
               role="tab"
               aria-selected="{{ in_array($currentRole, ['All', '']) ? 'true' : 'false' }}"
               title="Show All Users">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="card-label-text">
                        <i class="fa-solid fa-users" style="color: #475569;" aria-hidden="true"></i>
                        All Users
                    </span>
                    @if(in_array($currentRole, ['All', '']))
                        <span class="card-active-pill" aria-label="Active category filter">Active</span>
                    @endif
                </div>
                <div class="d-flex align-items-baseline justify-content-between mt-auto">
                    <span class="card-count-text">{{ $tabCounts['All'] ?? $totalUsers }}</span>
                    <span class="card-subtext-total fw-semibold">Total</span>
                </div>
            </a>
        </div>

        {{-- Card 2: Residents --}}
        <div class="col-6 col-lg-3">
            <a href="{{ request()->fullUrlWithQuery(['role' => 'Resident']) }}" 
               class="user-filter-card {{ $currentRole === 'Resident' ? 'active' : '' }}"
               role="tab"
               aria-selected="{{ $currentRole === 'Resident' ? 'true' : 'false' }}"
               title="Filter by Residents">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="card-label-text">
                        <i class="fa-solid fa-house-user" style="color: #1d4ed8;" aria-hidden="true"></i>
                        Residents
                    </span>
                    @if($currentRole === 'Resident')
                        <span class="card-active-pill" aria-label="Active category filter">Active</span>
                    @endif
                </div>
                <div class="d-flex align-items-baseline justify-content-between mt-auto">
                    <span class="card-count-text">{{ $tabCounts['Resident'] ?? 0 }}</span>
                    <span class="card-subtext-residents fw-semibold">Residents</span>
                </div>
            </a>
        </div>

        {{-- Card 3: Administrators --}}
        <div class="col-6 col-lg-3">
            <a href="{{ request()->fullUrlWithQuery(['role' => 'Admin']) }}" 
               class="user-filter-card {{ $currentRole === 'Admin' ? 'active' : '' }}"
               role="tab"
               aria-selected="{{ $currentRole === 'Admin' ? 'true' : 'false' }}"
               title="Filter by Administrators">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="card-label-text">
                        <i class="fa-solid fa-user-shield" style="color: #6d28d9;" aria-hidden="true"></i>
                        Administrators
                    </span>
                    @if($currentRole === 'Admin')
                        <span class="card-active-pill" aria-label="Active category filter">Active</span>
                    @endif
                </div>
                <div class="d-flex align-items-baseline justify-content-between mt-auto">
                    <span class="card-count-text">{{ $tabCounts['Admin'] ?? 0 }}</span>
                    <span class="card-subtext-admins fw-semibold">Staff & Admins</span>
                </div>
            </a>
        </div>

        {{-- Card 4: Pending / New --}}
        <div class="col-6 col-lg-3">
            <a href="{{ request()->fullUrlWithQuery(['role' => 'Pending']) }}" 
               class="user-filter-card {{ in_array($currentRole, ['Pending', 'Pending / New']) ? 'active' : '' }}"
               role="tab"
               aria-selected="{{ in_array($currentRole, ['Pending', 'Pending / New']) ? 'true' : 'false' }}"
               title="Filter by Pending Approvals and New Accounts">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="card-label-text">
                        <i class="fa-solid fa-user-clock text-amber-700" style="color: #b45309;" aria-hidden="true"></i>
                        Pending / New
                    </span>
                    @if(in_array($currentRole, ['Pending', 'Pending / New']))
                        <span class="card-active-pill" style="background-color: #92400e;" aria-label="Active category filter">Active</span>
                    @endif
                </div>
                <div class="d-flex align-items-baseline justify-content-between mt-auto">
                    <span class="card-count-text">{{ $tabCounts['Pending'] ?? 0 }}</span>
                    <span class="card-pending-badge fw-bold" aria-label="New and pending count">
                        New & Pending
                    </span>
                </div>
            </a>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        {{-- Table Control Bar (Directly below cards) --}}
        <div class="card-header border-bottom bg-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            
            {{-- Left: Prominent Search Input --}}
            <form action="{{ route('admin.users') }}" method="GET" class="m-0 flex-grow-1" style="max-width: 440px;" id="searchForm">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif

                <div class="position-relative w-100">
                    <span class="search-icon-adornment" aria-hidden="true">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" 
                           id="searchInput" 
                           name="search" 
                           value="{{ request('search') }}" 
                           class="form-control control-search-input text-slate-800" 
                           placeholder="Search by name, ID, or email..." 
                           autocomplete="off"
                           aria-label="Search by name, ID, or email">
                    @if(request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" 
                           class="position-absolute end-0 top-50 translate-middle-y me-2 d-flex align-items-center justify-content-center text-decoration-none"
                           style="width: 38px; height: 38px; border-radius: 50%; color: #64748b;"
                           title="Clear search"
                           aria-label="Clear search input">
                            <i class="fa-solid fa-circle-xmark fa-lg"></i>
                        </a>
                    @endif
                </div>
            </form>

            {{-- Right: Filters Button with Clear Dropdown Arrow --}}
            <div class="d-flex align-items-center gap-2 ms-auto">
                <div class="dropdown">
                    <button class="btn btn-filter-trigger d-inline-flex align-items-center gap-2" 
                            type="button" 
                            id="filtersDropdownBtn" 
                            data-bs-toggle="dropdown" 
                            data-bs-auto-close="outside" 
                            aria-expanded="false"
                            aria-label="Open filter options">
                        <i class="fa-solid fa-sliders text-slate-500" style="color: #64748b;" aria-hidden="true"></i>
                        <span>Filters</span>
                        @if((request('status') && request('status') !== 'All') || (request('sort') && request('sort') !== 'newest'))
                            <span class="badge rounded-pill bg-primary text-white ms-1" style="font-size: 10px; padding: 3px 6px;">Active</span>
                        @endif
                        <i class="fa-solid fa-chevron-down text-slate-500 fa-xs ms-1" style="color: #64748b;" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border border-slate-200 rounded-3" style="min-width: 300px; z-index: 1060;" aria-labelledby="filtersDropdownBtn">
                        <form action="{{ route('admin.users') }}" method="GET">
                            @if(request('role'))
                                <input type="hidden" name="role" value="{{ request('role') }}">
                            @endif
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                                <h6 class="fw-bold mb-0 text-slate-800" style="color: #1e293b; font-size: 0.9375rem;">Filter Users</h6>
                                @if((request('status') && request('status') !== 'All') || (request('sort') && request('sort') !== 'newest'))
                                    <a href="{{ route('admin.users', ['role' => request('role'), 'search' => request('search')]) }}" class="text-decoration-none text-danger small fw-semibold">
                                        Reset All
                                    </a>
                                @endif
                            </div>

                            {{-- Status Filter --}}
                            <div class="mb-3">
                                <label for="filterStatusSelect" class="form-label text-xs fw-bold text-slate-600 text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #475569;">
                                    Account Status
                                </label>
                                <select name="status" id="filterStatusSelect" class="form-select form-select-sm" style="min-height: 40px; border-color: #cbd5e1; font-size: 0.875rem;">
                                    <option value="All" {{ request('status') == 'All' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive / Deactivated</option>
                                </select>
                            </div>

                            {{-- Sort Order --}}
                            <div class="mb-3">
                                <label for="filterSortSelect" class="form-label text-xs fw-bold text-slate-600 text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #475569;">
                                    Sort Order
                                </label>
                                <select name="sort" id="filterSortSelect" class="form-select form-select-sm" style="min-height: 40px; border-color: #cbd5e1; font-size: 0.875rem;">
                                    <option value="newest" {{ request('sort') == 'newest' || !request('sort') ? 'selected' : '' }}>Newest Registered First</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest Registered First</option>
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2 pt-2 border-top">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill fw-semibold py-2" style="background-color: #0f2240; border-color: #0f2240; min-height: 38px;">
                                    Apply Filters
                                </button>
                                <a href="{{ route('admin.users', ['role' => request('role'), 'search' => request('search')]) }}" class="btn btn-light btn-sm flex-fill fw-semibold py-2 border" style="min-height: 38px;">
                                    Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Selection Bar --}}
        <div id="bulkActionsBar" class="d-none bg-primary bg-opacity-10 border-bottom px-3 px-md-4 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold" id="selectedCountBadge">0 Selected</span>
                <span class="text-muted small d-none d-sm-inline">Select multiple users to delete in bulk.</span>
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

        {{-- Desktop Table View (Screens >= 768px) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0 table-users" id="usersMasterTable">
                <thead class="text-muted small" style="background-color: var(--color-bg);">
                    <tr>
                        <th class="ps-3 py-3 border-0 text-center" style="width: 38px;">
                            <input class="form-check-input" type="checkbox" id="selectAllUsers" title="Select All">
                        </th>
                        <th class="py-3 border-0 text-nowrap" style="width: 100px;">USER ID</th>
                        <th class="py-3 border-0" style="min-width: 140px;">NAME</th>
                        <th class="py-3 border-0" style="min-width: 130px;">EMAIL</th>
                        <th class="py-3 border-0 text-center text-nowrap" style="width: 80px;">ROLE</th>
                        <th class="py-3 border-0 text-center text-nowrap" style="width: 85px;">STATUS</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 105px;">REGISTERED</th>
                        <th class="pe-3 py-3 border-0 text-end col-actions">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($allUsers as $user)
                    <tr id="user-row-{{ $user->id }}"
                        class="user-row-clickable"
                        title="Click to view details for {{ $user->name }}"
                        role="button"
                        tabindex="0"
                        data-id="{{ $user->id }}"
                        data-user-id-code="USR-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}"
                        data-name="{{ $user->name }}"
                        data-email="{{ $user->email }}"
                        data-phone="{{ $user->phone_number ?? '' }}"
                        data-role="{{ $user->role }}"
                        data-status="{{ $user->status }}"
                        data-registered="{{ $user->created_at->format('M d, Y') }}"
                        data-gender="{{ $user->gender ?? 'N/A' }}"
                        data-birthdate="{{ $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('M d, Y') : 'N/A' }}"
                        data-birthdate-raw="{{ $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('Y-m-d') : '' }}"
                        data-age="{{ $user->age ?? 'N/A' }}"
                        data-birthplace="{{ $user->birthplace ?? 'N/A' }}"
                        data-civil-status="{{ $user->civil_status ?? 'N/A' }}"
                        data-nationality="{{ $user->nationality ?? 'Filipino' }}"
                        data-occupation="{{ $user->occupation ?? 'N/A' }}"
                        data-purok="{{ $user->purok ?? 'N/A' }}"
                        data-sitio="{{ $user->sitio ?? 'N/A' }}"
                        data-barangay="{{ $user->barangay_address ?? 'Cogon East' }}"
                        data-municipality="{{ $user->municipality ?? 'Carmen' }}"
                        data-province="{{ $user->province ?? 'Cebu' }}"
                        data-zip="{{ $user->zip_code ?? '6005' }}"
                        data-id-type="{{ $user->id_type ?? 'N/A' }}"
                        data-id-doc-url="{{ $user->id_document_url }}"
                        data-selfie-url="{{ $user->selfie_photo ? route('admin.resident.selfie', basename($user->selfie_photo)) : '' }}"
                        data-face-verified="{{ $user->face_verified_at ? \Carbon\Carbon::parse($user->face_verified_at)->format('M d, Y h:i A') : '' }}">
                        <td class="ps-3 text-center py-2.5" style="width: 38px;" onclick="event.stopPropagation()">
                            <input class="form-check-input user-checkbox" type="checkbox" value="{{ $user->id }}" data-name="{{ $user->name }}" {{ $user->id === auth()->id() ? 'disabled title="Cannot delete your own account"' : '' }}>
                        </td>
                        <td class="fw-bold text-dark text-nowrap">
                            <span class="font-monospace" style="font-size: 12.5px;">USR-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2 fw-bold text-uppercase flex-shrink-0" style="width: 30px; height: 30px; font-size: 11px;">
                                    {{ substr(strip_tags($user->name), 0, 2) }}
                                </div>
                                <div class="fw-bold text-dark text-truncate" style="font-size: 13px; max-width: 140px;" title="{{ $user->name }}">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="text-muted text-truncate" style="max-width: 150px; font-size: 12.5px;" title="{{ $user->email }}">{{ $user->email }}</div>
                        </td>
                        <td class="text-center text-nowrap">
                            @if($user->role == 'Admin')
                                <span class="badge badge-admin-role bg-opacity-10 px-2.5 py-1 rounded-pill" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; font-size: 11px;">Admin</span>
                            @else
                                <span class="badge badge-resident-role bg-secondary bg-opacity-10 text-secondary px-2.5 py-1 rounded-pill" style="font-size: 11px;">Resident</span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap">
                            @if(in_array(strtolower($user->status), ['active']))
                                <span class="status-badge status-active"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Active</span>
                            @elseif($user->status == 'pending_approval')
                                <span class="status-badge status-pending-approval"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Pending</span>
                            @else
                                <span class="status-badge status-inactive"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Inactive</span>
                            @endif
                        </td>
                        <td class="text-muted text-nowrap" style="font-size: 12px;">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="pe-3 text-end text-nowrap col-actions" onclick="event.stopPropagation()">
                            <div class="user-actions-group">
                                {{-- DEACTIVATE / ACTIVATE BUTTON --}}
                                <button type="button"
                                    class="btn-table-action btn-toggle-status {{ in_array(strtolower($user->status), ['active']) ? 'action-deactivate text-danger' : 'action-activate text-success' }}"
                                    aria-label="{{ in_array(strtolower($user->status), ['active']) ? 'Deactivate' : 'Activate' }} user account for {{ $user->name }}"
                                    title="{{ in_array(strtolower($user->status), ['active']) ? 'Deactivate' : 'Activate' }} user account"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-action="{{ in_array(strtolower($user->status), ['active']) ? 'deactivate' : 'activate' }}"
                                    data-url="{{ route('admin.users.toggle-status', $user->id) }}">
                                    <i class="fa-solid {{ in_array(strtolower($user->status), ['active']) ? 'fa-ban' : 'fa-circle-check' }} me-1"></i>
                                    <span>{{ in_array(strtolower($user->status), ['active']) ? 'Deactivate' : 'Activate' }}</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users fa-3x mb-3 text-light"></i>
                            <h5 class="fw-bold text-dark">No Users Found</h5>
                            <div>There are currently no users matching your criteria.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (Screens < 768px, matching Request tab) --}}
        <div class="d-md-none p-3" id="users-mobile-list">
            @forelse($allUsers as $user)
                <div class="mobile-user-card user-row-clickable"
                     role="button"
                     tabindex="0"
                     title="Tap to view user details for {{ $user->name }}"
                     data-id="{{ $user->id }}"
                     data-user-id-code="USR-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}"
                     data-name="{{ $user->name }}"
                     data-email="{{ $user->email }}"
                     data-phone="{{ $user->phone_number ?? '' }}"
                     data-role="{{ $user->role }}"
                     data-status="{{ $user->status }}"
                     data-registered="{{ $user->created_at->format('M d, Y') }}"
                     data-gender="{{ $user->gender ?? 'N/A' }}"
                     data-birthdate="{{ $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('M d, Y') : 'N/A' }}"
                     data-birthdate-raw="{{ $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('Y-m-d') : '' }}"
                     data-age="{{ $user->age ?? 'N/A' }}"
                     data-birthplace="{{ $user->birthplace ?? 'N/A' }}"
                     data-civil-status="{{ $user->civil_status ?? 'N/A' }}"
                     data-nationality="{{ $user->nationality ?? 'Filipino' }}"
                     data-occupation="{{ $user->occupation ?? 'N/A' }}"
                     data-purok="{{ $user->purok ?? 'N/A' }}"
                     data-sitio="{{ $user->sitio ?? 'N/A' }}"
                     data-barangay="{{ $user->barangay_address ?? 'Cogon East' }}"
                     data-municipality="{{ $user->municipality ?? 'Carmen' }}"
                     data-province="{{ $user->province ?? 'Cebu' }}"
                     data-zip="{{ $user->zip_code ?? '6005' }}"
                     data-id-type="{{ $user->id_type ?? 'N/A' }}"
                     data-id-doc-url="{{ $user->id_document_url }}"
                     data-selfie-url="{{ $user->selfie_photo ? route('admin.resident.selfie', basename($user->selfie_photo)) : '' }}"
                     data-face-verified="{{ $user->face_verified_at ? \Carbon\Carbon::parse($user->face_verified_at)->format('M d, Y h:i A') : '' }}">
                    {{-- Header: ID + Role + Status --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation()">
                            <input class="form-check-input user-checkbox mt-0" type="checkbox" value="{{ $user->id }}" data-name="{{ $user->name }}" {{ $user->id === auth()->id() ? 'disabled title="Cannot delete your own account"' : '' }}>
                            <span class="font-monospace fw-bold text-dark" style="font-size: 13px;">
                                USR-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            @if($user->role == 'Admin')
                                <span class="badge bg-opacity-10 px-2 py-0.5 rounded-pill" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; font-size: 10.5px;">Admin</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-0.5 rounded-pill" style="font-size: 10.5px;">Resident</span>
                            @endif

                            @if(in_array(strtolower($user->status), ['active']))
                                <span class="status-badge status-active" style="font-size: 10.5px; padding: 2px 7px;">Active</span>
                            @elseif($user->status == 'pending_approval')
                                <span class="status-badge status-pending-approval" style="font-size: 10.5px; padding: 2px 7px;">Pending</span>
                            @else
                                <span class="status-badge status-inactive" style="font-size: 10.5px; padding: 2px 7px;">Inactive</span>
                            @endif
                        </div>
                    </div>

                    {{-- User Details --}}
                    <div class="d-flex align-items-center my-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2.5 fw-bold text-uppercase flex-shrink-0" style="width: 38px; height: 38px; font-size: 13px;">
                            {{ substr(strip_tags($user->name), 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 14px;">{{ $user->name }}</div>
                            <div class="text-muted text-truncate" style="font-size: 12px;">{{ $user->email }}</div>
                            <div class="text-muted" style="font-size: 11px;">Registered: {{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="text-muted ms-2">
                            <i class="fa-solid fa-chevron-right text-muted opacity-50" style="font-size: 12px;"></i>
                        </div>
                    </div>

                    {{-- Actions Grid for Mobile: Clickable row opens details, button activates/deactivates --}}
                    <div class="d-flex gap-2 pt-2 border-top mt-2" onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-sm rounded-3 w-100 fw-semibold py-2 btn-toggle-status {{ in_array(strtolower($user->status), ['active']) ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                style="min-height: 38px;"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-action="{{ in_array(strtolower($user->status), ['active']) ? 'deactivate' : 'activate' }}"
                                data-url="{{ route('admin.users.toggle-status', $user->id) }}">
                            <i class="fa-solid {{ in_array(strtolower($user->status), ['active']) ? 'fa-ban' : 'fa-check' }} me-1"></i>
                            {{ in_array(strtolower($user->status), ['active']) ? 'Deactivate User' : 'Activate User' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-users fa-3x mb-2 text-light d-block"></i>
                    <h6 class="fw-bold text-dark">No Users Found</h6>
                    <div class="small">There are currently no users matching your criteria.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===================== CREATE NEW USER MODAL ===================== --}}
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-3" style="background: linear-gradient(135deg, #0F2240 0%, #18365D 100%); border-radius: 1rem 1rem 0 0;">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-1" id="createUserModalLabel">
                        <i class="fa-solid fa-user-plus me-2"></i>Create New User
                    </h5>
                    <p class="text-white-50 small mb-0">Add a new administrator or resident account</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label for="createUserName" class="form-label fw-bold text-slate-700 small" style="color: #334155;">
                            FULL NAME <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="createUserName" name="name" required placeholder="e.g. JUAN DELA CRUZ" style="min-height: 44px; text-transform: uppercase;">
                    </div>

                    <div class="mb-3">
                        <label for="createUserEmail" class="form-label fw-bold text-slate-700 small" style="color: #334155;">
                            EMAIL ADDRESS <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="createUserEmail" name="email" required placeholder="e.g. user@example.com" style="min-height: 44px;">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="createUserRole" class="form-label fw-bold text-slate-700 small" style="color: #334155;">
                                ROLE <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="createUserRole" name="role" required style="min-height: 44px;">
                                <option value="Resident" selected>Resident</option>
                                <option value="Admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="createUserStatus" class="form-label fw-bold text-slate-700 small" style="color: #334155;">
                                INITIAL STATUS
                            </label>
                            <select class="form-select" id="createUserStatus" name="status" style="min-height: 44px;">
                                <option value="active" selected>Active</option>
                                <option value="pending_approval">Pending Approval</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="createUserPassword" class="form-label fw-bold text-slate-700 small" style="color: #334155;">
                            PASSWORD <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="createUserPassword" name="password" required minlength="8" placeholder="At least 8 characters" style="min-height: 44px;">
                        <div class="form-text small" style="color: #64748b;">Must be at least 8 characters. User will use this to sign in.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-1">
                    <button type="button" class="btn btn-light rounded-3 fw-semibold px-4" data-bs-dismiss="modal" style="min-height: 44px;">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-semibold px-4" style="background-color: #0f2240; border-color: #0f2240; min-height: 44px;">
                        <i class="fa-solid fa-plus me-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== USER DETAILS MODAL (EXPANDED HORIZONTALLY) ===================== --}}
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1040px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- MODAL HEADER WITH EDIT BUTTON --}}
            <div class="modal-header bg-light border-bottom d-flex align-items-center py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-user-circle fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="viewUserModalLabel">
                            <span id="userModalHeaderTitle">User Details</span>
                        </h5>
                        <div class="text-muted small font-monospace" id="viewUserIdLabel">USR-00000</div>
                    </div>
                </div>

                {{-- RIGHT-SIDE CONTROLS: EDIT BUTTON ON USER DETAILS CARD + CLOSE --}}
                <div class="ms-auto d-flex align-items-center gap-2">
                    <button type="button"
                            class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1.5 fw-semibold shadow-sm"
                            id="viewModalEditBtn"
                            onclick="toggleUserModalEdit(true)">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- ================= VIEW MODE ================= --}}
            <div id="userModalViewSection" class="modal-body p-4 text-start">
                {{-- Top Hero Card --}}
                <div class="hero-user-card p-3.5 p-md-4 rounded-3 border mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 shadow-xs"
                     style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-white rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase shadow-sm flex-shrink-0"
                             style="width: 58px; height: 58px; font-size: 20px; background: linear-gradient(135deg, #0f2240 0%, #1e40af 100%) !important;"
                             id="viewUserAvatar">
                            US
                        </div>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h4 class="fw-bold text-dark mb-0 text-truncate" id="viewUserName">JUAN DELA CRUZ</h4>
                                <span id="viewUserRoleBadge"></span>
                                <span id="viewUserStatusBadge"></span>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-1.5 flex-wrap text-muted small">
                                <div><i class="fa-solid fa-envelope me-1 text-primary"></i> <span id="viewUserEmail">user@example.com</span></div>
                                <div id="viewUserPhoneWrap"><i class="fa-solid fa-phone me-1 text-primary"></i> <span id="viewUserPhone">N/A</span></div>
                                <div><i class="fa-solid fa-calendar-check me-1 text-muted"></i> Registered: <span id="viewUserRegistered">Nov 07, 2026</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3-Column Horizontally Expanded Grid --}}
                <div class="row g-3">
                    {{-- Column 1: Personal Demographics --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border shadow-xs rounded-3">
                            <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-id-card text-primary"></i>
                                <span class="fw-bold text-dark small text-uppercase letter-spacing-wide">Personal Details</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2.5">
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">GENDER</div>
                                        <div id="viewUserGender" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">CIVIL STATUS</div>
                                        <div id="viewUserCivilStatus" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">BIRTHDATE</div>
                                        <div id="viewUserBirthdate" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">AGE</div>
                                        <div id="viewUserAge" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">BIRTHPLACE</div>
                                        <div id="viewUserBirthplace" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">NATIONALITY</div>
                                        <div id="viewUserNationality" class="fw-bold text-dark small">Filipino</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">OCCUPATION</div>
                                        <div id="viewUserOccupation" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Column 2: Address & Location --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border shadow-xs rounded-3">
                            <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-location-dot text-primary"></i>
                                <span class="fw-bold text-dark small text-uppercase letter-spacing-wide">Residence & Location</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2.5">
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">BARANGAY</div>
                                        <div id="viewUserBarangay" class="fw-bold text-dark small">Cogon East</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">PUROK</div>
                                        <div id="viewUserPurok" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">SITIO</div>
                                        <div id="viewUserSitio" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">ZIP CODE</div>
                                        <div id="viewUserZip" class="fw-bold text-dark small">6005</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">MUNICIPALITY</div>
                                        <div id="viewUserMunicipality" class="fw-bold text-dark small">Carmen</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">PROVINCE</div>
                                        <div id="viewUserProvince" class="fw-bold text-dark small">Cebu</div>
                                    </div>
                                    <div class="col-12 pt-1 border-top">
                                        <div class="text-muted small fw-semibold" style="font-size: 11px;">CONTACT NUMBER</div>
                                        <div id="viewUserContactCard" class="fw-bold text-dark small">N/A</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Column 3: Identity & Biometrics Cross-Check --}}
                    <div class="col-lg-4 col-md-12">
                        <div class="card h-100 border shadow-xs rounded-3">
                            <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-shield-halved text-primary"></i>
                                <span class="fw-bold text-dark small text-uppercase letter-spacing-wide">Verification & Documents</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <div class="text-muted small fw-semibold mb-1" style="font-size: 11px;">GOVERNMENT-ISSUED ID</div>
                                    <div class="d-flex align-items-center gap-2 mb-1.5">
                                        <span class="badge bg-light text-dark border px-2 py-1" id="viewUserIdType">N/A</span>
                                    </div>
                                    <div id="viewUserIdDoc">
                                        <span class="text-muted small">No ID Uploaded</span>
                                    </div>
                                </div>
                                <div class="pt-2 border-top mb-3">
                                    <div class="text-muted small fw-semibold mb-1" style="font-size: 11px;">LIVE FACIAL SELFIE</div>
                                    <div id="viewUserSelfie">
                                        <span class="text-muted small">No live selfie captured</span>
                                    </div>
                                </div>
                                <div class="pt-2 border-top">
                                    <div class="text-muted small fw-semibold mb-1" style="font-size: 11px;">FACIAL VERIFICATION STATUS</div>
                                    <div id="viewUserFaceVerified">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Not Verified</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= EDIT MODE (EXPANDED HORIZONTALLY) ================= --}}
            <div id="userModalEditSection" class="modal-body p-4 text-start d-none">
                <form id="editUserForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    
                    <div class="alert alert-primary border-0 small rounded-3 mb-4 py-2.5 px-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-pen-to-square fs-6"></i>
                        <span>Editing account information for <strong id="editUserHeaderName">User</strong>. Update the fields below and click <strong>Save Changes</strong>.</span>
                    </div>

                    {{-- Form Row 1: Account Essentials (Name, Email, Role, Status, Phone) --}}
                    <div class="card border shadow-xs rounded-3 mb-3">
                        <div class="card-header bg-light py-2 px-3 border-bottom fw-bold text-dark small text-uppercase">
                            <i class="fa-solid fa-user-gear text-primary me-1.5"></i> Account & Credentials
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        FULL NAME <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="name" id="editUserName" class="form-control rounded-3" style="text-transform: uppercase;" required placeholder="e.g. JUAN DELA CRUZ">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        EMAIL ADDRESS <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" name="email" id="editUserEmail" class="form-control rounded-3" required placeholder="user@example.com">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        SYSTEM ROLE <span class="text-danger">*</span>
                                    </label>
                                    <select name="role" id="editUserRoleSelect" class="form-select rounded-3">
                                        <option value="Resident">Resident</option>
                                        <option value="Admin">Administrator</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        ACCOUNT STATUS <span class="text-danger">*</span>
                                    </label>
                                    <select name="status" id="editUserStatusSelect" class="form-select rounded-3">
                                        <option value="active">Active</option>
                                        <option value="pending_approval">Pending Approval</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        PHONE / MOBILE NUMBER
                                    </label>
                                    <input type="text" name="phone_number" id="editUserPhone" class="form-control rounded-3" placeholder="09123456789">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Row 2: Demographics (Gender, Civil Status, Birthdate, Occupation, Nationality) --}}
                    <div class="card border shadow-xs rounded-3 mb-3">
                        <div class="card-header bg-light py-2 px-3 border-bottom fw-bold text-dark small text-uppercase">
                            <i class="fa-solid fa-id-card text-primary me-1.5"></i> Personal Demographics
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small fw-bold text-dark mb-1">GENDER</label>
                                    <select name="gender" id="editUserGender" class="form-select rounded-3">
                                        <option value="">-- Select Gender --</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small fw-bold text-dark mb-1">CIVIL STATUS</label>
                                    <select name="civil_status" id="editUserCivilStatus" class="form-select rounded-3">
                                        <option value="">-- Select Status --</option>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Separated">Separated</option>
                                        <option value="Divorced">Divorced</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small fw-bold text-dark mb-1">BIRTHDATE</label>
                                    <input type="date" name="birthdate" id="editUserBirthdate" class="form-control rounded-3">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small fw-bold text-dark mb-1">NATIONALITY</label>
                                    <input type="text" name="nationality" id="editUserNationality" class="form-control rounded-3" placeholder="Filipino">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">BIRTHPLACE</label>
                                    <input type="text" name="birthplace" id="editUserBirthplace" class="form-control rounded-3" placeholder="e.g. Carmen, Cebu">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark mb-1">OCCUPATION</label>
                                    <input type="text" name="occupation" id="editUserOccupation" class="form-control rounded-3" placeholder="e.g. Fisherman, Farmer, Private Employee">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Row 3: Address & Residence (Purok, Sitio, Barangay, Municipality, Province, ZIP) --}}
                    <div class="card border shadow-xs rounded-3 mb-3">
                        <div class="card-header bg-light py-2 px-3 border-bottom fw-bold text-dark small text-uppercase">
                            <i class="fa-solid fa-map-location-dot text-primary me-1.5"></i> Residence & Address
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">PUROK</label>
                                    <input type="text" name="purok" id="editUserPurok" class="form-control rounded-3" placeholder="e.g. Purok 1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">SITIO</label>
                                    <input type="text" name="sitio" id="editUserSitio" class="form-control rounded-3" placeholder="e.g. Sitio Manga">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">BARANGAY</label>
                                    <input type="text" name="barangay_address" id="editUserBarangay" class="form-control rounded-3" placeholder="Cogon East">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">MUNICIPALITY</label>
                                    <input type="text" name="municipality" id="editUserMunicipality" class="form-control rounded-3" placeholder="Carmen">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">PROVINCE</label>
                                    <input type="text" name="province" id="editUserProvince" class="form-control rounded-3" placeholder="Cebu">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-dark mb-1">ZIP CODE</label>
                                    <input type="text" name="zip_code" id="editUserZip" class="form-control rounded-3" placeholder="6005">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- In-Form Submit Actions (Centered) --}}
                    <div class="d-flex justify-content-center align-items-center gap-3 pt-3">
                        <button type="button" class="btn btn-light border px-4 py-2 rounded-3 fw-semibold shadow-xs" onclick="toggleUserModalEdit(false)">
                            <i class="fa-solid fa-xmark me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm" style="background-color: #0f2240; border-color: #0f2240;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- MODAL FOOTER --}}
            <div class="modal-footer bg-light border-top px-4 py-2.5">
                <div class="text-muted small text-center w-100" id="userModalFooterHint">
                    <i class="fa-solid fa-circle-info me-1"></i> Click <strong>Edit</strong> in the top header to modify this user account.
                </div>
            </div>
        </div>
    </div>
</div>
{{-- ===================== CONFIRM TOGGLE STATUS MODAL ===================== --}}
<div class="modal fade" id="confirmToggleModal" tabindex="-1" aria-labelledby="confirmToggleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body text-center px-4 py-4">
                <div class="mb-3" id="confirmToggleIcon">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-user-xmark fs-4"></i>
                    </div>
                </div>
                <h5 class="fw-bold text-dark mb-1" id="confirmToggleTitle">Deactivate User?</h5>
                <p class="text-muted small" id="confirmToggleMessage">This will prevent the user from logging in.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4 gap-2">
                <button type="button" class="btn btn-light rounded-3 fw-semibold px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-3 fw-semibold px-4" id="confirmToggleBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for toggle-status POST --}}
<form id="toggleStatusForm" method="POST" action="" style="display:none;">
    @csrf
    @method('PATCH')
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- TOGGLE MODAL EDIT MODE ----
    let currentUserData = null;

    function toggleUserModalEdit(editing) {
        const viewSec = document.getElementById('userModalViewSection');
        const editSec = document.getElementById('userModalEditSection');
        const btnEdit = document.getElementById('viewModalEditBtn');
        const title = document.getElementById('userModalHeaderTitle');
        const hint = document.getElementById('userModalFooterHint');

        if (editing) {
            viewSec?.classList.add('d-none');
            editSec?.classList.remove('d-none');
            btnEdit?.classList.add('d-none');
            if (title) title.textContent = 'Edit User Details';
            if (hint) {
                hint.innerHTML = '<i class="fa-solid fa-pen-to-square me-1 text-primary"></i> Modify the fields above and click <strong>Save Changes</strong>.';
            }
        } else {
            viewSec?.classList.remove('d-none');
            editSec?.classList.add('d-none');
            btnEdit?.classList.remove('d-none');
            if (title) title.textContent = 'User Details';
            if (hint) {
                hint.innerHTML = '<i class="fa-solid fa-circle-info me-1"></i> Click <strong>Edit</strong> in the top header to modify this user account.';
            }
        }
    }
    window.toggleUserModalEdit = toggleUserModalEdit;

    // ---- OPEN USER DETAILS MODAL ----
    const userDetailsModalEl = document.getElementById('viewUserModal');
    const userDetailsModal = userDetailsModalEl ? new bootstrap.Modal(userDetailsModalEl) : null;

    function openUserDetails(dataset) {
        if (!dataset || !userDetailsModal) return;
        currentUserData = dataset;
        toggleUserModalEdit(false);

        const id = dataset.id;
        const name = dataset.name || '';
        const email = dataset.email || '';
        const phone = dataset.phone || '';
        const role = dataset.role || 'Resident';
        const status = (dataset.status || 'active').toLowerCase();
        const registered = dataset.registered || '';
        const gender = dataset.gender && dataset.gender !== 'N/A' ? dataset.gender : '—';
        const birthdate = dataset.birthdate && dataset.birthdate !== 'N/A' ? dataset.birthdate : '—';
        const birthdateRaw = dataset.birthdateRaw || '';
        const age = dataset.age && dataset.age !== 'N/A' ? dataset.age : '—';
        const birthplace = dataset.birthplace && dataset.birthplace !== 'N/A' ? dataset.birthplace : '—';
        const civilStatus = dataset.civilStatus && dataset.civilStatus !== 'N/A' ? dataset.civilStatus : '—';
        const nationality = dataset.nationality || 'Filipino';
        const occupation = dataset.occupation && dataset.occupation !== 'N/A' ? dataset.occupation : '—';
        const purok = dataset.purok && dataset.purok !== 'N/A' ? dataset.purok : '—';
        const sitio = dataset.sitio && dataset.sitio !== 'N/A' ? dataset.sitio : '—';
        const barangay = dataset.barangay || 'Cogon East';
        const municipality = dataset.municipality || 'Carmen';
        const province = dataset.province || 'Cebu';
        const zip = dataset.zip || '6005';
        const idType = dataset.idType && dataset.idType !== 'N/A' ? dataset.idType : 'Not Specified';
        const idDocUrl = dataset.idDocUrl || '';
        const selfieUrl = dataset.selfieUrl || '';
        const faceVerified = dataset.faceVerified || '';

        // Populate View Section
        document.getElementById('viewUserIdLabel').textContent = dataset.userIdCode || ('USR-' + String(id).padStart(5, '0'));
        document.getElementById('viewUserAvatar').textContent = name.substring(0, 2).toUpperCase();
        document.getElementById('viewUserName').textContent = name;
        document.getElementById('viewUserEmail').textContent = email;
        document.getElementById('viewUserPhone').textContent = phone || 'No phone provided';
        document.getElementById('viewUserContactCard').textContent = phone || 'No phone provided';
        document.getElementById('viewUserRegistered').textContent = registered;

        // Badges
        document.getElementById('viewUserRoleBadge').innerHTML = role === 'Admin'
            ? '<span class="badge bg-opacity-10 px-2.5 py-1 rounded-pill" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; font-size: 11px;">Admin</span>'
            : '<span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1 rounded-pill" style="font-size: 11px;">Resident</span>';

        let statusBadge = '<span class="status-badge status-inactive">Inactive</span>';
        if (status === 'active') {
            statusBadge = '<span class="status-badge status-active">Active</span>';
        } else if (status === 'pending_approval') {
            statusBadge = '<span class="status-badge status-pending-approval">Pending</span>';
        }
        document.getElementById('viewUserStatusBadge').innerHTML = statusBadge;

        document.getElementById('viewUserGender').textContent = gender;
        document.getElementById('viewUserCivilStatus').textContent = civilStatus;
        document.getElementById('viewUserBirthdate').textContent = birthdate;
        document.getElementById('viewUserAge').textContent = age;
        document.getElementById('viewUserBirthplace').textContent = birthplace;
        document.getElementById('viewUserNationality').textContent = nationality;
        document.getElementById('viewUserOccupation').textContent = occupation;

        document.getElementById('viewUserBarangay').textContent = barangay;
        document.getElementById('viewUserPurok').textContent = purok;
        document.getElementById('viewUserSitio').textContent = sitio;
        document.getElementById('viewUserMunicipality').textContent = municipality;
        document.getElementById('viewUserProvince').textContent = province;
        document.getElementById('viewUserZip').textContent = zip;

        // ID Type & Doc
        document.getElementById('viewUserIdType').textContent = idType;
        const docContainer = document.getElementById('viewUserIdDoc');
        if (docContainer) {
            if (idDocUrl) {
                const isPdf = idDocUrl.toLowerCase().split('?')[0].endsWith('.pdf');
                docContainer.innerHTML = `
                    <a href="${idDocUrl}" target="_blank" class="btn btn-sm btn-light border text-primary rounded-2 px-3 py-1.5 fw-semibold shadow-xs">
                        <i class="fa-solid ${isPdf ? 'fa-file-pdf text-danger' : 'fa-id-card text-primary'} me-1"></i> View ID Document <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted small"></i>
                    </a>
                `;
            } else {
                docContainer.innerHTML = '<span class="text-muted small">No ID Uploaded</span>';
            }
        }

        // Selfie
        const selfieContainer = document.getElementById('viewUserSelfie');
        if (selfieContainer) {
            if (selfieUrl) {
                selfieContainer.innerHTML = `
                    <a href="${selfieUrl}" target="_blank" class="btn btn-sm btn-light border text-success rounded-2 px-3 py-1.5 fw-semibold shadow-xs">
                        <i class="fa-solid fa-camera me-1"></i> View Live Selfie <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted small"></i>
                    </a>
                `;
            } else {
                selfieContainer.innerHTML = '<span class="text-muted small">No live selfie captured</span>';
            }
        }

        // Face verified
        const faceContainer = document.getElementById('viewUserFaceVerified');
        if (faceContainer) {
            if (faceVerified) {
                faceContainer.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="fa-solid fa-circle-check me-1"></i> Verified (${faceVerified})</span>`;
            } else {
                faceContainer.innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary">Not Verified</span>';
            }
        }

        // Populate Edit Section Form
        document.getElementById('editUserHeaderName').textContent = name;
        document.getElementById('editUserName').value = name.toUpperCase();
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserRoleSelect').value = role;
        document.getElementById('editUserStatusSelect').value = status;
        document.getElementById('editUserPhone').value = phone || '';
        document.getElementById('editUserGender').value = ['Male', 'Female', 'Other'].includes(gender) ? gender : '';
        document.getElementById('editUserCivilStatus').value = ['Single', 'Married', 'Widowed', 'Separated', 'Divorced'].includes(civilStatus) ? civilStatus : '';
        document.getElementById('editUserBirthdate').value = birthdateRaw || '';
        document.getElementById('editUserNationality').value = nationality;
        document.getElementById('editUserBirthplace').value = birthplace !== '—' ? birthplace : '';
        document.getElementById('editUserOccupation').value = occupation !== '—' ? occupation : '';
        document.getElementById('editUserPurok').value = purok !== '—' ? purok : '';
        document.getElementById('editUserSitio').value = sitio !== '—' ? sitio : '';
        document.getElementById('editUserBarangay').value = barangay !== '—' ? barangay : '';
        document.getElementById('editUserMunicipality').value = municipality !== '—' ? municipality : '';
        document.getElementById('editUserProvince').value = province !== '—' ? province : '';
        document.getElementById('editUserZip').value = zip !== '—' ? zip : '';

        const baseUrl = '{{ url("/admin/users") }}';
        document.getElementById('editUserForm').action = baseUrl + '/' + id + '/update';

        // Show modal
        userDetailsModal.show();
    }
    window.openUserDetails = openUserDetails;

    // ---- CLICKABLE ROW & CARD HANDLER ----
    document.addEventListener('click', function (e) {
        const row = e.target.closest('.user-row-clickable');
        if (!row) return;

        // Ignore clicks on form inputs, checkboxes, action buttons, or links
        if (e.target.closest('input, button, a, .form-check, .user-actions-group')) {
            return;
        }

        openUserDetails(row.dataset);
    });

    // Keyboard support (Enter/Space on focused row)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            const activeEl = document.activeElement;
            if (activeEl && activeEl.classList.contains('user-row-clickable')) {
                if (e.target.closest('input, button, a, .form-check, .user-actions-group')) return;
                e.preventDefault();
                openUserDetails(activeEl.dataset);
            }
        }
    });

    const editUserNameInput = document.getElementById('editUserName');
    if (editUserNameInput) {
        editUserNameInput.addEventListener('input', function() {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            if (typeof this.value === 'string') {
                this.value = this.value.toUpperCase();
            }
            if (start !== null && end !== null) {
                this.setSelectionRange(start, end);
            }
        });
    }

    const createUserNameInput = document.getElementById('createUserName');
    if (createUserNameInput) {
        createUserNameInput.addEventListener('input', function() {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            if (typeof this.value === 'string') {
                this.value = this.value.toUpperCase();
            }
            if (start !== null && end !== null) {
                this.setSelectionRange(start, end);
            }
        });
    }

    // ---- TOGGLE STATUS (DEACTIVATE / ACTIVATE) MODAL ----
    const toggleModal    = new bootstrap.Modal(document.getElementById('confirmToggleModal'));
    const toggleForm     = document.getElementById('toggleStatusForm');
    const confirmBtn     = document.getElementById('confirmToggleBtn');
    const toggleTitle    = document.getElementById('confirmToggleTitle');
    const toggleMessage  = document.getElementById('confirmToggleMessage');
    const toggleIcon     = document.getElementById('confirmToggleIcon');

    document.querySelectorAll('.btn-toggle-status').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = this.dataset.action; // 'deactivate' or 'activate'
            const name   = this.dataset.name;
            const url    = this.dataset.url;

            toggleForm.action = url;

            if (action === 'deactivate') {
                toggleTitle.textContent   = 'Deactivate User?';
                toggleMessage.textContent = name + ' will not be able to log in until reactivated.';
                confirmBtn.className      = 'btn btn-danger rounded-3 fw-semibold px-4';
                confirmBtn.textContent    = 'Deactivate';
                toggleIcon.innerHTML = '<div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;"><i class="fa-solid fa-user-xmark fs-4"></i></div>';
            } else {
                toggleTitle.textContent   = 'Activate User?';
                toggleMessage.textContent = name + ' will be able to log in after activation.';
                confirmBtn.className      = 'btn btn-success rounded-3 fw-semibold px-4';
                confirmBtn.textContent    = 'Activate';
                toggleIcon.innerHTML = '<div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;"><i class="fa-solid fa-user-check fs-4"></i></div>';
            }

            toggleModal.show();
        });
    });

    confirmBtn.addEventListener('click', function () {
        toggleModal.hide();
        toggleForm.submit();
    });

    // ---- MULTI-SELECT & BULK DELETE FOR USERS ----
    const selectAllCheckbox = document.getElementById('selectAllUsers');
    const userCheckboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const bulkDeleteModalEl = document.getElementById('usersBulkDeleteModal');
    const bulkDeleteModal = bulkDeleteModalEl ? new bootstrap.Modal(bulkDeleteModalEl) : null;
    const bulkHiddenIds = document.getElementById('bulk_hidden_ids');
    const bulkModalCount = document.getElementById('bulk_modal_count');
    const bulkSelectedNamesList = document.getElementById('bulk_selected_names_list');

    function updateBulkBar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.user-checkbox:checked'));
        const count = checkedBoxes.length;

        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            selectedCountBadge.textContent = `${count} Selected`;
            bulkDeleteCount.textContent = count;
        } else {
            bulkActionsBar.classList.add('d-none');
        }

        // Highlight table rows and mobile cards
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                if (cb.checked) {
                    row.classList.add('table-primary');
                } else {
                    row.classList.remove('table-primary');
                }
            }
            const card = cb.closest('.mobile-user-card');
            if (card) {
                if (cb.checked) {
                    card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                } else {
                    card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                }
            }
        });

        if (selectAllCheckbox) {
            const allTableBoxes = document.querySelectorAll('#usersMasterTable .user-checkbox:not(:disabled)');
            const checkedTableBoxes = document.querySelectorAll('#usersMasterTable .user-checkbox:checked');
            selectAllCheckbox.checked = (allTableBoxes.length > 0 && checkedTableBoxes.length === allTableBoxes.length);
            selectAllCheckbox.indeterminate = (checkedTableBoxes.length > 0 && checkedTableBoxes.length < allTableBoxes.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            userCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkBar();
        });
    }

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            userCheckboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    if (bulkDeleteBtn && bulkDeleteModal) {
        bulkDeleteBtn.addEventListener('click', function () {
            const checkedBoxes = Array.from(document.querySelectorAll('.user-checkbox:checked'));
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
                li.textContent = `${cb.getAttribute('data-name') || ('User ID #' + cb.value)}`;
                bulkSelectedNamesList.appendChild(li);
            });

            bulkDeleteModal.show();
        });
    }
});
</script>

{{-- MODAL: BULK DELETE CONFIRMATION FOR USERS --}}
<div class="modal fade" id="usersBulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-trash-can fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Delete Selected Users?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to permanently delete <strong id="bulk_modal_count" class="text-danger"></strong> selected user(s)? This action will remove their profiles and access.
                </p>
                <div class="bg-light rounded-3 p-3 text-start small mb-4 border" style="max-height: 140px; overflow-y: auto;">
                    <div class="fw-semibold text-muted mb-1 text-uppercase" style="font-size: 11px;">Selected Users:</div>
                    <ul id="bulk_selected_names_list" class="mb-0 ps-3 text-dark"></ul>
                </div>
                <form id="usersBulkDeleteForm" action="{{ route('admin.users.bulk-delete') }}" method="POST" class="d-inline">
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
@endsection