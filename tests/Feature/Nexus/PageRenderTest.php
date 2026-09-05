<?php

use App\Nexus\Modules\Page\Models\Page;

/**
 * Coverage for the public /pages/{slug} route (PageController) and the
 * nexusBlocks Blade directive it relies on (NexusServiceProvider.php, next
 * to nexusForm) — proves a Page's ordered PageBlock rows render as real HTML
 * through their own per-type public partial (resources/views/public/block_types).
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Page'])->run();
});

it('renders a page\'s blocks in order through /pages/{slug}', function () {
    $page = Page::create(['title' => 'Home', 'slug' => 'home', 'is_active' => true]);
    $page->blocks()->create(['type' => 'hero', 'data' => ['headline' => 'Welcome', 'cta_label' => 'Get started', 'cta_url' => 'https://example.com'], 'position' => 0]);
    $page->blocks()->create(['type' => 'cta', 'data' => ['heading' => 'Ready?', 'button_label' => 'Sign up', 'button_url' => 'https://example.com/signup'], 'position' => 1]);

    $response = $this->get('/pages/home');

    $response->assertOk();
    $response->assertSeeInOrder(['Welcome', 'Get started', 'Ready?', 'Sign up']);
    $response->assertSee('href="https://example.com/signup"', false);
});

it('returns 404 for an unknown or inactive page', function () {
    $this->get('/pages/does-not-exist')->assertNotFound();

    Page::create(['title' => 'Draft', 'slug' => 'draft', 'is_active' => false]);
    $this->get('/pages/draft')->assertNotFound();
});
