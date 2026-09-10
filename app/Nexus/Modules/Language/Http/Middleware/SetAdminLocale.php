<?php

namespace App\Nexus\Modules\Language\Http\Middleware;

use App\Nexus\Modules\Language\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies an admin's own display-locale choice (set via Language's
 * 'switchAdminLocale' row action), independent of whatever locale a future
 * public storefront might resolve. Falls back to config('app.locale') when
 * nothing is chosen yet, or the chosen code no longer names a published
 * language (e.g. it was unpublished/deleted after being selected).
 */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = session('admin_locale');

        if ($code) {
            try {
                if (Language::query()->publish()->where('code', $code)->exists()) {
                    App::setLocale($code);
                }
            } catch (\Throwable) {
                // languages table not migrated yet (fresh install) — fail
                // open rather than breaking every admin request, matching
                // ModuleRegistry::getEnabledModules()'s own fail-open
                // precedent for the same "table might not exist yet" case.
            }
        }

        return $next($request);
    }
}
