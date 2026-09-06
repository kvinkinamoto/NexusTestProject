<?php

use App\Models\User;
use App\Nexus\Modules\Settings\Models\Settings;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

/**
 * Site-wide settings is a singleton by convention: exactly one `settings`
 * row ever exists, reached from the admin topbar dropdown (previously a
 * dead `href="#"`) rather than from a list in the sidebar. These tests
 * exercise the guards that keep it a singleton and the real Livewire edit
 * path the topbar link now points at, the same way
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

it('lazily creates exactly one settings row on first access, with no manual step', function () {
    expect(Settings::count())->toBe(0);

    Settings::current();

    expect(Settings::count())->toBe(1);
});

it('blocks creating a second settings row', function () {
    Settings::current();

    $second = Settings::create(['site_name' => 'Second']);

    expect($second->id)->toBeNull()
        ->and(Settings::count())->toBe(1);
});

it('blocks deleting the settings row', function () {
    $settings = Settings::current();

    $deleted = $settings->delete();

    expect($deleted)->toBeFalse()
        ->and(Settings::find($settings->id))->not->toBeNull();
});

it('saves changes through the real admin edit form', function () {
    $settings = Settings::current();

    Livewire::test(ModuleForm::class, ['moduleName' => 'settings', 'id' => (string) $settings->id])
        ->set('data.site_name', 'Nexus CMS')
        ->set('data.site_description', 'A modular Laravel CMS')
        ->set('data.maintenance_mode', true)
        ->call('save');

    $settings->refresh();

    expect($settings->site_name)->toBe('Nexus CMS')
        ->and($settings->site_description)->toBe('A modular Laravel CMS')
        ->and($settings->maintenance_mode)->toBeTrue();
});

it('points the admin topbar Settings link at the settings edit page, not a dead link', function () {
    $settings = Settings::current();

    $response = $this->get(route('nexus.admin'));

    $response->assertOk();
    $response->assertSee(route('nexus.module.action', ['module' => 'settings', 'action' => 'edit', 'id' => $settings->id]), false);
});
