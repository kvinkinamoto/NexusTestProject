<?php

namespace App\Nexus\Modules\Storefront;

use Nodex\Nexus\Attributes\Module;

/**
 * Config-only module (no model/migration), same "Strategy 1: dedicated
 * ModuleConfiguration class" pattern as App\Nexus\Modules\Settings — gives
 * NexusServiceProvider a `storefront::` view namespace and routes/web.php
 * auto-loading with no Eloquent model behind it. Owns the parts of the
 * storefront with no single data-owning module: the home page and the
 * shared public layout/partials every other module's public views extend.
 * Not shown in the sidebar — there is nothing here for an admin to browse.
 */
#[Module(
    name: 'storefront',
    label: 'Storefront',
    icon: 'solar:shop-bold',
    group: 'Site',
    showInMenu: false,
)]
class ModuleConfiguration {}
