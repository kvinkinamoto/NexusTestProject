@use('Mcamara\LaravelLocalization\Facades\LaravelLocalization')
@php
    $wishlistCount = auth()->check()
        ? auth()->user()->wishlistItems()->count()
        : \App\Nexus\Modules\Wishlist\Models\WishlistItem::where('session_id', session()->getId())->count();
    $cartCount = auth()->check()
        ? (auth()->user()->cart?->products()->count() ?? 0)
        : (\App\Nexus\Modules\Cart\Models\Cart::where('session_id', session()->getId())->first()?->products()->count() ?? 0);
    $navCategories = \App\Nexus\Modules\ShopCategory\Models\ShopCategory::query()->publish()->whereIsRoot()->orderBy('name')->get();
    $currentCurrency = \App\Nexus\Modules\Currency\Services\CurrencyService::getSelectedCurrency();
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="currency-code" content="{{ $currentCurrency?->code ?? 'UAH' }}" />
    <meta name="currency-symbol" content="{{ $currentCurrency?->symbol ?? 'грн' }}" />
    <meta name="currency-position" content="{{ $currentCurrency?->symbol_position ?? 'after' }}" />
    <meta name="currency-rate" content="{{ $currentCurrency?->is_base ? 1 : ($currentCurrency?->rate ?? 1) }}" />
    @php
        $ogTitle = trim($__env->yieldContent('title', 'Nexus Store — '.__('Модулі для платформи Nexus')));
        $ogDescription = trim($__env->yieldContent('meta_description', __('Готові платні модулі, плагіни та віджети для Nexus: миттєве завантаження, довічні оновлення, приватний доступ до вихідного коду.')));
        $ogImage = trim($__env->yieldContent('og_image', ''));
        $canonicalUrl = trim($__env->yieldContent('canonical_url', url()->current()));
    @endphp
    <title>{{ $ogTitle }}</title>
    <meta name="description" content="{{ $ogDescription }}" />
    <link rel="canonical" href="{{ $canonicalUrl }}" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('og_type', 'website')" />
    <meta property="og:site_name" content="Nexus Store" />
    <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'uk_UA' }}" />
    <meta property="og:url" content="{{ $canonicalUrl }}" />
    <meta property="og:title" content="{{ $ogTitle }}" />
    <meta property="og:description" content="{{ $ogDescription }}" />
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}" />
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}" />
    <meta name="twitter:title" content="{{ $ogTitle }}" />
    <meta name="twitter:description" content="{{ $ogDescription }}" />
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}" />
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/storefront.js'])
    @include('storefront::public.partials.tracking-scripts', ['position' => 'head'])
</head>
<body class="bg-white text-neutral-900 antialiased font-sans">
@include('storefront::public.partials.tracking-scripts', ['position' => 'body'])

<header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-neutral-200" x-data="{ mobileOpen: false, searchOpen: false }">
    <div class="hidden lg:block border-b border-neutral-100 bg-neutral-50/60">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center justify-between h-9 text-xs text-neutral-500">
            <div class="flex items-center gap-5">
                <a href="{{ url('/delivery') }}" class="hover:text-neutral-900">{{ __('Ліцензування') }}</a>
                <a href="{{ url('/about') }}" class="hover:text-neutral-900">{{ __('Про нас') }}</a>
                <a href="{{ url('/contacts') }}" class="hover:text-neutral-900">{{ __('Контакти') }}</a>
            </div>
            <div class="flex items-center gap-5">
                <div class="flex items-center gap-2">
                    <a href="{{ LaravelLocalization::getLocalizedURL('uk', null, [], true) }}" class="hover:text-neutral-900 {{ app()->getLocale() === 'uk' ? 'font-semibold text-neutral-900' : '' }}">УКР</a>
                    <span>/</span>
                    <a href="{{ LaravelLocalization::getLocalizedURL('en', null, [], true) }}" class="hover:text-neutral-900 {{ app()->getLocale() === 'en' ? 'font-semibold text-neutral-900' : '' }}">EN</a>
                </div>
                <a href="mailto:support@nexus-store.dev" class="hover:text-neutral-900">support@nexus-store.dev</a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 sm:gap-4 h-16 lg:h-20">
            <button class="lg:hidden -ml-2 p-2 text-neutral-700" @click="mobileOpen = true" aria-label="{{ __('Меню') }}">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" /></svg>
            </button>

            <a href="{{ route('shop.home') }}" class="flex flex-col leading-none mr-2 lg:mr-8 shrink-0">
                <span class="font-outfit text-2xl font-bold tracking-tight text-neutral-900">Nexus Store</span>
                <span class="hidden sm:block text-[10px] tracking-[0.25em] text-neutral-400 font-medium">PLATFORM MODULES</span>
            </a>

            <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-neutral-700">
                @if(($navCategories ?? collect())->isNotEmpty())
                    <div class="relative" x-data="{ categoriesOpen: false }" @click.outside="categoriesOpen = false">
                        <button type="button" @click="categoriesOpen = !categoriesOpen" class="flex items-center gap-1 hover:text-neutral-900">
                            {{ __('Категорії') }}
                            <svg class="size-3.5 transition-transform" :class="categoriesOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div x-show="categoriesOpen" x-cloak x-transition.opacity.duration.150ms class="absolute left-0 top-full mt-3 w-[560px] bg-white rounded-2xl shadow-lg border border-neutral-200 p-3 grid grid-cols-2 gap-1 z-50">
                            @foreach($navCategories as $navCategory)
                                <a href="{{ route('shop.catalog.category', $navCategory->slug) }}" class="px-3 py-2 rounded-lg text-sm hover:bg-neutral-50 hover:text-neutral-900">{{ $navCategory->displayName() }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <a href="{{ route('shop.catalog') }}" class="hover:text-neutral-900">{{ __('Каталог модулів') }}</a>
            </nav>

            <div class="flex-1"></div>

            <div class="hidden md:block relative w-56 lg:w-72" x-data="searchAutocomplete()" @click.outside="open = false" @keydown.escape="open = false">
                <form action="{{ route('shop.catalog') }}" method="GET" class="flex items-center relative">
                    <svg class="size-4 absolute left-3.5 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="search" name="q" value="{{ request('q') }}" x-model="query" @input="onInput" @focus="if (results.length) open = true" autocomplete="off" placeholder="{{ __('Знайти модуль...') }}" class="w-full h-10 rounded-full border border-neutral-200 bg-neutral-50 pl-10 pr-4 text-sm placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-300" />
                </form>
                <div x-show="open && results.length" x-cloak class="absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-lg border border-neutral-200 overflow-hidden z-50">
                    <template x-for="p in results" :key="p.slug">
                        <a :href="p.url" class="flex items-center gap-3 p-3 hover:bg-neutral-50 border-b border-neutral-100 last:border-0">
                            <div class="ph size-12 rounded-lg overflow-hidden shrink-0">
                                <img :src="p.image" x-show="p.image" class="size-full object-cover" alt="">
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium truncate" x-text="p.name"></p>
                                <p class="text-xs text-neutral-400" x-text="p.category"></p>
                            </div>
                            <span class="text-sm font-semibold shrink-0" x-text="formatPrice(p.price)"></span>
                        </a>
                    </template>
                    <a :href="moreUrl" class="block text-center text-sm font-medium py-3 bg-neutral-50 hover:bg-neutral-100">{{ __('Усі результати →') }}</a>
                </div>
            </div>

            <button class="md:hidden p-2 text-neutral-700" @click="searchOpen = !searchOpen" aria-label="{{ __('Пошук') }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </button>

            <a href="{{ route('shop.wishlist') }}" class="relative p-2 text-neutral-700 hover:text-neutral-900" aria-label="{{ __('Список бажань') }}">
                <svg class="size-5 sm:size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                <span id="header-wishlist-count" class="absolute -top-0.5 -right-0.5 flex items-center justify-center size-4 rounded-full bg-neutral-900 text-white text-[9px] font-semibold" @if($wishlistCount <= 0) hidden @endif>{{ $wishlistCount }}</span>
            </a>
            <a href="{{ route('shop.cart') }}" class="relative p-2 text-neutral-700 hover:text-neutral-900" aria-label="{{ __('Кошик') }}">
                <svg class="size-5 sm:size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m-3 0h13.5l-.9 9.9a2.25 2.25 0 0 1-2.24 2.05H7.89a2.25 2.25 0 0 1-2.24-2.05L4.75 10.5Z" /></svg>
                <span id="header-cart-count" class="absolute -top-0.5 -right-0.5 flex items-center justify-center size-4 rounded-full bg-neutral-900 text-white text-[9px] font-semibold" @if($cartCount <= 0) hidden @endif>{{ $cartCount }}</span>
            </a>
            <a href="{{ auth()->check() ? route('account.profile.edit') : route('login') }}" class="hidden sm:inline-flex p-2 text-neutral-700 hover:text-neutral-900" aria-label="{{ __('Кабінет') }}">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.964 0a9 9 0 1 0-11.964 0m11.964 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
            </a>
        </div>

        <div x-show="searchOpen" x-cloak class="md:hidden pb-3 relative" x-data="searchAutocomplete()" @keydown.escape="open = false">
            <form action="{{ route('shop.catalog') }}" method="GET" class="relative">
                <svg class="size-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                <input type="search" name="q" value="{{ request('q') }}" x-model="query" @input="onInput" autocomplete="off" placeholder="{{ __('Знайти модуль...') }}" class="w-full h-10 rounded-full border border-neutral-200 bg-neutral-50 pl-10 pr-4 text-sm placeholder:text-neutral-400 focus:outline-none" />
            </form>
            <div x-show="open && results.length" x-cloak class="absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-lg border border-neutral-200 overflow-hidden z-50">
                <template x-for="p in results" :key="p.slug">
                    <a :href="p.url" class="flex items-center gap-3 p-3 hover:bg-neutral-50 border-b border-neutral-100 last:border-0">
                        <div class="ph size-12 rounded-lg overflow-hidden shrink-0">
                            <img :src="p.image" x-show="p.image" class="size-full object-cover" alt="">
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium truncate" x-text="p.name"></p>
                            <p class="text-xs text-neutral-400" x-text="p.category"></p>
                        </div>
                        <span class="text-sm font-semibold shrink-0" x-text="formatPrice(p.price)"></span>
                    </a>
                </template>
                <a :href="moreUrl" class="block text-center text-sm font-medium py-3 bg-neutral-50 hover:bg-neutral-100">{{ __('Усі результати →') }}</a>
            </div>
        </div>
    </div>

    <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-neutral-900/40" @click="mobileOpen = false"></div>
        <div class="absolute inset-y-0 left-0 w-80 max-w-[85%] bg-white shadow-xl flex flex-col"
             x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <div class="flex items-center justify-between h-16 px-5 border-b border-neutral-200">
                <span class="font-outfit text-xl font-bold">Nexus Store</span>
                <button class="p-2 -mr-2" @click="mobileOpen = false" aria-label="{{ __('Закрити') }}">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto px-5 py-6 space-y-1 text-base font-medium">
                @foreach($navCategories ?? [] as $navCategory)
                    <a href="{{ route('shop.catalog.category', $navCategory->slug) }}" class="block py-2.5 border-b border-neutral-100">{{ $navCategory->displayName() }}</a>
                @endforeach
                <a href="{{ route('shop.catalog') }}" class="block py-2.5 border-b border-neutral-100">{{ __('Каталог модулів') }}</a>
                <a href="{{ url('/about') }}" class="block py-2.5 border-b border-neutral-100 text-neutral-500">{{ __('Про нас') }}</a>
                <a href="{{ url('/contacts') }}" class="block py-2.5 border-b border-neutral-100 text-neutral-500">{{ __('Контакти') }}</a>
                <div class="flex items-center gap-3 py-2.5 text-neutral-500">
                    <a href="{{ LaravelLocalization::getLocalizedURL('uk', null, [], true) }}" class="{{ app()->getLocale() === 'uk' ? 'font-semibold text-neutral-900' : '' }}">УКР</a>
                    <span>/</span>
                    <a href="{{ LaravelLocalization::getLocalizedURL('en', null, [], true) }}" class="{{ app()->getLocale() === 'en' ? 'font-semibold text-neutral-900' : '' }}">EN</a>
                </div>
            </nav>
            <div class="px-5 py-5 border-t border-neutral-200 space-y-3">
                @auth
                    <a href="{{ route('account.profile.edit') }}" class="flex items-center gap-2 text-sm font-medium">{{ __('Мій кабінет') }}</a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2 text-sm font-medium">{{ __('Увійти') }}</a>
                @endauth
                <a href="mailto:support@nexus-store.dev" class="flex items-center gap-2 text-sm text-neutral-500">support@nexus-store.dev</a>
            </div>
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="bg-neutral-950 text-neutral-300 mt-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-2 lg:grid-cols-4 gap-10">
        <div class="col-span-2 lg:col-span-1">
            <span class="font-outfit text-2xl font-bold text-white">Nexus Store</span>
            <p class="mt-3 text-sm text-neutral-400 max-w-xs">{{ __('Готові платні модулі, плагіни та віджети для платформи Nexus — з довічними оновленнями та підтримкою розробника.') }}</p>
        </div>
        <div>
            <h4 class="text-white font-semibold text-sm mb-4">{{ __('Навігація') }}</h4>
            <ul class="space-y-2.5 text-sm">
                <li><a href="{{ route('shop.home') }}" class="hover:text-white">{{ __('Головна') }}</a></li>
                <li><a href="{{ route('shop.catalog') }}" class="hover:text-white">{{ __('Каталог модулів') }}</a></li>
                <li><a href="{{ url('/about') }}" class="hover:text-white">{{ __('Про нас') }}</a></li>
                <li><a href="{{ url('/contacts') }}" class="hover:text-white">{{ __('Контакти') }}</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white font-semibold text-sm mb-4">{{ __('Допомога') }}</h4>
            <ul class="space-y-2.5 text-sm">
                <li><a href="{{ url('/delivery') }}" class="hover:text-white">{{ __('Ліцензування') }}</a></li>
                <li><a href="{{ url('/delivery') }}" class="hover:text-white">{{ __('Оновлення та підтримка') }}</a></li>
                @auth
                    <li><a href="{{ route('account.orders') }}" class="hover:text-white">{{ __('Мої замовлення') }}</a></li>
                @endauth
            </ul>
        </div>
        <div>
            <h4 class="text-white font-semibold text-sm mb-4">{{ __('Контакти') }}</h4>
            <ul class="space-y-2.5 text-sm text-neutral-400">
                <li><a href="mailto:support@nexus-store.dev" class="hover:text-white">support@nexus-store.dev</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-neutral-500">
            <p>© {{ date('Y') }} Nexus Store. {{ __('Усі права захищені.') }}</p>
            <div class="flex items-center gap-4">
                @guest
                    <a href="{{ route('login') }}" class="hover:text-neutral-300">{{ __('Увійти') }}</a>
                @endguest
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
