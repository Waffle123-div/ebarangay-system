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
        /* Lock body viewport to prevent structural layout breaks during step expansions */
        html, body { height: 100vh; overflow: hidden; background-color: #ffffff; font-family: var(--font-body); margin: 0; }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }

        /* Split-screen wrapper styles */
        .split-layout { height: 100vh; width: 100vw; display: flex; flex-wrap: nowrap; overflow: hidden; }
        
        /* Left Panel: Fixed sticky viewport container so carousel never scrolls away */
        .left-panel {
            height: 100vh;
            background-color: #0b2e59;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 0;
        }
        #loginCarousel { position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; }
        .carousel-inner, .carousel-item { height: 100%; width: 100%; }
        /* Responsive Banner Fix: cover + left center keeps banner content fully visible without cropping or letterboxing */
        .carousel-item img { object-fit: cover; object-position: left center; height: 100%; width: 100%; }
        .carousel-indicators { margin-bottom: 2rem; z-index: 3; }
        .carousel-indicators button { width: 30px !important; height: 4px !important; border-radius: 2px; background-color: rgba(255,255,255,0.4) !important; border: none !important; }
        .carousel-indicators button.active { background-color: #ffffff !important; box-shadow: 0 0 8px rgba(0,0,0,0.3); }

        /* Carousel Side Arrows */
        .carousel-control-prev, .carousel-control-next { width: 8%; z-index: 4; opacity: 0.8; transition: opacity 0.2s ease; }
        .carousel-control-prev:hover, .carousel-control-next:hover { opacity: 1; }
        .carousel-control-prev-icon, .carousel-control-next-icon { width: 2.2rem; height: 2.2rem; background-size: 100%, 100%; }

        /* Right Panel: Independently scrollable container so forms/cameras never break layout */
        .right-panel {
            height: 100vh;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            background: #f8fafc; /* Slightly off-white background to highlight container */
        }
        .register-form-container { width: 100%; max-width: 780px; margin: auto; }

        /* Distinct Card Container Styling */
        .auth-card-container {
            background: #ffffff;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
        }

        .gateway-panel { display: none !important; }
        .manual-form-panel { display: block; }

        /* Progress bar */
        .step-indicator { width: 33.333%; height: 4px; background-color: #e9ecef; transition: background-color 0.3s ease; }
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
            max-height: 52vh;
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

        /* Emphasized Next Button System */
        .next-step-btn {
            background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%) !important;
            color: #ffffff !important;
            border: none !important;
            font-weight: 700 !important;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(13, 110, 253, 0.4), 0 0 0 2px rgba(13, 110, 253, 0.15) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .next-step-btn.is-revealed {
            display: inline-flex !important;
            animation: nextBtnPopIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards,
                       nextBtnPulseEmphasis 2.5s ease-in-out infinite 0.4s;
        }

        .next-step-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -70%;
            width: 45%;
            height: 200%;
            background: linear-gradient(
                60deg,
                rgba(255, 255, 255, 0) 15%,
                rgba(255, 255, 255, 0.4) 50%,
                rgba(255, 255, 255, 0) 85%
            );
            transform: rotate(25deg);
            pointer-events: none;
        }

        .next-step-btn.is-revealed::after {
            animation: nextBtnShineSweep 3s ease-in-out infinite 0.6s;
        }

        @keyframes nextBtnPopIn {
            0% {
                opacity: 0;
                transform: translateY(12px) scale(0.92);
            }
            70% {
                transform: translateY(-2px) scale(1.03);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes nextBtnPulseEmphasis {
            0%, 100% {
                box-shadow: 0 4px 14px rgba(13, 110, 253, 0.4), 0 0 0 0 rgba(13, 110, 253, 0.4);
            }
            50% {
                box-shadow: 0 8px 22px rgba(13, 110, 253, 0.65), 0 0 0 6px rgba(13, 110, 253, 0.15);
            }
        }

        @keyframes nextBtnShineSweep {
            0% {
                left: -70%;
            }
            30% {
                left: 140%;
            }
            100% {
                left: 140%;
            }
        }

        .next-step-btn:hover {
            background: linear-gradient(135deg, #0b5ed7 0%, #003fa8 100%) !important;
            transform: translateY(-2px) scale(1.01) !important;
            box-shadow: 0 8px 22px rgba(13, 110, 253, 0.6), 0 0 0 3px rgba(13, 110, 253, 0.25) !important;
        }

        .next-step-btn:active {
            transform: translateY(0) scale(0.98) !important;
        }

        .next-step-btn .next-btn-icon {
            transition: transform 0.2s ease;
        }

        .next-step-btn:hover .next-btn-icon {
            transform: translateX(4px);
        }

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

        /* Live Face Verification Styles */
        .face-cam-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
        }
        .face-video-viewport {
            width: 100%;
            aspect-ratio: 4 / 3;
            min-height: 320px;
            background: #090e17;
            overflow: hidden;
            position: relative;
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
            width: clamp(210px, 45%, 280px);
            height: clamp(270px, 65%, 350px);
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
            max-height: 400px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        @media (max-width: 991.98px) {
            html, body { height: auto; overflow: auto; }
            .split-layout { height: auto; overflow: auto; }
            .left-panel { display: none; }
            .right-panel { height: auto; padding: 2rem 1rem; width: 100%; overflow: visible; background: #ffffff; }
        }
    </style>
</head>
@php
    $viaGoogle     = session('google_reg_id') && session('google_reg_email');
    $googleEmail   = session('google_reg_email', '');
    $googleName    = session('google_reg_name', '');
@endphp
<body>

<div class="container-fluid p-0">
    <div class="row g-0 split-layout">
        
        <!-- LEFT SIDE: CAROUSEL BANNER WITH ARROWS -->
        <div class="col-lg-6 left-panel d-none d-lg-block">
            <div id="loginCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="2"></button>
                </div>

                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="{{ asset('images/Banner 1.png') }}" alt="Banner 1">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ asset('images/Banner 2.png') }}" alt="Banner 2">
                    </div>
                    <div class="carousel-item">
                        <img src="{{ asset('images/Banner 3.png') }}" alt="Banner 3">
                    </div>
                </div>

                <!-- Side Navigation Arrows -->
                <button class="carousel-control-prev" type="button" data-bs-target="#loginCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#loginCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>

        <!-- RIGHT SIDE: FULL REGISTRATION FORM -->
        <div class="col-lg-6 right-panel">
            <div class="register-form-container">
                <div class="card auth-card-container rounded-4 p-4 p-md-5 my-3">

                    {{-- ============================================ --}}
                    {{-- REGISTRATION WIZARD --}}
                    {{-- ============================================ --}}
                    <div id="manualFormPanel" class="manual-form-panel">

                        {{-- Progress Bar --}}
                        <div class="d-flex mb-4">
                            <div class="step-indicator active rounded-start" data-step="1"></div>
                            <div class="step-indicator ms-1" data-step="2"></div>
                            <div class="step-indicator ms-1 rounded-end" data-step="3"></div>
                        </div>

                        {{-- Step Title --}}
                        <div class="text-center mb-3">
                            <h4 class="fw-bold text-dark mb-1" id="stepTitle">Step 1 of 3</h4>
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
                                            <input type="number" name="age" id="regAge" class="form-control rounded-3 bg-light" value="{{ old('age') }}" placeholder="Auto-computed" readonly required>
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

                                    {{-- B. Residency --}}
                                    <div class="section-label mt-2"><i class="fa-solid fa-location-dot me-1"></i> B. Residency</div>

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

                                    <div class="d-flex gap-2 mt-4 pt-1 pb-2" id="step1Actions">
                                        <a href="{{ route('resident.login') }}" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" id="btnBackStep1"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
                                        <button type="button" class="btn btn-primary w-50 rounded-3 py-2 fw-bold next-step-btn" id="btnNextStep1" onclick="goToStep(2)" style="display: none;">Next <i class="fa-solid fa-arrow-right ms-1 next-btn-icon"></i></button>
                                    </div>

                                </div>
                            </div>

                            {{-- ========================================== --}}
                            {{-- STEP 2: CREDENTIALS & VERIFICATION --}}
                            {{-- ========================================== --}}
                            <div class="step-panel" id="panel-step-2">
                                <div class="step-scroll">

                                    {{-- A. Contact Information --}}
                                    <div class="section-label"><i class="fa-solid fa-address-card me-1"></i> A. Contact Information</div>
                                    <div class="alert alert-info border-0 small py-2 px-3 mb-3 text-start" style="font-size: 12px;">
                                        <i class="fa-solid fa-circle-info me-1"></i> You can enter your <strong>Mobile Number</strong>, <strong>Email Address</strong>, or both. At least one is required to verify your account.
                                    </div>

                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-semibold">Mobile Number</label>
                                            <input type="text" name="phone_number" class="form-control rounded-3" value="{{ old('phone_number') }}" placeholder="09XXXXXXXXX">
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
                                        <div class="alert alert-success border-0 small rounded-3" role="alert">
                                            <i class="fa-brands fa-google me-1 text-danger"></i>
                                            You are signing up with <strong>Google</strong>. No password is needed — you can always sign in using your Google account.
                                        </div>
                                        <input type="hidden" name="password" value="">
                                    @else
                                        <div class="row text-start">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                                                <div class="position-relative">
                                                    <input type="password" name="password" id="regPassword" class="form-control rounded-3" required oninput="checkPasswordStrength(this.value)">
                                                    <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent text-muted" onclick="togglePassword('regPassword', this)">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                </div>
                                                {{-- Password Strength Meter --}}
                                                <div class="mt-1">
                                                    <div class="d-flex gap-1 mb-1" id="strengthBars">
                                                        <div class="flex-fill rounded" id="bar1" style="height:4px;background:#e5e7eb;"></div>
                                                        <div class="flex-fill rounded" id="bar2" style="height:4px;background:#e5e7eb;"></div>
                                                        <div class="flex-fill rounded" id="bar3" style="height:4px;background:#e5e7eb;"></div>
                                                        <div class="flex-fill rounded" id="bar4" style="height:4px;background:#e5e7eb;"></div>
                                                    </div>
                                                    <div class="small" id="strengthLabel" style="font-size:11px;color:#9ca3af;">Minimum 8 characters</div>
                                                </div>
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

                                    <div class="d-flex gap-2 mt-4 pt-1 pb-2" id="step2Actions">
                                        <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" id="btnBackStep2" onclick="goToStep(1)"><i class="fa-solid fa-arrow-left me-1"></i> Back</button>
                                        <button type="button" class="btn btn-primary w-50 rounded-3 py-2 fw-bold next-step-btn" id="btnNextStep2" onclick="goToStep(3)" style="display: none;">Next <i class="fa-solid fa-arrow-right ms-1 next-btn-icon"></i></button>
                                    </div>

                                </div>
                            </div>

                            {{-- ========================================== --}}
                            {{-- STEP 3: ADMIN VERIFICATION & AGREEMENTS --}}
                            {{-- ========================================== --}}
                            <div class="step-panel" id="panel-step-3">
                                <div class="step-scroll">
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

                                        <input type="hidden" name="selfie_photo" id="selfiePhotoInput" value="">
                                        <canvas id="faceSnapshotCanvas" class="d-none"></canvas>
                                    </div>

                                    {{-- ============================================ --}}
                                    {{-- DATA PRIVACY & TERMS AGREEMENTS             --}}
                                    {{-- ============================================ --}}
                                    <div class="mt-4 pt-3 border-top text-start">
                                        <div class="section-label mb-2"><i class="fa-solid fa-shield-halved me-1"></i> Data Privacy &amp; Consent</div>
                                        <div class="mb-3 p-3 bg-light rounded-3 small text-muted terms-box">
                                            <h6 class="fw-bold text-dark"><i class="fa-solid fa-shield-halved me-1"></i> Data Privacy Act of 2012 (RA 10173)</h6>
                                            <p>In compliance with the Data Privacy Act of 2012 and its Implementing Rules and Regulations, the Barangay Cogon East administration ensures that all personal information collected through this platform shall be processed fairly and lawfully.</p>
                                            <p>Your personal data will be used solely for the purpose of verifying your identity, processing your barangay document requests, and maintaining accurate residency records. This information shall not be shared, sold, or disclosed to any third party without your explicit consent, except when required by law.</p>
                                            <p class="mb-0">You have the right to access, correct, and request the removal of your personal data at any time by contacting the barangay office.</p>
                                        </div>

                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" name="privacy" value="1" id="agreePrivacy" required>
                                            <label class="form-check-label small text-muted" for="agreePrivacy">
                                                I have read and agree to the <strong>Data Privacy Act</strong> notice above <span class="text-danger">*</span>
                                            </label>
                                        </div>

                                        <div class="section-label mb-2"><i class="fa-solid fa-file-contract me-1"></i> Terms &amp; Conditions</div>
                                        <div class="mb-3 p-3 bg-light rounded-3 small text-muted terms-box">
                                            <h6 class="fw-bold text-dark"><i class="fa-solid fa-file-contract me-1"></i> Terms and Conditions</h6>
                                            <p>By accessing this platform, you warrant that you are a legal resident of Barangay Cogon East, Carmen, Cebu. All document requests submitted through this web-based tracking system must consist of authentic personal declarations.</p>
                                            <p>Falsification of administrative identity profiles or residency documentation will result in the immediate revocation of credentials and potential endorsement to local legal authorities.</p>
                                            <p class="mb-0">The barangay reserves the right to verify all submitted documents and deny or revoke access to any account found to have submitted fraudulent information.</p>
                                        </div>

                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" name="terms" value="1" id="agreeTerms" required>
                                            <label class="form-check-label small text-muted" for="agreeTerms">
                                                I agree to the <strong>Terms and Conditions</strong> stated above <span class="text-danger">*</span>
                                            </label>
                                        </div>

                                        <div class="alert alert-primary border-0 small rounded-3 mb-2" role="alert">
                                            <i class="fa-solid fa-clock me-1"></i>
                                            After submitting, your account will be reviewed by the barangay admin. Please wait for approval before you can fully access the system.
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mt-4 pt-1 pb-2" id="step3Actions">
                                        <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="stopFaceCamera(); goToStep(2)">
                                            <i class="fa-solid fa-arrow-left me-1"></i> Back
                                        </button>
                                        <button type="submit" id="btnSubmitRegister" class="btn btn-primary w-50 rounded-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-user-plus me-1"></i> Sign Up
                                        </button>
                                    </div>

                                </div>
                            </div>

                        </form>

                        <div class="text-center small mt-3">
                            Already have an account? <a href="{{ route('resident.login') }}" class="text-primary text-decoration-none fw-bold">Log In Here</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

    {{-- ============================================ --}}
    {{-- LIVE FACE CAMERA MODAL --}}
    {{-- ============================================ --}}
    <div class="modal fade" id="faceCameraModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="faceCameraModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 620px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-0 text-center position-relative bg-dark">
                    <div class="face-video-viewport m-0 border-0 rounded-0" style="max-width: 100%; aspect-ratio: 4 / 3; min-height: 360px;">
                        <video id="faceVideoElement" autoplay playsinline></video>
                        <div id="faceFlashOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white" style="opacity: 0; pointer-events: none; transition: opacity 0.25s ease-out; z-index: 20;"></div>
                        
                        {{-- Top Controls Bar --}}
                        <div class="position-absolute top-0 start-0 end-0 p-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(to bottom, rgba(0,0,0,0.7), transparent); z-index: 10;">
                            <span class="badge bg-danger text-white px-2.5 py-1.5 rounded-pill d-inline-flex align-items-center gap-1.5" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-circle text-white fa-fade"></i> LIVE BIOMETRIC CAM
                            </span>
                            <button type="button" class="btn btn-sm btn-dark bg-opacity-75 border-0 text-white rounded-circle shadow" style="width: 32px; height: 32px;" onclick="stopFaceCamera()" title="Close Camera">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        {{-- Crosshair and Oval Guidelines --}}
                        <div class="biometric-crosshair top-left"></div>
                        <div class="biometric-crosshair top-right"></div>
                        <div class="biometric-crosshair bottom-left"></div>
                        <div class="biometric-crosshair bottom-right"></div>
                        <div class="biometric-oval-frame"></div>

                        {{-- Bottom Overlay Guideline Bar --}}
                        <div class="position-absolute bottom-0 start-0 end-0 py-2.5 px-3 bg-dark bg-opacity-75 text-white text-center small" style="backdrop-filter: blur(4px); z-index: 10;">
                            <i class="fa-solid fa-arrows-to-circle text-primary me-1"></i> Align your face within the oval guideline and click <strong>Take Snapshot</strong>
                        </div>
                    </div>

                    {{-- Modal Bottom Actions Panel --}}
                    <div class="p-3 bg-white d-flex justify-content-center align-items-center gap-2">
                        <button type="button" class="btn btn-success btn-lg px-4 py-2.5 rounded-pill fw-bold shadow" onclick="captureFaceSnapshot()">
                            <i class="fa-solid fa-camera-retro me-2"></i> Take Snapshot
                        </button>
                        <button type="button" class="btn btn-light border px-4 py-2.5 rounded-pill text-muted fw-semibold" onclick="stopFaceCamera()">
                            Cancel
                        </button>
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
            1: { title: 'Step 1 of 3', subtitle: 'Resident Identity' },
            2: { title: 'Step 2 of 3', subtitle: 'Credentials & Verification' },
            3: { title: 'Step 3 of 3', subtitle: 'Admin Verification & Terms' }
        };

        let currentStep = 1;
        let isContactVerified = false;
        let verifiedTarget = '';
        let activeOtpTarget = '';
        let activeOtpType = 'email'; // 'email' or 'phone'
        let otpCountdownInterval = null;
        let otpModalInstance = null;
        let channelChooserInstance = null;
        let faceCameraModalInstance = null;

        // ============================================
        // GOOGLE SIGN-UP: Pre-mark email as verified
        // so the OTP modal is never triggered.
        // ============================================
        @if($viaGoogle)
            isContactVerified = true;
            verifiedTarget    = @json($googleEmail);
        @endif

        function showManualForm() {
            const gw = document.getElementById('gatewayPanel');
            if (gw) gw.style.display = 'none';
            const mf = document.getElementById('manualFormPanel');
            if (mf) mf.style.display = 'block';
        }

        function showGateway() {
            window.location.href = "{{ route('resident.login') }}";
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

        function isStep1Complete() {
            const lastName = document.querySelector('input[name="last_name"]')?.value.trim() || '';
            const firstName = document.querySelector('input[name="first_name"]')?.value.trim() || '';
            const birthdate = document.querySelector('input[name="birthdate"]')?.value.trim() || '';
            const ageVal = document.querySelector('input[name="age"]')?.value.trim() || '';
            const birthplace = document.querySelector('input[name="birthplace"]')?.value.trim() || '';
            const gender = document.querySelector('select[name="gender"]')?.value.trim() || '';
            const civilStatus = document.querySelector('select[name="civil_status"]')?.value.trim() || '';
            const nationality = document.querySelector('input[name="nationality"]')?.value.trim() || '';
            const occupation = document.querySelector('select[name="occupation"]')?.value.trim() || '';
            const purok = document.querySelector('input[name="purok"]')?.value.trim() || '';

            const ageNum = parseInt(ageVal, 10);
            const isAgeValid = !isNaN(ageNum) && ageNum >= 0 && ageNum <= 150;

            return Boolean(
                lastName &&
                firstName &&
                birthdate &&
                isAgeValid &&
                birthplace &&
                gender &&
                civilStatus &&
                nationality &&
                occupation &&
                purok
            );
        }

        function isStep2Complete() {
            @if($viaGoogle)
                return true;
            @else
                const phone = document.querySelector('input[name="phone_number"]')?.value.trim() || '';
                const cleanPhone = phone.replace(/[^0-9]/g, '');
                const email = document.querySelector('input[name="email"]')?.value.trim() || '';

                const hasValidPhone = cleanPhone.length >= 10 && cleanPhone.length <= 11;
                const hasValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                if (!hasValidPhone && !hasValidEmail) {
                    return false;
                }

                const pwd = document.getElementById('regPassword')?.value || '';
                const confirm = document.getElementById('regPasswordConfirm')?.value || '';

                return Boolean(pwd.length >= 8 && confirm.length >= 8 && pwd === confirm);
            @endif
        }

        function updateNextButtonsVisibility() {
            // Step 1 Next Button
            const btnNext1 = document.getElementById('btnNextStep1');
            if (btnNext1) {
                const isReady = isStep1Complete();
                if (isReady) {
                    if (btnNext1.style.display === 'none' || !btnNext1.classList.contains('is-revealed')) {
                        btnNext1.style.display = 'inline-flex';
                        requestAnimationFrame(() => {
                            btnNext1.classList.add('is-revealed');
                        });
                    }
                } else {
                    btnNext1.classList.remove('is-revealed');
                    btnNext1.style.display = 'none';
                }
            }

            // Step 2 Next Button
            const btnNext2 = document.getElementById('btnNextStep2');
            if (btnNext2) {
                const isReady = isStep2Complete();
                if (isReady) {
                    if (btnNext2.style.display === 'none' || !btnNext2.classList.contains('is-revealed')) {
                        btnNext2.style.display = 'inline-flex';
                        requestAnimationFrame(() => {
                            btnNext2.classList.add('is-revealed');
                        });
                    }
                } else {
                    btnNext2.classList.remove('is-revealed');
                    btnNext2.style.display = 'none';
                }
            }
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
            updateNextButtonsVisibility();

            // Scroll step scroll containers and right panel back to top cleanly
            document.querySelectorAll('.step-scroll').forEach(s => s.scrollTop = 0);
            document.querySelector('.right-panel').scrollTo({ top: 0, behavior: 'smooth' });
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

                if (data.demo_code) {
                    showOtpAlert(data.message || `Render free tier blocks SMTP. Your verification code is: ${data.demo_code}`, 'warning');
                    const digits = String(data.demo_code).split('');
                    digits.forEach((digit, idx) => {
                        const box = document.querySelector(`.code-box[data-index="${idx}"]`);
                        if (box) box.value = digit;
                    });
                } else {
                    hideOtpAlert();
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

        function showOtpAlert(msg, type = 'danger') {
            const el = document.getElementById('otpAlert');
            if (el) {
                el.textContent = msg;
                el.className = `alert alert-${type} border-0 small rounded-3 py-2 mb-3 text-start`;
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
                cleanupModalBackdrops();
            }, 400);
        }

        function cleanupModalBackdrops() {
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

                // Privacy check
                const privacy = document.getElementById('agreePrivacy');
                if (!privacy || !privacy.checked) {
                    if (privacy) privacy.classList.add('is-invalid');
                    showAlert('Please read and agree to the Data Privacy Act notice.');
                    return false;
                }

                // Terms check
                const terms = document.getElementById('agreeTerms');
                if (!terms || !terms.checked) {
                    if (terms) terms.classList.add('is-invalid');
                    showAlert('Please agree to the Terms and Conditions.');
                    return false;
                }

                // Stop camera when valid
                stopFaceCamera();

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

        function checkPasswordStrength(password) {
            const bars = ['bar1', 'bar2', 'bar3', 'bar4'];
            const label = document.getElementById('strengthLabel');
            let score = 0;

            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score = Math.min(score + 1, 4);

            const levels = [
                { color: '#e5e7eb', text: 'Minimum 8 characters', filled: 0 },
                { color: '#ef4444', text: '\u26a0\ufe0f Weak — try adding numbers or symbols', filled: 1 },
                { color: '#f97316', text: '\ud83d\udfe1 Fair — add uppercase or special characters', filled: 2 },
                { color: '#22c55e', text: '\u2705 Good password', filled: 3 },
                { color: '#16a34a', text: '\ud83d\udee1\ufe0f Strong password', filled: 4 },
            ];

            const level = password.length === 0 ? levels[0] : levels[Math.max(1, Math.min(score, 4))];

            bars.forEach((id, i) => {
                document.getElementById(id).style.background = i < level.filled ? level.color : '#e5e7eb';
            });
            label.textContent = level.text;
            label.style.color = password.length === 0 ? '#9ca3af' : level.color;
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
        function updateAgeFromBirthdate() {
            const birthdateInput = document.getElementById('regBirthdate');
            const ageInput = document.getElementById('regAge');
            if (!birthdateInput || !birthdateInput.value) {
                if (ageInput) ageInput.value = '';
                updateNextButtonsVisibility();
                return;
            }
            const dob = new Date(birthdateInput.value);
            if (!isNaN(dob.getTime())) {
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                if (age >= 0 && age <= 150) {
                    if (ageInput) {
                        ageInput.value = age;
                        ageInput.classList.remove('field-error');
                    }
                } else {
                    if (ageInput) ageInput.value = '';
                }
            } else {
                if (ageInput) ageInput.value = '';
            }
            updateNextButtonsVisibility();
        }

        document.getElementById('regBirthdate')?.addEventListener('change', updateAgeFromBirthdate);
        document.getElementById('regBirthdate')?.addEventListener('input', updateAgeFromBirthdate);

        // ============================================
        // LIVE FACE VERIFICATION / WEBCAM MODAL SYSTEM
        // ============================================
        let faceMediaStream = null;

        async function startFaceCamera() {
            try {
                const videoEl = document.getElementById('faceVideoElement');
                const fallbackBox = document.getElementById('selfieUploadFallbackBox');

                if (fallbackBox) fallbackBox.classList.add('d-none');

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

                const modalEl = document.getElementById('faceCameraModal');
                if (!faceCameraModalInstance) {
                    faceCameraModalInstance = new bootstrap.Modal(modalEl, {
                        backdrop: 'static',
                        keyboard: false
                    });
                }
                faceCameraModalInstance.show();

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

            if (faceCameraModalInstance) {
                faceCameraModalInstance.hide();
            } else {
                const modalEl = document.getElementById('faceCameraModal');
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) instance.hide();
            }

            const promptState = document.getElementById('facePromptState');
            const capturedState = document.getElementById('faceCapturedState');

            // Only show initial prompt state if NO photo is currently captured
            if (promptState && (!capturedState || capturedState.classList.contains('d-none'))) {
                promptState.classList.remove('d-none');
            }

            setTimeout(() => {
                cleanupModalBackdrops();
            }, 400);
        }

        function captureFaceSnapshot() {
            const videoEl = document.getElementById('faceVideoElement');
            const canvas = document.getElementById('faceSnapshotCanvas');
            const previewImg = document.getElementById('faceCapturedPreview');
            const hiddenInput = document.getElementById('selfiePhotoInput');
            const capturedState = document.getElementById('faceCapturedState');
            const promptState = document.getElementById('facePromptState');
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
                // Hide prompt box completely and show captured photo preview
                if (promptState) promptState.classList.add('d-none');
                if (capturedState) capturedState.classList.remove('d-none');
                stopFaceCamera();
            }, 200);
        }

        function retakeFacePhoto() {
            const hiddenInput = document.getElementById('selfiePhotoInput');
            const capturedState = document.getElementById('faceCapturedState');
            const promptState = document.getElementById('facePromptState');

            if (hiddenInput) hiddenInput.value = '';
            if (capturedState) capturedState.classList.add('d-none');
            if (promptState) promptState.classList.remove('d-none');

            startFaceCamera();
        }

        function toggleSelfieUploadFallback() {
            stopFaceCamera();
            document.getElementById('facePromptState')?.classList.add('d-none');
            document.getElementById('selfieUploadFallbackBox')?.classList.remove('d-none');
        }

        function useFaceCameraInstead() {
            document.getElementById('selfieUploadFallbackBox')?.classList.add('d-none');
            document.getElementById('facePromptState')?.classList.remove('d-none');
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
            if (!validateStep(3)) {
                e.preventDefault();
                return false;
            }
            const btn = document.getElementById('btnSubmitRegister');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting...';
            }
        });

        // Dynamic event listeners for input fields to show/hide Next buttons
        document.addEventListener('input', function(e) {
            if (e.target && e.target.closest('#panel-step-1, #panel-step-2')) {
                updateNextButtonsVisibility();
            }
        });
        document.addEventListener('change', function(e) {
            if (e.target && e.target.closest('#panel-step-1, #panel-step-2')) {
                updateNextButtonsVisibility();
            }
        });

        // Initial evaluation on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', updateNextButtonsVisibility);
        } else {
            updateNextButtonsVisibility();
        }
        window.addEventListener('load', updateNextButtonsVisibility);
    </script>
</body>
</html>