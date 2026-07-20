@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
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

    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Total Users</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalUsers }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Active Users</div>
                        <h3 class="fw-bold mb-0 text-success">{{ $activeUsers }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">New Registrations</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $newRegistrations }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Admin Users</div>
                        <h3 class="fw-bold mb-0" style="color: #6f42c1;">{{ $adminUsers }}</h3>
                    </div>
                    <div class="bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px; background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- THE HORIZONTAL TABS -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentRole == 'All' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['role' => 'All']) }}">
                <i class="fa-solid fa-users me-1"></i> All Users 
                <span class="badge rounded-pill ms-1 {{ $currentRole == 'All' ? 'bg-white text-primary' : 'bg-secondary' }}">{{ $tabCounts['All'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentRole == 'Resident' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['role' => 'Resident']) }}">
                <i class="fa-solid fa-house-user me-1"></i> Residents
                <span class="badge rounded-pill ms-1 {{ $currentRole == 'Resident' ? 'bg-white text-primary' : 'bg-secondary' }}">{{ $tabCounts['Resident'] ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentRole == 'Admin' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['role' => 'Admin']) }}">
                <i class="fa-solid fa-user-shield me-1"></i> Administrators
                <span class="badge rounded-pill ms-1 {{ $currentRole == 'Admin' ? 'bg-white text-primary' : 'bg-secondary' }}">{{ $tabCounts['Admin'] ?? 0 }}</span>
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header border-0 bg-white py-4 px-4 d-flex justify-content-between align-items-center rounded-top-4">
            
            <form action="{{ route('admin.users') }}" method="GET" class="m-0 d-flex gap-2" id="searchForm">
                @if(request('role'))
                    <input type="hidden" name="role" value="{{ request('role') }}">
                @endif
                
                <div class="input-group input-group-sm rounded" style="width: 300px; background-color: var(--color-bg-alt);">
                    <button type="submit" class="input-group-text border-0 bg-transparent text-muted"><i class="fa fa-search"></i></button>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent shadow-none" placeholder="Search users..." autocomplete="off">
                </div>
            </form>
            
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-light border px-3 fw-semibold"><i class="fa-solid fa-filter text-muted me-1"></i> Advanced</button>
                <button class="btn btn-sm btn-primary px-3 fw-semibold"><i class="fa-solid fa-user-plus me-1"></i> Create New</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="text-muted small" style="background-color: var(--color-bg);">
                    <tr>
                        <th class="ps-4 py-3 border-0">USER ID</th>
                        <th class="py-3 border-0">NAME</th>
                        <th class="py-3 border-0">EMAIL</th>
                        <th class="py-3 border-0">ROLE</th>
                        <th class="py-3 border-0">STATUS</th>
                        <th class="py-3 border-0">REGISTRATION DATE</th>
                        <th class="pe-4 py-3 border-0 text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($allUsers as $user)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">
                            USR-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold text-uppercase" style="width: 36px; height: 36px; font-size: 13px;">
                                    {{ substr(strip_tags($user->name), 0, 2) }}
                                </div>
                                <div class="fw-bold text-dark" style="font-size: 14px;">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            @if($user->role == 'Admin')
                                <span class="badge bg-opacity-10 px-3 py-2 rounded-pill" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">Admin</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Resident</span>
                            @endif
                        </td>
                        <td>
                            @if(in_array(strtolower($user->status), ['active']))
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="fa-solid fa-circle text-success" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Active</span>
                            @elseif($user->status == 'pending_approval')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill"><i class="fa-solid fa-circle text-warning" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Pending</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill"><i class="fa-solid fa-circle text-danger" style="font-size: 6px; vertical-align: middle; margin-right: 4px;"></i> Inactive</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="pe-4 text-end text-nowrap">
                            {{-- VIEW BUTTON --}}
                            <button type="button"
                                class="btn btn-link text-primary fw-bold text-decoration-none p-0 me-3 btn-view-user"
                                style="font-size: 14px;"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                data-status="{{ $user->status }}"
                                data-registered="{{ $user->created_at->format('M d, Y') }}"
                                data-gender="{{ $user->gender ?? 'N/A' }}"
                                data-birthdate="{{ $user->birthdate ?? 'N/A' }}"
                                data-id-type="{{ $user->id_type ?? 'N/A' }}">
                                View
                            </button>

                            {{-- EDIT BUTTON --}}
                            <button type="button"
                                class="btn btn-link text-warning fw-bold text-decoration-none p-0 me-3 btn-edit-user"
                                style="font-size: 14px;"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}">
                                Edit
                            </button>

                            {{-- DEACTIVATE / ACTIVATE BUTTON --}}
                            <button type="button"
                                class="btn btn-link fw-bold text-decoration-none p-0 btn-toggle-status {{ in_array(strtolower($user->status), ['active']) ? 'text-danger' : 'text-success' }}"
                                style="font-size: 14px;"
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
    </div>
    
    <div class="text-center mt-5 text-muted small">
        © 2026 E-Barangay System
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
                        <div class="text-muted small fw-bold mb-1">ID TYPE</div>
                        <div id="viewUserIdType" class="fw-semibold text-dark"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small fw-bold mb-1">REGISTERED</div>
                        <div id="viewUserRegistered" class="fw-semibold text-dark"></div>
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
                        <input type="text" name="name" id="editUserName" class="form-control rounded-3" required placeholder="Full name">
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
            document.getElementById('viewUserIdType').textContent   = this.dataset.idType || 'N/A';
            document.getElementById('viewUserRegistered').textContent = this.dataset.registered;

            // Status badge
            const status = this.dataset.status.toLowerCase();
            let badgeHtml = '';
            if (status === 'active') {
                badgeHtml = '<span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Active</span>';
            } else if (status === 'pending_approval') {
                badgeHtml = '<span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Pending</span>';
            } else {
                badgeHtml = '<span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Inactive</span>';
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

            document.getElementById('editUserName').value          = name;
            document.getElementById('editUserEmail').value         = email;
            document.getElementById('editUserRoleSelect').value    = role;

            const baseUrl = '{{ url("/admin/users") }}';
            document.getElementById('editUserForm').action = baseUrl + '/' + id + '/update';

            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        });
    });

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
});
</script>
@endsection