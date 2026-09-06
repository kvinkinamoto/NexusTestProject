<?php

use App\Models\User;
use App\Nexus\Modules\Plugins\Models\Plugin;
use App\Nexus\Plugins\Example\ExamplePlugin;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleTable;
use Nodex\Nexus\Services\HookManager;
use Nodex\Nexus\Services\PluginManager;
use Spatie\Permission\Models\Permission;

/**
 * Step 2 of the Tier-1 admin gap plan (see project_nexus_tier1_admin_gaps
 * memory): admin-facing enable/disable for plugins under app/Nexus/Plugins,
 * backed by the new `nexus_plugins` table instead of only the static
 * config('nexus.plugins.disabled') array PluginSystemTest already covers.
 * These tests exercise the real Livewire toggle end-to-end and then prove
 * the DB flag actually changes PluginManager's behavior on a fresh
 * autoDiscover() — not just that a row's column flipped.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Plugins'])->run();

    // First autoDiscover() seeds one nexus_plugins row per discovered
    // plugin folder (see PluginManager::isPluginEnabled()'s firstOrCreate).
    app(PluginManager::class)->autoDiscover();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');
    $admin = User::factory()->create();
    $admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($admin);
});

it('seeds one row per discovered plugin folder, enabled by default', function () {
    expect(Plugin::findByName('Example'))->not->toBeNull()
        ->and(Plugin::findByName('Example')->is_enabled)->toBeTrue();
});

it('disables a plugin through the real admin toggle action', function () {
    $example = Plugin::findByName('Example');

    Livewire::test(ModuleTable::class, ['moduleName' => 'Plugins'])
        ->call('toggleBool', $example->id, 'is_enabled');

    expect(Plugin::find($example->id)->is_enabled)->toBeFalse();
});

it('stops discovering a plugin disabled via the admin screen, on a fresh autoDiscover()', function () {
    $example = Plugin::findByName('Example');

    Livewire::test(ModuleTable::class, ['moduleName' => 'Plugins'])
        ->call('toggleBool', $example->id, 'is_enabled');

    $manager = new PluginManager(app(HookManager::class));
    $manager->autoDiscover();

    expect($manager->getPlugins('Demo'))->not->toContain(ExamplePlugin::class);
});

it('re-enabling a plugin makes it discoverable again', function () {
    $example = Plugin::findByName('Example');
    $example->update(['is_enabled' => false]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Plugins'])
        ->call('toggleBool', $example->id, 'is_enabled');

    $manager = new PluginManager(app(HookManager::class));
    $manager->autoDiscover();

    expect(Plugin::find($example->id)->is_enabled)->toBeTrue()
        ->and($manager->getPlugins('Demo'))->toContain(ExamplePlugin::class);
});
