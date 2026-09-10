<?php

namespace App\Nexus\Modules\ShopProduct\Models;

use App\Nexus\Concerns\HasAutoSlug;
use App\Nexus\Modules\Currency\Services\CurrencyService;
use App\Nexus\Modules\ShopAttribute\Models\AttributeOption;
use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\ShopProduct\Requests\AdminStoreRequest;
use App\Nexus\Modules\ShopProduct\Requests\AdminUpdateRequest;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductPriceAction;
use App\Nexus\Modules\ShopProductType\Models\ShopProductType;
use App\Nexus\Modules\ShopProductVariation\Models\VariationGroup;
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
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Simplified from ZentaraStartProject's ShopProduct: dropped SoftDeletes,
 * Taggable, video/gallery-images (no transcoding pipeline in Nexus), the
 * meta_h1/meta_robots SEO fields (kept meta_title/meta_description only,
 * matching Page/ShopCategory), and the 3-table ProductAttribute/
 * ProductAttributeOption EAV structure — this
 * collapses to a single `attributeOptions` belongsToMany straight onto
 * AttributeOption (a chosen option already identifies its own attribute via
 * AttributeOption::attribute_id), losing only the rare ability to offer
 * several option values of the *same* attribute without that being a
 * variation. `variation_group_id`/`variationGroup` deliberately not added
 * yet — see the ShopProductVariation module built right after this one,
 * which adds it via an additive migration once `variation_groups` exists.
 */
#[Module(
    name: 'shopProduct',
    label: 'Products',
    icon: 'solar:box-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:box-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
#[Section(name: 'relations_section', column: 'left', type: 'base', icon: 'solar:link-bold')]
#[Section(name: 'seo_section', column: 'right', type: 'base', icon: 'solar:magnifer-bold')]
class ShopProduct extends Model
{
    use HasAttributeSchemaProperties, HasAutoSlug;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, rules: ['max:255'])]
    protected $name;

    #[Column(label: 'Slug', sortable: true, searchable: true)]
    #[Field(type: 'slug', section: 'main', label: 'Slug', required: false, slugSource: 'name')]
    protected $slug;

    #[Field(type: 'text', section: 'main', label: 'Short description', required: false)]
    protected $short_description;

    #[Field(type: 'text', section: 'main', label: 'Description', required: false)]
    protected $description;

    #[Column(label: 'Image', customField: 'showImage')]
    #[Field(type: 'image', section: 'main', label: 'Image', required: false)]
    protected $image;

    #[Field(type: 'relation', section: 'settings', label: 'Category', required: false)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'category_id');
    }

    #[Field(type: 'relation', section: 'settings', label: 'Type', required: false)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function type(): BelongsTo
    {
        return $this->belongsTo(ShopProductType::class, 'type_id');
    }

    #[Column(label: 'Price', sortable: true)]
    #[Field(type: 'number', section: 'settings', label: 'Price', required: true, rules: ['numeric', 'min:0'])]
    protected $price;

    #[Field(type: 'number', section: 'settings', label: 'Discount price', required: false, rules: ['nullable', 'numeric', 'min:0', 'lt:price'])]
    protected $price_discount;

    #[Field(type: 'number', section: 'settings', label: 'Bonuses', required: false, rules: ['nullable', 'numeric', 'min:0'])]
    protected $bonuses;

    #[Column(label: 'Stock')]
    #[Field(type: 'number', section: 'settings', label: 'Stock', required: false, rules: ['nullable', 'integer', 'min:0'])]
    protected $stock;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    #[Field(type: 'relation', section: 'settings', label: 'Variation group', required: false)]
    #[Relation(type: 'belongsTo', show: 'id')]
    public function variationGroup(): BelongsTo
    {
        return $this->belongsTo(VariationGroup::class, 'variation_group_id');
    }

    #[Field(type: 'relation', section: 'relations_section', label: 'Attribute values', required: false)]
    #[Relation(type: 'belongsToMany', show: 'value', ajax: true)]
    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class, 'product_attribute_options', 'product_id', 'attribute_option_id')->withTimestamps();
    }

    #[Field(type: 'relation', section: 'relations_section', label: 'Related products', required: false)]
    #[Relation(type: 'belongsToMany', show: 'name', ajax: true)]
    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'products_related', 'product_id', 'related_id')->withTimestamps();
    }

    #[Field(type: 'string', section: 'seo_section', label: 'Meta title', required: false, rules: ['max:255'])]
    protected $meta_title;

    #[Field(type: 'text', section: 'seo_section', label: 'Meta description', required: false)]
    protected $meta_description;

    protected $fillable = [
        'name', 'slug', 'short_description', 'description', 'image',
        'category_id', 'type_id', 'variation_group_id', 'price', 'price_discount', 'bonuses', 'stock', 'publish',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_discount' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'stock' => 'integer',
        'publish' => 'boolean',
    ];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }

    /**
     * Effective selling price for the given quantity — delegates to
     * PromotionEngine via ResolveProductPriceAction (Pricing rules phase),
     * which already accounts for `price_discount` as one candidate among any
     * active Promotion/PromoCode discount rules and picks the best price.
     */
    public function effectivePrice(int $quantity = 1): float
    {
        return ResolveProductPriceAction::handle($this, $quantity)['price'];
    }

    public function priceConverted(int $quantity = 1): float
    {
        return CurrencyService::convertToSessionCurrency($this->effectivePrice($quantity));
    }
}
