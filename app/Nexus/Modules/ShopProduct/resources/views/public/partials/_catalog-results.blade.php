@if($products->isEmpty())
    <div class="py-20 text-center text-neutral-500">
        <p class="text-lg font-medium">{{ __('Товарів не знайдено') }}</p>
        <p class="text-sm mt-1">{{ __('Спробуйте змінити фільтри або пошуковий запит.') }}</p>
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
        @foreach($products as $product)
            @include('storefront::public.partials.product-card', ['product' => $product])
        @endforeach
    </div>

    <div class="mt-10">
        {{ $products->onEachSide(1)->links() }}
    </div>
@endif
