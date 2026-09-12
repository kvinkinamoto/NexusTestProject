<?php

namespace App\Nexus\Modules\Advertising\Models;

use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Setting;

/**
 * Settings-only module — see Analytics\Models\Analytics.php's docblock for
 * the full rationale (same shape, same reasoning, kept as a separate module
 * so ad-conversion tracking can be toggled/configured independently of
 * analytics). Read at render time via SettingsBuilder::get('advertising',
 * {name}) — see
 * Storefront\resources\views\public\partials\tracking-scripts.blade.php.
 */
#[Module(
    name: 'advertising',
    label: 'Advertising',
    icon: 'solar:megaphone-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[Setting(name: 'enabled', type: 'boolean', label: 'Enabled', default: true, comment: 'Turn off to stop injecting any ad-tracking script, without clearing the IDs below.')]
#[Setting(name: 'facebook_pixel_id', type: 'string', label: 'Meta (Facebook) Pixel ID', comment: 'Numeric Pixel ID from Meta Events Manager.')]
#[Setting(name: 'google_ads_conversion_id', type: 'string', label: 'Google Ads Conversion ID', comment: 'e.g. AW-XXXXXXXXX — from Google Ads > Conversions.')]
class Advertising extends Model
{
    //
}
