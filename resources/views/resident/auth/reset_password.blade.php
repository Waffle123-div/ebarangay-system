<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ \App\Providers\AppServiceProvider::versionedAsset('css/ebarangay-theme.css') }}">
    <style>
        body { background-color: var(--color-bg); font-family: var(--font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4">

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa-solid fa-shield-halved text-primary fs-3 me-2"></i>
                            <h5 class="fw-bold text-dark mb-0">Password Reset</h5>
                        </div>
                        
                        <div class="small text-muted mb-4">
                            Account Recovery > Secure Reset
                        </div>
                    </div>

                    <div class="bg-light border rounded-3 p-3 mb-4">
                        <span class="d-block small text-muted fw-semibold mb-1">Account Name</span>
                        <span class="d-block fw-bold text-primary fs-5">{{ $user->name }}</span>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 small rounded-3 mb-4 py-2">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('resident.forgot_password.reset.submit') }}"> 
                        @csrf
                        
                        <input type="hidden" name="email" value="{{ session('verified_email') }}">
                        
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-semibold text-muted mb-1">Change my password</label>
                            <input type="password" name="password" class="form-control rounded-3 py-2" required autofocus>
                        </div>

                        <div class="mb-4 text-start">
                            <label class="form-label small fw-semibold text-muted mb-1">Re-enter your new password</label>
                            <input type="password" name="password_confirmation" class="form-control rounded-3 py-2" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-3 fw-semibold shadow-sm">
                            Change Password
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

</body>
</html>