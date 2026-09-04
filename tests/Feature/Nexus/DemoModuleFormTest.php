<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Enums\DemoStatus;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($this->admin);
});

it('renders every Demo field type without falling back to the unsupported banner', function () {
    $demo = Demo::create(['title' => 'Fixture Demo']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => (string) $demo->id])
        ->assertOk()
        ->assertDontSee('unsupported', false);
});

it('creates a record and attaches a belongsToMany relation on store', function () {
    $permission = App\Nexus\Modules\Permission\Models\Permission::create(['name' => 'fixture.permission', 'guard_name' => 'web']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo'])
        ->set('data.title', 'Created via test')
        ->set('data.permissionsPreload', [$permission->id])
        ->call('save')
        ->assertHasNoErrors();

    $demo = Demo::where('title', 'Created via test')->firstOrFail();
    expect($demo->permissionsPreload()->pluck('permissions.id')->all())->toBe([$permission->id]);
});

it('persists a native-dropdown (select-style) field pick through update', function () {
    $demo = Demo::create(['title' => 'Editable Demo', 'status' => 'draft']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => (string) $demo->id])
        ->set('data.status', 'published')
        ->call('save')
        ->assertHasNoErrors();

    $status = $demo->fresh()->status;
    expect($status instanceof DemoStatus ? $status->value : $status)
        ->toBe(DemoStatus::PUBLISHED->value);
});

it('shows a red form-level error instead of crashing when save() hits a real failure, with debug off', function () {
    config(['app.debug' => false]);
    $demo = Demo::create(['title' => 'Will be deleted mid-edit']);

    $component = Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => (string) $demo->id]);
    $demo->delete();

    $component->set('data.title', 'Updated title')->call('save');

    $component->assertHasErrors('form');
});

it('rethrows a real save() failure instead of masking it, with debug on', function () {
    config(['app.debug' => true]);
    $demo = Demo::create(['title' => 'Will be deleted mid-edit']);

    $component = Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => (string) $demo->id]);
    $demo->delete();

    expect(fn () => $component->set('data.title', 'Updated title')->call('save'))
        ->toThrow(ModelNotFoundException::class);
});
