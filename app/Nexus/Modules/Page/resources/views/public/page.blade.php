@extends('storefront::public.layouts.storefront')

@section('title', ($page->meta_title ?: $page->title).' — Nexus Store')
@section('meta_description', $page->meta_description ?: '')

@section('content')
    <article>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-6 pb-2">
            <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-4">
                <a href="{{ route('shop.home') }}" class="hover:text-neutral-900">{{ __('Головна') }}</a><span>/</span>
                <span class="text-neutral-900 font-medium">{{ $page->title }}</span>
            </nav>
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold">{{ $page->title }}</h1>
        </div>

        <div>
            @nexusBlocks($page->blocks)
        </div>
    </article>
@endsection
