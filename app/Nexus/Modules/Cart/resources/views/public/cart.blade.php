@extends('storefront::public.layouts.storefront')

@section('title', 'Кошик — STYLE')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-4">
            <a href="{{ route('shop.home') }}" class="hover:text-neutral-900">Головна</a><span>/</span>
            <span class="text-neutral-900 font-medium">Кошик</span>
        </nav>
        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">Кошик</h1>

        <div x-data="cartPage()" x-init="init()">
            <template x-if="loading">
                <p class="text-neutral-500 py-10">Завантаження кошика...</p>
            </template>

            <template x-if="!loading && cart && cart.products.length === 0">
                <div class="text-center py-20">
                    <p class="text-neutral-500 mb-6">Ваш кошик порожній.</p>
                    <a href="{{ route('shop.catalog') }}" class="inline-flex h-12 px-8 items-center justify-center rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">Перейти до каталогу</a>
                </div>
            </template>

            <template x-if="!loading && cart && cart.products.length > 0">
                <div class="grid lg:grid-cols-3 gap-10">
                    <div class="lg:col-span-2 space-y-5">
                        <template x-for="item in cart.products" :key="item.id">
                            <div class="flex gap-4 pb-5 border-b border-neutral-100">
                                <a :href="'/product/' + item.slug" class="ph size-24 sm:size-28 rounded-xl overflow-hidden shrink-0">
                                    <img :src="item.image" x-show="item.image" class="size-full object-cover" alt="">
                                </a>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between gap-2">
                                        <a :href="'/product/' + item.slug" class="font-medium text-sm sm:text-base hover:underline" x-text="item.title"></a>
                                        <button type="button" @click="remove(item.id)" class="text-neutral-400 hover:text-red-500 shrink-0" aria-label="Видалити">
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between mt-3">
                                        <div class="inline-flex items-center h-9 border border-neutral-300 rounded-full px-1">
                                            <button type="button" @click="updateQuantity(item.id, item.quantity - 1)" class="size-7 flex items-center justify-center" aria-label="Зменшити">−</button>
                                            <span class="w-7 text-center text-sm font-semibold" x-text="item.quantity"></span>
                                            <button type="button" @click="updateQuantity(item.id, item.quantity + 1)" class="size-7 flex items-center justify-center" aria-label="Збільшити">+</button>
                                        </div>
                                        <span class="font-semibold text-sm sm:text-base" x-text="formatPrice(item.price * item.quantity)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <a href="{{ route('shop.catalog') }}" class="inline-flex items-center gap-2 text-sm font-medium text-neutral-600 hover:text-neutral-900">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                            Продовжити покупки
                        </a>
                    </div>

                    <div>
                        <div class="bg-neutral-50 rounded-2xl p-6 lg:sticky lg:top-24">
                            <h2 class="font-semibold text-lg mb-5">Підсумок замовлення</h2>

                            <div class="flex gap-2 mb-5">
                                <input type="text" x-model="promoCode" placeholder="Промокод" class="flex-1 h-11 rounded-full border border-neutral-300 bg-white px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10">
                                <button type="button" @click="applyPromo()" :disabled="promoBusy" class="h-11 px-5 rounded-full border border-neutral-900 text-sm font-semibold hover:bg-neutral-900 hover:text-white transition">Застосувати</button>
                            </div>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between text-neutral-600">
                                    <span>Товари</span>
                                    <span class="text-neutral-900 font-medium" x-text="formatPrice(cart.subtotal)"></span>
                                </div>
                                <div class="flex justify-between text-neutral-600" x-show="cart.cart_discount_total > 0">
                                    <span>Знижка</span>
                                    <span class="text-red-500 font-medium" x-text="'−' + formatPrice(cart.cart_discount_total)"></span>
                                </div>
                                <div class="flex justify-between text-neutral-600" x-show="cart.promo_code_discount > 0">
                                    <span>Промокод</span>
                                    <span class="text-red-500 font-medium" x-text="'−' + formatPrice(cart.promo_code_discount)"></span>
                                </div>
                                <div class="flex justify-between text-neutral-600" x-show="cart.free_shipping">
                                    <span>Доставка</span>
                                    <span class="text-emerald-600 font-medium">Безкоштовно</span>
                                </div>
                            </div>

                            <div class="flex justify-between items-baseline mt-5 pt-5 border-t border-neutral-200">
                                <span class="font-semibold">Всього</span>
                                <span class="text-2xl font-bold" x-text="formatPrice(cart.total_price)"></span>
                            </div>

                            <a href="{{ route('shop.checkout') }}" class="mt-6 flex items-center justify-center h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800 transition">Оформити замовлення</a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        @if($suggested->isNotEmpty())
            <section class="mt-16">
                <h2 class="font-outfit text-xl sm:text-2xl font-bold mb-5">Вам також може сподобатися</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5">
                    @foreach($suggested as $product)
                        @include('storefront::public.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
