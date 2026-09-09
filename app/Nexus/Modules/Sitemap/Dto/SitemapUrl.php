<?php

namespace App\Nexus\Modules\Sitemap\Dto;

use Carbon\Carbon;

class SitemapUrl
{
    public function __construct(
        public readonly string $url,
        public readonly ?Carbon $lastmod = null,
        public readonly string $changefreq = 'daily',
        public readonly float $priority = 0.8,

        /**
         * @var array<int, array{url: string, title?: string, caption?: string}>
         */
        public readonly array $images = [],
    ) {}
}
