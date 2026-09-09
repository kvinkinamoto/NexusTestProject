<?php

use App\Models\User;
use App\Nexus\Modules\Redirect\Models\Redirect;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Redirect'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Redirect/action/create')->assertOk();
});

it('creates a redirect through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Redirect'])
        ->set('data.from_path', 'old-page')
        ->set('data.to_path', '/new-page')
        ->set('data.status_code', 301)
        ->set('data.is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $redirect = Redirect::where('from_path', 'old-page')->firstOrFail();
    expect($redirect->to_path)->toBe('/new-page');
    expect($redirect->status_code)->toBe(301);
    expect($redirect->is_active)->toBeTrue();
});

it('strips leading and trailing slashes from the submitted from_path', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Redirect'])
        ->set('data.from_path', '/old-page/')
        ->set('data.to_path', '/new-page')
        ->call('save')
        ->assertHasNoErrors();

    expect(Redirect::where('from_path', 'old-page')->exists())->toBeTrue();
});

it('adds a leading slash to a bare to_path so it resolves as a site-relative path', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Redirect'])
        ->set('data.from_path', 'old-page')
        ->set('data.to_path', 'new-page')
        ->call('save')
        ->assertHasNoErrors();

    $redirect = Redirect::where('from_path', 'old-page')->firstOrFail();
    expect($redirect->to_path)->toBe('/new-page');
});

it('leaves an absolute URL to_path untouched', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Redirect'])
        ->set('data.from_path', 'old-page')
        ->set('data.to_path', 'https://example.com/new-page')
        ->call('save')
        ->assertHasNoErrors();

    $redirect = Redirect::where('from_path', 'old-page')->firstOrFail();
    expect($redirect->to_path)->toBe('https://example.com/new-page');
});

it('rejects a duplicate from_path', function () {
    Redirect::create(['from_path' => 'old-page', 'to_path' => '/new-page']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Redirect'])
        ->set('data.from_path', 'old-page')
        ->set('data.to_path', '/somewhere-else')
        ->call('save')
        ->assertHasErrors(['data.from_path' => 'unique']);
});
