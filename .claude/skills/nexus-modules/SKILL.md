---
name: nexus-modules
description: "Use when creating a new Nexus admin module (content type) or extending an existing one — adding/editing fields, sections, table columns, custom field types, or filter handlers under app/Nexus/Modules/**. Covers the packages/nodex/nexus attribute-driven module system: the #[Module]/#[Field]/#[Column]/#[Section] attributes, nexus:make:module and its sub-generators (nexus:make:field, nexus:make:filter), the Request-class validation rule, and the module manifest cache trap. Do not use for dashboard widgets (see nexus-widgets) or config-mutation/hook extensions that shouldn't own their own table (see nexus-plugins)."
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

## The manifest-cache trap (read this before debugging "my change isn't showing up")

If `bootstrap/cache/nexus-modules.php` exists, module/field-type/widget/listener/translation discovery is served from that compiled manifest, not the live filesystem. A newly added module, field type, filter, or widget file **will not appear** until you run:
```bash
php artisan nexus:module:clear
```
(or `php artisan nexus:module:cache` to recompile immediately). `nexus:make:field` itself detects the cache file and warns about this — do the same mental check after any structural change under `app/Nexus/Modules/**`. This is the module-system analog of the config-publish trap already recorded in project memory — same category of "silent stale state," different subsystem.

## Migrations

Extend an existing module's table with a new additive migration (`add_x_to_y_table`) rather than editing the original `create_x_table` migration — see `app/Nexus/Modules/Demo/database/migrations/` for the real pattern (base table, then two additive migrations as fields were added later).

## Canonical reference

`app/Nexus/Modules/Demo` is the living example covering nearly every field type, section/tab layout, a repeater, translated fields, `showWhen` conditionals, and a module-scoped widget. When unsure how a field type or attribute option behaves, read it there before guessing.
