<?php

namespace App\Nexus\Modules\BlogPost\Models;

use App\Nexus\Concerns\HasAutoSlug;
use App\Nexus\Modules\BlogCategory\Models\BlogCategory;
use App\Nexus\Modules\BlogPost\Requests\AdminStoreRequest;
use App\Nexus\Modules\BlogPost\Requests\AdminUpdateRequest;
use App\Nexus\Modules\BlogPost\Services\BlogPostMenuResolver;
use App\Nexus\Modules\Tag\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    name: 'blogpost',
    label: 'Blog Posts',
    icon: 'solar:document-text-bold',
    group: 'Site',
    showInMenu: true,
    livewire: true,
    requires: ['BlogCategory', 'Tag'],
    menuResolver: BlogPostMenuResolver::class,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'columns_2', icon: 'solar:document-text-bold')]
#[Section(name: 'publish_section', column: 'right', type: 'base', icon: 'solar:calendar-bold')]
#[Section(name: 'seo_section', column: 'right', type: 'base', icon: 'solar:magnifer-bold')]
#[Section(name: 'relations_section', column: 'right', type: 'base', icon: 'solar:tag-bold')]
#[Section(name: 'content_section', column: 'left', type: 'base', icon: 'solar:notebook-bold')]
#[Section(name: 'media_section', column: 'left', type: 'base', icon: 'solar:gallery-bold')]
class BlogPost extends Model
{
    use HasAttributeSchemaProperties;
    use HasAutoSlug;

    protected function slugSourceColumn(): string
    {
        return 'title';
    }

    #[Column(label: 'Title', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Title', required: true, rules: ['max:255'], apiExpose: true)]
    protected $title;

    #[Field(type: 'slug', section: 'main', label: 'Slug', required: false, slugSource: 'title', apiExpose: true)]
    protected $slug;

    #[Column(label: 'Published', action: 'boolToggle', fieldName: 'is_published')]
    #[Field(type: 'boolean', section: 'publish_section', label: 'Is published', required: false, default: false, apiExpose: true)]
    protected $is_published;

    #[Column(label: 'Published at', sortable: true)]
    #[Field(type: 'datetime', section: 'publish_section', label: 'Published at', required: false, apiExpose: true)]
    protected $published_at;

    #[Field(type: 'string', section: 'seo_section', label: 'Meta title', required: false, rules: ['max:255'], apiExpose: true)]
    protected $meta_title;

    #[Field(type: 'text', section: 'seo_section', label: 'Meta description', required: false, apiExpose: true)]
    protected $meta_description;

    #[Field(type: 'text', section: 'content_section', label: 'Excerpt', required: false, apiExpose: true)]
    protected $excerpt;

    #[Field(type: 'text', section: 'content_section', label: 'Content', required: false, editor: true, apiExpose: true)]
    protected $content;

    #[Column(label: 'Image', customField: 'image')]
    #[Field(type: 'image', section: 'media_section', label: 'Featured image', required: false, apiExpose: true)]
    protected $featured_image;

    #[Column(label: 'Category', sortable: true)]
    #[Field(type: 'relation', section: 'relations_section', label: 'Category', required: false, apiExpose: true)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    #[Field(type: 'relation', section: 'relations_section', label: 'Tags', required: false, apiExpose: true)]
    #[Relation(type: 'belongsToMany', show: 'name', ajax: true)]
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag', 'blog_post_id', 'tag_id');
    }

    protected $fillable = [
        'title', 'slug', 'is_published', 'published_at',
        'meta_title', 'meta_description', 'excerpt', 'content',
        'featured_image', 'blog_category_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
