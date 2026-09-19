@extends('storefront::public.layouts.storefront')

@section('title', 'Дякуємо за замовлення — Nexus Store')

@section('content')
    <div class="mx-auto max-w-lg px-4 sm:px-6 py-16 sm:py-24 text-center">
        <div class="mx-auto size-20 rounded-full bg-emerald-50 flex items-center justify-center">
            <svg class="size-10 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
        </div>

        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mt-6">Дякуємо за замовлення!</h1>
        <p class="text-neutral-500 mt-3 text-sm sm:text-base">Ваше замовлення успішно оформлено. Посилання для завантаження та ліцензійний ключ надіслано на вашу email-адресу.</p>

        <div class="mt-8 bg-neutral-50 rounded-2xl p-6 text-left space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-neutral-500">Номер замовлення</span><span class="font-semibold">#{{ $order->id }}</span></div>
            <div class="flex justify-between"><span class="text-neutral-500">Дата</span><span class="font-semibold">{{ $order->created_at->format('d.m.Y') }}</span></div>
            <div class="flex justify-between"><span class="text-neutral-500">Сума</span><span class="font-semibold">{{ number_format((float) $order->total_price, 0, '.', ' ') }} грн</span></div>
            @if($order->paymentMethod)
                <div class="flex justify-between"><span class="text-neutral-500">Спосіб оплати</span><span class="font-semibold">{{ $order->paymentMethod->title }}</span></div>
            @endif
            <div class="flex justify-between items-center"><span class="text-neutral-500">Статус</span><span class="inline-flex items-center gap-1.5 text-amber-600 font-semibold"><span class="size-1.5 rounded-full bg-amber-500"></span>{{ $order->status?->name ?? 'Нове' }}</span></div>
        </div>

        @if($order->products->isNotEmpty())
            <div class="mt-6 text-left space-y-2">
                @foreach($order->products as $line)
                    <div class="flex justify-between text-sm text-neutral-600">
                        <span>{{ $line->localizedField('name') }} × {{ $line->quantity }}</span>
                        <span>{{ number_format((float) $line->total_price, 0, '.', ' ') }} грн</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 mt-8">
            <a href="{{ route('account.orders') }}" class="flex-1 h-12 rounded-full border border-neutral-300 text-sm font-semibold flex items-center justify-center hover:border-neutral-900">Мої замовлення</a>
            <a href="{{ route('shop.home') }}" class="flex-1 h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold flex items-center justify-center hover:bg-neutral-800">На головну</a>
        </div>
    </div>
@endsection
