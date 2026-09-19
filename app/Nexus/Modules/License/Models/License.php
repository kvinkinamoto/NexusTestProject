<?php

namespace App\Nexus\Modules\License\Models;

use App\Models\User;
use App\Nexus\Modules\License\Enums\LicenseStatus;
use App\Nexus\Modules\License\Requests\AdminStoreRequest;
use App\Nexus\Modules\License\Requests\AdminUpdateRequest;
use App\Nexus\Modules\LicenseActivation\Models\LicenseActivation;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
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

#[Module(
    name: 'license',
    label: 'Ліцензії',
    icon: 'solar:key-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
    requires: ['ShopProduct', 'Order'],
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Пошук за ключем', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'right', type: 'base', icon: 'solar:key-bold')]
#[Section(name: 'relations_section', column: 'right', type: 'base', icon: 'solar:link-bold', tab: 'relations')]
class License extends Model
{
    use HasAttributeSchemaProperties;

    #[Field(type: 'relation', section: 'main', label: 'Користувач', required: true)]
    #[Relation(type: 'belongsTo', show: 'email', ajax: true)]
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Field(type: 'relation', section: 'main', label: 'Замовлення', required: true)]
    #[Relation(type: 'belongsTo', show: 'id')]
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    #[Field(type: 'relation', section: 'main', label: 'Продукт', required: true)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class);
    }

    #[Column(label: 'Ключ', sortable: true, searchable: true)]
    #[Field(type: 'string', section: 'main', label: 'Ключ', required: true, disabled: 'edit', rules: ['max:64'])]
    protected $license_key;

    #[Column(label: 'Статус')]
    #[Field(type: 'enum', section: 'main', label: 'Статус', required: true, enum: LicenseStatus::class)]
    protected $status;

    #[Column(label: 'Ліміт активацій')]
    #[Field(type: 'number', section: 'main', label: 'Ліміт активацій', required: true, rules: ['integer', 'min:1'], default: 1)]
    protected $max_activations;

    #[Field(type: 'relationManager', section: 'relations_section', label: 'Активації', required: false)]
    #[Relation(type: 'hasMany')]
    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class);
    }

    protected $fillable = [
        'user_id', 'order_id', 'product_id', 'license_key', 'status', 'max_activations',
    ];

    protected $casts = [
        'status' => LicenseStatus::class,
        'max_activations' => 'integer',
    ];

    public static function generateKey(): string
    {
        do {
            $segments = collect(range(1, 4))->map(fn () => Str::upper(Str::random(5)));
            $key = 'NXS-'.$segments->implode('-');
        } while (self::query()->where('license_key', $key)->exists());

        return $key;
    }

    public function isActive(): bool
    {
        return $this->status === LicenseStatus::ACTIVE;
    }
}
