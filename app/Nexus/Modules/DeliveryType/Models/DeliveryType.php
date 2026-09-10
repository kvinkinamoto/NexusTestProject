<?php

namespace App\Nexus\Modules\DeliveryType\Models;

use App\Nexus\Modules\Delivery\Models\Delivery;
use App\Nexus\Modules\DeliveryType\Requests\AdminStoreRequest;
use App\Nexus\Modules\DeliveryType\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Translatable\HasTranslations;

/**
 * A concrete option within a Delivery (e.g. Nova Poshta -> "warehouse
 * pickup" vs "courier"). `key` is what `Order.delivery_type` stores.
 * Dropped SoftDeletes, matching every other simplified module in this port.
 */
#[Module(
    name: 'deliveryType',
    label: 'Delivery Types',
    icon: 'solar:list-check-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
    requires: ['Delivery'],
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by title or key', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:list-check-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class DeliveryType extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    #[Column(label: 'Title', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Title', required: true, translated: true)]
    protected $title;

    #[Column(label: 'Key', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Key', required: true, rules: ['max:64'])]
    protected $key;

    #[Field(type: 'relation', section: 'settings', label: 'Delivery', required: false)]
    #[Relation(type: 'belongsTo', show: 'title')]
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    public $translatable = ['title'];

    protected $fillable = ['title', 'key', 'delivery_id', 'publish'];

    protected $casts = ['publish' => 'boolean'];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }
}
