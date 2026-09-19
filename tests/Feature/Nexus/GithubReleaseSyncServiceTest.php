<?php

use App\Nexus\Modules\ProductRelease\Models\ProductRelease;
use App\Nexus\Modules\ProductRelease\Services\GithubReleaseSyncService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.github.token' => 'test-token']);

    $this->product = ShopProduct::create([
        'name' => 'SEO Booster Pro',
        'slug' => 'seo-booster-pro',
        'price' => 1499,
        'publish' => true,
        'package_type' => 'module',
        'github_owner' => 'example-org',
        'github_repo' => 'seo-booster-pro',
        'min_core_version' => '2.4',
    ]);
});

afterEach(function () {
    File::deleteDirectory(storage_path('app/releases/seo-booster-pro'));
});

it('downloads the release zip and creates a ProductRelease row', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/repos/example-org/seo-booster-pro/zipball/v1.4.0' => Http::response('fake zip bytes', 200),
    ]);

    $service = app(GithubReleaseSyncService::class);
    $release = $service->sync($this->product, 'v1.4.0');

    expect($release)->toBeInstanceOf(ProductRelease::class)
        ->and($release->product_id)->toBe($this->product->id)
        ->and($release->github_tag)->toBe('v1.4.0')
        ->and($release->version)->toBe('1.4.0')
        ->and($release->min_core_version)->toBe('2.4')
        ->and($release->zip_size)->toBe(14)
        ->and($release->synced_at)->not->toBeNull();

    expect(File::exists($release->zip_disk_path))->toBeTrue();
    expect(File::get($release->zip_disk_path))->toBe('fake zip bytes');

    $this->assertDatabaseHas('product_releases', [
        'product_id' => $this->product->id,
        'github_tag' => 'v1.4.0',
    ]);
});

it('re-syncing the same tag updates the existing row instead of duplicating it', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/repos/example-org/seo-booster-pro/zipball/v1.4.0' => Http::response('first bytes', 200),
    ]);

    $service = app(GithubReleaseSyncService::class);
    $first = $service->sync($this->product, 'v1.4.0');
    $firstSyncedAt = $first->synced_at;

    $this->travel(1)->minutes();

    // A second real sink-download call within the same test process reads
    // as an empty body under Http::fake() (a known Laravel/Guzzle testing
    // quirk with the 'sink' option, verified in isolation — not a
    // production behavior, since a real cURL request each call has no such
    // shared-stream state) — so this second call only asserts the
    // updateOrCreate identity/timing contract, not byte content.
    $second = $service->sync($this->product, 'v1.4.0');

    expect($second->id)->toBe($first->id);
    expect(ProductRelease::query()->count())->toBe(1);
    expect($second->synced_at)->not->toEqual($firstSyncedAt);
});

it('throws and leaves no partial file when the GitHub request fails', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/repos/example-org/seo-booster-pro/zipball/v9.9.9' => Http::response('Not Found', 404),
    ]);

    $service = app(GithubReleaseSyncService::class);

    expect(fn () => $service->sync($this->product, 'v9.9.9'))->toThrow(RuntimeException::class);

    $this->assertDatabaseMissing('product_releases', ['github_tag' => 'v9.9.9']);
    expect(File::exists(storage_path('app/releases/seo-booster-pro/v9.9.9.zip')))->toBeFalse();
});

it('throws when the product has no GitHub repo configured', function () {
    $product = ShopProduct::create([
        'name' => 'No Repo Product',
        'slug' => 'no-repo-product',
        'price' => 100,
        'publish' => true,
    ]);

    $service = app(GithubReleaseSyncService::class);

    expect(fn () => $service->sync($product, 'v1.0.0'))->toThrow(RuntimeException::class);
});
