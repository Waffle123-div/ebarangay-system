<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Barangay System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <style>
        body { 
            background-color: var(--color-bg);
            font-family: var(--font-body);
        }
        .login-card { 
            max-width: 420px; 
            width: 100%; 
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="card border-0 shadow-sm rounded-4 login-card p-4 p-md-5 bg-white">
        
        <div class="text-center mb-4">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 65px; height: 65px;">
                <i class="fa-solid fa-shield-halved fs-3"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Admin Portal</h4>
            <p class="text-muted small mb-0">Sign in to manage E-Barangay</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small rounded-3 fw-semibold border-0 bg-danger bg-opacity-10 text-danger mb-4">
                <i class="fa-solid fa-circle-exclamation me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold" style="letter-spacing: 0.5px;">EMAIL ADDRESS</label>
                <div class="position-relative">
                    <i class="fa-solid fa-envelope position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="email" name="email" class="form-control bg-light border-0 ps-5 py-2 rounded-3" placeholder="admin@barangay.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label text-muted small fw-bold mb-0" style="letter-spacing: 0.5px;">PASSWORD</label>
                </div>
                <div class="position-relative">
                    <i class="fa-solid fa-lock position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="password" name="password" class="form-control bg-light border-0 ps-5 py-2 rounded-3" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2">
                Log In <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="text-center mt-4 text-muted" style="font-size: 12px;">
            &copy; 2026 E-Barangay System
        </div>

    </div>

</body>
</html>