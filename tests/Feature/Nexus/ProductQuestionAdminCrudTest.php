<?php

use App\Models\User;
use App\Nexus\Modules\ProductQuestion\Models\ProductQuestion;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ProductQuestion'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    $this->product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
});

it('mounts the create form without error', function () {
    $this->get('/admin/ProductQuestion/action/create')->assertOk();
});

it('creates a question through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'ProductQuestion'])
        ->set('data.product', (string) $this->product->id)
        ->set('data.text', 'Does this come in blue?')
        ->call('save')
        ->assertHasNoErrors();

    $question = ProductQuestion::query()->firstOrFail();

    expect($question->product_id)->toBe($this->product->id);
    expect($question->text)->toBe('Does this come in blue?');
});

it('creates a reply as a child row of the same table', function () {
    $root = ProductQuestion::create(['product_id' => $this->product->id, 'text' => 'Does this come in blue?', 'publish' => true]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'ProductQuestion'])
        ->set('data.product', (string) $this->product->id)
        ->set('data.parent', (string) $root->id)
        ->set('data.text', 'Yes, in navy blue.')
        ->call('save')
        ->assertHasNoErrors();

    $reply = ProductQuestion::query()->where('parent_id', $root->id)->firstOrFail();

    expect($reply->text)->toBe('Yes, in navy blue.');
});

it('toggles publish through the boolToggle table action', function () {
    $question = ProductQuestion::create(['product_id' => $this->product->id, 'text' => 'Q', 'publish' => false]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ProductQuestion'])
        ->call('toggleBool', (string) $question->id, 'publish');

    expect($question->refresh()->publish)->toBeTrue();
});
