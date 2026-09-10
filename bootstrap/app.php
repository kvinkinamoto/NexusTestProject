<?php

use App\Nexus\Modules\Redirect\Http\Middleware\RedirectMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Applies Laravel's default 60-requests/minute throttle to every
        // route using the 'api' middleware group — covers the REST wildcard
        // route and /graphql (both include 'api' in their configured
        // middleware, see config/nexus.php's api_middleware/graphql_middleware),
        // neither of which had any rate limiting before this.
        $middleware->throttleApi();

        // True global middleware (not ->web()) so it runs ahead of routing
        // itself — see RedirectMiddleware's own docblock for why a group
        // middleware wouldn't cover the common "old URL, no route matches
        // at all" case.
        $middleware->append(RedirectMiddleware::class);

        // mcamara/laravel-localization's own middleware, registered as
        // aliases (not applied to any route group) — this app has no
        // public storefront yet for locale-prefixed routes to belong to
        // (see the Language module's docs.blade.php). A future storefront
        // route group can opt in with these names directly, matching how
        // the old ZentaraStartProject project registered them.
        $middleware->alias([
            'localize' => LaravelLocalizationRoutes::class,
            'localizationRedirect' => LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect' => LocaleSessionRedirect::class,
            'localeCookieRedirect' => LocaleCookieRedirect::class,
            'localeViewPath' => LaravelLocalizationViewPath::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
