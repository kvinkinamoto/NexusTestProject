---
name: nexus-widgets
description: "Use when creating or editing a Nexus dashboard/front widget under app/Nexus/Widgets/** or a module's own {Module}/Widgets/** folder. Covers packages/nodex/nexus's #[Widget] attribute, the WidgetInterface/ApiSerializable/RendersHtml/ProvidesMetric contracts, nexus:make:widget, WidgetContext/MetricDto shapes, and widget-registration gotchas (surface/capability mismatches, requires:, dashboard default config, output caching). Do not use for a full admin CRUD content type (see nexus-modules) or a config-mutating extension (see nexus-plugins)."
metadata:
  scope: project
---

# Nexus Widgets

A widget is a class implementing `WidgetInterface`, discovered via a `#[Widget]` attribute, that can render on the admin dashboard, a front-end page (`@position()`), and/or the API. One folder per widget with a co-located Blade view — same self-contained-folder convention as field types and plugins.

## Scaffold

```bash
php artisan nexus:make:widget {Name}
```
Creates `app/Nexus/Widgets/{Name}/{Name}.php` (implementing `WidgetInterface, RendersHtml`) and `app/Nexus/Widgets/{Name}/{lowerName}.blade.php`. A module-owned widget instead goes under `{Module}/Widgets/{Name}/` (e.g. `app/Nexus/Modules/Demo/Widgets/DemoRecordsCount/`) and is discovered scoped to that module.

After scaffolding, add `'{lowerName}'` to `config('nexus.dashboard.default')` to show it by default, or leave it for users to add via the dashboard's widget picker.

## `#[Widget]` attribute — every parameter

```php
#[Widget(
    name: 'usersCount',              // stable slug persisted in placements — never rename once placed
    label: 'Total Users',
    surfaces: [WidgetSurface::Admin, WidgetSurface::Api],  // also: WidgetSurface::Front
    icon: 'bx bx-group',
    group: 'stats',                  // widget-picker grouping key
    module: null,                    // owning module, cosmetic only
    cacheTtl: null,                  // seconds; null/0 = never cached, recomputed every call
    requires: [],                    // module name(s) that must be enabled or registration is silently skipped
    apiPublic: false,                // may the Api surface serve this to anonymous requests
    defaultSize: '1x1',
    permission: null,                // required permission to view/place in the admin dashboard
    lazy: false,                     // render an AJAX placeholder instead of computing HTML inline
)]
```

## Pick the right capability interface(s)

```php
interface WidgetInterface {                                    // always required
    public function getData(array $config, WidgetContext $context): array;
    public static function configFields(): array;               // FieldConfigDto[] for widget-instance settings
}
interface ProvidesMetric {                                      // single-number stat card, no view needed
    public function toMetric(array $config, WidgetContext $context): MetricDto;
}
interface RendersHtml {                                         // custom Blade output
    public function availableViews(): array;                    // ['file.blade.php' => 'Display Name']
    public function viewFor(?string $name): ?string;
    public function render(array $config, WidgetContext $context): string;
}
interface ApiSerializable {                                     // reshape output for the API surface
    public function toApiPayload(array $config, WidgetContext $context): array;
}
```
- Declaring `Admin` or `Front` in `surfaces` needs `RendersHtml` **or** `ProvidesMetric` implemented — the registry does not enforce this, it only logs a warning and the widget silently renders nothing. Pick one deliberately:
  - No view of your own, just a tracked number → implement `ProvidesMetric` only (falls back to the built-in `metric_card.blade.php` partial). See `UsersCount`.
  - Real custom markup → implement `RendersHtml`. See `RecentDemoRecords`.
  - A widget can implement both.
- `Api` surface needs nothing beyond `WidgetInterface::getData()`; add `ApiSerializable` only when the API shape should differ from the dashboard shape (e.g. hiding an internal field) — see `UsersCount::toApiPayload()` returning `['totalUsers' => ...]` vs `getData()`'s `['count' => ...]`.

## `WidgetContext` and `MetricDto`

```php
final class WidgetContext {
    public readonly WidgetSurface $surface;
    public readonly ?string $position;
    public readonly ?string $templateType;
    public readonly ?int $instanceId;
    public readonly ?Authenticatable $user;
    public readonly ?string $locale;
    public readonly array $params;   // e.g. $context->params['view'] to pick a RendersHtml view variant
}

class MetricDto extends \stdClass {
    public string $label;
    public int|float|string $value;
    public int|float|string|null $previous = null;
    public ?float $deltaPercent = null;
    public ?string $direction = null;   // 'up' | 'down' | 'flat' | null
    public array $series = [];          // sparkline points, oldest first
    public ?string $unit = null;
    public ?string $icon = null;
    public ?string $link = null;
}
```

## Gotchas

- **Stale dashboard default.** `config('nexus.dashboard.default')` ships as `['helloWorld']` — not a real widget key. A fresh install's dashboard renders empty until this is pointed at a real `#[Widget(name:)]` slug.
- **`requires: [...]` fails silently.** If the listed module isn't enabled, the widget just isn't registered — no error, no log. A "missing" widget in the picker often means an unmet `requires` dependency, not a bug.
- **Output caching happens after filters, not before.** `WidgetOutputCache::remember()` fires the `WidgetOutputResolving` event and the `widget.data.{key}`/`widget.html.{key}` plugin filters, *then* caches the result if `cacheTtl` is set. If a plugin mutates widget output and you need it to stay fresh, don't fight the cache with the filter — lower or clear `cacheTtl` on the `#[Widget]` attribute instead.
- **Manifest cache applies here too.** A new widget file (app-global or module-scoped) is discovered through the same `bootstrap/cache/nexus-modules.php` compiled manifest as modules. If it doesn't show up, run `php artisan nexus:module:clear` (see the [nexus-modules](../nexus-modules/SKILL.md) skill for the full explanation).
- **`name` is the persisted identity.** It's a slug, not the class name, deliberately — so renaming the PHP class doesn't orphan existing dashboard/front placements. Never change `name` on a widget already in use without a migration plan for existing placements.

## Reference examples

- `app/Nexus/Widgets/UsersCount/UsersCount.php` — `ProvidesMetric` + `ApiSerializable`, `Admin`+`Api` surfaces, no custom view.
- `app/Nexus/Widgets/RecentDemoRecords/RecentDemoRecords.php` — `RendersHtml`, `Admin`+`Front` surfaces, reads `$config['limit']`, has a real co-located Blade view.
- `app/Nexus/Modules/Demo/Widgets/DemoRecordsCount/DemoRecordsCount.php` — module-scoped widget example (lives under the module's own folder, not `app/Nexus/Widgets/`).
