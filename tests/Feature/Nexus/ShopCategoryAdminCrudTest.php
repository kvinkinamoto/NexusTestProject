<?php

use App\Models\User;
use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopCategory'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/ShopCategory/action/create')->assertOk();
});

it('creates a category through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopCategory'])
        ->set('data.name.'.config('app.locale'), 'Electronics')
        ->set('data.publish', true)
        ->call('save')
        ->assertHasNoErrors();

    $category = ShopCategory::where('slug', 'electronics')->firstOrFail();
    expect($category->slug)->toBe('electronics');
});

it('nests a category under a parent', function () {
    $parent = ShopCategory::create(['name' => 'Electronics', 'slug' => 'electronics']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopCategory'])
        ->set('data.name.'.config('app.locale'), 'Phones')
        ->set('data.parent', (string) $parent->id)
        ->call('save')
        ->assertHasNoErrors();

    $child = ShopCategory::where('slug', 'phones')->firstOrFail();
    expect($child->parent_id)->toBe($parent->id);
    expect($parent->fresh()->children)->toHaveCount(1);
});

it('rejects a duplicate slug', function () {
    ShopCategory::create(['name' => 'Electronics', 'slug' => 'electronics']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopCategory'])
        ->set('data.name.'.config('app.locale'), 'Electronics again')
        ->set('data.slug', 'electronics')
        ->call('save')
        ->assertHasErrors(['data.slug' => 'unique']);
});

it('toggles publish through the boolToggle table action', function () {
    $category = ShopCategory::create(['name' => 'Electronics', 'slug' => 'electronics', 'publish' => true]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ShopCategory'])
        ->call('toggleBool', (string) $category->id, 'publish');

    expect($category->refresh()->publish)->toBeFalse();
});
