<?php

namespace App\Nexus\Modules\ShopCategory\Sitemap;

use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\Sitemap\Generators\AbstractSitemapGenerator;
use Illuminate\Database\Eloquent\Builder;

class SitemapGenerator extends AbstractSitemapGenerator
{
    public static function sitemapConfig(): array
    {
        return [
            'filename' => 'sitemap_categories.xml',
            'enabled' => true,
            'changefreq' => 'weekly',
            'priority' => 0.7,
        ];
    }

    protected function query(): Builder
    {
        return ShopCategory::query()->publish();
    }

    protected function buildPath(mixed $record): ?string
    {
        return $record->slug ? "/catalog/{$record->slug}" : null;
    }
}
