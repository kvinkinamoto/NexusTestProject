---
name: nexus-package
description: "Use when working on the Nexus package's own internals rather than authoring an app-level extension: packages/nodex/nexus/src/NexusServiceProvider.php boot sequence, config/nexus.php merging and publishing, the module manifest cache (bootstrap/cache/nexus-modules.php), the built-in Blade directives (@position/@nexusForm/@nexusBlocks), the template system (tailadmin/adminlte), field-type registration order, or any nexus:* artisan command. For authoring a content type, extension, or dashboard card use the sibling skills instead: nexus-modules, nexus-plugins, nexus-widgets."
metadata:
  scope: project
---

# Nexus Package Internals

`packages/nodex/nexus/src` is the vendor package; `app/Nexus/{Modules,Plugins,Widgets}` is where this application extends it. This skill is about the package's own machinery — read it before touching `NexusServiceProvider`, `config/nexus.php`, the manifest cache, or anything that affects *every* module/plugin/widget at once, not just one of them.

## Boot sequence (`NexusServiceProvider`)

**`register()`** (order matters — later steps assume earlier singletons exist):
1. `require helpers/functions.php` — makes `nexus_filter()`/`nexus_action()` globally available.
2. Bind every core service as a singleton: `PathManager`, `ModuleRegistry`, `ModuleManager`, `ModuleManifestCache`, `FormBuilder`, `ModuleServiceForAdminPanel`, `IconManager`, `HookManager`, `RelationRegistrar`, `ModuleDependencyChecker`, `PluginManager`, `AttributeSchemaReader`, `DirectTranslationService`, `FieldTypeRegistry`, `FieldVisibilityEvaluator`, `WidgetRegistry`, `BlockTypeRegistry`, `DashboardLayoutResolver`, `AdminDashboardRenderer`, `FrontWidgetRenderer`, `TemplateTypeResolver`, `NexusRuleCollector`. Inject any of these directly rather than re-resolving via `app()` inside new package code.
3. `MediaLibraryInterface` is `bind()` (not singleton) to `SpatieMediaLibraryService` — an app can override it with its own `$this->app->bind(MediaLibraryInterface::class, ...)` in a provider that boots after Nexus's, no special extension point needed.
4. `PluginManager::autoDiscover()` + `registerAll()` run here — plugin `register()` hooks fire during the container's register phase, before any other provider's `boot()`.

**`boot()`**, in this exact order:
1. `mergeConfig()` — see the config trap below.
2. `publish()` — registers publishable groups (see table below).
3. Package migrations, default API rate limiter (`RateLimiter::for('api', ...)`, only if the app hasn't defined one itself — guards against `MissingRateLimiterException` when an app calls `throttleApi()` without registering a limiter).
4. Blade directives registered: `@position`, `@nexusForm`, `@nexusBlocks` (see below).
5. `registerValidationRulesFilter()` — hooks `Validator\Factory::resolver()` so `nexus.validation.rules` fires for every `nexus.module.action` request; unrelated `Validator::make()` calls elsewhere are untouched no-ops.
6. Resolve the enabled-module list — from the manifest cache outside console, from a live `ModuleRegistry` scan inside console (console always sees *every* module, not just enabled ones, to avoid migration/FK ordering surprises).
7. Load per-module migrations, views, translations, icons, routes, events/listeners, relations (`RelationRegistrar`), field types, then two built-in field-type registration calls (`registerBuiltInFieldTypeAliases`, `registerBuiltInFieldTypeDefaultRules`), then widgets, then the two generic Livewire components (`nexus-module-table`, `nexus-module-form`).
8. View composers (`viewCompose()`) — binds sidebar menu data onto `nexus::{template}.layouts.adminpanel`, plus any module's own `$config->composers`.
9. `runCommand()` registers every package command under `commands/` plus every module's own console commands.
10. `PluginManager::bootAll()` — plugin `boot()` hooks fire last, after everything above already exists.
11. `FieldTypesRegistering` event + `nexus.field_types.register` action fire — the third and final field-type registration point.

**Field types can be registered from three separate points** (see `FieldTypeRegistry`'s own docblock): a module's `FieldTypes/` folder (step 7 above), the `FieldTypesRegistering` event, and the `nexus.field_types.register` action (both step 11, event fires first). Plugins normally use the action via `#[Action(hook: 'nexus.field_types.register')]` (see [nexus-plugins](../nexus-plugins/SKILL.md)).

A broken plugin during either `register()` or `boot()` only crashes the app when `app.debug` is true; otherwise it's `report()`-ed and swallowed — check logs, don't assume a quiet boot means every plugin ran.

## Config: the double-published-file trap

`mergeConfigFrom(__DIR__.'/config/nexus.php', 'nexus')` in `boot()` **only fills in keys absent from the app's own published `config/nexus.php`** — it never overrides a key the app-root file already defines. This repo has both:
- `packages/nodex/nexus/src/config/nexus.php` (package default)
- `config/nexus.php` (app-root, a **stale published copy** from before several recent features)

**Editing the package's config file alone silently does nothing for any key the app-root file already has.** A brand-new key merges in fine (the stale file doesn't have it yet), which is exactly what makes this easy to miss the first time.

**Rule: when adding or changing any `nexus.*` config key, edit both files, then verify with:**
```bash
php artisan config:show nexus.<key>
```
Don't trust that the package default took effect without checking.

Known keys not present in the shipped config file but read via `config('key', $default)` fallbacks, so they work even though you won't find them by reading the file: `nexus.plugins.disabled` (array of plugin FQCNs to skip discovery, default `[]`), `nexus.graphql_middleware` (default `['api', 'auth:sanctum']`), `nexus.graphql_prefix` (default `'graphql'`).

## The module manifest cache

`ModuleManifestCache` compiles a live filesystem scan (views/routes/translations/icons/commands/listeners/relations/field types/widgets, across every module) into `bootstrap/cache/nexus-modules.php`. When that file exists, `NexusServiceProvider::boot()` reads from it instead of scanning disk on every request — this affects **all** of modules, widgets, plugins-adjacent field types, listeners, and relations, not just one subsystem.

```bash
php artisan nexus:module:cache   # compile bootstrap/cache/nexus-modules.php
php artisan nexus:module:clear   # delete it, revert to live scanning
```

If something you just added under `app/Nexus/**` (a module, a field type, a widget, a listener, a translation file) doesn't show up, check whether this file exists before assuming your code is wrong.

## Templates and view namespace

`config('nexus.template')` (env `ZENTARA_TEMPLATE`, default `tailadmin`; `adminlte` is the other shipped option) selects the admin theme. The admin layout view composer targets `nexus::{template}.layouts.adminpanel` specifically — a new template needs a `resources/views/{template}/layouts/adminpanel.blade.php` to receive the sidebar-menu/`moduleMissingDependencies` data. Views live under `packages/nodex/nexus/src/resources/views/{tailadmin,adminlte}/...`, registered under the `nexus::` namespace — a module's own views register under `Str::lcfirst($moduleName)::...` instead (e.g. `demo::admin.custom_index_fields.image`). See the [views.md](../../../.ai/rules/views.md) project rule for a Tailwind-specific trap inside these Blade files (never build a utility class name via runtime interpolation).

## Built-in Blade directives — all fail quiet, none throw for missing data

```blade
@position('sidebar-top')                 {{-- or @position('name', $templateType) --}}
@nexusForm('contact-slug')
@nexusBlocks($page->blocks)
```
- `@position` renders every active `WidgetAssignment` for that position via `FrontWidgetRenderer`; template type resolves through `TemplateTypeResolver` (route param → route default → `config('nexus.widget_template_map')` by route name → `'default'`) when omitted.
- `@nexusForm` renders nothing for an unknown/inactive `Form` slug.
- `@nexusBlocks` skips (not errors) a block whose `type` no longer resolves to a `nexus::public.block_types.{type}` view — a page keeps rendering its other blocks even if one block type was removed from `BlockTypeRegistry`.

This "unknown input renders nothing, never a 500" posture is deliberate and consistent across all three — follow it if you add a fourth.

## Artisan commands reference

| Command | Signature | Purpose |
| --- | --- | --- |
| Install | `nexus:install` | Initialize the package |
| Module install | `nexus:module:install {name?}` | Create `Module` row, run migrations, seed permissions, register widgets |
| Module cache | `nexus:module:cache` | Compile the manifest cache |
| Module clear | `nexus:module:clear` | Delete the manifest cache |
| Make module | `nexus:make:module {name}` | Scaffold a module — see [nexus-modules](../nexus-modules/SKILL.md) |
| Make widget | `nexus:make:widget {name}` | Scaffold a widget — see [nexus-widgets](../nexus-widgets/SKILL.md) |
| Make plugin | `nexus:make:plugin {name} {--module=}` | Scaffold a plugin — see [nexus-plugins](../nexus-plugins/SKILL.md) |
| Make field | `nexus:make:field {name} {--module=}` | Scaffold a custom field-type renderer |
| Make filter | `nexus:make:filter {module}` | Scaffold a module's list-filter handler |
| Publish default modules | `nexus:default_module:publish` | Publish the package's bundled default modules |
| Publish resources | `nexus:resource:publish` | Publish package resources (`nexus:resource:publish` — internally `PublishDependency`) |
| Update | `nexus:update` | Run package update routine |
| Create super admin | `nexus:create:superadmin {name?} {email?} {password?} {--name=} {--email=} {--password=}` | Seed the first admin user |
| Init permissions | `nexus:permission:init` | Seed base permissions |
| Issue API token | `nexus:api-token:issue {email}` | Issue a Sanctum personal access token |
| Field-type docs | `nexus:docs:field-types {--markdown=}` | Cross-check curated field-type descriptions against what the admin form actually renders; `--markdown=path` writes a reference file instead of printing a table |

## Publish tags

```bash
php artisan vendor:publish --tag=nexus            # everything below
php artisan vendor:publish --tag=nexus-config     # config/nexus.php — see the double-file trap above
php artisan vendor:publish --tag=nexus-migrations
php artisan vendor:publish --tag=nexus-views      # resources/views/vendor/nexus
php artisan vendor:publish --tag=nexus-lang
php artisan vendor:publish --tag=nexus-resources-publish   # public/ assets
php artisan vendor:publish --tag=nexus-js
php artisan vendor:publish --tag=nexus-modules-publish     # the package's own bundled Modules/ into app/Nexus/Modules
```

## Gotchas summary

- Editing `packages/nodex/nexus/src/config/nexus.php` doesn't change a key the app-root `config/nexus.php` already has — edit both, verify with `config:show`.
- `bootstrap/cache/nexus-modules.php` freezes discovery for modules, widgets, field types, listeners, relations, routes, views, translations, icons, and commands all at once — `nexus:module:clear` after any structural change under `app/Nexus/**`.
- Plugin `register()`/`boot()` failures are swallowed outside `app.debug` — check logs before assuming a plugin ran.
- `@position`/`@nexusForm`/`@nexusBlocks` never error on missing data by design — an empty render usually means a data/config problem, not a directive bug.
