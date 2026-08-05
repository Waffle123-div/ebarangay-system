<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="E-Barangay: Web-Based Document Request and Tracking System for Barangay Cogon East, Carmen, Cebu. Request barangay documents online, track your requests in real-time, and access community services.">
  <title>E-Barangay — Document Request & Tracking System | Barangay Cogon East, Carmen, Cebu</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Icons (Lucide via CDN) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide-static@latest/font/lucide.min.css">

  <!-- Styles (Updated for Laravel) -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

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
          <img src="{{ asset('images/logo.png') }}" alt="E-Barangay Logo">
        </div>
        <div class="logo-text">
          E-Barangay
          <span>Cogon East • Carmen, Cebu</span>
        </div>
      </a>

      <nav class="nav-links" id="navLinks">
        <a href="#hero" class="active">Home</a>
        <a href="#how-it-works">How It Works</a>
        <a href="#services">Documents</a>
        <a href="#news">News</a>
        <a href="#concerns">Community Concern</a>
        <a href="#emergency">Emergency</a>
      </nav>

      <div class="nav-cta">
        @php
          $navUser = auth()->user();
          $isLoggedInResident = $navUser && trim(strtolower($navUser->role)) !== 'admin';
          $navInitials = collect(explode(' ', trim($navUser?->name ?? '')))
              ->filter()
              ->take(2)
              ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
              ->implode('');
        @endphp

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
                  <img src="{{ \Illuminate\Support\Facades\Storage::url($navUser->profile_picture) }}" alt="{{ $navUser->name }}" class="welcome-profile-avatar">
                @else
                  <span class="welcome-profile-avatar welcome-profile-avatar-fallback">{{ $navInitials ?: 'R' }}</span>
                @endif
                <span class="welcome-profile-name">{{ $navUser->name }}</span>
                <i class="icon-chevron-down welcome-profile-chevron"></i>
              </button>
              <div class="welcome-dropdown welcome-profile-dropdown" id="welcomeProfileDropdown">
                <div class="welcome-profile-card">
                  @if($navUser->profile_picture)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($navUser->profile_picture) }}" alt="{{ $navUser->name }}" class="welcome-profile-card-avatar">
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
    <div class="hero-bg">
      <!-- Image path updated for Laravel -->
      <img src="{{ asset('images/hero.png') }}" alt="Barangay Cogon East, Carmen, Cebu community" loading="eager">
    </div>
    <div class="container">
      <div class="hero-content">
        <div class="hero-badge">
          <i class="icon-shield-check"></i>
          Official Barangay Digital Service Portal
        </div>
        <h1>
          Request & Track <br>
          <span class="highlight">Barangay Documents</span><br>
          <span class="hero-sub">Online</span>
        </h1>
        <p class="hero-description">
          E-Barangay makes it easy for residents of Barangay Cogon East, Carmen, Cebu 
          to request barangay documents online, track their request status in real-time, 
          and skip the long queues at the barangay hall.
        </p>
        <div class="hero-actions">
          <a href="#services" class="btn btn-gold btn-lg">
            <i class="icon-file-plus"></i>
            Request Document Now
          </a>
          <a href="{{ route('resident.register') }}" class="btn btn-secondary btn-lg" style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.3); color: white;" id="heroSignupBtn">
            <i class="icon-user-plus"></i>
            Create Account
          </a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
            <div class="hero-stat-value">3,316+</div>
            <div class="hero-stat-label">Residents Served</div>
          </div>
          <div class="hero-stat">
            <div class="hero-stat-value">5</div>
            <div class="hero-stat-label">Document Types</div>
          </div>
          <div class="hero-stat">
            <div class="hero-stat-value">24/7</div>
            <div class="hero-stat-label">Online Access</div>
          </div>
        </div>
      </div>

      <div class="hero-cards">
        <a href="#services" class="hero-card" data-doc="clearance">
          <div class="hero-card-icon green">
            <i class="icon-file-badge"></i>
          </div>
          <div class="hero-card-info">
            <h3>Barangay Clearance</h3>
            <p>For employment, permits & legal transactions</p>
          </div>
          <div class="hero-card-arrow"><i class="icon-arrow-right"></i></div>
        </a>
        <a href="#services" class="hero-card" data-doc="residency">
          <div class="hero-card-icon gold">
            <i class="icon-home"></i>
          </div>
          <div class="hero-card-info">
            <h3>Certificate of Residency</h3>
            <p>Official proof of residence in Cogon East</p>
          </div>
          <div class="hero-card-arrow"><i class="icon-arrow-right"></i></div>
        </a>
        <a href="#services" class="hero-card" data-doc="indigency">
          <div class="hero-card-icon rose">
            <i class="icon-heart-handshake"></i>
          </div>
          <div class="hero-card-info">
            <h3>Certificate of Indigency</h3>
            <p>For financial aid, scholarships & medical help</p>
          </div>
          <div class="hero-card-arrow"><i class="icon-arrow-right"></i></div>
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

        <div class="service-card" data-keywords="clearance barangay clearance employment permit legal">
          <div class="service-card-icon" style="background: var(--color-primary-50); color: var(--color-primary);">
            <i class="icon-file-badge"></i>
          </div>
          <h3>Barangay Clearance</h3>
          <p>Required for employment, business permits, and legal transactions. Present a valid government ID and proof of residency.</p>
          <div class="service-card-meta">
            <span class="meta-tag"><i class="icon-clock"></i> ~30 mins processing</span>
            <span class="meta-tag"><i class="icon-philippine-peso"></i> ₱100.00</span>
          </div>
          <div class="service-card-footer">
            <div class="service-card-requirements">
              <strong>Requirements:</strong> Valid ID, 2x2 Photo, Proof of Residency
            </div>
            <a href="#" class="btn btn-primary btn-sm request-doc-btn" data-doc="Barangay Clearance">
              <i class="icon-file-plus"></i> Request Now
            </a>
          </div>
        </div>

        <div class="service-card" data-keywords="certificate residency residence proof address">
          <div class="service-card-icon" style="background: #EDE9FE; color: #7C3AED;">
            <i class="icon-home"></i>
          </div>
          <h3>Certificate of Residency</h3>
          <p>Official proof that you are a bonafide resident of Barangay Cogon East. Required for various government transactions.</p>
          <div class="service-card-meta">
            <span class="meta-tag"><i class="icon-clock"></i> ~20 mins processing</span>
            <span class="meta-tag"><i class="icon-philippine-peso"></i> ₱50.00</span>
          </div>
          <div class="service-card-footer">
            <div class="service-card-requirements">
              <strong>Requirements:</strong> Valid ID, Utility Bill or Lease Contract
            </div>
            <a href="#" class="btn btn-primary btn-sm request-doc-btn" data-doc="Certificate of Residency">
              <i class="icon-file-plus"></i> Request Now
            </a>
          </div>
        </div>

        <div class="service-card" data-keywords="certificate indigency financial assistance free scholarship medical">
          <div class="service-card-icon" style="background: #FEF3C7; color: var(--color-secondary);">
            <i class="icon-heart-handshake"></i>
          </div>
          <h3>Certificate of Indigency</h3>
          <p>For residents in need of financial assistance, free medical care, or educational scholarships from government agencies.</p>
          <div class="service-card-meta">
            <span class="meta-tag"><i class="icon-clock"></i> ~20 mins processing</span>
            <span class="meta-tag"><i class="icon-philippine-peso"></i> FREE</span>
          </div>
          <div class="service-card-footer">
            <div class="service-card-requirements">
              <strong>Requirements:</strong> Valid ID, Brief Interview with Social Worker
            </div>
            <a href="#" class="btn btn-primary btn-sm request-doc-btn" data-doc="Certificate of Indigency">
              <i class="icon-file-plus"></i> Request Now
            </a>
          </div>
        </div>

        <div class="service-card" data-keywords="business permit clearance negosyo sari-sari store commercial">
          <div class="service-card-icon" style="background: #CCFBF1; color: var(--color-accent-teal);">
            <i class="icon-store"></i>
          </div>
          <h3>Business Clearance</h3>
          <p>Required for new businesses or annual renewal of business permits within the barangay jurisdiction of Cogon East.</p>
          <div class="service-card-meta">
            <span class="meta-tag"><i class="icon-clock"></i> 1–2 days processing</span>
            <span class="meta-tag"><i class="icon-philippine-peso"></i> ₱200.00</span>
          </div>
          <div class="service-card-footer">
            <div class="service-card-requirements">
              <strong>Requirements:</strong> Valid ID, DTI Registration, Business Address Proof
            </div>
            <a href="#" class="btn btn-primary btn-sm request-doc-btn" data-doc="Business Clearance">
              <i class="icon-file-plus"></i> Request Now
            </a>
          </div>
        </div>

        <div class="service-card" data-keywords="barangay id identification card resident">
          <div class="service-card-icon" style="background: #FFE4E6; color: var(--color-accent-rose);">
            <i class="icon-id-card"></i>
          </div>
          <h3>Barangay ID</h3>
          <p>Official identification card for all registered residents of Barangay Cogon East. Valid for discounts and local government transactions.</p>
          <div class="service-card-meta">
            <span class="meta-tag"><i class="icon-clock"></i> 3–5 days processing</span>
            <span class="meta-tag"><i class="icon-philippine-peso"></i> ₱75.00</span>
          </div>
          <div class="service-card-footer">
            <div class="service-card-requirements">
              <strong>Requirements:</strong> Valid ID, 1x1 Photo, Proof of Residency
            </div>
            <a href="#" class="btn btn-primary btn-sm request-doc-btn" data-doc="Barangay ID">
              <i class="icon-file-plus"></i> Request Now
            </a>
          </div>
        </div>

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
       NEWS & ANNOUNCEMENTS
       ============================ -->
  <section class="news-section" id="news">
    <div class="container">
      <div class="fade-in-up">
        <div class="section-label">Latest Updates</div>
        <h2 class="section-title">Barangay News & Announcements</h2>
      </div>

      <div class="news-grid fade-in-up">
        <div class="news-card">
          <div class="news-card-img">
            <i class="icon-laptop"></i>
          </div>
          <div class="news-card-body">
            <div class="news-card-date">
              <i class="icon-calendar"></i>
              June 10, 2026
            </div>
            <h3>E-Barangay System Now Live!</h3>
            <p>The new web-based document request and tracking system is now available for all residents of Barangay Cogon East. Register now to skip the lines!</p>
          </div>
        </div>

        <div class="news-card">
          <div class="news-card-img" style="background: var(--gradient-gold);">
            <i class="icon-megaphone"></i>
          </div>
          <div class="news-card-body">
            <div class="news-card-date">
              <i class="icon-calendar"></i>
              June 5, 2026
            </div>
            <h3>Free Barangay Clearance Day — June 20</h3>
            <p>In celebration of the E-Barangay launch, barangay clearance processing will be FREE for all residents on June 20, 2026. Don't miss it!</p>
          </div>
        </div>

        <div class="news-card">
          <div class="news-card-img" style="background: linear-gradient(135deg, #0D9488, #14B8A6);">
            <i class="icon-calendar-check"></i>
          </div>
          <div class="news-card-body">
            <div class="news-card-date">
              <i class="icon-calendar"></i>
              June 1, 2026
            </div>
            <h3>Community Assembly — June 15 at 2PM</h3>
            <p>All residents are invited to the quarterly community assembly at the Barangay Hall covered court. Discussion on the digital transformation initiative.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================
       COMMUNITY CONCERNS
       ============================ -->
  <section class="concerns-section" id="concerns">
    <div class="container">
      <div class="concerns-content fade-in-up">
        <div class="concerns-text">
          <div class="section-label">Community Feature</div>
          <h2 class="section-title">Submit a Community Concern</h2>
          <p class="section-subtitle">
            Have an issue or concern in your purok? Report it online and barangay officials 
            will monitor and address it. Together, we build a better Cogon East.
          </p>
          <div class="concern-types">
            <div class="concern-type">
              <i class="icon-construction"></i>
              <span>Infrastructure</span>
            </div>
            <div class="concern-type">
              <i class="icon-shield-alert"></i>
              <span>Peace & Order</span>
            </div>
            <div class="concern-type">
              <i class="icon-leaf"></i>
              <span>Sanitation</span>
            </div>
            <div class="concern-type">
              <i class="icon-lightbulb"></i>
              <span>Utilities</span>
            </div>
          </div>
        </div>
        <div class="concerns-form-card">
          @php
            $authUser = auth()->user();
            $isResident = $authUser && trim(strtolower($authUser->role)) !== 'admin';
            $categories = \App\Models\CommunityConcern::categories();
            $guestLocked = !$isResident;
          @endphp

          @if($errors->any())
            <div class="concern-form-alert">
              <strong>Please fix the following:</strong>
              <ul>
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if($guestLocked)
            <div class="concern-auth-notice">
              <i class="icon-lock"></i>
              <span>Please log in to submit a Community Concern.</span>
            </div>
          @endif

          <form id="concernForm"
                class="concern-form {{ $guestLocked ? 'concern-form-locked' : '' }}"
                @if($isResident)
                  action="{{ route('resident.concern.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                @else
                  data-require-auth="true"
                  data-login-url="{{ route('resident.login') }}"
                @endif>
            @if($isResident)
              @csrf
              <input type="hidden" name="redirect_to" value="welcome">
            @endif

            <div class="concern-form-grid concern-form-grid-2">
              <div class="form-group">
                <label for="concernTitle"><i class="icon-file-text"></i> Concern Title</label>
                <input type="text" id="concernTitle" name="title" value="{{ old('title') }}" placeholder="Brief title of your concern" {{ $isResident ? 'required' : 'readonly tabindex=-1' }}>
              </div>
              <div class="form-group">
                <label for="concernType"><i class="icon-tag"></i> Concern Category</label>
                <select id="concernType" name="category" {{ $isResident ? 'required' : 'disabled tabindex=-1' }}>
                  <option value="">Select category...</option>
                  @foreach($categories as $category)
                    <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="concernDetails"><i class="icon-message-square"></i> Description</label>
              <textarea id="concernDetails" name="description" rows="3" placeholder="Describe your concern in detail..." {{ $isResident ? 'required' : 'readonly tabindex=-1' }}>{{ old('description') }}</textarea>
            </div>
            <div class="concern-form-grid concern-form-grid-2">
              <div class="form-group">
                <label for="concernLocation"><i class="icon-map-pin"></i> Location</label>
                <input type="text" id="concernLocation" name="location" value="{{ old('location') }}" placeholder="Purok, Street Name, Landmark" {{ $isResident ? 'required' : 'readonly tabindex=-1' }}>
              </div>
              <div class="form-group">
                <label for="concernDate"><i class="icon-calendar"></i> Date of Incident <small>(Optional)</small></label>
                <input type="date" id="concernDate" name="incident_date" value="{{ old('incident_date') }}" max="{{ date('Y-m-d') }}" {{ $isResident ? '' : 'readonly disabled tabindex=-1' }}>
              </div>
            </div>
            <div class="form-group">
              <label for="concernPhoto"><i class="icon-image"></i> Photo Attachment <small>(JPG, JPEG, PNG — Max 5MB)</small></label>
              <input type="file" id="concernPhoto" name="photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" {{ $isResident ? '' : 'disabled tabindex=-1' }}>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
              <i class="icon-send"></i> Submit Concern
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

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
              <img src="{{ asset('images/logo.png') }}" alt="E-Barangay Logo">
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
            <li><a href="{{ route('resident.login') }}">Log In</a></li>
            <li><a href="#concerns">Submit Concern</a></li>
            <li><a href="#news">News & Updates</a></li>
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
       VIRTUAL ASSISTANT CHAT WIDGET
       ============================ -->
  <div class="chat-widget" id="chatWidget">
    <div class="chat-window" id="chatWindow">
      <div class="chat-header">
        <div class="chat-avatar">🤖</div>
        <div class="chat-header-info">
          <h4>E-Barangay Assistant</h4>
          <p><span class="chat-status-dot"></span>Online — Ready to help</p>
        </div>
      </div>
      <div class="chat-body" id="chatBody">
        <div class="chat-msg bot">
          Magandang araw! 👋 I'm your E-Barangay Assistant. How can I help you today? Choose from the options below or ask a question.
        </div>
      </div>
      <div class="chat-prompts">
        <div class="chat-prompts-label">Quick Questions:</div>
        <div class="chat-prompt-btns" id="chatPromptBtns">
          <button class="chat-prompt-btn" data-prompt="how-to-request">📄 How to request a document?</button>
          <button class="chat-prompt-btn" data-prompt="track">🔍 How to track my request?</button>
          <button class="chat-prompt-btn" data-prompt="requirements">📋 What do I need to bring?</button>
          <button class="chat-prompt-btn" data-prompt="schedule">🕐 Office hours?</button>
          <button class="chat-prompt-btn" data-prompt="concern">📢 Submit a concern</button>
        </div>
      </div>
    </div>
    <button class="chat-toggle" id="chatToggle" aria-label="Open chat assistant">
      <i class="icon-message-circle chat-icon-open"></i>
      <i class="icon-x chat-icon-close"></i>
    </button>
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
  <script src="{{ asset('js/app.js') }}"></script>
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