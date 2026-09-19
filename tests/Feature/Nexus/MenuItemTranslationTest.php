<?php

use App\Nexus\Modules\Menu\Models\Menu;
use App\Nexus\Modules\Menu\Models\MenuItem;

/**
 * Data-layer coverage for MenuItem::$translatable (Spatie\Translatable) —
 * separate from MenuItemsManagerTest's UI-level coverage, since the admin
 * form can only ever exercise a single locale in this install today (no
 * Language module exists yet — see activeLocales() test). This proves the
 * underlying storage is genuinely locale-aware regardless of what the UI
 * currently offers.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Menu'])->run();

    $this->menu = Menu::create(['name' => 'Header', 'key' => 'header', 'is_active' => true]);
});

it('stores a plain string under the current app locale', function () {
    $item = $this->menu->items()->create(['title' => 'Home']);

    expect($item->getTranslation('title', config('app.locale')))->toBe('Home');
});

it('round-trips per-locale titles independently', function () {
    $item = $this->menu->items()->create(['title' => 'Home']);
    $defaultLocale = config('app.locale');
    // Picked relative to the app's real default (now 'uk' — see .env) rather
    // than hardcoded, so this proves two *different* locale keys round-trip
    // independently regardless of which locale that default is.
    $otherLocale = $defaultLocale === 'uk' ? 'en' : 'uk';

    $item->setTranslation('title', $otherLocale, 'Головна');
    $item->save();
    $item->refresh();

    expect($item->getTranslation('title', $defaultLocale))->toBe('Home');
    expect($item->getTranslation('title', $otherLocale))->toBe('Головна');
});
