<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use App\Nexus\Widgets\GeneratorRegressionCheck\GeneratorRegressionCheck;
use App\Nexus\Widgets\RecentDemoRecords\RecentDemoRecords;
use App\Nexus\Widgets\UsersCount\UsersCount;
use Illuminate\Support\Facades\File;
use Nodex\Nexus\Attributes\Widget;
use Nodex\Nexus\Contracts\Widgets\RendersHtml;
use Nodex\Nexus\Contracts\Widgets\WidgetInterface;
use Nodex\Nexus\Dto\Widgets\WidgetContext;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Enums\WidgetSurface;
use Nodex\Nexus\Services\ModuleManifestCache;
use Nodex\Nexus\Services\Widgets\AdminDashboardRenderer;
use Nodex\Nexus\Services\Widgets\DashboardLayoutResolver;
use Nodex\Nexus\Services\Widgets\WidgetRegistry;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the widget system fix: zero test coverage existed
 * anywhere for WidgetRegistry, DashboardLayoutResolver, AdminDashboardRenderer,
 * the dashboard Customize UI's save endpoint, or the /api/widgets surface —
 * see this session's widget-system audit for the full "what was
 * scaffolded-but-inert" picture this closes.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
});

it('discovers both app-global widgets through the live registry', function () {
    // NexusServiceProvider::loadWidgets() scans app_path('Nexus/Widgets')
    // unconditionally, so usersCount/recentDemoRecords (both there) are
    // always in the registry regardless of any module's enabled state.
    $registry = app(WidgetRegistry::class);

    expect($registry->find('usersCount'))->not->toBeNull()
        ->and($registry->find('recentDemoRecords'))->not->toBeNull();
});

it('discovers a module-scoped widget (demoRecordsCount) via ModuleManifestCache::discoverWidgets()', function () {
    // Unlike the app-global pair above, a module's own Widgets/ folder is
    // only scanned by loadWidgets() for modules already enabled at the
    // moment the app boots (Illuminate\Foundation\Testing\TestCase boots a
    // fresh app before beforeEach() runs nexus:module:install) — so this
    // exercises the real discovery mechanism directly instead of the
    // boot-time registry, which can't see it within a single test's
    // lifecycle no matter when install() runs. Confirmed working the
    // normal way (registry has it after a real page load) via tinker
    // against the actual dev database, outside this test suite.
    $entries = app(ModuleManifestCache::class)
        ->discoverWidgets(app_path('Nexus/Modules/Demo/Widgets'), 'App\\Nexus\\Modules\\Demo');

    expect(collect($entries)->pluck('name'))->toContain('demoRecordsCount');
});

it('renders both a ProvidesMetric-only and a RendersHtml widget through AdminDashboardRenderer', function () {
    Demo::create(['title' => 'Widget-visible record']);

    $context = new WidgetContext(surface: WidgetSurface::Admin, user: $this->admin);
    $cards = app(AdminDashboardRenderer::class)->render(['usersCount', 'recentDemoRecords'], $context);

    expect($cards)->toHaveCount(2);
    $html = collect($cards)->pluck('html')->implode('');
    expect($html)->toContain('Total Users')
        ->toContain('Widget-visible record');
});

it('persists a dashboard layout through the Customize UI\'s save endpoint', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson(route('nexus.preferences.dashboard-layout'), [
        'widgets' => ['recentDemoRecords', 'usersCount'],
    ]);

    $response->assertOk()->assertJson(['success' => true]);
    expect(app(DashboardLayoutResolver::class)->resolveFor($this->admin))->toBe(['recentDemoRecords', 'usersCount']);
});

it('exposes ApiSerializable\'s own payload shape, distinct from getData()', function () {
    $widget = new UsersCount;
    $context = new WidgetContext(surface: WidgetSurface::Api);

    expect($widget->toApiPayload([], $context))->toHaveKey('totalUsers')
        ->and($widget->getData([], $context))->toHaveKey('count')
        ->not->toHaveKey('totalUsers');
});

it('hides a non-public widget\'s existence from an anonymous /api/widgets/{key} request', function () {
    $response = $this->getJson(route('api.nexus.widgets.show', ['key' => 'usersCount']));

    $response->assertNotFound();
});

it('serves usersCount through /api/widgets/{key} for an authenticated request', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson(route('api.nexus.widgets.show', ['key' => 'usersCount']));

    $response->assertOk()->assertJsonPath('data.data.totalUsers', User::count());
});

it('lists Api-surface widgets through /api/widgets for an authenticated request', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson(route('api.nexus.widgets.index'));

    $response->assertOk();
    expect(collect($response->json('data'))->pluck('key'))->toContain('usersCount');
});

it('produces real HTML output for the RecentDemoRecords widget, not an empty string', function () {
    Demo::create(['title' => 'Render-check record']);

    $html = (new RecentDemoRecords)->render([], new WidgetContext(surface: WidgetSurface::Admin));

    expect($html)->toContain('Render-check record');
});

it('renders the correct widget key (not a groupBy-renumbered index) on a not-yet-added checkbox in the Customize drawer', function () {
    // Regression test for a real bug caught live in the browser:
    // Collection::groupBy() resets each group's keys to 0, 1, 2... unless
    // its $preserveKeys argument is passed true — dashboard.blade.php's
    // "not yet added" section groups $availableWidgets by #[Widget(group:)]
    // and iterates the group with `$key` used as both data-widget-key and
    // the checkbox's value="{{ $key }}"; without preserveKeys, checking
    // that box saved the literal string "0" into nexus_dashboard_layouts
    // instead of the real widget key.
    $this->actingAs($this->admin);

    $response = $this->get(route('nexus.admin'));

    $response->assertOk()
        ->assertSee('value="recentDemoRecords"', false)
        ->assertDontSee('data-widget-key="0"', false);
});

it('nexus:make:widget generates a class that actually implements WidgetInterface', function () {
    $classFile = app_path('Nexus/Widgets/GeneratorRegressionCheck/GeneratorRegressionCheck.php');
    $viewFile = resource_path('views/widgets/generatorRegressionCheck.blade.php');

    if (File::exists(dirname($classFile))) {
        File::deleteDirectory(dirname($classFile));
    }
    if (File::exists($viewFile)) {
        File::delete($viewFile);
    }

    try {
        $this->artisan('nexus:make:widget', ['name' => 'generatorRegressionCheck'])->assertSuccessful();

        expect(File::exists($classFile))->toBeTrue()
            ->and(File::exists($viewFile))->toBeTrue();

        require $classFile;
        $class = GeneratorRegressionCheck::class;

        expect(is_subclass_of($class, WidgetInterface::class))->toBeTrue()
            ->and(is_subclass_of($class, RendersHtml::class))->toBeTrue();

        $reflection = new ReflectionClass($class);
        expect($reflection->getAttributes(Widget::class))->not->toBeEmpty();
    } finally {
        File::deleteDirectory(dirname($classFile));
        File::delete($viewFile);
    }
});
