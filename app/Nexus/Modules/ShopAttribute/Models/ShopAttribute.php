<?php

namespace App\Nexus\Modules\ShopAttribute\Models;

use App\Nexus\Modules\ShopAttribute\Enums\AttributeType;
use App\Nexus\Modules\ShopAttribute\Requests\AdminStoreRequest;
use App\Nexus\Modules\ShopAttribute\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\RepeaterField;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Translatable\HasTranslations;

/**
 * A catalog attribute (Color, Size, Material, ...) products/variations pick
 * values from. `merges_catalog_cards` is carried over from ZentaraStartProject
 * as a stored flag only — its original meaning was "variations differing
 * only by this attribute collapse into one catalog card with a swatch
 * switcher"; no catalog listing UI exists in Nexus yet to act on it.
 *
 * Simplified from the Zentara original: dropped the NestedSet parent/child
 * tree (attributes are a flat list here — every other Nexus catalog module
 * ported so far found NestedSet vestigial, unused by any real parent/child
 * UI) and SoftDeletes (no other Nexus module uses it).
 */
#[Module(
    name: 'shopAttribute',
    label: 'Attributes',
    icon: 'solar:tag-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:tag-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
#[Section(name: 'options_section', column: 'bottom', type: 'base', icon: 'solar:list-bold')]
class ShopAttribute extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, translated: true)]
    protected $name;

    #[Field(type: 'text', section: 'main', label: 'Description', required: false, translated: true)]
    protected $description;

    #[Column(label: 'Type')]
    #[Field(type: 'enum', section: 'settings', label: 'Type', required: true, enum: AttributeType::class, default: 'text')]
    protected $type;

    #[Field(type: 'string', section: 'settings', label: 'Key', required: false, rules: ['nullable', 'alpha_dash', 'max:255'])]
    protected $key;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    #[Column(label: 'Merges catalog cards', action: 'boolToggle', fieldName: 'merges_catalog_cards')]
    #[Field(type: 'boolean', section: 'settings', label: 'Merges catalog cards', required: false, default: false)]
    protected $merges_catalog_cards;

    #[Field(type: 'repeater', section: 'options_section', label: 'Options', required: false)]
    #[Relation(type: 'hasMany')]
    #[RepeaterField(name: 'value', type: 'string', label: 'Value', required: true)]
    #[RepeaterField(name: 'color', type: 'color', label: 'Color', required: false)]
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class, 'attribute_id')->orderBy('order');
    }

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'name', 'description', 'type', 'key', 'publish', 'merges_catalog_cards',
    ];

    protected $casts = [
        'publish' => 'boolean',
        'merges_catalog_cards' => 'boolean',
    ];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }
}
