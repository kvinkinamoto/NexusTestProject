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

</head>
@php $activeLocale = \Modules\Language\Registry\LanguageRegistry::get('is_active'); @endphp
<body @if(isset($activeLocale) && $activeLocale->is_rtl) dir="rtl" class="body-rtl" @else dir="ltr" @endif >


<div class="d-flex flex-column h-100 p-3">
    <div class="d-flex flex-column flex-grow-1">
        <div class="container d-flex align-items-center justify-content-center min-vh-100">
            <div class="card shadow-lg rounded-4 p-4 text-center" style="max-width: 500px; width: 100%;">
                @if (!$userGoogle2faSecret)
                    <h4 class="mb-3">@lang('brilara::brilara.google_authenticator.set_up_authenticator')</h4>
                    <p>
                        @lang('brilara::brilara.google_authenticator.description', ['code' => $secret])
                    </p>
                    <div class="mb-3">
                        {!! $QR_Image !!}
                    </div>
                    <p class="text-muted">
                        @lang('brilara::brilara.google_authenticator.instruction')
                    </p>
                @endif
                <form action="{{ route('admin.2fa.verify') }}"
                      method="POST"
                      class="authentication-form">
                    @csrf
                    @if (!$userGoogle2faSecret && $secret)
                        <input type="hidden" name="secret" value="{{ $secret }}">
                    @endif
                    <div class="mb-3 ">
                        <label class="form-label" for="code">
                            @lang('brilara::brilara.google_authenticator.code')
                        </label>
                        <input
                                id="code"
                                name="code"
                                class="form-control @if($errors->get('code')) is-invalid @endif"
                                placeholder="XXXXXX"
                                value="{{ old('code') }}"
                        >
                        @if($errors->get('code'))
                            <div id="validationCode" class="invalid-feedback">
                                {!! implode('<br>', $errors->get('code')) !!}
                            </div>
                        @endif
                    </div>
                    <div class="mb-1 text-center d-grid">
                        <button class="btn btn-soft-primary" type="submit">
                            @lang('brilara::brilara.google_authenticator.verify')
                        </button>
                    </div>
                </form>
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

</body>
</html>
