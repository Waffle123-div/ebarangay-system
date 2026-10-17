@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">

    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> &rsaquo; Settings</div>
        <h2 class="header-title">Settings & Configuration</h2>
        <p class="header-subtitle mb-0">Manage admin credentials, barangay profile, fee collection, policies, and system settings.</p>
    </div>

    {{-- Global Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1 fw-bold">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Please fix the following validation errors:</span>
            </div>
            <ul class="mb-0 ps-4 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ======================================================== --}}
        {{-- LEFT COLUMN: Navigation Tabs --}}
        {{-- ======================================================== --}}
        <div class="col-xl-3 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                
                {{-- Profile mini header --}}
                <div style="height:90px; background: linear-gradient(135deg, #0F2240 0%, #18365D 55%, #2B6CB0 100%);"></div>
                <div class="d-flex justify-content-center" style="margin-top:-44px;">
                    <div class="bg-white rounded-circle" style="padding:3px; box-shadow: 0 4px 12px rgba(24,54,93,0.25);">
                        <div class="rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase"
                             style="width:82px;height:82px;font-size:26px;background:linear-gradient(135deg,#EBF4FF,#BEE3F8);color:#18365D; border:3px solid #fff;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    </div>
                </div>
                <div class="card-body text-center pt-2 pb-3 px-3">
                    <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $user->name }}</h6>
                    <p class="text-muted small mb-2 text-truncate" style="font-size:12px;">{{ $user->email }}</p>
                    <span class="badge px-3 py-1 rounded-pill mb-3" style="background:#E6FFFA;color:#22706E;font-size:11px;">
                        <i class="fa-solid fa-circle me-1" style="font-size:6px;vertical-align:middle;"></i> Active Admin
                    </span>

                    {{-- Navigation Links Group --}}
                    <div class="list-group list-group-flush text-start rounded-3 overflow-hidden border">
                        
                        <div class="px-3 py-2 text-uppercase fw-bold text-muted small d-flex align-items-center gap-2" style="font-size:11px; letter-spacing:0.8px; background:#EBF4FF; color:#2B6CB0 !important;">
                            <i class="fa-solid fa-user-shield" style="font-size:13px;"></i> Admin Account
                        </div>
                        <a href="#profile-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2.5 px-3 active-tab-link fw-semibold admin-settings-nav-item" 
                           data-tab="profile-tab" id="nav-profile" style="font-size:14px;">
                            <span class="admin-nav-icon icon-profile">
                                <i class="fa-regular fa-id-card"></i>
                            </span>
                            <span>Profile Info</span>
                        </a>
                        <a href="#password-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2.5 px-3 fw-semibold admin-settings-nav-item" 
                           data-tab="password-tab" id="nav-password" style="font-size:14px;">
                            <span class="admin-nav-icon icon-password">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>
                            <span>Change Password</span>
                        </a>

                        <div class="px-3 py-2 text-uppercase fw-bold text-muted small border-top d-flex align-items-center gap-2" style="font-size:11px; letter-spacing:0.8px; background:#EBF4FF; color:#2B6CB0 !important;">
                            <i class="fa-solid fa-landmark" style="font-size:13px;"></i> Barangay Config
                        </div>
                        <a href="#barangay-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2.5 px-3 fw-semibold admin-settings-nav-item" 
                           data-tab="barangay-tab" id="nav-barangay" style="font-size:14px;">
                            <span class="admin-nav-icon icon-barangay">
                                <i class="fa-solid fa-landmark"></i>
                            </span>
                            <span>Barangay & Officials</span>
                        </a>
                        <a href="#documents-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2.5 px-3 fw-semibold admin-settings-nav-item" 
                           data-tab="documents-tab" id="nav-documents" style="font-size:14px;">
                            <span class="admin-nav-icon icon-documents">
                                <i class="fa-solid fa-file-lines"></i>
                            </span>
                            <span>Document Policies</span>
                        </a>
                        <a href="#residents-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2.5 px-3 fw-semibold admin-settings-nav-item" 
                           data-tab="residents-tab" id="nav-residents" style="font-size:14px;">
                            <span class="admin-nav-icon icon-residents">
                                <i class="fa-solid fa-users-gear"></i>
                            </span>
                            <span>Residents & Puroks</span>
                        </a>
                        <a href="#announcements-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2.5 px-3 fw-semibold admin-settings-nav-item" 
                           data-tab="announcements-tab" id="nav-announcements" style="font-size:14px;">
                            <span class="admin-nav-icon icon-announcements">
                                <i class="fa-solid fa-bullhorn"></i>
                            </span>
                            <span>Announcements</span>
                        </a>

                        <div class="px-3 py-2 text-uppercase fw-bold text-muted small border-top d-flex align-items-center gap-2" style="font-size:11px; letter-spacing:0.8px; background:#EBF4FF; color:#2B6CB0 !important;">
                            <i class="fa-solid fa-server" style="font-size:13px;"></i> System & Data
                        </div>
                        <a href="#system-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2.5 px-3 fw-semibold admin-settings-nav-item" 
                           data-tab="system-tab" id="nav-system" style="font-size:14px;">
                            <span class="admin-nav-icon icon-system">
                                <i class="fa-solid fa-server"></i>
                            </span>
                            <span>System & Backup</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Quick Stats Card --}}
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-2.5 mb-2">
                    <span class="admin-nav-icon icon-lgu" style="width:38px;height:38px;min-width:38px;border-radius:10px;font-size:18px;">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                    <span class="small fw-bold text-dark fs-6 ms-1">LGU Identity Status</span>
                </div>
                <div class="small text-muted mb-2">
                    Current LGU: <strong class="text-dark">{{ $settings['barangay_name'] ?? 'Barangay San Antonio' }}</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border small" style="background:#F7FAFC;">
                    <span class="text-muted">Online Requests</span>
                    <span class="badge {{ ($settings['online_requests_enabled'] ?? '1') == '1' ? '' : 'bg-secondary' }}" style="{{ ($settings['online_requests_enabled'] ?? '1') == '1' ? 'background:#E6FFFA;color:#22706E;' : '' }}">
                        {{ ($settings['online_requests_enabled'] ?? '1') == '1' ? 'Active' : 'Paused' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- RIGHT COLUMN: Tab Panes --}}
        {{-- ======================================================== --}}
        <div class="col-xl-9 col-lg-8">

            {{-- ========================================== --}}
            {{-- TAB 1: PROFILE INFO                        --}}
            {{-- ========================================== --}}
            <div id="profile-tab" class="settings-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background:#EBF4FF;">
                                <i class="fa-regular fa-id-card fs-5" style="color:#18365D;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Profile Information</h5>
                                <p class="text-muted small mb-0">Update your administrator name and login email address.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.settings.profile') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control px-3 py-2" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control px-3 py-2" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">System Role</label>
                                    <input type="text" class="form-control px-3 py-2 bg-light text-muted" value="{{ $user->role }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Admin User ID</label>
                                    <input type="text" class="form-control px-3 py-2 bg-light text-muted font-monospace" value="{{ $user->user_id }}" readonly>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- TAB 2: CHANGE PASSWORD                     --}}
            {{-- ========================================== --}}
            <div id="password-tab" class="settings-tab d-none">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background:#E6FFFA;">
                                <i class="fa-solid fa-shield-halved fs-5" style="color:#319795;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Change Password</h5>
                                <p class="text-muted small mb-0">For your security, please verify your current password before setting a new one.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if(session('password_success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
                                <i class="fa-solid fa-circle-check me-2"></i> {{ session('password_success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.settings.password') }}" method="POST" id="password-form">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-semibold">Current Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="current_password" id="current_password"
                                               class="form-control px-3 py-2 @error('current_password') is-invalid @enderror"
                                               placeholder="Enter your current password" required>
                                        <button class="btn btn-light border" type="button" onclick="togglePassword('current_password', this)">
                                            <i class="fa-regular fa-eye text-muted"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="text-danger small mt-1"><i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="new_password" id="new_password"
                                               class="form-control px-3 py-2 @error('new_password') is-invalid @enderror"
                                               placeholder="Minimum 8 characters" required>
                                        <button class="btn btn-light border" type="button" onclick="togglePassword('new_password', this)">
                                            <i class="fa-regular fa-eye text-muted"></i>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <div class="text-danger small mt-1"><i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="new_password_confirmation" id="confirm_password"
                                               class="form-control px-3 py-2"
                                               placeholder="Re-enter new password" required>
                                        <button class="btn btn-light border" type="button" onclick="togglePassword('confirm_password', this)">
                                            <i class="fa-regular fa-eye text-muted"></i>
                                        </button>
                                    </div>
                                    <div id="confirm-msg" class="small mt-1"></div>
                                </div>
                            </div>

                            {{-- Password strength meter --}}
                            <div class="mt-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small">Password Strength</span>
                                    <span class="small fw-semibold" id="strength-label">–</span>
                                </div>
                                <div class="progress rounded-pill" style="height:6px;">
                                    <div class="progress-bar rounded-pill" id="strength-bar" style="width:0%; transition:width 0.4s;"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4 gap-2">
                                <button type="reset" class="btn btn-light border px-4 fw-semibold">Reset</button>
                                <button type="submit" class="btn btn-warning px-5 fw-semibold text-dark shadow-sm">
                                    <i class="fa-solid fa-key me-2"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- TAB 3: BARANGAY & OFFICIALS                --}}
            {{-- ========================================== --}}
            <div id="barangay-tab" class="settings-tab d-none">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background:#EBF4FF;">
                                <i class="fa-solid fa-landmark fs-5" style="color:#18365D;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Barangay Information & Officials</h5>
                                <p class="text-muted small mb-0">Configure your LGU identity, seal logos, official contacts, and certificate signatories.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.settings.barangay') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="_group" value="barangay">

                            {{-- Section: LGU Branding & Seals --}}
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-shield-halved text-primary"></i> Official Seals & Logos
                            </h6>
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Official Barangay Seal</label>
                                    <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light">
                                        <div class="rounded-circle overflow-hidden bg-white border shadow-sm d-flex align-items-center justify-content-center"
                                             style="width:72px; height:72px; flex-shrink:0;">
                                            @if(!empty($settings['barangay_logo']))
                                                <img src="{{ asset('storage/' . $settings['barangay_logo']) }}" alt="Barangay Logo" style="width:100%; height:100%; object-fit:contain;">
                                            @else
                                                <img src="{{ asset('images/logo.png') }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Barangay+Seal&background=4f46e5&color=fff';" alt="Logo" style="width:100%; height:100%; object-fit:contain;">
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" name="barangay_logo" class="form-control form-control-sm mb-1" accept="image/*">
                                            <span class="text-muted small" style="font-size:11px;">PNG, JPG up to 2MB. Displayed on certificates.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">City / Municipal Seal</label>
                                    <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light">
                                        <div class="rounded-circle overflow-hidden bg-white border shadow-sm d-flex align-items-center justify-content-center"
                                             style="width:72px; height:72px; flex-shrink:0;">
                                            @if(!empty($settings['municipal_logo']))
                                                <img src="{{ asset('storage/' . $settings['municipal_logo']) }}" alt="City Logo" style="width:100%; height:100%; object-fit:contain;">
                                            @else
                                                <div class="text-muted small text-center px-1"><i class="fa-solid fa-city fa-2x text-secondary"></i></div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file" name="municipal_logo" class="form-control form-control-sm mb-1" accept="image/*">
                                            <span class="text-muted small" style="font-size:11px;">PNG, JPG up to 2MB. Appears alongside barangay seal.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section: Jurisdiction & Location --}}
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-primary"></i> Jurisdiction & Address
                            </h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Barangay Name <span class="text-danger">*</span></label>
                                    <input type="text" name="barangay_name" class="form-control px-3 py-2" value="{{ old('barangay_name', $settings['barangay_name'] ?? 'Barangay San Antonio') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">City / Municipality <span class="text-danger">*</span></label>
                                    <input type="text" name="municipality" class="form-control px-3 py-2" value="{{ old('municipality', $settings['municipality'] ?? 'City of Pasig') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Province <span class="text-danger">*</span></label>
                                    <input type="text" name="province" class="form-control px-3 py-2" value="{{ old('province', $settings['province'] ?? 'Metro Manila') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Region</label>
                                    <input type="text" name="region" class="form-control px-3 py-2" value="{{ old('region', $settings['region'] ?? 'National Capital Region (NCR)') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-semibold">Barangay Hall Physical Address</label>
                                    <input type="text" name="barangay_hall_address" class="form-control px-3 py-2" value="{{ old('barangay_hall_address', $settings['barangay_hall_address'] ?? '77 General Malvar St., San Antonio, Pasig City') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-semibold">Official Tagline / Slogan</label>
                                    <input type="text" name="barangay_tagline" class="form-control px-3 py-2" value="{{ old('barangay_tagline', $settings['barangay_tagline'] ?? 'Serbisyong Maaasahan, Tapat, at Makatao') }}">
                                </div>
                            </div>

                            {{-- Section: Official Signatories --}}
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-signature text-primary"></i> Official Signatories (Certificate Headers & Sign-offs)
                            </h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-semibold">Punong Barangay (Captain)</label>
                                    <input type="text" name="barangay_captain" class="form-control px-3 py-2" value="{{ old('barangay_captain', $settings['barangay_captain'] ?? 'Hon. Raymond C. Lising') }}" placeholder="Hon. [Name]">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-semibold">Barangay Secretary</label>
                                    <input type="text" name="barangay_secretary" class="form-control px-3 py-2" value="{{ old('barangay_secretary', $settings['barangay_secretary'] ?? 'Maria Elena D. Santos') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-semibold">Barangay Treasurer</label>
                                    <input type="text" name="barangay_treasurer" class="form-control px-3 py-2" value="{{ old('barangay_treasurer', $settings['barangay_treasurer'] ?? 'Roberto V. Ramos') }}">
                                </div>
                            </div>

                            {{-- Section: Contact & Working Hours --}}
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="fa-solid fa-phone text-primary"></i> Contact Numbers & Working Schedule
                            </h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Official Contact / Hotlines</label>
                                    <input type="text" name="official_contact" class="form-control px-3 py-2" value="{{ old('official_contact', $settings['official_contact'] ?? '(02) 8631-0000 / 0917-123-4567') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Emergency Hotline / Police / Tanod</label>
                                    <input type="text" name="emergency_hotline" class="form-control px-3 py-2" value="{{ old('emergency_hotline', $settings['emergency_hotline'] ?? '(02) 8643-0000 / 911') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Official Barangay Email</label>
                                    <input type="email" name="official_email" class="form-control px-3 py-2" value="{{ old('official_email', $settings['official_email'] ?? 'sanantonio.pasig@gmail.com') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Office Operating Hours</label>
                                    <input type="text" name="office_hours" class="form-control px-3 py-2" value="{{ old('office_hours', $settings['office_hours'] ?? 'Monday – Friday: 8:00 AM – 5:00 PM') }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Barangay Details
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



            {{-- ========================================== --}}
            {{-- TAB 5: DOCUMENT POLICIES                   --}}
            {{-- ========================================== --}}
            <div id="documents-tab" class="settings-tab d-none">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background:#E6FFFA;">
                                <i class="fa-solid fa-file-lines fs-5" style="color:#319795;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Document Request Policies</h5>
                                <p class="text-muted small mb-0">Configure document tracking numbers, turnaround SLA times, and service availability.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.settings.barangay') }}" method="POST">
                            @csrf
                            <input type="hidden" name="_group" value="documents">

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Default Processing Turnaround Time (Days) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="default_processing_days" min="1" max="30" class="form-control px-3 py-2"
                                               value="{{ old('default_processing_days', $settings['default_processing_days'] ?? '2') }}" required>
                                        <span class="input-group-text bg-light text-muted">Business Day(s)</span>
                                    </div>
                                    <span class="text-muted small" style="font-size:11px;">Default SLA communicated to residents upon application.</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Tracking Number Prefix <span class="text-danger">*</span></label>
                                    <input type="text" name="tracking_prefix" id="trackingPrefixInput" class="form-control px-3 py-2 font-monospace text-uppercase"
                                           value="{{ old('tracking_prefix', $settings['tracking_prefix'] ?? 'BRGY-DOC') }}" required>
                                    <span class="text-muted small" style="font-size:11px;">
                                        Live sample: <strong class="text-primary font-monospace" id="trackingPrefixPreview">{{ $settings['tracking_prefix'] ?? 'BRGY-DOC' }}-{{ date('Y') }}-0001</strong>
                                    </span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Max Active Requests Per Resident</label>
                                    <input type="number" name="max_active_requests_per_resident" min="1" max="20" class="form-control px-3 py-2"
                                           value="{{ old('max_active_requests_per_resident', $settings['max_active_requests_per_resident'] ?? '3') }}" required>
                                    <span class="text-muted small" style="font-size:11px;">Limits simultaneous pending requests per resident to prevent queue flooding.</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Online Request Submissions</label>
                                    <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-semibold text-dark small">Allow Online Requests</div>
                                            <div class="text-muted" style="font-size:11px;">When disabled, residents are notified that online requests are paused.</div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="online_requests_enabled" value="1"
                                                   {{ ($settings['online_requests_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Link to Document Types Catalog --}}
                            <div class="p-4 rounded-4 border bg-light d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-folder-open text-primary me-2"></i> Document Types & Fee Catalog
                                    </div>
                                    <div class="text-muted small">Manage available certificates (Clearance, Indigency, Residency), set individual fees, and update Word template files.</div>
                                </div>
                                <a href="{{ route('admin.document-types.index') }}" class="btn btn-outline-primary btn-sm px-4 rounded-pill fw-semibold">
                                    Open Catalog &rsaquo;
                                </a>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Document Policies
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- TAB 6: RESIDENTS & PUROKS                  --}}
            {{-- ========================================== --}}
            <div id="residents-tab" class="settings-tab d-none">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background:#EBF4FF;">
                                <i class="fa-solid fa-users-gear fs-5" style="color:#18365D;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Resident Registration & Purok Directory</h5>
                                <p class="text-muted small mb-0">Manage resident onboarding rules, valid ID verification policies, and covered Puroks / Sitios.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.settings.barangay') }}" method="POST">
                            @csrf
                            <input type="hidden" name="_group" value="residents">

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Resident Verification Mode <span class="text-danger">*</span></label>
                                    <select name="resident_verification_mode" class="form-select px-3 py-2">
                                        <option value="manual" {{ ($settings['resident_verification_mode'] ?? 'manual') === 'manual' ? 'selected' : '' }}>
                                            Strict — Manual Admin Approval (Requires Valid ID check)
                                        </option>
                                        <option value="auto" {{ ($settings['resident_verification_mode'] ?? 'manual') === 'auto' ? 'selected' : '' }}>
                                            Automated — Instant Approval (After Email / Phone OTP)
                                        </option>
                                    </select>
                                    <span class="text-muted small" style="font-size:11px;">Current mode: Admin inspects resident ID photos in the Approvals queue before activation.</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Minimum Age for Self-Registration <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="minimum_registration_age" min="1" max="100" class="form-control px-3 py-2"
                                               value="{{ old('minimum_registration_age', $settings['minimum_registration_age'] ?? '15') }}" required>
                                        <span class="input-group-text bg-light text-muted">Years Old</span>
                                    </div>
                                    <span class="text-muted small" style="font-size:11px;">Residents below this age must be registered under their guardian.</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Active Puroks & Sitios (One per line) <span class="text-danger">*</span></label>
                                    <textarea name="purok_list" rows="6" class="form-control px-3 py-2 font-monospace small" placeholder="Purok 1&#10;Purok 2&#10;Sitio Ilaya..." required>{{ old('purok_list', $settings['purok_list'] ?? "Purok 1\nPurok 2\nPurok 3\nPurok 4\nPurok 5\nSitio Ilaya\nSitio Ibaba\nGreenwoods Village") }}</textarea>
                                    <span class="text-muted small" style="font-size:11px;">Populates the Purok dropdown in the resident registration & profile forms.</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Accepted Valid Government IDs (One per line) <span class="text-danger">*</span></label>
                                    <textarea name="accepted_ids" rows="6" class="form-control px-3 py-2 font-monospace small" placeholder="PhilSys National ID&#10;Passport..." required>{{ old('accepted_ids', $settings['accepted_ids'] ?? "Philippine Passport\nPhilSys National ID\nDriver's License\nUMID / SSS ID\nVoter's ID / Certification\nPostal ID\nPRC ID\nStudent ID (with Enrollment Form)") }}</textarea>
                                    <span class="text-muted small" style="font-size:11px;">Options shown when residents upload ID for account verification.</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Resident Rules
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- TAB 7: ANNOUNCEMENTS                       --}}
            {{-- ========================================== --}}
            <div id="announcements-tab" class="settings-tab d-none">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background:#FFF5F5;">
                                <i class="fa-solid fa-bullhorn fs-5 text-danger"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Public Announcement Banner</h5>
                                <p class="text-muted small mb-0">Publish urgent notices, advisory bulletins, or holiday schedules across the resident portal.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.settings.barangay') }}" method="POST">
                            @csrf
                            <input type="hidden" name="_group" value="announcements">

                            <div class="p-4 border rounded-4 bg-light mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                    <div>
                                        <div class="fw-bold text-dark fs-6">Enable Public Announcement Banner</div>
                                        <div class="text-muted small">When active, this banner appears prominently on the resident portal dashboard.</div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="announcement_enabled" value="1" id="announcementSwitch"
                                               {{ ($settings['announcement_enabled'] ?? '0') == '1' ? 'checked' : '' }} style="width: 48px; height: 24px;">
                                    </div>
                                </div>

                                {{-- Quick Presets --}}
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-semibold d-flex align-items-center gap-1 mb-2">
                                        <i class="fa-solid fa-wand-magic-sparkles text-primary"></i> Quick Presets / Templates (Click to Auto-fill)
                                    </label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold" onclick="applyAnnouncementPreset('typhoon')">
                                            🌪️ Calamity / Weather Alert
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill fw-semibold" onclick="applyAnnouncementPreset('health')">
                                            💉 Health & Vaccination Drive
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold" onclick="applyAnnouncementPreset('assembly')">
                                            🗓️ Barangay General Assembly
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill fw-semibold text-dark" onclick="applyAnnouncementPreset('cleanup')">
                                            🚧 Community Clean-up / Works
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small fw-semibold">Banner Alert Level <span class="text-danger">*</span></label>
                                        <select name="announcement_type" id="bannerTypeSelect" class="form-select px-3 py-2">
                                            <option value="info" {{ ($settings['announcement_type'] ?? 'info') === 'info' ? 'selected' : '' }}>🔵 Info (General Notice)</option>
                                            <option value="warning" {{ ($settings['announcement_type'] ?? 'info') === 'warning' ? 'selected' : '' }}>🟡 Warning (Advisory / Caution)</option>
                                            <option value="danger" {{ ($settings['announcement_type'] ?? 'info') === 'danger' ? 'selected' : '' }}>🔴 Danger (Emergency / Urgent Alert)</option>
                                            <option value="success" {{ ($settings['announcement_type'] ?? 'info') === 'success' ? 'selected' : '' }}>🟢 Success (Event / Public Service)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label text-muted small fw-semibold">Category / Topic</label>
                                        <input type="text" name="announcement_category" id="bannerCategoryInput" class="form-control px-3 py-2"
                                               value="{{ old('announcement_category', $settings['announcement_category'] ?? 'Public Advisory') }}"
                                               placeholder="e.g. Weather Alert, Health Advisory">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label text-muted small fw-semibold">Expiry Date (Optional)</label>
                                        <input type="date" name="announcement_expiry_date" class="form-control px-3 py-2"
                                               value="{{ old('announcement_expiry_date', $settings['announcement_expiry_date'] ?? '') }}">
                                        <span class="text-muted small" style="font-size: 11px;">Leave empty to keep banner active indefinitely.</span>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label text-muted small fw-semibold">Headline / Title <span class="text-danger">*</span></label>
                                        <input type="text" name="announcement_title" id="bannerTitleInput" class="form-control px-3 py-2"
                                               value="{{ old('announcement_title', $settings['announcement_title'] ?? 'Public Advisory') }}" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label text-muted small fw-semibold">Announcement Message <span class="text-danger">*</span></label>
                                        <textarea name="announcement_message" id="bannerMessageInput" rows="3" class="form-control px-3 py-2" required>{{ old('announcement_message', $settings['announcement_message'] ?? 'Welcome to the official E-Barangay Online Portal. You may request certificates and clearances online 24/7.') }}</textarea>
                                    </div>

                                    {{-- Call-to-action link & dismissible toggle --}}
                                    <div class="col-md-5">
                                        <label class="form-label text-muted small fw-semibold">Action Button Label (Optional)</label>
                                        <input type="text" name="announcement_action_text" id="bannerActionTextInput" class="form-control px-3 py-2"
                                               value="{{ old('announcement_action_text', $settings['announcement_action_text'] ?? '') }}"
                                               placeholder="e.g. Learn More, View Hotline">
                                    </div>

                                    <div class="col-md-7">
                                        <label class="form-label text-muted small fw-semibold">Action Button Link / URL (Optional)</label>
                                        <input type="text" name="announcement_action_url" id="bannerActionUrlInput" class="form-control px-3 py-2"
                                               value="{{ old('announcement_action_url', $settings['announcement_action_url'] ?? '') }}"
                                               placeholder="e.g. /resident/concerns or https://...">
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check form-switch pt-1">
                                            <input class="form-check-input" type="checkbox" role="switch" name="announcement_is_dismissible" value="1" id="dismissibleSwitch"
                                                   {{ ($settings['announcement_is_dismissible'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-semibold text-muted" for="dismissibleSwitch">
                                                Allow residents to temporarily dismiss the banner during their session
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Live Banner Preview Card --}}
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-semibold d-flex align-items-center gap-1">
                                    <i class="fa-solid fa-eye text-primary"></i> Live Resident Banner Preview (What Residents Will See)
                                </label>
                                <div id="bannerPreviewBox" class="p-4 rounded-4 shadow-sm border"
                                     style="background: #eff6ff; border-color: #93c5fd !important; color: #1e40af;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary text-white px-3 py-1 rounded-pill fw-bold text-uppercase" id="bannerPreviewCategory" style="font-size: 11px;">
                                                <i class="fa-solid fa-circle-info me-1"></i> Public Advisory
                                            </span>
                                            <span class="badge bg-danger rounded-pill px-2.5 py-1 text-white small fw-bold d-none" id="bannerPreviewUrgentTag" style="font-size: 10px;">
                                                <i class="fa-solid fa-circle-dot me-1"></i> URGENT ALERT
                                            </span>
                                            <span class="small opacity-75" style="font-size: 12px;">Just now</span>
                                        </div>
                                        <span class="opacity-50"><i class="fa-solid fa-xmark"></i></span>
                                    </div>
                                    <div class="d-flex align-items-start gap-3 mt-2">
                                        <div id="bannerPreviewIcon" class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white shadow-sm flex-shrink-0"
                                             style="width: 44px; height: 44px; font-size: 18px;">
                                            <i class="fa-solid fa-circle-info"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="fw-bold mb-1" id="bannerPreviewTitle" style="color: inherit;">
                                                {{ $settings['announcement_title'] ?? 'Public Advisory' }}
                                            </h6>
                                            <p class="small mb-0 opacity-90" id="bannerPreviewText" style="line-height: 1.5; white-space: pre-line;">
                                                {{ $settings['announcement_message'] ?? 'Welcome to the official E-Barangay Online Portal.' }}
                                            </p>
                                            <div id="bannerPreviewActionWrapper" class="mt-2.5 {{ empty($settings['announcement_action_text']) ? 'd-none' : '' }}">
                                                <span class="btn btn-primary btn-sm px-3 py-1 rounded-pill fw-semibold shadow-sm" id="bannerPreviewActionBtn">
                                                    {{ $settings['announcement_action_text'] ?? 'Learn More' }} &rsaquo;
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-bullhorn me-2"></i> Save & Publish Banner
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- TAB 8: SYSTEM & MAINTENANCE                --}}
            {{-- ========================================== --}}
            <div id="system-tab" class="settings-tab d-none">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 d-flex align-items-center justify-content-center" style="background:#EDF2F7;">
                                <i class="fa-solid fa-server fs-5" style="color:#4A5568;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">System & Disaster Recovery</h5>
                                <p class="text-muted small mb-0">Export full database backups, manage session timeouts, and review system diagnostics.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        
                        {{-- Database Backup Action --}}
                        <div class="p-4 border rounded-4 bg-light mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div>
                                <div class="fw-bold text-dark fs-6 mb-1">
                                    <i class="fa-solid fa-database text-primary me-2"></i> One-Click Full Database Backup (.sql)
                                </div>
                                <div class="text-muted small">
                                    Generates a complete SQL dump of all database tables (users, requests, document types, logs, and settings) for local archiving and disaster recovery.
                                </div>
                            </div>
                            <a href="{{ route('admin.settings.backup-db') }}" class="btn btn-success px-4 py-2 fw-semibold rounded-pill shadow-sm text-nowrap">
                                <i class="fa-solid fa-download me-2"></i> Export SQL Backup
                            </a>
                        </div>

                        {{-- Security and Timeout Form --}}
                        <form action="{{ route('admin.settings.barangay') }}" method="POST">
                            @csrf
                            <input type="hidden" name="_group" value="system">

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Admin Session Inactivity Timeout <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="session_timeout_minutes" min="15" max="1440" class="form-control px-3 py-2"
                                               value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes'] ?? '120') }}" required>
                                        <span class="input-group-text bg-light text-muted">Minutes</span>
                                    </div>
                                    <span class="text-muted small" style="font-size:11px;">Recommended: 30–120 minutes for shared barangay workstations.</span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">System Maintenance Mode</label>
                                    <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-semibold text-dark small">Maintenance Mode</div>
                                            <div class="text-muted" style="font-size:11px;">Temporarily pause resident actions during data migration.</div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="system_maintenance_mode" value="1"
                                                   {{ ($settings['system_maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mb-4">
                                <button type="submit" class="btn btn-primary px-5 fw-semibold shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Save System Configuration
                                </button>
                            </div>
                        </form>

                        {{-- Server Diagnostics Table --}}
                        <h6 class="fw-bold text-dark mb-3 pt-2 border-top d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-nodes text-primary"></i> Environment Diagnostics
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="text-muted small mb-1">Application Environment</div>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-bold">
                                        {{ strtoupper(app()->environment()) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="text-muted small mb-1">Laravel Version</div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill font-monospace fw-bold">
                                        v{{ app()->version() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="text-muted small mb-1">PHP Engine</div>
                                    <span class="badge bg-secondary bg-opacity-10 text-dark px-3 py-1.5 rounded-pill font-monospace fw-bold">
                                        PHP {{ phpversion() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* Admin Settings Navigation Icons */
.admin-settings-nav-item {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.admin-nav-icon {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 2px rgba(24, 54, 93, 0.04);
}
.admin-nav-icon i {
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Category-specific icon color themes */
.icon-profile       { background: #eff6ff; color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.18); }
.icon-password      { background: #f0fdfa; color: #0d9488; border: 1px solid rgba(13, 148, 136, 0.18); }
.icon-barangay      { background: #eef2ff; color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.18); }
.icon-documents     { background: #f0f9ff; color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.18); }
.icon-residents     { background: #faf5ff; color: #9333ea; border: 1px solid rgba(147, 51, 234, 0.18); }
.icon-announcements { background: #fff1f2; color: #e11d48; border: 1px solid rgba(225, 29, 72, 0.18); }
.icon-system        { background: #f8fafc; color: #475569; border: 1px solid rgba(71, 85, 105, 0.18); }
.icon-lgu           { background: #ebf4ff; color: #18365d; border: 1px solid rgba(24, 54, 93, 0.18); }

/* Hover effect */
.admin-settings-nav-item:hover .admin-nav-icon {
    transform: scale(1.08);
}

/* Settings Tab Active State */
.list-group-item.active-tab-link,
a[data-tab].tab-active {
    background-color: #EBF4FF !important;
    color: #18365D !important;
    border-left: 3px solid #18365D !important;
    font-weight: 700 !important;
}

.admin-settings-nav-item.active .admin-nav-icon,
.admin-settings-nav-item.tab-active .admin-nav-icon,
.admin-settings-nav-item.active-tab-link .admin-nav-icon {
    background: linear-gradient(135deg, #18365D 0%, #2B6CB0 100%) !important;
    color: #ffffff !important;
    border-color: transparent !important;
    box-shadow: 0 4px 10px rgba(24, 54, 93, 0.28);
}
.admin-settings-nav-item.active .admin-nav-icon i,
.admin-settings-nav-item.tab-active .admin-nav-icon i,
.admin-settings-nav-item.active-tab-link .admin-nav-icon i {
    color: #ffffff !important;
}

.settings-tab {
    animation: fadeInTab 0.22s ease;
}

@keyframes fadeInTab {
    from { opacity: 0; transform: translateY(5px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<script>
// Tab switching
document.querySelectorAll('a[data-tab]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const tabId = this.getAttribute('data-tab');

        // Hide all tabs
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.add('d-none'));
        
        // Show selected tab
        const target = document.getElementById(tabId);
        if (target) {
            target.classList.remove('d-none');
        }

        // Reset nav styles
        document.querySelectorAll('a[data-tab]').forEach(l => {
            l.classList.remove('active', 'tab-active', 'active-tab-link');
            l.style.backgroundColor = '';
            l.style.color = '';
            l.style.borderLeft = '';
        });

        // Activate clicked nav
        this.classList.add('active', 'tab-active', 'active-tab-link');
        this.style.backgroundColor = '#EBF4FF';
        this.style.color = '#18365D';
        this.style.borderLeft = '3px solid #18365D';

        // Update URL hash without scroll
        if (history.pushState) {
            history.pushState(null, null, '#' + tabId);
        }
    });
});

// Auto-activate tab based on session flash or URL hash
document.addEventListener('DOMContentLoaded', function() {
    let initialTab = null;

    @if(session('active_tab'))
        initialTab = "{{ session('active_tab') }}";
    @elseif($errors->has('current_password') || $errors->has('new_password') || session('password_success'))
        initialTab = "password-tab";
    @elseif(request()->has('tab'))
        initialTab = "{{ request('tab') }}";
    @endif

    if (!initialTab && window.location.hash) {
        initialTab = window.location.hash.replace('#', '');
    }

    if (initialTab) {
        const navLink = document.querySelector(`a[data-tab="${initialTab}"]`);
        if (navLink) {
            navLink.click();
        }
    }
});

// Password visibility toggle
function togglePassword(fieldId, btn) {
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

// Password strength meter
const newPwdField = document.getElementById('new_password');
const strengthBar  = document.getElementById('strength-bar');
const strengthLabel = document.getElementById('strength-label');

if (newPwdField && strengthBar && strengthLabel) {
    newPwdField.addEventListener('input', function() {
        const val = this.value;
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors  = ['', '#ef4444', '#f59e0b', '#3b82f6', '#22c55e'];
        const widths  = ['0%', '25%', '50%', '75%', '100%'];

        strengthBar.style.width  = widths[score] || '0%';
        strengthBar.style.background = colors[score] || '#e5e7eb';
        strengthLabel.textContent    = levels[score] || '–';
        strengthLabel.style.color    = colors[score] || '#9ca3af';
    });
}

// Confirm password match
const confirmField = document.getElementById('confirm_password');
if (confirmField && newPwdField) {
    confirmField.addEventListener('input', function() {
        const msg = document.getElementById('confirm-msg');
        if (this.value === newPwdField.value) {
            msg.innerHTML = '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Passwords match</span>';
        } else {
            msg.innerHTML = '<span class="text-danger"><i class="fa-solid fa-xmark me-1"></i>Passwords do not match</span>';
        }
    });
}

// Document tracking preview live update
const trackingInput = document.getElementById('trackingPrefixInput');
const trackingPreview = document.getElementById('trackingPrefixPreview');
if (trackingInput && trackingPreview) {
    trackingInput.addEventListener('input', function() {
        const prefix = (this.value || 'BRGY-DOC').trim().toUpperCase();
        trackingPreview.textContent = prefix + '-{{ date("Y") }}-0001';
    });
}

// Announcement Banner live preview & Presets
const bannerSelect       = document.getElementById('bannerTypeSelect');
const bannerCategory     = document.getElementById('bannerCategoryInput');
const bannerTitle        = document.getElementById('bannerTitleInput');
const bannerMsg          = document.getElementById('bannerMessageInput');
const bannerActionText   = document.getElementById('bannerActionTextInput');
const bannerActionUrl    = document.getElementById('bannerActionUrlInput');

const bannerBox          = document.getElementById('bannerPreviewBox');
const bannerPrevTitle    = document.getElementById('bannerPreviewTitle');
const bannerPrevText     = document.getElementById('bannerPreviewText');
const bannerPrevCategory = document.getElementById('bannerPreviewCategory');
const bannerPrevUrgent   = document.getElementById('bannerPreviewUrgentTag');
const bannerPrevIcon     = document.getElementById('bannerPreviewIcon');
const bannerPrevActionW  = document.getElementById('bannerPreviewActionWrapper');
const bannerPrevActionB  = document.getElementById('bannerPreviewActionBtn');

const bannerPresets = {
    typhoon: {
        type: 'danger',
        category: 'Weather & Calamity Alert',
        title: 'Severe Weather Warning: Precautionary Advisory',
        message: 'A storm signal advisory has been raised in our region. Residents along flood-prone areas and riverbanks are advised to stay alert, prepare emergency go-bags, and contact the Barangay Emergency Operations Center for assistance.',
        action_text: 'Emergency Hotlines',
        action_url: '#contact-hotlines'
    },
    health: {
        type: 'success',
        category: 'Health & Sanitation',
        title: 'Free Anti-Rabies & Community Vaccination Drive',
        message: 'The Barangay Health Center is hosting a free vaccination and medical check-up mission this coming Saturday from 8:00 AM to 12:00 PM at the Multi-Purpose Hall. Open to all registered barangay residents.',
        action_text: 'View Schedule',
        action_url: ''
    },
    assembly: {
        type: 'info',
        category: 'Barangay Assembly',
        title: 'Notice of Annual Barangay General Assembly',
        message: 'All residents of Barangay are invited to attend the State of the Barangay Address (SOBA) and general assembly this Sunday at 2:00 PM. Community issues, financial reports, and upcoming projects will be discussed.',
        action_text: 'Read Agenda',
        action_url: ''
    },
    cleanup: {
        type: 'warning',
        category: 'Community Works',
        title: 'Scheduled Drainage Declogging & Temporary Road Advisory',
        message: 'Please be advised that road works and clean-up operations will take place along Main Street on Wednesday. Expect light traffic and kindly refrain from parking vehicles along affected service roads.',
        action_text: 'Advisory Details',
        action_url: ''
    }
};

window.applyAnnouncementPreset = function(key) {
    const p = bannerPresets[key];
    if (!p) return;
    if (bannerSelect) bannerSelect.value = p.type;
    if (bannerCategory) bannerCategory.value = p.category;
    if (bannerTitle) bannerTitle.value = p.title;
    if (bannerMsg) bannerMsg.value = p.message;
    if (bannerActionText) bannerActionText.value = p.action_text || '';
    if (bannerActionUrl) bannerActionUrl.value = p.action_url || '';
    updateBannerPreview();
};

function updateBannerPreview() {
    if (!bannerBox) return;
    const type = bannerSelect ? bannerSelect.value : 'info';
    const category = (bannerCategory && bannerCategory.value) ? bannerCategory.value : 'Public Advisory';
    const title = (bannerTitle && bannerTitle.value) ? bannerTitle.value : 'Public Advisory';
    const msg = (bannerMsg && bannerMsg.value) ? bannerMsg.value : 'Announcement details...';
    const actionText = bannerActionText ? bannerActionText.value : '';

    if (bannerPrevTitle) bannerPrevTitle.textContent = title;
    if (bannerPrevText) bannerPrevText.textContent = msg;

    const styles = {
        info:    { bg: '#eff6ff', border: '#93c5fd', color: '#1e40af', badgeClass: 'bg-primary text-white', icon: '<i class="fa-solid fa-circle-info"></i>', btnClass: 'btn-primary' },
        warning: { bg: '#fffbeb', border: '#fcd34d', color: '#92400e', badgeClass: 'bg-warning text-dark', icon: '<i class="fa-solid fa-triangle-exclamation"></i>', btnClass: 'btn-warning text-dark' },
        danger:  { bg: '#fef2f2', border: '#fca5a5', color: '#991b1b', badgeClass: 'bg-danger text-white', icon: '<i class="fa-solid fa-circle-exclamation"></i>', btnClass: 'btn-danger' },
        success: { bg: '#f0fdf4', border: '#86efac', color: '#166534', badgeClass: 'bg-success text-white', icon: '<i class="fa-solid fa-bullhorn"></i>', btnClass: 'btn-success' }
    };

    const s = styles[type] || styles.info;
    bannerBox.style.background = s.bg;
    bannerBox.style.borderColor = s.border;
    bannerBox.style.color = s.color;

    if (bannerPrevCategory) {
        bannerPrevCategory.className = `badge ${s.badgeClass} px-3 py-1 rounded-pill fw-bold text-uppercase`;
        bannerPrevCategory.innerHTML = `${s.icon} <span class="ms-1">${category}</span>`;
    }

    if (bannerPrevUrgent) {
        if (type === 'danger') {
            bannerPrevUrgent.classList.remove('d-none');
        } else {
            bannerPrevUrgent.classList.add('d-none');
        }
    }

    if (bannerPrevIcon) {
        bannerPrevIcon.className = `rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0 ${s.badgeClass}`;
        bannerPrevIcon.innerHTML = s.icon;
    }

    if (bannerPrevActionW && bannerPrevActionB) {
        if (actionText.trim() !== '') {
            bannerPrevActionW.classList.remove('d-none');
            bannerPrevActionB.className = `btn ${s.btnClass} btn-sm px-3 py-1 rounded-pill fw-semibold shadow-sm`;
            bannerPrevActionB.textContent = actionText + ' \u203a';
        } else {
            bannerPrevActionW.classList.add('d-none');
        }
    }
}

if (bannerSelect) bannerSelect.addEventListener('change', updateBannerPreview);
if (bannerCategory) bannerCategory.addEventListener('input', updateBannerPreview);
if (bannerTitle) bannerTitle.addEventListener('input', updateBannerPreview);
if (bannerMsg) bannerMsg.addEventListener('input', updateBannerPreview);
if (bannerActionText) bannerActionText.addEventListener('input', updateBannerPreview);
</script>

@endsection
