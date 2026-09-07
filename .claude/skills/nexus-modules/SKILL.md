---
name: nexus-modules
description: "Use when creating a new Nexus admin module (content type) or extending an existing one — adding/editing fields, sections, table columns, custom field types, filter handlers, or a relation/scope attached onto a DIFFERENT module you don't own (a specialized module like Review extending a base module like Product) — under app/Nexus/Modules/**. Covers the packages/nodex/nexus attribute-driven module system: the #[Module]/#[Field]/#[Column]/#[Section]/#[AttachRelation]/#[AttachScope] attributes, nexus:make:module and its sub-generators (nexus:make:field, nexus:make:filter), the Request-class validation rule, and the module manifest cache trap. Do not use for dashboard widgets (see nexus-widgets) or config-mutation/hook extensions that shouldn't own their own table (see nexus-plugins)."
metadata:
  scope: project
---

# Nexus Modules

A Nexus "module" is an admin CRUD content type. In this codebase every real module configures itself **entirely through PHP 8 attributes on its Eloquent model** — there is no `ModuleConfiguration.php` fluent builder in any shipped module (that style is legacy; `ModuleManager` only falls back to it when a module has no model at all).

## Scaffold, don't hand-write

```bash
php artisan nexus:make:module {Name}
```
Generates, under `app/Nexus/Modules/{Name}/`:
- `Models/{Name}.php` — attribute-driven model (the real source of truth)
- `Requests/AdminStoreRequest.php`, `Requests/AdminUpdateRequest.php`
- `database/migrations/{timestamp}_create_{plural_snake}_table.php`
- `resources/lang/en/translate.php`, `resources/lang/uk/translate.php`
- `resources/views/docs.blade.php`

Then:
```bash
php artisan migrate
php artisan nexus:module:install {Name}
```
`nexus:module:install` creates the `Module` row, runs migrations, seeds one `Permission` row per `PermissionPlacesEnum` case, and registers the module's own `Widgets/` folder into the live registry.

## The model's attributes (what to edit after scaffolding)

```php
#[Module(name: 'demo', label: 'Demo', icon: 'solar:box-bold', group: 'Site', showInMenu: true, livewire: true)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableImport(name: 'import', label: 'Import')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:box-bold')]
class Demo extends Model
{
    use HasAttributeSchemaProperties; // required on every attribute-driven module model

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true)]
    protected $name;
}
```

- `#[Section(...)]` declares an admin-form tab/column (`type: 'base'|'columns_2'`, `tab: 'basic'|'media'|'choices'|'relations'|'repeater'|'advanced'|'extra'`). Add one per logical grouping — see `app/Nexus/Modules/Demo/Models/Demo.php` for a real 8-section example.
- `#[Field(type: '...', ...)]` per property, optionally paired with `#[Column(...)]` to also show it in the list table.
- Built-in `type` values seen in production use (check `app/Nexus/Modules/Demo/Models/Demo.php` for a live example of each): `string`, `text` (`editor: true`), `number`, `email`, `password`, `boolean`, `date`, `datetime`, `image`, `video`, `images`, `videos`, `gallery` (Spatie Media Library, no DB column), `enum` (`enum: SomeBackedEnum::class`), `select`, `relation` (`belongsToMany`, optionally `ajax: true`, `ajaxMode: 'load'`), `belongsTo`, `relationManager`, `repeater` (`hasMany` + `#[RepeaterField]`), `json`, `time`, `location`, `multiple_string`, `radio`, `slug` (`slugSource: 'title'`), `color`, `icon`, `file`, `markdown`, `range`, `currency`, `rating`, `url`, `view` (renders a Blade partial: `view: 'demo::admin.components.price-preview'`).
- Useful per-field options: `translated: true` (needs `Spatie\Translatable\HasTranslations` + `public $translatable`), `showWhen: [['field' => 'x', 'op' => 'truthy']]` + `clearWhenHidden: true`, `disabled: 'edit'` (locked after create — value is `'create'|'edit'` and maps to which actions get blocked), `showInInfolist: false`, `apiExpose: true`.
- Need a field type the core doesn't have? `php artisan nexus:make:field {Name} --module={Module}` scaffolds a `FieldTypeRenderer` under `{Module}/FieldTypes/{Name}.php` + a co-located Blade view, then reference it as `#[Field(type: '{{lowerName}}', ...)]`.

## The Request-class rule (easy to get wrong)

**Once a module declares `#[Requests(actions: ['store' => ..., 'update' => ...])]`, the field-level `rules`/`storeRules`/`updateRules` on `#[Field]` are never consulted for that module's store/update — the Request's `rules()` must cover every field itself, scalar and relation alike.**

Any other action name works too — `actions` is a plain `action => FormRequest::class` map, resolved by `GetModuleRequestAction::getRequestByMethodName()` for every built-in and custom action (`restore`, `boolToggle`, `duplicate`, a custom `#[TableGroupAction]` name, etc.), not just `store`/`update`.

Relation fields use dotted `relation.*` keys, e.g.:
```php
'relation.permissionsPreload' => 'nullable|array',
'relation.permissionsPreload.*' => 'nullable|integer',
'relation.items.*.title' => 'required|string',
```
A relation key with no rule is silently dropped by validation — the relation just doesn't sync, with no error. When adding a new relation field to a module that already has Request classes, always add its `relation.*` rule too.

`AdminUpdateRequest` should re-declare any unique-constraint field with `Rule::unique(...)->ignore($this->id)` instead of the store request's plain `unique:table,column`.

## Filters (list-page search filters)

```bash
php artisan nexus:make:filter {Module}
```
Scaffolds `{Module}/Filters/ModuleFilterHandler.php` extending `FilterHandler`, overriding `filter(Builder $query, string $filterName, string $value): Builder` with a `match` per filter name. **The filter name must also be declared in the module's `TableConfigDto->filters`, or it silently never appears in the admin list UI** even though the handler code is correct.

## Declaring (non-blocking) dependencies between modules — `#[Module(requires:)]`

```php
#[Module(name: 'review', label: 'Review', requires: ['Product'])]
class Review extends Model { ... }
```

Purely informational — **`requires` never blocks install or enable.** A module with an unmet requirement still installs and enables fine; `ModuleDependencyChecker::getMissingDependencies($moduleName)`/`hasMissingDependencies()`/`getAllUnmetDependencies()` surface the gap as an admin-panel banner instead of refusing the operation, since the actual fix is usually "install the other module too," not "reject this one." Declare it on every module that uses `#[AttachRelation]`/`#[AttachField]` onto another module — it's the one piece of self-documentation that tells an admin *why* a field/column silently isn't there, instead of them having to guess.

## Attaching a relation/scope onto another module without owning it — `#[AttachRelation]`/`#[AttachScope]`

When a module needs to extend **another** module's model (e.g. a Review module adding a `reviews` relation onto Product) without Product's file ever referencing it back, don't add the relation to Product directly — declare it from the specialized module's own side instead, under a `Relations/` folder:

```php
// app/Nexus/Modules/Review/Relations/ProductRelations.php
class ProductRelations
{
    #[AttachRelation(model: \App\Nexus\Modules\Product\Models\Product::class, name: 'reviews')]
    public static function reviews(Product $product): HasMany
    {
        return $product->hasMany(Review::class);
    }

    #[AttachScope(model: Product::class, name: 'withReviewsCount')]
    public static function withReviewsCount(): \Closure
    {
        return fn ($query) => $query->withCount('reviews');
    }
}
```

- Public **static** methods only. Scanned per enabled module by `ModuleManifestCache::discoverRelations()`/`discoverScopes()`, wired by `NexusServiceProvider::loadRelations()` — backed by plain `Model::resolveRelationUsing()`/`Model::addGlobalScope()`, nothing Nexus-specific.
- **A relation attached this way only supports method-call syntax** (`$product->reviews()`), never the `$product->reviews` dynamic-property shortcut — a documented Eloquent limitation of `resolveRelationUsing()`. Any Blade/PHP code touching it must call it as a method; guard the call in a `try`/`catch` if the relation might not exist (e.g. the owning module could be disabled).
- This alone only wires the **data layer** — `$product->reviews()` resolves, but nothing shows up in Product's admin UI. To surface it there, pair this with `#[TargetModule('Product')]` + `#[AttachField]`/`#[AttachColumn]` on a plugin — see [nexus-plugins](../nexus-plugins/SKILL.md#attaching-a-fieldcolumnfilter-onto-another-module--attachfieldattachcolumnattachfilter).
- Installing the module mid-request (the admin "install" action, or a console command inside the same process) registers these immediately (`ModuleManager::instanceInstall()`) — no server restart needed. Disabling/removing the module makes them disappear on the next request the normal way (its `Relations/` folder is simply skipped by the enabled-module scan).
- Reference: `app/Nexus/Modules/ConfirmFixtureNote/Relations/ConfirmFixtureRelations.php`, backed by `tests/Feature/Nexus/AttachFieldColumnTest.php`.

## The manifest-cache trap (read this before debugging "my change isn't showing up")

If `bootstrap/cache/nexus-modules.php` exists, module/field-type/widget/listener/translation discovery is served from that compiled manifest, not the live filesystem. A newly added module, field type, filter, or widget file **will not appear** until you run:
```bash
php artisan nexus:module:clear
```
(or `php artisan nexus:module:cache` to recompile immediately). `nexus:make:field` itself detects the cache file and warns about this — do the same mental check after any structural change under `app/Nexus/Modules/**`. This is the module-system analog of the config-publish trap already recorded in project memory — same category of "silent stale state," different subsystem.

## Reacting to another module's lifecycle without a plugin class — `Listeners/`

Any class under a module's own `Listeners/` folder is auto-discovered (no `EventServiceProvider` registration needed) and wired one of two ways:
- a `handle({EventClass} $event)` method with the event type-hinted as the **first parameter** — auto-detected by reflection;
- a `subscribe($events)` method (Laravel's own subscriber pattern) for one class handling several events.

```php
// app/Nexus/Modules/ActivityLog/Listeners/LogModuleAction.php
class LogModuleAction
{
    public function handle(\Nodex\Nexus\Events\ModuleActionExecuted $event): void { ... }
}
```

This is the **Laravel-native** half of Nexus's extension system — every entity-lifecycle/form/table/validation/etc. moment that fires a `nexus_filter()`/`nexus_action()` (see [nexus-plugins](../nexus-plugins/SKILL.md#extension-points-reference-events--their-plugin-hook-twins)) fires a matching real Laravel event **first**, at the same call site, carrying the same data. Use a `Listeners/` class here when the reaction is specific to *this* module (no `#[TargetModule]`/plugin class needed, no cross-cutting reuse); use a plugin's `#[Filter]`/`#[Action]` instead when the same logic should apply across modules or ship as an independent, toggleable unit (see [nexus-plugins](../nexus-plugins/SKILL.md)).

## Less common per-model attributes (full reference)

Beyond `#[Module]`/`#[Field]`/`#[Column]`/`#[Section]`/`#[Requests]` above, all `Attribute::TARGET_CLASS` unless noted, all repeatable unless noted:

| Attribute | Purpose | Example |
| --- | --- | --- |
| `#[TableFilter]` | Declares a list-page filter box (paired with `nexus:make:filter`'s handler — see "Filters" above; a bare `type: 'search'` needs no handler at all, `FilterHandler::filter()`'s generic case already LIKE-matches every column) | `#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]` |
| `#[TableLens]` | A named, pre-filtered alternate view of the index table (Nova-style Lens) — reuses `UniversalFilterBuilder`'s condition-array shape, can override visible columns/default sort while active | `#[TableLens(name: 'published', label: 'lens_published', conditions: [['column' => 'is_published', 'operator' => '=', 'value' => 1]])]` |
| `#[TableImport]` | Opts into CSV import — unlike export, import has no auto-registered default; a module must declare it explicitly since it writes data | `#[TableImport(name: 'import', label: 'Import')]` |
| `#[Permission]` | Registers a custom permission (`{moduleName}_{action}`) so it's assignable to roles from the admin panel — doesn't grant it to anyone. Also usable `TARGET_METHOD` on a custom controller action | `#[Permission(action: 'export', label: 'Export Articles')]` |
| `#[Composer]` | Registers a Blade view-composer class for this module's own views | `#[Composer(class: DemoIndexComposer::class)]` |
| `#[MethodResource]` | Binds a dedicated API Resource class (with eager-load hints) to one custom controller method, instead of the generic `NexusResource` fallback | `#[MethodResource(method: 'export', resourceClass: DemoExportResource::class, with: ['items'])]` |
| `#[Setting]` | Attaches a module-level setting (its own settings screen, independent of any table row) — no migration needed, values live in the shared `nexus_module_settings` key/value table. See the dedicated Settings registry project memory for the full story | `#[Setting(name: 'items_per_page', type: 'number', label: 'items_per_page', default: 25)]` |
| `#[SectionColumn]` | Overrides a form-layout column's CSS width (`left`/`right` exist by default at `col-lg-4`/`col-lg-8`) | `#[SectionColumn(name: 'left', class: 'col-lg-8')]` |
| `#[RepeaterField]` `TARGET_METHOD` | Declares one column of a `type: 'repeater'` field — stack several on the same `hasMany` relation method, one per column, in declaration order; the relation method itself still needs `#[Field(type: 'repeater')]` + `#[Relation(type: 'hasMany')]` | `#[RepeaterField(name: 'quantity', type: 'number', label: 'quantity', required: true)]` |

## Migrations

Extend an existing module's table with a new additive migration (`add_x_to_y_table`) rather than editing the original `create_x_table` migration — see `app/Nexus/Modules/Demo/database/migrations/` for the real pattern (base table, then two additive migrations as fields were added later).

## Canonical reference

`app/Nexus/Modules/Demo` is the living example covering nearly every field type, section/tab layout, a repeater, translated fields, `showWhen` conditionals, and a module-scoped widget. When unsure how a field type or attribute option behaves, read it there before guessing.
