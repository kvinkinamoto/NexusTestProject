<?php

namespace App\Nexus\Modules\Settings;

use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Setting;

/**
 * Site-wide settings, built entirely on the generic #[Setting(...)] registry
 * (Nodex\Nexus\Livewire\ModuleSettingsForm + SettingsBuilder) rather than a
 * bound Eloquent model — each setting is one attribute here and one
 * (module, key) row in nexus_module_settings, never its own migration or
 * model column. This is "Strategy 1: Dedicated ModuleConfiguration class" in
 * ModuleManager::resolveModuleConfig() — the same path a config-only module
 * with no table of its own always had available; #[Setting] just gives it
 * something to declare.
 *
 * Not shown in the sidebar menu — the admin topbar's account dropdown links
 * straight to this module's settings screen (see current(), used from
 * tailadmin/layouts/header.blade.php) rather than a list, since a
 * ModuleConfiguration class has no list to browse to. showInMenu: false
 * follows the same precedent as Nodex\Nexus\Modules\Auth\ModuleConfiguration.
 *
 * Any other module can attach its own settings the exact same way — add
 * #[Setting(...)] to its own Models/{Name}.php or ModuleConfiguration.php —
 * see App\Nexus\Modules\Plugins\Models\Plugin for a module that could grow
 * one later (e.g. a global default enabled/disabled policy for new plugins).
 */
#[Module(
    name: 'settings',
    label: 'Site Settings',
    icon: 'solar:settings-bold',
    group: 'Site',
    showInMenu: false,
)]
#[Setting(name: 'site_name', type: 'string', label: 'Site name', default: 'Nexus', required: true)]
#[Setting(name: 'site_description', type: 'text', label: 'Site description')]
#[Setting(name: 'maintenance_mode', type: 'boolean', label: 'Maintenance mode', default: false)]
#[Setting(name: 'logo', type: 'image', label: 'Logo')]
#[Setting(name: 'favicon', type: 'image', label: 'Favicon')]
class ModuleConfiguration {}
