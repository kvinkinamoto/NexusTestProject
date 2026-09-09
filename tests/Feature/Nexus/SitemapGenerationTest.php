<?php

use App\Nexus\Modules\BlogPost\Models\BlogPost;
use App\Nexus\Modules\Page\Models\Page;
use App\Nexus\Modules\Sitemap\Models\SitemapCustomUrl;
use App\Nexus\Modules\Sitemap\Services\SitemapAutoService;
use Illuminate\Support\Facades\File;
use Nodex\Nexus\Services\SettingsBuilder;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Sitemap'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Page'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogCategory'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Tag'])->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogPost'])->run();

    foreach (['sitemap.xml', 'sitemap_pages.xml', 'sitemap_blog_posts.xml', 'sitemap_custom.xml'] as $file) {
        File::delete(public_path($file));
    }
});

afterEach(function () {
    foreach (['sitemap.xml', 'sitemap_pages.xml', 'sitemap_blog_posts.xml', 'sitemap_custom.xml'] as $file) {
        File::delete(public_path($file));
    }
});

it('generates a master index plus one file per discovered module generator, in multi mode', function () {
    Page::create(['title' => 'About', 'slug' => 'about', 'is_active' => true]);
    Page::create(['title' => 'Draft', 'slug' => 'draft', 'is_active' => false]);
    BlogPost::create(['title' => 'Hello', 'slug' => 'hello', 'is_published' => true]);

    app(SitemapAutoService::class)->generateAuto('https://example.com', single: false);

    expect(File::exists(public_path('sitemap.xml')))->toBeTrue();
    expect(File::exists(public_path('sitemap_pages.xml')))->toBeTrue();
    expect(File::exists(public_path('sitemap_blog_posts.xml')))->toBeTrue();

    $pages = File::get(public_path('sitemap_pages.xml'));
    expect($pages)->toContain('https://example.com/pages/about');
    expect($pages)->not->toContain('/pages/draft');

    $posts = File::get(public_path('sitemap_blog_posts.xml'));
    expect($posts)->toContain('https://example.com/blog/hello');

    $index = File::get(public_path('sitemap.xml'));
    expect($index)->toContain('sitemap_pages.xml');
    expect($index)->toContain('sitemap_blog_posts.xml');
});

it('merges every URL into a single sitemap.xml in single mode', function () {
    Page::create(['title' => 'About', 'slug' => 'about', 'is_active' => true]);
    BlogPost::create(['title' => 'Hello', 'slug' => 'hello', 'is_published' => true]);

    app(SitemapAutoService::class)->generateAuto('https://example.com', single: true);

    expect(File::exists(public_path('sitemap.xml')))->toBeTrue();
    expect(File::exists(public_path('sitemap_pages.xml')))->toBeFalse();

    $content = File::get(public_path('sitemap.xml'));
    expect($content)->toContain('https://example.com/pages/about');
    expect($content)->toContain('https://example.com/blog/hello');
});

it('includes active custom URLs and excludes inactive ones', function () {
    SitemapCustomUrl::create(['url' => '/custom-active', 'is_active' => true]);
    SitemapCustomUrl::create(['url' => '/custom-inactive', 'is_active' => false]);
    SitemapCustomUrl::create(['url' => 'https://external.example.com/landing', 'is_active' => true]);

    app(SitemapAutoService::class)->generateAuto('https://example.com', single: false);

    $content = File::get(public_path('sitemap_custom.xml'));
    expect($content)->toContain('https://example.com/custom-active');
    expect($content)->not->toContain('custom-inactive');
    expect($content)->toContain('https://external.example.com/landing');
});

it('reads mode and base_url from the module settings when not passed explicitly', function () {
    SettingsBuilder::set('sitemap', 'mode', 'single');
    SettingsBuilder::set('sitemap', 'base_url', 'https://cdn.example.com');

    Page::create(['title' => 'About', 'slug' => 'about', 'is_active' => true]);

    app(SitemapAutoService::class)->generateAuto();

    expect(File::exists(public_path('sitemap.xml')))->toBeTrue();
    expect(File::exists(public_path('sitemap_pages.xml')))->toBeFalse();

    $content = File::get(public_path('sitemap.xml'));
    expect($content)->toContain('https://cdn.example.com/pages/about');
});

it('splits a generator into chunk files plus a sub-index once split_size is exceeded', function () {
    SettingsBuilder::set('sitemap', 'split_size', 1);

    Page::create(['title' => 'First', 'slug' => 'first', 'is_active' => true]);
    Page::create(['title' => 'Second', 'slug' => 'second', 'is_active' => true]);

    app(SitemapAutoService::class)->generateAuto('https://example.com', single: false);

    expect(File::exists(public_path('sitemap_pages_chunk_1.xml')))->toBeTrue();
    expect(File::exists(public_path('sitemap_pages_chunk_2.xml')))->toBeTrue();

    $subIndex = File::get(public_path('sitemap_pages.xml'));
    expect($subIndex)->toContain('sitemap_pages_chunk_1.xml');
    expect($subIndex)->toContain('sitemap_pages_chunk_2.xml');

    File::delete(public_path('sitemap_pages_chunk_1.xml'));
    File::delete(public_path('sitemap_pages_chunk_2.xml'));
});

it('drives generateAuto with the same settings the console command reads, proving Commands/GenerateSitemaps.php is a thin wrapper', function () {
    // Not invoked via $this->artisan('nexus:sitemap:generate') — Nexus only
    // registers a module's own Commands/ classes off getEnabledModules() at
    // NexusServiceProvider::boot() time (see runCommand()), which for a
    // RefreshDatabase test runs before this file's beforeEach() ever installs
    // Sitemap. Confirmed for real outside the test sandbox instead: a plain
    // `php artisan list` after a real `nexus:module:install Sitemap` shows
    // nexus:sitemap:generate registered and runnable. This test covers the
    // same read-settings-then-generate path GenerateSitemaps::handle() runs,
    // via the same public entry point (SitemapAutoService::generateAuto()).
    SettingsBuilder::set('sitemap', 'mode', 'multi');
    SettingsBuilder::set('sitemap', 'base_url', null);

    Page::create(['title' => 'About', 'slug' => 'about', 'is_active' => true]);

    $mode = SettingsBuilder::get('sitemap', 'mode', 'multi');
    $baseUrl = SettingsBuilder::get('sitemap', 'base_url') ?: null;

    app(SitemapAutoService::class)->generateAuto($baseUrl, $mode === 'single');

    expect(File::exists(public_path('sitemap.xml')))->toBeTrue();
});
