<?php

return [
    'modules' => 'module',
    'Modules' => 'Module Management',
    'index_title' => 'Module Management',
    'module_name' => 'Module',

    'Name' => 'Name',
    'Enabled' => 'Enabled',
    'System' => 'System',
    'View' => 'View',
    'Install from archive' => 'Install from archive',
    'search by name' => 'Search by name',

    'id' => 'ID',
    'name' => 'Name',
    'enabled' => 'Enabled',
    'system' => 'System',
    'view' => 'View',

    'install_from_archive' => [
        'title' => 'Install from archive',
        'back' => 'Back to modules',
        'help' => 'Upload a .zip containing exactly one top-level folder, named after the module, plugin, or widget (e.g. a zip whose root is an Advertising/ folder). For a module, this also runs nexus:module:install for you.',
        'type' => 'Type',
        'type_module' => 'Module',
        'type_plugin' => 'Plugin',
        'type_widget' => 'Widget',
        'archive' => 'Archive (.zip)',
        'submit' => 'Install',
        'success' => 'Installed ":name" successfully.',
    ],

    'docs' => [
        'title' => 'Module Management',
        'subtitle' => 'Every module installed in the application, with a per-module enable/disable switch.',
        'usage_title' => 'View, toggle, and install from archive',
        'usage_desc' => 'Rows here are created by nexus:module:install — the admin can view a module and enable/disable it, or install a new module, plugin, or widget by uploading a .zip archive.',
        'tech_title' => 'Technical Details',
        'admin_part' => 'Admin Part',
        'api_part' => 'API Part',
        'endpoints' => 'Endpoints',
        'fields' => 'Fields',
    ],
];
