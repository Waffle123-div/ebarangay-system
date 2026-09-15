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

                    @include('partials.resident-login-form', [
                        'formId' => 'residentLoginForm',
                        'ajaxMode' => false,
                    ])

                </div>
            </div>
        </div>
    </div>

</body>
</html>