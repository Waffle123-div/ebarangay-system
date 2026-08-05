@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h2 class="header-title" style="font-weight: 400;">Account Approvals</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-0 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- THE HORIZONTAL TABS -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentStatus == 'pending_approval' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'pending_approval']) }}">
                <i class="fa-solid fa-user-clock me-1"></i> Pending Approvals 
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'pending_approval' ? 'bg-white text-primary' : 'bg-secondary' }}">{{ $tabCounts['pending_approval'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentStatus == 'active' ? 'active fw-bold shadow-sm bg-success border-success text-white' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}">
                <i class="fa-solid fa-user-check me-1"></i> Approved Accounts
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'active' ? 'bg-white text-success' : 'bg-secondary' }}">{{ $tabCounts['active'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentStatus == 'rejected' ? 'active fw-bold shadow-sm bg-danger border-danger text-white' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}">
                <i class="fa-solid fa-user-xmark me-1"></i> Rejected Accounts
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'rejected' ? 'bg-white text-danger' : 'bg-secondary' }}">{{ $tabCounts['rejected'] }}</span>
            </a>
        </li>
    </ul>

    <!-- FILTER & TABLE CONTAINER -->
    <div class="d-flex justify-content-end mb-3">
        <form action="{{ route('admin.approvals') }}" method="GET" class="d-flex align-items-center gap-2">
            <!-- Preserve the current status tab when filtering by ID -->
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            
            <label for="id_type" class="fw-bold text-muted small mb-0">Filter by ID:</label>
            <select name="id_type" id="id_type" class="form-select form-select-sm shadow-sm border-0 w-auto" onchange="this.form.submit()">
                <option value="">All Documents</option>
                <option value="National ID" {{ request('id_type') == 'National ID' ? 'selected' : '' }}>National ID</option>
                <option value="Voter's ID" {{ request('id_type') == "Voter's ID" ? 'selected' : '' }}>Voter's ID</option>
                <option value="Driver's License" {{ request('id_type') == "Driver's License" ? 'selected' : '' }}>Driver's License</option>
                <option value="Passport" {{ request('id_type') == 'Passport' ? 'selected' : '' }}>Passport</option>
            </select>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 text-center">
                <thead class="text-muted small" style="background-color: var(--color-bg);">
                    <tr>
                        <th class="py-3 border-0">NAME</th>
                        <th class="py-3 border-0">EMAIL</th>
                        <th class="py-3 border-0">DATE SUBMITTED</th>
                        <th class="py-3 border-0">ID TYPE</th>
                        <th class="py-3 border-0">DOCUMENT</th>
                        <th class="py-3 border-0">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($pendingUsers as $user)
                    <tr>
                        <td class="fw-bold text-dark">{{ $user->name }}</td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1">{{ $user->id_type ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($user->id_front_path)
                                <a href="{{ Storage::url($user->id_front_path) }}" target="_blank" class="btn btn-sm btn-light border text-primary">
                                    <i class="fa-solid fa-image me-1"></i> View ID
                                </a>
                            @else
                                <span class="text-muted small">No ID Uploaded</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                
                                @if($currentStatus == 'pending_approval')
                                    <!-- ONLY SHOW ACTIONS IN THE PENDING TAB -->
                                    <form action="{{ route('admin.approvals.approve', $user->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm rounded-1 shadow-sm" title="Approve">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.approvals.reject', $user->id) }}" method="POST" class="m-0" onsubmit="return confirm('Reject this account?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-danger btn-sm rounded-1 shadow-sm" title="Reject">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </form>
                                @elseif($currentStatus == 'active')
                                    <span class="status-badge status-approved">Approved</span>
                                @elseif($currentStatus == 'rejected')
                                    <span class="status-badge status-rejected">Rejected</span>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <span class="d-block mb-2">No {{ str_replace('_', ' ', $currentStatus) }} accounts to review.</span>
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
@endsection