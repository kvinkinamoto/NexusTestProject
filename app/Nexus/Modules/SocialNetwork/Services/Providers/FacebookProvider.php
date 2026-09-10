<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Providers;

use App\Nexus\Modules\SocialNetwork\Services\Auth\FacebookAuthService;
use App\Nexus\Modules\SocialNetwork\Services\Contracts\SocialProviderInterface;
use App\Nexus\Modules\SocialNetwork\Services\DTO\SocialPostDTO;
use App\Nexus\Modules\SocialNetwork\Services\MediaDownloaderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class FacebookProvider implements SocialProviderInterface
{
    public function __construct(
        protected FacebookAuthService $auth,
        protected MediaDownloaderService $downloader,
    ) {}

    public function fetchPosts(): array
    {
        $token = $this->auth->getToken();
        $pageId = $this->auth->getPageId();

        $response = Http::get(
            "https://graph.facebook.com/v19.0/{$pageId}/posts",
            [
                'fields' => 'id,message,full_picture,permalink_url,created_time,attachments{media_type,media,url}',
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
                $dto->platform = 'facebook';
                $dto->externalId = $post['id'];
                $dto->text = $post['message'] ?? null;
                $dto->url = $post['permalink_url'] ?? null;
                $dto->publishedAt = Carbon::parse($post['created_time']);

                $this->fillMedia($dto, $post);

                return $dto;
            })
            ->all();
    }

    private function fillMedia(SocialPostDTO $dto, array $post): void
    {
        $attachment = $post['attachments']['data'][0] ?? null;

        if ($attachment) {
            $isVideo = ($attachment['media_type'] ?? '') === 'video';

            if ($isVideo) {
                $thumbnailUrl = $attachment['media']['image']['src'] ?? $post['full_picture'] ?? null;

                $dto->media = [[
                    'type' => 'video_link',
                    'url' => $post['permalink_url'],
                    'thumbnail' => $thumbnailUrl ? $this->downloader->download(
                        $thumbnailUrl,
                        'facebook',
                        $post['id'],
                        'thumbnail'
                    ) : null,
                ]];
            } else {
                $imageUrl = $attachment['media']['image']['src'] ?? $post['full_picture'] ?? null;

                $dto->media = [[
                    'type' => 'image',
                    'url' => $imageUrl ? $this->downloader->download(
                        $imageUrl,
                        'facebook',
                        $post['id'],
                        'image'
                    ) : null,
                    'thumbnail' => $imageUrl ? $this->downloader->download(
                        $imageUrl,
                        'facebook',
                        $post['id'],
                        'image'
                    ) : null,
                ]];
            }

            return;
        }

        if (! empty($post['full_picture'])) {
            $imageUrl = $this->downloader->download(
                $post['full_picture'],
                'facebook',
                $post['id'],
                'image'
            );

            $dto->media = [[
                'type' => 'image',
                'url' => $imageUrl,
                'thumbnail' => $imageUrl,
            ]];
        }
    }
}
