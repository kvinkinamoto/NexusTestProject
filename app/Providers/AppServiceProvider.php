<?php

namespace App\Providers;

use App\Nexus\Modules\Menu\Livewire\MenuItemsManager;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nodex\Nexus\Services\HookManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Deferred to the app's "booting" callback (fires after every
        // provider's register() has run, right before any provider's boot()
        // does) rather than called directly here or from boot(): HookManager
        // is bound as a singleton in NexusServiceProvider::register(), whose
        // order relative to this provider's register() isn't guaranteed, and
        // NexusServiceProvider::boot() itself eagerly reads (and memoizes)
        // ModuleRegistry::getAllModules() while running in console — so the
        // filter must exist before ANY provider's boot() runs.
        $this->app->booting(function () {
            $this->hideConfirmFixtureOutsideTesting();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // App-level module Livewire components don't live under Livewire's
        // configured `class_namespace` (App\Livewire), so tag auto-discovery
        // never finds them — registered here rather than in the package's
        // NexusServiceProvider, which must not depend on an app-level
        // module's classes (same layering rule as the Slider/Menu public
        // Blade partials not living under the package's `x-nexus::` namespace).
        Livewire::component('nexus-menu-items-manager', MenuItemsManager::class);
    }

    /**
     * App\Nexus\Modules\ConfirmFixture is a permanent test fixture (see its
     * docblock and tests/Feature/Nexus/TableActionConfirmTest.php) — it has
     * no admin menu entry, but ModuleRegistry discovers it the same as any
     * real module by scanning app/Nexus/Modules, so `nexus:module:install`
     * (with no name, as run by `nexus:install`) would otherwise install it
     * on every environment, including production. Stripping it from
     * discovery outside testing keeps it installable only via the test's
     * own `nexus:module:install ConfirmFixture` call.
     */
    private function hideConfirmFixtureOutsideTesting(): void
    {
        if ($this->app->environment('testing')) {
            return;
        }

        $this->app->make(HookManager::class)->addFilter('nexus.module.discovery', function ($modules) {
            $modules->forget('confirmfixture');

            return $modules;
        });
    }
}
