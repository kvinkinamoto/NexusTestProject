<?php

namespace App\Nexus\Modules\PromoCode\Models;

use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\PromoCode\Requests\AdminStoreRequest;
use App\Nexus\Modules\PromoCode\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * A customer-entered code that activates a DiscountRule — the code-activated
 * sibling of Promotion (always-on). Simplified from ZentaraStartProject:
 * dropped shop-or-global scoping (single-shop-per-install).
 */
#[Module(
    name: 'promoCode',
    label: 'Promo Codes',
    icon: 'solar:ticket-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by code', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:ticket-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class PromoCode extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Code', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Code', required: true, rules: ['max:64'])]
    protected $code;

    #[Field(type: 'relation', section: 'main', label: 'Discount rule', required: true)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function discountRule(): BelongsTo
    {
        return $this->belongsTo(DiscountRule::class, 'discount_rule_id');
    }

    #[Column(label: 'Starts at')]
    #[Field(type: 'datetime', section: 'settings', label: 'Starts at', required: false)]
    protected $starts_at;

    #[Column(label: 'Expires at')]
    #[Field(type: 'datetime', section: 'settings', label: 'Expires at', required: false)]
    protected $expires_at;

    #[Field(type: 'number', section: 'settings', label: 'Usage limit (total)', required: false, rules: ['nullable', 'integer', 'min:1'])]
    protected $usage_limit;

    #[Field(type: 'number', section: 'settings', label: 'Usage limit (per user)', required: false, rules: ['nullable', 'integer', 'min:1'])]
    protected $usage_limit_per_user;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    protected $fillable = [
        'code', 'discount_rule_id', 'starts_at', 'expires_at',
        'usage_limit', 'usage_limit_per_user', 'publish',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'publish' => 'boolean',
    ];

    /**
     * Normalized so lookups are case-insensitive by construction — the value
     * stored and matched against is always upper-trimmed, never whatever
     * casing the admin/customer typed.
     */
    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtoupper(trim($value)),
        );
    }

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }

    /**
     * Computed, not stored — mirrors Promotion::getStatusAttribute() so both
     * modules stay consistent, even though a PromoCode is activated by a
     * code instead of being automatic.
     */
    public function getStatusAttribute(): string
    {
        if (! $this->publish) {
            return 'draft';
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return 'scheduled';
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return 'expired';
        }

        return 'active';
    }
}
