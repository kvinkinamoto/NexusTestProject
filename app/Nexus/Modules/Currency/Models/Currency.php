<?php

namespace App\Nexus\Modules\Currency\Models;

use App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition;
use App\Nexus\Modules\Currency\Requests\AdminStoreRequest;
use App\Nexus\Modules\Currency\Requests\AdminUpdateRequest;
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

#[Module(
    name: 'currency',
    label: 'Currency',
    icon: 'solar:dollar-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by code or name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'general', column: 'left', type: 'base', icon: 'solar:dollar-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class Currency extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    #[Column(label: 'Code', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'general', label: 'Code', required: true, rules: ['max:10'])]
    protected $code;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'general', label: 'Name', required: false, translated: true)]
    protected $name;

    #[Column(label: 'Symbol')]
    #[Field(type: 'string', section: 'general', label: 'Symbol', required: false, rules: ['max:10'])]
    protected $symbol;

    #[Field(type: 'enum', section: 'settings', label: 'Symbol position', required: true, enum: CurrencySymbolPosition::class, default: 'after')]
    protected $symbol_position;

    #[Column(label: 'Rate', sortable: true)]
    #[Field(type: 'number', section: 'settings', label: 'Rate', required: true, default: 1)]
    protected $rate;

    #[Column(label: 'Base', action: 'boolToggle', fieldName: 'is_base')]
    #[Field(type: 'boolean', section: 'settings', label: 'Is base', required: false)]
    protected $is_base;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    public $translatable = ['name'];

    protected $fillable = [
        'code', 'name', 'symbol', 'symbol_position', 'rate', 'is_base', 'publish',
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'publish' => 'boolean',
        'rate' => 'decimal:4',
    ];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }
}
