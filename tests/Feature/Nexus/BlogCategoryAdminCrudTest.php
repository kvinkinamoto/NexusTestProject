<?php

use App\Models\User;
use App\Nexus\Modules\BlogCategory\Models\BlogCategory;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'BlogCategory'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/BlogCategory/action/create')->assertOk();
});

it('creates a blog category through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'BlogCategory'])
        ->set('data.name', 'Announcements')
        ->set('data.slug', 'announcements')
        ->set('data.description', 'Product news and updates.')
        ->call('save')
        ->assertHasNoErrors();

    $category = BlogCategory::where('slug', 'announcements')->firstOrFail();
    expect($category->name)->toBe('Announcements');
    expect($category->description)->toBe('Product news and updates.');
});

it('rejects a duplicate slug', function () {
    BlogCategory::create(['name' => 'News', 'slug' => 'news']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'BlogCategory'])
        ->set('data.name', 'Newsroom')
        ->set('data.slug', 'news')
        ->call('save')
        ->assertHasErrors(['data.slug' => 'unique']);
});
