@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">

    <style>
        /* Responsive Table & Sticky Actions */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .table-responsive::-webkit-scrollbar { height: 7px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .table-responsive::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }

        .table-users {
            width: 100%;
            min-width: 900px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-users thead th.col-actions,
        .table-users tbody td.col-actions {
            white-space: nowrap;
            text-align: right;
            padding-right: 1.25rem !important;
        }

        /* Mobile Status Pills Scroll & Card Layout */
        @media (max-width: 767.98px) {
            .users-nav-pills {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding-bottom: 8px;
                -webkit-overflow-scrolling: touch;
            }
            .users-nav-pills .nav-link {
                white-space: nowrap;
                padding: 7px 15px;
                font-size: 13px;
                flex-shrink: 0;
            }
            .mobile-user-card {
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                margin-bottom: 12px;
                padding: 14px;
            }
        }
    </style>

    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> > Users</div>
        <h2 class="header-title" style="font-weight: 400;">User Management</h2>
        <p class="header-subtitle mb-0">Manage user accounts and permissions in the system</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- KPI Cards (2 on mobile, 4 on desktop) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1" style="font-size: 11px;">Total Users</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalUsers }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1" style="font-size: 11px;">Active Users</div>
                        <h3 class="fw-bold mb-0 text-success">{{ $activeUsers }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1" style="font-size: 11px;">New Registered</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $newRegistrations }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1" style="font-size: 11px;">Administrators</div>
                        <h3 class="fw-bold mb-0" style="color: #6f42c1;">{{ $adminUsers }}</h3>
                    </div>
                    <div class="rounded-circle d-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px; background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- THE HORIZONTAL TABS (Scrollable on mobile) -->
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap users-nav-pills">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 {{ $currentRole == 'All' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['role' => 'All']) }}">
                <i class="fa-solid fa-users me-1"></i> All Users 
                <span class="badge rounded-pill ms-1 {{ $currentRole == 'All' ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark' }}">{{ $tabCounts['All'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 {{ $currentRole == 'Resident' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['role' => 'Resident']) }}">
                <i class="fa-solid fa-house-user me-1"></i> Residents 
                <span class="badge rounded-pill ms-1 {{ $currentRole == 'Resident' ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark' }}">{{ $tabCounts['Resident'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-3.5 py-2 {{ $currentRole == 'Admin' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['role' => 'Admin']) }}">
                <i class="fa-solid fa-user-shield me-1"></i> Administrators 
                <span class="badge rounded-pill ms-1 {{ $currentRole == 'Admin' ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark' }}">{{ $tabCounts['Admin'] ?? 0 }}</span>
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header border-0 bg-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-top-4">
            
            <form action="{{ route('admin.users') }}" method="GET" class="m-0 w-100 w-md-auto flex-grow-1 flex-md-grow-0" id="searchForm">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                
                <div class="input-group input-group-sm rounded w-100" style="max-width: 320px; background-color: var(--color-bg-alt);">
                    <button type="submit" class="input-group-text border-0 bg-transparent text-muted"><i class="fa fa-search"></i></button>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent shadow-none" placeholder="Search users..." autocomplete="off">
                    @if(request('search'))
                        <a href="{{ route('admin.users', ['role' => request('role')]) }}" class="btn btn-sm btn-transparent border-0 text-muted">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
            
            <div class="d-flex gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
                <button class="btn btn-sm btn-light border px-3 fw-semibold flex-fill flex-md-grow-0"><i class="fa-solid fa-filter text-muted me-1"></i> Advanced</button>
                <button class="btn btn-sm btn-primary px-3 fw-semibold flex-fill flex-md-grow-0"><i class="fa-solid fa-user-plus me-1"></i> Create New</button>
            </div>
        </div>

        {{-- Bulk Selection Bar --}}
        <div id="bulkActionsBar" class="d-none bg-primary bg-opacity-10 border-bottom px-3 px-md-4 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold" id="selectedCountBadge">0 Selected</span>
                <span class="text-muted small d-none d-sm-inline">Select multiple users to delete in bulk.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold bg-white" id="deselectAllBtn">
                    <i class="fa-solid fa-xmark me-1"></i> Deselect
                </button>
                <button type="button" class="btn btn-sm btn-danger rounded-3 px-3 fw-semibold shadow-sm" id="bulkDeleteBtn">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete Selected (<span id="bulkDeleteCount">0</span>)
                </button>
            </div>
        </div>

        {{-- Desktop Table View (Screens >= 768px) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0 table-users" id="usersMasterTable">
                <thead class="text-muted small" style="background-color: var(--color-bg);">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-center" style="width: 44px;">
                            <input class="form-check-input" type="checkbox" id="selectAllUsers" title="Select All">
                        </th>
                        <th class="py-3 border-0 text-nowrap" style="width: 120px;">USER ID</th>
                        <th class="py-3 border-0" style="min-width: 160px;">NAME</th>
                        <th class="py-3 border-0" style="min-width: 170px;">EMAIL</th>
                        <th class="py-3 border-0 text-center text-nowrap" style="width: 110px;">ROLE</th>
                        <th class="py-3 border-0 text-center text-nowrap" style="width: 110px;">STATUS</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 130px;">REGISTRATION DATE</th>
                        <th class="pe-4 py-3 border-0 text-end col-actions">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($allUsers as $user)
                    <tr id="user-row-{{ $user->id }}">
                        <td class="ps-4 text-center py-3" style="width: 44px;">
                            <input class="form-check-input user-checkbox" type="checkbox" value="{{ $user->id }}" data-name="{{ $user->name }}" {{ $user->id === auth()->id() ? 'disabled title="Cannot delete your own account"' : '' }}>
                        </td>
                        <td class="fw-bold text-dark text-nowrap">
                            <span class="font-monospace" style="font-size: 13px;">USR-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2.5 fw-bold text-uppercase flex-shrink-0" style="width: 34px; height: 34px; font-size: 12px;">
                                    {{ substr(strip_tags($user->name), 0, 2) }}
                                </div>
                                <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px; max-width: 160px;">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="text-muted text-truncate" style="max-width: 180px; font-size: 13px;">{{ $user->email }}</td>
                        <td class="text-center text-nowrap">
                            @if($user->role == 'Admin')
                                <span class="badge bg-opacity-10 px-2.5 py-1 rounded-pill" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; font-size: 11px;">Admin</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1 rounded-pill" style="font-size: 11px;">Resident</span>
                            @endif
                        </td>
                        <td class="text-center text-nowrap">
                            @if(in_array(strtolower($user->status), ['active']))
                                <span class="status-badge status-active"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Active</span>
                            @elseif($user->status == 'pending_approval')
                                <span class="status-badge status-pending-approval"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Pending</span>
                            @else
                                <span class="status-badge status-inactive"><i class="fa-solid fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Inactive</span>
                            @endif
                        </td>
                        <td class="text-muted text-nowrap" style="font-size: 12.5px;">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="pe-4 text-end text-nowrap col-actions">
                            {{-- VIEW BUTTON --}}
                            <button type="button"
                                class="btn btn-link text-primary fw-bold text-decoration-none p-0 me-3 btn-view-user"
                                style="font-size: 13.5px;"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                data-status="{{ $user->status }}"
                                data-registered="{{ $user->created_at->format('M d, Y') }}"
                                data-gender="{{ $user->gender ?? 'N/A' }}"
                                data-birthdate="{{ $user->birthdate ?? 'N/A' }}"
                                data-age="{{ $user->age ?? 'N/A' }}"
                                data-birthplace="{{ $user->birthplace ?? 'N/A' }}"
                                data-id-type="{{ $user->id_type ?? 'N/A' }}"
                                data-id-doc-url="{{ $user->id_document_url }}">
                                View
                            </button>

                            {{-- EDIT BUTTON --}}
                            <button type="button"
                                class="btn btn-link text-warning fw-bold text-decoration-none p-0 me-3 btn-edit-user"
                                style="font-size: 13.5px;"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}">
                                Edit
                            </button>

                            {{-- DEACTIVATE / ACTIVATE BUTTON --}}
                            <button type="button"
                                class="btn btn-link fw-bold text-decoration-none p-0 btn-toggle-status {{ in_array(strtolower($user->status), ['active']) ? 'text-danger' : 'text-success' }}"
                                style="font-size: 13.5px;"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-action="{{ in_array(strtolower($user->status), ['active']) ? 'deactivate' : 'activate' }}"
                                data-url="{{ route('admin.users.toggle-status', $user->id) }}">
                                {{ in_array(strtolower($user->status), ['active']) ? 'Deactivate' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users fa-3x mb-3 text-light"></i>
                            <h5 class="fw-bold text-dark">No Users Found</h5>
                            <div>There are currently no users matching your criteria.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (Screens < 768px, matching Request tab) --}}
        <div class="d-md-none p-3" id="users-mobile-list">
            @forelse($allUsers as $user)
                <div class="mobile-user-card">
                    {{-- Header: ID + Role + Status --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input user-checkbox mt-0" type="checkbox" value="{{ $user->id }}" data-name="{{ $user->name }}" {{ $user->id === auth()->id() ? 'disabled title="Cannot delete your own account"' : '' }}>
                            <span class="font-monospace fw-bold text-dark" style="font-size: 13px;">
                                USR-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            @if($user->role == 'Admin')
                                <span class="badge bg-opacity-10 px-2 py-0.5 rounded-pill" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; font-size: 10.5px;">Admin</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-0.5 rounded-pill" style="font-size: 10.5px;">Resident</span>
                            @endif

                            @if(in_array(strtolower($user->status), ['active']))
                                <span class="status-badge status-active" style="font-size: 10.5px; padding: 2px 7px;">Active</span>
                            @elseif($user->status == 'pending_approval')
                                <span class="status-badge status-pending-approval" style="font-size: 10.5px; padding: 2px 7px;">Pending</span>
                            @else
                                <span class="status-badge status-inactive" style="font-size: 10.5px; padding: 2px 7px;">Inactive</span>
                            @endif
                        </div>
                    </div>

                    {{-- User Details --}}
                    <div class="d-flex align-items-center my-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2.5 fw-bold text-uppercase flex-shrink-0" style="width: 38px; height: 38px; font-size: 13px;">
                            {{ substr(strip_tags($user->name), 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 14px;">{{ $user->name }}</div>
                            <div class="text-muted text-truncate" style="font-size: 12px;">{{ $user->email }}</div>
                            <div class="text-muted" style="font-size: 11px;">Registered: {{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>

                    {{-- Actions Grid for Mobile --}}
                    <div class="d-flex gap-2 pt-2 border-top mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 flex-fill fw-semibold py-1.5 btn-view-user"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                data-status="{{ $user->status }}"
                                data-registered="{{ $user->created_at->format('M d, Y') }}"
                                data-gender="{{ $user->gender ?? 'N/A' }}"
                                data-birthdate="{{ $user->birthdate ?? 'N/A' }}"
                                data-age="{{ $user->age ?? 'N/A' }}"
                                data-birthplace="{{ $user->birthplace ?? 'N/A' }}"
                                data-id-type="{{ $user->id_type ?? 'N/A' }}"
                                data-id-doc-url="{{ $user->id_document_url }}">
                            <i class="fa-solid fa-eye me-1"></i> View
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-3 flex-fill fw-semibold py-1.5 btn-edit-user"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}">
                            <i class="fa-solid fa-pen me-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm rounded-3 flex-fill fw-semibold py-1.5 btn-toggle-status {{ in_array(strtolower($user->status), ['active']) ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-action="{{ in_array(strtolower($user->status), ['active']) ? 'deactivate' : 'activate' }}"
                                data-url="{{ route('admin.users.toggle-status', $user->id) }}">
                            {{ in_array(strtolower($user->status), ['active']) ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-users fa-3x mb-2 text-light d-block"></i>
                    <h6 class="fw-bold text-dark">No Users Found</h6>
                    <div class="small">There are currently no users matching your criteria.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===================== VIEW USER MODAL ===================== --}}
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 px-4 pt-4" style="background: var(--gradient-primary); border-radius: 1rem 1rem 0 0;">
                <div>
                    <h5 class="modal-title fw-bold text-white" id="viewUserModalLabel">
                        <i class="fa-solid fa-user-circle me-2"></i> User Details
                    </h5>
                    <div class="text-white opacity-75 small" id="viewUserIdLabel"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase" style="width: 56px; height: 56px; font-size: 20px;" id="viewUserAvatar"></div>
                    <div>
                        <div class="fw-bold text-dark" id="viewUserName" style="font-size: 17px;"></div>
                        <div class="text-muted small" id="viewUserEmail"></div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">ROLE</div>
                        <div id="viewUserRole" class="fw-semibold"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">STATUS</div>
                        <div id="viewUserStatus"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">GENDER</div>
                        <div id="viewUserGender" class="fw-semibold text-dark"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">BIRTHDATE</div>
                        <div id="viewUserBirthdate" class="fw-semibold text-dark"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">AGE</div>
                        <div id="viewUserAge" class="fw-semibold text-dark"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">BIRTHPLACE</div>
                        <div id="viewUserBirthplace" class="fw-semibold text-dark"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">ID TYPE</div>
                        <div id="viewUserIdType" class="fw-semibold text-dark"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">REGISTERED</div>
                        <div id="viewUserRegistered" class="fw-semibold text-dark"></div>
                    </div>
                    <div class="col-12 mt-2 pt-2 border-top">
                        <div class="text-muted small fw-bold mb-1">VERIFICATION / ID DOCUMENT</div>
                        <div id="viewUserIdDoc">
                            <span class="text-muted small">No ID Uploaded</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3 fw-semibold px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ===================== EDIT USER MODAL ===================== --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="editUserModalLabel">
                    <i class="fa-solid fa-user-pen me-2 text-primary"></i> Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">FULL NAME</label>
                        <input type="text" name="name" id="editUserName" class="form-control rounded-3" style="text-transform: uppercase;" required placeholder="FULL NAME">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">EMAIL ADDRESS</label>
                        <input type="email" name="email" id="editUserEmail" class="form-control rounded-3" required placeholder="email@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ROLE</label>
                        <select name="role" id="editUserRoleSelect" class="form-select rounded-3">
                            <option value="Resident">Resident</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 fw-semibold px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-semibold px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- ===================== CONFIRM TOGGLE STATUS MODAL ===================== --}}
<div class="modal fade" id="confirmToggleModal" tabindex="-1" aria-labelledby="confirmToggleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body text-center px-4 py-4">
                <div class="mb-3" id="confirmToggleIcon">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-user-xmark fs-4"></i>
                    </div>
                </div>
                <h5 class="fw-bold text-dark mb-1" id="confirmToggleTitle">Deactivate User?</h5>
                <p class="text-muted small" id="confirmToggleMessage">This will prevent the user from logging in.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4 gap-2">
                <button type="button" class="btn btn-light rounded-3 fw-semibold px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-3 fw-semibold px-4" id="confirmToggleBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for toggle-status POST --}}
<form id="toggleStatusForm" method="POST" action="" style="display:none;">
    @csrf
    @method('PATCH')
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- VIEW MODAL ----
    document.querySelectorAll('.btn-view-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const name = this.dataset.name;

            document.getElementById('viewUserIdLabel').textContent  = 'USR-' + String(id).padStart(5, '0');
            document.getElementById('viewUserAvatar').textContent   = name.substring(0, 2).toUpperCase();
            document.getElementById('viewUserName').textContent     = name;
            document.getElementById('viewUserEmail').textContent    = this.dataset.email;
            document.getElementById('viewUserRole').textContent     = this.dataset.role;
            document.getElementById('viewUserGender').textContent   = this.dataset.gender;
            document.getElementById('viewUserBirthdate').textContent= this.dataset.birthdate;
            document.getElementById('viewUserAge').textContent      = this.dataset.age || 'N/A';
            document.getElementById('viewUserBirthplace').textContent = this.dataset.birthplace || 'N/A';
            document.getElementById('viewUserIdType').textContent   = this.dataset.idType || 'N/A';
            document.getElementById('viewUserRegistered').textContent = this.dataset.registered;

            const docUrl = this.dataset.idDocUrl;
            const docContainer = document.getElementById('viewUserIdDoc');
            if (docContainer) {
                if (docUrl) {
                    const isPdf = docUrl.toLowerCase().split('?')[0].endsWith('.pdf');
                    docContainer.innerHTML = `
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <a href="${docUrl}" target="_blank" class="btn btn-sm btn-light border text-primary rounded-2 px-3 py-1.5 fw-semibold shadow-sm">
                                <i class="fa-solid ${isPdf ? 'fa-file-pdf text-danger' : 'fa-id-card text-primary'} me-1"></i> View ID Document <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-muted small"></i>
                            </a>
                        </div>
                    `;
                } else {
                    docContainer.innerHTML = '<span class="text-muted small">No ID Uploaded</span>';
                }
            }

            // Status badge
            const status = this.dataset.status.toLowerCase();
            let badgeHtml = '';
            if (status === 'active') {
                badgeHtml = '<span class="status-badge status-active">Active</span>';
            } else if (status === 'pending_approval') {
                badgeHtml = '<span class="status-badge status-pending-approval">Pending</span>';
            } else {
                badgeHtml = '<span class="status-badge status-inactive">Inactive</span>';
            }
            document.getElementById('viewUserStatus').innerHTML = badgeHtml;

            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
        });
    });

    // ---- EDIT MODAL ----
    document.querySelectorAll('.btn-edit-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id    = this.dataset.id;
            const name  = this.dataset.name;
            const email = this.dataset.email;
            const role  = this.dataset.role;

            const editUserNameInput = document.getElementById('editUserName');
            editUserNameInput.value          = (name || '').toUpperCase();
            document.getElementById('editUserEmail').value         = email;
            document.getElementById('editUserRoleSelect').value    = role;

            const baseUrl = '{{ url("/admin/users") }}';
            document.getElementById('editUserForm').action = baseUrl + '/' + id + '/update';

            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        });
    });

    const editUserNameInput = document.getElementById('editUserName');
    if (editUserNameInput) {
        editUserNameInput.addEventListener('input', function() {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            if (typeof this.value === 'string') {
                this.value = this.value.toUpperCase();
            }
            if (start !== null && end !== null) {
                this.setSelectionRange(start, end);
            }
        });
    }

    // ---- TOGGLE STATUS (DEACTIVATE / ACTIVATE) MODAL ----
    const toggleModal    = new bootstrap.Modal(document.getElementById('confirmToggleModal'));
    const toggleForm     = document.getElementById('toggleStatusForm');
    const confirmBtn     = document.getElementById('confirmToggleBtn');
    const toggleTitle    = document.getElementById('confirmToggleTitle');
    const toggleMessage  = document.getElementById('confirmToggleMessage');
    const toggleIcon     = document.getElementById('confirmToggleIcon');

    document.querySelectorAll('.btn-toggle-status').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = this.dataset.action; // 'deactivate' or 'activate'
            const name   = this.dataset.name;
            const url    = this.dataset.url;

            toggleForm.action = url;

            if (action === 'deactivate') {
                toggleTitle.textContent   = 'Deactivate User?';
                toggleMessage.textContent = name + ' will not be able to log in until reactivated.';
                confirmBtn.className      = 'btn btn-danger rounded-3 fw-semibold px-4';
                confirmBtn.textContent    = 'Deactivate';
                toggleIcon.innerHTML = '<div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;"><i class="fa-solid fa-user-xmark fs-4"></i></div>';
            } else {
                toggleTitle.textContent   = 'Activate User?';
                toggleMessage.textContent = name + ' will be able to log in after activation.';
                confirmBtn.className      = 'btn btn-success rounded-3 fw-semibold px-4';
                confirmBtn.textContent    = 'Activate';
                toggleIcon.innerHTML = '<div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width:60px;height:60px;"><i class="fa-solid fa-user-check fs-4"></i></div>';
            }

            toggleModal.show();
        });
    });

    confirmBtn.addEventListener('click', function () {
        toggleModal.hide();
        toggleForm.submit();
    });

    // ---- MULTI-SELECT & BULK DELETE FOR USERS ----
    const selectAllCheckbox = document.getElementById('selectAllUsers');
    const userCheckboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const bulkDeleteModalEl = document.getElementById('usersBulkDeleteModal');
    const bulkDeleteModal = bulkDeleteModalEl ? new bootstrap.Modal(bulkDeleteModalEl) : null;
    const bulkHiddenIds = document.getElementById('bulk_hidden_ids');
    const bulkModalCount = document.getElementById('bulk_modal_count');
    const bulkSelectedNamesList = document.getElementById('bulk_selected_names_list');

    function updateBulkBar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.user-checkbox:checked'));
        const count = checkedBoxes.length;

        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            selectedCountBadge.textContent = `${count} Selected`;
            bulkDeleteCount.textContent = count;
        } else {
            bulkActionsBar.classList.add('d-none');
        }

        // Highlight table rows and mobile cards
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                if (cb.checked) {
                    row.classList.add('table-primary');
                } else {
                    row.classList.remove('table-primary');
                }
            }
            const card = cb.closest('.mobile-user-card');
            if (card) {
                if (cb.checked) {
                    card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                } else {
                    card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                }
            }
        });

        if (selectAllCheckbox) {
            const allTableBoxes = document.querySelectorAll('#usersMasterTable .user-checkbox:not(:disabled)');
            const checkedTableBoxes = document.querySelectorAll('#usersMasterTable .user-checkbox:checked');
            selectAllCheckbox.checked = (allTableBoxes.length > 0 && checkedTableBoxes.length === allTableBoxes.length);
            selectAllCheckbox.indeterminate = (checkedTableBoxes.length > 0 && checkedTableBoxes.length < allTableBoxes.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            userCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkBar();
        });
    }

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            userCheckboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    if (bulkDeleteBtn && bulkDeleteModal) {
        bulkDeleteBtn.addEventListener('click', function () {
            const checkedBoxes = Array.from(document.querySelectorAll('.user-checkbox:checked'));
            if (checkedBoxes.length === 0) return;

            bulkHiddenIds.innerHTML = '';
            bulkSelectedNamesList.innerHTML = '';
            bulkModalCount.textContent = `${checkedBoxes.length}`;

            const seenIds = new Set();
            checkedBoxes.forEach(cb => {
                if (seenIds.has(cb.value)) return;
                seenIds.add(cb.value);

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'ids[]';
                hiddenInput.value = cb.value;
                bulkHiddenIds.appendChild(hiddenInput);

                const li = document.createElement('li');
                li.textContent = `${cb.getAttribute('data-name') || ('User ID #' + cb.value)}`;
                bulkSelectedNamesList.appendChild(li);
            });

            bulkDeleteModal.show();
        });
    }
});
</script>

{{-- MODAL: BULK DELETE CONFIRMATION FOR USERS --}}
<div class="modal fade" id="usersBulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-trash-can fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Delete Selected Users?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to permanently delete <strong id="bulk_modal_count" class="text-danger"></strong> selected user(s)? This action will remove their profiles and access.
                </p>
                <div class="bg-light rounded-3 p-3 text-start small mb-4 border" style="max-height: 140px; overflow-y: auto;">
                    <div class="fw-semibold text-muted mb-1 text-uppercase" style="font-size: 11px;">Selected Users:</div>
                    <ul id="bulk_selected_names_list" class="mb-0 ps-3 text-dark"></ul>
                </div>
                <form id="usersBulkDeleteForm" action="{{ route('admin.users.bulk-delete') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <div id="bulk_hidden_ids"></div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light border px-4 fw-semibold rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 fw-semibold rounded-pill shadow-sm">
                            <i class="fa-solid fa-trash-can me-1"></i> Yes, Delete All Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection