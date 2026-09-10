<?php

namespace App\Nexus\Modules\SocialNetwork\Services;

use App\Nexus\Modules\SocialNetwork\Services\Contracts\SocialProviderInterface;
use App\Nexus\Modules\SocialNetwork\Services\Providers\FacebookProvider;
use App\Nexus\Modules\SocialNetwork\Services\Providers\InstagramProvider;
use App\Nexus\Modules\SocialNetwork\Services\Providers\TelegramProvider;
use App\Nexus\Modules\SocialNetwork\Services\Providers\TikTokProvider;
use App\Nexus\Modules\SocialNetwork\Services\Providers\YoutubeProvider;

class SocialManager
{
    protected array $providers = [
        'instagram' => InstagramProvider::class,
        'facebook' => FacebookProvider::class,
        'telegram' => TelegramProvider::class,
        'tiktok' => TikTokProvider::class,
        'youtube' => YoutubeProvider::class,
    ];

    public function provider(string $platform): SocialProviderInterface
    {
        abort_unless(isset($this->providers[$platform]), 400, "Unknown platform: {$platform}");

        return app($this->providers[$platform]);
    }

    public function allPlatforms(): array
    {
        return array_keys($this->providers);
    }
}
