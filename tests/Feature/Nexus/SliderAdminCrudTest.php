<?php

use App\Models\User;
use App\Nexus\Modules\Slider\Models\Slider;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Slider'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Slider/action/create')->assertOk();
});

it('creates a slider group with its own slides through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Slider'])
        ->set('data.name', 'Homepage Hero')
        ->set('data.key', 'homepage')
        ->set('relationRows.slides', [
            ['image' => 'uploads/slide1.jpg', 'title' => 'Big Sale', 'subtitle' => null, 'link_url' => null, 'link_text' => null],
            ['image' => 'uploads/slide2.jpg', 'title' => 'New Arrivals', 'subtitle' => null, 'link_url' => null, 'link_text' => null],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $slider = Slider::where('key', 'homepage')->firstOrFail();
    $slides = $slider->slides()->orderBy('order')->get();

    expect($slides)->toHaveCount(2);
    expect($slides[0]->title)->toBe('Big Sale');
    expect($slides[1]->title)->toBe('New Arrivals');
});

it('allows two different sliders to exist independently, one active one not', function () {
    $homepage = Slider::create(['name' => 'Homepage', 'key' => 'homepage', 'is_active' => true]);
    $homepage->slides()->create(['image' => 'uploads/home.jpg', 'order' => 0]);

    $promo = Slider::create(['name' => 'Checkout Promo', 'key' => 'checkout-promo', 'is_active' => false]);
    $promo->slides()->create(['image' => 'uploads/promo.jpg', 'order' => 0]);

    expect(Slider::where('is_active', true)->count())->toBe(1);
    expect(Slider::where('is_active', false)->count())->toBe(1);
    expect($homepage->slides)->toHaveCount(1);
    expect($promo->slides)->toHaveCount(1);
});

it('auto-generates the key from the name when left blank, instead of crashing on a NOT NULL column', function () {
    // Regression: same class of bug reported against Menu — see
    // App\Nexus\Concerns\HasAutoSlug.
    Livewire::test(ModuleForm::class, ['moduleName' => 'Slider'])
        ->set('data.name', 'Checkout Promo')
        ->call('save')
        ->assertHasNoErrors();

    $slider = Slider::where('name', 'Checkout Promo')->firstOrFail();
    expect($slider->key)->toBe('checkout-promo');
});

it('rejects a duplicate key', function () {
    Slider::create(['name' => 'Homepage', 'key' => 'homepage', 'is_active' => true]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Slider'])
        ->set('data.name', 'Homepage Again')
        ->set('data.key', 'homepage')
        ->call('save')
        ->assertHasErrors(['data.key' => 'unique']);
});
