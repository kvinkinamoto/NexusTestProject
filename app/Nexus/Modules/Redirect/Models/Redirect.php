<?php

namespace App\Nexus\Modules\Redirect\Models;

use App\Nexus\Modules\Redirect\Enums\RedirectStatusCode;
use App\Nexus\Modules\Redirect\Requests\AdminStoreRequest;
use App\Nexus\Modules\Redirect\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

#[Module(
    name: 'redirect',
    label: 'Redirects',
    icon: 'solar:signpost-2-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:signpost-2-bold')]
class Redirect extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'From', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'From path', required: true, rules: ['max:255'])]
    protected $from_path;

    #[Column(label: 'To', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'To path or URL', required: true, rules: ['max:2048'])]
    protected $to_path;

    #[Column(label: 'Type')]
    #[Field(type: 'enum', section: 'main', label: 'Redirect type', required: false, enum: RedirectStatusCode::class, default: 301)]
    protected $status_code;

    #[Column(label: 'Active', action: 'boolToggle', fieldName: 'is_active')]
    #[Field(type: 'boolean', section: 'main', label: 'Is active', required: false, default: true)]
    protected $is_active;

    protected $fillable = [
        'from_path',
        'to_path',
        'status_code',
        'is_active',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'is_active' => 'boolean',
    ];
}
