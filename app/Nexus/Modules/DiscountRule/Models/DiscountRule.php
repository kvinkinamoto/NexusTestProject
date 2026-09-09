<?php

namespace App\Nexus\Modules\DiscountRule\Models;

use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Enums\ScopeTarget;
use App\Nexus\Modules\DiscountRule\Enums\ValueType;
use App\Nexus\Modules\DiscountRule\Requests\AdminStoreRequest;
use App\Nexus\Modules\DiscountRule\Requests\AdminUpdateRequest;
use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
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
 * The reusable discount "engine rule" a Promotion (always-on) or PromoCode
 * (code-activated) points at — see PromotionEngine, which is what actually
 * interprets `type`/`mechanics` at price-resolution time.
 *
 * Simplified from ZentaraStartProject: the 4 bespoke mechanics field types
 * (BundleItemsRepeaterField/ProductPricesRepeaterField/TierRulesRepeaterField/
 * MechanicsConfigField, each with its own Blade widget) collapse into one
 * plain `#[Field(type: 'json')] $mechanics` — admins enter the type-specific
 * shape directly (e.g. `{"tier_rules":[{"qty_from":5,"discount":10}]}` for
 * quantity_discount). PromotionEngine itself is ported at full fidelity; only
 * the JSON-builder admin UI is cut. Dropped SoftDeletes, `customer_segment`
 * (Zentara's own later migration dropped it too — dead even there), and
 * shop-or-global scoping (single-shop-per-install, so every rule is
 * effectively "global" already).
 */
#[Module(
    name: 'discountRule',
    label: 'Discount Rules',
    icon: 'solar:tag-price-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:tag-price-bold')]
#[Section(name: 'scope_section', column: 'left', type: 'base', icon: 'solar:target-bold')]
#[Section(name: 'conditions_section', column: 'right', type: 'base', icon: 'solar:calendar-bold')]
#[Section(name: 'mechanics_section', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class DiscountRule extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, rules: ['max:255'])]
    protected $name;

    #[Column(label: 'Type')]
    #[Field(type: 'enum', section: 'main', label: 'Type', required: true, enum: DiscountRuleType::class, default: 'product_discount')]
    protected $type;

    #[Field(type: 'enum', section: 'main', label: 'Value type', required: false, enum: ValueType::class, default: 'percent')]
    protected $value_type;

    #[Column(label: 'Amount')]
    #[Field(type: 'number', section: 'main', label: 'Amount', required: false, rules: ['nullable', 'numeric', 'min:0'])]
    protected $amount;

    #[Column(label: 'Priority', sortable: true)]
    #[Field(type: 'number', section: 'main', label: 'Priority', required: false, default: 0)]
    protected $priority;

    #[Column(label: 'Stackable', action: 'boolToggle', fieldName: 'stackable')]
    #[Field(type: 'boolean', section: 'main', label: 'Stackable', required: false, default: false)]
    protected $stackable;

    #[Field(type: 'enum', section: 'scope_section', label: 'Scope target', required: true, enum: ScopeTarget::class, default: 'all')]
    protected $scope_target;

    #[Field(type: 'relation', section: 'scope_section', label: 'Category', required: false)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'category_id');
    }

    #[Field(type: 'relation', section: 'scope_section', label: 'Products', required: false)]
    #[Relation(type: 'belongsToMany', show: 'name', ajax: true)]
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ShopProduct::class, 'discount_rule_products', 'discount_rule_id', 'product_id');
    }

    #[Field(type: 'relation', section: 'scope_section', label: 'Excluded products', required: false)]
    #[Relation(type: 'belongsToMany', show: 'name', ajax: true)]
    public function excludedProducts(): BelongsToMany
    {
        return $this->belongsToMany(ShopProduct::class, 'discount_rule_excluded_products', 'discount_rule_id', 'product_id');
    }

    #[Field(type: 'number', section: 'conditions_section', label: 'Min cart amount', required: false, rules: ['nullable', 'numeric', 'min:0'])]
    protected $min_cart_amount;

    #[Field(type: 'number', section: 'conditions_section', label: 'Min quantity', required: false, rules: ['nullable', 'integer', 'min:1'])]
    protected $min_qty;

    #[Field(type: 'multiple_string', section: 'conditions_section', label: 'Days of week (0=Sun..6=Sat)', required: false)]
    protected $day_of_week;

    #[Field(type: 'time', section: 'conditions_section', label: 'Time from', required: false)]
    protected $time_from;

    #[Field(type: 'time', section: 'conditions_section', label: 'Time to', required: false)]
    protected $time_to;

    #[Field(type: 'json', section: 'mechanics_section', label: 'Mechanics (type-specific config)', required: false)]
    protected $mechanics;

    protected $fillable = [
        'name', 'type', 'value_type', 'amount', 'priority', 'stackable',
        'scope_target', 'category_id', 'min_cart_amount', 'min_qty',
        'day_of_week', 'time_from', 'time_to', 'mechanics',
    ];

    protected $casts = [
        'type' => DiscountRuleType::class,
        'value_type' => ValueType::class,
        'scope_target' => ScopeTarget::class,
        'amount' => 'decimal:2',
        'priority' => 'integer',
        'stackable' => 'boolean',
        'min_cart_amount' => 'decimal:2',
        'min_qty' => 'integer',
        'day_of_week' => 'array',
        'mechanics' => 'array',
    ];
}
