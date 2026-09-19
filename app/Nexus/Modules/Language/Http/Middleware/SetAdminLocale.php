<?php

namespace App\Nexus\Modules\Language\Http\Middleware;

use App\Nexus\Modules\Language\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies an admin's own display-locale choice (set via Language's
 * 'switchAdminLocale' row action), independent of whatever locale the public
 * storefront resolves. The admin panel's own ambient default is always
 * English — it must be asserted unconditionally, not merely as a fallback
 * when no session choice exists, because config('app.locale') can no longer
 * be trusted as "English" by the time this middleware runs: the storefront's
 * locale-prefixed route groups call LaravelLocalization::setLocale() eagerly
 * while every route file loads (mcamara's own documented behavior), which
 * overwrites the global app locale to Ukrainian (this app's public-storefront
 * default) on every request whose first URL segment isn't a locale code —
 * that includes every admin request. This middleware re-asserts the correct
 * admin locale afterward, in the same request, to undo that.
 */
class SetAdminLocale
{
    private const string DEFAULT_LOCALE = 'en';

    public function handle(Request $request, Closure $next): Response
    {
        $code = session('admin_locale');

        if ($code) {
            try {
                if (Language::query()->publish()->where('code', $code)->exists()) {
                    App::setLocale($code);

                    return $next($request);
                }
            } catch (\Throwable) {
                // languages table not migrated yet (fresh install) — fail
                // open rather than breaking every admin request, matching
                // ModuleRegistry::getEnabledModules()'s own fail-open
                // precedent for the same "table might not exist yet" case.
            }
        }

        App::setLocale(self::DEFAULT_LOCALE);

        return $next($request);
    }
}
