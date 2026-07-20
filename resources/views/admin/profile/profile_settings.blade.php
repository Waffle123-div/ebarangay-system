@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h2 class="header-title">Profile Settings</h2>
        <p class="header-subtitle mb-0">Manage your personal information and security</p>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 text-center pb-4 h-100">
                <div class="bg-primary rounded-top-4" style="height: 120px;"></div>
                
                <div class="d-flex justify-content-center" style="margin-top: -50px;">
                    <div class="bg-white p-2 rounded-circle shadow-sm">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase" style="width: 100px; height: 100px; font-size: 32px;">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                    </div>
                </div>
                
                <div class="card-body pt-3">
                    <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->role }}</p>
                    
                    @if($user->status == 'Active')
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-4">
                            <i class="fa-solid fa-circle me-1" style="font-size: 6px; vertical-align: middle;"></i> Active Account
                        </span>
                    @endif
                    
                    <ul class="list-group list-group-flush text-start mb-0">
                        <li class="list-group-item px-3 py-3 border-light d-flex align-items-center gap-3">
                            <i class="fa-solid fa-envelope text-muted"></i>
                            <span class="text-dark small">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item px-3 py-3 border-light d-flex align-items-center gap-3">
                            <i class="fa-solid fa-id-badge text-muted"></i>
                            <span class="text-dark small">ID: {{ $user->user_id }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 bg-white pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0">Edit Information</h5>
                </div>
                <div class="card-body p-4">
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control px-3 py-2" value="{{ $user->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control px-3 py-2" value="{{ $user->email }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control px-3 py-2" value="{{ $user->contact_number }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">System Role</label>
                                <input type="text" class="form-control px-3 py-2 bg-light text-muted" value="{{ $user->role }}" readonly>
                            </div>
                        </div>

                        <hr class="border-light my-4">

                        <h5 class="fw-bold text-dark mb-4">Change Password</h5>
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label class="form-label text-muted small fw-semibold">Current Password</label>
                                <input type="password" name="current_password" class="form-control px-3 py-2" placeholder="Enter current password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">New Password</label>
                                <input type="password" name="new_password" class="form-control px-3 py-2" placeholder="Enter new password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control px-3 py-2" placeholder="Confirm new password">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="reset" class="btn btn-light px-4 fw-semibold">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection