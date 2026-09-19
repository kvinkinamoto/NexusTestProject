<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('Вхід') }} — Nexus Store</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/storefront.js'])
</head>
<body class="bg-white text-neutral-900 antialiased font-sans">

<div class="min-h-screen grid lg:grid-cols-2">
    <div class="flex flex-col justify-center px-6 sm:px-12 lg:px-20 py-12">
        <a href="{{ route('shop.home') }}" class="flex flex-col leading-none mb-10">
            <span class="font-outfit text-2xl font-bold tracking-tight">Nexus Store</span>
            <span class="text-[10px] tracking-[0.25em] text-neutral-400 font-medium">PLATFORM MODULES</span>
        </a>

        <div class="max-w-sm w-full mx-auto lg:mx-0">
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold">{{ __('Вхід в акаунт') }}</h1>
            <p class="text-neutral-500 text-sm mt-2">{{ __('Раді бачити вас знову! Введіть дані для входу.') }}</p>

            @if(session('social_auth_error'))
                <p class="mt-4 text-sm text-red-600 bg-red-50 rounded-lg px-4 py-3">{{ session('social_auth_error') }}</p>
            @endif

            <form action="{{ route('login') }}" method="POST" class="mt-8 space-y-4" x-data="{ showPass: false }">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium mb-1.5">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder="ivan@example.com"
                           class="w-full h-12 rounded-xl border px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 {{ $errors->has('email') ? 'border-red-400' : 'border-neutral-300 focus:border-neutral-400' }}" />
                    @error('email')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium mb-1.5">{{ __('Пароль') }}</label>
                    <div class="relative">
                        <input id="password" name="password" :type="showPass ? 'text' : 'password'" required placeholder="••••••••"
                               class="w-full h-12 rounded-xl border px-4 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 {{ $errors->has('password') ? 'border-red-400' : 'border-neutral-300 focus:border-neutral-400' }}" />
                        <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400" aria-label="{{ __('Показати пароль') }}">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                    </div>
                    @error('password')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-neutral-600"><input type="checkbox" name="remember" value="1" class="size-4 rounded border-neutral-300"> {{ __("Запам'ятати мене") }}</label>
                    <a href="{{ route('shop.forgot-password') }}" class="font-medium hover:underline">{{ __('Забули пароль?') }}</a>
                </div>
                <button type="submit" class="w-full h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">{{ __('Увійти') }}</button>
            </form>

            <p class="text-center text-sm text-neutral-500 mt-8">{{ __('Немає акаунту?') }} <a href="{{ route('shop.register') }}" class="font-semibold text-neutral-900 hover:underline">{{ __('Зареєструватися') }}</a></p>
        </div>
    </div>

    <div class="hidden lg:block relative ph">
        <svg class="absolute inset-0 m-auto size-32 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-8.25V15M4.5 4.5h15A1.5 1.5 0 0 1 21 6v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        <div class="absolute bottom-12 left-12 right-12 text-white">
            <p class="text-xs font-semibold tracking-[0.2em] uppercase text-white/80 mb-2">{{ __('Нові надходження') }}</p>
            <h2 class="font-outfit text-3xl font-bold">{{ __('Свіжі модулі щотижня') }}</h2>
        </div>
    </div>
</div>

</body>
</html>
