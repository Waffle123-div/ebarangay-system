@php
    $formId = $formId ?? 'residentLoginForm';
    $ajaxMode = $ajaxMode ?? false;
    $errorContainerId = $errorContainerId ?? 'residentLoginError';
@endphp

@if($ajaxMode)
    <div id="{{ $errorContainerId }}" class="concern-form-alert" hidden></div>
@elseif($errors->any())
    <div class="alert alert-danger border-0 small rounded-3 mb-3 py-2">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="{{ $formId }}" method="POST" action="{{ route('resident.login.submit') }}" @if($ajaxMode) data-ajax-login="true" @endif>
    @csrf
    <input type="hidden" name="intended" id="{{ $formId }}Intended" value="">

    <div class="{{ $ajaxMode ? 'form-group' : 'mb-3 text-start' }}">
        <label class="{{ $ajaxMode ? '' : 'form-label small fw-semibold mb-1' }}" for="{{ $formId }}Email">Email Address or Mobile Number</label>
        <input type="text" id="{{ $formId }}Email" name="email" class="{{ $ajaxMode ? '' : 'form-control rounded-3' }}" value="{{ old('email') }}" required @if(!$ajaxMode) autofocus @endif>
    </div>

    <div class="{{ $ajaxMode ? 'form-group' : 'mb-3 text-start' }}">
        @if($ajaxMode)
            <label for="{{ $formId }}Password">Password</label>
            <input type="password" id="{{ $formId }}Password" name="password" required>
            <a href="{{ route('resident.forgot_password') }}" class="welcome-login-forgot">Forgot Password?</a>
        @else
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label small fw-semibold mb-0" for="{{ $formId }}Password">Password</label>
                <a href="{{ route('resident.forgot_password') }}" class="small text-primary text-decoration-none fw-bold">Forgot Password?</a>
            </div>
            <input type="password" id="{{ $formId }}Password" name="password" class="form-control rounded-3" required>
        @endif
    </div>

    @php $recaptchaSiteKey = env('RECAPTCHA_SITE_KEY'); @endphp
    @if($recaptchaSiteKey)
        <div class="{{ $ajaxMode ? 'form-group' : 'mb-3 d-flex justify-content-center' }}">
            @if($ajaxMode)
                <div id="{{ $formId }}Recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
            @else
                <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
            @endif
        </div>
    @endif

    @if($ajaxMode)
        <button type="submit" class="btn btn-primary btn-block" id="{{ $formId }}Submit">Log In</button>
    @else
        <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold mb-3">Log In</button>
    @endif
</form>

@if($ajaxMode)
    <div class="welcome-login-divider"><span>OR</span></div>
    <a href="{{ route('google.login', ['action' => 'login']) }}" class="btn btn-secondary btn-block" id="welcomeGoogleLogin">Continue with Google</a>
    <p class="welcome-login-register">Don't have an account? <a href="{{ route('resident.register') }}">Create one here</a></p>
@else
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
@endif