@extends('storefront::public.layouts.storefront')

@section('title', 'Список бажань — STYLE')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-4">
            <a href="{{ route('shop.home') }}" class="hover:text-neutral-900">Головна</a><span>/</span>
            <span class="text-neutral-900 font-medium">Список бажань</span>
        </nav>
        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">Список бажань <span class="text-neutral-400 font-normal">({{ $products->count() }})</span></h1>

        @if($products->isEmpty())
            <div class="text-center py-20">
                <p class="text-neutral-500 mb-6">Список бажань порожній.</p>
                <a href="{{ route('shop.catalog') }}" class="inline-flex h-12 px-8 items-center justify-center rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800">Перейти до каталогу</a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach($products as $product)
                    @include('storefront::public.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </div>
@endsection
