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

/**
 * Coverage for BlockDataLocalizer + the translatable block fields marked in
 * app/Nexus/Plugins/BlockTypes/Types/*.php (BlockFieldDefinition::$translatable)
 * — a translated field's value is stored as a locale-keyed map in
 * PageBlock::$data and BlockDataLocalizer::resolve() (wired into the
 * nexusBlocks Blade directive) collapses it to the current app locale's
 * string before the block_types/{type}.blade.php partial ever sees it.
 */
it('resolves a translated block field to the current locale, falling back when missing', function () {
    $page = Page::create(['title' => 'Home', 'slug' => 'home-i18n', 'is_active' => true]);
    $page->blocks()->create([
        'type' => 'hero',
        'data' => ['headline' => ['uk' => 'Ласкаво просимо', 'en' => 'Welcome']],
        'position' => 0,
    ]);
    $page->blocks()->create([
        'type' => 'cta',
        // 'en' missing on purpose — falls back to app.fallback_locale, then to
        // the first available translation (see BlockDataLocalizer::resolve()).
        'data' => ['heading' => ['uk' => 'Готові?'], 'button_label' => 'OK', 'button_url' => '#'],
        'position' => 1,
    ]);

    app()->setLocale('uk');
    $this->get('/pages/home-i18n')->assertSee('Ласкаво просимо');

    app()->setLocale('en');
    $this->get('/pages/home-i18n')->assertSee('Welcome')->assertSee('Готові?');
});

it('leaves a legacy plain-string block value untouched when the field became translatable later', function () {
    $page = Page::create(['title' => 'Home', 'slug' => 'home-legacy', 'is_active' => true]);
    $page->blocks()->create(['type' => 'hero', 'data' => ['headline' => 'Legacy headline'], 'position' => 0]);

    $this->get('/pages/home-legacy')->assertSee('Legacy headline');
});
