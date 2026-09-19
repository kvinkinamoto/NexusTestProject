<?php

use App\Models\User;
use App\Nexus\Modules\ProductRelease\Models\ProductRelease;
use App\Nexus\Modules\ProductRelease\Services\Actions\ValidateReleaseZipStructureAction;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

/**
 * Builds a real .zip (ValidateReleaseZipStructureAction opens it with
 * ZipArchive, so a fake-sized UploadedFile::fake() stub won't do) whose
 * only top-level entry is $folderName, containing $files.
 */
function makeReleaseUpload(string $folderName, array $files = ['Models/X.php' => '<?php']): UploadedFile
{
    $stagingDir = storage_path('app/tmp/release-upload-test-src-'.Str::random(8));
    foreach ($files as $relativePath => $contents) {
        $path = $stagingDir.DIRECTORY_SEPARATOR.$folderName.DIRECTORY_SEPARATOR.$relativePath;
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }

    $zipPath = storage_path('app/tmp/release-upload-test-'.Str::random(8).'.zip');
    File::ensureDirectoryExists(dirname($zipPath));

    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE);

    $addDir = function (string $dir, string $localBase) use (&$addDir, $zip): void {
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $dir.DIRECTORY_SEPARATOR.$entry;
            $localPath = $localBase === '' ? $entry : $localBase.'/'.$entry;

            if (is_dir($fullPath)) {
                $zip->addEmptyDir($localPath);
                $addDir($fullPath, $localPath);
            } else {
                $zip->addFile($fullPath, $localPath);
            }
        }
    };
    $addDir($stagingDir, '');
    $zip->close();

    File::deleteDirectory($stagingDir);

    return new UploadedFile($zipPath, $folderName.'.zip', 'application/zip', null, true);
}

describe('ValidateReleaseZipStructureAction', function () {
    it('accepts a zip with exactly one validly named top-level folder', function () {
        $upload = makeReleaseUpload('SeoBoosterPro');

        expect(fn () => ValidateReleaseZipStructureAction::handle($upload->getRealPath()))->not->toThrow(RuntimeException::class);
    });

    it('rejects a zip with more than one top-level entry', function () {
        $upload = makeReleaseUpload('SeoBoosterPro');
        $zip = new ZipArchive;
        $zip->open($upload->getRealPath());
        $zip->addFromString('stray.txt', 'oops');
        $zip->close();

        expect(fn () => ValidateReleaseZipStructureAction::handle($upload->getRealPath()))->toThrow(RuntimeException::class);
    });

    it('rejects an invalid top-level folder name', function () {
        $upload = makeReleaseUpload('seo-booster-pro');

        expect(fn () => ValidateReleaseZipStructureAction::handle($upload->getRealPath()))->toThrow(RuntimeException::class);
    });

    it('rejects a file that is not a valid zip', function () {
        $path = storage_path('app/tmp/not-a-zip-'.Str::random(8).'.zip');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'not a zip file');

        expect(fn () => ValidateReleaseZipStructureAction::handle($path))->toThrow(RuntimeException::class);
    });
});

describe('uploadRelease admin action', function () {
    beforeEach(function () {
        $this->artisan('nexus:permission:init')->run();
        $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
        $this->artisan('nexus:module:install', ['name' => 'ProductRelease'])->run();

        Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
        Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);

        $this->product = ShopProduct::create(['name' => 'SEO Booster Pro', 'slug' => 'seo-booster-pro', 'price' => 1499, 'publish' => true]);
    });

    afterEach(function () {
        File::deleteDirectory(storage_path('app/releases/seo-booster-pro'));
    });

    it('renders the upload form', function () {
        $this->actingAs($this->admin)
            ->get(route('nexus.module.action', ['module' => 'productRelease', 'action' => 'uploadRelease']))
            ->assertOk()
            ->assertSee($this->product->name);
    });

    it('creates a published release from a valid uploaded zip', function () {
        $upload = makeReleaseUpload('SeoBoosterPro');

        $response = $this->actingAs($this->admin)->post(
            route('nexus.module.action', ['module' => 'productRelease', 'action' => 'uploadRelease']),
            [
                'product_id' => $this->product->id,
                'version' => '1.0.0',
                'min_core_version' => '2.0',
                'max_core_version' => '3.0',
                'changelog' => 'Initial release.',
                'archive' => $upload,
            ],
        );

        $response->assertRedirect();
        $response->assertSessionHas('alert_type', 'success');

        $release = ProductRelease::query()->where('product_id', $this->product->id)->first();
        expect($release)->not->toBeNull();
        expect($release->version)->toBe('1.0.0');
        expect($release->min_core_version)->toBe('2.0');
        expect($release->max_core_version)->toBe('3.0');
        expect($release->published_at)->not->toBeNull();
        expect(File::exists($release->zip_disk_path))->toBeTrue();
    });

    it('rejects a malformed zip and creates no release', function () {
        $upload = makeReleaseUpload('SeoBoosterPro');
        $zip = new ZipArchive;
        $zip->open($upload->getRealPath());
        $zip->addFromString('stray.txt', 'oops');
        $zip->close();

        $response = $this->actingAs($this->admin)->post(
            route('nexus.module.action', ['module' => 'productRelease', 'action' => 'uploadRelease']),
            ['product_id' => $this->product->id, 'version' => '1.0.0', 'archive' => $upload],
        );

        $response->assertRedirect();
        expect(ProductRelease::query()->where('product_id', $this->product->id)->exists())->toBeFalse();
    });

    it('replaces the file when the same version is uploaded again for the same product', function () {
        $first = makeReleaseUpload('SeoBoosterPro', ['Models/X.php' => '<?php // v1']);
        $this->actingAs($this->admin)->post(
            route('nexus.module.action', ['module' => 'productRelease', 'action' => 'uploadRelease']),
            ['product_id' => $this->product->id, 'version' => '1.0.0', 'archive' => $first],
        );

        $second = makeReleaseUpload('SeoBoosterPro', ['Models/X.php' => '<?php // v2']);
        $this->actingAs($this->admin)->post(
            route('nexus.module.action', ['module' => 'productRelease', 'action' => 'uploadRelease']),
            ['product_id' => $this->product->id, 'version' => '1.0.0', 'archive' => $second],
        );

        expect(ProductRelease::query()->where('product_id', $this->product->id)->count())->toBe(1);
    });

    it('denies uploading without the admin permission', function () {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->get(route('nexus.module.action', ['module' => 'productRelease', 'action' => 'uploadRelease']))
            ->assertRedirect();
    });
});
