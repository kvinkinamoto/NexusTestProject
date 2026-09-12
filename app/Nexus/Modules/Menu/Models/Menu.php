<?php

namespace App\Nexus\Modules\Menu\Models;

use App\Nexus\Concerns\HasAutoSlug;
use App\Nexus\Modules\Menu\Requests\AdminStoreRequest;
use App\Nexus\Modules\Menu\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

#[Module(
    name: 'menu',
    label: 'Menus',
    icon: 'solar:hamburger-menu-bold',
    group: 'Site',
    showInMenu: false,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'columns_2', icon: 'solar:hamburger-menu-bold')]
#[Section(name: 'items_section', column: 'left', type: 'base', icon: 'solar:list-bold')]
class Menu extends Model
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
     * The nested item tree has no admin-form field type of its own (nothing
     * in the attribute system models lft/rgt) — embedded instead as an
     * arbitrary Blade slot (a nested Livewire component, MenuItemsManager)
     * via the built-in #[Field(type: 'view')] hook, same mechanism as
     * Demo::$price_preview. Virtual: no DB column, never in $fillable.
     */
    #[Field(type: 'view', section: 'items_section', label: 'Items', required: false, view: 'menu::admin.components.items-manager')]
    protected $items_manager;

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    protected $fillable = [
        'name', 'key', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
