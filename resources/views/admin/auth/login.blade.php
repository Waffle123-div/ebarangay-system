<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Portal Login | E-Barangay Government Cloud</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">

    <style>
        :root {
            --font-main: 'Instrument Sans', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --primary-navy: #0F2240;
            --primary-blue: #18365D;
            --accent-royal: #2563EB;
            --accent-sky: #38BDF8;
            --accent-teal: #0D9488;
            --accent-emerald: #10B981;
            
            /* Light theme variables */
            --bg-page: #f1f5f9;
            --card-bg: rgba(255, 255, 255, 0.92);
            --card-border: rgba(203, 213, 225, 0.8);
            --card-shadow: 0 25px 60px -15px rgba(15, 34, 64, 0.16), 0 0 1px rgba(15, 34, 64, 0.2);
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --input-bg: #f8fafc;
            --input-border: #cbd5e1;
            --input-text: #0f172a;
            --input-focus-border: #2563eb;
            --input-focus-shadow: rgba(37, 99, 235, 0.18);
            --grid-color: rgba(15, 34, 64, 0.04);
            --badge-bg: rgba(37, 99, 235, 0.08);
            --badge-border: rgba(37, 99, 235, 0.22);
            --badge-text: #1d4ed8;
            --footer-border: #e2e8f0;
        }

        html[data-theme="dark"],
        html[data-bs-theme="dark"] {
            /* Dark theme variables */
            --bg-page: #0b1120;
            --card-bg: rgba(19, 29, 49, 0.88);
            --card-border: rgba(51, 65, 85, 0.8);
            --card-shadow: 0 30px 70px -10px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.06);
            --text-heading: #f8fafc;
            --text-body: #cbd5e1;
            --text-muted: #94a3b8;
            --input-bg: #182238;
            --input-border: #334155;
            --input-text: #f8fafc;
            --input-focus-border: #38bdf8;
            --input-focus-shadow: rgba(56, 189, 248, 0.25);
            --grid-color: rgba(255, 255, 255, 0.03);
            --badge-bg: rgba(56, 189, 248, 0.12);
            --badge-border: rgba(56, 189, 248, 0.35);
            --badge-text: #38bdf8;
            --footer-border: #1e293b;
        }

        * {
            box-sizing: border-box;
            transition: background-color 0.25s ease, border-color 0.25s ease, color 0.2s ease;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: var(--font-main);
            background-color: var(--bg-page);
            color: var(--text-body);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Ambient Animated Grid */
        .ambient-grid {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(to right, var(--grid-color) 1px, transparent 1px),
                linear-gradient(to bottom, var(--grid-color) 1px, transparent 1px);
            background-size: 38px 38px;
            pointer-events: none;
            z-index: 1;
            mask-image: radial-gradient(ellipse at center, rgba(0,0,0,1) 40%, rgba(0,0,0,0) 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, rgba(0,0,0,1) 40%, rgba(0,0,0,0) 80%);
        }

        /* Floating Gradient Aurora Orbs */
        .aurora-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .aurora-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.65;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: transform;
        }

        .orb-1 {
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.45) 0%, rgba(24, 54, 93, 0.15) 70%, transparent 100%);
            top: -150px;
            left: -100px;
            animation: floatOrb1 18s ease-in-out infinite alternate;
        }

        .orb-2 {
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.38) 0%, rgba(13, 148, 136, 0.15) 70%, transparent 100%);
            bottom: -120px;
            right: -100px;
            animation: floatOrb2 22s ease-in-out infinite alternate;
        }

        .orb-3 {
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, rgba(15, 34, 64, 0.1) 70%, transparent 100%);
            top: 40%;
            left: 55%;
            animation: floatOrb3 20s ease-in-out infinite alternate;
        }

        @keyframes floatOrb1 {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(120px, 80px) scale(1.12); }
            100% { transform: translate(-40px, 140px) scale(0.95); }
        }

        @keyframes floatOrb2 {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(-100px, -60px) scale(1.08); }
            100% { transform: translate(50px, -120px) scale(0.92); }
        }

        @keyframes floatOrb3 {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(-80px, 70px) scale(1.15); }
            100% { transform: translate(90px, -50px) scale(0.9); }
        }

        /* Top Navigation Header */
        .auth-top-header {
            position: relative;
            z-index: 10;
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            backdrop-filter: blur(12px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .back-home-btn:hover {
            color: var(--text-heading);
            border-color: var(--accent-royal);
            transform: translateX(-3px);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.15);
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .clock-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(12px);
        }

        .clock-badge i {
            color: var(--accent-sky);
        }

        .theme-toggle-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-heading);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(12px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .theme-toggle-btn:hover {
            transform: scale(1.08) rotate(15deg);
            border-color: var(--accent-royal);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2);
        }

        /* Main Container */
        .auth-main-wrapper {
            position: relative;
            z-index: 5;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem 3rem;
        }

        /* Glassmorphism Login Card */
        .login-glass-card {
            width: 100%;
            max-width: 460px;
            background: var(--card-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            box-shadow: var(--card-shadow);
            padding: 2.75rem 2.5rem;
            position: relative;
            overflow: hidden;
            animation: cardEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transform-origin: center top;
        }

        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Card Top Shimmer Line */
        .login-glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #18365D 0%, #2563EB 35%, #38BDF8 65%, #0D9488 100%);
        }

        /* Top Security Pill Badge */
        .security-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            background: var(--badge-bg);
            border: 1px solid var(--badge-border);
            color: var(--badge-text);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .status-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent-emerald);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulseGlow 2s infinite;
        }

        @keyframes pulseGlow {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* Animated Government Seal Icon */
        .emblem-wrapper {
            position: relative;
            width: 86px;
            height: 86px;
            margin: 0 auto 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emblem-outer-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px dashed rgba(37, 99, 235, 0.35);
            animation: rotateEmblem 30s linear infinite;
        }

        @keyframes rotateEmblem {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        .emblem-inner {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: linear-gradient(135deg, #0F2240 0%, #18365D 50%, #2563EB 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.35);
            color: #ffffff;
            font-size: 2rem;
            position: relative;
            z-index: 2;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .emblem-wrapper:hover .emblem-inner {
            transform: scale(1.06) translateY(-2px);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.45);
        }

        /* Typography */
        .login-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.5px;
            margin-bottom: 0.35rem;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1.75rem;
        }

        /* Error Alert Banner */
        .custom-error-alert {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #ef4444;
            border-radius: 14px;
            padding: 0.85rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            animation: shakeAlert 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        @keyframes shakeAlert {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-3px, 0, 0); }
            40%, 60% { transform: translate3d(3px, 0, 0); }
        }

        /* Modern Input Group */
        .form-field-group {
            margin-bottom: 1.35rem;
            text-align: start;
        }

        .field-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.45rem;
        }

        .input-control-box {
            position: relative;
            display: flex;
            align-items: center;
            border-radius: 14px;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .input-control-box:focus-within {
            border-color: var(--input-focus-border);
            background: var(--card-bg);
            box-shadow: 0 0 0 4px var(--input-focus-shadow);
        }

        .input-icon-lead {
            width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 1rem;
            flex-shrink: 0;
            transition: color 0.2s ease;
        }

        .input-control-box:focus-within .input-icon-lead {
            color: var(--accent-royal);
        }

        .custom-form-input {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            padding: 0.8rem 0.5rem 0.8rem 0;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--input-text);
            font-family: inherit;
        }

        .custom-form-input::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }

        .password-toggle-btn {
            background: transparent;
            border: none;
            padding: 0 1rem;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease, transform 0.15s ease;
        }

        .password-toggle-btn:hover {
            color: var(--text-heading);
            transform: scale(1.1);
        }

        /* Caps Lock Warning Pill */
        .caps-warning-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: #f59e0b;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 9999px;
            padding: 0.2rem 0.65rem;
            margin-top: 0.4rem;
            animation: fadeIn 0.2s ease;
        }

        /* Remember & Security Row */
        .form-options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .custom-checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        .custom-checkbox-input {
            width: 17px;
            height: 17px;
            border-radius: 5px;
            cursor: pointer;
            accent-color: var(--accent-royal);
        }

        .security-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .security-indicator i {
            color: var(--accent-emerald);
            font-size: 0.75rem;
        }

        /* Action Button */
        .btn-admin-submit {
            width: 100%;
            padding: 0.95rem 1.5rem;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, #0F2240 0%, #18365D 45%, #2563EB 100%);
            color: #ffffff;
            font-size: 0.98rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4), 0 4px 10px -2px rgba(15, 34, 64, 0.3);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-admin-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.22), transparent);
            transform: skewX(-20deg);
            transition: left 0.75s ease;
        }

        .btn-admin-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px -5px rgba(37, 99, 235, 0.55), 0 6px 14px -2px rgba(15, 34, 64, 0.35);
            color: #ffffff;
        }

        .btn-admin-submit:hover::before {
            left: 140%;
        }

        .btn-admin-submit:active {
            transform: translateY(0);
        }

        .btn-admin-submit i.fa-arrow-right {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-admin-submit:hover i.fa-arrow-right {
            transform: translateX(4px);
        }

        .btn-admin-submit:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Card Bottom Metadata */
        .card-meta-footer {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--footer-border);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }

        .meta-badges-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .meta-pill i {
            font-size: 0.7rem;
            color: var(--accent-sky);
        }

        .copyright-text {
            font-size: 0.75rem;
            color: var(--text-muted);
            opacity: 0.8;
            margin: 0;
        }

        /* Subtle responsive tweaks */
        @media (max-width: 576px) {
            .auth-top-header {
                padding: 1rem 1.25rem;
            }
            .clock-badge {
                display: none;
            }
            .login-glass-card {
                padding: 2rem 1.5rem;
                border-radius: 22px;
            }
            .login-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Grid Background -->
    <div class="ambient-grid"></div>

    <!-- Floating Aurora Gradient Orbs -->
    <div class="aurora-container" id="auroraContainer">
        <div class="aurora-orb orb-1" id="orb1"></div>
        <div class="aurora-orb orb-2" id="orb2"></div>
        <div class="aurora-orb orb-3" id="orb3"></div>
    </div>

    <!-- Top Utility Header -->
    <header class="auth-top-header">
        <a href="{{ route('welcome') }}" class="back-home-btn" title="Return to Resident Portal">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Resident Portal</span>
        </a>

        <div class="header-controls">
            <div class="clock-badge" id="livePstClock">
                <i class="fa-regular fa-clock"></i>
                <span id="pstTimeDisplay">Philippine Standard Time</span>
            </div>

            <button type="button" class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle Theme" title="Toggle Light / Dark Mode">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="auth-main-wrapper">
        <div class="login-glass-card text-center" id="loginCard">
            
            <!-- Top Live Status Badge -->
            <div class="d-flex justify-content-center">
                <div class="security-status-badge">
                    <span class="status-pulse-dot"></span>
                    <span>Restricted Access &bull; LGU Admin Portal</span>
                </div>
            </div>

            <!-- Animated Government Shield Emblem -->
            <div class="emblem-wrapper">
                <div class="emblem-outer-ring"></div>
                <div class="emblem-inner">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>

            <!-- Headings -->
            <h1 class="login-title">Admin Authentication</h1>
            <p class="login-subtitle">Sign in to the official municipal management console</p>

            <!-- Error Notification Alert -->
            @if($errors->any())
                <div class="custom-error-alert text-start" role="alert">
                    <i class="fa-solid fa-circle-exclamation fs-5 flex-shrink-0"></i>
                    <div>
                        <div class="fw-bold">Authentication Failed</div>
                        <div class="small opacity-90">{{ $errors->first() }}</div>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('admin.login.submit') }}" method="POST" id="adminLoginForm">
                @csrf

                <!-- Email Input -->
                <div class="form-field-group">
                    <label for="adminEmail" class="field-label">Administrator Email</label>
                    <div class="input-control-box">
                        <span class="input-icon-lead">
                            <i class="fa-solid fa-user-shield"></i>
                        </span>
                        <input type="email" 
                               name="email" 
                               id="adminEmail" 
                               class="custom-form-input" 
                               placeholder="admin@barangay.gov.ph" 
                               value="{{ old('email') }}"
                               required 
                               autofocus 
                               autocomplete="email">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-field-group">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="adminPassword" class="field-label mb-0">Security Password</label>
                    </div>
                    <div class="input-control-box">
                        <span class="input-icon-lead">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" 
                               name="password" 
                               id="adminPassword" 
                               class="custom-form-input" 
                               placeholder="••••••••••••" 
                               required 
                               autocomplete="current-password">
                        <button type="button" 
                                class="password-toggle-btn" 
                                id="passwordToggleBtn" 
                                aria-label="Toggle password visibility" 
                                title="Show/Hide password">
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    
                    <!-- Caps Lock Alert -->
                    <div id="capsLockWarning" class="caps-warning-pill d-none">
                        <i class="fa-solid fa-arrow-up-long"></i> Caps Lock is ON
                    </div>
                </div>

                <!-- Remember Me & Security Indicator -->
                <div class="form-options-row">
                    <label class="custom-checkbox-label">
                        <input type="checkbox" name="remember" class="custom-checkbox-input" id="rememberMe" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember session</span>
                    </label>

                    <div class="security-indicator" title="Encrypted Connection">
                        <i class="fa-solid fa-lock"></i>
                        <span>256-bit SSL</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-admin-submit" id="submitBtn">
                    <span id="btnText">Authorize &amp; Enter Dashboard</span>
                    <i class="fa-solid fa-arrow-right" id="btnIcon"></i>
                </button>
            </form>

            <!-- Card Meta Footer -->
            <div class="card-meta-footer">
                <div class="meta-badges-row">
                    <span class="meta-pill">
                        <i class="fa-solid fa-shield-check"></i> Encrypted
                    </span>
                    <span class="meta-pill">&bull;</span>
                    <span class="meta-pill">
                        <i class="fa-solid fa-bolt"></i> Realtime Sync
                    </span>
                    <span class="meta-pill">&bull;</span>
                    <span class="meta-pill">
                        <i class="fa-solid fa-fingerprint"></i> Audit Trail
                    </span>
                </div>
                <p class="copyright-text">
                    &copy; {{ date('Y') }} E-Barangay Digital Services System &bull; All Rights Reserved
                </p>
            </div>

        </div>
    </main>

    <!-- Interactive Scripts -->
    <script>
        (function() {
            // ==========================================
            // 1. Dark / Light Theme System
            // ==========================================
            const html = document.documentElement;
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');

            function applyTheme(isDark) {
                if (isDark) {
                    html.setAttribute('data-theme', 'dark');
                    html.setAttribute('data-bs-theme', 'dark');
                    themeIcon.classList.replace('fa-moon', 'fa-sun');
                    themeToggleBtn.setAttribute('title', 'Switch to Light Mode');
                } else {
                    html.removeAttribute('data-theme');
                    html.removeAttribute('data-bs-theme');
                    themeIcon.classList.replace('fa-sun', 'fa-moon');
                    themeToggleBtn.setAttribute('title', 'Switch to Dark Mode');
                }
            }

            // Read persisted theme preference
            const savedTheme = localStorage.getItem('ebarangay_theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDarkInitial = savedTheme ? (savedTheme === 'dark') : prefersDark;
            applyTheme(isDarkInitial);

            themeToggleBtn.addEventListener('click', function() {
                const isCurrentlyDark = html.getAttribute('data-theme') === 'dark';
                const nextDark = !isCurrentlyDark;
                applyTheme(nextDark);
                localStorage.setItem('ebarangay_theme', nextDark ? 'dark' : 'light');
            });

            // ==========================================
            // 2. Interactive Password Visibility Toggle
            // ==========================================
            const passwordInput = document.getElementById('adminPassword');
            const passwordToggleBtn = document.getElementById('passwordToggleBtn');
            const toggleIcon = document.getElementById('toggleIcon');

            passwordToggleBtn.addEventListener('click', function() {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                if (isPassword) {
                    passwordInput.setAttribute('type', 'text');
                    toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
                    passwordToggleBtn.setAttribute('title', 'Hide password');
                } else {
                    passwordInput.setAttribute('type', 'password');
                    toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
                    passwordToggleBtn.setAttribute('title', 'Show password');
                }
                passwordInput.focus();
            });

            // ==========================================
            // 3. Caps Lock Detection Warning
            // ==========================================
            const capsLockWarning = document.getElementById('capsLockWarning');

            passwordInput.addEventListener('keyup', function(e) {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    capsLockWarning.classList.remove('d-none');
                } else {
                    capsLockWarning.classList.add('d-none');
                }
            });

            passwordInput.addEventListener('keydown', function(e) {
                if (e.getModifierState && e.getModifierState('CapsLock')) {
                    capsLockWarning.classList.remove('d-none');
                } else {
                    capsLockWarning.classList.add('d-none');
                }
            });

            // ==========================================
            // 4. Live Philippine Standard Time (PST) Clock
            // ==========================================
            const pstTimeDisplay = document.getElementById('pstTimeDisplay');

            function updatePstClock() {
                try {
                    const now = new Date();
                    // Format in Philippine Standard Time
                    const options = {
                        timeZone: 'Asia/Manila',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    };
                    const dateOptions = {
                        timeZone: 'Asia/Manila',
                        month: 'short',
                        day: 'numeric'
                    };
                    const timeStr = new Intl.DateTimeFormat('en-US', options).format(now);
                    const dateStr = new Intl.DateTimeFormat('en-US', dateOptions).format(now);
                    pstTimeDisplay.textContent = `${dateStr} • ${timeStr} PHT`;
                } catch (e) {
                    pstTimeDisplay.textContent = new Date().toLocaleTimeString();
                }
            }

            updatePstClock();
            setInterval(updatePstClock, 1000);

            // ==========================================
            // 5. Interactive Form Submission & Loading State
            // ==========================================
            const adminLoginForm = document.getElementById('adminLoginForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');

            adminLoginForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                btnText.textContent = 'Verifying Credentials...';
                btnIcon.className = 'fa-solid fa-circle-notch fa-spin';
            });

            // ==========================================
            // 6. Interactive 3D Cursor Parallax for Aurora Orbs
            // ==========================================
            const orb1 = document.getElementById('orb1');
            const orb2 = document.getElementById('orb2');
            const orb3 = document.getElementById('orb3');
            const loginCard = document.getElementById('loginCard');

            let mouseX = 0;
            let mouseY = 0;
            let currentX = 0;
            let currentY = 0;

            window.addEventListener('mousemove', function(e) {
                mouseX = (e.clientX / window.innerWidth) - 0.5;
                mouseY = (e.clientY / window.innerHeight) - 0.5;
            });

            function renderParallax() {
                currentX += (mouseX - currentX) * 0.05;
                currentY += (mouseY - currentY) * 0.05;

                if (orb1 && orb2 && orb3) {
                    orb1.style.transform = `translate(${currentX * 45}px, ${currentY * 45}px)`;
                    orb2.style.transform = `translate(${currentX * -60}px, ${currentY * -60}px)`;
                    orb3.style.transform = `translate(${currentX * 35}px, ${currentY * -35}px)`;
                }

                requestAnimationFrame(renderParallax);
            }

            requestAnimationFrame(renderParallax);

        })();
    </script>
</body>
</html>
