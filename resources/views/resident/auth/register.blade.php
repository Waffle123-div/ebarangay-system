<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: var(--color-bg); font-family: var(--font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
        .gateway-panel { display: block; }
        .manual-form-panel { display: none; }

        /* Progress bar */
        .step-indicator { width: 20%; height: 4px; background-color: #e9ecef; transition: background-color 0.3s ease; }
        .step-indicator.active { background-color: var(--color-primary); }

        /* Step panels */
        .step-panel { display: none; }
        .step-panel.active { display: block; }

        /* Section headers */
        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--color-primary);
            margin-bottom: 0.75rem;
            padding-bottom: 0.35rem;
            border-bottom: 2px solid var(--color-primary-100);
        }

        /* Scrollable card body for Step 1 */
        .step-scroll {
            max-height: 60vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding-left: 12px;
            padding-right: 12px;
        }
        .step-scroll::-webkit-scrollbar { width: 5px; }
        .step-scroll::-webkit-scrollbar-track { background: transparent; }
        .step-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .step-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Terms scroll box */
        .terms-box { max-height: 180px; overflow-y: auto; }

        /* Invalid feedback for custom validation */
        .field-error { border-color: #dc3545 !important; }

        /* Auto-uppercase for name and address inputs */
        .input-uppercase { text-transform: uppercase; }
        .input-uppercase::placeholder { text-transform: none; }

        /* OTP Code Boxes */
        .code-box {
            width: 48px;
            height: 56px;
            font-size: 22px;
            text-align: center;
            font-weight: 700;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .code-box:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.15);
            outline: none;
        }
        .code-box.shake {
            animation: shake 0.4s ease-in-out;
            border-color: #dc3545;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        /* Standalone Channel Switcher Button */
        #btnSwitchChannel {
            font-size: 0.82rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        #btnSwitchChannel:hover {
            transform: scale(1.03);
        }

        /* Channel Chooser Modal Buttons */
        .channel-option {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
            width: 100%;
        }
        .channel-option:hover {
            border-color: var(--color-primary);
            background: #f0f5ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 86, 219, 0.1);
        }
        .channel-option .channel-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .channel-option .channel-icon.email-icon {
            background: #e8f0fe;
            color: var(--color-primary);
        }
        .channel-option .channel-icon.sms-icon {
            background: #d4edda;
            color: #198754;
        }
        .channel-option .channel-label {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1a1a2e;
        }
        .channel-option .channel-desc {
            font-size: 0.78rem;
            color: #6c757d;
            margin-top: 2px;
        }

        /* Enlarged Live Face Verification Styles */
        .face-cam-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
        }
        .face-video-viewport {
            width: 100%;
            max-width: 620px;
            aspect-ratio: 4 / 3;
            min-height: 360px;
            background: #090e17;
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
            margin: 0 auto;
        }
        .face-video-viewport video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        .biometric-oval-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: clamp(230px, 50%, 300px);
            height: clamp(290px, 70%, 380px);
            border: 3.5px dashed rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            box-shadow: 0 0 0 9999px rgba(9, 14, 23, 0.45);
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .biometric-crosshair {
            position: absolute;
            width: 22px;
            height: 22px;
            border-color: rgba(255, 255, 255, 0.8);
            pointer-events: none;
        }
        .biometric-crosshair.top-left {
            top: 20px;
            left: 20px;
            border-top: 3.5px solid;
            border-left: 3.5px solid;
            border-top-left-radius: 8px;
        }
        .biometric-crosshair.top-right {
            top: 20px;
            right: 20px;
            border-top: 3.5px solid;
            border-right: 3.5px solid;
            border-top-right-radius: 8px;
        }
        .biometric-crosshair.bottom-left {
            bottom: 56px;
            left: 20px;
            border-bottom: 3.5px solid;
            border-left: 3.5px solid;
            border-bottom-left-radius: 8px;
        }
        .biometric-crosshair.bottom-right {
            bottom: 56px;
            right: 20px;
            border-bottom: 3.5px solid;
            border-right: 3.5px solid;
            border-bottom-right-radius: 8px;
        }
        .face-captured-viewport {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            border-radius: 1rem;
            overflow: hidden;
            background: #090e17;
            border: 2px solid #e2e8f0;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        .face-captured-viewport img {
            width: 100%;
            max-height: 440px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
@php
    $viaGoogle     = session('google_reg_id') && session('google_reg_email');
    $googleEmail   = session('google_reg_email', '');
    $googleName    = session('google_reg_name', '');
@endphp
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 2rem 0;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-11 col-lg-10 col-xl-8" style="max-width: 880px;">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">

                    {{-- ============================================ --}}
                    {{-- GATEWAY PANEL (Choose Method) --}}
                    {{-- ============================================ --}}
                    <div id="gatewayPanel" class="gateway-panel text-center mx-auto" style="max-width: 440px; {{ $viaGoogle ? 'display:none;' : '' }}">
                        <div class="mb-4">
                            <i class="fa-solid fa-id-card text-primary fs-1 mb-2"></i>
                            <h4 class="fw-bold text-dark">Create an Account</h4>
                            <p class="text-muted small">Register to request barangay documents</p>
                        </div>

                        <button type="button" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold mb-3" onclick="showManualForm()">
                            Create Account Manually
                        </button>

                        <div class="d-flex align-items-center mb-3">
                            <hr class="flex-grow-1">
                            <span class="mx-2 text-muted small fw-bold">OR</span>
                            <hr class="flex-grow-1">
                        </div>

                        <a href="{{ route('google.login', ['action' => 'register']) }}" class="btn btn-dark w-100 rounded-3 py-2 fw-semibold mb-4">
                            <i class="fa-brands fa-google me-2"></i> Sign up with Google
                        </a>

                        <div class="text-center small">
                            Already have an account? <a href="{{ route('resident.login') }}" class="text-primary text-decoration-none fw-bold">Log In Here</a>
                        </div>
                    </div>

                    {{-- ============================================ --}}
                    {{-- MANUAL REGISTRATION WIZARD --}}
                    {{-- ============================================ --}}
                    <div id="manualFormPanel" class="manual-form-panel" style="{{ $viaGoogle ? 'display:block;' : '' }}">

                        {{-- Progress Bar --}}
                        <div class="d-flex mb-4">
                            <div class="step-indicator active rounded-start" data-step="1"></div>
                            <div class="step-indicator ms-1" data-step="2"></div>
                            <div class="step-indicator ms-1" data-step="3"></div>
                            <div class="step-indicator ms-1" data-step="4"></div>
                            <div class="step-indicator ms-1 rounded-end" data-step="5"></div>
                        </div>

                        {{-- Step Title --}}
                        <div class="text-center mb-3">
                            <h4 class="fw-bold text-dark mb-1" id="stepTitle">Step 1 of 5</h4>
                            <p class="text-muted small" id="stepSubtitle">Resident Identity</p>
                        </div>

                        {{-- Server-side errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 small rounded-3 mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('resident.register.submit') }}" enctype="multipart/form-data" id="registerForm" novalidate>
                            @csrf

                            {{-- ========================================== --}}
                            {{-- STEP 1: RESIDENT IDENTITY --}}
                            {{-- ========================================== --}}
                            <div class="step-panel active" id="panel-step-1">
                                <div class="step-scroll">

                                    {{-- A. Personal Profile --}}
                                    <div class="section-label"><i class="fa-solid fa-user me-1"></i> A. Personal Profile</div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" name="last_name" class="form-control rounded-3 input-uppercase" value="{{ old('last_name') }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                                            <input type="text" name="first_name" class="form-control rounded-3 input-uppercase" value="{{ old('first_name') }}" required>
                                        </div>
                                    </div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Middle Name</label>
                                            <input type="text" name="middle_name" class="form-control rounded-3 input-uppercase" value="{{ old('middle_name') }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Suffix <span class="text-muted">(Optional)</span></label>
                                            <select name="suffix" class="form-select rounded-3">
                                                <option value="" {{ old('suffix') == '' ? 'selected' : '' }}>None</option>
                                                <option value="Jr." {{ old('suffix') == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                                <option value="Sr." {{ old('suffix') == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                                <option value="II" {{ old('suffix') == 'II' ? 'selected' : '' }}>II</option>
                                                <option value="III" {{ old('suffix') == 'III' ? 'selected' : '' }}>III</option>
                                                <option value="IV" {{ old('suffix') == 'IV' ? 'selected' : '' }}>IV</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Birthdate <span class="text-danger">*</span></label>
                                            <input type="date" name="birthdate" id="regBirthdate" class="form-control rounded-3" value="{{ old('birthdate') }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Age <span class="text-danger">*</span></label>
                                            <input type="number" name="age" id="regAge" class="form-control rounded-3" value="{{ old('age') }}" min="0" max="150" placeholder="e.g. 25" required>
                                        </div>
                                    </div>

                                    <div class="mb-3 text-start">
                                        <label class="form-label small fw-semibold">Birthplace <span class="text-danger">*</span></label>
                                        <input type="text" name="birthplace" class="form-control rounded-3 input-uppercase" value="{{ old('birthplace') }}" placeholder="City / Municipality, Province" required>
                                    </div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Gender <span class="text-danger">*</span></label>
                                            <select name="gender" class="form-select rounded-3" required>
                                                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select Gender</option>
                                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Civil Status <span class="text-danger">*</span></label>
                                            <select name="civil_status" class="form-select rounded-3" required>
                                                <option value="" disabled {{ old('civil_status') ? '' : 'selected' }}>Select Status</option>
                                                <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                                <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                                <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                                <option value="Separated" {{ old('civil_status') == 'Separated' ? 'selected' : '' }}>Separated</option>
                                                <option value="Divorced" {{ old('civil_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Nationality <span class="text-danger">*</span></label>
                                            <input type="text" name="nationality" class="form-control rounded-3 input-uppercase" value="{{ old('nationality', 'Filipino') }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Occupation <span class="text-danger">*</span></label>
                                            <select name="occupation" class="form-select rounded-3" required>
                                                <option value="" disabled {{ old('occupation') ? '' : 'selected' }}>Select Occupation</option>
                                                <option value="Student" {{ old('occupation') == 'Student' ? 'selected' : '' }}>Student</option>
                                                <option value="Employed" {{ old('occupation') == 'Employed' ? 'selected' : '' }}>Employed</option>
                                                <option value="Self-Employed" {{ old('occupation') == 'Self-Employed' ? 'selected' : '' }}>Self-Employed</option>
                                                <option value="Government Employee" {{ old('occupation') == 'Government Employee' ? 'selected' : '' }}>Government Employee</option>
                                                <option value="OFW" {{ old('occupation') == 'OFW' ? 'selected' : '' }}>OFW</option>
                                                <option value="Freelancer" {{ old('occupation') == 'Freelancer' ? 'selected' : '' }}>Freelancer</option>
                                                <option value="Business Owner" {{ old('occupation') == 'Business Owner' ? 'selected' : '' }}>Business Owner</option>
                                                <option value="Farmer" {{ old('occupation') == 'Farmer' ? 'selected' : '' }}>Farmer</option>
                                                <option value="Fisherman" {{ old('occupation') == 'Fisherman' ? 'selected' : '' }}>Fisherman</option>
                                                <option value="Housewife" {{ old('occupation') == 'Housewife' ? 'selected' : '' }}>Housewife</option>
                                                <option value="Retired" {{ old('occupation') == 'Retired' ? 'selected' : '' }}>Retired</option>
                                                <option value="Unemployed" {{ old('occupation') == 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
                                                <option value="Others" {{ old('occupation') == 'Others' ? 'selected' : '' }}>Others</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- B. Residency and Contact --}}
                                    <div class="section-label mt-2"><i class="fa-solid fa-location-dot me-1"></i> B. Residency and Contact</div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Purok <span class="text-danger">*</span></label>
                                            <input type="text" name="purok" class="form-control rounded-3 input-uppercase" value="{{ old('purok') }}" placeholder="e.g. Purok 1" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Sitio <span class="text-muted">(Optional)</span></label>
                                            <input type="text" name="sitio" class="form-control rounded-3 input-uppercase" value="{{ old('sitio') }}" placeholder="e.g. Sitio Lubi">
                                        </div>
                                    </div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Barangay</label>
                                            <input type="text" class="form-control rounded-3 bg-light input-uppercase" value="COGON EAST" readonly>
                                            <input type="hidden" name="barangay_address" value="COGON EAST">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Municipality</label>
                                            <input type="text" class="form-control rounded-3 bg-light input-uppercase" value="CARMEN, CEBU" readonly>
                                            <input type="hidden" name="municipality" value="CARMEN, CEBU">
                                        </div>
                                    </div>

                                    <div class="mb-3 text-start">
                                        <label class="form-label small fw-semibold">ZIP Code</label>
                                        <input type="text" class="form-control rounded-3 bg-light" value="6005" readonly style="max-width: 120px;">
                                        <input type="hidden" name="zip_code" value="6005">
                                    </div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                                            <input type="text" id="step1PhoneNumber" class="form-control rounded-3" value="{{ old('phone_number') }}" placeholder="09XXXXXXXXX">
                                            @if($viaGoogle)
                                                <div class="form-text small text-muted">Optional when signing in with Google.</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Email Address</label>
                                            @if($viaGoogle)
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0" style="border-color:#dee2e6;">
                                                        <i class="fa-brands fa-google text-danger" style="font-size:13px;"></i>
                                                    </span>
                                                    <input type="email" id="step1Email" class="form-control rounded-end-3 bg-light border-start-0"
                                                           value="{{ $googleEmail }}" readonly style="border-color:#dee2e6;">
                                                </div>
                                                <div class="form-text small text-success fw-semibold">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Verified via Google
                                                </div>
                                            @else
                                                <input type="email" id="step1Email" class="form-control rounded-3" value="{{ old('email') }}" placeholder="you@email.com">
                                            @endif
                                        </div>
                                    </div>

                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="showGateway()"><i class="fa-solid fa-arrow-left me-1"></i> Back</button>
                                    <button type="button" class="btn btn-primary w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(2)">Next <i class="fa-solid fa-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- ========================================== --}}
                            {{-- STEP 2: CREDENTIALS & VERIFICATION --}}
                            {{-- ========================================== --}}
                            <div class="step-panel" id="panel-step-2">
                                <div class="step-scroll">

                                    {{-- A. Contact Information --}}
                                    <div class="section-label"><i class="fa-solid fa-address-card me-1"></i> A. Contact Information</div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                                            <input type="text" name="phone_number" class="form-control rounded-3" value="{{ old('phone_number') }}" placeholder="09XXXXXXXXX" {{ $viaGoogle ? '' : 'required' }}>
                                            @if($viaGoogle)
                                                <div class="form-text small text-muted">Optional when signing in with Google.</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Email Address</label>
                                            @if($viaGoogle)
                                                {{-- Pre-filled from Google; read-only --}}
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0" style="border-color:#dee2e6;">
                                                        <i class="fa-brands fa-google text-danger" style="font-size:13px;"></i>
                                                    </span>
                                                    <input type="email" name="email" class="form-control rounded-end-3 bg-light border-start-0"
                                                           value="{{ $googleEmail }}" readonly style="border-color:#dee2e6;">
                                                </div>
                                                <div class="form-text small text-success fw-semibold">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Verified via Google
                                                </div>
                                            @else
                                                <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" placeholder="you@email.com">
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Verified credential badge (shown after OTP verification) --}}
                                    <div id="verifiedCredentialBadge" class="alert alert-success border-0 small rounded-3 d-none mb-3" role="alert">
                                        <i class="fa-solid fa-circle-check me-1"></i>
                                        <span id="verifiedCredentialText">Your contact information has been verified!</span>
                                    </div>

                                    {{-- B. Account Password --}}
                                    <div class="section-label mt-2"><i class="fa-solid fa-lock me-1"></i> B. Account Password</div>

                                    @if($viaGoogle)
                                        {{-- Google users: password not required, they authenticate via Google OAuth --}}
                                        <div class="alert alert-success border-0 small rounded-3" role="alert">
                                            <i class="fa-brands fa-google me-1 text-danger"></i>
                                            You are signing up with <strong>Google</strong>. No password is needed — you can always sign in using your Google account.
                                        </div>
                                        {{-- Hidden empty password fields to satisfy any browser validation --}}
                                        <input type="hidden" name="password" value="">
                                    @else
                                        <div class="row text-start">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                                                <div class="position-relative">
                                                    <input type="password" name="password" id="regPassword" class="form-control rounded-3" required>
                                                    <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent text-muted" onclick="togglePassword('regPassword', this)">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text small text-muted">Minimum 8 characters</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                                <div class="position-relative">
                                                    <input type="password" name="password_confirmation" id="regPasswordConfirm" class="form-control rounded-3" required>
                                                    <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent text-muted" onclick="togglePassword('regPasswordConfirm', this)">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="invalid-feedback small">Passwords do not match.</div>
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(1)"><i class="fa-solid fa-arrow-left me-1"></i> Back</button>
                                    <button type="button" class="btn btn-primary w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(3)">Next <i class="fa-solid fa-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- ========================================== --}}
                            {{-- STEP 3: ADMIN VERIFICATION REQUIREMENTS --}}
                            {{-- ========================================== --}}
                            <div class="step-panel" id="panel-step-3">
                                <div class="alert alert-warning border-0 small text-start mb-4">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    Access is restricted to valid residents. Please attach a valid ID or document to verify your identity.
                                </div>

                                <div class="mb-3 text-start">
                                    <label class="form-label small fw-semibold">Type of ID / Document <span class="text-danger">*</span></label>
                                    <select name="id_type" id="idTypeSelect" class="form-select rounded-3" required>
                                        <option value="" disabled selected>Select ID Type...</option>
                                        <option value="National ID" {{ old('id_type') == 'National ID' ? 'selected' : '' }}>National ID</option>
                                        <option value="Driver's License" {{ old('id_type') == "Driver's License" ? 'selected' : '' }}>Driver's License</option>
                                        <option value="Voter's ID" {{ old('id_type') == "Voter's ID" ? 'selected' : '' }}>Voter's ID</option>
                                        <option value="Student ID" {{ old('id_type') == 'Student ID' ? 'selected' : '' }}>Student ID</option>
                                        <option value="Others" {{ old('id_type') == 'Others' ? 'selected' : '' }}>Others (Voter's Certificate, PSA, etc.)</option>
                                    </select>
                                </div>

                                <div class="mb-4 text-start">
                                    <label class="form-label small fw-semibold">Upload ID Image <span class="text-danger">*</span></label>
                                    <input class="form-control rounded-3" type="file" name="proof_of_residency" id="idUploadInput" accept="image/*,application/pdf" required>
                                    <div class="form-text small text-muted">Accepted formats: JPG, JPEG, PNG, PDF (Max 10MB).</div>
                                </div>

                                {{-- ============================================ --}}
                                {{-- SECTION: LIVE FACIAL VERIFICATION          --}}
                                {{-- ============================================ --}}
                                <div class="mb-4 text-start">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label small fw-bold mb-0 text-dark">
                                            <i class="fa-solid fa-camera text-primary me-1"></i> Live Face Verification <span class="text-danger">*</span>
                                        </label>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                            Biometric Check
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-3" style="font-size: 12px;">
                                        Please take a clear, well-lit live selfie to verify against your ID. Look straight at the camera without sunglasses, hats, or masks.
                                    </p>

                                    <div class="face-cam-card p-3 p-md-4 text-center" id="faceCameraContainer">
                                        {{-- 1. Initial State: Prompt to open camera --}}
                                        <div id="facePromptState">
                                            <div class="py-4 px-2">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                                                    <i class="fa-solid fa-camera fs-2"></i>
                                                </div>
                                                <h5 class="fw-bold text-dark mb-1">Live Facial Camera</h5>
                                                <p class="text-muted small mb-3" style="max-width: 460px; margin: 0 auto; font-size: 13px;">
                                                    Take a real-time photo of your face for identity verification against your uploaded government ID.
                                                </p>
                                                
                                                <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
                                                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-pill" style="font-size: 11.5px;">
                                                        <i class="fa-regular fa-sun text-warning me-1"></i> Good Lighting
                                                    </span>
                                                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-pill" style="font-size: 11.5px;">
                                                        <i class="fa-solid fa-glasses text-secondary me-1"></i> No Sunglasses / Mask
                                                    </span>
                                                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-pill" style="font-size: 11.5px;">
                                                        <i class="fa-regular fa-face-smile text-primary me-1"></i> Look Straight Ahead
                                                    </span>
                                                </div>

                                                <div class="d-flex flex-column align-items-center gap-2">
                                                    <button type="button" class="btn btn-primary px-4 py-2.5 rounded-3 fw-bold shadow-sm" style="font-size: 15px;" onclick="startFaceCamera()">
                                                        <i class="fa-solid fa-video me-2"></i> Start Live Face Camera
                                                    </button>
                                                    <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted" onclick="toggleSelfieUploadFallback()">
                                                        <i class="fa-solid fa-arrow-up-from-bracket me-1"></i> Or upload a selfie image file instead
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 2. Active Video Stream State --}}
                                        <div id="faceActiveState" class="d-none">
                                            <div class="face-video-viewport">
                                                <video id="faceVideoElement" autoplay playsinline></video>
                                                
                                                {{-- Flash effect overlay on capture --}}
                                                <div id="faceFlashOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white" style="opacity: 0; pointer-events: none; transition: opacity 0.25s ease-out; z-index: 20;"></div>

                                                {{-- Biometric HUD Header --}}
                                                <div class="position-absolute top-0 start-0 end-0 p-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(to bottom, rgba(0,0,0,0.7), transparent); z-index: 10;">
                                                    <span class="badge bg-danger text-white px-2.5 py-1.5 rounded-pill d-inline-flex align-items-center gap-1.5" style="font-size: 11px; letter-spacing: 0.5px;">
                                                        <i class="fa-solid fa-circle text-white fa-fade"></i> LIVE BIOMETRIC CAM
                                                    </span>
                                                    <button type="button" class="btn btn-sm btn-dark bg-opacity-75 border-0 text-white rounded-circle shadow" style="width: 32px; height: 32px;" onclick="stopFaceCamera()" title="Close Camera">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>

                                                {{-- Corner Biometric Crosshairs --}}
                                                <div class="biometric-crosshair top-left"></div>
                                                <div class="biometric-crosshair top-right"></div>
                                                <div class="biometric-crosshair bottom-left"></div>
                                                <div class="biometric-crosshair bottom-right"></div>

                                                {{-- Oval Guideline Overlay --}}
                                                <div class="biometric-oval-frame"></div>

                                                {{-- Bottom Guidance Banner --}}
                                                <div class="position-absolute bottom-0 start-0 end-0 py-2.5 px-3 bg-dark bg-opacity-75 text-white text-center small" style="backdrop-filter: blur(4px); z-index: 10;">
                                                    <i class="fa-solid fa-arrows-to-circle text-primary me-1"></i> Align your face within the oval guideline and click <strong>Take Snapshot</strong>
                                                </div>
                                            </div>

                                            <div class="mt-3 pt-2 d-flex justify-content-center align-items-center gap-2 flex-wrap">
                                                <button type="button" class="btn btn-success btn-lg px-4 py-2.5 rounded-pill fw-bold shadow" onclick="captureFaceSnapshot()">
                                                    <i class="fa-solid fa-camera-retro me-2"></i> Take Snapshot
                                                </button>
                                                <button type="button" class="btn btn-light border px-3 py-2 rounded-pill text-muted fw-semibold" onclick="stopFaceCamera()">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>

                                        {{-- 3. Photo Captured State --}}
                                        <div id="faceCapturedState" class="d-none">
                                            <div class="face-captured-viewport position-relative">
                                                <img id="faceCapturedPreview" src="" alt="Live Face Preview">
                                                <span class="position-absolute top-0 end-0 m-3 badge bg-success shadow rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 12px;">
                                                    <i class="fa-solid fa-circle-check"></i> Face Snapshot Captured
                                                </span>
                                            </div>

                                            <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 py-2.5 px-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 mx-auto text-start" style="max-width: 520px;">
                                                <div class="text-success small fw-semibold">
                                                    <i class="fa-solid fa-check-double me-1.5"></i> Photo is clearly visible and ready for review.
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-success bg-white rounded-3 fw-semibold px-3 py-1 shadow-sm" onclick="retakeFacePhoto()">
                                                    <i class="fa-solid fa-rotate-left me-1"></i> Retake Photo
                                                </button>
                                            </div>
                                        </div>

                                        {{-- 4. Fallback File Upload State --}}
                                        <div id="selfieUploadFallbackBox" class="d-none text-start p-3 mt-3 border rounded-3 bg-white">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <label class="form-label small fw-bold text-dark mb-0">
                                                    <i class="fa-solid fa-image text-primary me-1"></i> Upload Selfie Portrait:
                                                </label>
                                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold" onclick="useFaceCameraInstead()">
                                                    <i class="fa-solid fa-video me-1"></i> Use Live Camera Instead
                                                </button>
                                            </div>
                                            <input type="file" name="selfie_upload" id="selfieUploadInput" class="form-control rounded-3" accept="image/*" onchange="handleSelfieFileSelect(this)">
                                            <div class="form-text small text-muted">Upload a clear photo showing your face directly (JPG, PNG, max 10MB).</div>
                                        </div>
                                    </div>

                                    {{-- Hidden Inputs for Form Submission --}}
                                    <input type="hidden" name="selfie_photo" id="selfiePhotoInput" value="">
                                    <canvas id="faceSnapshotCanvas" class="d-none"></canvas>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="stopFaceCamera(); goToStep(2)"><i class="fa-solid fa-arrow-left me-1"></i> Back</button>
                                    <button type="button" class="btn btn-primary w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(4)">Next <i class="fa-solid fa-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- ========================================== --}}
                            {{-- STEP 4: DATA PRIVACY --}}
                            {{-- ========================================== --}}
                            <div class="step-panel" id="panel-step-4">

                                <div class="mb-3 p-3 bg-light rounded-3 small text-muted text-start terms-box">
                                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-shield-halved me-1"></i> Data Privacy Act of 2012 (RA 10173)</h6>
                                    <p>In compliance with the Data Privacy Act of 2012 and its Implementing Rules and Regulations, the Barangay Cogon East administration ensures that all personal information collected through this platform shall be processed fairly and lawfully.</p>
                                    <p>Your personal data will be used solely for the purpose of verifying your identity, processing your barangay document requests, and maintaining accurate residency records. This information shall not be shared, sold, or disclosed to any third party without your explicit consent, except when required by law.</p>
                                    <p class="mb-0">You have the right to access, correct, and request the removal of your personal data at any time by contacting the barangay office.</p>
                                </div>

                                <div class="form-check mb-4 text-start">
                                    <input class="form-check-input" type="checkbox" name="privacy" value="1" id="agreePrivacy" required>
                                    <label class="form-check-label small text-muted" for="agreePrivacy">
                                        I have read and agree to the <strong>Data Privacy Act</strong> notice above
                                    </label>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(3)"><i class="fa-solid fa-arrow-left me-1"></i> Back</button>
                                    <button type="button" class="btn btn-primary w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(5)">Next <i class="fa-solid fa-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- ========================================== --}}
                            {{-- STEP 5: TERMS AND CONDITIONS --}}
                            {{-- ========================================== --}}
                            <div class="step-panel" id="panel-step-5">

                                <div class="mb-3 p-3 bg-light rounded-3 small text-muted text-start terms-box">
                                    <h6 class="fw-bold text-dark"><i class="fa-solid fa-file-contract me-1"></i> Terms and Conditions</h6>
                                    <p>By accessing this platform, you warrant that you are a legal resident of Barangay Cogon East, Carmen, Cebu. All document requests submitted through this web-based tracking system must consist of authentic personal declarations.</p>
                                    <p>Falsification of administrative identity profiles or residency documentation will result in the immediate revocation of credentials and potential endorsement to local legal authorities.</p>
                                    <p class="mb-0">The barangay reserves the right to verify all submitted documents and deny or revoke access to any account found to have submitted fraudulent information.</p>
                                </div>

                                <div class="form-check mb-4 text-start">
                                    <input class="form-check-input" type="checkbox" name="terms" value="1" id="agreeTerms" required>
                                    <label class="form-check-label small text-muted" for="agreeTerms">
                                        I agree to the <strong>Terms and Conditions</strong> stated above
                                    </label>
                                </div>

                                <div class="alert alert-primary border-0 small rounded-3 mb-4" role="alert">
                                    <i class="fa-solid fa-clock me-1"></i>
                                    After submitting, your account will be reviewed by the barangay admin. Please wait for approval before you can fully access the system.
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(4)"><i class="fa-solid fa-arrow-left me-1"></i> Back</button>
                                    <button type="submit" id="btnSubmitRegister" class="btn btn-primary w-50 rounded-3 py-2 fw-semibold">
                                        <i class="fa-solid fa-user-plus me-1"></i> Sign Up
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- OTP VERIFICATION MODAL --}}
    {{-- ============================================ --}}
    <div class="modal fade" id="otpModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body text-center p-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background-color: #e8f0fe; color: var(--color-primary); border-radius: 50%; font-size: 1.75rem;">
                        <i class="fa-solid fa-shield-halved" id="otpIcon"></i>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">Verify Your Identity</h5>
                    <p class="text-muted small mb-2">
                        Before proceeding to Step 3, please enter the 5-digit verification code sent to:
                    </p>
                    <div class="mb-3">
                        <span class="badge bg-light text-primary border px-3 py-2 fw-bold fs-6 text-break" id="otpTargetLabel"></span>
                    </div>

                    {{-- Channel Switcher (Standalone Pill Button) --}}
                    <div id="channelSwitchWrapper" class="mb-3 d-none text-center">
                        <button type="button" class="btn btn-sm btn-success text-white rounded-pill px-3 py-1 fw-semibold shadow-sm" id="btnSwitchChannel" onclick="switchOtpChannel()">
                            <i class="fa-solid fa-mobile-screen me-1"></i> Send via SMS
                        </button>
                    </div>

                    <div id="otpAlert" class="alert alert-danger border-0 small rounded-3 py-2 mb-3 d-none text-start"></div>

                    {{-- 5 OTP Boxes --}}
                    <div class="d-flex justify-content-center gap-2 mb-3" id="otpInputsContainer">
                        <input type="text" class="code-box form-control" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="0">
                        <input type="text" class="code-box form-control" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="1">
                        <input type="text" class="code-box form-control" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="2">
                        <input type="text" class="code-box form-control" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="3">
                        <input type="text" class="code-box form-control" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="off" data-index="4">
                    </div>

                    <button type="button" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold mb-3 shadow-sm" id="btnVerifyOtp" onclick="submitOtpVerification()">
                        Verify & Proceed to Step 3
                    </button>

                    <div class="d-flex justify-content-between align-items-center text-muted small pt-2 border-top">
                        <span id="otpCountdownText">Resend code in <strong id="otpTimerSeconds">60</strong>s</span>
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary d-none" id="btnResendOtp" onclick="requestOtp(true)">
                            <i class="fa-solid fa-rotate-right me-1"></i> Resend Code
                        </button>
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted" onclick="dismissOtpModal()">
                            Edit Info
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- CHANNEL CHOOSER MODAL --}}
    {{-- ============================================ --}}
    <div class="modal fade" id="channelChooserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="channelChooserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body text-center p-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background-color: #e8f0fe; color: var(--color-primary); border-radius: 50%; font-size: 1.75rem;">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>

                    <h5 class="fw-bold text-dark mb-1">Choose Verification Method</h5>
                    <p class="text-muted small mb-4">
                        How would you like to receive your 5-digit verification code?
                    </p>

                    <div class="d-flex flex-column gap-3 mb-3" id="channelOptions">
                        {{-- Email option --}}
                        <button type="button" class="channel-option" id="channelOptEmail" onclick="chooseChannel('email')">
                            <div class="channel-icon email-icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <div class="channel-label">Email</div>
                                <div class="channel-desc" id="channelEmailDesc">Send code to your email address</div>
                            </div>
                        </button>

                        {{-- SMS option --}}
                        <button type="button" class="channel-option" id="channelOptSms" onclick="chooseChannel('phone')">
                            <div class="channel-icon sms-icon">
                                <i class="fa-solid fa-comment-sms"></i>
                            </div>
                            <div>
                                <div class="channel-label">SMS</div>
                                <div class="channel-desc" id="channelSmsDesc">Send code to your mobile number</div>
                            </div>
                        </button>
                    </div>

                    <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted" onclick="dismissChannelChooser()">
                        <i class="fa-solid fa-arrow-left me-1"></i> Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const stepTitles = {
            1: { title: 'Step 1 of 5', subtitle: 'Resident Identity' },
            2: { title: 'Step 2 of 5', subtitle: 'Credentials & Verification' },
            3: { title: 'Step 3 of 5', subtitle: 'Admin Verification Requirements' },
            4: { title: 'Step 4 of 5', subtitle: 'Data Privacy' },
            5: { title: 'Step 5 of 5', subtitle: 'Terms and Conditions' }
        };

        let currentStep = 1;
        let isContactVerified = false;
        let verifiedTarget = '';
        let activeOtpTarget = '';
        let activeOtpType = 'email'; // 'email' or 'phone'
        let otpCountdownInterval = null;
        let otpModalInstance = null;
        let channelChooserInstance = null;

        // ============================================
        // GOOGLE SIGN-UP: Pre-mark email as verified
        // so the OTP modal is never triggered.
        // ============================================
        @if($viaGoogle)
            isContactVerified = true;
            verifiedTarget    = @json($googleEmail);
        @endif

        function showManualForm() {
            document.getElementById('gatewayPanel').style.display = 'none';
            document.getElementById('manualFormPanel').style.display = 'block';
        }

        function showGateway() {
            document.getElementById('manualFormPanel').style.display = 'none';
            document.getElementById('gatewayPanel').style.display = 'block';
        }

        function isContactTargetVerified() {
            if (!isContactVerified || !verifiedTarget) return false;
            const email = document.querySelector('input[name="email"]')?.value.trim();
            const rawPhone = document.querySelector('input[name="phone_number"]')?.value.trim();
            const cleanPhone = rawPhone ? rawPhone.replace(/[^0-9]/g, '') : '';

            return (verifiedTarget === email || verifiedTarget === cleanPhone);
        }

        function getCurrentTarget() {
            const email = document.querySelector('input[name="email"]')?.value.trim();
            const rawPhone = document.querySelector('input[name="phone_number"]')?.value.trim();
            const cleanPhone = rawPhone ? rawPhone.replace(/[^0-9]/g, '') : '';

            if (verifiedTarget && (verifiedTarget === cleanPhone || verifiedTarget === email)) {
                return verifiedTarget;
            }

            return email || cleanPhone || '';
        }

        function goToStep(step) {
            // --- Validate current step before moving forward ---
            if (step > currentStep) {
                if (!validateStep(currentStep)) return;
            }

            // CRITICAL: Contact MUST be verified before proceeding to Step 3
            if (step === 3 && currentStep === 2) {
                if (!isContactTargetVerified()) {
                    openChannelChooser();
                    return;
                }
            }

            // Sync Step 1 contact fields → Step 2 when entering Step 2
            if (step === 2 && currentStep === 1) {
                const s1Phone = document.getElementById('step1PhoneNumber');
                const s1Email = document.getElementById('step1Email');
                const s2Phone = document.querySelector('input[name="phone_number"]');
                const s2Email = document.querySelector('input[name="email"]');
                if (s1Phone && s2Phone) s2Phone.value = s1Phone.value;
                if (s1Email && s2Email && !s2Email.readOnly) s2Email.value = s1Email.value;
            }

            // Sync Step 2 contact fields → Step 1 when going back to Step 1
            if (step === 1 && currentStep === 2) {
                const s1Phone = document.getElementById('step1PhoneNumber');
                const s1Email = document.getElementById('step1Email');
                const s2Phone = document.querySelector('input[name="phone_number"]');
                const s2Email = document.querySelector('input[name="email"]');
                if (s2Phone && s1Phone) s1Phone.value = s2Phone.value;
                if (s2Email && s1Email && !s1Email.readOnly) s1Email.value = s2Email.value;
            }

            // When entering Step 3, show the verified badge in Step 2
            if (step === 3) {
                const badge = document.getElementById('verifiedCredentialBadge');
                const badgeText = document.getElementById('verifiedCredentialText');
                if (badge && isContactVerified) {
                    badgeText.textContent = 'Verified: ' + verifiedTarget + ' — This will be used as your login credential.';
                    badge.classList.remove('d-none');
                }
            }

            // Switch panels
            document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
            document.getElementById(`panel-step-${step}`).classList.add('active');

            // Update progress indicators
            document.querySelectorAll('.step-indicator').forEach((ind, i) => {
                ind.classList.toggle('active', i < step);
            });

            // Update title
            document.getElementById('stepTitle').textContent = stepTitles[step].title;
            document.getElementById('stepSubtitle').textContent = stepTitles[step].subtitle;

            currentStep = step;

            // Scroll to top of card
            document.querySelector('.card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function openChannelChooser() {
            clearErrors();
            const emailInput = document.querySelector('input[name="email"]')?.value.trim();
            const rawPhone = document.querySelector('input[name="phone_number"]')?.value.trim();
            const phoneInput = rawPhone ? rawPhone.replace(/[^0-9]/g, '') : '';

            if (!emailInput && !phoneInput) {
                showAlert('Please provide an email address or mobile number to verify.');
                return;
            }

            // If only one channel is available, skip chooser and go straight to OTP
            if (emailInput && !phoneInput) {
                activeOtpType = 'email';
                activeOtpTarget = emailInput;
                openOtpModal();
                return;
            }
            if (!emailInput && phoneInput) {
                activeOtpType = 'phone';
                activeOtpTarget = phoneInput;
                openOtpModal();
                return;
            }

            // Both channels available — show chooser
            const emailDesc = document.getElementById('channelEmailDesc');
            const smsDesc = document.getElementById('channelSmsDesc');
            if (emailDesc) emailDesc.textContent = 'Send code to ' + emailInput;
            if (smsDesc) smsDesc.textContent = 'Send code to ' + phoneInput;

            document.getElementById('channelOptEmail').classList.remove('d-none');
            document.getElementById('channelOptSms').classList.remove('d-none');

            const modalEl = document.getElementById('channelChooserModal');
            if (!channelChooserInstance) {
                channelChooserInstance = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
            }
            channelChooserInstance.show();
        }

        function chooseChannel(type) {
            const emailInput = document.querySelector('input[name="email"]')?.value.trim();
            const rawPhone = document.querySelector('input[name="phone_number"]')?.value.trim();
            const phoneInput = rawPhone ? rawPhone.replace(/[^0-9]/g, '') : '';

            activeOtpType = type;
            activeOtpTarget = (type === 'email') ? emailInput : phoneInput;

            // Dismiss chooser, then open OTP modal
            dismissChannelChooser();
            setTimeout(() => {
                openOtpModal();
            }, 300);
        }

        function dismissChannelChooser() {
            if (channelChooserInstance) {
                channelChooserInstance.hide();
            } else {
                const el = document.getElementById('channelChooserModal');
                const instance = bootstrap.Modal.getInstance(el);
                if (instance) instance.hide();
            }
        }

        function openOtpModal() {
            // Channel switch option if both email and phone are present
            const emailInput = document.querySelector('input[name="email"]')?.value.trim();
            const rawPhone = document.querySelector('input[name="phone_number"]')?.value.trim();
            const phoneInput = rawPhone ? rawPhone.replace(/[^0-9]/g, '') : '';

            const switcher = document.getElementById('channelSwitchWrapper');
            if (emailInput && phoneInput) {
                switcher.classList.remove('d-none');
                updateSwitcherLabel();
            } else {
                switcher.classList.add('d-none');
            }

            updateOtpTargetDisplay();
            clearOtpInputs();
            hideOtpAlert();

            const modalEl = document.getElementById('otpModal');
            if (!otpModalInstance) {
                otpModalInstance = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
            }
            otpModalInstance.show();

            // Request OTP code immediately
            requestOtp(false);
        }

        function updateSwitcherLabel() {
            const switchBtn = document.getElementById('btnSwitchChannel');

            if (activeOtpType === 'email') {
                if (switchBtn) {
                    switchBtn.className = 'btn btn-sm btn-success text-white rounded-pill px-3 py-1 fw-semibold shadow-sm';
                    switchBtn.innerHTML = '<i class="fa-solid fa-mobile-screen me-1"></i> Send via SMS';
                }
            } else {
                if (switchBtn) {
                    switchBtn.className = 'btn btn-sm btn-primary text-white rounded-pill px-3 py-1 fw-semibold shadow-sm';
                    switchBtn.innerHTML = '<i class="fa-solid fa-envelope me-1"></i> Send via Email';
                }
            }
        }

        function switchOtpChannel() {
            const emailInput = document.querySelector('input[name="email"]')?.value.trim();
            const rawPhone = document.querySelector('input[name="phone_number"]')?.value.trim();
            const phoneInput = rawPhone ? rawPhone.replace(/[^0-9]/g, '') : '';

            if (activeOtpType === 'email' && phoneInput) {
                activeOtpType = 'phone';
                activeOtpTarget = phoneInput;
            } else if (activeOtpType === 'phone' && emailInput) {
                activeOtpType = 'email';
                activeOtpTarget = emailInput;
            }

            updateSwitcherLabel();
            updateOtpTargetDisplay();
            clearOtpInputs();
            requestOtp(false);
        }

        function updateOtpTargetDisplay() {
            const targetLabel = document.getElementById('otpTargetLabel');
            const icon = document.getElementById('otpIcon');
            if (activeOtpType === 'email') {
                targetLabel.textContent = activeOtpTarget;
                icon.className = 'fa-solid fa-envelope-circle-check';
            } else {
                targetLabel.textContent = activeOtpTarget;
                icon.className = 'fa-solid fa-comment-sms';
            }
        }

        async function requestOtp(isResend = false) {
            hideOtpAlert();
            const emailInput = document.querySelector('input[name="email"]')?.value.trim();
            const phoneInput = document.querySelector('input[name="phone_number"]')?.value.trim();
            const firstName = document.querySelector('input[name="first_name"]')?.value.trim();

            const btnResend = document.getElementById('btnResendOtp');
            if (btnResend) btnResend.classList.add('d-none');
            document.getElementById('otpCountdownText').classList.remove('d-none');

            try {
                const response = await fetch("{{ route('resident.register.send_otp') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: emailInput,
                        phone_number: phoneInput,
                        first_name: firstName,
                        target_type: activeOtpType
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    if (data.field) {
                        dismissOtpModal();
                        const fieldEl = document.querySelector(`[name="${data.field}"]`);
                        if (fieldEl) {
                            fieldEl.classList.add('field-error');
                            fieldEl.focus();
                        }
                        showAlert(data.message || 'This contact is already registered.');
                        return;
                    }
                    showOtpAlert(data.message || 'Failed to send verification code. Please try again.');
                    return;
                }

                if (data.display_target) {
                    const targetLabel = document.getElementById('otpTargetLabel');
                    if (targetLabel) targetLabel.textContent = data.display_target;
                }

                // Start 60-second timer
                startOtpTimer(60);

                // Focus first input box
                setTimeout(() => {
                    const firstInput = document.querySelector('.code-box[data-index="0"]');
                    if (firstInput) firstInput.focus();
                }, 300);

            } catch (err) {
                showOtpAlert('Network error while requesting verification code. Please check your connection.');
            }
        }

        function startOtpTimer(seconds) {
            clearInterval(otpCountdownInterval);
            let remaining = seconds;
            const timerSpan = document.getElementById('otpTimerSeconds');
            const countdownText = document.getElementById('otpCountdownText');
            const resendBtn = document.getElementById('btnResendOtp');

            countdownText.classList.remove('d-none');
            resendBtn.classList.add('d-none');
            timerSpan.textContent = remaining;

            otpCountdownInterval = setInterval(() => {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(otpCountdownInterval);
                    countdownText.classList.add('d-none');
                    resendBtn.classList.remove('d-none');
                } else {
                    timerSpan.textContent = remaining;
                }
            }, 1000);
        }

        async function submitOtpVerification() {
            hideOtpAlert();
            const boxes = document.querySelectorAll('.code-box');
            let code = '';
            boxes.forEach(b => code += b.value.trim());

            if (code.length < 5) {
                showOtpAlert('Please enter the complete 5-digit verification code.');
                shakeBoxes();
                return;
            }

            const btn = document.getElementById('btnVerifyOtp');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Verifying...';

            try {
                const response = await fetch("{{ route('resident.register.verify_otp') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        code: code,
                        target: activeOtpTarget
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    showOtpAlert(data.message || 'Incorrect verification code. Please try again.');
                    shakeBoxes();
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    return;
                }

                // SUCCESS!
                isContactVerified = true;
                verifiedTarget = data.verified_target || activeOtpTarget;
                clearInterval(otpCountdownInterval);

                btn.className = 'btn btn-success w-100 rounded-3 py-2 fw-semibold mb-3 shadow-sm';
                btn.innerHTML = '<i class="fa-solid fa-circle-check me-2"></i> Verified!';

                setTimeout(() => {
                    dismissOtpModal();
                    btn.disabled = false;
                    btn.className = 'btn btn-primary w-100 rounded-3 py-2 fw-semibold mb-3 shadow-sm';
                    btn.innerHTML = originalText;

                    // Show verified badge on Step 2
                    const badge = document.getElementById('verifiedCredentialBadge');
                    const badgeText = document.getElementById('verifiedCredentialText');
                    if (badge) {
                        badgeText.textContent = 'Verified: ' + verifiedTarget + ' — This will be used as your login credential.';
                        badge.classList.remove('d-none');
                    }

                    // Advance to Step 3 if Step 2 validation passes (passwords valid)
                    if (validateStep(2)) {
                        goToStep(3);
                    }
                }, 500);

            } catch (err) {
                showOtpAlert('Network error while verifying code. Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function shakeBoxes() {
            const boxes = document.querySelectorAll('.code-box');
            boxes.forEach(b => {
                b.classList.add('shake');
                setTimeout(() => b.classList.remove('shake'), 500);
            });
        }

        function clearOtpInputs() {
            document.querySelectorAll('.code-box').forEach(b => {
                b.value = '';
                b.classList.remove('shake');
            });
        }

        function showOtpAlert(msg) {
            const el = document.getElementById('otpAlert');
            if (el) {
                el.textContent = msg;
                el.classList.remove('d-none');
            }
        }

        function hideOtpAlert() {
            const el = document.getElementById('otpAlert');
            if (el) {
                el.classList.add('d-none');
                el.textContent = '';
            }
        }

        function dismissOtpModal() {
            if (otpModalInstance) {
                otpModalInstance.hide();
            } else {
                const el = document.getElementById('otpModal');
                const instance = bootstrap.Modal.getInstance(el);
                if (instance) instance.hide();
            }
            clearInterval(otpCountdownInterval);
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(b => {
                    if (!document.querySelector('.modal.show')) {
                        b.remove();
                    }
                });
                if (!document.querySelector('.modal.show')) {
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.body.style.removeProperty('overflow');
                }
            }, 400);
        }

        // Setup OTP code box listeners
        document.querySelectorAll('.code-box').forEach((box, idx, boxes) => {
            box.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length >= 1) {
                    this.value = this.value.slice(0, 1);
                    if (idx < boxes.length - 1) {
                        boxes[idx + 1].focus();
                    } else if (idx === boxes.length - 1) {
                        // Auto-submit when all 5 digits are entered
                        submitOtpVerification();
                    }
                }
            });

            box.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && idx > 0) {
                    boxes[idx - 1].focus();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    submitOtpVerification();
                }
            });

            box.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
                if (pasteData.length > 0) {
                    for (let i = 0; i < boxes.length; i++) {
                        boxes[i].value = pasteData[i] || '';
                    }
                    if (pasteData.length >= 5) {
                        boxes[4].focus();
                        submitOtpVerification();
                    } else {
                        const nextEmpty = Math.min(pasteData.length, boxes.length - 1);
                        boxes[nextEmpty].focus();
                    }
                }
            });
        });

        // Invalidate verification only if the verified contact field changes
        document.querySelector('input[name="email"]')?.addEventListener('input', function() {
            if (isContactVerified && verifiedTarget && verifiedTarget.includes('@') && verifiedTarget !== this.value.trim()) {
                isContactVerified = false;
                verifiedTarget = '';
                document.getElementById('verifiedCredentialBadge')?.classList.add('d-none');
            }
        });
        document.querySelector('input[name="phone_number"]')?.addEventListener('input', function() {
            const clean = this.value.trim().replace(/[^0-9]/g, '');
            if (isContactVerified && verifiedTarget && !verifiedTarget.includes('@') && verifiedTarget !== clean) {
                isContactVerified = false;
                verifiedTarget = '';
                document.getElementById('verifiedCredentialBadge')?.classList.add('d-none');
            }
        });

        // Also invalidate if Step 1 contact fields change
        document.getElementById('step1PhoneNumber')?.addEventListener('input', function() {
            const clean = this.value.trim().replace(/[^0-9]/g, '');
            if (isContactVerified && verifiedTarget && !verifiedTarget.includes('@') && verifiedTarget !== clean) {
                isContactVerified = false;
                verifiedTarget = '';
                document.getElementById('verifiedCredentialBadge')?.classList.add('d-none');
            }
        });
        document.getElementById('step1Email')?.addEventListener('input', function() {
            if (isContactVerified && verifiedTarget && verifiedTarget.includes('@') && verifiedTarget !== this.value.trim()) {
                isContactVerified = false;
                verifiedTarget = '';
                document.getElementById('verifiedCredentialBadge')?.classList.add('d-none');
            }
        });

        function validateStep(step) {
            clearErrors();

            if (step === 1) {
                const required = [
                    { name: 'last_name', label: 'Last Name' },
                    { name: 'first_name', label: 'First Name' },
                    { name: 'birthdate', label: 'Birthdate' },
                    { name: 'age', label: 'Age' },
                    { name: 'birthplace', label: 'Birthplace' },
                    { name: 'gender', label: 'Gender' },
                    { name: 'civil_status', label: 'Civil Status' },
                    { name: 'nationality', label: 'Nationality' },
                    { name: 'occupation', label: 'Occupation' },
                    { name: 'purok', label: 'Purok' },
                ];

                for (const f of required) {
                    const el = document.querySelector(`[name="${f.name}"]`);
                    if (!el || !el.value.trim()) {
                        el.classList.add('field-error');
                        el.focus();
                        showAlert(`Please fill in the ${f.label} field.`);
                        return false;
                    }
                }

                // Validate age value
                const ageVal = parseInt(document.querySelector('[name="age"]').value, 10);
                if (isNaN(ageVal) || ageVal < 0 || ageVal > 150) {
                    document.querySelector('[name="age"]').classList.add('field-error');
                    showAlert('Please enter a valid age between 0 and 150.');
                    return false;
                }

                // Validate Step 1 contact fields
                const s1Phone = document.getElementById('step1PhoneNumber');
                const s1Email = document.getElementById('step1Email');
                const s1PhoneVal = s1Phone ? s1Phone.value.trim().replace(/[^0-9]/g, '') : '';
                const s1EmailVal = s1Email ? s1Email.value.trim() : '';

                @if(!$viaGoogle)
                if (!s1PhoneVal && !s1EmailVal) {
                    if (s1Phone) { s1Phone.classList.add('field-error'); s1Phone.focus(); }
                    showAlert('Please provide a mobile number or email address.');
                    return false;
                }

                if (s1PhoneVal && (s1PhoneVal.length < 10 || s1PhoneVal.length > 11)) {
                    if (s1Phone) { s1Phone.classList.add('field-error'); s1Phone.focus(); }
                    showAlert('Please enter a valid mobile number (e.g. 09XXXXXXXXX).');
                    return false;
                }
                @endif

                if (s1EmailVal && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s1EmailVal)) {
                    if (s1Email) { s1Email.classList.add('field-error'); s1Email.focus(); }
                    showAlert('Please enter a valid email address.');
                    return false;
                }

                return true;
            }

            if (step === 2) {
                // Validate contact info (phone/email) — at least one required
                @if(!$viaGoogle)
                const phone = document.querySelector('[name="phone_number"]').value.trim();
                const cleanPhone = phone.replace(/[^0-9]/g, '');
                const email = document.querySelector('[name="email"]').value.trim();

                if (!cleanPhone && !email) {
                    document.querySelector('[name="phone_number"]').classList.add('field-error');
                    showAlert('Please provide a mobile number or email address.');
                    return false;
                }

                if (cleanPhone && (cleanPhone.length < 10 || cleanPhone.length > 11)) {
                    document.querySelector('[name="phone_number"]').classList.add('field-error');
                    showAlert('Please enter a valid mobile number (e.g. 09XXXXXXXXX).');
                    return false;
                }

                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    document.querySelector('[name="email"]').classList.add('field-error');
                    showAlert('Please enter a valid email address.');
                    return false;
                }
                @endif

                // Validate password
                @if($viaGoogle)
                    // Google users don't set a password — skip password validation
                @else
                const pwd = document.getElementById('regPassword');
                const confirm = document.getElementById('regPasswordConfirm');

                if (!pwd.value || pwd.value.length < 8) {
                    pwd.classList.add('field-error');
                    pwd.focus();
                    showAlert('Password must be at least 8 characters.');
                    return false;
                }

                if (pwd.value !== confirm.value) {
                    confirm.classList.add('field-error', 'is-invalid');
                    confirm.focus();
                    return false;
                }
                @endif

                return true;
            }

            if (step === 3) {
                const idType = document.getElementById('idTypeSelect');
                const idFile = document.getElementById('idUploadInput');

                if (!idType.value) {
                    idType.classList.add('field-error');
                    idType.focus();
                    showAlert('Please select an ID type.');
                    return false;
                }

                if (!idFile.files.length) {
                    idFile.classList.add('field-error');
                    idFile.focus();
                    showAlert('Please upload your ID document.');
                    return false;
                }

                // Validate file size (10MB max)
                if (idFile.files[0].size > 10 * 1024 * 1024) {
                    idFile.classList.add('field-error');
                    showAlert('File is too large. Maximum size is 10MB.');
                    return false;
                }

                // Face verification check
                const selfieData = document.getElementById('selfiePhotoInput')?.value;
                const selfieFile = document.getElementById('selfieUploadInput')?.files;
                if (!selfieData && (!selfieFile || !selfieFile.length)) {
                    showAlert('Please take a live face photo or upload a selfie for identity verification.');
                    return false;
                }

                // When leaving step 3, stop the camera if active
                stopFaceCamera();

                return true;
            }

            if (step === 4) {
                const privacy = document.getElementById('agreePrivacy');

                if (!privacy || !privacy.checked) {
                    if (privacy) privacy.classList.add('is-invalid');
                    showAlert('Please read and agree to the Data Privacy Act notice.');
                    return false;
                }

                return true;
            }

            if (step === 5) {
                const terms = document.getElementById('agreeTerms');

                if (!terms || !terms.checked) {
                    if (terms) terms.classList.add('is-invalid');
                    showAlert('Please agree to the Terms and Conditions.');
                    return false;
                }

                return true;
            }

            return true;
        }

        function clearErrors() {
            document.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            const existing = document.getElementById('clientAlert');
            if (existing) existing.remove();
        }

        function showAlert(message) {
            const existing = document.getElementById('clientAlert');
            if (existing) existing.remove();

            const alert = document.createElement('div');
            alert.id = 'clientAlert';
            alert.className = 'alert alert-danger border-0 small rounded-3 mb-3';
            alert.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i> ${message}`;

            const form = document.getElementById('registerForm');
            form.insertBefore(alert, form.firstChild);
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-solid fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-solid fa-eye';
            }
        }

        // Remove error styling on input
        document.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('input', function() {
                this.classList.remove('field-error', 'is-invalid');
            });
        });

        // Enforce CapsLock / uppercase on input typing
        document.querySelectorAll('.input-uppercase').forEach(function(el) {
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

        // Auto-calculate age from birthdate
        document.getElementById('regBirthdate')?.addEventListener('change', function() {
            if (!this.value) return;
            const dob = new Date(this.value);
            if (!isNaN(dob.getTime())) {
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                if (age >= 0 && age <= 150) {
                    const ageInput = document.getElementById('regAge');
                    if (ageInput) {
                        ageInput.value = age;
                        ageInput.classList.remove('field-error');
                    }
                }
            }
        });

        // ============================================
        // LIVE FACE VERIFICATION / WEBCAM SYSTEM
        // ============================================
        let faceMediaStream = null;

        async function startFaceCamera() {
            try {
                const videoEl = document.getElementById('faceVideoElement');
                const promptState = document.getElementById('facePromptState');
                const activeState = document.getElementById('faceActiveState');
                const capturedState = document.getElementById('faceCapturedState');
                const fallbackBox = document.getElementById('selfieUploadFallbackBox');

                if (fallbackBox) fallbackBox.classList.add('d-none');
                if (capturedState) capturedState.classList.add('d-none');

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Camera is not supported on this browser or device. Please upload a selfie photo instead.');
                    toggleSelfieUploadFallback();
                    return;
                }

                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280, min: 640 },
                        height: { ideal: 720, min: 480 }
                    },
                    audio: false
                });

                faceMediaStream = stream;
                if (videoEl) {
                    videoEl.srcObject = stream;
                    await videoEl.play();
                }

                if (promptState) promptState.classList.add('d-none');
                if (activeState) activeState.classList.remove('d-none');
            } catch (err) {
                console.warn('Face camera access error:', err);
                alert('Could not access camera (' + (err.message || 'permission denied') + '). You may upload a photo of your face instead.');
                toggleSelfieUploadFallback();
            }
        }

        function stopFaceCamera() {
            if (faceMediaStream) {
                faceMediaStream.getTracks().forEach(track => track.stop());
                faceMediaStream = null;
            }
            const videoEl = document.getElementById('faceVideoElement');
            if (videoEl) videoEl.srcObject = null;

            const promptState = document.getElementById('facePromptState');
            const activeState = document.getElementById('faceActiveState');
            if (activeState) activeState.classList.add('d-none');
            if (promptState && document.getElementById('faceCapturedState')?.classList.contains('d-none')) {
                promptState.classList.remove('d-none');
            }
        }

        function captureFaceSnapshot() {
            const videoEl = document.getElementById('faceVideoElement');
            const canvas = document.getElementById('faceSnapshotCanvas');
            const previewImg = document.getElementById('faceCapturedPreview');
            const hiddenInput = document.getElementById('selfiePhotoInput');
            const activeState = document.getElementById('faceActiveState');
            const capturedState = document.getElementById('faceCapturedState');
            const flashOverlay = document.getElementById('faceFlashOverlay');

            if (!videoEl || !canvas) return;

            // Visual shutter flash feedback
            if (flashOverlay) {
                flashOverlay.style.opacity = '0.9';
                setTimeout(() => {
                    flashOverlay.style.opacity = '0';
                }, 160);
            }

            const width = videoEl.videoWidth || 1280;
            const height = videoEl.videoHeight || 720;
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            // Mirror image horizontally to match webcam mirror preview
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(videoEl, 0, 0, width, height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.90);

            if (hiddenInput) hiddenInput.value = dataUrl;
            if (previewImg) previewImg.src = dataUrl;

            // Clear file upload input if any
            const fileInput = document.getElementById('selfieUploadInput');
            if (fileInput) fileInput.value = '';

            setTimeout(() => {
                stopFaceCamera();
                if (activeState) activeState.classList.add('d-none');
                if (capturedState) capturedState.classList.remove('d-none');
            }, 200);
        }

        function retakeFacePhoto() {
            const hiddenInput = document.getElementById('selfiePhotoInput');
            const capturedState = document.getElementById('faceCapturedState');
            if (hiddenInput) hiddenInput.value = '';
            if (capturedState) capturedState.classList.add('d-none');
            startFaceCamera();
        }

        function toggleSelfieUploadFallback() {
            stopFaceCamera();
            document.getElementById('facePromptState')?.classList.add('d-none');
            document.getElementById('faceActiveState')?.classList.add('d-none');
            document.getElementById('selfieUploadFallbackBox')?.classList.remove('d-none');
        }

        function useFaceCameraInstead() {
            document.getElementById('selfieUploadFallbackBox')?.classList.add('d-none');
            startFaceCamera();
        }

        function handleSelfieFileSelect(input) {
            if (input && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('faceCapturedPreview');
                    const capturedState = document.getElementById('faceCapturedState');
                    const hiddenInput = document.getElementById('selfiePhotoInput');
                    if (previewImg) previewImg.src = e.target.result;
                    if (hiddenInput) hiddenInput.value = ''; // Let server process the file input
                    document.getElementById('facePromptState')?.classList.add('d-none');
                    document.getElementById('faceActiveState')?.classList.add('d-none');
                    document.getElementById('selfieUploadFallbackBox')?.classList.add('d-none');
                    if (capturedState) capturedState.classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // If server-side errors exist, show manual form and go to step 1
        @if ($errors->any())
            showManualForm();
        @endif

        // Submit button validation & loading state
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            if (!validateStep(5)) {
                e.preventDefault();
                return false;
            }
            const btn = document.getElementById('btnSubmitRegister');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting...';
            }
        });
    </script>
</body>
</html>