<?php

use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\Sitemap\Services\SitemapAutoService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    foreach (['sitemap.xml', 'sitemap_products.xml', 'sitemap_categories.xml'] as $file) {
        File::delete(public_path($file));
    }
});

afterEach(function () {
    foreach (['sitemap.xml', 'sitemap_products.xml', 'sitemap_categories.xml'] as $file) {
        File::delete(public_path($file));
    }
});

it('includes published products, excluding unpublished ones', function () {
    ShopProduct::create(['name' => 'Published Module', 'slug' => 'published-module', 'price' => 100, 'publish' => true]);
    ShopProduct::create(['name' => 'Draft Module', 'slug' => 'draft-module', 'price' => 100, 'publish' => false]);

    app(SitemapAutoService::class)->generateAuto('https://example.com', single: false);

    expect(File::exists(public_path('sitemap_products.xml')))->toBeTrue();

    $content = File::get(public_path('sitemap_products.xml'));
    expect($content)->toContain('https://example.com/product/published-module');
    expect($content)->not->toContain('draft-module');
});

it('includes published categories, excluding unpublished ones', function () {
    ShopCategory::create(['name' => 'Published Category', 'slug' => 'published-category', 'publish' => true]);
    ShopCategory::create(['name' => 'Draft Category', 'slug' => 'draft-category', 'publish' => false]);

    app(SitemapAutoService::class)->generateAuto('https://example.com', single: false);

    expect(File::exists(public_path('sitemap_categories.xml')))->toBeTrue();

    $content = File::get(public_path('sitemap_categories.xml'));
    expect($content)->toContain('https://example.com/catalog/published-category');
    expect($content)->not->toContain('draft-category');
});

it('lists both product and category sitemaps in the master index', function () {
    ShopProduct::create(['name' => 'A Module', 'slug' => 'a-module', 'price' => 100, 'publish' => true]);
    ShopCategory::create(['name' => 'A Category', 'slug' => 'a-category', 'publish' => true]);

    app(SitemapAutoService::class)->generateAuto('https://example.com', single: false);

    $index = File::get(public_path('sitemap.xml'));
    expect($index)->toContain('sitemap_products.xml');
    expect($index)->toContain('sitemap_categories.xml');
});
