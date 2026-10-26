@extends('resident.layouts.resident')

@section('content')
<style>
    /* =========================================================
       MODERN DASHBOARD UI STYLES (Adapted for E-Barangay)
       ========================================================= */
    .dashboard-wrapper {
        background-color: #f8fafb;
        min-height: calc(100vh - 70px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    
    /* Layout Cards */
    .dash-card {
        background: #ffffff;
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    /* Hero Banner - Using E-Barangay Primary Color */
    .dash-hero {
        background: linear-gradient(135deg, var(--color-primary) 0%, #3b82f6 100%);
        border-radius: 1.25rem;
        padding: 2.5rem;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }
    .dash-hero::after {
        content: '';
        position: absolute; right: -40px; top: -40px;
        width: 200px; height: 200px; border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        z-index: 1;
    }
    .dash-hero-content { position: relative; z-index: 2; max-width: 65%; }
    .dash-hero h3 { font-weight: 700; font-size: 1.6rem; margin-bottom: 0.5rem; }
    .dash-hero p { color: rgba(255,255,255,0.9); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.5; }
    .dash-hero-btn {
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #ffffff; font-weight: 600; padding: 0.5rem 1.5rem;
        border-radius: 2rem; backdrop-filter: blur(4px);
        transition: all 0.2s ease; text-decoration: none; display: inline-block;
    }
    .dash-hero-btn:hover { background: #ffffff; color: var(--color-primary); }
    .dash-hero-icon {
        position: absolute; right: 30px; bottom: -20px;
        font-size: 8rem; color: rgba(255,255,255,0.15);
        transform: rotate(-15deg); z-index: 1;
    }

    /* Section Headers */
    .section-title { font-weight: 700; color: #2d3748; font-size: 1.1rem; margin: 0; }
    .section-link { font-size: 0.85rem; color: #718096; text-decoration: none; font-weight: 600; transition: color 0.2s; }
    .section-link:hover { color: var(--color-primary); }

    /* Table Styles */
    .dash-table th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #a0aec0; font-weight: 600; padding: 1rem; border-bottom: 1px solid #edf2f7; }
    .dash-table td { padding: 1rem; font-size: 0.9rem; color: #4a5568; border-bottom: 1px solid #f7fafc; vertical-align: middle; }
    .dash-table tr:last-child td { border-bottom: none; }
    .dash-badge { padding: 0.35rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
    .badge-pending { background: #fffbeb; color: #d97706; }
    .badge-approved { background: #f0fdf4; color: #16a34a; }
    .badge-ready { background: #e0f2fe; color: #0284c7; }
    .badge-completed { background: #f1f5f9; color: #475569; }

    /* Right Master Sidebar Container */
    .master-sidebar-card {
        background: #ffffff;
        border-radius: 1.5rem;
        padding: 1.75rem 1.25rem;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.02);
        height: auto;
        display: flex;
        flex-direction: column;
    }

    /* Calendar Inside Unified Container */
    .calendar-section-inner { padding-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; margin-bottom: 1.25rem; }
    .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .calendar-nav { cursor: pointer; color: #a0aec0; padding: 0.2rem; font-size: 0.8rem; transition: color 0.2s; }
    .calendar-nav:hover { color: var(--color-primary); }
    .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center; }
    .calendar-day-name { font-size: 0.7rem; color: #a0aec0; font-weight: 600; margin-bottom: 0.5rem; }
    .calendar-day {
        font-size: 0.8rem; color: #4a5568; width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; margin: 0 auto; cursor: pointer; transition: all 0.2s;
    }
    .calendar-day:hover { background: #edf2f7; color: var(--color-primary); font-weight: 600; }
    .calendar-day.active { background: var(--color-primary); color: #fff; font-weight: 600; box-shadow: 0 2px 8px rgba(30, 64, 175, 0.3); }
    .calendar-day.active:hover { background: var(--color-primary); color: #fff; }
    .calendar-day.muted { color: #cbd5e0; pointer-events: none; }

    /* Reminders Inside Unified Container */
    .reminders-section-inner .sub-title { font-weight: 700; color: #2d3748; font-size: 1rem; margin-bottom: 0.85rem; }
    .reminder-item { display: flex; gap: 0.85rem; align-items: flex-start; margin-bottom: 0.85rem; }
    .reminder-item:last-child { margin-bottom: 0; }
    .reminder-icon {
        width: 34px; height: 34px; border-radius: 10px; background: #f7fafc;
        display: flex; align-items: center; justify-content: center; color: #718096; flex-shrink: 0;
        border: 1px solid #edf2f7; font-size: 0.85rem;
    }
    .reminder-info h6 { margin: 0 0 0.15rem 0; font-size: 0.85rem; font-weight: 600; color: #2d3748; }
    .reminder-info p { margin: 0; font-size: 0.72rem; color: #a0aec0; }
</style>

<div class="dashboard-wrapper pb-5">
    <div class="container-fluid py-4 px-4 px-xl-5">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible show rounded-4 mb-4 shadow-sm border-0" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- OFFICIAL BARANGAY ANNOUNCEMENT BANNER      --}}
        {{-- ========================================== --}}
        @if(!empty($announcement) && !empty($announcement['message']))
            @php
                $type = $announcement['type'] ?? 'info';
                $config = [
                    'info' => [
                        'bg'     => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
                        'border' => '#93c5fd',
                        'color'  => '#1e40af',
                        'badge'  => 'bg-primary text-white',
                        'icon'   => 'fa-solid fa-circle-info',
                        'iconBg' => 'bg-primary text-white',
                        'btn'    => 'btn-primary',
                    ],
                    'warning' => [
                        'bg'     => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)',
                        'border' => '#fcd34d',
                        'color'  => '#92400e',
                        'badge'  => 'bg-warning text-dark',
                        'icon'   => 'fa-solid fa-triangle-exclamation',
                        'iconBg' => 'bg-warning text-dark',
                        'btn'    => 'btn-warning text-dark',
                    ],
                    'danger' => [
                        'bg'     => 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)',
                        'border' => '#fca5a5',
                        'color'  => '#991b1b',
                        'badge'  => 'bg-danger text-white',
                        'icon'   => 'fa-solid fa-circle-exclamation',
                        'iconBg' => 'bg-danger text-white',
                        'btn'    => 'btn-danger text-white',
                    ],
                    'success' => [
                        'bg'     => 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)',
                        'border' => '#86efac',
                        'color'  => '#166534',
                        'badge'  => 'bg-success text-white',
                        'icon'   => 'fa-solid fa-bullhorn',
                        'iconBg' => 'bg-success text-white',
                        'btn'    => 'btn-success text-white',
                    ],
                ][$type] ?? [
                    'bg'     => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
                    'border' => '#93c5fd',
                    'color'  => '#1e40af',
                    'badge'  => 'bg-primary text-white',
                    'icon'   => 'fa-solid fa-circle-info',
                    'iconBg' => 'bg-primary text-white',
                    'btn'    => 'btn-primary',
                ];
            @endphp

            <div id="barangay-announcement-card" class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative" style="background: {{ $config['bg'] }}; border: 1px solid {{ $config['border'] }} !important;">
                @if($type === 'danger')
                    <div style="height: 4px; background: linear-gradient(90deg, #ef4444, #b91c1c); animation: pulse 2s infinite;"></div>
                @endif
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge {{ $config['badge'] }} px-3 py-1.5 rounded-pill fw-bold text-uppercase shadow-sm" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="{{ $config['icon'] }} me-1"></i> {{ $announcement['category'] ?? 'Barangay Advisory' }}
                            </span>
                            @if($type === 'danger')
                                <span class="badge bg-danger rounded-pill px-2.5 py-1 text-white small fw-bold" style="font-size: 10px; animation: blinker 1.5s linear infinite;">
                                    <i class="fa-solid fa-circle-dot me-1"></i> URGENT ALERT
                                </span>
                            @endif
                            @if(!empty($announcement['updated_at']))
                                <span class="small" style="color: {{ $config['color'] }}; opacity: 0.85; font-size: 12px;">
                                    <i class="fa-regular fa-clock me-1"></i> {{ \Carbon\Carbon::parse($announcement['updated_at'])->format('M d, Y · h:i A') }}
                                </span>
                            @endif
                        </div>
                        @if(!empty($announcement['is_dismissible']))
                            <button type="button" class="btn-close ms-auto" aria-label="Close" onclick="dismissAnnouncementBanner()" style="font-size: 11px;"></button>
                        @endif
                    </div>
                    <div class="d-flex align-items-start gap-3 mt-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0 {{ $config['iconBg'] }}" style="width: 48px; height: 48px; font-size: 20px;">
                            <i class="{{ $config['icon'] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1" style="color: {{ $config['color'] }};">{{ $announcement['title'] }}</h5>
                            <p class="mb-0" style="color: {{ $config['color'] }}; opacity: 0.95; font-size: 14.5px; line-height: 1.55; white-space: pre-line;">{{ $announcement['message'] }}</p>
                            @if(!empty($announcement['action_url']))
                                <div class="mt-3">
                                    <a href="{{ $announcement['action_url'] }}" class="btn {{ $config['btn'] }} btn-sm px-3.5 py-1.5 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center gap-1.5">
                                        <span>{{ $announcement['action_text'] ?: 'Learn More' }}</span>
                                        <i class="fa-solid fa-arrow-right" style="font-size: 11px;"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if(!empty($announcement['is_dismissible']))
                <script>
                (function() {
                    const bannerKey = 'brgy_announcement_dismissed_{{ md5($announcement['title'] . ($announcement['message'] ?? '')) }}';
                    if (sessionStorage.getItem(bannerKey)) {
                        const card = document.getElementById('barangay-announcement-card');
                        if (card) card.style.display = 'none';
                    }
                    window.dismissAnnouncementBanner = function() {
                        const card = document.getElementById('barangay-announcement-card');
                        if (card) {
                            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(-10px)';
                            setTimeout(() => card.style.display = 'none', 300);
                        }
                        sessionStorage.setItem(bannerKey, 'true');
                    };
                })();
                </script>
            @endif
        @endif

        <div class="row g-4 align-items-start">
            
            {{-- ========================================== --}}
            {{-- LEFT MAIN COLUMN (Hero & Table)            --}}
            {{-- ========================================== --}}
            <div class="col-xl-8 col-lg-7 d-flex flex-column">

                {{-- Hero Banner --}}
                <div class="dash-hero mb-4 shadow-sm">
                    <div class="dash-hero-content">
                        @if($isNewUser)
                            <h3>Welcome, {{ strtoupper($firstName) }}!</h3>
                        @else
                            <h3>Welcome back, {{ strtoupper($firstName) }}!</h3>
                        @endif
                        <p>Need a new barangay document? Skip the line and easily submit your request online right now.</p>
                        <a href="{{ route('resident.request.create') }}" class="dash-hero-btn shadow-sm">Request Document</a>
                    </div>
                    <i class="fa-solid fa-file-invoice dash-hero-icon"></i>
                </div>

                {{-- Recent Requests Table --}}
                <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
                    <h5 class="section-title">Recent Requests</h5>
                    <a href="{{ route('resident.activity-logs') }}" class="section-link">View All <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.75rem;"></i></a>
                </div>
                <div class="dash-card shadow-sm flex-grow-1">
                    <div class="table-responsive p-2">
                        <table class="table table-borderless dash-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Tracking No.</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $topRequests = isset($recentRequests) ? $recentRequests->take(5)->values() : collect();
                                @endphp
                                @if($topRequests->count() > 0)
                                    @for($i = 0; $i < $topRequests->count(); $i++)
                                        @php
                                            $request = $topRequests[$i];
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $request->document_type }}</div>
                                                <div class="small text-muted">{{ $request->purpose ?? 'General' }}</div>
                                            </td>
                                            <td class="font-monospace text-muted">{{ $request->tracking_number }}</td>
                                            <td>{{ $request->created_at->format('d.m.Y') }}</td>
                                            <td>
                                                <button type="button" class="btn btn-link text-primary text-decoration-none fw-semibold p-0" style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $request->id }}">
                                                    <i class="fa-solid fa-download me-1"></i> Receipt
                                                </button>
                                            </td>
                                            <td>
                                                @if($request->status == 'Completed')
                                                    <span class="dash-badge badge-completed"><i class="fa-solid fa-circle-check"></i> Done</span>
                                                @elseif($request->status == 'Ready')
                                                    <span class="dash-badge badge-ready"><i class="fa-solid fa-bell"></i> Ready</span>
                                                @elseif($request->status == 'Approved')
                                                    <span class="dash-badge badge-approved"><i class="fa-solid fa-thumbs-up"></i> Approved</span>
                                                @elseif($request->status == 'Pending')
                                                    <span class="dash-badge badge-pending"><i class="fa-solid fa-clock"></i> Pending</span>
                                                @else
                                                    <span class="dash-badge bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-spinner"></i> {{ $request->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endfor
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fa-regular fa-folder-open mb-2 fa-2x opacity-50"></i>
                                            <div class="small">No recent activity found.</div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- ========================================== --}}
            {{-- RIGHT UNIFIED MASTER SIDEBAR CONTAINER     --}}
            {{-- ========================================== --}}
            <div class="col-xl-4 col-lg-5 d-flex flex-column">

                <div class="master-sidebar-card shadow-sm">
                    
                    <div>
                        {{-- Interactive Mini Calendar Section --}}
                        <div class="calendar-section-inner">
                            <div class="calendar-header">
                                <i class="fa-solid fa-chevron-left calendar-nav" id="calPrev"></i>
                                <h6 id="calMonthYear" class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">Loading...</h6>
                                <i class="fa-solid fa-chevron-right calendar-nav" id="calNext"></i>
                            </div>
                            <div class="calendar-grid">
                                <div class="calendar-day-name">Mon</div>
                                <div class="calendar-day-name">Tue</div>
                                <div class="calendar-day-name">Wed</div>
                                <div class="calendar-day-name">Thu</div>
                                <div class="calendar-day-name">Fri</div>
                                <div class="calendar-day-name">Sat</div>
                                <div class="calendar-day-name">Sun</div>
                            </div>
                            <div class="calendar-grid mt-1" id="calDays">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    {{-- System Reminders & Info Section --}}
                    <div class="reminders-section-inner">
                        <div class="sub-title">System Reminders</div>
                        <div class="reminder-item">
                            <div class="reminder-icon text-primary"><i class="fa-regular fa-clock"></i></div>
                            <div class="reminder-info">
                                <h6>Office Hours</h6>
                                <p>Monday to Friday, 8:00 AM – 5:00 PM</p>
                            </div>
                        </div>
                        <div class="reminder-item">
                            <div class="reminder-icon text-success"><i class="fa-solid fa-file-circle-check"></i></div>
                            <div class="reminder-info">
                                <h6>Document Processing</h6>
                                <p>Requests processed within working hours</p>
                            </div>
                        </div>
                        <div class="reminder-item">
                            <div class="reminder-icon text-warning"><i class="fa-solid fa-bullhorn"></i></div>
                            <div class="reminder-info">
                                <h6>Online Tracking</h6>
                                <p>Monitor real-time status via tracking number</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODALS SECTION                             --}}
{{-- ========================================== --}}

{{-- RECEIPT MODALS LOOP --}}
@php
    $modalRequests = isset($recentRequests) ? $recentRequests->values() : collect();
@endphp
@for($m = 0; $m < $modalRequests->count(); $m++)
@php
    $request = $modalRequests[$m];
    $receiptNumber = str_replace('REQ', 'REC', $request->tracking_number);
    $isBusiness = str_contains(strtolower($request->document_type), 'business');
@endphp

<div class="modal fade" id="receiptModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <div class="modal-header bg-light border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                <h6 class="modal-title fw-bold text-dark mb-0">
                    <i class="fa-solid fa-file-invoice text-primary me-2"></i> Document Receipt
                </h6>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" onclick="downloadReceiptAsImage('receiptCard{{ $request->id }}', '{{ $receiptNumber }}')" class="btn btn-primary btn-sm rounded-3 shadow-sm px-3 fw-semibold">
                        <i class="fa-solid fa-download me-1"></i> Save Image
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div id="receiptCard{{ $request->id }}" class="bg-white">
                <div class="p-4 text-center border-bottom position-relative" style="border-bottom: 2px solid #000 !important; background-color: #f8f9fa;">
                    <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 50px; height: 50px; font-size: 22px;">
                            <i class="fa-solid fa-landmark"></i>
                        </div>
                        <div class="text-start">
                            <div class="text-uppercase text-muted fw-bold small" style="letter-spacing: 1px; font-size: 11px;">Republic of the Philippines</div>
                            <h5 class="fw-bold text-dark mb-0">Barangay Cogon East</h5>
                            <div class="text-muted small">E-Barangay System — Official Transaction Receipt</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 text-start">
                    <div class="row g-3 mb-4 pb-3 border-bottom">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">RECEIPT NUMBER</span>
                            <h5 class="fw-bold text-primary mb-0 font-monospace">{{ $receiptNumber }}</h5>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small d-block">DATE & TIME</span>
                            <span class="fw-bold text-dark">{{ $request->created_at->format('F d, Y — h:i A') }}</span>
                        </div>
                    </div>

                    <div class="bg-light rounded-3 p-3 mb-4 border">
                        <h6 class="fw-bold text-dark mb-3 text-uppercase small" style="letter-spacing: 0.5px;">Resident Information</h6>
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <span class="text-muted small">Full Name:</span>
                                <div class="fw-bold text-dark">
                                    {{ trim($request->first_name . ' ' . $request->middle_initial . ' ' . $request->last_name) ?: auth()->user()->name }}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small">Contact Info:</span>
                                <div class="fw-bold text-dark">{{ $request->resident_email ?? $request->contact_number ?? 'N/A' }}</div>
                            </div>
                            <div class="col-sm-6 mt-2">
                                <span class="text-muted small">User ID:</span>
                                <div class="fw-bold text-dark font-monospace">{{ $request->user_id ?? 'N/A' }}</div>
                            </div>
                            <div class="col-sm-6 mt-2">
                                <span class="text-muted small">Barangay:</span>
                                <div class="fw-bold text-dark">Barangay Cogon East</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark mb-3 text-uppercase small" style="letter-spacing: 0.5px;">Transaction Details</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-2 text-muted small fw-bold">TRACKING NO.</th>
                                    <th class="py-2 text-muted small fw-bold">DOCUMENT TYPE</th>
                                    <th class="py-2 text-muted small fw-bold text-end">FEE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold text-dark font-monospace py-3">{{ $request->tracking_number }}</td>
                                    <td class="py-3">
                                        <div class="fw-bold text-primary">{{ $request->document_type }}</div>
                                        @if(!empty($request->purpose))
                                            <div class="text-muted small" style="font-size: 11px;">Purpose: {{ $request->purpose }}</div>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-dark text-end py-3">
                                        @if($isBusiness)
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">TO BE SETTLED</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">FREE</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top pt-4 mt-4 text-center">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="text-start">
                                <div class="fw-bold text-dark small"><i class="fa-solid fa-shield-halved text-primary me-1"></i> Verified E-Barangay Transaction</div>
                                <div class="text-muted" style="font-size: 11px;">Generated automatically.</div>
                            </div>
                            <div class="text-end font-monospace text-muted small" style="font-size: 10px;">
                                SYS-VERIFIED: {{ md5($receiptNumber . $request->tracking_number) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-primary text-white text-center py-2 small fw-semibold">
                    Thank you for using the E-Barangay System — Barangay Cogon East
                </div>
            </div>
        </div>
    </div>
</div>
@endfor

{{-- SETUP PIN MODAL --}}
@if(auth()->check() && is_null(auth()->user()->security_pin))
    <div class="modal" id="setupPinModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 bg-primary bg-opacity-10 text-primary py-4 px-4 justify-content-center flex-column">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-shield-halved fs-4"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0">Set Up Your Security PIN</h5>
                    <p class="small text-muted mb-0 mt-2 text-center">Create a 6-digit PIN for faster, secure logins to the E-Barangay system.</p>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('resident.pin.setup') }}" method="POST" id="pinSetupForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Enter 6-Digit PIN</label>
                            <input type="password" name="security_pin" class="form-control form-control-lg text-center fw-bold rounded-3 bg-light border-0" 
                                   maxlength="6" inputmode="numeric" pattern="[0-9]*" 
                                   placeholder="● ● ● ● ● ●" style="letter-spacing: 8px;" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Confirm 6-Digit PIN</label>
                            <input type="password" name="security_pin_confirmation" class="form-control form-control-lg text-center fw-bold rounded-3 bg-light border-0" 
                                   maxlength="6" inputmode="numeric" pattern="[0-9]*" 
                                   placeholder="● ● ● ● ● ●" style="letter-spacing: 8px;" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm">
                            <i class="fa-solid fa-lock me-2"></i> Save Secure PIN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var pinModal = new bootstrap.Modal(document.getElementById('setupPinModal'));
            pinModal.show();
        });
    </script>
@endif

<!-- JS Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
// Receipt Download logic
function downloadReceiptAsImage(targetId, receiptNumber) {
    const receiptElement = document.getElementById(targetId);
    html2canvas(receiptElement, { scale: 2, useCORS: true }).then(canvas => {
        const image = canvas.toDataURL("image/png");
        const link = document.createElement('a');
        link.download = 'Receipt-' + receiptNumber + '.png';
        link.href = image;
        link.click();
    });
}

// Dynamic Calendar logic
document.addEventListener('DOMContentLoaded', function() {
    const calMonthYear = document.getElementById('calMonthYear');
    const calDays = document.getElementById('calDays');
    let currDate = new Date(); 

    function renderCalendar() {
        currDate.setDate(1); 
        const month = currDate.getMonth();
        const year = currDate.getFullYear();
        
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        calMonthYear.textContent = `${monthNames[month]} ${year}`;

        let firstDayIndex = currDate.getDay() - 1;
        if (firstDayIndex === -1) firstDayIndex = 6; 

        const lastDay = new Date(year, month + 1, 0).getDate();
        const prevLastDay = new Date(year, month, 0).getDate();
        
        const today = new Date();
        let daysHtml = "";

        // Prev month filler days
        for (let x = firstDayIndex; x > 0; x--) {
            daysHtml += `<div class="calendar-day muted">${prevLastDay - x + 1}</div>`;
        }

        // Current month days
        for (let i = 1; i <= lastDay; i++) {
            let classes = "calendar-day";
            if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                classes += " active";
            }
            daysHtml += `<div class="${classes}">${i}</div>`;
        }

        // Next month filler days
        const totalCells = firstDayIndex + lastDay;
        const remainingCells = (Math.ceil(totalCells / 7) * 7) - totalCells;
        for (let j = 1; j <= remainingCells; j++) {
            daysHtml += `<div class="calendar-day muted">${j}</div>`;
        }

        calDays.innerHTML = daysHtml;
    }

    renderCalendar();

    document.getElementById('calPrev').addEventListener('click', () => {
        currDate.setMonth(currDate.getMonth() - 1);
        renderCalendar();
    });
    
    document.getElementById('calNext').addEventListener('click', () => {
        currDate.setMonth(currDate.getMonth() + 1);
        renderCalendar();
    });
});
</script>
@endsection