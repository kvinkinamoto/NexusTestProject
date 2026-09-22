<?php

use App\Nexus\Modules\Language\Models\Language;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Services\FormBuilder;

/**
 * Regression coverage for FormBuilder::getLanguages() — the source every
 * translatable #[Field] (ShopCategory::name, Demo::title_i18n, and now the
 * Page block-editor's translatable fields) reads through
 * ModuleForm::activeLocales() to decide how many per-locale rows to render.
 *
 * Bug: Module::$name is stored as whatever module-folder name
 * nexus:module:install was given (Str::studly, e.g. "Language" — see
 * ModuleManager::instanceInstall()), not the #[Module(name: 'language')]
 * attribute's lowercase registry key. The old exact-match
 * where('name', 'language') therefore never matched a really-installed
 * Language module, so getLanguages() silently always fell back to the
 * single config('app.locale') value — every translatable field in the
 * admin only ever rendered one locale row, even with several published
 * languages configured. Fixed to reuse Module::findByName()'s existing
 * case-insensitive lookup.
 */
it('returns just the app locale when the Language module is not installed', function () {
    expect(FormBuilder::getLanguages())->toBe([config('app.locale')]);
});

it('returns every published language once the Language module is installed and enabled', function () {
    $this->artisan('nexus:module:install', ['name' => 'Language'])->run();

    Language::query()->create(['language' => 'Українська', 'code' => 'uk', 'is_default' => true, 'publish' => true]);
    Language::query()->create(['language' => 'English', 'code' => 'en', 'publish' => true]);
    Language::query()->create(['language' => 'Draft', 'code' => 'xx', 'publish' => false]);

    expect(FormBuilder::getLanguages()->all())->toBe(['uk', 'en']);
});

it('falls back to the app locale when the Language module is installed but disabled', function () {
    $this->artisan('nexus:module:install', ['name' => 'Language'])->run();
    Module::findByName('language')->update(['is_enabled' => false]);

    Language::query()->create(['language' => 'Українська', 'code' => 'uk', 'is_default' => true, 'publish' => true]);

    expect(FormBuilder::getLanguages())->toBe([config('app.locale')]);
});
