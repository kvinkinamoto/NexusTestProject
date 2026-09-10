<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Providers;

use App\Nexus\Modules\SocialNetwork\Services\Auth\TikTokAuthService;
use App\Nexus\Modules\SocialNetwork\Services\Contracts\SocialProviderInterface;
use App\Nexus\Modules\SocialNetwork\Services\DTO\SocialPostDTO;
use App\Nexus\Modules\SocialNetwork\Services\MediaDownloaderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TikTokProvider implements SocialProviderInterface
{
    public function __construct(
        protected TikTokAuthService $auth,
        protected MediaDownloaderService $downloader
    ) {}

    public function fetchPosts(): array
    {
        $token = $this->auth->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
        ])->post('https://open.tiktokapis.com/v2/video/list/?fields=id,title,video_description,create_time,cover_image_url,share_url,like_count,comment_count,view_count', [
            'max_count' => 20,
        ]);

        if ($response->status() === 401) {
            Cache::forget('tiktok:access_token');
            $token = $this->auth->getAccessToken();

            $response = Http::withHeaders(['Authorization' => "Bearer {$token}"])
                ->post('https://open.tiktokapis.com/v2/video/list/', [
                    'max_count' => 20,
                ]);
        }

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('data.videos', []))
            ->map(function ($video) {
                $dto = new SocialPostDTO;
                $dto->platform = 'tiktok';
                $dto->externalId = $video['id'];
                $dto->text = $video['video_description'] ?? $video['title'] ?? null;
                $dto->url = $video['share_url'];
                $dto->publishedAt = Carbon::createFromTimestamp($video['create_time']);
                $dto->metrics = [
                    'likes' => $video['like_count'] ?? 0,
                    'comments' => $video['comment_count'] ?? 0,
                    'views' => $video['view_count'] ?? 0,
                ];
                $dto->media = [[
                    'type' => 'tiktok',
                    'url' => $video['share_url'],
                    'thumbnail' => $this->downloader->download(
                        $video['cover_image_url'],
                        'tiktok',
                        $video['id'],
                        'thumbnail'
                    ),
                    'video_id' => $video['id'],
                ]];

                return $dto;
            })
            ->all();
    }
}
