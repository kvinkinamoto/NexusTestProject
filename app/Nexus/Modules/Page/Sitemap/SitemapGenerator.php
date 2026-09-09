<?php

namespace App\Nexus\Modules\Page\Sitemap;

use App\Nexus\Modules\Page\Models\Page;
use App\Nexus\Modules\Sitemap\Generators\AbstractSitemapGenerator;
use Illuminate\Database\Eloquent\Builder;

class SitemapGenerator extends AbstractSitemapGenerator
{
    public static function sitemapConfig(): array
    {
        return [
            'filename' => 'sitemap_pages.xml',
            'enabled' => true,
            'changefreq' => 'weekly',
            'priority' => 0.8,
        ];
    }

    protected function query(): Builder
    {
        return Page::query()->isPublished();
    }

    protected function buildPath(mixed $record): ?string
    {
        return $record->slug ? "/pages/{$record->slug}" : null;
    }
}
