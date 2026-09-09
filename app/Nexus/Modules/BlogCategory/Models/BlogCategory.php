<?php

namespace App\Nexus\Modules\BlogCategory\Models;

use App\Nexus\Concerns\HasAutoSlug;
use App\Nexus\Modules\BlogCategory\Requests\AdminStoreRequest;
use App\Nexus\Modules\BlogCategory\Requests\AdminUpdateRequest;
use App\Nexus\Modules\BlogPost\Models\BlogPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

#[Module(
    name: 'blogcategory',
    label: 'Blog Categories',
    icon: 'solar:folder-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:box-bold')]
#[Section(name: 'posts_section', column: 'left', type: 'base', icon: 'solar:document-text-bold')]
class BlogCategory extends Model
{
    use HasAttributeSchemaProperties;
    use HasAutoSlug;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, apiExpose: true)]
    protected $name;

    #[Column(label: 'Slug', sortable: true, searchable: true)]
    #[Field(type: 'slug', section: 'main', label: 'Slug', required: false, slugSource: 'name', apiExpose: true)]
    protected $slug;

    #[Field(type: 'text', section: 'main', label: 'Description', required: false, apiExpose: true)]
    protected $description;

    #[Field(type: 'relationManager', section: 'posts_section', label: 'Posts', required: false)]
    #[Relation(show: 'title', relatedModule: 'blogPost')]
    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'blog_category_id');
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];
}
