<?php

use App\Nexus\Modules\Redirect\Http\Middleware\RedirectMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
