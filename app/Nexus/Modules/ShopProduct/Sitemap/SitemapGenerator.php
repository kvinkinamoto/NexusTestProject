<?php

namespace App\Nexus\Modules\ShopProduct\Sitemap;

use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\Sitemap\Generators\AbstractSitemapGenerator;
use Illuminate\Database\Eloquent\Builder;

/**
 * The catalog's own products were missing from the sitemap entirely — only
 * Page and BlogPost had a generator (see SitemapAutoService's auto-discovery
 * docblock) — even though products are this store's actual sellable content
 * and the ones worth ranking most.
 */
class SitemapGenerator extends AbstractSitemapGenerator
{
    public static function sitemapConfig(): array
    {
        return [
            'filename' => 'sitemap_products.xml',
            'enabled' => true,
            'chunk' => true,
            'changefreq' => 'weekly',
            'priority' => 0.9,
        ];
    }

    protected function query(): Builder
    {
        return ShopProduct::query()->publish();
    }

    protected function buildPath(mixed $record): ?string
    {
        return $record->slug ? "/product/{$record->slug}" : null;
    }
}
