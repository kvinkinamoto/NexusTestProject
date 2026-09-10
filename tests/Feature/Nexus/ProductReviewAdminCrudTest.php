<?php

use App\Models\User;
use App\Nexus\Modules\ProductReview\Models\Review;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Bonus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ProductReview'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    $this->product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
});

it('mounts the create form without error', function () {
    $this->get('/admin/ProductReview/action/create')->assertOk();
});

it('creates a review through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'ProductReview'])
        ->set('data.product', (string) $this->product->id)
        ->set('data.user_name', 'Bob')
        ->set('data.rating', 5)
        ->set('data.comment', 'Great shirt')
        ->call('save')
        ->assertHasNoErrors();

    $review = Review::query()->firstOrFail();

    expect($review->rating)->toBe(5);
    expect($review->comment)->toBe('Great shirt');
});

it('rejects a rating outside 1-5', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'ProductReview'])
        ->set('data.product', (string) $this->product->id)
        ->set('data.user_name', 'Bob')
        ->set('data.rating', 7)
        ->call('save')
        ->assertHasErrors(['data.rating']);
});

it('toggles publish through the boolToggle table action', function () {
    $review = Review::create(['product_id' => $this->product->id, 'user_name' => 'Bob', 'rating' => 4, 'publish' => false]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ProductReview'])
        ->call('toggleBool', (string) $review->id, 'publish');

    expect($review->refresh()->publish)->toBeTrue();
});
