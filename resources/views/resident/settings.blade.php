@extends('resident.layouts.resident')

@section('content')

{{-- ====================================================
     RESIDENT SETTINGS PAGE
     Single-page tabbed UI with 6 sections
     ====================================================  --}}

<style>
    /* ---- Settings Page Scoped Styles ---- */
    .settings-hero {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark, #1a35a0) 100%);
        border-radius: var(--radius-xl);
        padding: 28px 32px;
        color: #fff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .settings-hero::after {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.07);
    }
    .settings-hero h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
    .settings-hero p  { opacity: 0.8; font-size: 13px; margin: 0; }

    /* ---- Sidebar Nav ---- */
    .settings-nav .nav-link {
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 500;
        font-size: 14.5px;
        color: #4b5563;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 5px;
        border: 1px solid transparent;
        text-align: left;
        width: 100%;
        background: transparent;
    }
    .settings-nav .nav-link:hover {
        background: #f1f5f9;
        color: var(--color-primary);
    }
    .settings-nav .nav-link:hover .nav-icon {
        transform: scale(1.06);
        background: #e6eef7;
        color: var(--color-primary);
        box-shadow: 0 2px 6px rgba(24, 54, 93, 0.08);
    }
    .settings-nav .nav-link.active {
        background: #edf4fd;
        color: var(--color-primary);
        font-weight: 600;
        border-color: rgba(24, 54, 93, 0.1);
        box-shadow: 0 1px 3px rgba(24, 54, 93, 0.04);
    }
    .settings-nav .nav-link .nav-icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        background: #f0f5fa;
        color: #2b6cb0;
        border: 1px solid rgba(43, 108, 176, 0.12);
        box-shadow: 0 1px 2px rgba(24, 54, 93, 0.04);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .settings-nav .nav-link .nav-icon i {
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .settings-nav .nav-link.active .nav-icon {
        background: linear-gradient(135deg, #18365D 0%, #2B6CB0 100%);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(24, 54, 93, 0.28);
    }

    /* ---- Danger Nav Link (Account Management) ---- */
    .settings-nav .nav-link.nav-link-danger {
        color: #dc2626;
    }
    .settings-nav .nav-link.nav-link-danger .nav-icon {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.15);
        box-shadow: 0 1px 2px rgba(220, 38, 38, 0.04);
    }
    .settings-nav .nav-link.nav-link-danger:hover {
        background: #fff5f5;
        color: #b91c1c;
    }
    .settings-nav .nav-link.nav-link-danger:hover .nav-icon {
        background: #fee2e2;
        color: #b91c1c;
        transform: scale(1.06);
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.12);
    }
    .settings-nav .nav-link.nav-link-danger.active {
        background: #fef2f2;
        color: #991b1b;
        font-weight: 600;
        border-color: rgba(220, 38, 38, 0.2);
    }
    .settings-nav .nav-link.nav-link-danger.active .nav-icon {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    /* ---- Section Card ---- */
    .settings-card {
        background: #fff;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        border: none;
        overflow: hidden;
    }
    .settings-card-header {
        padding: 20px 24px 16px;
        border-bottom: 1px solid #f1f3f5;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .settings-card-header .section-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: #f0f5fa;
        color: var(--color-primary);
        border: 1px solid rgba(43, 108, 176, 0.12);
        box-shadow: 0 1px 3px rgba(24, 54, 93, 0.04);
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        transition: all 0.2s ease;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .settings-card-header .section-icon i {
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .settings-card-header h5 { margin: 0; font-size: 15px; font-weight: 700; color: var(--color-text); }
    .settings-card-header p  { margin: 0; font-size: 12px; color: var(--color-text-light); }
    .settings-card-body { padding: 24px; }

    /* ---- Form Controls ---- */
    .form-label-sm {
        font-size: 12px; font-weight: 600;
        color: var(--color-text-light, #6b7280);
        text-transform: uppercase; letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .form-control-settings {
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background: #f9fafb;
    }
    .form-control-settings:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(30,64,175,0.1);
        background: #fff;
        outline: none;
    }
    .form-control-settings:disabled, .form-control-settings[readonly] {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* ---- Profile Avatar ---- */
    .avatar-wrap { position: relative; display: inline-block; }
    .avatar-circle {
        width: 90px; height: 90px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .avatar-placeholder {
        width: 90px; height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--color-primary), #6366f1);
        display: flex; align-items: center; justify-content: center;
        font-size: 32px; font-weight: 700; color: #fff;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .avatar-edit-btn {
        position: absolute; bottom: 0; right: 0;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--color-primary);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
        cursor: pointer;
        border: 2px solid #fff;
        transition: transform 0.2s ease;
    }
    .avatar-edit-btn:hover { transform: scale(1.1); }

    /* ---- Password Strength ---- */
    .strength-bar { height: 6px; border-radius: 3px; background: #e5e7eb; overflow: hidden; }
    .strength-fill { height: 100%; border-radius: 3px; transition: width 0.4s ease, background 0.4s ease; width: 0%; }

    /* ---- Status Badges ---- */
    .badge-verified   { background: rgba(16,185,129,0.12); color: #059669; }
    .badge-pending    { background: rgba(245,158,11,0.12); color: #d97706; }
    .badge-unverified { background: rgba(239,68,68,0.12); color: #dc2626; }

    /* ---- Session Row ---- */
    .session-row {
        display: flex; align-items: center; gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .session-row:last-child { border-bottom: none; }
    .session-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: #f1f5f9;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #64748b; flex-shrink: 0;
    }

    /* ---- Activity Log ---- */
    .activity-row {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f3f5;
    }
    .activity-row:last-child { border-bottom: none; }
    .activity-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--color-primary);
        margin-top: 6px; flex-shrink: 0;
    }

    /* ---- Toggle Switch ---- */
    .toggle-switch { position: relative; display: inline-block; width: 52px; height: 28px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #d1d5db; border-radius: 28px;
        transition: 0.3s;
    }
    .toggle-slider:before {
        content: "";
        position: absolute;
        height: 22px; width: 22px;
        left: 3px; bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: 0.3s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .toggle-switch input:checked + .toggle-slider { background: var(--color-primary); }
    .toggle-switch input:checked + .toggle-slider:before { transform: translateX(24px); }

    /* ---- Danger Zone ---- */
    .danger-card { border: 1.5px solid #fee2e2 !important; background: #fffbfb; }
    .danger-card .settings-card-header { border-bottom-color: #fee2e2; }
    .danger-card .section-icon {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid rgba(220, 38, 38, 0.15);
        box-shadow: 0 1px 3px rgba(220, 38, 38, 0.04);
    }

    /* ---- OTP Verification Code Boxes ---- */
    .settings-code-box {
        width: 48px;
        height: 56px;
        font-size: 22px;
        text-align: center;
        font-weight: 700;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .settings-code-box:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.15);
        outline: none;
    }
    .settings-code-box.shake {
        animation: settingsShake 0.4s ease-in-out;
        border-color: #dc3545;
    }
    @keyframes settingsShake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-6px); }
        40%, 80% { transform: translateX(6px); }
    }

    /* ---- Channel Selector Options Card ---- */
    .otp-channel-card {
        transition: all 0.2s ease;
        background: #f9fafb;
        cursor: pointer;
    }
    .otp-channel-card:hover {
        border-color: var(--color-primary) !important;
        background: #f0f5fa;
    }

    /* ---- Tab pane transitions ---- */
    .tab-pane { animation: fadeInUp 0.3s ease; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ---- Divider ---- */
    .section-divider { border-top: 1px solid #f1f3f5; margin: 24px 0; }

    /* ---- Responsive ---- */
    @media (max-width: 768px) {
        .settings-nav .nav-link { font-size: 13.5px; padding: 9px 12px; gap: 10px; }
        .settings-nav .nav-link .nav-icon { width: 36px; height: 36px; font-size: 16px; border-radius: 9px; }
        .settings-card-body { padding: 16px; }
    }
</style>

<div class="container py-4">

    {{-- Hero Banner --}}
    <div class="settings-hero">
        <h1><i class="fa-solid fa-sliders me-2"></i>Settings</h1>
        <p>Manage your profile, account security, and preferences</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible rounded-3 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible rounded-3 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-circle-info"></i>
            <span>{{ session('info') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 align-items-start">

        {{-- ============================================================
             LEFT SIDEBAR: Tab Navigation
             ============================================================ --}}
        <div class="col-lg-3">
            <div class="settings-card p-3">
                <nav class="settings-nav nav flex-column" id="settingsTabs" role="tablist">

                    <button class="nav-link {{ $activeTab === 'profile'  ? 'active' : '' }}"
                            id="tab-profile" data-bs-toggle="tab" data-bs-target="#pane-profile"
                            type="button" role="tab">
                        <span class="nav-icon"><i class="fa-solid fa-user"></i></span>
                        <span>Profile Settings</span>
                    </button>

                    <button class="nav-link {{ $activeTab === 'account'  ? 'active' : '' }}"
                            id="tab-account" data-bs-toggle="tab" data-bs-target="#pane-account"
                            type="button" role="tab">
                        <span class="nav-icon"><i class="fa-solid fa-at"></i></span>
                        <span>Account Settings</span>
                    </button>

                    <button class="nav-link {{ $activeTab === 'security' ? 'active' : '' }}"
                            id="tab-security" data-bs-toggle="tab" data-bs-target="#pane-security"
                            type="button" role="tab">
                        <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                        <span>Security</span>
                    </button>

                    <button class="nav-link {{ $activeTab === 'ids'      ? 'active' : '' }}"
                            id="tab-ids" data-bs-toggle="tab" data-bs-target="#pane-ids"
                            type="button" role="tab">
                        <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span>
                        <span>My IDs / Verification</span>
                    </button>

                    <button class="nav-link {{ $activeTab === 'activity' ? 'active' : '' }}"
                            id="tab-activity" data-bs-toggle="tab" data-bs-target="#pane-activity"
                            type="button" role="tab">
                        <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                        <span>Activity Log</span>
                    </button>

                    <hr class="my-2" style="border-color: #e5e7eb; opacity: 0.8;">

                    <button class="nav-link nav-link-danger {{ $activeTab === 'manage'   ? 'active' : '' }}"
                            id="tab-manage" data-bs-toggle="tab" data-bs-target="#pane-manage"
                            type="button" role="tab">
                        <span class="nav-icon">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </span>
                        <span>Account Management</span>
                    </button>

                </nav>
            </div>
        </div>

        {{-- ============================================================
             RIGHT CONTENT: Tab Panes
             ============================================================ --}}
        <div class="col-lg-9">
            <div class="tab-content">

                {{-- ==============================
                     TAB 1 — PROFILE SETTINGS
                     ============================== --}}
                <div class="tab-pane fade {{ $activeTab === 'profile' ? 'show active' : '' }}"
                     id="pane-profile" role="tabpanel">

                    <form action="{{ route('resident.settings.profile') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Profile Picture --}}
                        <div class="settings-card mb-4">
                            <div class="settings-card-header">
                                <div class="section-icon"><i class="fa-solid fa-image"></i></div>
                                <div>
                                    <h5>Profile Picture</h5>
                                    <p>Upload a clear photo of yourself</p>
                                </div>
                            </div>
                            <div class="settings-card-body d-flex align-items-center gap-4">
                                <div class="avatar-wrap">
                                    @if($user->profile_picture)
                                        <img src="{{ Storage::url($user->profile_picture) }}"
                                             alt="Profile Picture" class="avatar-circle" id="avatarPreview">
                                    @else
                                        <div class="avatar-placeholder" id="avatarPlaceholder">
                                            {{ strtoupper(substr($user->first_name ?? $user->name ?? 'R', 0, 1)) }}
                                        </div>
                                        <img src="" alt="" class="avatar-circle d-none" id="avatarPreview">
                                    @endif
                                    <label for="profile_picture_input" class="avatar-edit-btn" title="Change photo">
                                        <i class="fa-solid fa-camera"></i>
                                    </label>
                                </div>
                                <div>
                                    <input type="file" name="profile_picture" id="profile_picture_input"
                                           accept="image/*" class="d-none" onchange="previewAvatar(this)">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-3"
                                            onclick="document.getElementById('profile_picture_input').click()">
                                        <i class="fa-solid fa-upload me-1"></i> Upload Photo
                                    </button>
                                    <div class="text-muted mt-1" style="font-size:12px;">
                                        JPG, PNG or WEBP. Max 2 MB.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Personal Information --}}
                        <div class="settings-card mb-4">
                            <div class="settings-card-header">
                                <div class="section-icon"><i class="fa-solid fa-user-pen"></i></div>
                                <div>
                                    <h5>Personal Information</h5>
                                    <p>Your legal name and personal details</p>
                                </div>
                            </div>
                            <div class="settings-card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label-sm">First Name *</label>
                                        <input type="text" name="first_name" id="first_name"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('first_name', $user->first_name ?? explode(' ', $user->name)[0] ?? '') }}"
                                               required placeholder="e.g. JUAN">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Middle Name</label>
                                        <input type="text" name="middle_name" id="middle_name"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('middle_name', $user->middle_name ?? '') }}"
                                               placeholder="e.g. SANTOS">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-sm">Last Name *</label>
                                        <input type="text" name="last_name" id="last_name"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('last_name', $user->last_name ?? '') }}"
                                               required placeholder="e.g. DELA CRUZ">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label-sm">Suffix</label>
                                        <input type="text" name="suffix" id="suffix"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('suffix', $user->suffix ?? '') }}"
                                               placeholder="JR.">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label-sm">Date of Birth</label>
                                        <input type="date" name="birthdate" id="birthdate"
                                               class="form-control form-control-settings"
                                               value="{{ old('birthdate', $user->birthdate ? $user->birthdate : '') }}"
                                               {{ trim(strtolower($user->status)) === 'active' ? 'readonly title=\'Locked: your birthdate has been verified by the admin.\'' : '' }}>
                                        @if(trim(strtolower($user->status)) === 'active')
                                            <div class="mt-1" style="font-size:11px; color:#6b7280;">
                                                <i class="fa-solid fa-lock me-1"></i>Locked after admin verification.
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-sm">Sex</label>
                                        <select name="gender" id="gender" class="form-control form-control-settings">
                                            <option value="">— Select —</option>
                                            @php
                                                $curGender = old('gender', $user->gender);
                                            @endphp
                                            <option value="Male" {{ in_array($curGender, ['Male', 'Man']) ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ in_array($curGender, ['Female', 'Woman']) ? 'selected' : '' }}>Female</option>
                                            <option value="Prefer not to say" {{ $curGender === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-sm">Civil Status</label>
                                        <select name="civil_status" id="civil_status" class="form-control form-control-settings">
                                            <option value="">— Select —</option>
                                            @foreach(['Single','Married','Widowed','Separated','Annulled'] as $cs)
                                                <option value="{{ $cs }}" {{ old('civil_status', $user->civil_status) === $cs ? 'selected' : '' }}>{{ $cs }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-sm">Nationality</label>
                                        <input type="text" name="nationality" id="nationality"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('nationality', $user->nationality ?? 'FILIPINO') }}"
                                               placeholder="FILIPINO">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-sm">Occupation</label>
                                        <input type="text" name="occupation" id="occupation"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('occupation', $user->occupation ?? '') }}"
                                               placeholder="e.g. TEACHER, FARMER, STUDENT...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-sm">Contact Number</label>
                                        <input type="text" name="phone_number" id="phone_number"
                                               class="form-control form-control-settings"
                                               value="{{ old('phone_number', $user->phone_number ?? '') }}"
                                               placeholder="e.g. 09171234567">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="settings-card mb-4">
                            <div class="settings-card-header">
                                <div class="section-icon"><i class="fa-solid fa-location-dot"></i></div>
                                <div>
                                    <h5>Home Address</h5>
                                    <p>Your registered address in the barangay</p>
                                </div>
                            </div>
                            <div class="settings-card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label-sm">House / Unit No.</label>
                                        <input type="text" name="house_number" id="house_number"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('house_number', $user->house_number ?? '') }}"
                                               placeholder="e.g. 12B">
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label-sm">Street / Sitio</label>
                                        <input type="text" name="street" id="street"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('street', $user->street ?? '') }}"
                                               placeholder="e.g. RIZAL STREET">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Barangay</label>
                                        <input type="text" name="barangay_address" id="barangay_address"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('barangay_address', $user->barangay_address ?? 'COGON EAST') }}"
                                               placeholder="e.g. COGON EAST">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-sm">Municipality / City</label>
                                        <input type="text" name="municipality" id="municipality"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('municipality', $user->municipality ?? 'CARMEN, CEBU') }}"
                                               placeholder="e.g. CARMEN, CEBU">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label-sm">Province</label>
                                        <input type="text" name="province" id="province"
                                               class="form-control form-control-settings"
                                               style="text-transform: uppercase;"
                                               value="{{ old('province', $user->province ?? 'CEBU') }}"
                                               placeholder="e.g. CEBU">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label-sm">ZIP Code</label>
                                        <input type="text" name="zip_code" id="zip_code"
                                               class="form-control form-control-settings"
                                               value="{{ old('zip_code', $user->zip_code ?? '6005') }}"
                                               placeholder="6005">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Save Profile
                            </button>
                        </div>
                    </form>
                </div>{{-- end pane-profile --}}


                {{-- ==============================
                     TAB 2 — ACCOUNT SETTINGS
                     ============================== --}}
                <div class="tab-pane fade {{ $activeTab === 'account' ? 'show active' : '' }}"
                     id="pane-account" role="tabpanel">

                    {{-- Username & Email --}}
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon"><i class="fa-solid fa-at"></i></div>
                            <div>
                                <h5>Account Details</h5>
                                <p>Manage your username and email address</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="{{ route('resident.settings.account') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-sm" for="username">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-3" style="border:1.5px solid #e5e7eb; border-right:none;">
                                                <i class="fa-solid fa-at text-muted" style="font-size:13px;"></i>
                                            </span>
                                            <input type="text" name="username" id="username"
                                                   class="form-control form-control-settings rounded-end-3"
                                                   style="border-left:none;"
                                                   value="{{ old('username', $user->username ?? '') }}"
                                                   placeholder="your_username"
                                                   pattern="[a-zA-Z0-9_\-]+" title="Letters, numbers, _ and - only">
                                        </div>
                                        <div class="mt-1" style="font-size:11px;color:#6b7280;">Letters, numbers, underscores and hyphens only.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-sm" for="account_email">Email Address</label>
                                        <div class="input-group">
                                            <input type="email" name="email" id="account_email"
                                                   class="form-control form-control-settings rounded-start-3"
                                                   style="border-right:none;"
                                                   value="{{ old('email', $user->email ?? '') }}"
                                                   placeholder="your@email.com">
                                            <span class="input-group-text bg-light border-0 rounded-end-3"
                                                  style="border:1.5px solid #e5e7eb; border-left:none;">
                                                @if($user->hasVerifiedEmail())
                                                    <span class="badge badge-verified rounded-pill py-1 px-2" style="font-size:10px;">
                                                        <i class="fa-solid fa-circle-check me-1"></i>Verified
                                                    </span>
                                                @else
                                                    <span class="badge badge-unverified rounded-pill py-1 px-2" style="font-size:10px;">
                                                        <i class="fa-solid fa-circle-xmark me-1"></i>Unverified
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                        @if(!$user->hasVerifiedEmail())
                                            <div class="mt-1" style="font-size:11px;color:#d97706;">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                                Email not verified.
                                                <form action="{{ route('verification.send') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link p-0 text-primary" style="font-size:11px;">Resend verification email</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Save Account Details
                                    </button>
                                </div>
                            </form>

                            {{-- Linked Accounts --}}
                            <div class="section-divider"></div>
                            <h6 class="fw-bold mb-3" style="font-size:13px; color:var(--color-text-light); text-transform:uppercase; letter-spacing:0.5px;">
                                Linked Accounts
                            </h6>
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background:#f9fafb; border:1.5px solid #e5e7eb;">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width:36px;height:36px;border-radius:10px;background:#fff;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                                        <i class="fa-brands fa-google" style="color:#EA4335;font-size:16px;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size:14px;">Google</div>
                                        <div style="font-size:12px;color:#6b7280;">
                                            @if($user->google_id)
                                                Connected as {{ $user->email }}
                                            @else
                                                Not connected
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($user->google_id)
                                    <span class="badge badge-verified rounded-pill px-3 py-2">
                                        <i class="fa-solid fa-link me-1"></i>Connected
                                    </span>
                                @else
                                    <a href="{{ route('google.login') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                                        <i class="fa-solid fa-link me-1"></i>Connect
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Change Password --}}
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon"><i class="fa-solid fa-lock"></i></div>
                            <div>
                                <h5>Change Password</h5>
                                <p>Use a strong password with letters, numbers & symbols</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="{{ route('resident.settings.password') }}" method="POST" id="passwordChangeForm">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label-sm" for="current_password">Current Password</label>
                                        <div class="input-group">
                                            <input type="password" name="current_password" id="current_password"
                                                   class="form-control form-control-settings rounded-start-3"
                                                   style="border-right:none;" placeholder="Enter current password">
                                            <button type="button" class="input-group-text bg-light border-0 rounded-end-3"
                                                    style="border:1.5px solid #e5e7eb;border-left:none;cursor:pointer;"
                                                    onclick="togglePwd('current_password', this)">
                                                <i class="fa-regular fa-eye text-muted" style="font-size:13px;"></i>
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-sm" for="password">New Password</label>
                                        <div class="input-group">
                                            <input type="password" name="password" id="password"
                                                   class="form-control form-control-settings rounded-start-3"
                                                   style="border-right:none;" placeholder="New password"
                                                   oninput="checkStrength(this.value)">
                                            <button type="button" class="input-group-text bg-light border-0 rounded-end-3"
                                                    style="border:1.5px solid #e5e7eb;border-left:none;cursor:pointer;"
                                                    onclick="togglePwd('password', this)">
                                                <i class="fa-regular fa-eye text-muted" style="font-size:13px;"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <div class="strength-bar mt-1">
                                                <div class="strength-fill" id="strengthFill"></div>
                                            </div>
                                            <div id="strengthLabel" class="mt-1" style="font-size:11px;color:#6b7280;"></div>
                                        </div>
                                        <ul class="mt-2 ps-3 mb-0" style="font-size:11px;color:#6b7280;">
                                            <li id="req-len">At least 8 characters</li>
                                            <li id="req-upper">At least one uppercase letter</li>
                                            <li id="req-number">At least one number</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-sm" for="password_confirmation">Confirm New Password</label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" id="password_confirmation"
                                                   class="form-control form-control-settings rounded-start-3"
                                                   style="border-right:none;" placeholder="Repeat new password">
                                            <button type="button" class="input-group-text bg-light border-0 rounded-end-3"
                                                    style="border:1.5px solid #e5e7eb;border-left:none;cursor:pointer;"
                                                    onclick="togglePwd('password_confirmation', this)">
                                                <i class="fa-regular fa-eye text-muted" style="font-size:13px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
                                        <i class="fa-solid fa-key me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>{{-- end pane-account --}}


                {{-- ==============================
                     TAB 3 — SECURITY
                     ============================== --}}
                <div class="tab-pane fade {{ $activeTab === 'security' ? 'show active' : '' }}"
                     id="pane-security" role="tabpanel">

                    {{-- 2FA Toggle --}}
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                            <div>
                                <h5>Two-Factor Authentication</h5>
                                <p>Add an extra layer of security to your account</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <form action="{{ route('resident.settings.security') }}" method="POST" id="twoFactorForm">
                                @csrf
                                <div class="d-flex align-items-center justify-content-between p-4 rounded-3" style="background:#f9fafb;border:1.5px solid #e5e7eb;">
                                    <div>
                                        <div class="fw-bold mb-1" style="font-size:14px;">
                                            <i class="fa-solid fa-shield-halved me-2 text-primary"></i>
                                            Two-Factor Authentication (2FA)
                                        </div>
                                        <div style="font-size:13px;color:#6b7280;">
                                            When enabled, you'll be asked to verify your identity with an OTP on each login.
                                        </div>
                                        <div class="mt-2">
                                            @if($user->two_factor_enabled)
                                                <span class="badge badge-verified rounded-pill px-3 py-2">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Enabled
                                                </span>
                                            @else
                                                <span class="badge badge-unverified rounded-pill px-3 py-2">
                                                    <i class="fa-solid fa-circle-xmark me-1"></i>Disabled
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-center gap-2">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="two_factor_enabled" value="1"
                                                   {{ $user->two_factor_enabled ? 'checked' : '' }}
                                                   onchange="document.getElementById('twoFactorForm').submit()">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span style="font-size:11px;color:#6b7280;">
                                            {{ $user->two_factor_enabled ? 'ON' : 'OFF' }}
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Active Sessions --}}
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon"><i class="fa-solid fa-desktop"></i></div>
                            <div>
                                <h5>Active Sessions</h5>
                                <p>Devices currently logged into your account</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            @if($sessions->isEmpty())
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-regular fa-clock-rotate-left fa-2x mb-2 d-block"></i>
                                    <small>No session data available.</small>
                                </div>
                            @else
                                @foreach($sessions as $session)
                                    <div class="session-row">
                                        <div class="session-icon">
                                            @if($session->device === 'Mobile')
                                                <i class="fa-solid fa-mobile-screen"></i>
                                            @else
                                                <i class="fa-solid fa-desktop"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold" style="font-size:14px;">
                                                {{ $session->browser }} on {{ $session->device }}
                                                @if($session->is_current)
                                                    <span class="badge badge-verified ms-2 rounded-pill px-2 py-1" style="font-size:10px;">This Device</span>
                                                @endif
                                            </div>
                                            <div style="font-size:12px;color:#6b7280;">
                                                IP: {{ $session->ip_address ?? 'Unknown' }}
                                                &bull; Last active: {{ $session->last_active_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            <div class="section-divider"></div>

                            {{-- Logout Other Devices --}}
                            <form action="{{ route('resident.settings.logout-others') }}" method="POST" class="mb-3">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-7">
                                        <label class="form-label-sm" for="current_password_sessions">
                                            Confirm Password to Logout Other Devices
                                        </label>
                                        <input type="password" name="current_password_sessions"
                                               id="current_password_sessions"
                                               class="form-control form-control-settings"
                                               placeholder="Enter your password">
                                        @error('current_password_sessions')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <button type="submit" class="btn btn-warning w-100 py-2 rounded-3 fw-bold">
                                            <i class="fa-solid fa-right-from-bracket me-2"></i>Logout Other Devices
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Logout All Devices --}}
                            <form action="{{ route('resident.settings.logout-all') }}" method="POST">
                                @csrf
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-7">
                                        <label class="form-label-sm" for="current_password_all">
                                            Confirm Password to Logout All Devices
                                        </label>
                                        <input type="password" name="current_password_all"
                                               id="current_password_all"
                                               class="form-control form-control-settings"
                                               placeholder="Enter your password">
                                        @error('current_password_all')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <button type="submit" class="btn btn-danger w-100 py-2 rounded-3 fw-bold">
                                            <i class="fa-solid fa-power-off me-2"></i>Logout All Devices
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>{{-- end pane-security --}}


                {{-- ==============================
                     TAB 4 — MY IDs / VERIFICATION
                     ============================== --}}
                <div class="tab-pane fade {{ $activeTab === 'ids' ? 'show active' : '' }}"
                     id="pane-ids" role="tabpanel">

                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon"><i class="fa-solid fa-id-card"></i></div>
                            <div>
                                <h5>Valid ID / Verification</h5>
                                <p>Upload a government-issued ID to verify your identity</p>
                            </div>
                        </div>
                        <div class="settings-card-body">

                            {{-- Current ID Status --}}
                            @if($user->id_photo)
                                <div class="p-3 rounded-3 mb-4" style="background:#f9fafb;border:1.5px solid #e5e7eb;">
                                    <div class="row align-items-center">
                                        <div class="col-md-3 text-center mb-3 mb-md-0">
                                            <img src="{{ Storage::url($user->id_photo) }}"
                                                 alt="Valid ID" class="rounded-3 shadow-sm"
                                                 style="max-width:100%;max-height:140px;object-fit:cover;border:2px solid #e5e7eb;">
                                        </div>
                                        <div class="col-md-9">
                                            <div class="mb-2">
                                                <span class="text-muted small fw-bold text-uppercase">ID Type</span>
                                                <div class="fw-bold mt-1">{{ $user->id_type ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <span class="text-muted small fw-bold text-uppercase">Verification Status</span>
                                                    <div class="mt-1">
                                                        @if($user->id_verified_at)
                                                            <span class="badge badge-verified rounded-pill px-3 py-2">
                                                                <i class="fa-solid fa-circle-check me-1"></i>Verified
                                                            </span>
                                                        @else
                                                            <span class="badge badge-pending rounded-pill px-3 py-2">
                                                                <i class="fa-solid fa-clock me-1"></i>Pending Verification
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <span class="text-muted small fw-bold text-uppercase">Uploaded</span>
                                                    <div class="mt-1" style="font-size:13px;">
                                                        {{ $user->id_uploaded_at ? $user->id_uploaded_at->format('M d, Y') : 'N/A' }}
                                                    </div>
                                                </div>
                                                @if($user->id_expiry_date)
                                                    <div class="col-6">
                                                        <span class="text-muted small fw-bold text-uppercase">Expiry Date</span>
                                                        <div class="mt-1" style="font-size:13px;
                                                            {{ $user->id_expiry_date->isPast() ? 'color:#dc2626;' : '' }}">
                                                            {{ $user->id_expiry_date->format('M d, Y') }}
                                                            @if($user->id_expiry_date->isPast())
                                                                <span class="badge badge-unverified ms-1">Expired</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($user->id_verified_at)
                                                    <div class="col-6">
                                                        <span class="text-muted small fw-bold text-uppercase">Verified On</span>
                                                        <div class="mt-1" style="font-size:13px;">
                                                            {{ $user->id_verified_at->format('M d, Y') }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4 mb-4" style="background:#f9fafb;border:1.5px dashed #d1d5db;border-radius:12px;">
                                    <i class="fa-regular fa-id-card fa-3x text-muted mb-3 d-block" style="opacity:0.4;"></i>
                                    <div class="fw-bold text-muted mb-1">No ID uploaded yet</div>
                                    <div class="text-muted small">Upload a valid government-issued ID to verify your identity</div>
                                </div>
                            @endif

                            {{-- Upload Form --}}
                            <form action="{{ route('resident.settings.id') }}" method="POST" enctype="multipart/form-data"
                                  id="idUploadForm">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-sm" for="id_type">ID Type *</label>
                                        <select name="id_type" id="id_type" class="form-control form-control-settings" required>
                                            <option value="">— Select ID Type —</option>
                                            @foreach(["National ID","Driver's License","Passport","PhilHealth","UMID","Voter's ID"] as $idType)
                                                <option value="{{ $idType }}" {{ old('id_type', $user->id_type) === $idType ? 'selected' : '' }}>
                                                    {{ $idType }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-sm" for="id_expiry_date">Expiry Date (if applicable)</label>
                                        <input type="date" name="id_expiry_date" id="id_expiry_date"
                                               class="form-control form-control-settings"
                                               value="{{ old('id_expiry_date', $user->id_expiry_date ? $user->id_expiry_date->format('Y-m-d') : '') }}"
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label-sm" for="id_photo_input">ID Photo *</label>
                                        <div class="d-flex align-items-center gap-3" id="idUploadArea"
                                             style="padding:20px;background:#f9fafb;border:2px dashed #d1d5db;border-radius:12px;cursor:pointer;"
                                             onclick="document.getElementById('id_photo_input').click()">
                                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted"></i>
                                            <div>
                                                <div class="fw-bold" style="font-size:14px;" id="idUploadLabel">
                                                    Click to upload or drag and drop
                                                </div>
                                                <div class="text-muted small">JPG, PNG or WEBP. Max 5 MB.</div>
                                            </div>
                                        </div>
                                        <input type="file" name="id_photo" id="id_photo_input" accept="image/*"
                                               class="d-none" required onchange="previewId(this)">
                                        <img id="idPreviewImg" src="" alt="ID Preview"
                                             class="d-none mt-3 rounded-3 shadow-sm"
                                             style="max-height:180px;max-width:100%;object-fit:contain;border:2px solid #e5e7eb;">
                                    </div>
                                </div>
                                <div class="d-flex gap-2 justify-content-end mt-3">
                                    @if($user->id_photo)
                                        <button type="submit" class="btn btn-outline-primary px-4 py-2 rounded-3 fw-bold">
                                            <i class="fa-solid fa-arrow-rotate-right me-2"></i>Replace ID
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm">
                                            <i class="fa-solid fa-upload me-2"></i>Upload ID
                                        </button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>{{-- end pane-ids --}}


                {{-- ==============================
                     TAB 5 — ACTIVITY LOG
                     ============================== --}}
                <div class="tab-pane fade {{ $activeTab === 'activity' ? 'show active' : '' }}"
                     id="pane-activity" role="tabpanel">

                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                            <div>
                                <h5>Activity Log</h5>
                                <p>Recent actions performed on your account</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            @if($activityLogs->isEmpty())
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-clock-rotate-left fa-3x mb-3 d-block" style="opacity:0.3;"></i>
                                    <div class="fw-bold mb-1">No activity recorded yet</div>
                                    <small>Your account actions will appear here.</small>
                                </div>
                            @else
                                @foreach($activityLogs as $log)
                                    <div class="activity-row">
                                        <div class="activity-dot mt-2"></div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-bold" style="font-size:14px;">{{ $log->action }}</div>
                                                    @if($log->description)
                                                        <div class="text-muted" style="font-size:12px;">{{ $log->description }}</div>
                                                    @endif
                                                    <div class="mt-1" style="font-size:11px;color:#9ca3af;">
                                                        @if($log->ip_address)
                                                            <i class="fa-solid fa-location-dot me-1"></i>{{ $log->ip_address }}
                                                            &bull;
                                                        @endif
                                                        {{ $log->created_at->format('M d, Y h:i A') }}
                                                    </div>
                                                </div>
                                                <div style="font-size:11px;color:#9ca3af;white-space:nowrap;margin-left:12px;">
                                                    {{ $log->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>{{-- end pane-activity --}}


                {{-- ==============================
                     TAB 6 — ACCOUNT MANAGEMENT
                     ============================== --}}
                <div class="tab-pane fade {{ $activeTab === 'manage' ? 'show active' : '' }}"
                     id="pane-manage" role="tabpanel">

                    {{-- Quick Actions --}}
                    <div class="settings-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
                            <div>
                                <h5>Session Actions</h5>
                                <p>Log out of your account</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <form action="{{ route('resident.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary px-4 py-2 rounded-3 fw-bold">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                                    </button>
                                </form>
                                <form action="{{ route('resident.settings.logout-all') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="current_password_all" id="quick_logout_all_pass" value="">
                                    <button type="button" class="btn btn-outline-warning px-4 py-2 rounded-3 fw-bold"
                                            onclick="promptLogoutAll()">
                                        <i class="fa-solid fa-power-off me-2"></i>Logout All Devices
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Deactivate Account --}}
                    <div class="settings-card danger-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon" style="background:rgba(245,158,11,0.1);color:#d97706;">
                                <i class="fa-solid fa-pause-circle"></i>
                            </div>
                            <div>
                                <h5 style="color:#d97706;">Deactivate Account</h5>
                                <p>Temporarily suspend your account. The barangay can reactivate it.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="alert rounded-3 mb-3" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);color:#92400e;font-size:13px;">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <strong>Warning:</strong> Deactivating your account will prevent you from logging in and accessing services until the barangay reactivates it.
                            </div>
                            <button type="button" class="btn btn-warning px-4 py-2 rounded-3 fw-bold"
                                    data-bs-toggle="modal" data-bs-target="#deactivateModal">
                                <i class="fa-solid fa-pause me-2"></i>Deactivate My Account
                            </button>
                        </div>
                    </div>

                    {{-- Delete Account --}}
                    <div class="settings-card danger-card mb-4">
                        <div class="settings-card-header">
                            <div class="section-icon"><i class="fa-solid fa-trash"></i></div>
                            <div>
                                <h5 style="color:#dc2626;">Delete Account</h5>
                                <p>Permanently remove your account and all associated data.</p>
                            </div>
                        </div>
                        <div class="settings-card-body">
                            <div class="alert rounded-3 mb-3" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);color:#7f1d1d;font-size:13px;">
                                <i class="fa-solid fa-skull-crossbones me-2"></i>
                                <strong>This action is irreversible.</strong> All your data, document requests, and account information will be permanently deleted and cannot be recovered.
                            </div>
                            <button type="button" class="btn btn-danger px-4 py-2 rounded-3 fw-bold"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fa-solid fa-trash me-2"></i>Delete My Account
                            </button>
                        </div>
                    </div>

                </div>{{-- end pane-manage --}}

            </div>{{-- end tab-content --}}
        </div>{{-- end col-lg-9 --}}

    </div>{{-- end row --}}
</div>{{-- end container --}}


{{-- ============================================================
     MODALS
     ============================================================ --}}

{{-- Deactivate Confirmation Modal --}}
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 py-4 justify-content-center flex-column text-center"
                 style="background:rgba(245,158,11,0.1);">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm"
                     style="width:60px;height:60px;background:#fff;">
                    <i class="fa-solid fa-pause fs-4 text-warning"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1" id="deactivateModalLabel">Deactivate Account?</h5>
                <p class="text-muted small mb-0">Type <strong>DEACTIVATE</strong> to confirm.</p>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('resident.settings.deactivate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label-sm" for="confirm_deactivate">Type DEACTIVATE to confirm</label>
                        <input type="text" name="confirm_deactivate" id="confirm_deactivate"
                               class="form-control form-control-settings text-center fw-bold"
                               placeholder="DEACTIVATE" required autocomplete="off">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1 rounded-3 fw-bold"
                                data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning flex-grow-1 rounded-3 fw-bold">
                            <i class="fa-solid fa-pause me-2"></i>Deactivate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Delete Account Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 py-4 justify-content-center flex-column text-center"
                 style="background:rgba(239,68,68,0.1);">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm"
                     style="width:60px;height:60px;background:#fff;">
                    <i class="fa-solid fa-trash fs-4 text-danger"></i>
                </div>
                <h5 class="modal-title fw-bold mb-1 text-danger" id="deleteModalLabel">Delete Account Permanently?</h5>
                <p class="text-muted small mb-0">This <strong>cannot be undone.</strong> Type <strong>DELETE</strong> and enter your password.</p>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('resident.settings.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mb-3">
                        <label class="form-label-sm" for="confirm_delete">Type DELETE to confirm</label>
                        <input type="text" name="confirm_delete" id="confirm_delete"
                               class="form-control form-control-settings text-center fw-bold"
                               placeholder="DELETE" required autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label class="form-label-sm" for="password_delete">Your Password</label>
                        <input type="password" name="password_delete" id="password_delete"
                               class="form-control form-control-settings"
                               placeholder="Enter your password" required>
                        @error('password_delete')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1 rounded-3 fw-bold"
                                data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger flex-grow-1 rounded-3 fw-bold">
                            <i class="fa-solid fa-trash me-2"></i>Delete Forever
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- ACCOUNT SETTINGS OTP VERIFICATION MODAL     --}}
{{-- ============================================ --}}
<div class="modal fade" id="settingsOtpModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="settingsOtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            {{-- STEP 1: CHANNEL CHOICE (Only displayed when user has BOTH email and phone registered) --}}
            <div id="settingsOtpChannelStep" class="modal-body text-center p-4 d-none">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; background-color: #e8f0fe; color: #0b2e59; border-radius: 50%; font-size: 1.75rem;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <h5 class="fw-bold text-dark mb-1">Choose Verification Method</h5>
                <p class="text-muted small mb-4">
                    Select where you would like us to send your 5-digit security code:
                </p>

                <div class="d-flex flex-column gap-2 mb-4">
                    <label class="otp-channel-card border rounded-3 p-3 text-start d-flex align-items-center gap-3 position-relative">
                        <input type="radio" name="otp_channel_choice" value="email" class="form-check-input my-0" id="otpChoiceEmail" checked>
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <i class="fa-solid fa-envelope text-primary fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark" style="font-size:14px;">Email Address</div>
                            <div class="text-muted small" id="choiceEmailLabel">---</div>
                        </div>
                    </label>

                    <label class="otp-channel-card border rounded-3 p-3 text-start d-flex align-items-center gap-3 position-relative">
                        <input type="radio" name="otp_channel_choice" value="phone" class="form-check-input my-0" id="otpChoicePhone">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <i class="fa-solid fa-mobile-screen-button text-success fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark" style="font-size:14px;">SMS / Mobile Phone</div>
                            <div class="text-muted small" id="choicePhoneLabel">---</div>
                        </div>
                    </label>
                </div>

                <button type="button" class="btn btn-primary w-100 rounded-3 py-2.5 fw-semibold shadow-sm mb-2" id="btnConfirmChannelChoice" onclick="confirmChannelAndSendOtp()">
                    <i class="fa-solid fa-paper-plane me-1"></i> Send Verification Code
                </button>
                <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>

            {{-- STEP 2: CODE ENTRY & VERIFICATION --}}
            <div id="settingsOtpCodeStep" class="modal-body text-center p-4">
                
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; background-color: #e8f0fe; color: #0b2e59; border-radius: 50%; font-size: 1.75rem;">
                    <i class="fa-solid fa-shield-halved" id="settingsOtpIcon"></i>
                </div>

                <h5 class="fw-bold text-dark mb-1">Security Verification</h5>
                <p class="text-muted small mb-2">
                    To authorize this account update, enter the 5-digit code sent to:
                </p>
                
                <div class="mb-3">
                    <span class="badge bg-light text-primary border px-3 py-2 fw-bold fs-6 text-break" id="settingsOtpTargetLabel">---</span>
                </div>

                <div id="settingsOtpAlert" class="alert alert-danger border-0 small rounded-3 py-2 mb-3 d-none text-start"></div>

                <!-- 5-Digit OTP Inputs -->
                <div class="d-flex justify-content-center gap-2 mb-3" id="settingsOtpInputsContainer">
                    <input type="text" class="settings-code-box form-control text-center fw-bold fs-4" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="0">
                    <input type="text" class="settings-code-box form-control text-center fw-bold fs-4" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="1">
                    <input type="text" class="settings-code-box form-control text-center fw-bold fs-4" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="2">
                    <input type="text" class="settings-code-box form-control text-center fw-bold fs-4" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="3">
                    <input type="text" class="settings-code-box form-control text-center fw-bold fs-4" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="4">
                </div>

                <button type="button" class="btn btn-primary w-100 rounded-3 py-2.5 fw-semibold mb-3 shadow-sm" id="btnVerifySettingsOtp" onclick="submitSettingsOtpVerification()">
                    <i class="fa-solid fa-lock-open me-1"></i> Verify & Confirm
                </button>

                <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
                    <span id="settingsOtpCountdownText">Resend code in <strong id="settingsOtpTimerSeconds">60</strong>s</span>
                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary d-none" id="btnResendSettingsOtp" onclick="requestSettingsOtp(true)">
                        <i class="fa-solid fa-rotate-right me-1"></i> Resend Code
                    </button>

                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-secondary d-none ms-auto me-2" id="btnBackToChannels" onclick="showChannelStep()">
                        <i class="fa-solid fa-arrow-left me-1"></i> Change Method
                    </button>

                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted ms-auto" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- ============================================================
     PAGE SCRIPTS
     ============================================================ --}}
<script>
    // ---- User Contacts for OTP Authorization ----
    const residentEmail = @json($user->email ?? '');
    const residentPhone = @json($user->phone_number ?? '');

    let activeSettingsOtpChannel = residentEmail ? 'email' : (residentPhone ? 'phone' : 'email');
    let settingsOtpModalInstance = null;
    let settingsOtpTimerInterval = null;
    let activeFormToSubmit = null;

    // ---- Avatar Preview ----
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const placeholder = document.getElementById('avatarPlaceholder');
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                if (placeholder) placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ---- ID Preview ----
    function previewId(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('idPreviewImg');
                const label = document.getElementById('idUploadLabel');
                img.src = e.target.result;
                img.classList.remove('d-none');
                label.textContent = input.files[0].name;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ---- Password Toggle ----
    function togglePwd(fieldId, btn) {
        const field = document.getElementById(fieldId);
        const icon = btn.querySelector('i');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // ---- Password Strength Meter ----
    function checkStrength(val) {
        const fill = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        const reqLen = document.getElementById('req-len');
        const reqUpper = document.getElementById('req-upper');
        const reqNum = document.getElementById('req-number');

        let score = 0;
        const hasLen    = val.length >= 8;
        const hasUpper  = /[A-Z]/.test(val);
        const hasLower  = /[a-z]/.test(val);
        const hasNum    = /[0-9]/.test(val);
        const hasSymbol = /[^A-Za-z0-9]/.test(val);

        // Update checklist
        reqLen.style.color   = hasLen   ? '#059669' : '#6b7280';
        reqUpper.style.color = hasUpper ? '#059669' : '#6b7280';
        reqNum.style.color   = hasNum   ? '#059669' : '#6b7280';

        if (hasLen)    score++;
        if (hasUpper)  score++;
        if (hasLower)  score++;
        if (hasNum)    score++;
        if (hasSymbol) score++;

        const levels = [
            { pct: '0%',   color: '#e5e7eb', text: '' },
            { pct: '20%',  color: '#ef4444', text: 'Very Weak' },
            { pct: '40%',  color: '#f97316', text: 'Weak' },
            { pct: '60%',  color: '#eab308', text: 'Fair' },
            { pct: '80%',  color: '#22c55e', text: 'Strong' },
            { pct: '100%', color: '#16a34a', text: 'Very Strong' },
        ];

        const level = levels[Math.min(score, 5)];
        fill.style.width      = level.pct;
        fill.style.background = level.color;
        label.textContent     = level.text;
        label.style.color     = level.color;
    }

    // ---- Prompt for Logout-All from manage tab ----
    function promptLogoutAll() {
        const pass = prompt('Enter your password to logout from all devices:');
        if (pass !== null && pass.trim() !== '') {
            document.getElementById('quick_logout_all_pass').value = pass;
            document.getElementById('quick_logout_all_pass').closest('form').submit();
        }
    }

    // ---- OTP Verification Modal Handlers ----
    function setupOtpInterception(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const isVerifiedInput = form.querySelector('input[name="otp_verified"]');
            if (isVerifiedInput && isVerifiedInput.value === '1') {
                return; // Form submits normally if already verified
            }

            e.preventDefault();
            activeFormToSubmit = form;
            openSettingsOtpModal();
        });
    }

    function openSettingsOtpModal() {
        const modalEl = document.getElementById('settingsOtpModal');
        if (!settingsOtpModalInstance) {
            settingsOtpModalInstance = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        }

        const hasBoth = Boolean(residentEmail && residentPhone);

        if (hasBoth) {
            // Show channel choice step first
            showChannelStep();
        } else {
            // Automatically set channel and jump to code step
            activeSettingsOtpChannel = residentEmail ? 'email' : (residentPhone ? 'phone' : 'email');
            showCodeStepAndRequestOtp();
        }

        settingsOtpModalInstance.show();
    }

    function showChannelStep() {
        clearInterval(settingsOtpTimerInterval);
        document.getElementById('settingsOtpChannelStep').classList.remove('d-none');
        document.getElementById('settingsOtpCodeStep').classList.add('d-none');

        document.getElementById('choiceEmailLabel').textContent = maskEmail(residentEmail);
        document.getElementById('choicePhoneLabel').textContent = maskPhone(residentPhone);
    }

    function confirmChannelAndSendOtp() {
        const emailRadio = document.getElementById('otpChoiceEmail');
        activeSettingsOtpChannel = emailRadio && emailRadio.checked ? 'email' : 'phone';

        showCodeStepAndRequestOtp();
    }

    function showCodeStepAndRequestOtp() {
        document.getElementById('settingsOtpChannelStep').classList.add('d-none');
        document.getElementById('settingsOtpCodeStep').classList.remove('d-none');

        const backBtn = document.getElementById('btnBackToChannels');
        if (residentEmail && residentPhone) {
            backBtn.classList.remove('d-none');
        } else {
            backBtn.classList.add('d-none');
        }

        clearSettingsOtpInputs();
        hideSettingsOtpAlert();
        updateSettingsOtpTargetDisplay();

        requestSettingsOtp(false);
    }

    function updateSettingsOtpTargetDisplay() {
        const label = document.getElementById('settingsOtpTargetLabel');
        const icon = document.getElementById('settingsOtpIcon');

        if (activeSettingsOtpChannel === 'email') {
            label.textContent = maskEmail(residentEmail);
            icon.className = 'fa-solid fa-envelope-circle-check';
        } else {
            label.textContent = maskPhone(residentPhone);
            icon.className = 'fa-solid fa-comment-sms';
        }
    }

    async function requestSettingsOtp(isResend = false) {
        hideSettingsOtpAlert();
        const resendBtn = document.getElementById('btnResendSettingsOtp');
        const countdownText = document.getElementById('settingsOtpCountdownText');

        if (resendBtn) resendBtn.classList.add('d-none');
        if (countdownText) countdownText.classList.remove('d-none');

        try {
            const response = await fetch("{{ route('resident.settings.send_otp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ channel: activeSettingsOtpChannel })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                showSettingsOtpAlert(data.message || 'Failed to send OTP code. Please try again.');
                return;
            }

            startSettingsOtpTimer(60);
            setTimeout(() => {
                document.querySelector('.settings-code-box[data-index="0"]')?.focus();
            }, 300);

        } catch (err) {
            showSettingsOtpAlert('Network error while requesting verification code.');
        }
    }

    function startSettingsOtpTimer(seconds) {
        clearInterval(settingsOtpTimerInterval);
        let remaining = seconds;
        const timerSpan = document.getElementById('settingsOtpTimerSeconds');
        const countdownText = document.getElementById('settingsOtpCountdownText');
        const resendBtn = document.getElementById('btnResendSettingsOtp');

        if (timerSpan) timerSpan.textContent = remaining;

        settingsOtpTimerInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(settingsOtpTimerInterval);
                if (countdownText) countdownText.classList.add('d-none');
                if (resendBtn) resendBtn.classList.remove('d-none');
            } else {
                if (timerSpan) timerSpan.textContent = remaining;
            }
        }, 1000);
    }

    async function submitSettingsOtpVerification() {
        hideSettingsOtpAlert();
        const boxes = document.querySelectorAll('.settings-code-box');
        let code = '';
        boxes.forEach(b => code += b.value.trim());

        if (code.length < 5) {
            showSettingsOtpAlert('Please enter the complete 5-digit verification code.');
            shakeSettingsBoxes();
            return;
        }

        const btn = document.getElementById('btnVerifySettingsOtp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Verifying...';

        try {
            const response = await fetch("{{ route('resident.settings.verify_otp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code, channel: activeSettingsOtpChannel })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                showSettingsOtpAlert(data.message || 'Incorrect verification code.');
                shakeSettingsBoxes();
                btn.disabled = false;
                btn.innerHTML = origText;
                return;
            }

            // Success feedback
            btn.className = 'btn btn-success w-100 rounded-3 py-2.5 fw-semibold mb-3 shadow-sm';
            btn.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> Verified!';

            setTimeout(() => {
                if (settingsOtpModalInstance) settingsOtpModalInstance.hide();
                btn.disabled = false;
                btn.className = 'btn btn-primary w-100 rounded-3 py-2.5 fw-semibold mb-3 shadow-sm';
                btn.innerHTML = origText;

                if (activeFormToSubmit) {
                    let verifiedField = activeFormToSubmit.querySelector('input[name="otp_verified"]');
                    if (!verifiedField) {
                        verifiedField = document.createElement('input');
                        verifiedField.type = 'hidden';
                        verifiedField.name = 'otp_verified';
                        activeFormToSubmit.appendChild(verifiedField);
                    }
                    verifiedField.value = '1';
                    activeFormToSubmit.submit();
                }
            }, 600);

        } catch (err) {
            showSettingsOtpAlert('Network error while verifying code.');
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    }

    function shakeSettingsBoxes() {
        document.querySelectorAll('.settings-code-box').forEach(b => {
            b.classList.add('shake');
            setTimeout(() => b.classList.remove('shake'), 400);
        });
    }

    function clearSettingsOtpInputs() {
        document.querySelectorAll('.settings-code-box').forEach(b => b.value = '');
    }

    function showSettingsOtpAlert(msg) {
        const alertBox = document.getElementById('settingsOtpAlert');
        if (alertBox) {
            alertBox.textContent = msg;
            alertBox.classList.remove('d-none');
        }
    }

    function hideSettingsOtpAlert() {
        const alertBox = document.getElementById('settingsOtpAlert');
        if (alertBox) {
            alertBox.classList.add('d-none');
            alertBox.textContent = '';
        }
    }

    function maskEmail(email) {
        if (!email) return 'N/A';
        const parts = email.split('@');
        if (parts[0].length <= 2) return email;
        return parts[0].substring(0, 2) + '***@' + parts[1];
    }

    function maskPhone(phone) {
        if (!phone) return 'N/A';
        if (phone.length < 7) return phone;
        return phone.substring(0, 4) + '****' + phone.substring(phone.length - 3);
    }

    // ---- DOM Content Loaded initialization ----
    document.addEventListener('DOMContentLoaded', function () {
        // Enforce CapsLock / uppercase on personal details & address inputs
        const uppercaseSelectors = '#first_name, #middle_name, #last_name, #suffix, #nationality, #occupation, #house_number, #street, #barangay_address, #municipality, #province';
        document.querySelectorAll(uppercaseSelectors).forEach(el => {
            el.addEventListener('input', function() {
                const start = this.selectionStart;
                const end = this.selectionEnd;
                if (typeof this.value === 'string') {
                    this.value = this.value.toUpperCase();
                }
                if (start !== null && end !== null) {
                    this.setSelectionRange(start, end);
                }
            });
        });

        // Set up active tab navigation
        const activeTab = '{{ $activeTab }}';
        const tabEl = document.getElementById('tab-' + activeTab);
        if (tabEl) {
            const bsTab = new bootstrap.Tab(tabEl);
            bsTab.show();
        }

        // Setup OTP Interceptors for Change Password & ID Upload forms
        const passwordForm = document.querySelector('form[action*="settings/password"]');
        const idForm = document.querySelector('form[action*="settings/id"]');

        if (passwordForm) {
            passwordForm.id = passwordForm.id || 'passwordChangeForm';
            setupOtpInterception(passwordForm.id);
        }
        if (idForm) {
            idForm.id = idForm.id || 'idUploadForm';
            setupOtpInterception(idForm.id);
        }

        // Configure 5-digit code box inputs navigation
        const codeBoxes = document.querySelectorAll('.settings-code-box');
        codeBoxes.forEach((box, idx) => {
            box.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length >= 1) {
                    this.value = this.value.slice(0, 1);
                    if (idx < codeBoxes.length - 1) {
                        codeBoxes[idx + 1].focus();
                    } else if (idx === codeBoxes.length - 1) {
                        submitSettingsOtpVerification();
                    }
                }
            });

            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && this.value === '' && idx > 0) {
                    codeBoxes[idx - 1].focus();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    submitSettingsOtpVerification();
                }
            });

            box.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
                if (pasteData.length > 0) {
                    for (let i = 0; i < codeBoxes.length; i++) {
                        codeBoxes[i].value = pasteData[i] || '';
                    }
                    if (pasteData.length >= 5) {
                        codeBoxes[4].focus();
                        submitSettingsOtpVerification();
                    }
                }
            });
        });
    });
</script>

@endsection