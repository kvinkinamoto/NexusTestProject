<?php

namespace App\Nexus\Modules\Bonus\Models;

use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\Bonus\Requests\AdminStoreRequest;
use App\Nexus\Modules\Bonus\Requests\AdminUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * One row per BonusTypeEnum case, configuring how much (`amount`), how long
 * to wait before it's spendable (`delay_days`) and how long it stays valid
 * (`validity_days`) — see BonusService's docblock for the one exception
 * (PURCHASE.amount is ignored; the caller supplies the actual sum).
 * Simplified from ZentaraStartProject: dropped BelongsToShopOrGlobal/shop_id
 * (single-shop-per-install) and SoftDeletes, matching every other module in
 * this port.
 */
#[Module(
    name: 'bonus',
    label: 'Bonus Rules',
    icon: 'solar:gift-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by type', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'base', icon: 'solar:gift-bold')]
class BonusRule extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'Type', sortable: true, searchable: true)]
    #[Field(type: 'enum', section: 'main', label: 'Type', required: true, enum: BonusTypeEnum::class)]
    protected $type;

    #[Column(label: 'Amount', sortable: true)]
    #[Field(type: 'number', section: 'main', label: 'Amount', required: true, rules: ['numeric', 'min:0'])]
    protected $amount;

    #[Column(label: 'Delay days')]
    #[Field(type: 'number', section: 'main', label: 'Delay days', required: false, default: 0, rules: ['nullable', 'integer', 'min:0'])]
    protected $delay_days;

    #[Column(label: 'Validity days')]
    #[Field(type: 'number', section: 'main', label: 'Validity days', required: false, rules: ['nullable', 'integer', 'min:0'])]
    protected $validity_days;

    #[Column(label: 'Active', action: 'boolToggle', fieldName: 'is_active')]
    #[Field(type: 'boolean', section: 'main', label: 'Active', required: false, default: false)]
    protected $is_active;

    protected $fillable = ['type', 'amount', 'delay_days', 'validity_days', 'is_active'];

    protected $casts = [
        'type' => BonusTypeEnum::class,
        'amount' => 'decimal:2',
        'delay_days' => 'integer',
        'validity_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function forType(string $type): ?self
    {
        return static::query()->where('type', $type)->where('is_active', true)->first();
    }
}
