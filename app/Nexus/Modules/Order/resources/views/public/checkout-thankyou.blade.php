@extends('storefront::public.layouts.storefront')

@section('title', __('Дякуємо за замовлення').' — Nexus Store')

@php
    $paymentStatus = $order->payment_status;
    $isPaid = $paymentStatus === \App\Nexus\Modules\Order\Enums\PaymentStatus::PAID;
    $isCancelled = $paymentStatus === \App\Nexus\Modules\Order\Enums\PaymentStatus::CANCELLED;
@endphp

@section('content')
    <div class="mx-auto max-w-lg px-4 sm:px-6 py-16 sm:py-24 text-center">
        @if($isPaid)
            <div class="mx-auto size-20 rounded-full bg-emerald-50 flex items-center justify-center">
                <svg class="size-10 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            </div>
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold mt-6">{{ __('Дякуємо за замовлення!') }}</h1>
            <p class="text-neutral-500 mt-3 text-sm sm:text-base">{{ __('Оплату отримано. Посилання для завантаження та ліцензійний ключ надіслано на вашу email-адресу, а також доступні у розділі') }} <a href="{{ route('account.licenses') }}" class="font-medium text-neutral-900 hover:underline">{{ __('Мої ліцензії') }}</a>.</p>
        @elseif($isCancelled)
            <div class="mx-auto size-20 rounded-full bg-red-50 flex items-center justify-center">
                <svg class="size-10 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </div>
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold mt-6">{{ __('Оплату не підтверджено') }}</h1>
            <p class="text-neutral-500 mt-3 text-sm sm:text-base">{{ __('Оплата не пройшла або була скасована. Спробуйте оформити замовлення ще раз або зверніться в підтримку.') }}</p>
        @else
            <div class="mx-auto size-20 rounded-full bg-amber-50 flex items-center justify-center">
                <svg class="size-10 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" /><circle cx="12" cy="12" r="9" /></svg>
            </div>
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold mt-6">{{ __('Замовлення оформлено') }}</h1>
            <p class="text-neutral-500 mt-3 text-sm sm:text-base">{{ __('Очікуємо підтвердження оплати від платіжної системи — це зазвичай займає лічені хвилини. Ліцензійний ключ і посилання для завантаження надішлемо на email одразу після підтвердження.') }}</p>
        @endif

        <div class="mt-8 bg-neutral-50 rounded-2xl p-6 text-left space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-neutral-500">{{ __('Номер замовлення') }}</span><span class="font-semibold">#{{ $order->id }}</span></div>
            <div class="flex justify-between"><span class="text-neutral-500">{{ __('Дата') }}</span><span class="font-semibold">{{ $order->created_at->format('d.m.Y') }}</span></div>
            <div class="flex justify-between"><span class="text-neutral-500">{{ __('Сума') }}</span><span class="font-semibold">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($order->total_price) }}</span></div>
            @if($order->paymentMethod)
                <div class="flex justify-between"><span class="text-neutral-500">{{ __('Спосіб оплати') }}</span><span class="font-semibold">{{ $order->paymentMethod->title }}</span></div>
            @endif
            <div class="flex justify-between items-center">
                <span class="text-neutral-500">{{ __('Оплата') }}</span>
                @if($isPaid)
                    <span class="inline-flex items-center gap-1.5 text-emerald-600 font-semibold"><span class="size-1.5 rounded-full bg-emerald-500"></span>{{ __('Оплачено') }}</span>
                @elseif($isCancelled)
                    <span class="inline-flex items-center gap-1.5 text-red-600 font-semibold"><span class="size-1.5 rounded-full bg-red-500"></span>{{ __('Скасовано') }}</span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-amber-600 font-semibold"><span class="size-1.5 rounded-full bg-amber-500"></span>{{ __('Очікує оплати') }}</span>
                @endif
            </div>
            <div class="flex justify-between items-center"><span class="text-neutral-500">{{ __('Статус замовлення') }}</span><span class="font-semibold">{{ $order->status?->name ?? __('Нове') }}</span></div>
        </div>

        @if($order->products->isNotEmpty())
            <div class="mt-6 text-left space-y-2">
                @foreach($order->products as $line)
                    <div class="flex justify-between text-sm text-neutral-600">
                        <span>{{ $line->localizedField('name') }} × {{ $line->quantity }}</span>
                        <span>{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($line->total_price) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 mt-8">
            <a href="{{ route('account.orders') }}" class="flex-1 h-12 rounded-full border border-neutral-300 text-sm font-semibold flex items-center justify-center hover:border-neutral-900">{{ __('Мої замовлення') }}</a>
            <a href="{{ route('shop.home') }}" class="flex-1 h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold flex items-center justify-center hover:bg-neutral-800">{{ __('На головну') }}</a>
        </div>
    </div>
@endsection
