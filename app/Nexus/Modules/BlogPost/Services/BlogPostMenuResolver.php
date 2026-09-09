<?php

namespace App\Nexus\Modules\BlogPost\Services;

use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Contracts\UrlResolverInterface;

/**
 * Wired via #[Module(menuResolver: self::class)] on BlogPost — see
 * App\Nexus\Modules\Page\Services\PageMenuResolver for the shared context
 * on completing the package's dormant menuResolver mechanism.
 */
class BlogPostMenuResolver implements UrlResolverInterface
{
    public function resolve(Model $model): ?string
    {
        if (! app('router')->has('nexus.blogPost.show')) {
            return null;
        }

        return route('nexus.blogPost.show', $model->slug);
    }
}
