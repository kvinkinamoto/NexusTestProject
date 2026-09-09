<?php

use App\Models\User;
use App\Nexus\Modules\Cart\Models\Cart;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Cart'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('lists carts in the admin panel', function () {
    $shopper = User::factory()->create();
    Cart::create(['user_id' => $shopper->id]);

    $this->get('/admin/Cart/action/index')->assertOk();
});

it('deletes a cart', function () {
    $shopper = User::factory()->create();
    $cart = Cart::create(['user_id' => $shopper->id]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Cart'])
        ->call('runAction', 'delete', (string) $cart->id);

    expect(Cart::find($cart->id))->toBeNull();
});
