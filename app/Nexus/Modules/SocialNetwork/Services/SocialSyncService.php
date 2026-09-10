<?php

namespace App\Nexus\Modules\SocialNetwork\Services;

use App\Nexus\Modules\SocialNetwork\Models\SocialNetwork;
use App\Nexus\Modules\SocialNetwork\Models\SocialPost;

class SocialSyncService
{
    public function __construct(
        protected SocialManager $manager,
    ) {}

    public function syncPlatform(string $platform): int
    {
        $posts = $this->manager->provider($platform)->fetchPosts();

        $socialAccountId = SocialNetwork::where('platform', $platform)->first()?->id;

        if (! $socialAccountId) {
            return 0;
        }

        $synced = 0;

        foreach ($posts as $dto) {
            if ($dto->text === null) {
                continue;
            }

            $dto->socialAccountId = $socialAccountId;

            SocialPost::updateOrCreate(
                ['platform' => $dto->platform, 'external_id' => $dto->externalId],
                $dto->toArray()
            );

            $synced++;
        }

        return $synced;
    }

    public function syncAll(): array
    {
        $results = [];
        foreach ($this->manager->allPlatforms() as $platform) {
            try {
                $results[$platform] = $this->syncPlatform($platform);
            } catch (\Throwable $e) {
                $results[$platform] = "error: {$e->getMessage()}";
                report($e);
            }
        }

        return $results;
    }
}
