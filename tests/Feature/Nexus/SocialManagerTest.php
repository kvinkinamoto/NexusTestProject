<?php

use App\Nexus\Modules\SocialNetwork\Services\Providers\FacebookProvider;
use App\Nexus\Modules\SocialNetwork\Services\Providers\InstagramProvider;
use App\Nexus\Modules\SocialNetwork\Services\Providers\TelegramProvider;
use App\Nexus\Modules\SocialNetwork\Services\Providers\TikTokProvider;
use App\Nexus\Modules\SocialNetwork\Services\Providers\YoutubeProvider;
use App\Nexus\Modules\SocialNetwork\Services\SocialManager;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    config(['services.telegram.bot_token' => 'bot-token', 'services.telegram.channel_id' => '@mychannel']);

    $this->manager = app(SocialManager::class);
});

it('resolves a provider for each known platform', function (string $platform, string $class) {
    expect($this->manager->provider($platform))->toBeInstanceOf($class);
})->with([
    ['instagram', InstagramProvider::class],
    ['facebook', FacebookProvider::class],
    ['telegram', TelegramProvider::class],
    ['tiktok', TikTokProvider::class],
    ['youtube', YoutubeProvider::class],
]);

it('aborts with 400 for an unknown platform', function () {
    $this->manager->provider('myspace');
})->throws(HttpException::class);

it('lists all known platform keys', function () {
    expect($this->manager->allPlatforms())->toBe(['instagram', 'facebook', 'telegram', 'tiktok', 'youtube']);
});
