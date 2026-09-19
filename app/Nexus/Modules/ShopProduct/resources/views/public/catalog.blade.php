@extends('storefront::public.layouts.storefront')

@section('title', ($category->name ?? 'Каталог модулів').' — Nexus Store')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6" x-data="catalogFilters()">

        <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-4 flex-wrap">
            <a href="{{ route('shop.home') }}" class="hover:text-neutral-900">Головна</a>
            <span>/</span>
            @if($category)
                <a href="{{ route('shop.catalog') }}" @click.prevent="load($el.href)" class="hover:text-neutral-900">Каталог модулів</a>
                <span>/</span>
                <span class="text-neutral-900 font-medium">{{ $category->name }}</span>
            @else
                <span class="text-neutral-900 font-medium">Каталог модулів</span>
            @endif
        </nav>

        <div class="flex items-center justify-between gap-4 mb-6">
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold">{{ $category->name ?? 'Каталог модулів' }}</h1>
            <p class="hidden sm:block text-sm text-neutral-500" id="catalog-count">{{ $products->total() }} модулів</p>
        </div>

        <div class="flex gap-8">
            <!-- ============ FILTERS (desktop sidebar / mobile drawer) ============ -->
            <div :class="filtersOpen ? 'fixed inset-0 z-50 flex' : 'hidden lg:block'">
                <div class="absolute inset-0 bg-neutral-900/40 lg:hidden" @click="filtersOpen = false"></div>

                <form x-ref="form" action="{{ $category ? route('shop.catalog.category', $category->slug) : route('shop.catalog') }}" method="GET"
                      @change="submit()" @submit.prevent="submit()"
                      class="relative w-72 lg:w-64 shrink-0 space-y-7 bg-white h-full lg:h-auto overflow-y-auto p-5 lg:p-0">
                    @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}" />
                    @endif
                    <input type="hidden" name="sort" x-ref="sortInput" value="{{ request('sort') }}" />

                    <div class="flex items-center justify-between lg:hidden">
                        <span class="font-semibold">Фільтри</span>
                        <button type="button" @click="filtersOpen = false" class="p-1" aria-label="Закрити">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div>
                        <h3 class="font-semibold text-sm mb-3">Категорія</h3>
                        <ul class="space-y-2.5 text-sm text-neutral-600">
                            <li><a href="{{ route('shop.catalog') }}" @click.prevent="load($el.href)" class="{{ ! $category ? 'text-neutral-900 font-medium' : 'hover:text-neutral-900' }}">Усі категорії</a></li>
                            @foreach($categories as $cat)
                                <li><a href="{{ route('shop.catalog.category', $cat->slug) }}" @click.prevent="load($el.href)" class="{{ $category?->id === $cat->id ? 'text-neutral-900 font-medium' : 'hover:text-neutral-900' }}">{{ $cat->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="pt-6 border-t border-neutral-200">
                        <h3 class="font-semibold text-sm mb-3">Тип пакета</h3>
                        <ul class="space-y-2.5 text-sm text-neutral-600">
                            <li><label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="radio" name="package_type" value="" @checked(! $selectedPackageType) class="size-4 border-neutral-300 text-neutral-900 focus:ring-neutral-900"> Усі типи</label></li>
                            @foreach($packageTypes as $type)
                                <li><label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="radio" name="package_type" value="{{ $type->value }}" @checked($selectedPackageType === $type) class="size-4 border-neutral-300 text-neutral-900 focus:ring-neutral-900"> {{ __('shopProduct::translate.'.$type->value) }}</label></li>
                            @endforeach
                        </ul>
                    </div>

                    @foreach($filterOptions as $attributeName => $options)
                        <div class="pt-6 border-t border-neutral-200">
                            <h3 class="font-semibold text-sm mb-3">{{ $attributeName }}</h3>
                            <div class="flex flex-wrap gap-2.5">
                                @foreach($options as $option)
                                    <label class="inline-flex items-center gap-1.5 text-sm cursor-pointer">
                                        <input type="checkbox" name="attr[]" value="{{ $option->id }}" @checked(in_array($option->id, $selectedAttrIds)) class="size-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900">
                                        @if($option->color)
                                            <span class="size-4 rounded-full border border-neutral-200" style="background-color: {{ $option->color }}"></span>
                                        @endif
                                        {{ $option->value }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-6 border-t border-neutral-200">
                        <h3 class="font-semibold text-sm mb-3">Ціна, грн</h3>
                        <div class="flex items-center gap-2">
                            <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Від" min="0" class="w-full h-10 rounded-lg border border-neutral-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10">
                            <span class="text-neutral-400">–</span>
                            <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="До" min="0" class="w-full h-10 rounded-lg border border-neutral-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/10">
                        </div>
                    </div>

                    <a href="{{ $category ? route('shop.catalog.category', $category->slug) : route('shop.catalog') }}" @click.prevent="load($el.href)" class="inline-block text-sm font-medium text-neutral-500 hover:text-neutral-900 underline underline-offset-4">Скинути фільтри</a>

                    <button type="button" @click="filtersOpen = false" class="lg:hidden w-full h-12 rounded-full bg-neutral-900 text-white text-sm font-semibold">Показати модулі</button>
                </form>
            </div>

            <!-- ============ RESULTS ============ -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-3 mb-5 pb-4 border-b border-neutral-200">
                    <button type="button" class="lg:hidden inline-flex items-center gap-2 h-10 px-4 rounded-full border border-neutral-300 text-sm font-medium" @click="filtersOpen = true">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m9 12h3.75M16.5 18a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 18H13.5M6.75 12h13.5M6.75 12a1.5 1.5 0 1 0-3 0m3 0a1.5 1.5 0 1 1-3 0" /></svg>
                        Фільтри
                    </button>
                    <p class="sm:hidden text-xs text-neutral-500">{{ $products->total() }} модулів</p>

                    <select name="sort" @change="$refs.sortInput.value = $event.target.value; submit()" class="ml-auto h-10 px-4 rounded-full border border-neutral-300 text-sm font-medium bg-white focus:outline-none">
                        <option value="" {{ request('sort') ? '' : 'selected' }}>За популярністю</option>
                        <option value="new" {{ request('sort') === 'new' ? 'selected' : '' }}>За новизною</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>За ціною (зростання)</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>За ціною (спадання)</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>За назвою</option>
                    </select>
                </div>

                <div id="catalog-results" @click="if ($event.target.closest('nav[role=navigation] a')) { $event.preventDefault(); load($event.target.closest('a').href); }">
                    @include('shopProduct::public.partials._catalog-results', ['products' => $products])
                </div>
            </div>
        </div>
    </div>
@endsection
