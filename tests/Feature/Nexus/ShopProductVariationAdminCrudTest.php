<?php

use App\Models\User;
use App\Nexus\Modules\ShopAttribute\Models\ShopAttribute;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\ShopProductVariation\Models\VariationGroup;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopAttribute'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProductVariation'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/ShopProductVariation/action/create')->assertOk();
});

it('creates a variation group with its distinguishing attributes', function () {
    $color = ShopAttribute::create(['name' => 'Color', 'type' => 'color']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopProductVariation'])
        ->set('data.variationAttributes', [$color->id])
        ->call('save')
        ->assertHasNoErrors();

    $group = VariationGroup::query()->firstOrFail();
    expect($group->variationAttributes()->pluck('name')->all())->toBe(['Color']);
});

it('requires at least one distinguishing attribute', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopProductVariation'])
        ->call('save')
        ->assertHasErrors(['data.variationAttributes' => 'required']);
});

it('assigns a product to a variation group from the product side', function () {
    $group = VariationGroup::create([]);
    $red = ShopProduct::create(['name' => 'T-Shirt Red', 'slug' => 'tshirt-red', 'price' => 20]);
    $blue = ShopProduct::create(['name' => 'T-Shirt Blue', 'slug' => 'tshirt-blue', 'price' => 20]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopProduct', 'id' => $red->id])
        ->set('data.variationGroup', (string) $group->id)
        ->call('save')
        ->assertHasNoErrors();

    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopProduct', 'id' => $blue->id])
        ->set('data.variationGroup', (string) $group->id)
        ->call('save')
        ->assertHasNoErrors();

    expect($group->fresh()->products()->pluck('name')->all())->toBe(['T-Shirt Red', 'T-Shirt Blue']);
});
