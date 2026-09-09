<?php

use App\Models\User;
use App\Nexus\Modules\Tag\Models\Tag;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Tag'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Tag/action/create')->assertOk();
});

it('creates a tag through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Tag'])
        ->set('data.name', 'Laravel Tips')
        ->set('data.slug', 'laravel-tips')
        ->call('save')
        ->assertHasNoErrors();

    $tag = Tag::where('name', 'Laravel Tips')->firstOrFail();
    expect($tag->slug)->toBe('laravel-tips');
});

it('auto-generates the slug from the name when left blank, instead of crashing on a NOT NULL column', function () {
    // Regression: same class of bug reported against Menu — see
    // App\Nexus\Concerns\HasAutoSlug.
    Livewire::test(ModuleForm::class, ['moduleName' => 'Tag'])
        ->set('data.name', 'Weekly Roundup')
        ->call('save')
        ->assertHasNoErrors();

    $tag = Tag::where('name', 'Weekly Roundup')->firstOrFail();
    expect($tag->slug)->toBe('weekly-roundup');
});

it('rejects a duplicate slug', function () {
    Tag::create(['name' => 'News', 'slug' => 'news']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Tag'])
        ->set('data.name', 'Newsroom')
        ->set('data.slug', 'news')
        ->call('save')
        ->assertHasErrors(['data.slug' => 'unique']);
});
