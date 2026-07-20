<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-cog"></i> Settings
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg admin-header d-flex justify-content-between align-items-center">
                <div class="header-titles"></div>
                <div class="d-flex align-items-center">
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
</body>
</html>