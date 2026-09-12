<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSS Files -->
    <link rel="stylesheet" href="{{asset('nexus/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('nexus/css/icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('nexus/css/app.min.css')}}">
    <link rel="stylesheet" href="{{asset('nexus/css/nexus-theme.css')}}">
    <link rel="stylesheet" href="{{asset('adminlte/plugins/jquery-colorbox/example1/colorbox.css')}}">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <style>
        .disabled-turnstile {
            pointer-events: none;
            opacity: 0.6;
        }

        .disabled-turnstile:hover {
            background-color: inherit;
            color: inherit;
            cursor: not-allowed;
        }
    </style>


</head>
{{--TODO implement later --}}
{{--@php $activeLocale = \Modules\Language\Registry\LanguageRegistry::get('is_active'); @endphp--}}
{{--<body @if(isset($activeLocale) && $activeLocale->is_rtl) dir="rtl" class="body-rtl" @else dir="ltr" @endif >--}}

<body dir="ltr">

<div class="d-flex flex-column h-100 p-3">
    <div class="d-flex flex-column flex-grow-1">
        <div class="row justify-content-center h-100">
            <div class="col-lg-4 py-lg-5">
                <div class="d-flex flex-column h-100 justify-content-center">
                    <div class="auth-logo mb-4">
                        <a href="#" class="logo-dark">
                            <img src="{{ asset('nexus/images/logo-dark.svg') }}" height="26"
                                 alt="logo dark">
                        </a>

                        <a href="#" class="logo-light">
                            <img src="{{ asset('nexus/images/logo-light.svg') }}" height="26"
                                 alt="logo light">
                        </a>
                    </div>

                    <h2 class="fw-bold fs-24">
                        @lang('brilara::brilara.sign_in')
                    </h2>

                    <p class="text-muted mt-1 mb-4">
                        @lang('brilara::brilara.enter_your_email_and_password_to_access_admin_panel')
                    </p>

                    <div class="mb-5">

                        <form action="{{ route('admin.login') }}"
                              method="POST"
                              class="authentication-form">
                            @csrf

                            <div class="mb-3 ">
                                <label class="form-label" for="email">
                                    @lang('brilara::brilara.email')
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    class="form-control @if($errors->get('email')) is-invalid @endif"
                                    placeholder="Enter your email"
                                    value="{{ old('email') }}"
                                >
                                @if($errors->get('email'))
                                    <div id="validationemail" class="invalid-feedback">
                                        {!! implode('<br>', $errors->get('email')) !!}
                                    </div>
                                @endif
                            </div>
                            <div class="mb-3 @if($errors->get('password')) error @endif">
                                <label class="form-label" for="password">
                                    @lang('brilara::brilara.password')
                                </label>
                                <input type="password"
                                       id="password"
                                       class="form-control @if($errors->get('password')) is-invalid @endif"
                                       name="password"
                                       placeholder="Enter your password"
                                       value="{{ old('password') }}"
                                >
                                @if($errors->get('password'))
                                    <div id="validationPassword" class="invalid-feedback">
                                        {!! implode('<br>', $errors->get('password')) !!}
                                    </div>
                                @endif
                            </div>

                            <div class="mb-3 @if($errors->get('remember')) error @endif">
                                <div class="form-check">
                                    <input type="checkbox"
                                           id="remember"
                                           name="remember"
                                           class="form-check-input @if($errors->get('remember')) is-invalid @endif"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        @lang('brilara::brilara.remeber')
                                    </label>
                                    @if($errors->get('remember'))
                                        <div class="invalid-feedback">
                                            {!! implode('<br>', $errors->get('remember')) !!}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="cf-turnstile"
                                 data-sitekey="{{ config('brilara.cf_turnstile_site_key') }}"
                                 data-callback="onTurnstileSuccess"
                                 data-expired-callback="onTurnstileExpired">
                            </div>
                            <div class="mb-3 @if($errors->get('cf-turnstile-response')) error @endif">
                                @if($errors->get('cf-turnstile-response'))
                                    <div id="validationCfTurnstile" class="invalid-feedback d-block">
                                        {!! implode('<br>', $errors->get('cf-turnstile-response')) !!}
                                    </div>
                                @endif
                            </div>

                            <div class="mb-1 text-center d-grid">
                                <button class="btn btn-soft-primary " type="submit" id="login-submit-btn">
                                    @lang('brilara::brilara.sign_in')
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 text-center">
                        <?php echo date("Y"); ?> Made by
                        <iconify-icon icon="iconamoon:heart-duotone"
                                      class="fs-18 align-middle text-danger"></iconify-icon>
                        <a href="https://sitemaster.pp.ua/" class="fw-bold footer-text" target="_blank">Nodex</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

@yield('js')


<script src="https://unpkg.com/imask"></script>
<script>
    // const emailInput = document.getElementById('email');

    // const mask = IMask(emailInput, {
    //     mask: '+{380} (00)-00-00-000',
    //     lazy: false,
    // });

    // const form = emailInput.closest('form');
    // form.addEventListener('submit', function () {
    //     emailInput.value = '+' + emailInput.value.replace(/\D/g, '');
    // });

    // function onTurnstileSuccess(token) {
    //     const btn = document.getElementById('login-submit-btn');
    //     if (btn) {
    //         btn.removeAttribute('disabled');
    //         btn.classList.remove('disabled-turnstile');
    //     }
    // }
    //
    // function onTurnstileExpired() {
    //     const btn = document.getElementById('login-submit-btn');
    //     if (btn) {
    //         btn.setAttribute('disabled', 'true');
    //         btn.classList.add('disabled-turnstile');
    //     }
    // }
</script>

</body>
</html>
