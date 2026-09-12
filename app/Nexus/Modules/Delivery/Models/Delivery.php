<?php

namespace App\Nexus\Modules\Delivery\Models;

use App\Nexus\Modules\Delivery\Requests\AdminStoreRequest;
use App\Nexus\Modules\Delivery\Requests\AdminUpdateRequest;
use App\Nexus\Modules\DeliveryType\Models\DeliveryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * A delivery carrier/method (Nova Poshta, pickup, taxi, Ukrposhta, ...).
 * `key` is what `Order.delivery_method` stores and what
 * `TrackingManager::TRACKABLE_CARRIERS` matches against ('nova-poshta',
 * 'ukr-poshta' are the two trackable ones — others just have no shipment
 * tracking). Dropped SoftDeletes, matching every other simplified module in
 * this port.
 */
#[Module(
    name: 'delivery',
    label: 'Deliveries',
    icon: 'solar:delivery-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by title or key', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:delivery-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
#[Section(name: 'types_section', column: 'left', type: 'base', icon: 'solar:list-bold')]
class Delivery extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    #[Column(label: 'Title', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Title', required: true, translated: true)]
    protected $title;

    #[Field(type: 'text', section: 'main', label: 'Description', required: false, translated: true)]
    protected $description;

    #[Column(label: 'Key', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Key', required: true, rules: ['max:64'])]
    protected $key;

    #[Column(label: 'Image', customField: 'showImage')]
    #[Field(type: 'image', section: 'settings', label: 'Image', required: false)]
    protected $image;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    #[Field(type: 'relationManager', section: 'types_section', label: 'Types', required: false)]
    #[Relation(show: 'title', relatedModule: 'deliveryType')]
    public function types(): HasMany
    {
        return $this->hasMany(DeliveryType::class);
    }

    /**
     * The carrier's own address classifier, cached by
     * DeliveryAddressSyncService — deliberately not a #[Field]/#[Relation]
     * admin relation (tens of thousands of rows per carrier, no consumer in
     * the admin table UI), only used by the checkout's city/warehouse
     * picker (see Http\Controllers\Public\DeliveryAddressController).
     */
    public function cities(): HasMany
    {
        return $this->hasMany(DeliveryCity::class);
    }

    public $translatable = ['title', 'description'];

    protected $fillable = ['title', 'description', 'key', 'image', 'publish'];

    protected $casts = ['publish' => 'boolean'];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }
}
