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
            <div class="position-relative" style="position: relative;">
                <input type="password" id="{{ $formId }}Password" name="password" style="padding-right: 42px;" required>
                <button type="button" 
                        class="password-toggle-btn" 
                        onclick="toggleResidentLoginPassword('{{ $formId }}Password', this)"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; padding: 0; cursor: pointer; color: #6c757d; display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; z-index: 5;"
                        aria-label="Toggle password visibility" 
                        title="Show password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <a href="{{ route('resident.forgot_password') }}" class="welcome-login-forgot">Forgot Password?</a>
        @else
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label small fw-semibold mb-0" for="{{ $formId }}Password">Password</label>
                <a href="{{ route('resident.forgot_password') }}" class="small text-primary text-decoration-none fw-bold">Forgot Password?</a>
            </div>
            <div class="position-relative">
                <input type="password" id="{{ $formId }}Password" name="password" class="form-control rounded-3" style="padding-right: 42px;" required>
                <button type="button" 
                        class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent text-muted password-toggle-btn" 
                        style="z-index: 5; cursor: pointer; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;"
                        onclick="toggleResidentLoginPassword('{{ $formId }}Password', this)"
                        aria-label="Toggle password visibility" 
                        title="Show password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
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
    <p class="welcome-login-register mt-3">Don't have an account? <a href="{{ route('resident.register') }}">Create one here</a></p>
@else
    <div class="text-center small">
        Don't have an account? <a href="{{ route('resident.register') }}" class="text-success text-decoration-none fw-bold">Create one here</a>
    </div>
@endif

<script>
if (typeof toggleResidentLoginPassword !== 'function') {
    function toggleResidentLoginPassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
            btn.setAttribute('title', 'Hide password');
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
            btn.setAttribute('title', 'Show password');
        }
        input.focus();
    }
}
</script>