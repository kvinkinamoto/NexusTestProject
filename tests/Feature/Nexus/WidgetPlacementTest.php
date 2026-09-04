<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use App\Nexus\Modules\WidgetPlacement\Models\WidgetPlacement;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Services\Widgets\FrontWidgetRenderer;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the front-placement system: WidgetInstance/
 * WidgetAssignment (real schema, real models) had no write path anywhere —
 * App\Nexus\Modules\WidgetPlacement (extends WidgetInstance, adds #[Field]s)
 * is that write path, dogfooding the package's own "any Eloquent model +
 * #[Module] = full CRUD admin" system instead of hand-building a bespoke
 * one. These tests prove the whole pipeline end to end: admin creates a
 * placement -> FrontWidgetRenderer finds it by position -> @position() (see
 * resources/views/welcome.blade.php) actually renders it on a real page.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();
    $this->artisan('nexus:module:install', ['name' => 'WidgetPlacement'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('creates a widget placement with a position assignment through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'WidgetPlacement'])
        ->set('data.name', 'Home top recent records')
        ->set('data.widget_key', 'recentDemoRecords')
        ->set('data.is_enabled', true)
        ->set('relationRows.assignments', [
            ['position' => 'home-top', 'target_template' => null, 'is_active' => true, 'sort_order' => 0],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $placement = WidgetPlacement::where('name', 'Home top recent records')->firstOrFail();
    expect($placement->widget_key)->toBe('recentDemoRecords')
        ->and($placement->assignments()->where('position', 'home-top')->exists())->toBeTrue();
});

it('renders a saved placement through FrontWidgetRenderer by position', function () {
    Demo::create(['title' => 'Front-placed record']);

    $placement = WidgetPlacement::create([
        'name' => 'Front render check',
        'widget_key' => 'recentDemoRecords',
        'is_enabled' => true,
    ]);
    $placement->assignments()->create(['position' => 'home-top', 'is_active' => true]);

    $html = app(FrontWidgetRenderer::class)->render('home-top');

    expect($html)->toContain('Front-placed record');
});

it('does not render a disabled placement even if its assignment is active', function () {
    $placement = WidgetPlacement::create([
        'name' => 'Disabled placement',
        'widget_key' => 'recentDemoRecords',
        'is_enabled' => false,
    ]);
    $placement->assignments()->create(['position' => 'home-top-disabled-check', 'is_active' => true]);

    $html = app(FrontWidgetRenderer::class)->render('home-top-disabled-check');

    expect($html)->toBe('');
});

it('shows the placed widget on the real front-end welcome page via @position(\'home-top\')', function () {
    Demo::create(['title' => 'Visible on welcome page']);

    $placement = WidgetPlacement::create([
        'name' => 'Welcome page placement',
        'widget_key' => 'recentDemoRecords',
        'is_enabled' => true,
    ]);
    $placement->assignments()->create(['position' => 'home-top', 'is_active' => true]);

    $this->actingAs($this->admin);
    $response = $this->get('/');

    $response->assertOk()->assertSee('Visible on welcome page');
});
