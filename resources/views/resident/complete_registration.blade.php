<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Complete Registration - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <style>
        body { background-color: var(--color-bg); font-family: var(--font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
        .step-panel { display: none; }
        .step-panel.active { display: block; }
        .step-indicator { width: 33.33%; height: 4px; background-color: #e9ecef; }
        .step-indicator.active { background-color: var(--color-primary); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <!-- Progress Bar -->
                <div class="d-flex mb-4">
                    <div id="badge-step-1" class="step-indicator active rounded-start"></div>
                    <div id="badge-step-2" class="step-indicator {{ Auth::user()->google_id ? '' : 'active' }} ms-1"></div>
                    <div id="badge-step-3" class="step-indicator ms-1 rounded-end"></div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 small rounded-3 mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.complete.submit') }}" enctype="multipart/form-data" id="multiStepForm">
                        @csrf

                        <!-- ========================================== -->
                        <!-- STEP 1: ONLY VISIBLE TO GOOGLE USERS -->
                        <!-- ========================================== -->
                        @if(Auth::user()->google_id)
                        <div class="step-panel active" id="panel-step-1">
                            <div class="text-center mb-4">
                                <h4 class="fw-bold text-dark mb-1">Step 1 of 3</h4>
                                <p class="text-muted small">Finish creating your account</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Email Address</label>
                                <input type="text" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Full Name</label>
                                <input type="text" class="form-control bg-light" value="{{ $user->name }}" readonly disabled>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold">Password</label>
                                    <input type="password" name="password" class="form-control rounded-3">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold">Confirm Password</label>
                                    <input type="password" id="confirm_password" class="form-control rounded-3">
                                    <div class="invalid-feedback small">Passwords do not match.</div>
                                </div>
                            </div>

                            <!-- Birthdate and Gender -->
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-semibold">Date of birth</label>
                                <input type="date" name="birthdate" class="form-control rounded-3" value="{{ old('birthdate') }}">
                            </div>

                            <div class="mb-4 text-start">
                                <label class="form-label small fw-semibold d-block">Gender</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="genderMan" value="Man" {{ old('gender') == 'Man' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="genderMan">Man</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="genderWoman" value="Woman" {{ old('gender') == 'Woman' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="genderWoman">Woman</label>
                                </div>
                            </div>

                            <button type="button" class="btn btn-success w-100 rounded-3 py-2 fw-semibold" onclick="goToStep(2)">
                                Next
                            </button>
                        </div>
                        @endif

                        <!-- ========================================== -->
                        <!-- STEP 2: TERMS (MANUAL USERS START HERE) -->
                        <!-- ========================================== -->
                        <div class="step-panel {{ Auth::user()->google_id ? '' : 'active' }}" id="panel-step-2">
                            <div class="text-center mb-4">
                                <h4 class="fw-bold text-dark mb-1">Step 2 of 3</h4>
                                <p class="text-muted small">Terms and Conditions</p>
                            </div>

                            <div class="mb-4 p-3 bg-light rounded-3 small text-muted text-start" style="max-height: 200px; overflow-y: auto;">
                                <h6>Barangay E-Services Platform Rules</h6>
                                <p>By accessing this platform, you warrant that you are a legal resident of Barangay Cogon East, Carmen, Cebu. All document requests submitted through this web-based tracking system must consist of authentic personal declarations.</p>
                                <p>Falsification of administrative identity profiles or residency documentation will result in the immediate revocation of credentials and potential endorsement to local legal authorities.</p>
                            </div>

                            <div class="form-check mb-4 text-start">
                                <input class="form-check-input" type="checkbox" name="terms" value="1" id="agreeTerms" required>
                                <label class="form-check-label small text-muted" for="agreeTerms">
                                    I agree to the terms and conditions stated above
                                </label>
                            </div>

                            <div class="d-flex gap-2">
                                @if(Auth::user()->google_id)
                                    <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(1)">Back</button>
                                @endif
                                <button type="button" class="btn btn-success w-100 rounded-3 py-2 fw-semibold" onclick="goToStep(3)">Next</button>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- STEP 3: ID UPLOAD -->
                        <!-- ========================================== -->
                        <div class="step-panel" id="panel-step-3">
                            <div class="text-center mb-4">
                                <h4 class="fw-bold text-dark mb-1">Step 3 of 3</h4>
                                <p class="text-muted small">Verify Residency</p>
                            </div>

                            <div class="alert alert-warning border-0 small text-start mb-4">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> 
                                Access is restricted to valid residents. Please attach a valid ID or document to verify your profile.
                            </div>

                            <!-- NEW: ID Type Dropdown -->
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-semibold">Type of ID / Document</label>
                                <select name="id_type" class="form-select rounded-3" required>
                                    <option value="" disabled selected>Select ID Type...</option>
                                    <option value="National ID">National ID</option>
                                    <option value="Driver's License">Driver's License</option>
                                    <option value="Voter's ID">Voter's ID</option>
                                    <option value="Student ID">Student ID</option>
                                    <option value="Others">Others (Voter's Certificate, PSA, etc.)</option>
                                </select>
                            </div>

                            <div class="mb-4 text-start">
                                <label class="form-label small fw-semibold">Upload Document Image</label>
                                <input class="form-control rounded-3" type="file" name="proof_of_residency" accept="image/*,application/pdf" required>
                                <div class="form-text small text-muted">Accepted formats: JPG, JPEG, PNG, PDF (Max 10MB).</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="goToStep(2)">Back</button>
                                <button type="submit" id="btnSubmitComplete" class="btn btn-success w-50 rounded-3 py-2 fw-semibold">Submit</button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('multiStepForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmitComplete');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting...';
            }
        });

        function goToStep(stepNumber) {
            
            // Validate Step 1 (Only runs if they are a Google user)
            if (stepNumber === 2 && {{ Auth::user()->google_id ? 'true' : 'false' }}) {
                const pwd = document.querySelector('input[name="password"]');
                const confirmPwd = document.getElementById('confirm_password');
                const birthdate = document.querySelector('input[name="birthdate"]');
                const genderChecked = document.querySelector('input[name="gender"]:checked');

                if (!pwd.value || pwd.value.length < 8) {
                    pwd.classList.add('is-invalid');
                    return;
                } else {
                    pwd.classList.remove('is-invalid');
                }

                if (pwd.value !== confirmPwd.value) {
                    confirmPwd.classList.add('is-invalid');
                    return;
                } else {
                    confirmPwd.classList.remove('is-invalid');
                }

                if (!birthdate.value) {
                    birthdate.classList.add('is-invalid');
                    return;
                } else {
                    birthdate.classList.remove('is-invalid');
                }

                if (!genderChecked) {
                    alert('Please select your gender.');
                    return;
                }
            }

            // Validate Step 2 (Terms)
            if (stepNumber === 3) {
                const agreeTerms = document.getElementById('agreeTerms');
                if (!agreeTerms.checked) {
                    agreeTerms.classList.add('is-invalid');
                    return;
                } else {
                    agreeTerms.classList.remove('is-invalid');
                }
            }

            // Execute layout toggle
            document.querySelectorAll('.step-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                if (index < stepNumber) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });

            document.getElementById(`panel-step-${stepNumber}`).classList.add('active');
        }
    </script>
</body>
</html>