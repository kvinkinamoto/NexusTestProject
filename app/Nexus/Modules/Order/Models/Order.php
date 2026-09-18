<?php

namespace App\Nexus\Modules\Order\Models;

use App\Models\User;
use App\Nexus\Modules\Delivery\Models\Delivery;
use App\Nexus\Modules\DeliveryType\Models\DeliveryType;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Observers\OrderObserver;
use App\Nexus\Modules\Order\Requests\AdminStoreRequest;
use App\Nexus\Modules\Order\Requests\AdminUpdateRequest;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use App\Nexus\Modules\Shipment\Models\OrderShipment;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Nodex\Nexus\Attributes\Column;
use Nodex\Nexus\Attributes\Field;
use Nodex\Nexus\Attributes\Module;
use Nodex\Nexus\Attributes\Relation;
use Nodex\Nexus\Attributes\Requests;
use Nodex\Nexus\Attributes\Section;
use Nodex\Nexus\Attributes\Setting;
use Nodex\Nexus\Attributes\TableAction;
use Nodex\Nexus\Attributes\TableFilter;
use Nodex\Nexus\Attributes\TableGroupAction;
use Nodex\Nexus\Concerns\HasAttributeSchemaProperties;

/**
 * Simplified from ZentaraStartProject's Order: dropped SoftDeletes,
 * BelongsToShop (single-shop-per-install), the billing_* fields plus
 * `signature` (never read/written by any of the 3 payment drivers — dead
 * even in the old code) and currency_code (never actually wired even in the
 * old form). `bank_order_id` was added back via an additive migration once
 * Phase 6 (PaymentMethod) actually needed it. `delivery_method`/
 * `delivery_type`/`payment_method` are all validated against their real
 * tables now (Phase 6/7) but kept plain strings rather than real belongsTo
 * Fields, since the admin form always saves a picked row's id, not an
 * arbitrary local key — see `paymentMethod()`/`deliveryMethod()`/
 * `deliveryType()` below for the read-side relations application code
 * actually uses. OrderObserver ported at full fidelity, including the
 * `completed_at` purchase-bonus hook (Phase 8). Order line items (`products`) and the
 * status timeline (`history`) are checkout-engine-owned, not exposed as
 * admin-editable Fields — no equivalent of the old bespoke
 * custom_products/custom_history table widgets exists in the current field
 * system, and nothing needs to hand-edit them in the admin. `is_gift`/
 * `gift_recipient_email`/`gift_message`/`gift_code`/`recipient_user_id` are
 * additive (see GiftCodeService) — a gift order still goes through the
 * normal delivery/payment flow, `recipient_user_id` just also grants
 * whoever redeems the code ownership of the order for their own account.
 */
#[Module(
    name: 'order',
    label: 'Orders',
    icon: 'solar:cart-large-bold',
    group: 'Shop',
    showInMenu: true,
    livewire: true,
    requires: ['ShopProduct', 'OrderStatus', 'PaymentMethod', 'Delivery', 'DeliveryType'],
)]
#[TableAction(name: 'edit', label: 'Edit', icon: 'edit', isConfirm: false)]
#[TableAction(name: 'delete', label: 'Delete', icon: 'delete', isConfirm: true)]
#[TableGroupAction(name: 'deleteGroup', fieldName: 'delete')]
#[TableFilter(name: 'search', label: 'Search by phone or name', type: 'search')]
#[Requests(actions: ['store' => AdminStoreRequest::class, 'update' => AdminUpdateRequest::class])]
#[Setting(name: 'allow_guest_checkout', type: 'boolean', label: 'Allow guest checkout', default: true)]
#[Section(name: 'customer', column: 'left', type: 'columns_2', icon: 'solar:user-bold')]
#[Section(name: 'recipient', column: 'left', type: 'columns_2', icon: 'solar:user-bold')]
#[Section(name: 'delivery', column: 'left', type: 'base', icon: 'solar:delivery-bold')]
#[Section(name: 'gift', column: 'left', type: 'columns_2', icon: 'solar:gift-bold')]
#[Section(name: 'summary', column: 'right', type: 'base', icon: 'solar:bill-list-bold')]
#[Section(name: 'payment', column: 'right', type: 'base', icon: 'solar:card-bold')]
#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasAttributeSchemaProperties;

    #[Field(type: 'string', section: 'customer', label: 'First name', required: true, rules: ['max:255'])]
    protected $user_name;

    #[Field(type: 'string', section: 'customer', label: 'Last name', required: false, rules: ['max:255'])]
    protected $user_last_name;

    #[Column(label: 'Phone', sortable: true, searchable: true)]
    #[Field(type: 'phone', section: 'customer', label: 'Phone', required: true, rules: ['max:32'])]
    protected $user_phone;

    #[Field(type: 'email', section: 'customer', label: 'Email', required: false)]
    protected $user_email;

    #[Field(type: 'text', section: 'customer', label: 'Comment', required: false)]
    protected $user_comment;

    #[Field(type: 'boolean', section: 'customer', label: 'Needs phone call', required: false, default: true)]
    protected $needs_phone_call;

    #[Field(type: 'boolean', section: 'recipient', label: 'Another recipient', required: false, default: false)]
    protected $is_another_recipient;

    #[Field(type: 'string', section: 'recipient', label: 'Recipient first name', required: false, rules: ['max:255'])]
    protected $recipient_name;

    #[Field(type: 'string', section: 'recipient', label: 'Recipient last name', required: false, rules: ['max:255'])]
    protected $recipient_last_name;

    #[Field(type: 'phone', section: 'recipient', label: 'Recipient phone', required: false, rules: ['max:32'])]
    protected $recipient_phone;

    #[Field(type: 'string', section: 'delivery', label: 'Delivery method', required: false, rules: ['max:255'])]
    protected $delivery_method;

    #[Field(type: 'string', section: 'delivery', label: 'Delivery type', required: false, rules: ['max:255'])]
    protected $delivery_type;

    #[Field(type: 'string', section: 'delivery', label: 'City', required: false, rules: ['max:255'])]
    protected $delivery_city;

    #[Field(type: 'string', section: 'delivery', label: 'Address', required: false, rules: ['max:255'])]
    protected $delivery_address;

    #[Field(type: 'boolean', section: 'gift', label: 'Is a gift', required: false, default: false)]
    protected $is_gift;

    #[Field(type: 'email', section: 'gift', label: 'Gift recipient email', required: false)]
    protected $gift_recipient_email;

    #[Field(type: 'text', section: 'gift', label: 'Gift message', required: false)]
    protected $gift_message;

    #[Column(label: 'Gift code')]
    #[Field(type: 'string', section: 'gift', label: 'Gift code', required: false, disabled: true)]
    protected $gift_code;

    #[Field(type: 'relation', section: 'summary', label: 'User', required: false)]
    #[Relation(type: 'belongsTo', show: 'email', ajax: true)]
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Field(type: 'relation', section: 'gift', label: 'Resolved gift recipient', required: false)]
    #[Relation(type: 'belongsTo', show: 'email', ajax: true)]
    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    #[Column(label: 'Status')]
    #[Field(type: 'relation', section: 'summary', label: 'Status', required: false)]
    #[Relation(type: 'belongsTo', show: 'name')]
    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    #[Field(type: 'relation', section: 'summary', label: 'Promo code', required: false)]
    #[Relation(type: 'belongsTo', show: 'code')]
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }

    #[Column(label: 'Products price')]
    #[Field(type: 'number', section: 'summary', label: 'Products price', required: false, disabled: true)]
    protected $products_price;

    #[Field(type: 'number', section: 'summary', label: 'Delivery price', required: false, rules: ['nullable', 'numeric', 'min:0'])]
    protected $delivery_price;

    #[Column(label: 'Total price', sortable: true)]
    #[Field(type: 'number', section: 'summary', label: 'Total price', required: false, disabled: true)]
    protected $total_price;

    #[Field(type: 'text', section: 'summary', label: 'Admin comment', required: false)]
    protected $admin_comment;

    #[Field(type: 'string', section: 'payment', label: 'Payment method', required: false, rules: ['max:255'])]
    protected $payment_method;

    #[Column(label: 'Payment status')]
    #[Field(type: 'enum', section: 'payment', label: 'Payment status', required: false, enum: PaymentStatus::class, default: 1)]
    protected $payment_status;

    protected $fillable = [
        'user_id', 'promo_code_id', 'status_id',
        'user_name', 'user_last_name', 'user_phone', 'user_email', 'user_comment', 'needs_phone_call',
        'is_another_recipient', 'recipient_name', 'recipient_last_name', 'recipient_phone',
        'delivery_method', 'delivery_type', 'delivery_city', 'delivery_address',
        'is_gift', 'gift_recipient_email', 'gift_message', 'gift_code', 'recipient_user_id',
        'payment_method', 'payment_status', 'bank_order_id',
        'products_price', 'delivery_price', 'total_price', 'language_code',
        'admin_comment', 'ip', 'forwarded_ip', 'confirmed_at', 'completed_at',
    ];

    protected $casts = [
        'needs_phone_call' => 'boolean',
        'is_another_recipient' => 'boolean',
        'is_gift' => 'boolean',
        'payment_status' => PaymentStatus::class,
        'products_price' => 'decimal:2',
        'delivery_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function getPromoCodeLabelAttribute(): string
    {
        return $this->promoCode?->code ?? __('order::translate.promo_code_none');
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(OrderHistory::class, 'order_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'code');
    }

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_method', 'key');
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(DeliveryType::class, 'delivery_type', 'key');
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(OrderShipment::class);
    }
}
