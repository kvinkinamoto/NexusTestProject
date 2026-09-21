@extends('storefront::public.layouts.storefront')

@section('title', __('Мій кабінет').' — '.__('Мої замовлення').' — Nexus Store')

@php($paymentStatus = \App\Nexus\Modules\Order\Enums\PaymentStatus::class)

@section('content')
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">{{ __('Мій кабінет') }}</h1>

        <div class="grid lg:grid-cols-[240px_1fr] gap-10">
            @include('user::public.partials.account-sidebar', ['active' => 'orders'])

            <div>
                <h2 class="font-semibold text-lg mb-5">{{ __('Історія замовлень') }}</h2>

                @if(session('status'))
                    <div class="mb-5 rounded-xl bg-green-50 text-green-700 text-sm px-4 py-3">{{ session('status') }}</div>
                @endif

                @if($orders->isEmpty())
                    <p class="text-sm text-neutral-500">{{ __('У вас ще немає замовлень.') }} <a href="{{ route('shop.catalog') }}" class="underline hover:text-neutral-900">{{ __('Перейти до каталогу') }}</a>.</p>
                @else
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-neutral-500 border-b border-neutral-200">
                                    <th class="py-3 font-medium">{{ __('№ замовлення') }}</th>
                                    <th class="py-3 font-medium">{{ __('Дата') }}</th>
                                    <th class="py-3 font-medium">{{ __('Сума') }}</th>
                                    <th class="py-3 font-medium">{{ __('Статус') }}</th>
                                    <th class="py-3 font-medium">{{ __('Оплата') }}</th>
                                    <th class="py-3 font-medium"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="py-4 font-medium"><a href="{{ route('account.orders.show', $order) }}" class="hover:underline">#{{ $order->id }}</a></td>
                                        <td class="py-4 text-neutral-500">{{ $order->created_at->format('d.m.Y') }}</td>
                                        <td class="py-4 font-medium">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($order->total_price) }}</td>
                                        <td class="py-4"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-semibold">{{ $order->status?->name ?? '—' }}</span></td>
                                        <td class="py-4">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $order->payment_status === $paymentStatus::PAID ? 'bg-green-100 text-green-700' : ($order->payment_status === $paymentStatus::CANCELLED ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                                {{ $order->payment_status?->label() ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-right whitespace-nowrap">
                                            <a href="{{ route('account.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 rounded-full border border-neutral-300 text-xs font-semibold hover:bg-neutral-50">{{ __('Переглянути') }}</a>
                                            @if($order->payment_status === $paymentStatus::PENDING && $order->payment_method)
                                                <a href="{{ route('payment.pay', $order->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-full bg-neutral-900 text-white text-xs font-semibold hover:bg-neutral-800">{{ __('Оплатити') }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="sm:hidden space-y-3">
                        @foreach($orders as $order)
                            <div class="border border-neutral-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <a href="{{ route('account.orders.show', $order) }}" class="font-semibold hover:underline">#{{ $order->id }}</a>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-semibold">{{ $order->status?->name ?? '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm text-neutral-500">
                                    <span>{{ $order->created_at->format('d.m.Y') }}</span>
                                    <span class="font-semibold text-neutral-900">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($order->total_price) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2 mt-3 pt-3 border-t border-neutral-100">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $order->payment_status === $paymentStatus::PAID ? 'bg-green-100 text-green-700' : ($order->payment_status === $paymentStatus::CANCELLED ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $order->payment_status?->label() ?? '—' }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('account.orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 rounded-full border border-neutral-300 text-xs font-semibold hover:bg-neutral-50">{{ __('Переглянути') }}</a>
                                        @if($order->payment_status === $paymentStatus::PENDING && $order->payment_method)
                                            <a href="{{ route('payment.pay', $order->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-full bg-neutral-900 text-white text-xs font-semibold hover:bg-neutral-800">{{ __('Оплатити') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">{{ $orders->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
