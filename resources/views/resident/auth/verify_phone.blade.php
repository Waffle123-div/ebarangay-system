<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Mobile - E-Barangay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ebarangay-theme.css') }}">
    <style>
        body { background-color: var(--color-bg); font-family: var(--font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--font-heading); }
        .code-input {
            width: 50px;
            height: 60px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }
        .code-input:focus {
            border-color: var(--color-primary-light);
            outline: none;
            box-shadow: none;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4">

                    <div class="mb-4 text-center">
                        <i class="fa-solid fa-comment-sms text-primary fs-1 mb-3"></i>
                        <h4 class="fw-bold text-dark mb-2">Check your phone message</h4>
                        <p class="small text-muted mb-0">
                            We texted you a 5-digit verification code. Please enter the code sent to:<br>
                            <span class="fw-bold text-primary">{{ $phone }}</span>
                        </p>
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

                    <form method="POST" action="{{ route('verify_phone.submit') }}"> 
                        @csrf
                        <input type="hidden" name="phone" value="{{ $phone }}">
                        
                        <div class="d-flex justify-content-center gap-2 mb-4">
                            <input type="text" name="code[]" class="code-input form-control" maxlength="1" required autofocus>
                            <input type="text" name="code[]" class="code-input form-control" maxlength="1" required>
                            <input type="text" name="code[]" class="code-input form-control" maxlength="1" required>
                            <input type="text" name="code[]" class="code-input form-control" maxlength="1" required>
                            <input type="text" name="code[]" class="code-input form-control" maxlength="1" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-3 fw-semibold shadow-sm mb-3">
                            Verify Code
                        </button>
                    </form>

                    <div class="text-center">
                        <form method="POST" action="{{ route('resident.logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link text-muted small text-decoration-none fw-semibold p-0 m-0 border-0">
                                Start over
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.code-input');
        inputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });
    </script>
</body>
</html>