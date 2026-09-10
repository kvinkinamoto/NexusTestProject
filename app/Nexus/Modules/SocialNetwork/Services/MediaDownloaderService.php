<?php

namespace App\Nexus\Modules\SocialNetwork\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MediaDownloaderService
{
    public function download(string $url, string $platform, string $postId, string $type = 'thumbnail'): string
    {
        $extension = $this->guessExtension($url);
        $path = "social/{$platform}/{$type}s/{$postId}.{$extension}";

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        try {
            $content = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($url)
                ->body();

            Storage::disk('public')->put($path, $content);

            return $path;
        } catch (\Throwable $e) {
            report($e);

            return $url;
        }
    }

    private function guessExtension(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'mov']) ? $ext : 'jpg';
    }
}
