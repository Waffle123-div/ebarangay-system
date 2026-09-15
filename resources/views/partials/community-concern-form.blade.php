@php
    $formId = $formId ?? 'concernForm';
    $context = $context ?? 'welcome';
    $showResidentInfo = $showResidentInfo ?? false;
    $guestLocked = $guestLocked ?? !($isResident ?? false);
    $idPrefix = $idPrefix ?? '';
    $residentAddress = $residentAddress ?? ($authUser?->formattedAddress() ?: 'Not provided');
    $isModal = $context === 'modal';
@endphp

@if($showResidentInfo && ($isResident ?? false) && ($authUser ?? null))
    <h6 class="fw-bold text-dark mb-3">Personal Information</h6>
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label text-muted small fw-semibold">Resident ID</label>
            <input type="text" class="form-control bg-light" value="{{ $authUser->user_id ?: 'USR-' . $authUser->id }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted small fw-semibold">Full Name</label>
            <input type="text" class="form-control bg-light" value="{{ $authUser->name }}" readonly>
        </div>
        <div class="col-md-12">
            <label class="form-label text-muted small fw-semibold">Address</label>
            <input type="text" class="form-control bg-light" value="{{ $residentAddress ?: 'Not provided' }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted small fw-semibold">Contact Number</label>
            <input type="text" class="form-control bg-light" value="{{ $authUser->phone_number ?: 'Not provided' }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted small fw-semibold">Email Address</label>
            <input type="text" class="form-control bg-light" value="{{ $authUser->email ?: 'Not provided' }}" readonly>
        </div>
    </div>
    <hr class="border-light mb-4">
    <h6 class="fw-bold text-dark mb-3">Concern Information</h6>
@endif

@if(!$isModal && $guestLocked)
    <div class="concern-auth-notice">
        <i class="icon-lock"></i>
        <span>Please log in to submit a Community Concern.</span>
        <a href="{{ route('resident.login') }}" class="btn btn-primary btn-sm concern-auth-login">Login</a>
    </div>
@endif

<form id="{{ $formId }}"
      class="{{ $isModal ? '' : 'concern-form' }} {{ $guestLocked ? 'concern-form-locked' : '' }} {{ $isModal ? 'mb-0' : '' }}"
      @if($isResident ?? false)
          action="{{ route('resident.concern.store') }}"
          method="POST"
          enctype="multipart/form-data"
      @else
          data-require-auth="true"
          data-login-url="{{ route('resident.login') }}"
      @endif>
    @if($isResident ?? false)
        @csrf
        @if($context === 'welcome')
            <input type="hidden" name="redirect_to" value="welcome">
        @endif
    @endif

    @if($isModal)
        <h6 class="fw-bold text-dark mb-3">Concern Information</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="{{ $idPrefix }}concernTitle" class="form-label text-muted small fw-semibold">Concern Title</label>
                <input type="text" id="{{ $idPrefix }}concernTitle" name="title" class="form-control" value="{{ old('title') }}" placeholder="Brief title of your concern" required>
            </div>
            <div class="col-md-6">
                <label for="{{ $idPrefix }}concernType" class="form-label text-muted small fw-semibold">Concern Category</label>
                <select id="{{ $idPrefix }}concernType" name="category" class="form-select" required>
                    <option value="">Select category...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label for="{{ $idPrefix }}concernDetails" class="form-label text-muted small fw-semibold">Description</label>
                <textarea id="{{ $idPrefix }}concernDetails" name="description" class="form-control" rows="3" placeholder="Describe your concern in detail..." required>{{ old('description') }}</textarea>
            </div>
            <div class="col-md-6">
                <label for="{{ $idPrefix }}concernLocation" class="form-label text-muted small fw-semibold">Location</label>
                <input type="text" id="{{ $idPrefix }}concernLocation" name="location" class="form-control" value="{{ old('location') }}" placeholder="Purok, Street Name, Landmark" required>
            </div>
            <div class="col-md-6">
                <label for="{{ $idPrefix }}concernDate" class="form-label text-muted small fw-semibold">Date of Incident <span class="text-muted">(Optional)</span></label>
                <input type="date" id="{{ $idPrefix }}concernDate" name="incident_date" class="form-control" value="{{ old('incident_date') }}" max="{{ date('Y-m-d') }}">
            </div>
            <div class="col-12">
                <label for="{{ $idPrefix }}concernPhoto" class="form-label text-muted small fw-semibold">Photo Attachment <span class="text-muted">(JPG, JPEG, PNG — Max 5MB)</span></label>
                <input type="file" id="{{ $idPrefix }}concernPhoto" name="photo" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-2">
            <button type="button" class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-3 px-4">
                <i class="fa-solid fa-paper-plane me-1"></i> Submit Concern
            </button>
        </div>
    @else
        <div class="concern-form-grid concern-form-grid-2">
            <div class="form-group">
                <label for="{{ $idPrefix }}concernTitle"><i class="icon-file-text"></i> Concern Title</label>
                <input type="text" id="{{ $idPrefix }}concernTitle" name="title" value="{{ old('title') }}" placeholder="Brief title of your concern" {{ ($isResident ?? false) ? 'required' : 'readonly tabindex=-1' }}>
            </div>
            <div class="form-group">
                <label for="{{ $idPrefix }}concernType"><i class="icon-tag"></i> Concern Category</label>
                <select id="{{ $idPrefix }}concernType" name="category" {{ ($isResident ?? false) ? 'required' : 'disabled tabindex=-1' }}>
                    <option value="">Select category...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="{{ $idPrefix }}concernDetails"><i class="icon-message-square"></i> Description</label>
            <textarea id="{{ $idPrefix }}concernDetails" name="description" rows="3" placeholder="Describe your concern in detail..." {{ ($isResident ?? false) ? 'required' : 'readonly tabindex=-1' }}>{{ old('description') }}</textarea>
        </div>
        <div class="concern-form-grid concern-form-grid-2">
            <div class="form-group">
                <label for="{{ $idPrefix }}concernLocation"><i class="icon-map-pin"></i> Location</label>
                <input type="text" id="{{ $idPrefix }}concernLocation" name="location" value="{{ old('location') }}" placeholder="Purok, Street Name, Landmark" {{ ($isResident ?? false) ? 'required' : 'readonly tabindex=-1' }}>
            </div>
            <div class="form-group">
                <label for="{{ $idPrefix }}concernDate"><i class="icon-calendar"></i> Date of Incident <small>(Optional)</small></label>
                <input type="date" id="{{ $idPrefix }}concernDate" name="incident_date" value="{{ old('incident_date') }}" max="{{ date('Y-m-d') }}" {{ ($isResident ?? false) ? '' : 'readonly disabled tabindex=-1' }}>
            </div>
        </div>
        <div class="form-group">
            <label for="{{ $idPrefix }}concernPhoto"><i class="icon-image"></i> Photo Attachment <small>(JPG, JPEG, PNG — Max 5MB)</small></label>
            <input type="file" id="{{ $idPrefix }}concernPhoto" name="photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" {{ ($isResident ?? false) ? '' : 'disabled tabindex=-1' }}>
        </div>
        <button type="submit" class="btn btn-primary btn-block">
            <i class="icon-send"></i> Submit Concern
        </button>
    @endif
</form>

@if($isResident ?? false)
<script>
(function () {
    const form = document.getElementById(@json($formId));
    if (!form || form.dataset.bound === '1') return;
    form.dataset.bound = '1';

    const photoInput = form.querySelector('input[name="photo"]');
    const submitBtn = form.querySelector('[type="submit"]');
    const maxBytes = 5 * 1024 * 1024;

    const rejectOversizedPhoto = () => {
        if (photoInput && photoInput.files[0] && photoInput.files[0].size > maxBytes) {
            alert('Photo attachment must be 5 MB or smaller (JPG, JPEG, or PNG).');
            photoInput.value = '';
            return true;
        }
        return false;
    };

    photoInput?.addEventListener('change', rejectOversizedPhoto);

    form.addEventListener('submit', function (event) {
        if (rejectOversizedPhoto()) {
            event.preventDefault();
            return;
        }
        if (submitBtn) submitBtn.disabled = true;
    });
})();
</script>
@endif
