<?php

return [
    'module_name' => 'Site Settings',
    'index_title' => 'Site Settings',
    'group_label' => 'Site Settings',

    // sections (keyed by the raw section name — same convention as Demo's translate.php)
    'main' => 'General',
    'media' => 'Media',

    // field labels (keyed lowercase — _label.blade.php looks up Str::lower($field->label))
    'id' => 'ID',
    'site name' => 'Site name',
    'site description' => 'Site description',
    'maintenance mode' => 'Maintenance mode',
    'logo' => 'Logo',
    'favicon' => 'Favicon',

    'docs' => [
        'title' => 'Site Settings',
        'subtitle' => 'Global, singleton site configuration (name, description, maintenance mode, logo, favicon) edited from a single row reached via the admin topbar.',
    ],
];
