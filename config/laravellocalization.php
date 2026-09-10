<?php

return [
    /*
     * Trimmed to the two locales this app actually ships translate.php
     * files for (see resources/lang/{en,uk}) — the package's own default
     * config ships an exhaustive, entirely commented-out list of every
     * locale it knows about, meant to be uncommented per-project.
     *
     * Static (not read from the Language module's DB rows) on purpose:
     * this config loads before the app is fully booted, and mcamara's own
     * route-prefix registration needs the list synchronously — a real
     * storefront that goes on to actually use locale-prefixed routes
     * should add a locale here too, alongside seeding the matching
     * Language row.
     */
    'supportedLocales' => [
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
        'uk' => ['name' => 'Ukrainian', 'script' => 'Cyrl', 'native' => 'українська', 'regional' => 'uk_UA'],
    ],

    // No public storefront exists yet to redirect based on the browser's
    // Accept-Language header — leave locale resolution to config('app.locale')
    // / the admin's own SetAdminLocale middleware until one does.
    'useAcceptLanguageHeader' => false,

    'hideDefaultLocaleInURL' => true,

    'localesOrder' => [],

    'localesMapping' => [],

    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', '.UTF-8'),

    'urlsIgnored' => [],

    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];
