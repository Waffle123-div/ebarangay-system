<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Barangay System | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    
    <style>
        :root {
            --sidebar-width: 260px;
        }

        body {
            background-color: var(--color-bg);
            font-family: var(--font-body);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #wrapper { display: flex; width: 100%; }
        #sidebar-wrapper {
            min-height: 100vh;
            width: var(--sidebar-width);
            background: var(--color-surface);
            border-right: 1px solid var(--color-border);
            position: fixed;
            z-index: 1000;
        }

        .sidebar-heading {
            padding: 2.5rem 1.5rem 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--color-border);
        }
        
        .sidebar-heading .brand-icon {
            font-size: 2rem;
            margin-right: 0.75rem;
            color: var(--color-primary);
        }

        .sidebar-heading h4 { font-size: 1.1rem; font-weight: 700; margin: 0; color: var(--color-text); font-family: var(--font-heading); }
        .sidebar-heading p { font-size: 0.8rem; margin: 0; color: var(--color-text-light); }

        .list-group-item {
            border: none; padding: 1rem 1.5rem; color: var(--color-text-light);
            font-weight: 600; font-size: 0.9rem; border-left: 4px solid transparent;
            transition: var(--transition-fast);
        }
        
        .list-group-item i { margin-right: 0.75rem; width: 20px; text-align: center; }
        .list-group-item:hover { background-color: var(--color-primary-50); color: var(--color-primary); }
        .list-group-item.active {
            background-color: var(--color-primary-50);
            color: var(--color-primary);
            border-left: 4px solid var(--color-primary);
        }

        /* Main Content Styling */
        #page-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
        }

        /* Top Header Styling */
        .admin-header {
            background-color: var(--color-surface); padding: 1rem 2rem;
            box-shadow: var(--shadow-md);
            border-bottom: 1px solid var(--color-border);
        }
        
        .user-dropdown { font-weight: 600; color: var(--color-text-light); }

        /* General Card & Table Styling */
        .card { border: none; box-shadow: var(--shadow-lg); border-radius: var(--radius-lg); }
        .card-header { background-color: var(--color-surface); border-bottom: 1px solid var(--color-border); padding: 1.5rem; font-weight: 700; color: var(--color-text); font-family: var(--font-heading); }
        .table > :not(caption) > * > * { padding: 1rem 1.5rem; }
        .table thead th { font-size: 0.75rem; font-weight: 700; color: var(--color-text-light); background-color: var(--color-bg-alt); border-bottom: none; text-transform: uppercase; letter-spacing: 0.5px; }
        .table tbody td { font-size: 0.9rem; color: var(--color-text); vertical-align: middle; }
        .badge { font-weight: 700; padding: 0.5em 0.75em; border-radius: 30px; }

        /* Headings use Outfit */
        h1, h2, h3, h4, h5, h6, .header-title { font-family: var(--font-heading); }

        /* ============================= */
        /* NOTIFICATION BELL             */
        /* ============================= */
        .notif-bell-btn {
            position: relative;
            width: 42px; height: 42px;
            border-radius: 50%;
            border: 1px solid var(--color-border, #e5e7eb);
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background 0.15s;
            color: #6b7280;
            text-decoration: none;
        }
        .notif-bell-btn:hover { background: #f3f4f6; color: #4f46e5; }

        .notif-badge {
            position: absolute;
            top: -3px; right: -3px;
            background: #ef4444;
            color: #fff;
            font-size: 10px; font-weight: 700;
            width: 18px; height: 18px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff;
            line-height: 1;
        }
        .notif-badge.d-none { display: none !important; }

        .notif-dropdown {
            position: absolute;
            top: calc(100% + 12px); right: 0;
            width: 360px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            z-index: 2000;
            display: none;
            overflow: hidden;
        }
        .notif-dropdown.open { display: block; }

        .notif-header {
            padding: 16px 20px 12px;
            border-bottom: 1px solid #f3f4f6;
            display: flex; align-items: center; justify-content: space-between;
        }
        .notif-header h6 { font-size: 14px; font-weight: 700; margin: 0; color: #111827; }

        .notif-list { max-height: 340px; overflow-y: auto; }

        .notif-item {
            padding: 14px 20px;
            border-bottom: 1px solid #f9fafb;
            display: flex; align-items: flex-start; gap: 12px;
            transition: background 0.1s;
            text-decoration: none;
            color: inherit;
        }
        .notif-item:hover { background: #f9fafb; }
        .notif-item.unread { background: #eef2ff; }
        .notif-item.unread:hover { background: #e0e7ff; }

        .notif-icon {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 14px;
        }
        .notif-icon.type-new_request    { background: #dbeafe; color: #2563eb; }
        .notif-icon.type-new_registration { background: #dcfce7; color: #16a34a; }
        .notif-icon.type-info           { background: #f3f4f6; color: #6b7280; }

        .notif-msg { font-size: 13px; color: #374151; line-height: 1.5; }
        .notif-time { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        .notif-footer {
            padding: 12px 20px;
            border-top: 1px solid #f3f4f6;
            text-align: center;
        }
        .notif-footer a { font-size: 13px; font-weight: 600; color: #4f46e5; text-decoration: none; }
        .notif-footer a:hover { color: #3730a3; }

        .notif-empty {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
        }
        .notif-empty i { font-size: 32px; display: block; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <i class="fa-solid fa-circle-user brand-icon"></i>
                <div>
                    <h4>Admin Panel</h4>
                    <p>E-Barangay System</p>
                </div>
            </div>
            <div class="list-group list-group-flush pt-3">
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-th-large"></i> Dashboard
                </a>
                <a href="{{ route('admin.requests') }}" class="list-group-item list-group-item-action {{ Route::is('admin.requests') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-alt"></i> Requests
                </a>
                <a href="{{ route('admin.users') }}" class="list-group-item list-group-item-action {{ Route::is('admin.users') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Users
                </a>
                
                <a href="{{ route('admin.approvals') }}" class="list-group-item list-group-item-action {{ Route::is('admin.approvals') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-check"></i> Approvals
                </a>

                <a href="{{ route('admin.reports') }}" class="list-group-item list-group-item-action {{ Route::is('admin.reports') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-bar"></i> Reports
                </a>

                <a href="{{ route('admin.activity-logs') }}" class="list-group-item list-group-item-action {{ Route::is('admin.activity-logs') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Activity Logs
                </a>

                <a href="{{ route('admin.settings') }}" class="list-group-item list-group-item-action {{ Route::is('admin.settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-cog"></i> Settings
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg admin-header d-flex justify-content-between align-items-center">
                <div class="header-titles"></div>
                <div class="d-flex align-items-center gap-3">

                    {{-- ===== NOTIFICATION BELL ===== --}}
                    <div class="position-relative" id="notif-wrapper">
                        <button class="notif-bell-btn" id="notif-bell" aria-label="Notifications" title="Notifications">
                            <i class="fa-solid fa-bell" style="font-size:16px;"></i>
                            <span class="notif-badge d-none" id="notif-badge">0</span>
                        </button>

                        <div class="notif-dropdown" id="notif-dropdown">
                            <div class="notif-header">
                                <h6><i class="fa-solid fa-bell me-2 text-primary" style="font-size:13px;"></i> Notifications</h6>
                                <button class="btn btn-link btn-sm text-muted text-decoration-none p-0" id="mark-all-read" style="font-size:12px;">
                                    Mark all as read
                                </button>
                            </div>
                            <div class="notif-list" id="notif-list">
                                <div class="notif-empty">
                                    <i class="fa-regular fa-bell-slash text-light"></i>
                                    You're all caught up!
                                </div>
                            </div>
                            <div class="notif-footer">
                                <a href="{{ route('admin.activity-logs') }}">View Activity Logs →</a>
                            </div>
                        </div>
                    </div>
                    {{-- ===== END NOTIFICATION BELL ===== --}}

                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-3 text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-end d-none d-md-block">
                                <div class="fw-bold text-dark" style="font-size: 14px; line-height: 1.2;">{{ auth()->user()->name }}</div>
                                <div class="text-muted" style="font-size: 12px;">Admin</div>
                            </div>
                            @php
                                $nameParts = explode(' ', trim(auth()->user()->name));
                                $initials = isset($nameParts[1]) 
                                    ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) 
                                    : strtoupper(substr($nameParts[0], 0, 2));
                            @endphp

                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 45px; height: 45px; font-size: 16px;">
                                {{ $initials }}
                            </div>
                        </a>
                        
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-4 mt-3 px-2 py-2" style="min-width: 220px;">
                            <li><h6 class="dropdown-header text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 1px;">My Account</h6></li>
                            <li>
                                <a class="dropdown-item rounded-3 d-flex align-items-center gap-3 py-2 fw-semibold" href="{{ route('admin.profile') }}">
                                    <i class="fa-regular fa-user text-primary" style="width: 20px;"></i> Profile Settings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-3 d-flex align-items-center gap-3 py-2 fw-semibold" href="{{ route('admin.settings') }}">
                                    <i class="fa-solid fa-cog text-secondary" style="width: 20px;"></i> Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <form action="{{ route('admin.logout') }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item rounded-3 d-flex align-items-center gap-3 py-2 fw-semibold text-danger bg-transparent border-0 w-100 text-start">
                                        <i class="fa-solid fa-arrow-right-from-bracket" style="width: 20px;"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            @yield('content')

            <footer class="text-center py-4 text-muted">
                <small>&copy; 2026 E-Barangay System</small>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // =============================================
    // ADMIN NOTIFICATION BELL SYSTEM
    // =============================================
    (function () {
        const bell         = document.getElementById('notif-bell');
        const dropdown     = document.getElementById('notif-dropdown');
        const badge        = document.getElementById('notif-badge');
        const list         = document.getElementById('notif-list');
        const markAllBtn   = document.getElementById('mark-all-read');
        const csrfToken    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const typeIcons = {
            'new_request':      'fa-file-lines',
            'new_registration': 'fa-user-plus',
            'info':             'fa-info',
        };

        function renderNotifications(data) {
            // Badge
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }

            // List
            if (!data.notifications || data.notifications.length === 0) {
                list.innerHTML = `
                    <div class="notif-empty">
                        <i class="fa-regular fa-bell-slash text-light"></i>
                        You're all caught up!
                    </div>`;
                return;
            }

            list.innerHTML = data.notifications.map(n => {
                const icon = typeIcons[n.type] || 'fa-bell';
                return `
                <a href="${n.link}" class="notif-item ${n.read ? '' : 'unread'}" data-id="${n.id}" onclick="markOneRead(event, '${n.id}', '${n.link}')">
                    <div class="notif-icon type-${n.type}">
                        <i class="fa-solid ${icon}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="notif-msg">${n.message}</div>
                        <div class="notif-time">${n.created_at}</div>
                    </div>
                    ${!n.read ? '<div class="flex-shrink-0"><span style="width:8px;height:8px;background:#4f46e5;border-radius:50%;display:inline-block;margin-top:4px;"></span></div>' : ''}
                </a>`;
            }).join('');
        }

        function fetchNotifications() {
            fetch('{{ route("admin.notifications.get") }}')
                .then(r => r.json())
                .then(renderNotifications)
                .catch(() => {});
        }

        window.markOneRead = function(e, id, link) {
            e.preventDefault();
            fetch('{{ route("admin.notifications.read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ notification_id: id }),
            }).then(() => {
                window.location.href = link;
            });
        };

        markAllBtn.addEventListener('click', function() {
            fetch('{{ route("admin.notifications.read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({}),
            }).then(() => {
                fetchNotifications();
            });
        });

        // Toggle dropdown
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('open');
            if (dropdown.classList.contains('open')) {
                fetchNotifications();
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!document.getElementById('notif-wrapper').contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        // Initial fetch + poll every 30 seconds
        fetchNotifications();
        setInterval(fetchNotifications, 30000);
    })();
    </script>
</body>
</html>