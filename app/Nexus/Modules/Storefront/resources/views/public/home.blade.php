@extends('storefront::public.layouts.storefront')

@section('title', 'Nexus Store — Модулі для платформи Nexus')

@section('content')
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
                    <p class="text-sm font-semibold text-neutral-900">Миттєве завантаження</p>
                    <p class="text-xs text-neutral-500 mt-0.5">Одразу після оплати</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="shrink-0 size-11 rounded-full bg-sand-100 flex items-center justify-center text-neutral-800">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">Оновлення ядра</p>
                    <p class="text-xs text-neutral-500 mt-0.5">Безкоштовні апдейти версії</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="shrink-0 size-11 rounded-full bg-sand-100 flex items-center justify-center text-neutral-800">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">Перевірений код</p>
                    <p class="text-xs text-neutral-500 mt-0.5">Сумісність з Nexus гарантована</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="shrink-0 size-11 rounded-full bg-sand-100 flex items-center justify-center text-neutral-800">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12h.008v.008H8.25V12Zm3.75 0h.008v.008H12V12Zm3.75 0h.008v.008h-.008V12ZM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-900">Підтримка розробника</p>
                    <p class="text-xs text-neutral-500 mt-0.5">Ми завжди на зв'язку</p>
                </div>
            </div>
        </div>
    </section>

    @if($newArrivals->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-end justify-between mb-5">
                <h2 class="font-outfit text-xl sm:text-2xl font-bold">Нові модулі</h2>
                <a href="{{ route('shop.catalog', ['sort' => 'new']) }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-900">Переглянути всі →</a>
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
                <h2 class="font-outfit text-xl sm:text-2xl font-bold">Популярні модулі</h2>
                <a href="{{ route('shop.catalog') }}" class="text-sm font-medium text-neutral-500 hover:text-neutral-900">Переглянути всі →</a>
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
            <h2 class="font-outfit text-xl sm:text-2xl font-bold mb-5">Категорії модулів</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($categories as $cat)
                    <a href="{{ route('shop.catalog.category', $cat->slug) }}" class="group relative ph aspect-[4/3] rounded-2xl overflow-hidden flex items-end p-5">
                        @if($cat->image)
                            <img src="{{ '/'.ltrim($cat->image, '/') }}" alt="{{ $cat->name }}" class="absolute inset-0 size-full object-cover" />
                        @else
                            <svg class="absolute inset-0 m-auto size-14 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-8.25V15M4.5 4.5h15A1.5 1.5 0 0 1 21 6v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                        @endif
                        <div class="relative">
                            <h3 class="text-white font-outfit font-semibold text-base sm:text-lg drop-shadow">{{ $cat->name }}</h3>
                            <span class="text-white/90 text-xs sm:text-sm inline-flex items-center gap-1 mt-0.5">Перейти
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                            </span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
