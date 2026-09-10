<?php

namespace App\Nexus\Modules\OrderStatus\Models;

use App\Nexus\Concerns\HasAutoSlug;
use App\Nexus\Modules\OrderStatus\Requests\AdminStoreRequest;
use App\Nexus\Modules\OrderStatus\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Translatable\HasTranslations;

/**
 * Kept as simple as ZentaraStartProject's own version — no SoftDeletes
 * (matching every other simplified module in this port), dropped
 * BelongsToShop (single-shop-per-install).
 */
#[Module(
    name: 'orderStatus',
    label: 'Order Statuses',
    icon: 'solar:clipboard-list-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'base', icon: 'solar:clipboard-list-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class OrderStatus extends Model
{
    use HasAttributeSchemaProperties, HasAutoSlug, HasTranslations;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, translated: true)]
    protected $name;

    #[Column(label: 'Slug', sortable: true)]
    #[Field(type: 'slug', section: 'main', label: 'Slug', required: false, slugSource: 'name', disabled: 'edit')]
    protected $slug;

    #[Column(label: 'Default', action: 'boolToggle', fieldName: 'is_default')]
    #[Field(type: 'boolean', section: 'settings', label: 'Is default', required: false)]
    protected $is_default;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    public $translatable = ['name'];

    protected $fillable = ['name', 'slug', 'is_default', 'publish'];

    protected $casts = [
        'is_default' => 'boolean',
        'publish' => 'boolean',
    ];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
