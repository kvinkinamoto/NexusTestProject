<div class="nexus-blog-index">
    @if(isset($category))
        <h1 class="nexus-blog-index__title">{{ $category->name }}</h1>
    @endif

    <ul class="nexus-blog-index__list">
        @foreach($posts as $post)
            <li class="nexus-blog-index__item">
                <a href="{{ route('nexus.blogPost.show', $post->slug) }}">{{ $post->title }}</a>
                @if($post->excerpt)
                    <p>{{ $post->excerpt }}</p>
                @endif
            </li>
        @endforeach
    </ul>

    {{ $posts->links() }}
</div>
