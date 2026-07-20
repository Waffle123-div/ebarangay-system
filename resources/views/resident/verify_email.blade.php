<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <style>
        body { background-color: var(--color-bg); font-family: var(--font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center">
                    
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                        <i class="fa-regular fa-envelope-open fs-1"></i>
                    </div>

                    <h4 class="fw-bold text-dark mb-3">Verify your email address</h4>
                    <p class="text-muted small mb-4">
                        You're almost there! We've sent an email to <strong>{{ auth()->user()->email }}</strong>. 
                        Please click the link in that email to complete your registration and access the dashboard.
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success border-0 small rounded-3 mb-4">
                            <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold mb-3">
                            Resend Verification Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('resident.logout') }}"> 
                        @csrf
                        <button type="submit" class="btn btn-light text-muted w-100 rounded-3 py-2 fw-semibold">
                            Log Out
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </div>

</body>
</html>