<?php

namespace App\Nexus\Modules\Page\Services;

use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Contracts\UrlResolverInterface;

/**
 * Wired via #[Module(menuResolver: self::class)] on Page — completes the
 * package's own dormant menuResolver/UrlResolverInterface mechanism
 * (AttributeSchemaReader already mapped #[Module(menuResolver:)] into
 * config->resolvers['menu'] since before this session, nothing had ever
 * implemented the interface or called the resolver). Router guard mirrors
 * the pattern already used elsewhere for a resolver whose route might not
 * be registered (e.g. the module disabled) — degrade to null, don't 500.
 */
class PageMenuResolver implements UrlResolverInterface
{
    public function resolve(Model $model): ?string
    {
        if (! app('router')->has('nexus.page.show')) {
            return null;
        }

        return route('nexus.page.show', $model->slug);
    }
}
