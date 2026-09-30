@extends('storefront::public.layouts.storefront')

@section('title', 'Nexus Store — '.__('Модулі для платформи Nexus'))

@section('content')
    <section id="about-nexus" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8">
        <div class="rounded-3xl bg-neutral-950 text-white px-6 py-10 sm:px-12 sm:py-14">
            <span class="inline-block text-xs font-semibold tracking-[0.2em] text-sand-300 uppercase mb-4">{{ __('Що таке Nexus') }}</span>
            <h1 class="font-outfit text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight max-w-3xl">
                {{ __('Модульна платформа для адмін-панелей на Laravel — швидка у розробці, легка у розширенні') }}
            </h1>
            <p class="mt-5 text-neutral-300 text-base sm:text-lg leading-relaxed max-w-2xl">
                {{ __('Nexus — це фреймворк адмін-панелі, де кожен модуль описує сам себе через PHP 8 атрибути прямо в моделі: без конфіг-файлів, без шаблонного коду, без окремого фронтенд-стеку. Livewire дає реактивний інтерфейс з коробки, а система плагінів дозволяє розширювати чужі модулі — додавати поля, зв\'язки, дії — не торкаючись їхнього коду. Цей магазин, який ви зараз бачите, повністю побудований на Nexus.') }}
            </p>

            <div class="mt-9 grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 max-w-4xl">
                <div>
                    <div class="size-10 rounded-full bg-white/10 flex items-center justify-center mb-3">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    </div>
                    <p class="font-outfit font-semibold text-sm sm:text-base">{{ __('Атрибути замість конфігів') }}</p>
                    <p class="text-xs sm:text-sm text-neutral-400 mt-1">{{ __('Модуль описує поля, форми й таблиці прямо в моделі') }}</p>
                </div>
                <div>
                    <div class="size-10 rounded-full bg-white/10 flex items-center justify-center mb-3">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 8.25h16.5M5.625 20.25h12.75a1.875 1.875 0 0 0 1.875-1.875V5.625A1.875 1.875 0 0 0 18.375 3.75H5.625A1.875 1.875 0 0 0 3.75 5.625v12.75A1.875 1.875 0 0 0 5.625 20.25Z" /></svg>
                    </div>
                    <p class="font-outfit font-semibold text-sm sm:text-base">{{ __('Модульна архітектура') }}</p>
                    <p class="text-xs sm:text-sm text-neutral-400 mt-1">{{ __('Встановлюйте лише те, що потрібно проєкту') }}</p>
                </div>
                <div>
                    <div class="size-10 rounded-full bg-white/10 flex items-center justify-center mb-3">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </div>
                    <p class="font-outfit font-semibold text-sm sm:text-base">{{ __('Livewire під капотом') }}</p>
                    <p class="text-xs sm:text-sm text-neutral-400 mt-1">{{ __('Реактивний інтерфейс без окремого фронтенд-стеку') }}</p>
                </div>
                <div>
                    <div class="size-10 rounded-full bg-white/10 flex items-center justify-center mb-3">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                    </div>
                    <p class="font-outfit font-semibold text-sm sm:text-base">{{ __('Плагіни без форку') }}</p>
                    <p class="text-xs sm:text-sm text-neutral-400 mt-1">{{ __("Розширюйте чужі модулі, не змінюючи їхній код") }}</p>
                </div>
            </div>

            <div class="mt-9 flex flex-wrap items-center gap-4">
                <a href="{{ route('shop.catalog') }}" class="inline-flex items-center gap-2 h-11 px-6 rounded-full bg-white text-neutral-900 text-sm font-semibold hover:bg-neutral-100">
                    {{ __('Переглянути каталог модулів') }}
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                </a>
                <a href="https://github.com/kvinkinamoto/nexus-package" target="_blank" rel="noopener" class="inline-flex items-center gap-2 h-11 px-6 rounded-full border border-white/20 text-white text-sm font-semibold hover:bg-white/10">
                    <svg class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.58.11.79-.25.79-.56 0-.27-.01-1.17-.02-2.12-3.2.7-3.88-1.36-3.88-1.36-.52-1.33-1.28-1.68-1.28-1.68-1.05-.72.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.56-.29-5.25-1.28-5.25-5.7 0-1.26.45-2.29 1.19-3.09-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.18 1.18a11.06 11.06 0 0 1 5.8 0c2.21-1.49 3.18-1.18 3.18-1.18.63 1.59.23 2.76.11 3.05.74.8 1.19 1.83 1.19 3.09 0 4.43-2.69 5.41-5.26 5.69.42.36.78 1.08.78 2.17 0 1.57-.01 2.83-.01 3.22 0 .31.21.68.8.56C20.71 21.39 24 17.07 24 12c0-6.35-5.15-11.5-12-11.5Z"/></svg>
                    {{ __('Репозиторій на GitHub') }}
                </a>
            </div>

            <p class="mt-6 flex items-start gap-2 text-xs sm:text-sm text-neutral-400 max-w-2xl">
                <svg class="size-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                {{ __('Nexus активно розробляється: можливі баги, зміни API та тимчасова нестабільність окремих функцій.') }}
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6" id="hero">
        @include('slider::public.slider', ['key' => 'home'])
    </section>

    <section id="benefits" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
            <div class="flex items-start gap-3">
                <div class="shrink-0 size-11 rounded-full bg-sand-100 flex items-center justify-center text-neutral-800">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5V14.25m0 0V9m0 5.25h13.5m-13.5 0V9m17.25 5.25V9a1.5 1.5 0 0 0-1.5-1.5h-1.5m3 6.75V9m-3 0H4.5" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">{{ __('Миттєве завантаження') }}</p>
                    <p class="text-xs text-neutral-500 mt-0.5">{{ __('Одразу після оплати') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="shrink-0 size-11 rounded-full bg-sand-100 flex items-center justify-center text-neutral-800">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">{{ __('Оновлення ядра') }}</p>
                    <p class="text-xs text-neutral-500 mt-0.5">{{ __('Безкоштовні апдейти версії') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="shrink-0 size-11 rounded-full bg-sand-100 flex items-center justify-center text-neutral-800">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">{{ __('Перевірений код') }}</p>
                    <p class="text-xs text-neutral-500 mt-0.5">{{ __('Сумісність з Nexus гарантована') }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="shrink-0 size-11 rounded-full bg-sand-100 flex items-center justify-center text-neutral-800">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12h.008v.008H8.25V12Zm3.75 0h.008v.008H12V12Zm3.75 0h.008v.008h-.008V12ZM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">{{ __('Підтримка розробника') }}</p>
                    <p class="text-xs text-neutral-500 mt-0.5">{{ __("Ми завжди на зв'язку") }}</p>
                </div>
            </div>
        </div>
    </section>

    @if($newArrivals->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-end justify-between mb-5">
                <h2 class="font-outfit text-xl sm:text-2xl font-bold">{{ __('Нові модулі') }}</h2>
                <a href="{{ route('shop.catalog', ['sort' => 'new']) }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-900">{{ __('Переглянути всі →') }}</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5">
                @foreach($newArrivals as $product)
                    @include('storefront::public.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    @if($popularProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-end justify-between mb-5">
                <h2 class="font-outfit text-xl sm:text-2xl font-bold">{{ __('Популярні модулі') }}</h2>
                <a href="{{ route('shop.catalog') }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-900">{{ __('Переглянути всі →') }}</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5">
                @foreach($popularProducts as $product)
                    @include('storefront::public.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    @if($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <h2 class="font-outfit text-xl sm:text-2xl font-bold mb-5">{{ __('Категорії модулів') }}</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($categories as $cat)
                    <a href="{{ route('shop.catalog.category', $cat->slug) }}" class="group relative ph aspect-[4/3] rounded-2xl overflow-hidden flex flex-col p-5" style="flex-direction:column;justify-content:space-between;align-items:flex-start">
                        @if($cat->image)
                            <img src="{{ '/'.ltrim($cat->image, '/') }}" alt="{{ $cat->displayName() }}" class="absolute inset-0 size-full object-cover" />
                        @else
                            <svg class="absolute inset-0 m-auto size-14 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-8.25V15M4.5 4.5h15A1.5 1.5 0 0 1 21 6v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/40"></div>
                        <h3 class="relative text-white font-outfit font-semibold text-base sm:text-lg drop-shadow">{{ $cat->displayName() }}</h3>
                        <span class="relative text-white/90 text-xs sm:text-sm inline-flex items-center gap-1">{{ __('Перейти') }}
                            <svg style="width:14px;height:14px;opacity:1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
