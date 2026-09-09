<?php

namespace App\Nexus\Modules\ShopProductType\Models;

use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\ShopProductType\Requests\AdminStoreRequest;
use App\Nexus\Modules\ShopProductType\Requests\AdminUpdateRequest;
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
 * A product classification label (Simple, Variable, Digital, ...) — kept as
 * thin as the ZentaraStartProject original really was: just a name. The
 * source module's directory implied an attribute-options association via a
 * leftover `attribute_options_field.blade.php` view, but its own
 * ModuleConfiguration never actually wired one up — dead scaffolding, not
 * ported. NestedSet/SoftDeletes dropped for the same reason as the other
 * ported catalog modules (vestigial, unused).
 */
#[Module(
    name: 'shopProductType',
    label: 'Product Types',
    icon: 'solar:widget-4-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'base', icon: 'solar:widget-4-bold')]
#[Section(name: 'products_section', column: 'left', type: 'base', icon: 'solar:box-bold')]
class ShopProductType extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, translated: true)]
    protected $name;

    #[Field(type: 'relationManager', section: 'products_section', label: 'Products', required: false)]
    #[Relation(show: 'name', relatedModule: 'shopProduct')]
    public function products(): HasMany
    {
        return $this->hasMany(ShopProduct::class, 'type_id');
    }

    public $translatable = ['name'];

    protected $fillable = ['name'];
}
