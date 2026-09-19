@extends('storefront::public.layouts.storefront')

@section('title', 'Оформлення замовлення — Nexus Store')

@section('content')
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-4">
            <a href="{{ route('shop.home') }}" class="hover:text-neutral-900">Головна</a><span>/</span>
            <a href="{{ route('shop.cart') }}" class="hover:text-neutral-900">Кошик</a><span>/</span>
            <span class="text-neutral-900 font-medium">Оформлення замовлення</span>
        </nav>
        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">Оформлення замовлення</h1>

        @php($user = auth()->user())
        <div class="grid lg:grid-cols-3 gap-10">
                <form x-data="checkoutForm()" @submit.prevent="submit($el)" class="lg:col-span-2 space-y-8">
                    <section>
                        <h2 class="font-semibold text-lg mb-4">Контактні дані</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Ім'я *</label>
                                <input required name="user_name" value="{{ old('user_name', $user?->name) }}" type="text" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Прізвище</label>
                                <input name="user_last_name" value="{{ old('user_last_name', $user?->last_name) }}" type="text" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Телефон *</label>
                                <input required name="user_phone" value="{{ old('user_phone', $user?->phone) }}" type="tel" placeholder="+38 (099) 123 45 67" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5">Email</label>
                                <input name="user_email" value="{{ old('user_email', $user?->email) }}" type="email" class="w-full h-12 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                            </div>
                        </div>
                        <label class="flex items-center gap-2.5 mt-4 text-sm text-neutral-600">
                            <input type="checkbox" name="needs_phone_call" value="1" checked class="size-4 rounded border-neutral-300">
                            Зателефонувати для підтвердження замовлення
                        </label>
                    </section>

                    @if($paymentMethods->isNotEmpty())
                        <section class="pt-6 border-t border-neutral-100">
                            <h2 class="font-semibold text-lg mb-4">Оплата</h2>
                            <div class="space-y-3" x-data="{ payment: '{{ $paymentMethods->first()?->code }}' }">
                                @foreach($paymentMethods as $pm)
                                    <label class="flex items-center gap-3 border rounded-xl p-4 cursor-pointer" :class="payment === '{{ $pm->code }}' ? 'border-neutral-900 ring-1 ring-neutral-900' : 'border-neutral-200'">
                                        <input type="radio" name="payment_method" value="{{ $pm->code }}" data-type="{{ $pm->type }}" x-model="payment" @if($loop->first) checked @endif class="size-4">
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold">{{ $pm->title }}</p>
                                            @if($pm->description)
                                                <p class="text-xs text-neutral-500">{{ $pm->description }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="pt-6 border-t border-neutral-100">
                        <label class="block text-sm font-medium mb-1.5">Коментар до замовлення</label>
                        <textarea name="user_comment" rows="3" placeholder="Побажання чи запитання щодо замовлення..." class="w-full rounded-xl border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400"></textarea>
                    </section>

                    <button type="submit" :disabled="busy" class="w-full h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">
                        <span x-show="!busy">Підтвердити замовлення</span>
                        <span x-show="busy" x-cloak>Оформлення...</span>
                    </button>
                </form>

                <div x-data="cartPage()" x-init="init()">
                    <div class="bg-neutral-50 rounded-2xl p-6 lg:sticky lg:top-8">
                        <h2 class="font-semibold text-lg mb-5">Ваше замовлення</h2>

                        <template x-if="loading"><p class="text-sm text-neutral-500">Завантаження...</p></template>

                        <template x-if="!loading && cart">
                            <div>
                                <div class="space-y-4 max-h-64 overflow-y-auto pr-1">
                                    <template x-for="item in cart.products" :key="item.id">
                                        <div class="flex gap-3">
                                            <div class="ph size-14 rounded-lg overflow-hidden shrink-0">
                                                <img :src="item.image" x-show="item.image" class="size-full object-cover" alt="">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium truncate" x-text="item.title"></p>
                                                <p class="text-xs text-neutral-500" x-text="'Кількість: ' + item.quantity"></p>
                                            </div>
                                            <span class="text-sm font-semibold shrink-0" x-text="formatPrice(item.price * item.quantity)"></span>
                                        </div>
                                    </template>
                                </div>

                                <div class="space-y-3 text-sm pt-5 mt-5 border-t border-neutral-200">
                                    <div class="flex justify-between text-neutral-600"><span>Модулі</span><span class="text-neutral-900 font-medium" x-text="formatPrice(cart.subtotal)"></span></div>
                                    <div class="flex justify-between text-neutral-600" x-show="cart.cart_discount_total > 0"><span>Знижка</span><span class="text-red-500 font-medium" x-text="'−' + formatPrice(cart.cart_discount_total)"></span></div>
                                </div>
                                <div class="flex justify-between items-baseline mt-5 pt-5 border-t border-neutral-200">
                                    <span class="font-semibold">Всього</span>
                                    <span class="text-2xl font-bold" x-text="formatPrice(cart.total_price)"></span>
                                </div>
                                <p class="text-xs text-neutral-400 mt-3">Посилання на завантаження та ліцензійний ключ будуть надіслані на email після оплати.</p>
                            </div>
                        </template>
                    </div>
                </div>
        </div>
    </div>
@endsection
