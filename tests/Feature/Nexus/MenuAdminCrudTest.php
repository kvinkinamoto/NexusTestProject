<?php

use App\Models\User;
use App\Nexus\Modules\Menu\Models\Menu;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Menu'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Menu/action/create')->assertOk();
});

it('creates a menu container through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Menu'])
        ->set('data.name', 'Header')
        ->set('data.key', 'header')
        ->call('save')
        ->assertHasNoErrors();

    $menu = Menu::where('key', 'header')->firstOrFail();
    expect($menu->is_active)->toBeTrue();
});

it('auto-generates the key from the name when left blank, instead of crashing on a NOT NULL column', function () {
    // Regression: reported by the user against the real admin UI — the key
    // column is NOT NULL with no DB default, and the "Generate" button is
    // opt-in, so saving with the field untouched used to raw-SQL-crash
    // (SQLSTATE[23000]) instead of silently deriving a value. See
    // App\Nexus\Concerns\HasAutoSlug.
    Livewire::test(ModuleForm::class, ['moduleName' => 'Menu'])
        ->set('data.name', 'Footer Links')
        ->call('save')
        ->assertHasNoErrors();

    $menu = Menu::where('name', 'Footer Links')->firstOrFail();
    expect($menu->key)->toBe('footer-links');
});

it('rejects a duplicate key', function () {
    Menu::create(['name' => 'Header', 'key' => 'header', 'is_active' => true]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Menu'])
        ->set('data.name', 'Header Again')
        ->set('data.key', 'header')
        ->call('save')
        ->assertHasErrors(['data.key' => 'unique']);
});
