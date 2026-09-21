<?php

namespace App\Nexus\Modules\BlogPost\Http\Controllers;

use App\Nexus\Modules\BlogCategory\Models\BlogCategory;
use App\Nexus\Modules\BlogPost\Models\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class BlogPostController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::query()
            ->with('category')
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('blogPost::public.index', ['posts' => $posts]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::query()->with(['category', 'tags'])->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('blogPost::public.post', ['post' => $post]);
    }

    public function category(string $slug): View
    {
        $category = BlogCategory::query()->where('slug', $slug)->firstOrFail();

        $posts = BlogPost::query()
            ->with('category')
            ->where('blog_category_id', $category->id)
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('blogPost::public.index', ['posts' => $posts, 'category' => $category]);
    }
}
