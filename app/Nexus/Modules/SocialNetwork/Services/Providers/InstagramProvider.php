<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Providers;

use App\Nexus\Modules\SocialNetwork\Services\Auth\InstagramAuthService;
use App\Nexus\Modules\SocialNetwork\Services\Contracts\SocialProviderInterface;
use App\Nexus\Modules\SocialNetwork\Services\DTO\SocialPostDTO;
use App\Nexus\Modules\SocialNetwork\Services\MediaDownloaderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class InstagramProvider implements SocialProviderInterface
{
    public function __construct(
        protected InstagramAuthService $auth,
        protected MediaDownloaderService $downloader,
    ) {}

    public function fetchPosts(): array
    {
        $token = $this->auth->getAccessToken();
        $userId = $this->auth->getUserId();

        $response = Http::get(
            "https://graph.instagram.com/v19.0/{$userId}/media",
            [
                'fields' => 'id,caption,media_type,media_url,thumbnail_url,timestamp,permalink,like_count,comments_count',
                'access_token' => $token,
                'limit' => 20,
            ]
        );

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('data', []))
            ->map(function ($post) {
                $dto = new SocialPostDTO;
                $dto->platform = 'instagram';
                $dto->externalId = $post['id'];
                $dto->text = $post['caption'] ?? null;
                $dto->url = $post['permalink'];
                $dto->publishedAt = Carbon::parse($post['timestamp']);
                $dto->metrics = [
                    'likes' => $post['like_count'] ?? 0,
                    'comments' => $post['comments_count'] ?? 0,
                ];

                $isVideo = in_array($post['media_type'], ['VIDEO', 'REELS']);

                if ($isVideo) {
                    $thumbnailUrl = $this->downloader->download(
                        $post['media_url'] ?? $post['thumbnail_url'],
                        'instagram',
                        $post['id'],
                        'thumbnail'
                    );

                    $dto->media = [[
                        'type' => 'video_link',
                        'url' => $post['permalink'],
                        'thumbnail' => $thumbnailUrl,
                    ]];
                } else {
                    $imageUrl = $this->downloader->download(
                        $post['media_url'],
                        'instagram',
                        $post['id'],
                        'image'
                    );

                    $dto->media = [[
                        'type' => 'image',
                        'url' => $imageUrl,
                        'thumbnail' => $imageUrl,
                    ]];
                }

                return $dto;
            })
            ->all();
    }
}
