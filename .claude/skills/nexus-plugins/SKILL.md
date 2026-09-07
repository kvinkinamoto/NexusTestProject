---
name: nexus-plugins
description: "Use when extending Nexus (packages/nodex/nexus) behavior without owning a database table: mutating another module's config at runtime, adding validation rules to an existing module, registering a custom field type globally, hooking dashboard/widget output, running boot-time code (routes, block types, container wiring), attaching a field/column/filter onto a module you don't own (a specialized module like Review extending a base module like Product), or reacting to any entity/form/table/validation/import/export/permission/media/search lifecycle moment. Covers app/Nexus/Plugins/** — the #[TargetModule]/#[Filter]/#[Action]/#[AttachField]/#[AttachColumn]/#[AttachFilter] attribute system, the full Events/ catalog and its nexus_filter()/nexus_action() twins, HookManager, and plugin discovery. Do not use for a new CRUD content type (see nexus-modules) or a dashboard card (see nexus-widgets)."
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

## Attaching a field/column/filter onto another module — `#[AttachField]`/`#[AttachColumn]`/`#[AttachFilter]`

Declarative sugar over `handle()` for the single most common cross-module case: a specialized module (e.g. Review) adding one admin field/column/filter onto a base module (e.g. Product) it doesn't own, without Product's file ever referencing Review. Place on a public marker method — the method body is never called, only its attributes are read (same shape as `#[Filter]`/`#[Action]` above):

```php
#[TargetModule('Product')]
class ReviewAdminPlugin
{
    #[AttachField(name: 'reviews', type: 'relationManager', section: 'relations', label: 'review::translate.reviews', permission: 'review_view')]
    #[Relation(type: 'hasMany', show: 'title', relatedModule: 'review')]
    public function reviewsField(): void {}

    #[AttachColumn(name: 'reviews_count', label: 'review::translate.reviews', permission: 'review_view')]
    public function reviewsCountColumn(): void {}

    #[AttachFilter(name: 'has_reviews', label: 'review::translate.has_reviews', type: 'search')]
    public function reviewsFilter(): void {}
}
```

- **`#[AttachField]`+`#[Relation]` only makes the field visible in Product's admin form — the underlying Eloquent relation must exist separately**, declared from Review's own side via `#[AttachRelation]` (see [nexus-modules](../nexus-modules/SKILL.md#attaching-a-relationscope-onto-another-module-without-owning-it--attachrelationattachscope)). Both halves are needed for a working `relationManager` field — this attribute alone won't make `$product->reviews()` resolve.
- **Labels must use the `'ownNamespace::translate.key'` form**, not a bare string. `_label.blade.php`/`module-table.blade.php` resolve a bare label against the **target** module's own lang file (`Product`'s, which you can't touch) — the `'::'`-containing form bypasses that and resolves from whatever namespace you name instead, so put the real translation in Review's own `resources/lang/{locale}/translate.php`.
- **`permission`, when set, independently gates the attachment per-viewer** — separate from whatever permission Product's own edit/view action already requires. An entry the current viewer can't see is never merged into the config at all (not rendered-but-hidden) — this is what keeps a paid/optional module's data from leaking to every editor of a free base module. Omit it to inherit the target module's own gate (the default, backward-compatible behavior).
- `#[AttachField(apiExpose: true)]` mirrors `#[Field(apiExpose:)]` — exposes the attached field through REST/GraphQL the same way a module's own field would be.
- `#[AttachColumn]`'s/`#[AttachFilter]`'s value still has to resolve on the target model like any other column/filter — these attributes only make an entry appear, they don't manufacture data. A computed value (e.g. a `_count` column) needs a paired `#[AttachScope]` applying `withCount(...)` (see nexus-modules link above).
- All three are discovered and applied by `PluginManager` alongside `#[Filter]`/`#[Action]`. Disabling this plugin, or Review never being installed at all, makes the attachment disappear cleanly on the next request — fail-quiet, never a 500 (the `relationManager` display partials already tolerate a missing/orphaned relation).
- Reference: `app/Nexus/Modules/ConfirmFixtureNote/` + `app/Nexus/Plugins/ConfirmFixtureNote/ConfirmFixtureNotePlugin.php`, backed by `tests/Feature/Nexus/AttachFieldColumnTest.php`.

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

When scoping a filter/action to one module, check the payload inside the method (e.g. `if ($moduleConfig->name !== 'demo') return $rules;`) — the hook itself isn't module-scoped, every registered listener for that hook fires for every module. The full hook catalog moved below, alongside each one's Laravel-native event twin.

## Extension points reference: events & their plugin-hook twins

**Nearly every extension point in this package fires twice at the same call site: a real Laravel event first, then the matching `nexus_filter()`/`nexus_action()` right after, with identical data.** They're deliberately overlapping, not one superseding the other — a module's own [`Listeners/` class](../nexus-modules/SKILL.md#reacting-to-another-modules-lifecycle-without-a-plugin-class--listeners) needs no plugin/`#[TargetModule]` at all and is the natural choice for module-specific reactions; a plugin's `#[Filter]`/`#[Action]` is the natural choice for reusable, cross-module, independently-toggleable logic. Pick whichever fits — both reach the exact same moment.

A `*ing`/`*Building`/`*Preparing`/`*Resolving`-named event mutates a **by-reference** property (`&$data`, `&$rules`, `&$result`, ...) — write into that property directly, don't `return` anything from `handle()`. Throwing from one of these aborts the operation with a normal exception (no separate "cancel" flag anywhere in this package). A past-tense event (`EntityCreated`, `ModuleInstalled`, ...) is a pure side-effect notification — nothing to mutate.

**Entity lifecycle** (every module's store/update/delete/restore/duplicate — `$moduleConfig` is the target module's schema DTO):

| Moment | Event class | Hook (kind) | Payload |
| --- | --- | --- | --- |
| Before create (incl. duplicate) | `EntityCreating` | `nexus.entity.creating` (filter) | `(Model $model, array &$data, $moduleConfig)` |
| After create | `EntityCreated` | `nexus.entity.created` (action) | `(Model $model, $moduleConfig)` |
| Before update | `EntityUpdating` | `nexus.entity.updating` (filter) | `(Model $model, array &$data, array $oldData, $moduleConfig)` |
| After update | `EntityUpdated` | `nexus.entity.updated` (action) | `(Model $model, $moduleConfig)` |
| Before delete | `EntityDeleting` | `nexus.entity.deleting` (action) | `(Model $model, $moduleConfig)` |
| After delete | `EntityDeleted` | `nexus.entity.deleted` (action) | `(Model $model, $moduleConfig)` |
| Before restore | `EntityRestoring` | `nexus.entity.restoring` (action) | `(Model $model, $moduleConfig)` |
| After restore | `EntityRestored` | `nexus.entity.restored` (action) | `(Model $model, $moduleConfig)` |
| Any table action/group-action finished (delete/restore/duplicate/custom) | `ModuleActionExecuted` | `nexus.module.action_executed` (action) | `(string $moduleName, string $actionName, ?string $id, ?array $ids)` — read-only actions (index/edit/create/view) excluded, this marks state changes only |
| A bulk selection is about to run | `BulkActionExecuting` | `nexus.bulk_action.executing` (filter) | `(string $moduleName, string $actionName, array &$ids)` — drop ids for a partial veto |

**Building a module's admin form/table** (inject a field/column/tab without touching the module — the general-purpose, imperative sibling of `#[AttachField]`/`#[AttachColumn]` above):

| Moment | Event class | Hook (kind) | Payload |
| --- | --- | --- | --- |
| Form config resolved, before rendering | `AdminFormBuilding` | `nexus.form.building` (action) | `(moduleName, config, ?Model $model, ?array $liveData)` — mutate `$event->config` directly (add fields/sections/tabs) |
| Form fields finalized | `FormFieldsPrepared` | `nexus.form.fields_prepared` (filter) | `(moduleName, array &$fields)` |
| Table config resolved, before rendering | `AdminTableBuilding` | `nexus.table.building` (action) | `(moduleName, config)` — mutate `$event->config` (add columns/filters/actions) |
| Table rows fetched | `TableDataPrepared` | `nexus.table.data_prepared` (filter) | `(moduleName, array &$data)` |

**Validation:**

| Moment | Event class | Hook (kind) | Payload |
| --- | --- | --- | --- |
| A FormRequest is about to validate | `PreparingForValidation` | `nexus.validation.preparing` (action) | `(FormRequest $request)` |
| Rules gathered (also fires for a module's own hand-written `AdminStoreRequest`/`AdminUpdateRequest` via `NexusFormRequest::withValidator()`) | `GatheringValidationRules` | `nexus.validation.rules` (filter) | `($moduleConfig, array &$rules, string $action)` |

**API, import/export:**

| Moment | Event class | Hook (kind) | Payload |
| --- | --- | --- | --- |
| Generic `NexusResource` serializing a model (skipped entirely if the module has its own `{Model}Resource`) | `ApiResourceBuilding` | `nexus.api.resource` (filter) | `(Model $resource, array &$data)` |
| Every exported CSV row | `ExportRowBuilding` | `nexus.export.row` (filter) | `(Model $model, array &$row, string $moduleName)` |
| Every imported CSV row, before mass-assignment (fillable-filtered either way) | `ImportRowBuilding` | `nexus.import.row` (filter) | `(array &$data, $moduleConfig)` |

**Permission, menu, dashboard:**

| Moment | Event class | Hook (kind) | Payload |
| --- | --- | --- | --- |
| A permission decision was just computed | `PermissionChecking` | `nexus.permission.check` (filter) | `(string $action, ?Module $module, string $place, bool &$result)` |
| Sidebar menu built | `SidebarMenuBuilding` | `nexus.menu.sidebar` (filter) | `(array &$menu)` — array of `MenuConfigDto`, can append an entry not tied to any module |
| Dashboard layout resolved (own-row → is_default-row → config waterfall already applied) | `DashboardLayoutResolving` | `nexus.dashboard.layout` (filter) | `(?Authenticatable $user, array &$layout)` |

**Media, module discovery/lifecycle, search, notifications:**

| Moment | Event class | Hook (kind) | Payload |
| --- | --- | --- | --- |
| Before a media upload attaches (throw to reject) | `MediaAttaching` | `nexus.media.attaching` (action) | `(Model $model, UploadedFile $file, string $collection)` |
| After a genuinely new upload attaches (not the sha256-dedup path) | `MediaAttached` | `nexus.media.attached` (action) | `(Model $model, MediaItemDto $item, string $collection)` |
| Module list assembled, after the Modules/UserModules filesystem scan | `ModuleDiscoveryCompleted` | `nexus.module.discovery` (filter) | `(Collection &$modules)` — inject a module that isn't a real directory, or hide one |
| A module finished installing / uninstalling | `ModuleInstalled` / `ModuleUninstalled` | `nexus.module.installed` / `nexus.module.uninstalled` (action) | `(string $moduleName)` |
| Global search results assembled | `GlobalSearchCompleted` | `nexus.search.results` (filter) | `(array &$results, string $term)` — append a non-module result group |
| Admin notification-bell payload built | `NotificationDataBuilding` | `nexus.notification.data` (filter) | `(Notification $notification, $notifiable, array &$data)` |

**Field types, widgets** (boot-time / output):

| Moment | Event class | Hook (kind) | Payload |
| --- | --- | --- | --- |
| Boot-time field-type registration (third and final registration point, see [nexus-package](../nexus-package/SKILL.md)) | `FieldTypesRegistering` | `nexus.field_types.register` (action) | `(FieldTypeRegistry $registry)` |
| A widget's `data` or rendered `html` resolved, **before** `WidgetOutputCache` caches it | `WidgetOutputResolving` | `widget.data.{key}` / `widget.html.{key}` (filter) | `(string $widgetKey, string $kind, mixed &$output, WidgetContext $context)` |

Every event class above lives in `packages/nodex/nexus/src/Events/` with a full docblock (rationale + a copy-pasteable listener example) — read the class itself before guessing a payload shape. `ModuleEvent` is an older, generic/untyped event kept for backward compatibility; prefer a specific typed event from the table above for anything new.

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
