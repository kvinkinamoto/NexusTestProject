<?php

namespace App\Nexus\Modules\ProductRelease\Services;

use App\Nexus\Modules\ProductRelease\Models\ProductRelease;
use App\Nexus\Modules\ProductRelease\Services\Actions\ValidateReleaseZipStructureAction;
use App\Nexus\Modules\ProductRelease\Services\Actions\ZipLocalModuleFolderAction;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Support\Facades\File;

/**
 * Same role as GithubReleaseSyncService, for a module that's a real folder
 * already on disk (nexus-modules-archive, or this project's own
 * app/Nexus/Modules/** for a module built only here, never mirrored to the
 * archive) rather than a GitHub-hosted repo — used to give every catalog
 * product (packs' individual member products, and single-module packs) a
 * genuinely installable release instead of no downloadable file at all.
 * Same storage_path('app/releases/...') convention, same synthetic-tag
 * idiom as the admin's manual-upload path (`local-{version}` instead of
 * `manual-{slug}`) so the existing unique(product_id, github_tag)/
 * updateOrCreate identity stays idempotent on re-runs.
 */
class LocalModuleReleaseService
{
    public function sync(ShopProduct $product, string $sourceDir, string $moduleFolderName, string $version = '1.0.0'): ProductRelease
    {
        $tag = 'local-'.$version;
        $destinationDir = storage_path('app'.DIRECTORY_SEPARATOR.'releases'.DIRECTORY_SEPARATOR.$product->slug);
        $destinationPath = $destinationDir.DIRECTORY_SEPARATOR.$tag.'.zip';

        ZipLocalModuleFolderAction::handle($sourceDir, $moduleFolderName, $destinationPath);
        ValidateReleaseZipStructureAction::handle($destinationPath);

        return ProductRelease::query()->updateOrCreate(
            ['product_id' => $product->id, 'github_tag' => $tag],
            [
                'version' => $version,
                'min_core_version' => $product->min_core_version,
                'published_at' => now(),
                'zip_disk_path' => $destinationPath,
                'zip_size' => File::size($destinationPath),
                'synced_at' => now(),
            ],
        );
    }
}
