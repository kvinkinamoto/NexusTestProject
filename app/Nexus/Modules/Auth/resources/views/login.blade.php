<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>

    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('nexus/css/icons.min.css') }}" type="text/css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>

<body class="h-full font-outfit bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-white/90">

<div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <div class="mb-8 flex justify-center">
            <img src="{{ asset('nexus/images/logo.svg') }}" alt="Logo" class="h-10 dark:hidden">
            <img src="{{ asset('nexus/images/logo-light.svg') }}" alt="Logo" class="hidden h-10 dark:block">
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg sm:p-8 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-2 text-title-sm font-semibold text-gray-800 dark:text-white/90">
                @lang('auth::translate.sign_in')
            </h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                @lang('auth::translate.enter_your_email_and_password_to_access_admin_panel')
            </p>

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        @lang('auth::translate.email')
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="text"
                        placeholder="Enter your email"
                        value="{{ old('email') }}"
                        class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-900 dark:text-white/90 {{ $errors->get('email') ? 'border-error-500 focus:ring-3 focus:ring-error-500/10' : 'border-gray-300 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700' }}"
                    >
                    @if($errors->get('email'))
                        <p id="validationemail" class="mt-1.5 text-xs text-error-500">
                            {!! implode('<br>', $errors->get('email')) !!}
                        </p>
                    @endif
                </div>

                <div class="mb-4">
                    <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        @lang('auth::translate.password')
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            value="{{ old('password') }}"
                            class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50 dark:bg-gray-900 dark:text-white/90 {{ $errors->get('password') ? 'border-error-500 focus:ring-3 focus:ring-error-500/10' : 'border-gray-300 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700' }}"
                        >
                        <button type="button" id="togglePasswordBtn" tabindex="-1"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i id="togglePasswordIcon" class="{{ nexus_icon('eye') }}"></i>
                        </button>
                    </div>
                    @if($errors->get('password'))
                        <p id="validationPassword" class="mt-1.5 text-xs text-error-500">
                            {!! implode('<br>', $errors->get('password')) !!}
                        </p>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                        <input type="checkbox" id="remember" name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700">
                        @lang('auth::translate.remeber')
                    </label>
                    @if($errors->get('remember'))
                        <p class="mt-1.5 text-xs text-error-500">
                            {!! implode('<br>', $errors->get('remember')) !!}
                        </p>
                    @endif
                </div>

                <div class="cf-turnstile mb-4"
                     data-sitekey="{{ config('brilara.cf_turnstile_site_key') }}"
                     data-callback="onTurnstileSuccess"
                     data-expired-callback="onTurnstileExpired">
                </div>
                @if($errors->get('cf-turnstile-response'))
                    <p id="validationCfTurnstile" class="mb-4 text-xs text-error-500">
                        {!! implode('<br>', $errors->get('cf-turnstile-response')) !!}
                    </p>
                @endif

                <button type="submit" id="login-submit-btn"
                    class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                    @lang('auth::translate.sign_in')
                </button>
            </form>
        </div>

        <footer class="mt-6 text-center text-xs text-gray-400 dark:text-gray-500">
            {{ date('Y') }} Made by
            <a href="https://nodexsoft.com" class="font-medium text-gray-500 hover:text-brand-500 dark:text-gray-400" target="_blank">Nodex</a>
        </footer>
    </div>
</div>

@yield('js')

<script>
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

    (function () {
        const input = document.getElementById('password');
        const btn = document.getElementById('togglePasswordBtn');
        const icon = document.getElementById('togglePasswordIcon');
        if (!input || !btn || !icon) return;

        btn.addEventListener('click', function () {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            icon.className = showing ? '{{ nexus_icon('eye') }}' : '{{ nexus_icon('eye_closed') }}';
        });
    })();
</script>

</body>
</html>
