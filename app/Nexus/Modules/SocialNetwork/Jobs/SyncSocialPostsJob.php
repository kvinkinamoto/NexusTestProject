<?php

namespace App\Nexus\Modules\SocialNetwork\Jobs;

use App\Nexus\Modules\SocialNetwork\Services\SocialSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSocialPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 300;

    public function __construct(public readonly string $platform) {}

    public function handle(SocialSyncService $service): void
    {
        try {
            $count = $service->syncPlatform($this->platform);
            Log::info("Synced {$count} posts from {$this->platform}");
        } catch (\Throwable $e) {
            Log::warning('Error SyncSocialPostsJob', [
                'msg' => $e->getMessage(),
                'platform' => $this->platform,
            ]);
        }
    }
}
