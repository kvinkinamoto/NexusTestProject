<?php

use App\Models\User;
use App\Nexus\Modules\Modules\Models\Module as ModulesAdminModel;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleTable;
use Nodex\Nexus\Models\Module;
use Spatie\Permission\Models\Permission;

/**
 * The 'Modules' module (app/Nexus/Modules/Modules) is what lets an admin
 * enable/disable other modules from the admin panel. Without a guard, an
 * admin could disable that module itself — or delete its nexus_modules row
 * outright — and lock themselves out of the only screen that could undo it.
 *
 * Two separate Eloquent classes back the same nexus_modules row here:
 * Nodex\Nexus\Models\Module (the framework's internal is_enabled gate) and
 * App\Nexus\Modules\Modules\Models\Module (what the generic admin CRUD
 * engine — BoolToggleActionMethod, DeleteActionMethod — actually resolves
 * to for this screen, via #[Module]'s reflected `model`). PHP attribute
 * reflection doesn't see inherited attributes, so these can't share a class;
 * both independently declare the same is_system guard in booted(), and this
 * test exercises the guard through the real Livewire component (ModuleTable)
 * so it's proven against the exact code path a click in the admin panel
 * takes, not just against whichever class happens to be easiest to unit test.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Modules'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $admin = User::factory()->create();
    $admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($admin);
});

it('marks the Modules module as system-protected on install, without a manual step', function () {
    expect(Module::findByName('Modules')->is_system)->toBeTrue();
});

it('does not mark an ordinary module as system-protected on install', function () {
    expect(Module::findByName('Demo')->is_system)->toBeFalse();
});

it('blocks disabling the system-protected Modules row via the real admin toggle action', function () {
    $modulesRow = Module::findByName('Modules');

    Livewire::test(ModuleTable::class, ['moduleName' => 'Modules'])
        ->call('toggleBool', $modulesRow->id, 'is_enabled');

    expect(ModulesAdminModel::find($modulesRow->id)->is_enabled)->toBeTrue();
});

it('blocks deleting the system-protected Modules row via the real admin delete action', function () {
    $modulesRow = Module::findByName('Modules');

    Livewire::test(ModuleTable::class, ['moduleName' => 'Modules'])
        ->call('runAction', 'delete', $modulesRow->id);

    expect(ModulesAdminModel::find($modulesRow->id))->not->toBeNull();
});

it('still allows disabling an ordinary (non-system) module through the same admin screen', function () {
    $demoRow = Module::findByName('Demo');

    Livewire::test(ModuleTable::class, ['moduleName' => 'Modules'])
        ->call('toggleBool', $demoRow->id, 'is_enabled');

    expect(ModulesAdminModel::find($demoRow->id)->is_enabled)->toBeFalse();
});
