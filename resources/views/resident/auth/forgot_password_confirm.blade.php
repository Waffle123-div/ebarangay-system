<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Account - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ \App\Providers\AppServiceProvider::versionedAsset('css/ebarangay-theme.css') }}">
    <style>
        body { background-color: var(--color-bg); font-family: var(--font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
        .btn-reset:hover { background-color: var(--color-primary-dark); color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4">

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa-solid fa-circle-question text-primary fs-3 me-2"></i>
                            <h5 class="fw-bold text-dark mb-0">E-Barangay Support</h5>
                        </div>
                        
                        <div class="small text-muted mb-4">
                            <a href="{{ route('resident.login') }}" class="text-decoration-none text-muted">Home</a> > 
                            <a href="{{ route('resident.forgot_password') }}" class="text-decoration-none text-muted">Find Account</a> > 
                            Confirm Account
                        </div>
                    </div>

                    <div class="bg-light border rounded-3 p-3 mb-4">
                        <div class="mb-2">
                            <span class="d-block small text-muted fw-semibold">Account Name</span>
                            <span class="d-block fw-bold text-dark fs-5">{{ $user->name }}</span>
                        </div>
                        <hr class="text-muted opacity-25 my-2">
                        <div>
                            <!-- THE FIX: Smart label that checks if it's an email or a phone number -->
                            <span class="d-block small text-muted fw-semibold">
                                {{ filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'Registered Email' : 'Mobile Number' }}
                            </span>
                            <span class="d-block fw-semibold text-primary">{{ $identifier }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('resident.forgot_password.send') }}">
                        @csrf
                        
                        <!-- THE FIX: Pass the exact identifier (email or phone) forward to the next step -->
                        <input type="hidden" name="email" value="{{ $identifier }}">

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-3 px-4 d-flex justify-content-between align-items-center fw-semibold shadow-sm btn-reset mb-3">
                            <span>Send recovery code</span>
                            <i class="fa-solid fa-chevron-right opacity-75"></i>
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('resident.forgot_password') }}" class="text-muted small text-decoration-none fw-semibold">
                            Not you? Search again
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>