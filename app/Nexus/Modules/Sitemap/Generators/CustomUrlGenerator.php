<?php

namespace App\Nexus\Modules\Sitemap\Generators;

use App\Nexus\Modules\Sitemap\Contracts\SitemapGeneratorInterface;
use App\Nexus\Modules\Sitemap\Dto\SitemapUrl;
use App\Nexus\Modules\Sitemap\Models\SitemapCustomUrl;

class CustomUrlGenerator implements SitemapGeneratorInterface
{
    public function __construct(protected array $config = []) {}

    public static function sitemapConfig(): array
    {
        return [
            'filename' => 'sitemap_custom.xml',
            'enabled' => true,
        ];
    }

    public function urls(string $baseUrl): \Generator
    {
        foreach (SitemapCustomUrl::active()->orderBy('id')->cursor() as $item) {
            $url = str_starts_with($item->url, 'http://') || str_starts_with($item->url, 'https://')
                ? $item->url
                : rtrim($baseUrl, '/').'/'.ltrim($item->url, '/');

            yield new SitemapUrl(
                url: $url,
                lastmod: $item->lastmod,
                changefreq: $item->changefreq,
                priority: (float) $item->priority,
            );
        }
    }
}
