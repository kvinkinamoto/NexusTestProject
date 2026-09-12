<?php

namespace App\Nexus\Modules\Role\Models;

use App\Nexus\Modules\Role\Requests\AdminStoreRequest;
use App\Nexus\Modules\Role\Requests\AdminUpdateRequest;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Translatable\HasTranslations;

#[Module(
    name: 'role',
    label: 'Роль',
    icon: 'solar:shield-user-bold',
    group: 'User',
    showInMenu: true,
    isTree: false,
    menuResolver: null,
    livewire: true
)]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'restore', label: 'Restore', icon: 'restore', isConfirm: true)]
#[TableAction(name: 'deletePermanent', label: 'Delete permanent', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Section(name: 'information', column: 'right', type: 'columns_2', icon: 'cart')]
#[Section(name: 'relations', column: 'left', type: 'base', icon: 'shield')]
class Role extends \Spatie\Permission\Models\Role
{
    #[Column(label: 'Name', sortable: true)]
    #[Field(type: 'string', section: 'information', label: 'First name')]
    protected $name;

    #[Field(type: 'string', section: 'information', label: 'Display name', translated: true)]
    protected $display_name;

    #[Field(type: 'relation', section: 'relations', label: 'Permissions')]
    #[Relation(type: 'bELONGSTOMANY', show: 'display_name')]
    protected $permissions;

    #[Field(type: 'relation', section: 'relations', label: 'Users')]
    #[Relation(type: 'bELONGSTOMANY', show: 'name')]
    protected $users;

    #[Column(label: 'ID', sortable: true)]
    protected $id;

    use HasAttributeSchemaProperties, HasTranslations;

    public $translatable = ['display_name'];

    protected $fillable = [
        'name',
        'display_name',
        'guard_name',
    ];
}
