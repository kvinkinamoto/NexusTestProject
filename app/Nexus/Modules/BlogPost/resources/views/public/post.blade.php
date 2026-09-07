<article class="nexus-blog-post">
    <h1 class="nexus-blog-post__title">{{ $post->title }}</h1>

    <div class="nexus-blog-post__meta">
        @if($post->category)
            <a href="{{ route('nexus.blogPost.category', $post->category->slug) }}">{{ $post->category->name }}</a>
        @endif
        @if($post->published_at)
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('Y-m-d') }}</time>
        @endif
    </div>

    @if($post->featured_image)
        <img src="/{{ ltrim($post->featured_image, '/') }}" alt="{{ $post->title }}" class="nexus-blog-post__image">
    @endif

    @if($post->excerpt)
        <p class="nexus-blog-post__excerpt">{{ $post->excerpt }}</p>
    @endif

    {{-- Escaped, not raw HTML, matching the existing Text block type's convention
         (app/Nexus/Plugins/BlockTypes/resources/views/public/block_types/text.blade.php)
         — no HTML sanitizer exists in this codebase yet, so editor content is
         never rendered unescaped anywhere in the public-facing views. --}}
    <div class="nexus-blog-post__content">
        {{ $post->content }}
    </div>

    @if($post->tags->isNotEmpty())
        <ul class="nexus-blog-post__tags">
            @foreach($post->tags as $tag)
                <li>{{ $tag->name }}</li>
            @endforeach
        </ul>
    @endif
</article>
