<?php

namespace App\Nexus\Modules\ProductRelease\Jobs;

use App\Nexus\Modules\ProductRelease\Services\GithubReleaseSyncService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs the same sync as the manual `nexus:store:sync-release` command, but
 * triggered by GithubWebhookController instead of an admin. Queued (rather
 * than synced inline in the controller) so the webhook response returns to
 * GitHub well inside its delivery timeout regardless of download size.
 */
class SyncGithubReleaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public function __construct(public readonly int $productId, public readonly string $tag) {}

    public function handle(GithubReleaseSyncService $service): void
    {
        $product = ShopProduct::find($this->productId);

        if (! $product) {
            return;
        }

        try {
            $service->sync($product, $this->tag);
        } catch (\Throwable $e) {
            Log::warning('SyncGithubReleaseJob failed', [
                'product_id' => $this->productId,
                'tag' => $this->tag,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
