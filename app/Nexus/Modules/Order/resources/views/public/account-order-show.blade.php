@extends('storefront::public.layouts.storefront')

@section('title', __('Мій кабінет').' — '.__('Замовлення').' #'.$order->id.' — Nexus Store')

@php($paymentStatus = \App\Nexus\Modules\Order\Enums\PaymentStatus::class)
@php($editable = $order->payment_status !== $paymentStatus::PAID)

@section('content')
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-4">
            <a href="{{ route('account.orders') }}" class="hover:text-neutral-900">{{ __('Мої замовлення') }}</a><span>/</span>
            <span class="text-neutral-900 font-medium">#{{ $order->id }}</span>
        </nav>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold">{{ __('Замовлення') }} #{{ $order->id }}</h1>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-semibold">{{ $order->status?->name ?? '—' }}</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $order->payment_status === $paymentStatus::PAID ? 'bg-green-100 text-green-700' : ($order->payment_status === $paymentStatus::CANCELLED ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                    {{ $order->payment_status?->label() ?? '—' }}
                </span>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-6 rounded-xl bg-green-50 text-green-700 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif

        <div class="grid lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-8">
                <section>
                    <h2 class="font-semibold text-lg mb-4">{{ __('Склад замовлення') }}</h2>
                    <div class="space-y-4">
                        @foreach($order->products as $line)
                            <div class="flex gap-3 border border-neutral-200 rounded-xl p-4">
                                <div class="ph size-14 rounded-lg overflow-hidden shrink-0">
                                    @if($line->localizedField('image'))
                                        <img src="{{ '/'.ltrim($line->localizedField('image'), '/') }}" class="size-full object-cover" alt="">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    @if($line->product)
                                        <a href="{{ route('shop.product', $line->product->slug) }}" class="text-sm font-medium hover:underline">{{ $line->localizedField('name') ?? $line->product->displayName() }}</a>
                                    @else
                                        <p class="text-sm font-medium">{{ $line->localizedField('name') ?? __('Товар видалено') }}</p>
                                    @endif
                                    <p class="text-xs text-neutral-500 mt-0.5">{{ __('Кількість:') }} {{ $line->quantity }}</p>
                                </div>
                                <span class="text-sm font-semibold shrink-0">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($line->total_price) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 text-sm pt-5 mt-5 border-t border-neutral-200">
                        <div class="flex justify-between text-neutral-600"><span>{{ __('Модулі') }}</span><span class="text-neutral-900 font-medium">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($order->products_price ?? $order->total_price) }}</span></div>
                        <div class="flex justify-between items-baseline pt-3 border-t border-neutral-200">
                            <span class="font-semibold">{{ __('Всього') }}</span>
                            <span class="text-xl font-bold">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($order->total_price) }}</span>
                        </div>
                    </div>
                </section>

                <section class="pt-6 border-t border-neutral-100">
                    <h2 class="font-semibold text-lg mb-4">{{ __('Контактні дані') }}</h2>

                    @if($editable)
                        <form method="POST" action="{{ route('account.orders.update', $order) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1.5">{{ __("Ім'я") }} *</label>
                                    <input required name="user_name" value="{{ old('user_name', $order->user_name) }}" type="text" class="w-full h-11 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                                    @error('user_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1.5">{{ __('Прізвище') }}</label>
                                    <input name="user_last_name" value="{{ old('user_last_name', $order->user_last_name) }}" type="text" class="w-full h-11 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1.5">{{ __('Телефон') }} *</label>
                                    <input required name="user_phone" value="{{ old('user_phone', $order->user_phone) }}" type="tel" class="w-full h-11 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                                    @error('user_phone')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1.5">Email</label>
                                    <input name="user_email" value="{{ old('user_email', $order->user_email) }}" type="email" class="w-full h-11 rounded-xl border border-neutral-300 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400" />
                                    @error('user_email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5">{{ __('Коментар до замовлення') }}</label>
                                <textarea name="user_comment" rows="3" class="w-full rounded-xl border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10 focus:border-neutral-400">{{ old('user_comment', $order->user_comment) }}</textarea>
                            </div>
                            <label class="flex items-center gap-2.5 text-sm text-neutral-600">
                                <input type="checkbox" name="needs_phone_call" value="1" @checked(old('needs_phone_call', $order->needs_phone_call)) class="size-4 rounded border-neutral-300">
                                {{ __('Зателефонувати для підтвердження замовлення') }}
                            </label>
                            <button type="submit" class="h-11 px-6 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">{{ __('Зберегти зміни') }}</button>
                        </form>
                    @else
                        <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                            <div><dt class="text-neutral-500">{{ __("Ім'я") }}</dt><dd class="font-medium">{{ $order->user_name }} {{ $order->user_last_name }}</dd></div>
                            <div><dt class="text-neutral-500">{{ __('Телефон') }}</dt><dd class="font-medium">{{ $order->user_phone }}</dd></div>
                            <div><dt class="text-neutral-500">Email</dt><dd class="font-medium">{{ $order->user_email ?: '—' }}</dd></div>
                            @if($order->user_comment)
                                <div class="sm:col-span-2"><dt class="text-neutral-500">{{ __('Коментар') }}</dt><dd class="font-medium">{{ $order->user_comment }}</dd></div>
                            @endif
                        </dl>
                        <p class="text-xs text-neutral-400 mt-4">{{ __('Замовлення оплачено — контактні дані вже не можна змінити.') }}</p>
                    @endif
                </section>
            </div>

            <div class="space-y-4">
                @if($order->payment_status === $paymentStatus::PENDING && $order->payment_method)
                    <a href="{{ route('payment.pay', $order->id) }}" class="block text-center h-12 leading-[3rem] rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">{{ __('Оплатити') }}</a>
                @endif

                @if($editable)
                    <form method="POST" action="{{ route('account.orders.destroy', $order) }}" onsubmit="return confirm('{{ __('Видалити це замовлення? Дію неможливо скасувати.') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full h-12 rounded-full border border-red-200 text-red-600 text-sm font-semibold hover:bg-red-50">{{ __('Видалити замовлення') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
