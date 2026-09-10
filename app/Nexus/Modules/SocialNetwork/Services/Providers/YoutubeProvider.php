<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Providers;

use App\Nexus\Modules\SocialNetwork\Services\Contracts\SocialProviderInterface;
use App\Nexus\Modules\SocialNetwork\Services\DTO\SocialPostDTO;
use App\Nexus\Modules\SocialNetwork\Services\MediaDownloaderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class YoutubeProvider implements SocialProviderInterface
{
    public function __construct(
        protected MediaDownloaderService $downloader,
    ) {}

    public function fetchPosts(): array
    {
        $channelId = config('services.youtube.channel_id');
        $apiKey = config('services.youtube.api_key');

        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
            'key' => $apiKey,
            'channelId' => $channelId,
            'part' => 'snippet',
            'order' => 'date',
            'maxResults' => 20,
            'type' => 'video',
            'videoDuration' => 'short',
        ]);

        if ($response->failed()) {
            return [];
        }

        $videoIds = collect($response->json('items', []))
            ->pluck('id.videoId')
            ->filter()
            ->join(',');

        if (empty($videoIds)) {
            return [];
        }

        $details = Http::get('https://www.googleapis.com/youtube/v3/videos', [
            'key' => $apiKey,
            'id' => $videoIds,
            'part' => 'snippet,statistics,contentDetails',
        ]);

        if ($details->failed()) {
            return [];
        }

        return collect($details->json('items', []))
            ->filter(fn ($v) => $this->durationSeconds($v['contentDetails']['duration'] ?? 'PT0S') <= 60)
            ->map(function ($video) {
                $snippet = $video['snippet'];
                $stats = $video['statistics'] ?? [];
                $videoId = $video['id'];

                $thumbnailUrl = $snippet['thumbnails']['maxres']['url']
                    ?? $snippet['thumbnails']['high']['url']
                    ?? $snippet['thumbnails']['default']['url'];

                $dto = new SocialPostDTO;
                $dto->platform = 'youtube';
                $dto->externalId = $videoId;
                $dto->text = $snippet['description'] ?? $snippet['title'] ?? null;
                $dto->url = "https://www.youtube.com/shorts/{$videoId}";
                $dto->publishedAt = Carbon::parse($snippet['publishedAt']);
                $dto->metrics = [
                    'views' => $stats['viewCount'] ?? 0,
                    'likes' => $stats['likeCount'] ?? 0,
                    'comments' => $stats['commentCount'] ?? 0,
                ];
                $dto->media = [[
                    'type' => 'youtube_short',
                    'url' => "https://www.youtube.com/shorts/{$videoId}",
                    'embed_url' => "https://www.youtube.com/embed/{$videoId}",
                    'thumbnail' => $this->downloader->download(
                        $thumbnailUrl,
                        'youtube',
                        $videoId,
                        'thumbnail'
                    ),
                    'video_id' => $videoId,
                ]];

                return $dto;
            })
            ->values()
            ->all();
    }

    private function durationSeconds(string $iso): int
    {
        preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $iso, $m);

        return (int) ($m[1] ?? 0) * 3600 + (int) ($m[2] ?? 0) * 60 + (int) ($m[3] ?? 0);
    }
}
