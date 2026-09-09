<?php

return [
    'sitemap' => 'sitemap',
    'Sitemap' => 'Sitemap',
    'index_title' => 'Sitemap — custom URLs',
    'url' => 'url',
    'priority' => 'priority',
    'change frequency' => 'Change frequency',
    'last modified' => 'Last modified',
    'is active' => 'Is active',
    'note' => 'note',
    'main' => 'Main',
    'module_name' => 'Sitemap',
    'edit' => 'Edit',
    'delete' => 'Delete',
    // module-level #[Setting(...)] labels — module-settings-form.blade.php
    // looks these up lowercased, same convention as Demo's own settings.
    'generation mode' => 'Generation mode',
    'base url override' => 'Base URL override',
    'split size' => 'Split size',
    'docs' => [
        'title' => 'Sitemap module',
        'subtitle' => 'Generates public/sitemap.xml (and per-module files) from every module that declares its own Sitemap/SitemapGenerator.php, plus the custom URLs listed below. Run "php artisan nexus:sitemap:generate" or wait for the daily schedule; tune mode/base URL/split size in Settings.',
    ],
];
