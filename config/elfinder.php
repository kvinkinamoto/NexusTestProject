<?php

use Nodex\Nexus\Http\Middleware\NexusAdminMiddleware;

return [

    /*
    |--------------------------------------------------------------------------
    | Upload dir
    |--------------------------------------------------------------------------
    |
    | The dir where to store the images (relative from public)
    |
    */
    'dir' => ['storage'],

    /*
    |--------------------------------------------------------------------------
    | Filesystem disks (Flysytem)
    |--------------------------------------------------------------------------
    |
    | Define an array of Filesystem disks, which use Flysystem.
    | You can set extra options, example:
    |
    | 'my-disk' => [
    |        'URL' => url('to/disk'),
    |        'alias' => 'Local storage',
    |    ]
    */
    'disks' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Routes group config
    |--------------------------------------------------------------------------
    |
    | The default group settings for the elFinder routes.
    |
    */

    'route' => [
        'prefix' => 'elfinder',
        // Plain 'auth' alone let ANY logged-in account (including a
        // front-end user with zero Nexus admin permissions) reach the file
        // manager. NexusAdminMiddleware requires the same admin_panel
        // permission every /admin/* route already does — see
        // config('nexus.admin_middleware'). Must come after 'auth': it
        // calls auth()->user() unconditionally, no null-user guard of its
        // own.
        'middleware' => ['web', 'auth', NexusAdminMiddleware::class], // Set to null to disable middleware filter
    ],

    /*
    |--------------------------------------------------------------------------
    | Access filter
    |--------------------------------------------------------------------------
    |
    | Filter callback to check the files
    |
    */

    'access' => 'Barryvdh\Elfinder\Elfinder::checkAccess',

    /*
    |--------------------------------------------------------------------------
    | Roots
    |--------------------------------------------------------------------------
    |
    | By default, the roots file is LocalFileSystem, with the above public dir.
    | If you want custom options, you can set your own roots below.
    |
    */

    'roots' => null,

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    | These options are merged, together with 'roots' and passed to the Connector.
    | See https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options-2.1
    |
    */

    'options' => [],

    /*
    |--------------------------------------------------------------------------
    | Root Options
    |--------------------------------------------------------------------------
    |
    | These options are merged, together with every root by default.
    | See https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options-2.1#root-options
    |
    */
    'root_options' => [
        // No allow-list existed before this — elFinder's own defaults
        // accepted any file type. Nexus's elFinder-backed field types are
        // image/video/general-file pickers only (see #[Field(type: 'image'
        // | 'video' | 'images' | 'videos' | 'file')]), so upload is
        // restricted to what those actually need.
        'uploadOrder' => ['deny', 'allow'],
        'uploadDeny' => ['all'],
        'uploadAllow' => ['image', 'video', 'application/pdf', 'text/plain'],
    ],

];
