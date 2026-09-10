<?php

use App\Nexus\Modules\SocialNetwork\Enums\SocialPlatformEnum;
use App\Nexus\Modules\SocialNetwork\Models\SocialNetwork;
use App\Nexus\Modules\SocialNetwork\Models\SocialPost;
use App\Nexus\Modules\SocialNetwork\Services\Contracts\SocialProviderInterface;
use App\Nexus\Modules\SocialNetwork\Services\DTO\SocialPostDTO;
use App\Nexus\Modules\SocialNetwork\Services\Providers\TelegramProvider;
use App\Nexus\Modules\SocialNetwork\Services\SocialSyncService;
use Illuminate\Support\Facades\Http;

function socialPostDto(string $platform, string $externalId, ?string $text = 'Hello'): SocialPostDTO
{
    $dto = new SocialPostDTO;
    $dto->platform = $platform;
    $dto->externalId = $externalId;
    $dto->text = $text;
    $dto->publishedAt = now();

    return $dto;
}

class FakeTelegramProvider implements SocialProviderInterface
{
    public function __construct(private array $posts) {}

    public function fetchPosts(): array
    {
        return $this->posts;
    }
}

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'SocialNetwork'])->run();

    $this->service = app(SocialSyncService::class);
});

it('does nothing when no social network row exists for the platform', function () {
    $this->app->bind(TelegramProvider::class, fn () => new FakeTelegramProvider([socialPostDto('telegram', '1')]));

    expect($this->service->syncPlatform('telegram'))->toBe(0);
    expect(SocialPost::query()->count())->toBe(0);
});

it('creates and dedupes posts by platform+external_id, skipping null text', function () {
    SocialNetwork::create(['url' => 'https://t.me/example', 'platform' => SocialPlatformEnum::TELEGRAM, 'publish' => true]);

    $this->app->bind(TelegramProvider::class, fn () => new FakeTelegramProvider([
        socialPostDto('telegram', '1', 'First post'),
        socialPostDto('telegram', '2', null),
    ]));

    $synced = $this->service->syncPlatform('telegram');

    expect($synced)->toBe(1);
    expect(SocialPost::query()->count())->toBe(1);

    $this->app->bind(TelegramProvider::class, fn () => new FakeTelegramProvider([
        socialPostDto('telegram', '1', 'First post, edited'),
    ]));

    $this->service->syncPlatform('telegram');

    expect(SocialPost::query()->count())->toBe(1);
    expect(SocialPost::query()->first()->text)->toBe('First post, edited');
});

it('syncs every platform and reports a per-platform error without aborting the rest', function () {
    // YoutubeProvider makes an unconditional HTTP call with no auth
    // precondition (unlike Facebook/Instagram/TikTok, which throw before
    // ever reaching Http) — fake it so this test never touches the network.
    Http::fake();

    SocialNetwork::create(['url' => 'https://t.me/example', 'platform' => SocialPlatformEnum::TELEGRAM, 'publish' => true]);

    $this->app->bind(TelegramProvider::class, fn () => new FakeTelegramProvider([socialPostDto('telegram', '1')]));

    $results = $this->service->syncAll();

    expect($results['telegram'])->toBe(1);
    expect($results['facebook'])->toStartWith('error:');
});
