<?php

use App\Models\User;
use App\Nexus\Modules\Language\Models\Language;
use Illuminate\Support\Facades\File;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Language'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Backup'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Translations'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    Language::create(['language' => 'English', 'code' => 'en', 'publish' => true]);
});

afterEach(function () {
    File::deleteDirectory(lang_path('vendor/zzzTestFixture'));
});

it('lists translation groups on the index route', function () {
    $this->get('/admin/Translations/action/index')
        ->assertOk()
        ->assertSee('Backup::translate');
});

it('browses keys for one group on the phrases route', function () {
    $this->get('/admin/Translations/action/phrases/backup::translate')
        ->assertOk()
        ->assertSee('docs.title');
});

it('opens the edit form for one key', function () {
    $this->get('/admin/Translations/action/edit/backup::translate?key=docs.title')
        ->assertOk()
        ->assertSee('docs.title');
});

it('saves a key to disk through the update action', function () {
    $this->post('/admin/Translations/action/update/zzzTestFixture::translate', [
        'key' => 'greeting',
        'translations' => ['en' => 'Hello from the admin form'],
    ])->assertRedirect();

    $data = File::getRequire(lang_path('vendor/zzzTestFixture/en/translate.php'));
    expect($data['greeting'])->toBe('Hello from the admin form');
});
