<?php

use App\Nexus\Modules\Advertising\Enums\AdvertisingType;
use App\Nexus\Modules\Advertising\Models\AdImpression;
use App\Nexus\Modules\Advertising\Models\Advertising;
use App\Nexus\Modules\Advertising\Services\AdPlacementService;
use App\Nexus\Modules\WidgetPlacement\Models\WidgetPlacement;
use App\Nexus\Widgets\AdvertisingSlot\AdvertisingSlot;
use Nodex\Nexus\Dto\Widgets\WidgetContext;
use Nodex\Nexus\Enums\WidgetSurface;
use Nodex\Nexus\Services\SettingsBuilder;
use Nodex\Nexus\Services\Widgets\FrontWidgetRenderer;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Advertising'])->run();

    $this->service = app(AdPlacementService::class);
});

it('picks an active ad for its slot and records an impression', function () {
    $ad = Advertising::create([
        'title' => 'Banner 1', 'slot' => 'sidebar', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => true,
    ]);

    $picked = $this->service->pick('sidebar');

    expect($picked?->id)->toBe($ad->id);
    expect($picked->impressions_count)->toBe(1);
    expect(AdImpression::where('advertising_id', $ad->id)->count())->toBe(1);
});

it('returns null when no ad matches the slot', function () {
    Advertising::create([
        'title' => 'Banner 1', 'slot' => 'sidebar', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => true,
    ]);

    expect($this->service->pick('footer'))->toBeNull();
});

it('returns null when advertising is disabled globally', function () {
    Advertising::create([
        'title' => 'Banner 1', 'slot' => 'sidebar', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => true,
    ]);
    SettingsBuilder::set('advertising', 'enabled', false);

    expect($this->service->pick('sidebar'))->toBeNull();
});

it('skips an inactive ad', function () {
    Advertising::create([
        'title' => 'Banner 1', 'slot' => 'sidebar', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => false,
    ]);

    expect($this->service->pick('sidebar'))->toBeNull();
});

it('skips an ad outside its start/end window', function () {
    Advertising::create([
        'title' => 'Not yet', 'slot' => 'sidebar', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => true, 'starts_at' => now()->addDay(),
    ]);
    Advertising::create([
        'title' => 'Expired', 'slot' => 'sidebar', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => true, 'ends_at' => now()->subDay(),
    ]);

    expect($this->service->pick('sidebar'))->toBeNull();
});

it('stops serving an ad once its global impression cap is reached', function () {
    Advertising::create([
        'title' => 'Capped', 'slot' => 'sidebar', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => true, 'max_impressions' => 1,
        'impressions_count' => 1,
    ]);

    expect($this->service->pick('sidebar'))->toBeNull();
});

it('stops serving an ad to a visitor once their per-visitor cap is reached', function () {
    $ad = Advertising::create([
        'title' => 'Capped per visitor', 'slot' => 'sidebar', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => true, 'max_impressions_per_visitor' => 1,
    ]);

    $first = $this->service->pick('sidebar');
    expect($first?->id)->toBe($ad->id);

    $second = $this->service->pick('sidebar');
    expect($second)->toBeNull();
});

it('renders a banner ad as an image link through the click route', function () {
    $ad = Advertising::create([
        'title' => 'Banner', 'slot' => 'header', 'type' => AdvertisingType::Banner,
        'image' => 'storage/ads/demo.jpg', 'target_url' => 'https://example.com', 'is_active' => true,
    ]);

    $html = (new AdvertisingSlot)->render(['slot' => 'header'], new WidgetContext(surface: WidgetSurface::Front));

    expect($html)->toContain('storage/ads/demo.jpg');
    expect($html)->toContain(route('advertising.click', $ad));
});

it('renders an html ad by echoing its content verbatim', function () {
    Advertising::create([
        'title' => 'AdSense unit', 'slot' => 'footer', 'type' => AdvertisingType::Html,
        'content_html' => '<ins class="adsbygoogle"></ins>', 'is_active' => true,
    ]);

    $html = (new AdvertisingSlot)->render(['slot' => 'footer'], new WidgetContext(surface: WidgetSurface::Front));

    expect($html)->toContain('<ins class="adsbygoogle"></ins>');
});

it('renders nothing when the slot has no eligible ad', function () {
    $html = (new AdvertisingSlot)->render(['slot' => 'unused'], new WidgetContext(surface: WidgetSurface::Front));

    expect(trim($html))->toBe('');
});

it('redirects to the target url and records a click', function () {
    $ad = Advertising::create([
        'title' => 'Banner', 'slot' => 'header', 'type' => AdvertisingType::Banner,
        'image' => 'storage/ads/demo.jpg', 'target_url' => 'https://example.com', 'is_active' => true,
    ]);

    $response = $this->get(route('advertising.click', $ad));

    $response->assertRedirect('https://example.com');
    expect($ad->fresh()->clicks_count)->toBe(1);
});

it('serves an ad through a real WidgetPlacement assignment at @position()', function () {
    $this->artisan('nexus:module:install', ['name' => 'WidgetPlacement'])->run();

    Advertising::create([
        'title' => 'Header banner', 'slot' => 'header-pool', 'type' => AdvertisingType::Html,
        'content_html' => '<div id="header-ad">hello</div>', 'is_active' => true,
    ]);

    $placement = WidgetPlacement::create([
        'name' => 'Header ad', 'widget_key' => 'advertisingSlot',
        'config' => ['slot' => 'header-pool'], 'is_enabled' => true,
    ]);
    $placement->assignments()->create(['position' => 'ad_header', 'is_active' => true]);

    $html = app(FrontWidgetRenderer::class)->render('ad_header');

    expect($html)->toContain('id="header-ad"');
});

it('returns 404 clicking an ad with no target url', function () {
    $ad = Advertising::create([
        'title' => 'Html ad', 'slot' => 'header', 'type' => AdvertisingType::Html,
        'content_html' => '<div>ad</div>', 'is_active' => true,
    ]);

    $response = $this->get(route('advertising.click', $ad));

    $response->assertNotFound();
});
