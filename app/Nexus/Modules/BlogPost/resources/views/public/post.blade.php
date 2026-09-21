@extends('storefront::public.layouts.storefront')

@section('title', ($post->meta_title ?: $post->title).' — Nexus Store')
@section('meta_description', $post->meta_description ?: Str::limit(strip_tags((string) $post->excerpt), 160))

@section('content')
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-6">
        <nav class="flex items-center gap-1.5 text-xs text-neutral-500 mb-4 flex-wrap">
            <a href="{{ route('shop.home') }}" class="hover:text-neutral-900">{{ __('Головна') }}</a><span>/</span>
            <a href="{{ route('nexus.blogPost.index') }}" class="hover:text-neutral-900">{{ __('Блог') }}</a><span>/</span>
            <span class="text-neutral-900 font-medium">{{ $post->title }}</span>
        </nav>

        <article>
            <h1 class="font-outfit text-2xl sm:text-3xl font-bold">{{ $post->title }}</h1>

            <div class="flex items-center gap-3 mt-3 text-sm text-neutral-500">
                @if($post->category)
                    <a href="{{ route('nexus.blogPost.category', $post->category->slug) }}" class="hover:text-neutral-900">{{ $post->category->name }}</a>
                @endif
                @if($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d.m.Y') }}</time>
                @endif
            </div>

            @if($post->featured_image)
                <div class="ph aspect-[16/9] rounded-2xl overflow-hidden mt-6">
                    <img src="{{ '/'.ltrim($post->featured_image, '/') }}" alt="{{ $post->title }}" class="size-full object-cover" />
                </div>
            @endif

            @if($post->excerpt)
                <p class="text-lg text-neutral-600 mt-6 leading-relaxed">{{ $post->excerpt }}</p>
            @endif

            {{-- Escaped, not raw HTML, matching the existing Text block type's convention
                 (app/Nexus/Plugins/BlockTypes/resources/views/public/block_types/text.blade.php)
                 — no HTML sanitizer exists in this codebase yet, so editor content is
                 never rendered unescaped anywhere in the public-facing views. --}}
            <div class="text-sm text-neutral-600 leading-relaxed mt-6">{!! nl2br(e($post->content)) !!}</div>

            @if($post->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-8 pt-6 border-t border-neutral-100">
                    @foreach($post->tags as $tag)
                        <span class="inline-flex items-center h-7 px-3 rounded-full bg-neutral-100 text-neutral-600 text-xs font-medium">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </article>
    </div>
@endsection
