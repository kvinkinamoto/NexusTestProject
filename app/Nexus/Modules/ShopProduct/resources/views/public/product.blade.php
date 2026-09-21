@extends('storefront::public.layouts.storefront')

@section('title', $product->meta_title ?: $product->displayName().' — Nexus Store')
@section('meta_description', $product->meta_description ?: Str::limit(strip_tags($product->displayShortDescription()), 160))
@section('og_type', 'product')
@if($product->image)
    @section('og_image', url('/'.ltrim($product->image, '/')))
@endif

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6"
         x-data="{ qty: 1, tab: 'versions' }">

        <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-5 flex-wrap">
            <a href="{{ route('shop.home') }}" class="hover:text-neutral-900">{{ __('Головна') }}</a><span>/</span>
            @if($product->category)
                <a href="{{ route('shop.catalog.category', $product->category->slug) }}" class="hover:text-neutral-900">{{ $product->category->displayName() }}</a><span>/</span>
            @endif
            <span class="text-neutral-900 font-medium">{{ $product->displayName() }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-8 lg:gap-14">
            <!-- ============ GALLERY ============ -->
            <div class="relative ph aspect-[3/4] rounded-2xl overflow-hidden">
                @if($product->image)
                    <img src="{{ '/'.ltrim($product->image, '/') }}" alt="{{ $product->displayName() }}" class="absolute inset-0 size-full object-cover" />
                @else
                    <svg class="size-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-8.25V15M4.5 4.5h15A1.5 1.5 0 0 1 21 6v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 19.5V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                @endif
                @php
                    $productInWishlist = auth()->check()
                        ? auth()->user()->wishlistItems()->where('product_id', $product->id)->exists()
                        : \App\Nexus\Modules\Wishlist\Models\WishlistItem::where('session_id', session()->getId())->where('product_id', $product->id)->exists();
                @endphp
                <div x-data="wishlistButton({{ $product->id }}, {{ $productInWishlist ? 'true' : 'false' }})">
                    <button type="button" @click="toggle" :disabled="busy" class="absolute top-4 right-4 size-9 rounded-full bg-white/90 flex items-center justify-center hover:bg-white" aria-label="{{ __('В обране') }}">
                        <svg class="size-4.5" viewBox="0 0 24 24" :fill="active ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.8" :class="active ? 'text-red-500' : ''"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                    </button>
                </div>
            </div>

            <!-- ============ INFO ============ -->
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm text-neutral-400">{{ __('Артикул') }}: {{ $product->id }}</p>
                    @if($product->package_type)
                        <span class="inline-flex items-center h-5 px-2 rounded-full bg-neutral-100 text-neutral-600 text-xs font-medium">{{ __('shopProduct::translate.'.$product->package_type->value) }}</span>
                    @endif
                </div>
                <h1 class="font-outfit text-2xl sm:text-3xl font-bold mt-1">{{ $product->displayName() }}</h1>

                <div class="flex items-center gap-3 mt-3 flex-wrap">
                    <div class="flex items-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="size-4 {{ $i <= round((float) $averageRating) ? 'text-amber-400 fill-amber-400' : 'text-neutral-300 fill-neutral-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.956a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 0 0-.363 1.118l1.287 3.955c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 0 0-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.539-1.118l1.287-3.955a1 1 0 0 0-.363-1.118L2.062 9.383c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.286-3.956Z" /></svg>
                        @endfor
                    </div>
                    <a href="#reviews" class="text-sm text-neutral-500 hover:text-neutral-900">{{ $averageRating }} · {{ __(':count відгуків', ['count' => $reviews->total()]) }}</a>
                </div>

                <div class="flex items-baseline gap-3 mt-5">
                    @if((float) $price <= 0)
                        <span class="text-2xl sm:text-3xl font-bold text-emerald-600">{{ __('Безкоштовно') }}</span>
                    @else
                        <span class="text-2xl sm:text-3xl font-bold">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($price) }}</span>
                        @if($originalPrice)
                            <span class="text-lg text-neutral-400 line-through">{{ \App\Nexus\Modules\Currency\Services\CurrencyService::formatForDisplay($originalPrice) }}</span>
                        @endif
                    @endif
                </div>

                @if($product->attributeOptions->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                        @foreach($product->attributeOptions->groupBy(fn($o) => $o->attribute?->name) as $attrName => $options)
                            <p><span class="font-medium">{{ $attrName }}:</span> <span class="text-neutral-500">{{ $options->pluck('value')->join(', ') }}</span></p>
                        @endforeach
                    </div>
                @endif

                @if($variationSiblings->count() > 1)
                    <div class="mt-6">
                        <p class="text-sm font-medium mb-2.5">{{ __('Інші варіанти:') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($variationSiblings as $sibling)
                                <a href="{{ route('shop.product', $sibling->slug) }}" class="h-11 px-4 inline-flex items-center rounded-lg border text-sm font-medium {{ $sibling->id === $product->id ? 'border-neutral-900 bg-neutral-900 text-white' : 'border-neutral-300 hover:border-neutral-900' }}">
                                    {{ $sibling->attributeOptions->pluck('value')->join(', ') ?: $sibling->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div x-data="addToCartButton({{ $product->id }})" class="flex flex-col sm:flex-row gap-3 mt-8">
                    <div class="inline-flex items-center h-12 border border-neutral-300 rounded-full px-1.5 w-fit">
                        <button type="button" @click="qty = Math.max(1, qty - 1)" class="size-9 flex items-center justify-center text-lg" aria-label="{{ __('Зменшити') }}">−</button>
                        <span class="w-8 text-center text-sm font-semibold" x-text="qty"></span>
                        <button type="button" @click="qty++" class="size-9 flex items-center justify-center text-lg" aria-label="{{ __('Збільшити') }}">+</button>
                    </div>
                    <button type="button" @click="add(qty)" :disabled="busy" class="flex-1 h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold hover:bg-neutral-800 inline-flex items-center justify-center gap-2">
                        <span x-show="!added">{{ (float) $price <= 0 ? __('Отримати безкоштовно') : __('Купити ліцензію') }}</span>
                        <span x-show="added" x-cloak>{{ __('Додано в кошик ✓') }}</span>
                    </button>
                </div>

                <div class="mt-8 space-y-3 border-t border-neutral-200 pt-6 text-sm">
                    <div class="flex items-center gap-3 text-neutral-600">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5V14.25m0 0V9m0 5.25h13.5m-13.5 0V9m17.25 5.25V9a1.5 1.5 0 0 0-1.5-1.5h-1.5m3 6.75V9m-3 0H4.5" /></svg>
                        {{ __('Довічні оновлення ядра та функціоналу') }}
                    </div>
                    <div class="flex items-center gap-3 text-neutral-600">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        {{ __('Ліцензія на 1 проєкт (додаткові проєкти — окрема покупка)') }}
                    </div>
                </div>

                <div class="mt-6 text-sm text-neutral-600 leading-relaxed">
                    {!! nl2br(e($product->displayDescription() ?: $product->displayShortDescription() ?: __('Опис товару буде додано найближчим часом.'))) !!}
                </div>
            </div>
        </div>

        <!-- ============ TABS ============ -->
        <div class="mt-14 max-w-3xl">
            <div class="flex gap-6 border-b border-neutral-200">
                <button @click="tab = 'versions'" class="pb-3 text-sm font-semibold border-b-2 -mb-px" :class="tab === 'versions' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-400'">{{ __('Версії') }}</button>
                <button @click="tab = 'reviews'" class="pb-3 text-sm font-semibold border-b-2 -mb-px" :class="tab === 'reviews' ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-400'">{{ __('Відгуки') }} ({{ $reviews->total() }})</button>
            </div>

            <div x-show="tab === 'versions'" x-cloak class="py-6 space-y-5">
                @forelse($releases as $release)
                    <div class="flex items-start gap-4 pb-5 border-b border-neutral-100 last:border-0 last:pb-0">
                        <span class="shrink-0 mt-0.5 inline-flex items-center h-6 px-2.5 rounded-full {{ $loop->first ? 'bg-neutral-900 text-white' : 'bg-neutral-100 text-neutral-600' }} text-xs font-semibold">{{ $loop->first ? __('Поточна') : $release->published_at?->format('d.m.Y') }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">{{ __('Версія') }} {{ $release->version }}</p>
                            @if($release->min_core_version)
                                <p class="text-xs text-neutral-400 mt-0.5">{{ __('Потребує Nexus :version+', ['version' => $release->min_core_version]) }}</p>
                            @endif
                            @if($release->changelog)
                                <div class="text-sm text-neutral-600 mt-2 leading-relaxed">{!! nl2br(e($release->changelog)) !!}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-neutral-500">{{ __("Історія версій та changelog з'являться тут найближчим часом.") }}</p>
                @endforelse
                <p class="text-sm text-neutral-500">{{ __('Після покупки всі майбутні оновлення цього модуля доступні безкоштовно у вашому кабінеті.') }}</p>
            </div>

            <div x-show="tab === 'reviews'" x-cloak id="reviews" class="py-6 space-y-6">
                <div class="flex items-center gap-6 pb-6 border-b border-neutral-100">
                    <div class="text-center">
                        <p class="text-4xl font-bold">{{ $averageRating }}</p>
                        <p class="text-xs text-neutral-500 mt-1">{{ __(':count відгуків', ['count' => $reviews->total()]) }}</p>
                    </div>
                    <button type="button" class="ml-auto h-11 px-5 rounded-full border border-neutral-300 text-sm font-semibold hover:border-neutral-900" x-data @click="$refs.reviewForm.classList.toggle('hidden')">{{ __('Залишити відгук') }}</button>
                </div>

                <form x-ref="reviewForm" class="hidden bg-neutral-50 rounded-2xl p-5 space-y-4" x-data="reviewForm({{ $product->id }})" @submit.prevent="submit">
                    <div>
                        <p class="text-sm font-medium mb-2">{{ __('Ваша оцінка') }}</p>
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" class="p-1">
                                    <svg class="size-6" :class="rating >= {{ $i }} ? 'text-amber-400 fill-amber-400' : 'text-neutral-300 fill-neutral-300'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.956a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 0 0-.363 1.118l1.287 3.955c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 0 0-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.539-1.118l1.287-3.955a1 1 0 0 0-.363-1.118L2.062 9.383c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.286-3.956Z" /></svg>
                                </button>
                            @endfor
                        </div>
                    </div>
                    @guest
                        <input type="text" x-model="userName" placeholder="{{ __("Ваше ім'я") }}" required class="w-full h-11 rounded-xl border border-neutral-300 px-4 text-sm">
                    @endguest
                    <textarea x-model="comment" rows="3" placeholder="{{ __('Ваш відгук...') }}" class="w-full rounded-xl border border-neutral-300 px-4 py-3 text-sm"></textarea>
                    <div class="flex items-center gap-3">
                        <button type="submit" :disabled="busy" class="h-11 px-6 rounded-full bg-neutral-900 text-white text-sm font-semibold">{{ __('Надіслати') }}</button>
                        <span x-show="sent" x-cloak class="text-sm text-emerald-600">{{ __('Дякуємо! Відгук на модерації.') }}</span>
                    </div>
                </form>

                <div class="space-y-5">
                    @forelse($reviews as $review)
                        <div class="pb-5 border-b border-neutral-100">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold">{{ $review->user_name ?: $review->user?->name ?: __('Гість') }}</p>
                                <span class="text-xs text-neutral-400">{{ $review->created_at->format('d.m.Y') }}</span>
                            </div>
                            <div class="flex items-center gap-0.5 mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="size-3.5 {{ $i <= $review->rating ? 'text-amber-400 fill-amber-400' : 'text-neutral-300 fill-neutral-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.956a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 0 0-.363 1.118l1.287 3.955c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 0 0-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.539-1.118l1.287-3.955a1 1 0 0 0-.363-1.118L2.062 9.383c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.286-3.956Z" /></svg>
                                @endfor
                            </div>
                            @if($review->comment)
                                <p class="text-sm text-neutral-600 mt-2">{{ $review->comment }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500">{{ __('Поки що немає відгуків. Будьте першим!') }}</p>
                    @endforelse
                </div>

                {{ $reviews->onEachSide(1)->links() }}
            </div>
        </div>

        <!-- ============ RELATED ============ -->
        @if($relatedProducts->isNotEmpty())
            <section class="mt-16">
                <h2 class="font-outfit text-xl sm:text-2xl font-bold mb-5">{{ __('Схожі модулі') }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($relatedProducts as $related)
                        @include('storefront::public.partials.product-card', ['product' => $related])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reviewForm', (productId) => ({
        rating: 0,
        comment: '',
        userName: '',
        busy: false,
        sent: false,
        async submit() {
            if (!this.rating) { alert('{{ __('Оберіть оцінку від 1 до 5') }}'); return; }
            this.busy = true;
            try {
                await window.storefrontFetch('/api/reviews', {
                    method: 'POST',
                    body: { product_id: productId, rating: this.rating, comment: this.comment, user_name: this.userName },
                });
                this.sent = true;
                this.comment = '';
                this.rating = 0;
            } catch (e) {
                alert(e.message);
            } finally {
                this.busy = false;
            }
        },
    }));
});
</script>
@endpush
