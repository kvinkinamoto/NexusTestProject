<?php

use App\Models\User;
use App\Nexus\Modules\Language\Http\Middleware\SetAdminLocale;
use App\Nexus\Modules\Language\Models\Language;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Language'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('lists languages on the index route', function () {
    Language::create(['language' => 'English', 'code' => 'en']);

    $this->get('/admin/Language/action/index')->assertOk();
});

it('creates a language through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Language'])
        ->set('data.language', 'Ukrainian')
        ->set('data.code', 'uk')
        ->set('data.publish', true)
        ->call('save')
        ->assertHasNoErrors();

    $language = Language::where('code', 'uk')->firstOrFail();
    expect($language->language)->toBe('Ukrainian');
    expect($language->publish)->toBeTrue();
});

it('rejects a duplicate locale code', function () {
    Language::create(['language' => 'English', 'code' => 'en']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Language'])
        ->set('data.language', 'English (US)')
        ->set('data.code', 'en')
        ->call('save')
        ->assertHasErrors(['data.code']);
});

it('sets the admin session locale via the switchAdminLocale action', function () {
    $language = Language::create(['language' => 'Ukrainian', 'code' => 'uk', 'publish' => true]);

    $this->post("/admin/Language/action/switchAdminLocale/{$language->id}")->assertRedirect();

    expect(session('admin_locale'))->toBe('uk');
});

it('refuses to switch to an unpublished language', function () {
    $language = Language::create(['language' => 'Draft', 'code' => 'xx', 'publish' => false]);

    $this->post("/admin/Language/action/switchAdminLocale/{$language->id}")->assertNotFound();

    expect(session('admin_locale'))->toBeNull();
});

it('applies the session admin_locale via SetAdminLocale middleware', function () {
    Language::create(['language' => 'Ukrainian', 'code' => 'uk', 'publish' => true]);
    session(['admin_locale' => 'uk']);

    (new SetAdminLocale)->handle(request(), fn ($req) => response('ok'));

    expect(App::getLocale())->toBe('uk');
});

it('falls back to the admin default locale when the session code is not a published language', function () {
    session(['admin_locale' => 'does-not-exist']);

    (new SetAdminLocale)->handle(request(), fn ($req) => response('ok'));

    expect(App::getLocale())->toBe('en');
});
