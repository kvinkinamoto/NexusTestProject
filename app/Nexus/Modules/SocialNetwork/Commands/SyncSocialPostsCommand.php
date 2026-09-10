<?php

namespace App\Nexus\Modules\SocialNetwork\Commands;

use App\Nexus\Modules\SocialNetwork\Jobs\SyncSocialPostsJob;
use App\Nexus\Modules\SocialNetwork\Models\SocialNetwork;
use Illuminate\Console\Command;

class SyncSocialPostsCommand extends Command
{
    protected $signature = 'social-posts:sync {platform? : instagram|facebook|telegram|tiktok|youtube}';

    protected $description = 'Sync posts from social networks';

    public function handle(): void
    {
        $socialPlatforms = SocialNetwork::query()->publish()->where('sync_posts', true)->pluck('platform')->toArray();

        $platforms = $this->argument('platform') && in_array($this->argument('platform'), $socialPlatforms)
            ? [$this->argument('platform')]
            : $socialPlatforms;

        foreach ($platforms as $platform) {
            SyncSocialPostsJob::dispatch($platform);
            $this->info("Dispatched job for: {$platform}");
        }
    }
}
