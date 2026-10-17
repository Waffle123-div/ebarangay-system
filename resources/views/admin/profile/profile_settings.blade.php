@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">
    <div class="mb-4">
        <h2 class="header-title">Profile Settings</h2>
        <p class="header-subtitle mb-0">Manage your personal information, profile photo, and security</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start">
                <i class="fa-solid fa-triangle-exclamation fs-5 me-2 mt-1 text-danger"></i>
                <div>
                    <div class="fw-bold mb-1">Please fix the following issues:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 text-center pb-4 h-100">
                <div class="bg-primary rounded-top-4" style="height: 120px; background: linear-gradient(135deg, #1e3a8a 0%, #0d9488 100%);"></div>
                
                <div class="d-flex justify-content-center" style="margin-top: -55px;">
                    <div class="bg-white p-2 rounded-circle shadow-sm position-relative">
                        <div id="avatarPreviewContainer" style="width: 105px; height: 105px; overflow: hidden; border-radius: 50%;">
                            @if($user->profile_picture)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($user->profile_picture) }}" 
                                     alt="{{ $user->name }}" 
                                     id="avatarPreviewImg"
                                     class="w-100 h-100 rounded-circle object-fit-cover shadow-sm">
                            @else
                                <div id="avatarFallbackBadge" class="bg-primary bg-opacity-10 text-primary w-100 h-100 rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase" style="font-size: 32px;">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 shadow" 
                                style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"
                                onclick="document.getElementById('profile_picture_input').click();"
                                title="Change profile picture">
                            <i class="fa-solid fa-camera" style="font-size: 13px;"></i>
                        </button>
                    </div>
                </div>
                
                <div class="card-body pt-3">
                    <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->role }}</p>
                    
                    @if($user->status == 'Active' || $user->status == 'active')
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-4">
                            <i class="fa-solid fa-circle me-1" style="font-size: 6px; vertical-align: middle;"></i> Active Account
                        </span>
                    @endif
                    
                    <ul class="list-group list-group-flush text-start mb-0">
                        <li class="list-group-item px-3 py-3 border-light d-flex align-items-center gap-3">
                            <i class="fa-solid fa-envelope text-muted"></i>
                            <span class="text-dark small text-truncate">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item px-3 py-3 border-light d-flex align-items-center gap-3">
                            <i class="fa-solid fa-phone text-muted"></i>
                            <span class="text-dark small">{{ $user->contact_number ?: 'No phone provided' }}</span>
                        </li>
                        <li class="list-group-item px-3 py-3 border-light d-flex align-items-center gap-3">
                            <i class="fa-solid fa-id-badge text-muted"></i>
                            <span class="text-dark small">ID: {{ $user->user_id ?: ('USR-' . str_pad($user->id, 5, '0', STR_PAD_LEFT)) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 bg-white pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0">Edit Information & Photo</h5>
                </div>
                <div class="card-body p-4">

                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" id="adminProfileForm">
                        @csrf
                        
                        {{-- PROFILE PICTURE UPLOAD SECTION --}}
                        <div class="p-3 bg-light rounded-4 border mb-4">
                            <label class="form-label text-dark fw-bold mb-2">Profile Picture</label>
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <div class="bg-white p-1 rounded-circle border shadow-sm flex-shrink-0" style="width: 64px; height: 64px; overflow: hidden;">
                                    @if($user->profile_picture)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($user->profile_picture) }}" 
                                             alt="{{ $user->name }}" 
                                             id="formAvatarThumb"
                                             class="w-100 h-100 rounded-circle object-fit-cover">
                                    @else
                                        <div id="formAvatarThumbFallback" class="bg-primary bg-opacity-10 text-primary w-100 h-100 rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase" style="font-size: 20px;">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <input type="file" name="profile_picture" id="profile_picture_input" accept="image/png,image/jpeg,image/jpg,image/webp" class="d-none">
                                        <input type="hidden" name="remove_profile_picture" id="remove_profile_picture" value="0">
                                        
                                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3 rounded-pill" onclick="document.getElementById('profile_picture_input').click();">
                                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload New Photo
                                        </button>
                                        
                                        @if($user->profile_picture)
                                            <button type="button" class="btn btn-sm btn-outline-danger fw-semibold px-3 rounded-pill" id="removePhotoBtn">
                                                <i class="fa-solid fa-trash-can me-1"></i> Remove Photo
                                            </button>
                                        @endif
                                    </div>
                                    <div class="text-muted small mt-1.5" style="font-size: 11.5px;">
                                        JPG, PNG, or WEBP up to 2MB. Square image recommended.
                                    </div>
                                    <div id="selectedFileName" class="text-primary small fw-semibold mt-1 d-none"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control px-3 py-2 rounded-3" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control px-3 py-2 rounded-3" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control px-3 py-2 rounded-3" value="{{ old('contact_number', $user->contact_number) }}" placeholder="e.g. 09123456789">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">System Role</label>
                                <input type="text" class="form-control px-3 py-2 bg-light text-muted rounded-3" value="{{ $user->role }}" readonly>
                            </div>
                        </div>

                        <hr class="border-light my-4">

                        <h5 class="fw-bold text-dark mb-3">Change Password</h5>
                        <p class="text-muted small mb-4">Leave password fields blank if you do not wish to change your current password.</p>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label class="form-label text-muted small fw-semibold">Current Password</label>
                                <input type="password" name="current_password" class="form-control px-3 py-2 rounded-3" placeholder="Enter current password if changing">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">New Password</label>
                                <input type="password" name="new_password" class="form-control px-3 py-2 rounded-3" placeholder="At least 8 characters">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control px-3 py-2 rounded-3" placeholder="Repeat new password">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                            <button type="reset" class="btn btn-light px-4 fw-semibold border rounded-pill">Reset</button>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold rounded-pill shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('profile_picture_input');
    const selectedFileName = document.getElementById('selectedFileName');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    const removePhotoHidden = document.getElementById('remove_profile_picture');
    const avatarPreviewContainer = document.getElementById('avatarPreviewContainer');
    const formAvatarThumb = document.getElementById('formAvatarThumb');

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('The selected image is larger than 2MB. Please choose a smaller file.');
                    fileInput.value = '';
                    return;
                }

                removePhotoHidden.value = '0';
                selectedFileName.textContent = `Selected: ${file.name}`;
                selectedFileName.classList.remove('d-none');

                const reader = new FileReader();
                reader.onload = function(event) {
                    avatarPreviewContainer.innerHTML = `<img src="${event.target.result}" class="w-100 h-100 rounded-circle object-fit-cover shadow-sm" alt="Preview">`;
                    if (formAvatarThumb) {
                        formAvatarThumb.src = event.target.result;
                    } else {
                        const fallback = document.getElementById('formAvatarThumbFallback');
                        if (fallback) {
                            fallback.outerHTML = `<img src="${event.target.result}" id="formAvatarThumb" class="w-100 h-100 rounded-circle object-fit-cover">`;
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (removePhotoBtn) {
        removePhotoBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                removePhotoHidden.value = '1';
                fileInput.value = '';
                selectedFileName.textContent = 'Photo will be removed on Save.';
                selectedFileName.classList.remove('d-none');
                avatarPreviewContainer.innerHTML = `<div class="bg-primary bg-opacity-10 text-primary w-100 h-100 rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase" style="font-size: 32px;">{{ substr($user->name, 0, 2) }}</div>`;
                if (formAvatarThumb) {
                    formAvatarThumb.outerHTML = `<div id="formAvatarThumbFallback" class="bg-primary bg-opacity-10 text-primary w-100 h-100 rounded-circle d-flex justify-content-center align-items-center fw-bold text-uppercase" style="font-size: 20px;">{{ substr($user->name, 0, 2) }}</div>`;
                }
            }
        });
    }
});
</script>
@endsection