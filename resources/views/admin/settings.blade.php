@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> &rsaquo; Settings</div>
        <h2 class="header-title">Settings</h2>
        <p class="header-subtitle mb-0">Manage your account profile and security preferences.</p>
    </div>

    <div class="row g-4">

        {{-- Left: Profile Card --}}
        <div class="col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 text-center overflow-hidden h-100">
                <div style="height:100px; background: linear-gradient(135deg, #4f46e5, #7c3aed);"></div>
                <div class="d-flex justify-content-center" style="margin-top:-52px;">
                    <div class="bg-white p-2 rounded-circle shadow-sm">
                        <div class="rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase"
                             style="width:95px;height:95px;font-size:30px;background:linear-gradient(135deg,#eef2ff,#ede9fe);color:#4f46e5;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3 pb-4">
                    <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }}</p>
                    <span class="badge px-3 py-2 rounded-pill mb-4" style="background:#dcfce7;color:#15803d;font-size:12px;">
                        <i class="fa-solid fa-circle me-1" style="font-size:6px;vertical-align:middle;"></i> Active Admin
                    </span>

                    {{-- Nav Tabs --}}
                    <div class="list-group list-group-flush text-start mt-2 rounded-3 overflow-hidden border">
                        <a href="#profile-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 px-4 active-tab-link fw-semibold" 
                           data-tab="profile-tab" id="nav-profile" style="font-size:14px;">
                            <i class="fa-regular fa-user text-primary" style="width:18px;"></i> Profile Info
                        </a>
                        <a href="#password-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 px-4 fw-semibold" 
                           data-tab="password-tab" id="nav-password" style="font-size:14px;">
                            <i class="fa-solid fa-lock text-warning" style="width:18px;"></i> Change Password
                        </a>
                        <a href="#system-tab" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 px-4 fw-semibold" 
                           data-tab="system-tab" id="nav-system" style="font-size:14px;">
                            <i class="fa-solid fa-sliders text-secondary" style="width:18px;"></i> System
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Tab Content --}}
        <div class="col-xl-9">

            {{-- ======================== --}}
            {{-- PROFILE INFO TAB --}}
            {{-- ======================== --}}
            <div id="profile-tab" class="settings-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <h5 class="fw-bold text-dark mb-1">Profile Information</h5>
                        <p class="text-muted small mb-0">Update your name and email address.</p>
                    </div>
                    <div class="card-body p-4">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
                                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->has('name') || $errors->has('email'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->only(['name','email']) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.settings.profile') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Full Name</label>
                                    <input type="text" name="name" class="form-control px-3 py-2" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Email Address</label>
                                    <input type="email" name="email" class="form-control px-3 py-2" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">System Role</label>
                                    <input type="text" class="form-control px-3 py-2 bg-light text-muted" value="{{ $user->role }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">User ID</label>
                                    <input type="text" class="form-control px-3 py-2 bg-light text-muted font-monospace" value="{{ $user->user_id }}" readonly>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 fw-semibold">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ======================== --}}
            {{-- CHANGE PASSWORD TAB --}}
            {{-- ======================== --}}
            <div id="password-tab" class="settings-tab d-none">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <h5 class="fw-bold text-dark mb-1">Change Password</h5>
                        <p class="text-muted small mb-0">For your security, please verify your current password before setting a new one.</p>
                    </div>
                    <div class="card-body p-4">

                        @if(session('password_success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
                                <i class="fa-solid fa-circle-check me-2"></i> {{ session('password_success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->has('current_password') || $errors->has('new_password'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->only(['current_password','new_password']) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.settings.password') }}" method="POST" id="password-form">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label text-muted small fw-semibold">Current Password</label>
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
                                    <label class="form-label text-muted small fw-semibold">New Password</label>
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
                                    <label class="form-label text-muted small fw-semibold">Confirm New Password</label>
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
                                <button type="reset" class="btn btn-light border px-4 fw-semibold">Cancel</button>
                                <button type="submit" class="btn btn-warning px-5 fw-semibold text-dark">
                                    <i class="fa-solid fa-key me-2"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ======================== --}}
            {{-- SYSTEM TAB --}}
            {{-- ======================== --}}
            <div id="system-tab" class="settings-tab d-none">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 pt-4 pb-3">
                        <h5 class="fw-bold text-dark mb-1">System Settings</h5>
                        <p class="text-muted small mb-0">System-wide configuration options.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded-3 border">
                                    <div>
                                        <div class="fw-semibold text-dark mb-1">Activity Log Retention</div>
                                        <div class="text-muted small">Logs are retained indefinitely. Contact your developer to set up automatic archiving.</div>
                                    </div>
                                    <span class="badge bg-secondary px-3 py-2 rounded-pill">∞ Forever</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded-3 border">
                                    <div>
                                        <div class="fw-semibold text-dark mb-1">Application Environment</div>
                                        <div class="text-muted small">Current runtime environment of the application.</div>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">{{ app()->environment() }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded-3 border">
                                    <div>
                                        <div class="fw-semibold text-dark mb-1">Laravel Version</div>
                                        <div class="text-muted small">Framework version currently in use.</div>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">v{{ app()->version() }}</span>
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
.settings-tab .list-group-item.active,
a[data-tab].tab-active {
    background-color: var(--color-primary-50, #eef2ff) !important;
    color: #4f46e5 !important;
    border-left: 3px solid #4f46e5 !important;
}
</style>

<script>
// Tab switching
document.querySelectorAll('a[data-tab]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const tabId = this.getAttribute('data-tab');

        document.querySelectorAll('.settings-tab').forEach(t => t.classList.add('d-none'));
        document.getElementById(tabId).classList.remove('d-none');

        document.querySelectorAll('a[data-tab]').forEach(l => {
            l.classList.remove('active', 'tab-active');
            l.style.backgroundColor = '';
            l.style.color = '';
            l.style.borderLeft = '';
        });

        this.classList.add('active', 'tab-active');
        this.style.backgroundColor = '#eef2ff';
        this.style.color = '#4f46e5';
        this.style.borderLeft = '3px solid #4f46e5';
    });
});

// Auto-activate password tab if there are password errors
@if($errors->has('current_password') || $errors->has('new_password') || session('password_success'))
    document.getElementById('nav-password').click();
@endif

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

if (newPwdField) {
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
</script>

@endsection
