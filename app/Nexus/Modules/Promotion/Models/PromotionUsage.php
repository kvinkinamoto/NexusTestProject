<?php

namespace App\Nexus\Modules\Promotion\Models;

use App\Models\User;
use App\Nexus\Modules\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per order line where an automatic Promotion (not a manually-entered
 * PromoCode — see PromoCode\Models\PromoCodeUsage for that) actually applied
 * a non-zero discount. Written by OrderService::recordPromotionUsage() at
 * checkout, never on price preview.
 */
class PromotionUsage extends Model
{
    protected $table = 'promotion_usages';

    public $timestamps = false;

    protected $fillable = ['promotion_id', 'user_id', 'order_id', 'applied_amount'];

    protected $casts = [
        'applied_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
