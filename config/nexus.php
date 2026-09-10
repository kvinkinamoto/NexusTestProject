<?php

use App\Nexus\Modules\Language\Http\Middleware\SetAdminLocale;
use Nodex\Nexus\Http\Middleware\NexusAdminMiddleware;

return [
    'template' => env('ZENTARA_TEMPLATE', 'tailadmin'), // tailadmin (nexus theme archived, see resources/views/nexus/ removed in Stage 2)
    'admin_prefix' => env('ADMIN_PREFIX', 'admin'),
    'admin_middleware' => ['web', 'auth', NexusAdminMiddleware::class, SetAdminLocale::class],

    'api_prefix' => env('API_PREFIX', 'api'),
    'api_middleware' => ['api'],

    'table' => [
        'pagination' => [
            'per_page_options' => [15, 30, 50, 100],
            'default_per_page' => 15,
        ],
    ],
    'permissions' => [
        'generate_default' => true,
    ],

    'toast' => [
        'enabled' => true,
        'delay' => 5000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Template Type Map
    |--------------------------------------------------------------------------
    | Map route names to template type slugs for the widget system.
    | Widgets assigned to a specific template_type will only appear
    | when the current route name matches the key here.
    |
    | Example:
    |   'home' => 'home',              // Route named 'home' → templateType 'home'
    |   'categories.show' => 'category',
    |   'pages.show' => 'page',
    |
    | Widgets with template_type = NULL are shown on ALL pages.
    |
    */
    'widget_template_map' => [
        // 'home' => 'home',
        // 'categories.show' => 'category',
        // 'pages.show' => 'page',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    | Ordered list of widget keys (#[Widget(name:)]) shown on a fresh install
    | with zero rows in nexus_dashboard_layouts — see
    | Services/Widgets/DashboardLayoutResolver.php.
    */
    'dashboard' => [
        'default' => ['usersCount', 'demoRecordsCount'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Library
    |--------------------------------------------------------------------------
    | #[Field(type: 'gallery')] is backed by Nodex\Nexus\Contracts\MediaLibrary\MediaLibraryInterface,
    | bound by default to a spatie/laravel-medialibrary-backed implementation
    | (Services/MediaLibrary/SpatieMediaLibraryService) — swap it for your own
    | by rebinding the interface in your own service provider (standard
    | Laravel container override, no Nexus-specific plumbing needed). Set
    | 'enabled' to false to disable the 'gallery' field type entirely (it
    | renders a plain notice instead) without touching any module — the
    | existing elFinder-backed image/images/video/videos types are
    | completely unaffected either way.
    */
    'media_library' => [
        'enabled' => env('NEXUS_MEDIA_LIBRARY_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins
    |--------------------------------------------------------------------------
    | PluginManager::autoDiscover() scans app/Nexus/Plugins/** for classes
    | carrying #[TargetModule]/#[Filter]/#[Action] (see Services/PluginManager.php,
    | commands/MakePluginCommand.php) and registers every one it finds — list
    | a plugin's fully-qualified class name here to skip it without deleting
    | the file (e.g. while debugging one plugin among several).
    */
    'plugins' => [
        'disabled' => [
            // App\Nexus\Plugins\Example\ExamplePlugin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GraphQL (premium plugin: app/Nexus/Plugins/GraphQL)
    |--------------------------------------------------------------------------
    | Read by GraphQLPlugin::boot() when it registers the /graphql route —
    | these keys used to live in the open-core package's own config, but
    | GraphQL is now a premium app-level plugin, so they live here instead.
    | Remove this section along with app/Nexus/Plugins/GraphQL to disable
    | GraphQL entirely.
    */
    'graphql_prefix' => env('GRAPHQL_PREFIX', 'graphql'),
    'graphql_middleware' => ['api', 'auth:sanctum'],
];
