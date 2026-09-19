<?php

namespace App\Nexus\Modules\ProductRelease\Services;

use App\Nexus\Modules\ProductRelease\Models\ProductRelease;
use App\Nexus\Modules\ProductRelease\Services\Actions\RepackageReleaseZipAction;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Downloads one GitHub-tagged release of a product's private repo as a zip
 * and records/updates the matching ProductRelease row. Shared by the manual
 * `nexus:store:sync-release` command, the GitHub webhook
 * (`GithubWebhookController`/`SyncGithubReleaseJob`), and the admin's
 * "Синхронізувати з GitHub" table action — three triggers, one sync. The
 * downloaded zip is immediately repackaged by RepackageReleaseZipAction so
 * what ends up on disk (and later gets sold via /account/licenses) already
 * passes ArchiveInstallService's "one top-level folder named after the
 * module" check, rather than GitHub's own `owner-repo-<sha>/` wrapper.
 * Stored under storage/app/releases/, matching ArchiveInstallService's own
 * storage_path('app/...') convention (see that service's docblock) rather
 * than the 'local' filesystem disk, since neither needs disk portability.
 */
class GithubReleaseSyncService
{
    public function sync(ShopProduct $product, string $tag): ProductRelease
    {
        if (! $product->github_owner || ! $product->github_repo) {
            throw new RuntimeException("Product \"{$product->name}\" has no GitHub owner/repo configured.");
        }

        $token = config('services.github.token');
        if (! $token) {
            throw new RuntimeException('GITHUB_TOKEN is not configured — see config/services.php.');
        }

        $destinationDir = storage_path('app'.DIRECTORY_SEPARATOR.'releases'.DIRECTORY_SEPARATOR.$product->slug);
        File::ensureDirectoryExists($destinationDir);
        $destinationPath = $destinationDir.DIRECTORY_SEPARATOR.$tag.'.zip';

        $url = "https://api.github.com/repos/{$product->github_owner}/{$product->github_repo}/zipball/{$tag}";

        $response = Http::withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(60)
            ->withOptions(['sink' => $destinationPath])
            ->get($url);

        if ($response->failed()) {
            File::delete($destinationPath);

            throw new RuntimeException("GitHub release download failed for {$product->github_owner}/{$product->github_repo}@{$tag}: HTTP {$response->status()}");
        }

        RepackageReleaseZipAction::handle($destinationPath, Str::studly($product->github_repo));

        return ProductRelease::query()->updateOrCreate(
            ['product_id' => $product->id, 'github_tag' => $tag],
            [
                'version' => ltrim($tag, 'vV'),
                'min_core_version' => $product->min_core_version,
                'zip_disk_path' => $destinationPath,
                'zip_size' => File::size($destinationPath),
                'synced_at' => now(),
            ],
        );
    }
}
