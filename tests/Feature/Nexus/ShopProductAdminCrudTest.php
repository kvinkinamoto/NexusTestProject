<?php

use App\Models\User;
use App\Nexus\Modules\ShopAttribute\Models\AttributeOption;
use App\Nexus\Modules\ShopAttribute\Models\ShopAttribute;
use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopCategory'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopAttribute'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/ShopProduct/action/create')->assertOk();
});

it('creates a product through the generic admin CRUD', function () {
    $category = ShopCategory::create(['name' => 'Electronics', 'slug' => 'electronics']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopProduct'])
        ->set('data.name', 'iPhone')
        ->set('data.price', 999)
        ->set('data.stock', 10)
        ->set('data.publish', true)
        ->set('data.category', (string) $category->id)
        ->call('save')
        ->assertHasNoErrors();

    $product = ShopProduct::where('name', 'iPhone')->firstOrFail();

    expect($product->slug)->toBe('iphone');
    expect($product->category_id)->toBe($category->id);
    expect((float) $product->price)->toBe(999.0);
});

it('rejects a discount price that is not lower than the regular price', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopProduct'])
        ->set('data.name', 'iPhone')
        ->set('data.price', 100)
        ->set('data.price_discount', 150)
        ->call('save')
        ->assertHasErrors(['data.price_discount']);
});

it('attaches attribute option values to a product', function () {
    $attribute = ShopAttribute::create(['name' => 'Color', 'type' => 'color']);
    $red = AttributeOption::create(['attribute_id' => $attribute->id, 'value' => 'Red']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopProduct'])
        ->set('data.name', 'T-Shirt')
        ->set('data.price', 20)
        ->set('data.attributeOptions', [$red->id])
        ->call('save')
        ->assertHasNoErrors();

    $product = ShopProduct::where('name', 'T-Shirt')->firstOrFail();
    expect($product->attributeOptions()->pluck('value')->all())->toBe(['Red']);
});

it('computes the effective price from the discount price when it is lower', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'price_discount' => 799]);

    expect($product->effectivePrice())->toBe(799.0);
});

it('falls back to the regular price when no discount price is set', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999]);

    expect($product->effectivePrice())->toBe(999.0);
});

it('toggles publish through the boolToggle table action', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'publish' => true]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ShopProduct'])
        ->call('toggleBool', (string) $product->id, 'publish');

    expect($product->refresh()->publish)->toBeFalse();
});
