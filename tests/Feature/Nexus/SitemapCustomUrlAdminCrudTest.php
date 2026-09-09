<?php

use App\Models\User;
use App\Nexus\Modules\Sitemap\Models\SitemapCustomUrl;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleSettingsForm;
use Nodex\Nexus\Services\SettingsBuilder;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Sitemap'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Sitemap/action/create')->assertOk();
});

it('creates a custom sitemap URL through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Sitemap'])
        ->set('data.url', '/landing/promo')
        ->set('data.priority', 0.9)
        ->set('data.changefreq', 'weekly')
        ->set('data.is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $url = SitemapCustomUrl::where('url', '/landing/promo')->firstOrFail();
    expect($url->priority)->toBe(0.9);
    expect($url->changefreq)->toBe('weekly');
    expect($url->is_active)->toBeTrue();
});

it('defaults changefreq and priority when left blank', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Sitemap'])
        ->set('data.url', '/landing/default')
        ->call('save')
        ->assertHasNoErrors();

    $url = SitemapCustomUrl::where('url', '/landing/default')->firstOrFail();
    expect($url->changefreq)->toBe('monthly');
    expect((float) $url->priority)->toBe(0.5);
});

it('rejects an out-of-range priority', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Sitemap'])
        ->set('data.url', '/landing/bad-priority')
        ->set('data.priority', 1.5)
        ->call('save')
        ->assertHasErrors(['data.priority']);
});

it('excludes an inactive row from the active scope', function () {
    SitemapCustomUrl::create(['url' => '/active', 'is_active' => true]);
    SitemapCustomUrl::create(['url' => '/inactive', 'is_active' => false]);

    $urls = SitemapCustomUrl::active()->pluck('url')->all();

    expect($urls)->toBe(['/active']);
});

it('saves mode/base_url/split_size through the real admin settings form', function () {
    Livewire::test(ModuleSettingsForm::class, ['moduleName' => 'sitemap'])
        ->set('data.mode', 'single')
        ->set('data.base_url', 'https://cdn.example.com')
        ->set('data.split_size', 25000)
        ->call('save');

    expect(SettingsBuilder::get('sitemap', 'mode', null))->toBe('single');
    expect(SettingsBuilder::get('sitemap', 'base_url', null))->toBe('https://cdn.example.com');
    expect((int) SettingsBuilder::get('sitemap', 'split_size', null))->toBe(25000);
});

it('falls back to multi mode and no split before any setting is saved', function () {
    expect(SettingsBuilder::get('sitemap', 'mode', 'multi'))->toBe('multi');
    expect((int) SettingsBuilder::get('sitemap', 'split_size', 0))->toBe(0);
});
