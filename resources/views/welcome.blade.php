<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="E-Barangay: Web-Based Document Request and Tracking System for Barangay Cogon East, Carmen, Cebu. Request barangay documents online, track your requests in real-time, and access community services.">
  <title>E-Barangay — Document Request & Tracking System | Barangay Cogon East, Carmen, Cebu</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Icons (Lucide via CDN; Font Awesome deferred to keep initial paint fast) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">
  <link rel="preload" as="font" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2" type="font/woff2" crossorigin>
  <link rel="preload" as="font" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-regular-400.woff2" type="font/woff2" crossorigin>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" media="print" onload="this.media='all'">

  <!-- Styles (Updated for Laravel) -->
  <link id="mainStylesheet" rel="stylesheet" href="{{ \App\Providers\AppServiceProvider::versionedAsset('css/style.css') }}" onerror="if(!this.dataset.retried){this.dataset.retried='1';this.href='{{ asset('css/style.css') }}?retry='+Date.now();}">
  <script>
    window.addEventListener('load', function () {
      var token = getComputedStyle(document.documentElement).getPropertyValue('--radius-full').trim();
      var link = document.getElementById('mainStylesheet');
      if (!token && link && !link.dataset.retried) {
        link.dataset.retried = '1';
        link.href = '{{ asset('css/style.css') }}?retry=' + Date.now();
      }
    });
  </script>

  @if(config('services.recaptcha.site_key'))
  <script>
    window.welcomeRecaptchaReady = false;
    window.onWelcomeRecaptchaLoad = function () {
      window.welcomeRecaptchaReady = true;
      if (typeof window.renderWelcomeRecaptcha === 'function') {
        window.renderWelcomeRecaptcha();
      }
    };
  </script>
  <script src="https://www.google.com/recaptcha/api.js?onload=onWelcomeRecaptchaLoad&render=explicit" async defer></script>
  @endif
</head>
@php
  $welcomeUser = auth()->user();
  $welcomeIsResident = $welcomeUser && trim(strtolower($welcomeUser->role)) !== 'admin';
@endphp
<body
  data-is-resident="{{ $welcomeIsResident ? '1' : '0' }}"
  data-login-url="{{ route('resident.login') }}"
  data-request-url="{{ route('resident.request.create') }}"
>

  <!-- Animated Background Blobs -->
  <div class="bg-blobs" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
  </div>

  <!-- ============================
       HEADER / NAVIGATION
       ============================ -->
  <header class="header" id="header">
    <div class="container">
      <a href="#" class="logo" id="logo">
        <div class="logo-icon">
          <!-- Image path updated for Laravel -->
          <img src="{{ asset('images/logo.png') }}" alt="E-Barangay Logo" width="40" height="40" decoding="async">
        </div>
        <div class="logo-text">
          E-Barangay
          <span>Cogon East • Carmen, Cebu</span>
        </div>
      </a>

      @php
        $navUser = auth()->user();
        $isLoggedInResident = $navUser && trim(strtolower($navUser->role)) !== 'admin';
        $navInitials = collect(explode(' ', trim($navUser?->name ?? '')))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
      @endphp

      <nav class="nav-links" id="navLinks">
        <div class="mobile-nav-items">
          <a href="#hero" class="active"><i class="icon-home mobile-nav-icon"></i> Home</a>
          <a href="#how-it-works"><i class="icon-help-circle mobile-nav-icon"></i> How It Works</a>
          <a href="#services"><i class="icon-file-text mobile-nav-icon"></i> Documents</a>
          <a href="#emergency"><i class="icon-alert-triangle mobile-nav-icon"></i> Emergency</a>
        </div>

        <div class="mobile-nav-cta">
          @if($isLoggedInResident)
            <a href="{{ route('resident.dashboard') }}" class="btn btn-primary">
              <i class="icon-home"></i> My Resident Dashboard
            </a>
            <form method="POST" action="{{ route('resident.logout') }}" style="width: 100%;">
              @csrf
              <button type="submit" class="btn btn-outline">
                <i class="icon-log-out"></i> Logout
              </button>
            </form>
          @else
            <a href="{{ route('resident.login') }}" class="btn btn-outline" id="mobileLoginBtn">
              <i class="icon-log-in"></i> Log In
            </a>
            <a href="{{ route('resident.register') }}" class="btn btn-primary" id="mobileSignupBtn">
              <i class="icon-user-plus"></i> Sign Up
            </a>
          @endif
        </div>
      </nav>

      <div class="nav-cta">

        @if($isLoggedInResident)
          <div class="welcome-user-nav">
            <div class="welcome-notif-wrap" id="welcomeNotifWrap">
              <button type="button" class="welcome-icon-btn" id="welcomeNotifBtn" aria-label="Notifications" title="Notifications">
                <i class="icon-bell"></i>
                @if($navUser->unreadNotifications->count() > 0)
                  <span class="welcome-badge" id="welcomeNotifBadge">{{ $navUser->unreadNotifications->count() }}</span>
                @endif
              </button>
              <div class="welcome-dropdown welcome-notif-dropdown" id="welcomeNotifDropdown">
                <div class="welcome-dropdown-header">
                  <span>Notifications</span>
                </div>
                <div class="welcome-dropdown-body">
                  @forelse($navUser->notifications->take(8) as $notification)
                    @php
                      $notifType = $notification->data['type'] ?? 'document';
                      $notifTitle = $notifType === 'concern_update'
                          ? ($notification->data['title'] ?? 'Community Concern Update')
                          : ($notification->data['document_type'] ?? 'Document Update');
                      $notifMeta = $notifType === 'concern_update'
                          ? ('ID: ' . ($notification->data['concern_id'] ?? 'N/A'))
                          : ('TRK: ' . ($notification->data['tracking_number'] ?? 'N/A'));
                    @endphp
                    <div class="welcome-notif-item {{ $notification->read_at === null ? 'unread' : '' }}">
                      <div class="welcome-notif-title">{{ $notifTitle }}</div>
                      <div class="welcome-notif-message">{{ $notification->data['message'] ?? 'You have a new update.' }}</div>
                      <div class="welcome-notif-meta">{{ $notifMeta }} • {{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                  @empty
                    <div class="welcome-dropdown-empty">
                      <i class="icon-bell-off"></i>
                      <span>No new notifications</span>
                    </div>
                  @endforelse
                </div>
              </div>
            </div>

            <div class="welcome-profile-wrap" id="welcomeProfileWrap">
              <button type="button" class="welcome-profile-btn" id="welcomeProfileBtn" aria-label="Open profile menu">
                @if($navUser->profile_picture)
                  <img src="{{ $navUser->profile_picture_url }}" alt="{{ $navUser->name }}" class="welcome-profile-avatar" width="34" height="34" decoding="async">
                @else
                  <span class="welcome-profile-avatar welcome-profile-avatar-fallback">{{ $navInitials ?: 'R' }}</span>
                @endif
                <span class="welcome-profile-name">{{ $navUser->name }}</span>
                <i class="icon-chevron-down welcome-profile-chevron"></i>
              </button>
              <div class="welcome-dropdown welcome-profile-dropdown" id="welcomeProfileDropdown">
                <div class="welcome-profile-card">
                  @if($navUser->profile_picture)
                    <img src="{{ $navUser->profile_picture_url }}" alt="{{ $navUser->name }}" class="welcome-profile-card-avatar" width="48" height="48" decoding="async">
                  @else
                    <span class="welcome-profile-card-avatar welcome-profile-avatar-fallback">{{ $navInitials ?: 'R' }}</span>
                  @endif
                  <div>
                    <div class="welcome-profile-card-name">{{ $navUser->name }}</div>
                    <div class="welcome-profile-card-email">{{ $navUser->email ?? 'No email provided' }}</div>
                  </div>
                </div>
                <div class="welcome-dropdown-menu">
                  <a href="{{ route('resident.settings') }}" class="welcome-dropdown-link">
                    <i class="icon-user"></i> My Profile
                  </a>
                  <a href="{{ route('resident.dashboard') }}" class="welcome-dropdown-link">
                    <i class="icon-home"></i> My Dashboard
                  </a>
                  <form method="POST" action="{{ route('resident.logout') }}" class="welcome-logout-form">
                    @csrf
                    <button type="submit" class="welcome-dropdown-link welcome-dropdown-logout">
                      <i class="icon-log-out"></i> Logout
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        @else
          <a href="{{ route('resident.login') }}" class="btn btn-outline-nav" id="loginBtn">
            <i class="icon-log-in"></i>
            Log In
          </a>
          <a href="{{ route('resident.register') }}" class="btn btn-primary" id="signupBtn">
            <i class="icon-user-plus"></i>
            Sign Up
          </a>
        @endif

        <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
          <i class="icon-moon dark-icon-moon"></i>
          <i class="icon-sun dark-icon-sun"></i>
        </button>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>
  </header>

  <!-- ============================
       HERO SECTION
       ============================ -->
  <section class="hero" id="hero">
    <div class="hero-bg" id="heroBg">
      <picture>
        <source srcset="{{ asset('images/hero.webp') }}" type="image/webp">
        <img src="{{ asset('images/hero.png') }}" alt="Barangay Cogon East, Carmen, Cebu community" loading="eager" fetchpriority="high">
      </picture>
      <div class="hero-bg-vignette"></div>

      <!-- Interactive Cursor-Follow Spotlight -->
      <div class="hero-interactive-spotlight" id="heroSpotlight"></div>

      <!-- Rotating Holographic Accent Rings -->
      <div class="hero-bg-ring hero-bg-ring-1"></div>
      <div class="hero-bg-ring hero-bg-ring-2"></div>

      <!-- Ambient Morphing Aurora Orbs -->
      <div class="hero-ambient-orb orb-gold"></div>
      <div class="hero-ambient-orb orb-blue"></div>
      <div class="hero-ambient-orb orb-cyan"></div>
      <div class="hero-ambient-orb orb-indigo"></div>

      <!-- Animated Grid Pattern & Light Beam Sweep -->
      <div class="hero-grid-pattern"></div>
      <div class="hero-grid-beam"></div>

      <!-- Floating Sparkles & Light Particles -->
      <div class="hero-particles" aria-hidden="true">
        <span class="particle p-1"></span>
        <span class="particle p-2"></span>
        <span class="particle p-3"></span>
        <span class="particle p-4"></span>
        <span class="particle p-5"></span>
        <span class="particle p-6"></span>
        <span class="particle p-7"></span>
        <span class="particle p-8"></span>
        <span class="particle p-9"></span>
        <span class="particle p-10"></span>
        <span class="particle p-11"></span>
        <span class="particle p-12"></span>
      </div>
    </div>

    <div class="container hero-container">
      <div class="hero-content">
        
        <!-- Live Verified Badge -->
        <div class="hero-badge">
          <span class="hero-pulse-dot"></span>
          <span>Official LGU Digital Portal &bull; Cogon East, Carmen, Cebu</span>
        </div>

        <!-- Grand Headline -->
        <h1 class="hero-title">
          <span class="hero-title-lead">Request &amp; Track</span>
          <span class="hero-title-highlight">Barangay Documents</span>
          <span class="hero-title-sub">
            <span>Online</span>
            <span class="hero-sub-pill"><i class="icon-activity"></i> Real-Time</span>
          </span>
        </h1>

        <!-- High-readability Description -->
        <p class="hero-description">
          Modernizing public service for all residents of Barangay Cogon East, Carmen, Cebu. 
          Submit certificate requests online, receive instant SMS/email updates, and skip the queue at the barangay hall.
        </p>

        <!-- Feature Trust Pills Row -->
        <div class="hero-trust-row">
          <div class="trust-item">
            <i class="icon-check-circle"></i>
            <span>Fast 1–2 Day Turnaround</span>
          </div>
          <div class="trust-item">
            <i class="icon-shield-check"></i>
            <span>RA 10173 Privacy Protected</span>
          </div>
          <div class="trust-item">
            <i class="icon-clock"></i>
            <span>24/7 Digital Filing</span>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="hero-actions">
          <a href="{{ route('resident.request.create') }}" class="btn-hero-primary request-doc-btn">
            <i class="icon-file-plus"></i>
            <span>Request Document Now</span>
            <i class="icon-arrow-right btn-arrow"></i>
          </a>

          @if($isLoggedInResident)
            <a href="{{ route('resident.dashboard') }}" class="btn-hero-glass" id="heroDashboardBtn">
              <i class="icon-home"></i>
              <span>My Resident Dashboard</span>
            </a>
          @else
            <a href="{{ route('resident.register') }}" class="btn-hero-glass" id="heroSignupBtn">
              <i class="icon-user-plus"></i>
              <span>Sign Up</span>
            </a>
          @endif
        </div>

        <!-- Floating Glassmorphic Stats Dock -->
        <div class="hero-stats-dock">
          <div class="dock-stat">
            <div class="dock-stat-icon gold"><i class="icon-users"></i></div>
            <div>
              <div class="dock-stat-value">3,316+</div>
              <div class="dock-stat-label">Residents Served</div>
            </div>
          </div>
          <div class="dock-divider"></div>
          <div class="dock-stat">
            <div class="dock-stat-icon blue"><i class="icon-files"></i></div>
            <div>
              <div class="dock-stat-value">5+</div>
              <div class="dock-stat-label">Official Services</div>
            </div>
          </div>
          <div class="dock-divider"></div>
          <div class="dock-stat">
            <div class="dock-stat-icon green"><i class="icon-zap"></i></div>
            <div>
              <div class="dock-stat-value">24/7</div>
              <div class="dock-stat-label">Instant Access</div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Interactive Quick Service Cards -->
      <div class="hero-cards-wrapper">
        <div class="hero-cards-header">
          <div class="cards-header-title">
            <i class="icon-sparkles"></i> Popular Barangay Services
          </div>
          <span class="cards-header-tag">Instant Request</span>
        </div>

        <div class="hero-cards">
          <!-- Card 1: Clearance -->
          <a href="#services" class="hero-card" data-doc="clearance">
            <div class="hero-card-icon card-icon-green">
              <i class="icon-file-badge"></i>
            </div>
            <div class="hero-card-info">
              <div class="card-title-row">
                <h3>Barangay Clearance</h3>
                <span class="card-pill pill-green">Most Requested</span>
              </div>
              <p>For employment, permits &amp; legal transactions</p>
              <div class="card-meta-line">
                <span><i class="icon-clock"></i> 1–2 Days</span>
                <span>&bull;</span>
                <span>₱50.00</span>
              </div>
            </div>
            <div class="hero-card-arrow">
              <i class="icon-arrow-right"></i>
            </div>
          </a>

          <!-- Card 2: Residency -->
          <a href="#services" class="hero-card" data-doc="residency">
            <div class="hero-card-icon card-icon-blue">
              <i class="icon-home"></i>
            </div>
            <div class="hero-card-info">
              <div class="card-title-row">
                <h3>Certificate of Residency</h3>
                <span class="card-pill pill-blue">Fast Release</span>
              </div>
              <p>Official proof of residence in Cogon East</p>
              <div class="card-meta-line">
                <span><i class="icon-clock"></i> Same-Day</span>
                <span>&bull;</span>
                <span>₱50.00</span>
              </div>
            </div>
            <div class="hero-card-arrow">
              <i class="icon-arrow-right"></i>
            </div>
          </a>

          <!-- Card 3: Indigency -->
          <a href="#services" class="hero-card" data-doc="indigency">
            <div class="hero-card-icon card-icon-rose">
              <i class="icon-heart-handshake"></i>
            </div>
            <div class="hero-card-info">
              <div class="card-title-row">
                <h3>Certificate of Indigency</h3>
                <span class="card-pill pill-rose">100% Free / Libre</span>
              </div>
              <p>For financial aid, scholarships &amp; medical help</p>
              <div class="card-meta-line">
                <span><i class="icon-check-circle"></i> Priority SLA</span>
                <span>&bull;</span>
                <span class="text-free">FREE</span>
              </div>
            </div>
            <div class="hero-card-arrow">
              <i class="icon-arrow-right"></i>
            </div>
          </a>
        </div>

        <!-- Quick Tracking Lookup Bar -->
        <a href="{{ route('resident.request.track') }}" class="hero-quick-track-bar">
          <div class="track-bar-content">
            <div class="track-icon"><i class="icon-search"></i></div>
            <div class="track-text">
              <span>Already have an active request?</span>
              <strong>Track with Reference Number &rarr;</strong>
            </div>
          </div>
        </a>
      </div>

    </div>
  </section>

  <!-- ============================
       HOW IT WORKS SECTION
       ============================ -->
  <section class="guidance-section" id="how-it-works">
    <div class="container">
      <div class="guidance-header fade-in-up">
        <div class="section-label">How It Works</div>
        <h2 class="section-title">Request Documents in 4 Easy Steps</h2>
        <p class="section-subtitle">
          No more long queues or repeated visits. Our web-based system streamlines the 
          entire document request process from submission to pickup.
        </p>
      </div>

      <!-- Step-by-step process -->
      <div class="how-it-works-steps fade-in-up">

        <div class="hiw-step">
          <div class="hiw-node">
            <div class="hiw-node-ring"></div>
            <div class="hiw-step-number">1</div>
          </div>
          <div class="hiw-step-body">
            <div class="hiw-step-icon">
              <i class="icon-user-plus"></i>
            </div>
            <h3>Register &amp; Verify</h3>
            <p>Create your account online. Barangay officials will verify your identity as a legitimate resident of Cogon East before activating your account.</p>
            <div class="hiw-step-tag">Step 1</div>
          </div>
        </div>

        <div class="hiw-step">
          <div class="hiw-node">
            <div class="hiw-node-ring"></div>
            <div class="hiw-step-number">2</div>
          </div>
          <div class="hiw-step-body">
            <div class="hiw-step-icon">
              <i class="icon-file-plus"></i>
            </div>
            <h3>Submit Request</h3>
            <p>Choose the document you need — Barangay Clearance, Certificate of Residency, Indigency, Business Clearance, or Barangay ID — and fill out the online form.</p>
            <div class="hiw-step-tag">Step 2</div>
          </div>
        </div>

        <div class="hiw-step">
          <div class="hiw-node">
            <div class="hiw-node-ring"></div>
            <div class="hiw-step-number">3</div>
          </div>
          <div class="hiw-step-body">
            <div class="hiw-step-icon">
              <i class="icon-scan-search"></i>
            </div>
            <h3>Track in Real-Time</h3>
            <p>Monitor the status of your request through your dashboard. Get notified when your document is being processed, approved, and ready for pickup.</p>
            <div class="hiw-step-tag">Step 3</div>
          </div>
        </div>

        <div class="hiw-step">
          <div class="hiw-node hiw-node--final">
            <div class="hiw-node-ring"></div>
            <div class="hiw-step-number">✓</div>
          </div>
          <div class="hiw-step-body">
            <div class="hiw-step-icon hiw-step-icon--final">
              <i class="icon-file-check-2"></i>
            </div>
            <h3>Claim Document</h3>
            <p>Visit the Barangay Hall to claim your completed document. Your records are securely stored in our digital system for future reference.</p>
            <div class="hiw-step-tag hiw-step-tag--final">Complete!</div>
          </div>
        </div>

      </div>

      <!-- Key Features -->
      <div class="features-grid fade-in-up">
        <div class="feature-card">
          <div class="feature-icon blue">
            <i class="icon-monitor-smartphone"></i>
          </div>
          <h3>Access Anywhere</h3>
          <p>Submit document requests from your phone, tablet, or computer — 24/7, no visit required.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon green">
            <i class="icon-activity"></i>
          </div>
          <h3>Real-Time Tracking</h3>
          <p>Know exactly where your request stands — Pending, Processing, Approved, or Ready for Pickup.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon gold">
            <i class="icon-shield-check"></i>
          </div>
          <h3>Secure & Private</h3>
          <p>Your personal data is protected under the Data Privacy Act (RA 10173) with encrypted storage.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon rose">
            <i class="icon-clock"></i>
          </div>
          <h3>Faster Processing</h3>
          <p>Reduced wait times and eliminated redundancies. No more lost paperwork or delayed requests.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================
       DOCUMENTS / SERVICES
       ============================ -->
  <section class="services-section" id="services">
    <div class="container">
      <div class="services-header fade-in-up">
        <div>
          <div class="section-label">Available Documents</div>
          <h2 class="section-title">Request Barangay Documents Online</h2>
          <p class="section-subtitle">Select the document you need and submit your request digitally.</p>
        </div>
        <div class="services-search" id="servicesSearch">
          <i class="icon-search"></i>
          <input type="text" placeholder="Search documents (e.g., clearance, ID...)" id="serviceSearchInput">
        </div>
      </div>

      <div class="services-grid fade-in-up" id="servicesGrid">
        @php
            $portalDocs = collect();
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('document_types')) {
                    $portalDocs = \App\Models\DocumentType::active()->orderBy('name')->get();
                }
            } catch (\Throwable $e) {
                $portalDocs = collect();
            }
        @endphp
        @forelse($portalDocs as $pDoc)
            @php
                $nameLower = strtolower($pDoc->name ?? '');
                if (str_contains($nameLower, 'clearance') && str_contains($nameLower, 'business')) {
                    $lucideIcon = 'icon-store';
                    $iconStyle = 'background: #CCFBF1; color: var(--color-accent-teal);';
                } elseif (str_contains($nameLower, 'clearance')) {
                    $lucideIcon = 'icon-file-badge';
                    $iconStyle = 'background: var(--color-primary-50); color: var(--color-primary);';
                } elseif (str_contains($nameLower, 'residency') || str_contains($nameLower, 'resident')) {
                    $lucideIcon = 'icon-home';
                    $iconStyle = 'background: #EDE9FE; color: #7C3AED;';
                } elseif (str_contains($nameLower, 'indigency') || str_contains($nameLower, 'assistance') || str_contains($nameLower, 'medical')) {
                    $lucideIcon = 'icon-heart-handshake';
                    $iconStyle = 'background: #FEF3C7; color: var(--color-secondary);';
                } elseif (str_contains($nameLower, 'business') || str_contains($nameLower, 'permit')) {
                    $lucideIcon = 'icon-store';
                    $iconStyle = 'background: #CCFBF1; color: var(--color-accent-teal);';
                } elseif (str_contains($nameLower, 'id') || str_contains($nameLower, 'card')) {
                    $lucideIcon = 'icon-id-card';
                    $iconStyle = 'background: #FFE4E6; color: var(--color-accent-rose);';
                } else {
                    $lucideIcon = 'icon-file-text';
                    $iconStyle = 'background: #E0F2FE; color: #0284C7;';
                }

                $pReqs = !empty($pDoc->requirements_list) ? implode(', ', $pDoc->requirements_list) : 'Valid ID, Proof of Residency';
                $keywords = strtolower($pDoc->name . ' ' . ($pDoc->description ?? '') . ' ' . $pReqs);
            @endphp
            <div class="service-card" data-keywords="{{ $keywords }}">
              <div class="service-card-icon" style="{{ $iconStyle }}">
                <i class="{{ $lucideIcon }}"></i>
              </div>
              <h3>{{ $pDoc->name }}</h3>
              <p>{{ $pDoc->description ?: 'Official certification issued by the barangay for administrative and legal requirements.' }}</p>
              <div class="service-card-meta">
                <span class="meta-tag"><i class="icon-clock"></i> {{ $pDoc->processing_days ?: '1–2 days processing' }}</span>
                <span class="meta-tag"><i class="icon-philippine-peso"></i> {{ $pDoc->formatted_fee }}</span>
              </div>
              <div class="service-card-footer">
                <div class="service-card-requirements">
                  <strong>Requirements:</strong> {{ $pReqs }}
                </div>
                <a href="#" class="btn btn-primary btn-sm request-doc-btn" data-doc="{{ $pDoc->name }}">
                  <i class="icon-file-plus"></i> Request Now
                </a>
              </div>
            </div>
        @empty
            <div class="col-12 text-center py-4 text-muted">
                <p>No document services currently available.</p>
            </div>
        @endforelse
      </div>

      <div class="services-no-results" id="servicesNoResults">
        <i class="icon-search-x"></i>
        <h3>No documents found</h3>
        <p>Try a different search term or browse all available documents above.</p>
      </div>
    </div>
  </section>

  <!-- ============================
       DOCUMENT REQUEST MODAL
       ============================ -->
  <div class="modal-overlay" id="requestModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-header-icon">
          <i class="icon-file-plus"></i>
        </div>
        <div>
          <h3 id="modalTitle">Request Document</h3>
          <p id="modalSubtitle">Fill out the form below to submit your request</p>
        </div>
        <button class="modal-close" id="modalClose" aria-label="Close modal">
          <i class="icon-x"></i>
        </button>
      </div>
      <div class="modal-body">
        <form id="requestForm">
          <div class="form-group">
            <label for="reqFullName"><i class="icon-user"></i> Full Name</label>
            <input type="text" id="reqFullName" placeholder="e.g., Juan Dela Cruz" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="reqAge"><i class="icon-calendar"></i> Age</label>
              <input type="number" id="reqAge" placeholder="e.g., 25" min="18" required>
            </div>
            <div class="form-group">
              <label for="reqSex"><i class="icon-users"></i> Sex</label>
              <select id="reqSex" required>
                <option value="">Select...</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="reqAddress"><i class="icon-map-pin"></i> Address in Cogon East</label>
            <input type="text" id="reqAddress" placeholder="e.g., Purok 3, Cogon East, Carmen, Cebu" required>
          </div>
          <div class="form-group">
            <label for="reqContact"><i class="icon-phone"></i> Contact Number</label>
            <input type="tel" id="reqContact" placeholder="e.g., 09171234567" required>
          </div>
          <div class="form-group">
            <label for="reqPurpose"><i class="icon-clipboard-list"></i> Purpose of Request</label>
            <textarea id="reqPurpose" rows="3" placeholder="e.g., Employment requirement, School enrollment..." required></textarea>
          </div>
          <div class="form-group form-consent">
            <label class="consent-label">
              <input type="checkbox" id="reqConsent" required>
              <span class="consent-box"></span>
              <span class="consent-text">
                I consent to the collection and processing of my personal data in accordance with
                <strong>Republic Act 10173</strong> (Data Privacy Act of 2012). My information will
                be used solely for processing this barangay document request.
              </span>
            </label>
          </div>
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" id="cancelRequest">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="icon-send"></i> Submit Request
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- ============================
       EMERGENCY CONTACTS
       ============================ -->
  <section class="emergency-section" id="emergency">
    <div class="container">
      <div class="emergency-header fade-in-up">
        <div class="section-label" style="color: #FCD34D; justify-content: center;">Emergency Hotlines</div>
        <h2 class="section-title">Need Immediate Help?</h2>
        <p class="section-subtitle">These lines are available 24/7 for emergency assistance in Carmen, Cebu.</p>
      </div>

      <div class="emergency-grid fade-in-up">
        <div class="emergency-card">
          <div class="emergency-card-icon red">
            <i class="icon-siren"></i>
          </div>
          <h3>Barangay Cogon East</h3>
          <div class="emergency-card-number">(032) 555-0100</div>
        </div>
        <div class="emergency-card">
          <div class="emergency-card-icon blue">
            <i class="icon-badge"></i>
          </div>
          <h3>Carmen PNP Station</h3>
          <div class="emergency-card-number">911</div>
        </div>
        <div class="emergency-card">
          <div class="emergency-card-icon orange">
            <i class="icon-flame"></i>
          </div>
          <h3>Bureau of Fire (BFP)</h3>
          <div class="emergency-card-number">(032) 555-0119</div>
        </div>
        <div class="emergency-card">
          <div class="emergency-card-icon green">
            <i class="icon-ambulance"></i>
          </div>
          <h3>Carmen Rural Health Unit</h3>
          <div class="emergency-card-number">161 / Red Cross</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================
       FOOTER
       ============================ -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-about">
          <a href="#" class="logo">
            <div class="logo-icon">
              <!-- Image path updated for Laravel -->
              <img src="{{ asset('images/logo.png') }}" alt="E-Barangay Logo" width="40" height="40" loading="lazy" decoding="async">
            </div>
            <div class="logo-text">
              E-Barangay
              <span>Cogon East • Carmen, Cebu</span>
            </div>
          </a>
          <p>
            E-Barangay is a web-based document request and tracking system 
            designed to modernize barangay services in Cogon East, Carmen, Cebu. 
            Developed as a thesis project by BSIT students of Cebu Technological University — Danao Campus.
          </p>
        </div>

        <div class="footer-col">
          <h4>Documents</h4>
          <ul>
            <li><a href="#services">Barangay Clearance</a></li>
            <li><a href="#services">Certificate of Residency</a></li>
            <li><a href="#services">Certificate of Indigency</a></li>
            <li><a href="#services">Business Clearance</a></li>
            <li><a href="#services">Barangay ID</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="#how-it-works">How It Works</a></li>
            @if($isLoggedInResident)
              <li><a href="{{ route('resident.dashboard') }}">My Dashboard</a></li>
            @else
              <li><a href="{{ route('resident.login') }}">Log In</a></li>
            @endif
            <li><a href="#services">Documents</a></li>
            <li><a href="#emergency">Emergency</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Contact Us</h4>
          <ul>
            <li><a href="#"><i class="icon-map-pin"></i> Cogon East, Carmen, Cebu</a></li>
            <li><a href="#"><i class="icon-phone"></i> (032) 555-0100</a></li>
            <li><a href="#"><i class="icon-mail"></i> ebarangay.cogoneast@gmail.com</a></li>
            <li><a href="#"><i class="icon-clock"></i> Mon–Fri, 8AM–5PM</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2026 E-Barangay — Barangay Cogon East, Carmen, Cebu. All rights reserved.</p>
        <p class="footer-thesis">
          A thesis by Adolfo, Arong, Castro, Nuñez, Sios-e & Suson — BSIT, CTU Danao Campus
        </p>
      </div>
    </div>
  </footer>

  <!-- ============================
       ISTONG AI CHAT WIDGET
       ============================ -->
  <div class="chat-widget" id="chatWidget">
    <div class="chat-window" id="chatWindow">
      <div class="chat-header">
        <div class="chat-avatar istong-avatar">
          <img src="{{ asset('images/fisherman.png') }}" alt="Istong AI" class="istong-avatar-img" width="42" height="42" loading="lazy" decoding="async">
          <span class="avatar-ai-badge">AI</span>
        </div>
        <div class="chat-header-info">
          <div class="chat-title-row">
            <h4>Istong AI</h4>
            <span class="chat-badge-tag">Digital Assistant</span>
          </div>
          <p><span class="chat-status-dot"></span>Online &bull; Cogon East Assistant</p>
        </div>
        <div class="chat-header-actions">
          <button type="button" class="chat-header-btn" id="chatResetBtn" title="Bag-ong istorya / Reset chat" aria-label="Reset chat">
            <i class="icon-rotate-ccw"></i>
          </button>
          <button type="button" class="chat-header-btn" id="chatCloseBtn" title="Close" aria-label="Close chat">
            <i class="icon-x"></i>
          </button>
        </div>
      </div>

      <div class="chat-body" id="chatBody">
        <div class="chat-msg bot">
          Maayong adlaw, silingan! 👋 Ako diay si <strong>Istong</strong>, ang opisyal nga digital assistant sa Barangay Cogon East.
          <br><br>
          Unsay ako ikatabang nimo karon? Pwede ka mangutana bahin sa:
          <br>&bull; Pamaagi sa pag-request og dokumento
          <br>&bull; Requirements ug mga bayronon (fees)
          <br>&bull; Pag-track sa status sa imong dokumento
          <br>&bull; Oras sa opisina ug serbisyo sa barangay
          <br><br>
          <em>Pangutana lang bisan Bisaya o English!</em>
        </div>
      </div>

      <!-- Quick Suggested Prompts -->
      <div class="chat-prompts">
        <div class="chat-prompts-label"><i class="icon-sparkles"></i> Paspas nga Pangutana:</div>
        <div class="chat-prompt-btns" id="chatPromptBtns">
          <button type="button" class="chat-prompt-btn" data-query="Unsaon pag-request og Barangay Clearance?">📄 Unsaon pag-request?</button>
          <button type="button" class="chat-prompt-btn" data-query="Giunsa nako pag-track ang akong gipasa nga document request?">🔍 Unsaon pag-track?</button>
          <button type="button" class="chat-prompt-btn" data-query="Unsay requirements para sa Certificate of Residency?">📋 Requirements sa Residency</button>
          <button type="button" class="chat-prompt-btn" data-query="Unsa ang schedule ug office hours sa Barangay Hall?">🕐 Office hours</button>
          <button type="button" class="chat-prompt-btn" data-query="Unsaon pagsumite og community concern o reklamo?">📢 Pag-submit og concern</button>
        </div>
      </div>

      <!-- Interactive Message Input -->
      <form class="chat-footer" id="chatForm">
        <input 
          type="text" 
          id="chatInput" 
          placeholder="Pangutana kang Istong diri..." 
          autocomplete="off" 
          maxlength="500" 
          aria-label="Ask Istong AI"
        >
        <button type="submit" id="chatSendBtn" class="chat-send-btn" aria-label="Ipadala" title="Send message">
          <i class="icon-send"></i>
        </button>
      </form>
    </div>

    <!-- Floating Toggle Button with Istong Pill -->
    <div class="chat-toggle-wrap">
      <div class="chat-pill" id="chatPill">
        <span class="chat-pill-sparkle">✨</span>
        <span>Pangutana kang <strong>Istong AI</strong></span>
      </div>
      <button class="chat-toggle" id="chatToggle" aria-label="Open Istong AI Assistant" title="Open Istong AI">
        <span class="chat-icon-avatar">
          <img src="{{ asset('images/fisherman.png') }}" alt="Istong AI Mascot" class="chat-mascot-img" width="38" height="38" loading="lazy" decoding="async">
        </span>
        <i class="icon-x chat-icon-close"></i>
      </button>
    </div>
  </div>

  <!-- Login Required Modal -->
  <div class="modal-overlay" id="authRequiredModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-header-icon">
          <i class="icon-lock"></i>
        </div>
        <div>
          <h3 id="authRequiredTitle">Login Required</h3>
          <p id="authRequiredMessage">Please log in to continue.</p>
        </div>
        <button class="modal-close" id="authRequiredClose" aria-label="Close modal">
          <i class="icon-x"></i>
        </button>
      </div>
      <div class="modal-body">
        <p id="authRequiredDescription" class="text-muted" style="margin: 0; line-height: 1.6;">
          You need to log in first before continuing.
        </p>
      </div>
      <div class="form-actions">
        <button type="button" class="btn btn-secondary" id="authRequiredCancel">Cancel</button>
        <button type="button" class="btn btn-primary" id="authRequiredLogin">
          <i class="icon-log-in"></i> Login
        </button>
      </div>
    </div>
  </div>

  <!-- Resident Login Modal -->
  <div class="modal-overlay" id="residentLoginModal">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-header-icon">
          <i class="icon-user"></i>
        </div>
        <div>
          <h3>Welcome Back</h3>
          <p>Log in to your resident portal</p>
        </div>
        <button class="modal-close" id="residentLoginClose" aria-label="Close modal">
          <i class="icon-x"></i>
        </button>
      </div>
      <div class="modal-body">
        @include('partials.resident-login-form', [
            'formId' => 'welcomeResidentLoginForm',
            'ajaxMode' => true,
            'errorContainerId' => 'welcomeLoginError',
        ])
      </div>
    </div>
  </div>

  <!-- Success Toast -->
  <div class="toast" id="toast">
    <div class="toast-icon"><i class="icon-check-circle"></i></div>
    <div class="toast-content">
      <h4 id="toastTitle">Success!</h4>
      <p id="toastMessage">{{ session('success', 'Your request has been submitted successfully.') }}</p>
    </div>
    <button class="toast-close" id="toastClose"><i class="icon-x"></i></button>
  </div>

  <!-- JavaScript (Updated for Laravel) -->
  <script src="{{ \App\Providers\AppServiceProvider::versionedAsset('js/app.js') }}"></script>
  @if(session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toast = document.getElementById('toast');
      const toastTitle = document.getElementById('toastTitle');
      const toastMessage = document.getElementById('toastMessage');
      if (toast && toastTitle && toastMessage) {
        toastTitle.textContent = 'Success!';
        toast.classList.add('visible');
        setTimeout(() => toast.classList.remove('visible'), 5000);
      }
    });
  </script>
  @endif
</body>
</html>