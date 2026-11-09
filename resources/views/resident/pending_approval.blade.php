<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Account Pending | E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <style>
        body {
            background-color: var(--color-bg);
            font-family: var(--font-body);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
        .pending-card {
            max-width: 500px;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.1);
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card pending-card mx-auto text-center p-5">
            <div class="icon-circle">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            
            <h3 class="fw-bold text-dark mb-3">Registration Submitted!</h3>
            
            <p class="text-muted mb-4">
                Thank you for providing your details. Please wait until the admin reviews your proof of residency and approves your account. 
            </p>

            <div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-primary mb-4" role="alert">
                @if(request('notify') === 'phone')
                    <i class="fa-solid fa-comment-sms me-2"></i> We will notify you via text message as soon as your account is ready!
                @else
                    <i class="fa-solid fa-envelope me-2"></i> We will notify you via email as soon as your account is ready!
                @endif
            </div>

            <form method="POST" action="{{ route('resident.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary w-100 fw-bold rounded-3 py-2">
                    Return to Login Page
                </button>
            </form>
            
        </div>
    </div>

</body>
</html>