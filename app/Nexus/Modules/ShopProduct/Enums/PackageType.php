<?php

namespace App\Nexus\Modules\ShopProduct\Enums;

/**
 * Determines how a purchased product is installed via the already-existing
 * ArchiveInstallService (app/Nexus/Modules/Modules/Services), which accepts
 * exactly these three destination types.
 */
enum PackageType: string
{
    case MODULE = 'module';
    case PLUGIN = 'plugin';
    case WIDGET = 'widget';
}
