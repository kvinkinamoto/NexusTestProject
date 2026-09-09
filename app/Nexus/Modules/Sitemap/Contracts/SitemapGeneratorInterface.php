<?php

namespace App\Nexus\Modules\Sitemap\Contracts;

use App\Nexus\Modules\Sitemap\Dto\SitemapUrl;

interface SitemapGeneratorInterface
{
    /**
     * @param  string  $baseUrl  e.g. https://example.com
     * @return iterable<SitemapUrl>
     */
    public function urls(string $baseUrl): iterable;
}
