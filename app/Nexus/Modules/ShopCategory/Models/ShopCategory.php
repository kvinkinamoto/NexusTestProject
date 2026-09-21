<?php

namespace App\Nexus\Modules\ShopCategory\Models;

use App\Nexus\Concerns\HasAutoSlug;
use App\Nexus\Modules\ShopCategory\Requests\AdminStoreRequest;
use App\Nexus\Modules\ShopCategory\Requests\AdminUpdateRequest;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;
use Spatie\Translatable\HasTranslations;

/**
 * Simplified from ZentaraStartProject's ShopCategory: dropped the horizontal
 * hero image, the video/gallery-images relations (no video-transcoding
 * pipeline exists in Nexus), Tags, meta_h1/meta_robots (kept meta_title/
 * meta_description only, matching Page's own SEO field set), SoftDeletes and
 * multi-tenant BelongsToShop (single-shop-per-install, see project memory).
 * Parent/child tree kept — unlike other ported catalog modules, category
 * hierarchy is a real expected feature, not vestigial.
 */
#[Module(
    name: 'shopCategory',
    label: 'Categories',
    icon: 'solar:widget-2-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:folder-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
#[Section(name: 'seo_section', column: 'right', type: 'base', icon: 'solar:magnifer-bold')]
#[Section(name: 'products_section', column: 'left', type: 'base', icon: 'solar:box-bold')]
class ShopCategory extends Model
{
    use HasAttributeSchemaProperties, HasAutoSlug, HasTranslations, NodeTrait;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, rules: ['max:255'], translated: true)]
    protected $name;

    #[Column(label: 'Slug', sortable: true, searchable: true)]
    #[Field(type: 'slug', section: 'main', label: 'Slug', required: false, slugSource: 'name')]
    protected $slug;

    #[Field(type: 'text', section: 'main', label: 'Short description', required: false)]
    protected $short_description;

    #[Field(type: 'text', section: 'main', label: 'Description', required: false)]
    protected $description;

    #[Column(label: 'Image', customField: 'showImage')]
    #[Field(type: 'image', section: 'settings', label: 'Image', required: false)]
    protected $image;

    #[Field(type: 'relation', section: 'settings', label: 'Parent category', required: false)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Free-form label grouping ROOT categories in a storefront header
     * mega-menu (e.g. "Electronics", "Home & Garden") — ignored for
     * non-root categories and independent of the real parent/child tree
     * (NodeTrait) that already drives catalog pages/breadcrumbs. Kept as a
     * plain string rather than an enum so any install can define its own
     * grouping labels without editing PHP.
     */
    #[Column(label: 'Nav group', sortable: true)]
    #[Field(type: 'string', section: 'settings', label: 'Nav group', required: false, rules: ['max:255'])]
    protected $nav_group;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    #[Field(type: 'relationManager', section: 'products_section', label: 'Products', required: false)]
    #[Relation(show: 'name', relatedModule: 'shopProduct')]
    public function products(): HasMany
    {
        return $this->hasMany(ShopProduct::class, 'category_id');
    }

    #[Field(type: 'string', section: 'seo_section', label: 'Meta title', required: false, rules: ['max:255'])]
    protected $meta_title;

    #[Field(type: 'text', section: 'seo_section', label: 'Meta description', required: false)]
    protected $meta_description;

    public $translatable = ['name'];

    protected $fillable = [
        'name', 'slug', 'short_description', 'description', 'image', 'parent_id', 'nav_group',
        'publish', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'publish' => 'boolean',
    ];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }

    public function displayName(): string
    {
        return $this->name;
    }
}
