<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Login - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

                    <div class="text-center mb-3">
                        <i class="fa-solid fa-house-user text-primary fs-2 mb-2"></i>
                        <h5 class="fw-bold text-dark mb-1">Welcome Back</h5>
                        <p class="text-muted small mb-0">Log in to your resident portal</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 small rounded-3 mb-3 py-2">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('resident.login.submit') }}">
                        @csrf
                        
                        <div class="mb-3 text-start">
                            <label class="form-label small fw-semibold mb-1">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" required autofocus>
                        </div>

                        <div class="mb-3 text-start">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold mb-0">Password</label>
                                <a href="{{ route('resident.forgot_password') }}" class="small text-primary text-decoration-none fw-bold">Forgot Password?</a>
                            </div>
                            <input type="password" name="password" class="form-control rounded-3" required>
                        </div>

                        <div class="mb-3 d-flex justify-content-center">
                            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold mb-3">
                            Log In
                        </button>
                    </form>

                    <div class="d-flex align-items-center mb-3">
                        <hr class="flex-grow-1 m-0">
                        <span class="mx-2 text-muted small fw-bold">OR</span>
                        <hr class="flex-grow-1 m-0">
                    </div>

                    <a href="{{ route('google.login', ['action' => 'login']) }}" class="btn btn-dark w-100 rounded-3 fw-semibold mb-3">
                        <i class="fa-brands fa-google me-2"></i> Continue with Google
                    </a>

                    <div class="text-center small">
                        Don't have an account? <a href="{{ route('resident.register') }}" class="text-success text-decoration-none fw-bold">Create one here</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>