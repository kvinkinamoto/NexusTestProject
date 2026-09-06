<?php

namespace App\Nexus\Modules\ConfirmFixture\Models;

use App\Nexus\Modules\ConfirmFixture\Requests\AdminStoreRequest;
use App\Nexus\Modules\ConfirmFixture\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Permanent test fixture, not a real business module — exists solely so
 * tests/Feature/Nexus/TableActionConfirmTest.php has a module with a custom
 * (non delete/restore/deletePermanent/duplicate) #[TableAction(isConfirm: true)]
 * to render, since no real module happens to have one. Hidden from the admin
 * menu (showInMenu: false); reachable only via Livewire::test() by module name.
 */
#[Module(
    name: 'confirmfixture',
    label: 'ConfirmFixture',
    icon: 'solar:box-bold',
    group: 'Site',
    showInMenu: false,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableAction(name: 'archive', label: 'Archive', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:box-bold')]
class ConfirmFixture extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true)]
    protected $name;

    protected $fillable = [
        'name',
    ];
}
