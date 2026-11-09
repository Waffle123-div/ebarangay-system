<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Resident Login - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        html, body { height: 100vh; overflow: hidden; background-color: var(--color-bg); font-family: var(--font-body); margin: 0; }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }

        .split-layout { height: 100vh; width: 100vw; display: flex; flex-wrap: nowrap; overflow: hidden; }

        .left-panel {
            height: 100vh;
            background-color: #0b2e59;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 0;
        }
        #loginCarousel { position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; }
        .carousel-inner, .carousel-item { height: 100%; width: 100%; }
        .carousel-item img { object-fit: cover; object-position: left center; height: 100%; width: 100%; }
        
        .carousel-indicators { margin-bottom: 2rem; z-index: 3; }
        .carousel-indicators button { width: 30px !important; height: 4px !important; border-radius: 2px; background-color: rgba(255,255,255,0.4) !important; border: none !important; }
        .carousel-indicators button.active { background-color: #ffffff !important; box-shadow: 0 0 8px rgba(0,0,0,0.3); }

        .carousel-control-prev, .carousel-control-next { width: 8%; z-index: 4; opacity: 0.8; transition: opacity 0.2s ease; }
        .carousel-control-prev:hover, .carousel-control-next:hover { opacity: 1; }
        .carousel-control-prev-icon, .carousel-control-next-icon { width: 2.2rem; height: 2.2rem; background-size: 100%, 100%; }

        .right-panel {
            height: 100vh;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background: #f8fafc; /* Slightly off-white background to highlight the container */
        }
        .login-form-container { width: 100%; max-width: 420px; margin: auto; }

        /* Distinct Card Container Styling */
        .auth-card-container {
            background: #ffffff;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
        }

        @media (max-width: 991.98px) {
            html, body { height: auto; overflow: auto; }
            .split-layout { height: auto; overflow: auto; }
            .left-panel { display: none; }
            .right-panel { height: auto; padding: 2rem 1rem; width: 100%; overflow: visible; background: #ffffff; }
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0 split-layout">
        
        <!-- LEFT SIDE: CAROUSEL -->
        <div class="col-lg-6 left-panel d-none d-lg-block">
            <div id="loginCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#loginCarousel" data-bs-slide-to="2"></button>
                </div>

                <div class="carousel-inner">
                    <div class="carousel-item active"><img src="{{ asset('images/Banner 1.png') }}" alt="Banner 1"></div>
                    <div class="carousel-item"><img src="{{ asset('images/Banner 2.png') }}" alt="Banner 2"></div>
                    <div class="carousel-item"><img src="{{ asset('images/Banner 3.png') }}" alt="Banner 3"></div>
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#loginCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#loginCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>

        <!-- RIGHT SIDE: LOGIN FORM CONTAINER -->
        <div class="col-lg-6 right-panel">
            <div class="login-form-container">
                <div class="card auth-card-container rounded-4 p-4">

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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>