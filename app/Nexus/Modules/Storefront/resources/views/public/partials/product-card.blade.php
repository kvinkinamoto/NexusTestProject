@php
    $cardPricing = \App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductPriceAction::handle($product, 1);
    $inWishlist = auth()->check()
        ? auth()->user()->wishlistItems()->where('product_id', $product->id)->exists()
        : \App\Nexus\Modules\Wishlist\Models\WishlistItem::where('session_id', session()->getId())->where('product_id', $product->id)->exists();
@endphp
<div class="group">
    <div class="relative ph aspect-[3/4] rounded-2xl overflow-hidden">
        <a href="{{ route('shop.product', $product->slug) }}" class="absolute inset-0" aria-label="{{ $product->displayName() }}"></a>
        @if($product->image)
            <img src="{{ '/'.ltrim($product->image, '/') }}" alt="{{ $product->displayName() }}" class="absolute inset-0 size-full object-cover" />
        @else
            <svg class="size-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-8.25V15M4.5 4.5h15A1.5 1.5 0 0 1 21 6v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
        @endif

        <button type="button" x-data="wishlistButton({{ $product->id }}, {{ $inWishlist ? 'true' : 'false' }})" @click="toggle" :disabled="busy"
                class="absolute top-3 right-3 size-8 rounded-full bg-white/90 flex items-center justify-center hover:bg-white z-10" aria-label="{{ __('В обране') }}">
            <svg class="size-4" viewBox="0 0 24 24" :fill="active ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.8" :class="active ? 'text-red-500' : ''">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
            </svg>
        </button>

        <div x-data="addToCartButton({{ $product->id }})" class="absolute inset-x-3 bottom-3 z-10">
            <button type="button" @click="add()" :disabled="busy"
                    class="w-full h-10 rounded-full bg-neutral-900 text-white text-xs font-semibold opacity-0 group-hover:opacity-100 transition items-center justify-center gap-1.5 hidden sm:flex">
                <span x-show="!added">{{ __('Додати в кошик') }}</span>
                <span x-show="added" x-cloak>{{ __('Додано ✓') }}</span>
            </button>
        </div>
    </div>
    <div class="mt-3">
        <p class="text-xs text-neutral-400">{{ $product->category?->displayName() }}</p>
        <a href="{{ route('shop.product', $product->slug) }}" class="text-sm font-medium mt-0.5 block truncate hover:underline">{{ $product->displayName() }}</a>
        <div class="flex items-center gap-2 mt-1">
            @if((float) $cardPricing['price'] <= 0)
                <span class="text-sm font-semibold text-emerald-600">{{ __('Безкоштовно') }}</span>
            @else
                <span class="text-sm font-semibold">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($cardPricing['price']) }}</span>
                @if($cardPricing['original_price'])
                    <span class="text-xs text-neutral-400 line-through">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($cardPricing['original_price']) }}</span>
                @endif
            @endif
        </div>
    </div>
</div>
