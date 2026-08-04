<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <style>
        body { background-color: var(--color-bg); font-family: var(--font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
        .gateway-panel { display: block; }
        .manual-form-panel { display: none; }
        
        /* Added the exact same progress bar styles from the Completion form! */
        .step-indicator { width: 33.33%; height: 4px; background-color: #e9ecef; }
        .step-indicator.active { background-color: #198754; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">

                    <div id="gatewayPanel" class="gateway-panel text-center">
                        <div class="mb-4">
                            <i class="fa-solid fa-id-card text-success fs-1 mb-2"></i>
                            <h4 class="fw-bold text-dark">Create an Account</h4>
                            <p class="text-muted small">Register to request barangay documents</p>
                        </div>

                        <button type="button" class="btn btn-success w-100 rounded-3 py-2 fw-semibold mb-3" onclick="showManualForm()">
                            Create Account Manually
                        </button>

                        <div class="d-flex align-items-center mb-3">
                            <hr class="flex-grow-1">
                            <span class="mx-2 text-muted small fw-bold">OR</span>
                            <hr class="flex-grow-1">
                        </div>

                        <a href="{{ route('google.login', ['action' => 'register']) }}" class="btn btn-dark w-100 rounded-3 py-2 fw-semibold mb-4">
                            <i class="fa-brands fa-google me-2"></i> Sign up with Google
                        </a>

                        <div class="text-center small">
                            Already have an account? <a href="{{ route('resident.login') }}" class="text-primary text-decoration-none fw-bold">Log In Here</a>
                        </div>
                    </div>

                    <div id="manualFormPanel" class="manual-form-panel">
                        
                        <div class="d-flex mb-4">
                            <div class="step-indicator active rounded-start"></div>
                            <div class="step-indicator ms-1"></div>
                            <div class="step-indicator ms-1 rounded-end"></div>
                        </div>

                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-dark mb-1">Step 1 of 3</h4>
                            <p class="text-muted small">Account Information</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 small rounded-3 mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('resident.register.submit') }}" id="registerForm">
                            @csrf
                            
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-semibold">Email Address or Mobile Number</label>
                                <input type="text" name="email" class="form-control rounded-3" value="{{ old('email') }}" required autofocus>
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label small fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                            </div>

                            <div class="row text-start">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold">Password</label>
                                    <input type="password" name="password" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label small fw-semibold">Date of birth</label>
                                <input type="date" name="birthdate" class="form-control rounded-3" value="{{ old('birthdate') }}" required>
                            </div>

                            <div class="mb-4 text-start">
                                <label class="form-label small fw-semibold d-block">Gender</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="gMan" value="Man" required {{ old('gender') == 'Man' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="gMan">Man</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="gWoman" value="Woman" {{ old('gender') == 'Woman' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="gWoman">Woman</label>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-light text-muted w-50 rounded-3 py-2 fw-semibold" onclick="showGateway()">Back</button>
                                <button type="submit" id="btnSubmitRegister" class="btn btn-success w-50 rounded-3 py-2 fw-semibold">Next</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function showManualForm() {
            document.getElementById('gatewayPanel').style.display = 'none';
            document.getElementById('manualFormPanel').style.display = 'block';
        }

        function showGateway() {
            document.getElementById('manualFormPanel').style.display = 'none';
            document.getElementById('gatewayPanel').style.display = 'block';
        }

        @if ($errors->any())
            showManualForm();
        @endif

        document.getElementById('registerForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmitRegister');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Registering...';
            }
        });
    </script>
</body>
</html>