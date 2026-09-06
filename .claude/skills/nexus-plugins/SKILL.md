---
name: nexus-plugins
description: "Use when extending Nexus (packages/nodex/nexus) behavior without owning a database table: mutating another module's config at runtime, adding validation rules to an existing module, registering a custom field type globally, hooking dashboard/widget output, or running boot-time code (routes, block types, container wiring). Covers app/Nexus/Plugins/** — the #[TargetModule]/#[Filter]/#[Action] attribute system, HookManager, nexus_filter()/nexus_action() helpers, and plugin discovery. Do not use for a new CRUD content type (see nexus-modules) or a dashboard card (see nexus-widgets)."
metadata:
  scope: project
---

# Nexus Plugins

A Nexus plugin is a class under `app/Nexus/Plugins/{Name}/{Name}Plugin.php`, auto-discovered every request — no manual registration. Use it for cross-cutting extension, not for anything that needs its own table (that's a [module](../nexus-modules/SKILL.md)) or a dashboard card (that's a [widget](../nexus-widgets/SKILL.md)).

## Scaffold

```bash
php artisan nexus:make:plugin {Name} --module={Module}
```
`--module` defaults to `User` and sets `#[TargetModule]`. Output:

```php
#[TargetModule('{Module}')]
class {Name}Plugin
{
    public function register(): void {}
    public function boot(): void {}
    public function handle(object $configuration): void {}

    #[Filter(hook: 'nexus.validation.rules', priority: 10)]
    public function extendValidationRules(array $rules, object $moduleConfig, string $action): array
    {
        return $rules;
    }

    #[Action(hook: 'nexus.field_types.register', priority: 10)]
    public function registerFieldTypes(FieldTypeRegistry $registry): void {}
}
```
Delete whichever of `handle()`/`register()`/`boot()`/hook methods you don't need.

## The critical gotcha: `#[TargetModule]` is a required anchor, even for hook-only plugins

`PluginManager` does two independent things per discovered class: (1) if it has `#[TargetModule]`, index it for `handle()`/`register()`/`boot()`; (2) regardless, reflect its public methods for `#[Filter]`/`#[Action]` and wire them into `HookManager`.

**But `registerAll()`/`bootAll()` only iterate classes indexed by `#[TargetModule]`.** A plugin with only `#[Filter]`/`#[Action]` attributes and no `#[TargetModule]` gets its hooks wired, but its `register()`/`boot()` are **never called**. If your plugin's real purpose is route registration or boot-time wiring (like `GraphQLPlugin`, `BlockTypesPlugin`) and it has no module config to mutate, still keep `#[TargetModule('User')]` as a required anchor — that's exactly why those two shipped plugins carry it.

`handle(object $configuration)` receives a **fresh deep copy** of the target module's schema DTO on every call — mutate it freely, it never leaks between requests or modules.

## `#[Filter]` vs `#[Action]`

```php
#[Filter(hook: string $hook, int $priority = 10)]   // must return the (possibly transformed) value
#[Action(hook: string $hook, int $priority = 10)]   // side effect only, no return value used
```
Both are repeatable on one method for multiple hooks. Lower `priority` runs first (WordPress convention, default 10). Methods must be **public** — `discoverClass()` only reflects `IS_PUBLIC` methods.

Call sites use the global helpers:
```php
nexus_filter(string $hook, mixed $value, mixed ...$args): mixed   // returns the filtered value
nexus_action(string $hook, mixed ...$args): void
```
`[ClassName, 'method']` callbacks are resolved through the container **lazily** — a plugin class is never instantiated unless one of its hooks actually fires.

## Known hooks in this codebase

| Hook | Kind | Fired from |
| --- | --- | --- |
| `nexus.validation.rules` | filter | Building a module's validation rules — `($rules, $moduleConfig, $action)` |
| `nexus.field_types.register` | action | Boot-time field type registration — `(FieldTypeRegistry $registry)` |
| `nexus.module.installed` / `nexus.module.uninstalled` | action | `ModuleManager::instanceInstall()`/`uninstall()` — `(string $moduleName)` |
| `nexus.module.discovery` | filter | `ModuleRegistry::getAllModules()` — can inject or hide a module entry |
| `nexus.dashboard.layout` | filter | `DashboardLayoutResolver::resolveFor()` — `(array $layout, ?Authenticatable $user)` |
| `widget.data.{widgetKey}` / `widget.html.{widgetKey}` | filter | `WidgetOutputCache::remember()` — applied **before** caching, so the filtered value is what gets cached |

When scoping a filter/action to one module, check the payload inside the method (e.g. `if ($moduleConfig->name !== 'demo') return $rules;`) — the hook itself isn't module-scoped, every registered listener for that hook fires for every module.

## Discovery and disabling

`autoDiscover()` scans `app_path('Nexus/Plugins')` one directory level deep (`{Plugin}/*.php`), plus loose `.php` files directly in `Nexus/Plugins/` for simple single-file plugins. To disable a plugin without deleting it:
```php
// config/nexus.php
'plugins' => ['disabled' => [\App\Nexus\Plugins\Foo\FooPlugin::class]],
```
This key isn't in the shipped config file by default but works via `config('nexus.plugins.disabled', [])`'s fallback — add the `plugins` array yourself if needed.

A broken plugin's exception during discovery/register/boot is rethrown only when `app.debug` is true; otherwise it's logged via `report()` and swallowed, so one bad plugin can't take down the whole admin panel in production — but check logs, don't assume silence means success.

## Reference examples

- `app/Nexus/Plugins/Example/ExamplePlugin.php` — full cycle: `handle()` mutating a menu label, a `#[Filter]` on `nexus.validation.rules` scoped to the Demo module, a `#[Action]` registering a field-type alias. Backed by `tests/Feature/Nexus/PluginSystemTest.php`.
- `app/Nexus/Plugins/GraphQL/GraphQLPlugin.php` — anchor-only `#[TargetModule('User')]`, real work happens in `boot()` (route registration).
- `app/Nexus/Plugins/BlockTypes/BlockTypesPlugin.php` — anchor-only, `boot()` registers block types into `BlockTypeRegistry` and adds a view namespace.
