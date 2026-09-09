<?php

namespace App\Nexus\Modules\Slider\Models;

use App\Nexus\Concerns\HasAutoSlug;
use App\Nexus\Modules\Slider\Requests\AdminStoreRequest;
use App\Nexus\Modules\Slider\Requests\AdminUpdateRequest;
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
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

#[Module(
    name: 'slider',
    label: 'Sliders',
    icon: 'solar:gallery-wide-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'columns_2', icon: 'solar:gallery-wide-bold')]
#[Section(name: 'slides_section', column: 'left', type: 'base', icon: 'solar:gallery-bold')]
class Slider extends Model
{
    use HasAttributeSchemaProperties;
    use HasAutoSlug;

    protected function slugColumn(): string
    {
        return 'key';
    }

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, rules: ['max:255'], apiExpose: true)]
    protected $name;

    #[Column(label: 'Key', sortable: true, searchable: true)]
    #[Field(type: 'slug', section: 'main', label: 'Key', required: false, slugSource: 'name', apiExpose: true)]
    protected $key;

    #[Column(label: 'Active', action: 'boolToggle', fieldName: 'is_active')]
    #[Field(type: 'boolean', section: 'main', label: 'Is active', required: false, default: true, apiExpose: true)]
    protected $is_active;

    /**
     * Slides edited inline as a repeater on the Slider's own edit screen —
     * same mechanism as Form::fields()/FormField. A slider groups its own
     * slides, so several sliders (e.g. "homepage" and "checkout-promo") can
     * each hold a different set and be published independently via is_active
     * above.
     */
    #[Field(type: 'repeater', section: 'slides_section', label: 'Slides', required: false)]
    #[Relation(type: 'hasMany')]
    #[RepeaterField(name: 'image', type: 'image', label: 'Image', required: true, width: '120px')]
    #[RepeaterField(name: 'title', type: 'string', label: 'Title', required: false)]
    #[RepeaterField(name: 'subtitle', type: 'string', label: 'Subtitle', required: false)]
    #[RepeaterField(name: 'link_url', type: 'string', label: 'Link URL', required: false)]
    #[RepeaterField(name: 'link_text', type: 'string', label: 'Link text', required: false)]
    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class)->orderBy('order');
    }

    protected $fillable = [
        'name', 'key', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
