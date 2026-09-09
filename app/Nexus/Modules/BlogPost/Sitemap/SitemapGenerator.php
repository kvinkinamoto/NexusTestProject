<?php

namespace App\Nexus\Modules\BlogPost\Sitemap;

use App\Nexus\Modules\BlogPost\Models\BlogPost;
use App\Nexus\Modules\Sitemap\Generators\AbstractSitemapGenerator;
use Illuminate\Database\Eloquent\Builder;

class SitemapGenerator extends AbstractSitemapGenerator
{
    public static function sitemapConfig(): array
    {
        return [
            'filename' => 'sitemap_blog_posts.xml',
            'enabled' => true,
            'changefreq' => 'weekly',
            'priority' => 0.7,
        ];
    }

    protected function query(): Builder
    {
        return BlogPost::query()->where('is_published', true);
    }

    protected function buildPath(mixed $record): ?string
    {
        return $record->slug ? "/blog/{$record->slug}" : null;
    }
}
