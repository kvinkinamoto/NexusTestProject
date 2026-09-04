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

it('round-trips every newly ported/added field type through a real save', function () {
    // One test covering all 13 field types added/ported to the Livewire
    // pipeline in this pass: time, location, multiple_string, radio, slug,
    // color, icon, file, markdown, range, currency, rating, url. Proves the
    // whole pipeline (wire:model binding, validation rules, casts, save)
    // for each, not just that dispatch.blade.php resolves a view for it
    // (see the "unsupported banner" test above for that).
    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo'])
        ->set('data.title', 'Field type round trip')
        ->set('data.event_time', '14:30')
        ->set('data.office_location', 'Kyiv HQ')
        ->set('data.tags', ['alpha', 'beta'])
        ->set('data.priority_radio', 'medium')
        ->set('data.slug', 'field-type-round-trip')
        ->set('data.theme_color', '#3366ff')
        ->set('data.nav_icon', 'solar:star-bold')
        ->set('data.attachment', 'uploads/spec.pdf')
        ->set('data.notes_markdown', '# Heading')
        ->set('data.volume', 42)
        ->set('data.price', 19.99)
        ->set('data.stars', 4)
        ->set('data.website', 'https://example.com')
        ->call('save')
        ->assertHasNoErrors();

    $demo = Demo::where('title', 'Field type round trip')->firstOrFail();

    expect($demo->event_time)->toContain('14:30')
        ->and($demo->office_location)->toBe('Kyiv HQ')
        ->and($demo->tags)->toBe(['alpha', 'beta'])
        ->and($demo->priority_radio)->toBe('medium')
        ->and($demo->slug)->toBe('field-type-round-trip')
        ->and($demo->theme_color)->toBe('#3366ff')
        ->and($demo->nav_icon)->toBe('solar:star-bold')
        ->and($demo->attachment)->toBe('uploads/spec.pdf')
        ->and($demo->notes_markdown)->toBe('# Heading')
        ->and($demo->volume)->toBe(42)
        ->and((float) $demo->price)->toBe(19.99)
        ->and($demo->stars)->toBe(4)
        ->and($demo->website)->toBe('https://example.com');
});

it('generates a slug from its configured source field via the slug field\'s "generate" button', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo'])
        ->set('data.title', 'A Brand New Title!')
        ->call('generateSlug', 'slug')
        ->assertSet('data.slug', 'a-brand-new-title');
});

it('translates wizard tab labels instead of showing the raw translation key', function () {
    // Regression test: wizard_progress.blade.php built the translation
    // namespace as $module->name . '::...' (e.g. "Demo::translate.basic")
    // while loadTranslation() registers every module's lang files under
    // Str::lcfirst($module['name']) (e.g. "demo") — the mismatched casing
    // meant Laravel's translator found no match and echoed the raw key
    // straight into the wizard's tab bar instead of "Basic".
    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo'])
        ->assertSee('Basic')
        ->assertDontSee('Demo::translate.basic', false);
});
