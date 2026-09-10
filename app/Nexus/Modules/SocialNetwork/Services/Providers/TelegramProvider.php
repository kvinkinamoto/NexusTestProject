<?php

namespace App\Nexus\Modules\SocialNetwork\Services\Providers;

use App\Nexus\Modules\SocialNetwork\Services\Contracts\SocialProviderInterface;
use App\Nexus\Modules\SocialNetwork\Services\DTO\SocialPostDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class TelegramProvider implements SocialProviderInterface
{
    protected string $botToken;

    protected string $channelId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->channelId = config('services.telegram.channel_id');
    }

    public function fetchPosts(): array
    {
        $response = Http::get(
            "https://api.telegram.org/bot{$this->botToken}/getUpdates",
            ['limit' => 20, 'allowed_updates' => json_encode(['channel_post'])]
        );

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('result', []))
            ->filter(fn ($u) => isset($u['channel_post']))
            ->map(function ($update) {
                $msg = $update['channel_post'];
                $dto = new SocialPostDTO;
                $dto->platform = 'telegram';
                $dto->externalId = (string) $msg['message_id'];
                $dto->text = $msg['text'] ?? $msg['caption'] ?? null;
                $dto->publishedAt = Carbon::createFromTimestamp($msg['date']);

                $channelUsername = ltrim($this->channelId, '@-100');
                $dto->url = "https://t.me/{$channelUsername}/{$msg['message_id']}";

                if (! empty($msg['photo'])) {
                    $photo = end($msg['photo']);
                    $fileUrl = $this->getFileUrl($photo['file_id']);
                    $dto->media = [['type' => 'image', 'url' => $fileUrl, 'thumbnail' => $fileUrl]];
                }

                if (! empty($msg['video'])) {
                    $video = $msg['video'];
                    $videoUrl = $this->getFileUrl($video['file_id']);
                    $thumbUrl = isset($video['thumbnail']) ? $this->getFileUrl($video['thumbnail']['file_id']) : null;
                    $dto->media = [['type' => 'video', 'url' => $videoUrl, 'thumbnail' => $thumbUrl]];
                }

                return $dto;
            })
            ->values()
            ->all();
    }

    protected function getFileUrl(string $fileId): string
    {
        $res = Http::get("https://api.telegram.org/bot{$this->botToken}/getFile", ['file_id' => $fileId]);
        $path = $res->json('result.file_path');

        return "https://api.telegram.org/file/bot{$this->botToken}/{$path}";
    }
}
