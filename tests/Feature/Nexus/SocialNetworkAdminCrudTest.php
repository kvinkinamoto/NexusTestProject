<?php

use App\Models\User;
use App\Nexus\Modules\SocialNetwork\Enums\SocialPlatformEnum;
use App\Nexus\Modules\SocialNetwork\Models\SocialNetwork;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'SocialNetwork'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/SocialNetwork/action/create')->assertOk();
});

it('creates a social network through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'SocialNetwork'])
        ->set('data.title.'.config('app.locale'), 'Instagram')
        ->set('data.url', 'https://instagram.com/example')
        ->set('data.platform', SocialPlatformEnum::INSTAGRAM->value)
        ->set('data.sync_posts', true)
        ->call('save')
        ->assertHasNoErrors();

    $social = SocialNetwork::query()->firstOrFail();

    expect($social->url)->toBe('https://instagram.com/example');
    expect($social->platform)->toBe(SocialPlatformEnum::INSTAGRAM);
    expect($social->sync_posts)->toBeTrue();
});

it('nests a child entry under a parent through the relation field', function () {
    $parent = SocialNetwork::create(['url' => 'https://example.com', 'publish' => true]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'SocialNetwork'])
        ->set('data.url', 'https://facebook.com/example')
        ->set('data.parent', (string) $parent->id)
        ->call('save')
        ->assertHasNoErrors();

    $child = SocialNetwork::query()->where('url', 'https://facebook.com/example')->firstOrFail();

    expect($child->parent_id)->toBe($parent->id);
});

it('toggles publish through the boolToggle table action', function () {
    $social = SocialNetwork::create(['url' => 'https://example.com', 'publish' => false]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'SocialNetwork'])
        ->call('toggleBool', (string) $social->id, 'publish');

    expect($social->refresh()->publish)->toBeTrue();
});
