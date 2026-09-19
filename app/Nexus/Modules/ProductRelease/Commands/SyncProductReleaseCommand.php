<?php

namespace App\Nexus\Modules\ProductRelease\Commands;

use App\Nexus\Modules\ProductRelease\Services\GithubReleaseSyncService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Console\Command;

/**
 * Manual trigger for GithubReleaseSyncService — the same sync a GitHub
 * webhook will call automatically in a later phase, invokable by hand until
 * that webhook exists. {product} accepts either the numeric id or the slug.
 */
class SyncProductReleaseCommand extends Command
{
    protected $signature = 'nexus:store:sync-release {product : ShopProduct id or slug} {tag : GitHub release tag, e.g. v1.4.0}';

    protected $description = 'Download one GitHub release tag for a product and record/update its ProductRelease row';

    public function handle(GithubReleaseSyncService $service): int
    {
        $productKey = $this->argument('product');
        $product = ctype_digit($productKey)
            ? ShopProduct::query()->find($productKey)
            : ShopProduct::query()->where('slug', $productKey)->first();

        if (! $product) {
            $this->error("No product found for \"{$productKey}\".");

            return self::FAILURE;
        }

        try {
            $release = $service->sync($product, $this->argument('tag'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Synced {$product->name} @ {$release->github_tag} (version {$release->version}, ".number_format($release->zip_size / 1024, 1).' KB).');

        return self::SUCCESS;
    }
}
