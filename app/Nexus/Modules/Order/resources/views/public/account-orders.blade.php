@extends('storefront::public.layouts.storefront')

@section('title', 'Мій кабінет — Мої замовлення — Nexus Store')

@section('content')
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">Мій кабінет</h1>

        <div class="grid lg:grid-cols-[240px_1fr] gap-10">
            @include('user::public.partials.account-sidebar', ['active' => 'orders'])

            <div>
                <h2 class="font-semibold text-lg mb-5">Історія замовлень</h2>

                @if($orders->isEmpty())
                    <p class="text-sm text-neutral-500">У вас ще немає замовлень. <a href="{{ route('shop.catalog') }}" class="underline hover:text-neutral-900">Перейти до каталогу</a>.</p>
                @else
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-neutral-500 border-b border-neutral-200">
                                    <th class="py-3 font-medium">№ замовлення</th>
                                    <th class="py-3 font-medium">Дата</th>
                                    <th class="py-3 font-medium">Сума</th>
                                    <th class="py-3 font-medium">Статус</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="py-4 font-medium">#{{ $order->id }}</td>
                                        <td class="py-4 text-neutral-500">{{ $order->created_at->format('d.m.Y') }}</td>
                                        <td class="py-4 font-medium">{{ number_format((float) $order->total_price, 0, '.', ' ') }} грн</td>
                                        <td class="py-4"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-semibold">{{ $order->status?->name ?? '—' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="sm:hidden space-y-3">
                        @foreach($orders as $order)
                            <div class="border border-neutral-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold">#{{ $order->id }}</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-neutral-100 text-neutral-700 text-xs font-semibold">{{ $order->status?->name ?? '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm text-neutral-500">
                                    <span>{{ $order->created_at->format('d.m.Y') }}</span>
                                    <span class="font-semibold text-neutral-900">{{ number_format((float) $order->total_price, 0, '.', ' ') }} грн</span>
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
