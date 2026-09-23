<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Portal - E-Barangay Cogon East</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    
    <style>
        :root {
            --sidebar-width: 250px;
        }

        body { font-family: var(--font-body); background-color: var(--color-bg); overflow-x: hidden; }
        
        #resident-wrapper { display: flex; width: 100%; min-height: 100vh; }
        
        /* Resident Sidebar Styling */
        #resident-sidebar-wrapper {
            min-height: 100vh;
            width: var(--sidebar-width);
            background: var(--color-surface);
            border-right: 1px solid var(--color-border);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem 1.5rem 1rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--color-border);
        }

        .resident-sidebar-list .list-group-item {
            border: none;
            padding: 0.9rem 1.5rem;
            color: var(--color-text-light);
            font-weight: 600;
            font-size: 0.9rem;
            border-left: 4px solid transparent;
            transition: var(--transition-fast);
            background: transparent;
        }
        
        .resident-sidebar-list .list-group-item i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .resident-sidebar-list .list-group-item:hover {
            background-color: var(--color-primary-50);
            color: var(--color-primary);
        }

        .resident-sidebar-list .list-group-item.active {
            background-color: var(--color-primary-50);
            color: var(--color-primary);
            border-left: 4px solid var(--color-primary);
        }

        /* Content Area */
        #resident-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .top-navbar {
            background: var(--color-surface);
            padding: 12px 30px;
            border-bottom: 1px solid var(--color-border);
        }

        .main-content { padding: 20px; flex-grow: 1; }
        .card-custom { border: 0; border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); }
        .header-title { font-weight: 700; color: var(--color-text); font-family: var(--font-heading); }
        .header-subtitle { color: var(--color-text-light); font-size: 14px; }
        .btn-link { text-decoration: none; }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }

        /* Responsive Mobile Drawer */
        @media (max-width: 991.98px) {
            #resident-sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #resident-wrapper.toggled #resident-sidebar-wrapper {
                margin-left: 0;
            }
            #resident-content-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div id="resident-wrapper">
        {{-- Resident Sidebar --}}
        <div id="resident-sidebar-wrapper">
            <div class="sidebar-brand d-flex align-items-center gap-2">
                <div class="bg-primary rounded-3 d-flex justify-content-center align-items-center" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-hotel text-white" style="font-size: 16px;"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 14px; line-height: 1.1;">E-Barangay</h5>
                    <small class="text-muted" style="font-size: 11px;">Resident Portal</small>
                </div>
            </div>

            <div class="list-group list-group-flush pt-3 resident-sidebar-list">
                <a href="{{ route('welcome') }}" class="list-group-item list-group-item-action {{ Route::is('welcome') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Welcome
                </a>
                <a href="{{ route('resident.dashboard') }}" class="list-group-item list-group-item-action {{ Route::is('resident.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-th-large"></i> Dashboard
                </a>
                <a href="{{ route('resident.request.create') }}" class="list-group-item list-group-item-action {{ Route::is('resident.request.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-circle-plus"></i> Request Document
                </a>
                <a href="{{ route('resident.request.track') }}" class="list-group-item list-group-item-action {{ Route::is('resident.request.track') ? 'active' : '' }}">
                    <i class="fa-solid fa-magnifying-glass"></i> Track Requests
                </a>
                <a href="{{ route('resident.concerns') }}" class="list-group-item list-group-item-action {{ Route::is('resident.concerns') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i> Community Concern
                </a>
                <a href="{{ route('resident.activity-logs') }}" class="list-group-item list-group-item-action {{ Route::is('resident.activity-logs') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Activity Logs
                </a>
                <a href="{{ route('resident.settings') }}" class="list-group-item list-group-item-action {{ Route::is('resident.settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-sliders"></i> Settings
                </a>
            </div>
        </div>

        {{-- Main Page Content Wrapper --}}
        <div id="resident-content-wrapper">
            <nav class="top-navbar d-flex justify-content-between align-items-center sticky-top shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light btn-sm border-0 d-lg-none" id="menu-toggle" aria-label="Toggle Navigation">
                        <i class="fa-solid fa-bars fs-5"></i>
                    </button>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 14px; line-height: 1;">Barangay Cogon East</div>
                        <div class="text-muted" style="font-size: 11px;">E-Barangay Citizen Services</div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-4">
                    <div class="dropdown">
                        <a class="text-decoration-none position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" onclick="markNotificationsAsRead()">
                            <i class="fa-regular fa-bell text-muted" style="font-size: 1.2rem; cursor: pointer;"></i>
                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                <span class="position-absolute translate-middle badge rounded-pill bg-danger shadow-sm" id="notification-badge" style="top: 0px; start: 90%; font-size: 0.6rem;">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0 mt-2" aria-labelledby="notificationDropdown" style="width: 320px; border-radius: 12px; overflow: hidden;">
                            <div class="bg-primary text-white px-3 py-2 fw-bold d-flex justify-content-between align-items-center">
                                <span>Notifications</span>
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                @if(auth()->check())
                                    @forelse(auth()->user()->notifications as $notification)
                                        <div class="px-3 py-3 border-bottom {{ $notification->read_at === null ? 'bg-light' : 'bg-white' }}">
                                            <div class="fw-bold text-dark" style="font-size: 14px;">
                                                <i class="fa-solid fa-circle-check text-success me-1"></i> {{ $notification->data['document_type'] ?? 'Document Update' }}
                                            </div>
                                            <div class="text-muted mt-1" style="font-size: 13px;">{{ $notification->data['message'] ?? 'The status of your document has been updated.' }}</div>
                                            <div class="mt-2 text-primary" style="font-size: 11px; font-weight: 600;">
                                                TRK: {{ $notification->data['tracking_number'] ?? 'N/A' }} • {{ $notification->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-3 py-4 text-center text-muted small">
                                            <i class="fa-regular fa-bell-slash fa-2x mb-2 text-light"></i><br>
                                            No new notifications
                                        </div>
                                    @endforelse
                                @endif
                            </div>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-decoration-none" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="bg-light text-muted rounded-circle d-flex justify-content-center align-items-center fw-bold" style="width: 35px; height: 35px; font-size: 12px;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="text-end d-none d-sm-block">
                                <div class="fw-bold text-dark" style="font-size: 13px;">{{ auth()->user()?->name ?? 'Resident' }}</div>
                                <div class="text-muted" style="font-size: 11px;">Resident</div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-3">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('resident.settings') }}">
                                    <i class="fa-solid fa-sliders text-muted"></i>
                                    Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('resident.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="background: none; border: none; width: 100%; text-align: left;">
                                        <i class="fa-solid fa-right-from-bracket me-2 text-muted"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="main-content">
                @yield('content')
            </div>
        </div>
    </div>

    {{-- UPDATED SLEEK SESSION LOCK SCREEN --}}
    @if(session('is_locked'))
        <div class="modal d-flex align-items-center justify-content-center" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(10px); position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 10000;" tabindex="-1">
            <div class="modal-dialog m-0" style="max-width: 420px; width: 100%; padding: 20px;">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <!-- Top accent bar -->
                    <div style="height: 5px; background: #dc3545; width: 100%;"></div>

                    <div class="modal-body p-5 text-center bg-white">
                        <!-- Polished Icon -->
                        <div class="d-flex justify-content-center mb-4">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 76px; height: 76px; border: 4px solid #fff; box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.15) !important;">
                                <i class="fa-solid fa-lock" style="font-size: 28px;"></i>
                            </div>
                        </div>

                        <!-- Refined Text -->
                        <h4 class="fw-bold text-dark mb-2" style="letter-spacing: -0.5px;">Session Locked</h4>
                        <p class="text-muted mb-4 px-2" style="font-size: 14px; line-height: 1.5;">Your session timed out due to inactivity. Please enter your 6-digit PIN to resume.</p>

                        <!-- Better Error Message -->
                        @error('security_pin')
                            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small py-2 px-3 rounded-3 mb-4 fw-semibold d-flex align-items-center justify-content-center gap-2" style="font-size: 13px;">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $message }}
                            </div>
                        @enderror

                        <!-- Upgraded Forms -->
                        <form action="{{ route('resident.session.unlock') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <input type="password" name="security_pin"
                                       class="form-control text-center fw-bold transition-all"
                                       maxlength="6" inputmode="numeric" pattern="[0-9]*"
                                       placeholder="••••••"
                                       style="letter-spacing: 16px; font-size: 1.75rem; background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; height: 64px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); color: #334155; transition: all 0.2s ease;"
                                       required autofocus
                                       onfocus="this.style.borderColor='#dc3545'; this.style.backgroundColor='#ffffff'; this.style.boxShadow='0 0 0 4px rgba(220, 53, 69, 0.15)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='#f8fafc'; this.style.boxShadow='inset 0 2px 4px rgba(0,0,0,0.02)';">
                            </div>
                            <button type="submit" class="btn btn-danger w-100 py-3 rounded-3 fw-bold mb-3" style="font-size: 15px; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2); transition: all 0.2s ease;" onmouseover="this.style.transform='translateY(-1px)';" onmouseout="this.style.transform='translateY(0)';">
                                <i class="fa-solid fa-unlock-keyhole me-2"></i> Unlock Session
                            </button>
                        </form>

                        <form action="{{ route('resident.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light w-100 py-2 rounded-3 fw-bold text-muted" style="border: 1.5px solid #e2e8f0; font-size: 14px; background: #ffffff; transition: all 0.2s ease;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#334155';" onmouseout="this.style.background='#ffffff'; this.style.color='';">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>document.body.style.overflow = 'hidden';</script>
    @else
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const timeoutThreshold = 600000;
                let lockTimer;
                let lockRequestSent = false;
                let lastActivity = 0;

                function lockSession() {
                    if (lockRequestSent) return;
                    lockRequestSent = true;

                    fetch("{{ route('resident.session.lock') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }).then(response => {
                        if (response.ok) window.location.reload();
                    }).catch(() => {
                        lockRequestSent = false;
                        resetIdleTimer();
                    });
                }

                function resetIdleTimer() {
                    const now = Date.now();
                    if (now - lastActivity < 1000) return;
                    lastActivity = now;
                    clearTimeout(lockTimer);
                    lockTimer = setTimeout(lockSession, timeoutThreshold);
                }

                ['mousemove', 'click', 'keydown', 'scroll', 'touchstart'].forEach(eventName => {
                    document.addEventListener(eventName, resetIdleTimer, { passive: eventName === 'scroll' || eventName === 'touchstart' });
                });

                resetIdleTimer();
            });
        </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function markNotificationsAsRead() {
            let badge = document.getElementById('notification-badge');
            if (badge && badge.style.display !== 'none') {
                badge.style.display = 'none';
                fetch('{{ route("resident.notifications.read") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' }
                }).catch(error => console.error('Error marking notifications read:', error));
            }
        }
    </script>

    <!-- E-BARANGAY AI FISHERMAN CHATBOT -->
    @if(!session('is_locked'))
        <div id="ai-chat-container">
            <div id="ai-chat-window">
                <div class="chat-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white rounded-circle d-flex justify-content-center align-items-center p-1 shadow-sm" style="width: 55px; height: 55px;">
                            <img src="{{ asset('images/fisherman.png') }}" alt="Istong" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <div class="header-titles">
                            <h5 class="m-0 fw-bold">Istong</h5>
                            <small style="opacity: 0.9;">Barangay Fisherman Assistant</small>
                        </div>
                    </div>
                    <button id="close-chat-btn">&times;</button>
                </div>
                <div class="chat-disclaimer">
                    <small><i class="fa-solid fa-circle-info me-1"></i> Information only. No official documents issued here.</small>
                </div>
                <div id="chat-body">
                    <div class="message bot-message">
                        Kumusta! Ako ang inyong Fisherman Assistant nga si Istong. Unsa may matabang nako nimo karon kabahin sa atong barangay?
                    </div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="chat-input" placeholder="Pangutana dinhi..." autocomplete="off">
                    <button id="send-chat-btn">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>

            <button id="ai-toggle-btn">
                <div class="ai-badge">
                    <i class="fa-solid fa-robot"></i> AI Chat
                </div>
                <img src="{{ asset('images/fisherman.png') }}" alt="Fisherman Mascot" class="mascot-img">
            </button>
        </div>
    @endif

    <style>
        #ai-chat-container { position: fixed; bottom: 25px; right: 25px; z-index: 9999; font-family: var(--font-body); }
        
        #ai-toggle-btn { 
            background-color: transparent; 
            border: none; 
            border-radius: 0; 
            width: 110px; 
            height: 110px; 
            cursor: grab; 
            box-shadow: none; 
            transition: transform 0.1s ease; 
            float: right; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 0; 
            overflow: visible; 
            position: relative; 
            animation: floatMascot 3s ease-in-out infinite; 
        }
        
        #ai-toggle-btn:active { cursor: grabbing; animation: none; } 
        #ai-toggle-btn:hover { transform: scale(1.05); }

        .ai-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background: var(--gradient-primary);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            border: 2px solid white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 5px;
            pointer-events: none;
            z-index: 10;
        }

        .mascot-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            pointer-events: none;
            filter: drop-shadow(0 6px 6px rgba(0,0,0,0.25)); 
        }

        @keyframes floatMascot {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
        
        /* CHAT WINDOW DESIGN UPGRADES */
        #ai-chat-window { 
            display: none; 
            width: 360px; 
            height: 520px; 
            background-color: #f4f6f9; 
            border-radius: 1.2rem; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
            margin-bottom: 15px; 
            flex-direction: column; 
            border: 1px solid rgba(0,0,0,0.05); 
            overflow: hidden; 
        }
        
        .chat-header { 
            background: var(--gradient-primary); 
            color: #ffffff;
            padding: 15px 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: none; 
        }
        
        .header-titles h5 { font-size: 16px; color: #ffffff; margin-bottom: 2px; letter-spacing: 0.3px; }
        .header-titles small { color: #ffffff; font-size: 12px; }
        
        #close-chat-btn { 
            background: rgba(255, 255, 255, 0.2); 
            border: none; 
            color: #ffffff; 
            font-size: 20px; 
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer; 
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }
        #close-chat-btn:hover { background: rgba(255, 255, 255, 0.4); }
        
        .chat-disclaimer { 
            background-color: #ffffff; 
            color: var(--color-text-light); 
            font-size: 11px; 
            padding: 8px 20px; 
            border-bottom: 1px solid #e9ecef; 
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            z-index: 2;
        }
        
        #chat-body { 
            flex-grow: 1; 
            padding: 20px; 
            overflow-y: auto; 
            display: flex; 
            flex-direction: column; 
            gap: 15px; 
        }
        
        .message { 
            max-width: 85%; 
            padding: 12px 16px; 
            font-size: 14px; 
            line-height: 1.5; 
            box-shadow: 0 1px 2px rgba(0,0,0,0.08); 
        }
        
        .bot-message { 
            background-color: #ffffff; 
            color: var(--color-text); 
            align-self: flex-start; 
            border-radius: 12px 12px 12px 4px; 
            border: 1px solid #e9ecef;
        }
        
        .user-message { 
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
            color: white; 
            align-self: flex-end; 
            border-radius: 12px 12px 4px 12px; 
        }
        
        .chat-input-area { 
            padding: 15px; 
            background-color: #ffffff; 
            border-top: 1px solid #e9ecef; 
            display: flex; 
            gap: 10px; 
            z-index: 2;
        }
        
        #chat-input { 
            flex-grow: 1; 
            background-color: #f4f6f9; 
            border: 1px solid transparent; 
            color: var(--color-text); 
            border-radius: 25px; 
            padding: 12px 20px; 
            outline: none; 
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        #chat-input:focus { 
            background-color: #ffffff;
            border-color: var(--color-primary-light); 
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1);
        }
        
        #chat-input::placeholder { color: #adb5bd; }
        
        #send-chat-btn { 
            background: var(--gradient-primary); 
            color: white; 
            border: none; 
            border-radius: 50%; 
            width: 45px; 
            height: 45px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            cursor: pointer; 
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 10px rgba(30, 64, 175, 0.3);
            font-size: 16px;
        }
        
        #send-chat-btn:hover { 
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(30, 64, 175, 0.4);
        }
    </style>

    @if(!session('is_locked'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('ai-toggle-btn');
            const closeBtn = document.getElementById('close-chat-btn');
            const chatWindow = document.getElementById('ai-chat-window');
            const sendBtn = document.getElementById('send-chat-btn');
            const chatInput = document.getElementById('chat-input');
            const chatBody = document.getElementById('chat-body');
            const chatContainer = document.getElementById('ai-chat-container');

            let isDragging = false;
            let hasDragged = false;
            let startX, startY, initialLeft, initialTop;

            function dragStart(e) {
                isDragging = true;
                hasDragged = false; 
                
                if (e.type === 'touchstart') {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                } else {
                    startX = e.clientX;
                    startY = e.clientY;
                }

                const rect = chatContainer.getBoundingClientRect();
                initialLeft = rect.left;
                initialTop = rect.top;

                chatContainer.style.bottom = 'auto';
                chatContainer.style.right = 'auto';
                chatContainer.style.left = initialLeft + 'px';
                chatContainer.style.top = initialTop + 'px';
            }

            function drag(e) {
                if (!isDragging) return;
                
                let currentX, currentY;
                if (e.type === 'touchmove') {
                    currentX = e.touches[0].clientX;
                    currentY = e.touches[0].clientY;
                    e.preventDefault(); 
                } else {
                    currentX = e.clientX;
                    currentY = e.clientY;
                }

                const diffX = currentX - startX;
                const diffY = currentY - startY;

                if (Math.abs(diffX) > 3 || Math.abs(diffY) > 3) {
                    hasDragged = true;
                }

                let newLeft = initialLeft + diffX;
                let newTop = initialTop + diffY;

                const maxLeft = window.innerWidth - toggleBtn.offsetWidth;
                const maxTop = window.innerHeight - toggleBtn.offsetHeight;
                
                newLeft = Math.max(0, Math.min(newLeft, maxLeft));
                newTop = Math.max(0, Math.min(newTop, maxTop));

                chatContainer.style.left = newLeft + 'px';
                chatContainer.style.top = newTop + 'px';
            }

            function dragEnd(e) {
                isDragging = false;
            }

            toggleBtn.addEventListener('mousedown', dragStart);
            toggleBtn.addEventListener('touchstart', dragStart, {passive: false});

            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag, {passive: false});

            document.addEventListener('mouseup', dragEnd);
            document.addEventListener('touchend', dragEnd);

            toggleBtn.addEventListener('click', (e) => { 
                if (hasDragged) {
                    e.preventDefault();
                    return;
                }

                chatContainer.dataset.draggedLeft = chatContainer.style.left;
                chatContainer.dataset.draggedTop = chatContainer.style.top;

                chatWindow.style.display = 'flex'; 
                toggleBtn.style.display = 'none'; 
                
                if (chatContainer.style.left && chatContainer.style.left !== 'auto') {
                    const rect = chatWindow.getBoundingClientRect();
                    let currentLeft = chatContainer.offsetLeft;
                    let currentTop = chatContainer.offsetTop;
                    let needsNudge = false;

                    if (rect.right > window.innerWidth) {
                        currentLeft -= (rect.right - window.innerWidth + 20);
                        needsNudge = true;
                    }
                    if (rect.left < 0) {
                        currentLeft += Math.abs(rect.left) + 20;
                        needsNudge = true;
                    }
                    if (rect.bottom > window.innerHeight) {
                        currentTop -= (rect.bottom - window.innerHeight + 20);
                        needsNudge = true;
                    }
                    if (rect.top < 0) {
                        currentTop += Math.abs(rect.top) + 20;
                        needsNudge = true;
                    }

                    if (needsNudge) {
                        chatContainer.style.left = Math.max(0, currentLeft) + 'px';
                        chatContainer.style.top = Math.max(0, currentTop) + 'px';
                    }
                }
            });

            closeBtn.addEventListener('click', () => { 
                chatWindow.style.display = 'none'; 
                toggleBtn.style.display = 'flex'; 
                
                if (chatContainer.dataset.draggedLeft && chatContainer.dataset.draggedLeft !== 'auto') {
                    chatContainer.style.left = chatContainer.dataset.draggedLeft;
                    chatContainer.style.top = chatContainer.dataset.draggedTop;
                }
            });

            let conversationHistory = [];

            async function sendMessage() {
                const text = chatInput.value.trim();
                if (!text) return;
                
                appendMessage(text, 'user-message');
                chatInput.value = '';
                
                conversationHistory.push({ role: 'user', content: text });
                
                if (conversationHistory.length > 10) {
                    conversationHistory = conversationHistory.slice(conversationHistory.length - 10);
                }

                const loadingId = appendLoading();
                
                try {
                    const response = await fetch("{{ route('chatbot.message') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ history: conversationHistory })
                    });
                    
                    const data = await response.json();
                    document.getElementById(loadingId).remove();
                    
                    appendMessage(data.reply, 'bot-message');
                    
                    if (data.reply !== "Error: Could not connect to the server." && !data.reply.includes("Pasensya")) {
                        conversationHistory.push({ role: 'assistant', content: data.reply });
                    }
                    
                } catch (error) {
                    document.getElementById(loadingId).remove();
                    appendMessage("Pasensya, naputol ang koneksyon. Palihug sulayi usab.", 'bot-message');
                    
                    conversationHistory.pop();
                }
            }

            sendBtn.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', function (e) { if (e.key === 'Enter') sendMessage(); });
            
            function appendMessage(text, className) {
                const msgDiv = document.createElement('div');
                msgDiv.className = `message ${className}`;
                msgDiv.innerText = text;
                chatBody.appendChild(msgDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
            }
            
            function appendLoading() {
                const msgDiv = document.createElement('div');
                msgDiv.className = `message bot-message`;
                msgDiv.id = `loading-${Date.now()}`;
                msgDiv.innerText = 'Naghunahuna...';
                chatBody.appendChild(msgDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
                return msgDiv.id;
            }
        });
    </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuToggle = document.getElementById("menu-toggle");
            const residentWrapper = document.getElementById("resident-wrapper");
            if (menuToggle && residentWrapper) {
                menuToggle.addEventListener("click", function(e) {
                    e.preventDefault();
                    residentWrapper.classList.toggle("toggled");
                });
            }
        });
    </script>
</body>
</html>