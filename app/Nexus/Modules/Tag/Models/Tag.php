<?php

namespace App\Nexus\Modules\Tag\Models;

use App\Nexus\Concerns\HasAutoSlug;
use App\Nexus\Modules\Tag\Requests\AdminStoreRequest;
use App\Nexus\Modules\Tag\Requests\AdminUpdateRequest;
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
    name: 'tag',
    label: 'Tag',
    icon: 'solar:hashtag-bold',
    group: 'Site',
    showInMenu: false,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:box-bold')]
class Tag extends Model
{
    use HasAttributeSchemaProperties;
    use HasAutoSlug;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, apiExpose: true)]
    protected $name;

    #[Column(label: 'Slug', sortable: true, searchable: true)]
    #[Field(type: 'slug', section: 'main', label: 'Slug', required: false, slugSource: 'name', apiExpose: true)]
    protected $slug;

    protected $fillable = [
        'name',
        'slug',
    ];
}
