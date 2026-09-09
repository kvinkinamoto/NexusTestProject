<?php

namespace App\Nexus\Modules\Promotion\Models;

use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\Promotion\Requests\AdminStoreRequest;
use App\Nexus\Modules\Promotion\Requests\AdminUpdateRequest;
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
use Spatie\Translatable\HasTranslations;

/**
 * An always-on (no code needed) activation of a DiscountRule, scheduled by
 * starts_at/expires_at and gated by `publish`. Simplified from
 * ZentaraStartProject: dropped the `image` promotional banner (no gallery
 * concept needed yet), ActivityLog integration (Phase 11) and shop-or-global
 * scoping (single-shop-per-install).
 */
#[Module(
    name: 'promotion',
    label: 'Promotions',
    icon: 'solar:sale-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'columns_2', icon: 'solar:sale-bold')]
#[Section(name: 'settings', column: 'right', type: 'base', icon: 'solar:settings-bold')]
class Promotion extends Model
{
    use HasAttributeSchemaProperties, HasTranslations;

    #[Column(label: 'Name', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Name', required: true, translated: true)]
    protected $name;

    #[Field(type: 'string', section: 'main', label: 'Label (badge text)', required: false, translated: true)]
    protected $label;

    #[Field(type: 'relation', section: 'main', label: 'Discount rule', required: true)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function discountRule(): BelongsTo
    {
        return $this->belongsTo(DiscountRule::class, 'discount_rule_id');
    }

    #[Column(label: 'Starts at', sortable: true)]
    #[Field(type: 'datetime', section: 'settings', label: 'Starts at', required: false)]
    protected $starts_at;

    #[Column(label: 'Expires at', sortable: true)]
    #[Field(type: 'datetime', section: 'settings', label: 'Expires at', required: false)]
    protected $expires_at;

    #[Column(label: 'Publish', action: 'boolToggle', fieldName: 'publish')]
    #[Field(type: 'boolean', section: 'settings', label: 'Publish', required: false, default: true)]
    protected $publish;

    public $translatable = ['name', 'label'];

    protected $fillable = [
        'name', 'label', 'discount_rule_id', 'starts_at', 'expires_at', 'publish',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'publish' => 'boolean',
    ];

    public function scopePublish($query)
    {
        return $query->where('publish', true);
    }

    /**
     * Computed, not stored — avoids the admin having to keep a status field
     * in sync with publish/starts_at/expires_at.
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

    /**
     * Whether this promotion is currently in effect — publish=true and,
     * if set, within the starts_at/expires_at window. Used by
     * PromotionEngine to select which promotions' rules apply right now.
     */
    public function isCurrentlyActive(): bool
    {
        return $this->getStatusAttribute() === 'active';
    }
}
