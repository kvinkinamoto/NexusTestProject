<?php

namespace App\Nexus\Plugins\GraphQL;

use Illuminate\Support\Facades\Route;
use Nodex\Nexus\Attributes\TargetModule;

/**
 * Premium plugin: the GraphQL API layer, extracted out of the open-core
 * nodex/nexus package. #[TargetModule('User')] doesn't mutate User's config
 * here (handle() is a no-op) — it's only how PluginManager::registerAll()/
 * bootAll() decide to call this plugin's register()/boot() at all (see
 * PluginManager::discoverClass()); 'User' is just a semantically-neutral,
 * always-installed anchor, same default nexus:make:plugin itself uses. The
 * actual GraphQL wiring happens in boot() below, independent of any
 * specific module.
 *
 * Discovered automatically from app/Nexus/Plugins/GraphQL/ — no manual
 * registration needed. Remove this whole folder to disable GraphQL
 * entirely, or list this class in config('nexus.plugins.disabled') to keep
 * the files but skip discovery.
 */
#[TargetModule('User')]
class GraphQLPlugin
{
    public function handle(object $configuration): void {}

    public function boot(): void
    {
        // Deliberately its own top-level route, not nested under
        // api_prefix — GraphQL clients expect one fixed endpoint
        // (conventionally /graphql), not one path per resource the way the
        // REST wildcard route is. Needs auth:sanctum on top of the plain
        // 'api' group REST uses, since token auth is this endpoint's own
        // concern (see SchemaBuilder's docblock).
        Route::middleware(config('nexus.graphql_middleware', ['api', 'auth:sanctum']))
            ->post(config('nexus.graphql_prefix', 'graphql'), [GraphQLController::class, 'handle'])
            ->name('api.nexus.graphql');
    }
}
