<?php

return [
    'module_name' => 'Site Settings',
    'index_title' => 'Site Settings',
    'group_label' => 'Site Settings',
    'settings' => 'Site Settings',

    // setting labels (keyed lowercase — same convention as field labels,
    // see module-settings-form.blade.php / _label.blade.php)
    'site name' => 'Site name',
    'site description' => 'Site description',
    'maintenance mode' => 'Maintenance mode',
    'logo' => 'Logo',
    'favicon' => 'Favicon',

    // "All Settings" overview page (Http/Controllers/AdminController::index())
    'manage' => 'Manage',
    'settings count suffix' => 'settings',
    'no settings registered' => 'No module has any settings registered yet.',

    'docs' => [
        'title' => 'Site Settings',
        'subtitle' => 'Global site configuration (name, description, maintenance mode, logo, favicon), reached via the admin topbar and backed by the generic per-module settings registry — not its own table.',
    ],
];
