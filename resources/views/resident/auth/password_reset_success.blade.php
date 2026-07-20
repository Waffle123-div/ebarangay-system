<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Updated - E-Barangay</title>
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
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center">

                    <div class="mb-4 mt-2">
                        <i class="fa-solid fa-circle-check text-success" style="font-size: 64px;"></i>
                    </div>
                    
                    <h4 class="fw-bold text-dark mb-3">Your password has been updated</h4>
                    
                    <p class="text-muted mb-4 px-2" style="font-size: 15px;">
                        Your password has been successfully changed and can be used to log into the E-Barangay system.
                    </p>

                    <a href="{{ route('resident.login') }}" class="btn btn-primary w-100 rounded-3 py-3 fw-semibold shadow-sm">
                        Sign in to E-Barangay
                    </a>

                </div>
            </div>
        </div>
    </div>

</body>
</html>