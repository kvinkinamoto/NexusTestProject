<?php

use App\Models\User;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\Wishlist\Models\WishlistItem;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Wishlist'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    $this->product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Wishlist/action/create')->assertOk();
});

it('creates a wishlist item through the generic admin CRUD', function () {
    $user = User::factory()->create();

    Livewire::test(ModuleForm::class, ['moduleName' => 'Wishlist'])
        ->set('data.user', (string) $user->id)
        ->set('data.product', (string) $this->product->id)
        ->call('save')
        ->assertHasNoErrors();

    $item = WishlistItem::query()->firstOrFail();

    expect($item->user_id)->toBe($user->id);
    expect($item->product_id)->toBe($this->product->id);
});

it('rejects a duplicate user+product pair', function () {
    $user = User::factory()->create();
    WishlistItem::create(['user_id' => $user->id, 'product_id' => $this->product->id]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Wishlist'])
        ->set('data.user', (string) $user->id)
        ->set('data.product', (string) $this->product->id)
        ->call('save')
        ->assertHasErrors(['relation.product']);
});
