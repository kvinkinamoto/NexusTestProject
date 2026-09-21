@extends('storefront::public.layouts.storefront')

@section('title', (isset($category) ? $category->name.' — ' : '').__('Блог').' — Nexus Store')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-4 flex-wrap">
            <a href="{{ route('shop.home') }}" class="hover:text-neutral-900">{{ __('Головна') }}</a>
            <span>/</span>
            @if(isset($category))
                <a href="{{ route('nexus.blogPost.index') }}" class="hover:text-neutral-900">{{ __('Блог') }}</a>
                <span>/</span>
                <span class="text-neutral-900 font-medium">{{ $category->name }}</span>
            @else
                <span class="text-neutral-900 font-medium">{{ __('Блог') }}</span>
            @endif
        </nav>

        <h1 class="font-outfit text-2xl sm:text-3xl font-bold mb-8">{{ isset($category) ? $category->name : __('Блог') }}</h1>

        @if($posts->isEmpty())
            <p class="text-sm text-neutral-500">{{ __('Поки що немає жодної статті.') }}</p>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posts as $post)
                    <article class="group">
                        <a href="{{ route('nexus.blogPost.show', $post->slug) }}" class="block ph aspect-[16/10] rounded-2xl overflow-hidden">
                            @if($post->featured_image)
                                <img src="{{ '/'.ltrim($post->featured_image, '/') }}" alt="{{ $post->title }}" class="size-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            @endif
                        </a>
                        <div class="mt-3">
                            @if($post->category)
                                <a href="{{ route('nexus.blogPost.category', $post->category->slug) }}" class="text-xs font-medium text-neutral-500 hover:text-neutral-900">{{ $post->category->name }}</a>
                            @endif
                            <h2 class="font-semibold mt-1"><a href="{{ route('nexus.blogPost.show', $post->slug) }}" class="hover:underline">{{ $post->title }}</a></h2>
                            @if($post->excerpt)
                                <p class="text-sm text-neutral-500 mt-1.5 line-clamp-2">{{ $post->excerpt }}</p>
                            @endif
                            @if($post->published_at)
                                <time datetime="{{ $post->published_at->toDateString() }}" class="block text-xs text-neutral-400 mt-2">{{ $post->published_at->format('d.m.Y') }}</time>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection
