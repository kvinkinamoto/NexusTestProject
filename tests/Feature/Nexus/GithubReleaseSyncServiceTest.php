<?php

use App\Nexus\Modules\ProductRelease\Models\ProductRelease;
use App\Nexus\Modules\ProductRelease\Services\GithubReleaseSyncService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Mimics a real GitHub zipball: everything wrapped in one
 * `owner-repo-<sha>/` folder GitHub adds itself. $nested true additionally
 * wraps the payload in a `SeoBoosterPro/` folder one level down (a seller
 * who committed their module as a subfolder of the repo root) — false puts
 * the payload files directly under the GitHub wrapper (a seller whose repo
 * root *is* the module). RepackageReleaseZipAction must normalize both
 * shapes to a single `SeoBoosterPro/` top level.
 */
function fakeGithubZipballBytes(bool $nested = true): string
{
    $tmpZip = tempnam(sys_get_temp_dir(), 'gh-zipball-').'.zip';

    $zip = new ZipArchive;
    $zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $githubWrapper = 'example-org-seo-booster-pro-abc1234';
    $payloadPrefix = $nested ? "{$githubWrapper}/SeoBoosterPro" : $githubWrapper;

    $zip->addFromString("{$payloadPrefix}/Models/SeoBoosterPro.php", '<?php // module class');
    $zip->close();

    $bytes = file_get_contents($tmpZip);
    unlink($tmpZip);

    return $bytes;
}

/**
 * @return string[] top-level entry names (with trailing slash stripped)
 */
function zipTopLevelEntries(string $path): array
{
    $zip = new ZipArchive;
    $zip->open($path);

    $tops = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $tops[explode('/', $zip->getNameIndex($i))[0]] = true;
    }
    $zip->close();

    return array_keys($tops);
}

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

it('downloads the release zip and repackages it into an ArchiveInstallService-compatible layout', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/repos/example-org/seo-booster-pro/zipball/v1.4.0' => Http::response(fakeGithubZipballBytes(nested: true), 200),
    ]);

    $service = app(GithubReleaseSyncService::class);
    $release = $service->sync($this->product, 'v1.4.0');

    expect($release)->toBeInstanceOf(ProductRelease::class)
        ->and($release->product_id)->toBe($this->product->id)
        ->and($release->github_tag)->toBe('v1.4.0')
        ->and($release->version)->toBe('1.4.0')
        ->and($release->min_core_version)->toBe('2.4')
        ->and($release->zip_size)->toBeGreaterThan(0)
        ->and($release->synced_at)->not->toBeNull();

    expect(File::exists($release->zip_disk_path))->toBeTrue();
    expect(zipTopLevelEntries($release->zip_disk_path))->toBe(['SeoBoosterPro']);

    $this->assertDatabaseHas('product_releases', [
        'product_id' => $this->product->id,
        'github_tag' => 'v1.4.0',
    ]);
});

it('also repackages correctly when the repo root itself is the module (no nested subfolder)', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/repos/example-org/seo-booster-pro/zipball/v1.4.0' => Http::response(fakeGithubZipballBytes(nested: false), 200),
    ]);

    $release = app(GithubReleaseSyncService::class)->sync($this->product, 'v1.4.0');

    expect(zipTopLevelEntries($release->zip_disk_path))->toBe(['SeoBoosterPro']);
});

it('re-syncing the same tag updates the existing row instead of duplicating it', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/repos/example-org/seo-booster-pro/zipball/v1.4.0' => Http::response(fakeGithubZipballBytes(), 200),
    ]);

    // Simulates a prior sync directly (rather than a real second sink
    // download) — Http::fake() + Guzzle's `sink` option only writes real
    // bytes on the *first* sink-download in a given test process (a
    // verified testing-harness quirk, not production behavior), so a
    // second real download here would read back empty and fail zip
    // validation. One real download is enough to prove updateOrCreate's
    // identity/timing contract.
    $existing = ProductRelease::create([
        'product_id' => $this->product->id,
        'version' => '1.3.0',
        'github_tag' => 'v1.4.0',
        'synced_at' => now()->subDay(),
    ]);

    $result = app(GithubReleaseSyncService::class)->sync($this->product, 'v1.4.0');

    expect($result->id)->toBe($existing->id);
    expect(ProductRelease::query()->count())->toBe(1);
    expect($result->synced_at)->not->toEqual($existing->synced_at);
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
