<?php

namespace App\Nexus\Modules\Language\Models;

use App\Nexus\Modules\Language\Requests\AdminStoreRequest;
use App\Nexus\Modules\Language\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Defines which locales exist for the admin panel (and, once a storefront
 * exists, the public site) — `language` is the display name ("English",
 * "Українська"), `code` the actual locale key used everywhere else in this
 * port's `resources/lang/{code}/translate.php` convention.
 *
 * Dropped from the old ZentaraStartProject module: `NodeTrait`/nested-set
 * columns (never actually used — languages aren't hierarchical, same
 * vestigial-tree pattern already cut from ShopAttribute/ShopProductType).
 * `is_default` has no exclusivity enforcement (only one row should really be
 * default), matching this port's own Currency module's `is_base` — same
 * "no enforcement" design already established in Phase 1.
 *
 * The `switchAdminLocale` row action (below) reuses the custom-TableAction-
 * dispatch mechanism discovered in Phase 12 (Backup's `run`/Restore's
 * `scan`/`runRestore`) — a plain method on this module's own AdminController.
 */
#[Module(
    name: 'language',
    label: 'Languages',
    icon: 'solar:global-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'restore', label: 'Restore', icon: 'restore', isConfirm: true)]
#[TableAction(name: 'deletePermanent', label: 'Delete permanent', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'switchAdminLocale', label: 'Use for admin', icon: 'translate', isConfirm: false)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'language', column: 'left', type: 'columns_2', icon: 'solar:global-bold')]
#[Section(name: 'setting', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class Language extends Model
{
    use HasAttributeSchemaProperties, SoftDeletes;

    #[Column(label: 'Language', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'language', label: 'Language', required: true)]
    protected $language;

    #[Column(label: 'Code', sortable: true)]
    #[Field(type: 'string', section: 'language', label: 'Code', required: true)]
    protected $code;

    #[Column(label: 'Default', sortable: true)]
    #[Field(type: 'boolean', section: 'setting', label: 'Is default', required: false, default: false)]
    protected $is_default;

    #[Column(label: 'Publish', sortable: true)]
    #[Field(type: 'boolean', section: 'setting', label: 'Publish', required: false, default: true)]
    protected $publish;

    #[Field(type: 'boolean', section: 'setting', label: 'Is RTL', required: false, default: false)]
    protected $is_rtl;

    protected $fillable = [
        'language',
        'code',
        'is_default',
        'publish',
        'is_rtl',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'publish' => 'boolean',
            'is_rtl' => 'boolean',
        ];
    }

    protected function scopePublish(Builder $query): Builder
    {
        return $query->where('publish', true);
    }

    /**
     * Alias for scopePublish() — Nodex\Nexus\Services\DirectTranslationService
     * (the core package's own, already-existing, singleton-bound service —
     * see Translations module's AdminController) and RelatedEntityFieldService
     * both look specifically for an `isPublished` scope by that exact name
     * (the latter via method_exists() as an optional capability check), a
     * different naming convention than the `publish` scope every other
     * "publish" boolean model in this port uses (carried over from
     * ZentaraStartProject's own scopePublish()). Both names point at the
     * same query so either caller gets the right rows.
     */
    protected function scopeIsPublished(Builder $query): Builder
    {
        return $this->scopePublish($query);
    }
}
