<?php

namespace App\Nexus\Modules\Redirect\Http\Middleware;

use App\Nexus\Modules\Redirect\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Nodex\Nexus\Models\Module;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registered as a true global middleware (bootstrap/app.php's
 * $middleware->append(), not ->web()) so it runs ahead of routing itself —
 * a group middleware only executes once a route has already matched, which
 * would miss the common case of an old URL that no longer matches any route
 * at all. Running globally also lets it override a still-technically-routable
 * but now-removed path (e.g. a stale slug that 404s inside a controller)
 * before that controller ever runs.
 */
class RedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isReservedPath($request) || ! $this->moduleEnabled()) {
            return $next($request);
        }

        $redirect = Redirect::query()
            ->where('is_active', true)
            ->where('from_path', trim($request->path(), '/'))
            ->first();

        if (! $redirect) {
            return $next($request);
        }

        $isAbsolute = str_starts_with($redirect->to_path, 'http://') || str_starts_with($redirect->to_path, 'https://');

        return $isAbsolute
            ? redirect()->away($redirect->to_path, $redirect->status_code)
            : redirect()->to($redirect->to_path, $redirect->status_code);
    }

    /**
     * Must never intercept admin, REST API, or GraphQL traffic — this
     * middleware runs ahead of routing for every request, including those,
     * per the explicit "rights + API compatibility" requirement covering
     * this whole module build.
     */
    private function isReservedPath(Request $request): bool
    {
        foreach ([config('nexus.admin_prefix'), config('nexus.api_prefix'), config('nexus.graphql_prefix')] as $prefix) {
            if (! $prefix) {
                continue;
            }

            if ($request->is($prefix) || $request->is($prefix.'/*')) {
                return true;
            }
        }

        return false;
    }

    private function moduleEnabled(): bool
    {
        try {
            return (bool) Module::findByName('redirect')?->is_enabled;
        } catch (\Throwable) {
            return false;
        }
    }
}
