<?php

namespace App\Nexus\Modules\SocialNetwork\Services\DTO;

use Carbon\Carbon;

class SocialPostDTO
{
    public string $platform;

    public string $externalId;

    public ?string $text = null;

    public array $media = [];

    public ?string $url = null;

    public array $metrics = [];

    public Carbon $publishedAt;

    public ?int $socialAccountId;

    public function toArray(): array
    {
        return [
            'platform' => $this->platform,
            'external_id' => $this->externalId,
            'text' => $this->text,
            'media' => $this->media,
            'post_url' => $this->url,
            'metrics' => $this->metrics,
            'published_at' => $this->publishedAt,
            'social_account_id' => $this->socialAccountId,
        ];
    }
}
