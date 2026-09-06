<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleSettingsForm;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Models\ModuleSetting;
use Nodex\Nexus\Services\ModuleManager;
use Nodex\Nexus\Services\SettingsBuilder;
use Spatie\Permission\Models\Permission;

/**
 * Site-wide settings is built entirely on the generic per-module #[Setting]
 * registry (Nodex\Nexus\Livewire\ModuleSettingsForm + SettingsBuilder +
 * nexus_module_settings), not a dedicated table/model per setting — see
 * App\Nexus\Modules\Settings\ModuleConfiguration. These tests exercise the
 * registry itself (any module could declare its own #[Setting]s the same
 * way) as well as the Settings module's specific attributes, and the real
 * Livewire edit path the topbar link points at, the same way
 * tests/Feature/Nexus/SystemModuleGuardTest.php proves its guards through
 * the real admin component rather than by calling Eloquent directly.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Settings'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('reads the Settings module #[Setting] attributes into its config, with no bound model', function () {
    $config = ModuleManager::getModuleConfig('settings');

    expect($config->model)->toBe(Model::class)
        ->and(array_keys($config->settings))->toBe(['site_name', 'site_description', 'maintenance_mode', 'logo', 'favicon'])
        ->and($config->settings['site_name']->default)->toBe('Nexus')
        ->and($config->settings['maintenance_mode']->type)->toBe('boolean');
});

it('falls back to the declared default before any value is ever set', function () {
    expect(SettingsBuilder::get('settings', 'site_name', 'fallback'))->toBe('fallback');

    expect(ModuleSetting::count())->toBe(0);
});

it('persists a value with no migration or model column involved', function () {
    SettingsBuilder::set('settings', 'site_name', 'Nexus CMS');
    SettingsBuilder::set('settings', 'maintenance_mode', true);

    expect(SettingsBuilder::get('settings', 'site_name', null))->toBe('Nexus CMS')
        ->and(SettingsBuilder::get('settings', 'maintenance_mode', null))->toBeTrue()
        ->and(ModuleSetting::count())->toBe(2);
});

it('scopes settings per module — the same key on two modules never collides', function () {
    $this->artisan('nexus:module:install', ['name' => 'Plugins'])->run();

    SettingsBuilder::set('settings', 'site_name', 'From Settings module');
    SettingsBuilder::set('plugins', 'site_name', 'From Plugins module');

    expect(SettingsBuilder::get('settings', 'site_name', null))->toBe('From Settings module')
        ->and(SettingsBuilder::get('plugins', 'site_name', null))->toBe('From Plugins module');
});

it('saves changes through the real admin settings form', function () {
    Livewire::test(ModuleSettingsForm::class, ['moduleName' => 'settings'])
        ->set('data.site_name', 'Nexus CMS')
        ->set('data.site_description', 'A modular Laravel CMS')
        ->set('data.maintenance_mode', true)
        ->call('save');

    expect(SettingsBuilder::get('settings', 'site_name', null))->toBe('Nexus CMS')
        ->and(SettingsBuilder::get('settings', 'site_description', null))->toBe('A modular Laravel CMS')
        ->and(SettingsBuilder::get('settings', 'maintenance_mode', null))->toBeTrue();
});

it('rejects clearing the required site name field through the real admin form', function () {
    Livewire::test(ModuleSettingsForm::class, ['moduleName' => 'settings'])
        ->set('data.site_name', '')
        ->call('save')
        ->assertHasErrors(['data.site_name' => 'required']);

    expect(ModuleSetting::count())->toBe(0);
});

it('points the admin topbar Settings link at the settings edit page, not a dead link', function () {
    $response = $this->get(route('nexus.admin'));

    $response->assertOk();
    $response->assertSee(route('nexus.module.action', ['module' => 'settings', 'action' => 'settings']), false);
});

it('redirects the settings module index to its edit screen instead of a CRUD list it has no model for', function () {
    $module = Module::findByName('Settings');

    $response = $this->get(route('nexus.module.action', ['module' => $module->name, 'action' => 'index']));

    $response->assertRedirect(route('nexus.module.action', ['module' => $module->name, 'action' => 'settings']));
});
