<?php

namespace App\Nexus\Modules\Cart\Models;

use App\Models\User;
use App\Nexus\Modules\Cart\Requests\AdminStoreRequest;
use App\Nexus\Modules\Cart\Requests\AdminUpdateRequest;
use App\Nexus\Modules\Cart\Services\CartService;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

/**
 * Kept deliberately thin compared to ZentaraStartProject's admin view (which
 * had custom `products_count`/`cart_total`/`user` index-field partials and a
 * product-search picker — real Blade views this port has no equivalent for,
 * and speculative without a real support/ops workflow asking for them yet).
 * The module exists mainly so admins can see who has an active cart and
 * clear one; the real logic lives in CartService, exercised by the public
 * `/api/cart/*` endpoints (routes/web.php) a storefront theme would call.
 * SoftDeletes/BelongsToShop dropped, matching every other ported module.
 */
#[Module(
    name: 'cart',
    label: 'Carts',
    icon: 'solar:cart-3-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
)]
#[TableAction(name: 'edit', label: 'View', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Section(name: 'main', column: 'left', type: 'base', icon: 'solar:cart-3-bold')]
class Cart extends Model
{
    use HasAttributeSchemaProperties;

    #[Column(label: 'User')]
    #[Field(type: 'relation', section: 'main', label: 'User', required: false)]
    #[Relation(type: 'belongsTo', show: 'email')]
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(CartProduct::class);
    }

    /**
     * Not exposed as an admin `#[Field]` — set via CartService::applyPromoCode()/
     * removePromoCode() from the public API, same as ZentaraStartProject.
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function getTotal(): float
    {
        return app(CartService::class)->getBreakdownForCart($this)['total_price'];
    }

    protected $fillable = ['user_id', 'session_id', 'promo_code_id'];
}
