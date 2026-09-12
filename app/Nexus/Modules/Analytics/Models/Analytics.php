<?php

namespace App\Nexus\Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Setting;

/**
 * Settings-only module, same shape as Sitemap's #[Setting]s (see
 * SitemapCustomUrl.php) — no manageable records of its own. Kept
 * showInMenu:true anyway (unlike Sitemap's settings, which piggyback on a
 * module with real rows to browse) purely for discoverability — a module
 * with nothing but settings would otherwise only surface inside the
 * profile-dropdown "Site Settings" hub, which is easy to miss. Its index
 * page renders an empty, harmless list; module-table.blade.php already puts
 * a "Settings" button in that page's own toolbar whenever
 * config->settings is non-empty, which is the real entry point. Settings
 * are read at render time via SettingsBuilder::get('analytics', {name}) —
 * see Storefront\resources\views\public\partials\tracking-scripts.blade.php,
 * the only consumer.
 */
#[Module(
    name: 'analytics',
    label: 'Analytics',
    icon: 'solar:chart-2-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[Setting(name: 'enabled', type: 'boolean', label: 'Enabled', default: true, comment: 'Turn off to stop injecting any analytics script, without clearing the IDs below.')]
#[Setting(name: 'ga4_measurement_id', type: 'string', label: 'Google Analytics 4 Measurement ID', comment: 'e.g. G-XXXXXXXXXX — from GA4 Admin > Data Streams.')]
#[Setting(name: 'gtm_container_id', type: 'string', label: 'Google Tag Manager Container ID', comment: 'e.g. GTM-XXXXXXX — leave blank if you use GA4 directly instead of GTM.')]
class Analytics extends Model
{
    //
}
