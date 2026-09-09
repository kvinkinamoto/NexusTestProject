<?php

namespace App\Nexus\Modules\PromoCode\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per time a PromoCode was actually consumed (recorded at checkout).
 * No `order_id` yet — Order doesn't exist until Phase 5, which will add it
 * via an additive migration, same pattern as ShopProduct's variation_group_id.
 */
class PromoCodeUsage extends Model
{
    public $timestamps = false;

    protected $fillable = ['promo_code_id', 'user_id', 'applied_amount'];

    protected $casts = [
        'applied_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
