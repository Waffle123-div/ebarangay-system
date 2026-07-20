<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - E-Barangay</title>
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

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa-solid fa-circle-question text-primary fs-3 me-2"></i>
                            <h5 class="fw-bold text-dark mb-0">E-Barangay Support</h5>
                        </div>
                        
                        <div class="small text-muted mb-4">
                            <a href="{{ route('resident.login') }}" class="text-decoration-none text-muted hover-primary">Home</a> > Find Account
                        </div>
                        
                        <h6 class="fw-semibold text-primary mb-1">I forgot my account password</h6>
                        <p class="small text-muted mb-0">Enter your email address to search for your account.</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success border-0 small rounded-3 mb-4 py-2">
                            <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 small rounded-3 mb-4 py-2">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="forgotPasswordForm" method="POST" action="{{ route('resident.forgot_password.search') }}"> 
                        @csrf
                        
                        <div class="mb-4 text-start">
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" required autofocus>
                        </div>

                        <div class="mb-4">
                            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-callback="hideRecaptchaWarning"></div>
                            
                            <div id="recaptchaWarning" class="text-danger small mt-2 d-none fw-semibold">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Please check the "I'm not a robot" box first.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('resident.login') }}" class="btn btn-light text-muted fw-semibold rounded-3 px-4 border-0 shadow-sm">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary rounded-3 fw-semibold px-5">
                                Search
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        // THE FIX: This function fires instantly when the user successfully solves the CAPTCHA
        function hideRecaptchaWarning() {
            document.getElementById('recaptchaWarning').classList.add('d-none');
        }

        // This function still stops the search if they try to click without checking the box
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(event) {
            var recaptchaResponse = grecaptcha.getResponse();
            var warningMsg = document.getElementById('recaptchaWarning');

            if (recaptchaResponse.length === 0) {
                event.preventDefault(); 
                warningMsg.classList.remove('d-none'); 
            }
        });
    </script>
</body>
</html>