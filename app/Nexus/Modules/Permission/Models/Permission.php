<?php

namespace App\Nexus\Modules\Permission\Models;

use App\Nexus\Modules\Permission\Requests\AdminStoreRequest;
use App\Nexus\Modules\Permission\Requests\AdminUpdateRequest;
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
    name: 'permission',
    label: 'Дозвіл',
    icon: 'solar:key-bold',
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
class Permission extends \Spatie\Permission\Models\Permission
{
    #[Column(label: 'Name', sortable: true)]
    #[Field(type: 'string', section: 'information', label: 'First name')]
    protected $name;

    #[Field(type: 'string', section: 'information', label: 'Display name', translated: true)]
    protected $display_name;

    #[Field(type: 'relation', section: 'relations', label: 'Roles')]
    #[Relation(type: 'bELONGSTOMANY', show: 'display_name')]
    protected $roles;

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
