<?php

use App\Nexus\Modules\BlogCategory\Models\BlogCategory;
use App\Nexus\Modules\BlogPost\Models\BlogPost;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogCategory'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Tag'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogPost'])->run();
});

it('renders a published post through /blog/{slug}', function () {
    $category = BlogCategory::create(['name' => 'News', 'slug' => 'news']);
    BlogPost::create([
        'title' => 'Hello World', 'slug' => 'hello-world', 'excerpt' => 'A short intro',
        'content' => 'Body copy', 'is_published' => true, 'blog_category_id' => $category->id,
    ]);

    $response = $this->get('/blog/hello-world');

    $response->assertOk();
    $response->assertSee('Hello World');
    $response->assertSee('Body copy');
});

it('returns 404 for an unknown or unpublished post', function () {
    $this->get('/blog/does-not-exist')->assertNotFound();

    BlogPost::create(['title' => 'Draft', 'slug' => 'draft', 'is_published' => false]);
    $this->get('/blog/draft')->assertNotFound();
});

it('lists only published posts through /blog', function () {
    BlogPost::create(['title' => 'Published Post', 'slug' => 'published-post', 'is_published' => true]);
    BlogPost::create(['title' => 'Draft Post', 'slug' => 'draft-post', 'is_published' => false]);

    $response = $this->get('/blog');

    $response->assertOk();
    $response->assertSee('Published Post');
    $response->assertDontSee('Draft Post');
});

it('lists only posts in the given category through /blog/category/{slug}', function () {
    $news = BlogCategory::create(['name' => 'News', 'slug' => 'news']);
    $tips = BlogCategory::create(['name' => 'Tips', 'slug' => 'tips']);
    BlogPost::create(['title' => 'News Post', 'slug' => 'news-post', 'is_published' => true, 'blog_category_id' => $news->id]);
    BlogPost::create(['title' => 'Tips Post', 'slug' => 'tips-post', 'is_published' => true, 'blog_category_id' => $tips->id]);

    $response = $this->get('/blog/category/news');

    $response->assertOk();
    $response->assertSee('News Post');
    $response->assertDontSee('Tips Post');
});
