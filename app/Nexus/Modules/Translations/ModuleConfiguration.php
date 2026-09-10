<?php

namespace App\Nexus\Modules\Translations;

use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Permission;

/**
 * No bound Eloquent model — "Strategy 1: Dedicated ModuleConfiguration
 * class" in ModuleManager::resolveModuleConfig(), the same path
 * App\Nexus\Modules\Settings uses. Every screen this module renders
 * (index/phrases/edit) is a fully custom Http\Controllers\AdminController
 * view, not the generic module-table/module-form Livewire components, so
 * no #[Section]/#[Field]/#[TableAction] declarations are needed here —
 * there is no declarative table/form for them to describe.
 *
 * 'index'/'edit' reuse the framework's own built-in action names (and their
 * default permission handling); 'phrases'/'update' are custom action names,
 * so they get their own #[Permission] the same way Backup's 'run' and
 * Restore's 'scan'/'runRestore' did in Phase 12 — registers the permission
 * for role assignment, doesn't grant it to anyone.
 */
#[Module(
    name: 'translations',
    label: 'Translations',
    icon: 'solar:translation-bold',
    group: 'Site',
    showInMenu: true,
)]
#[Permission(action: 'phrases', label: 'Browse translation phrases')]
#[Permission(action: 'update', label: 'Update translations')]
class ModuleConfiguration {}
