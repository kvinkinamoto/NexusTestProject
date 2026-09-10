<?php

namespace App\Nexus\Modules\BonusTransaction\Models;

use App\Models\User;
use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\BonusTransaction\Enums\BonusTransactionStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A per-user bonus-points ledger entry, written by BonusService — like
 * OrderProduct/OrderHistory/PromoCodeUsage, it's an append-only log, not a
 * registered Nexus module: nothing hand-creates or hand-edits a row here.
 */
class BonusTransaction extends Model
{
    protected $table = 'bonus_transactions';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'status',
        'available_at',
        'expires_at',
        'source_type',
        'source_id',
        'description',
    ];

    protected $casts = [
        'type' => BonusTypeEnum::class,
        'status' => BonusTransactionStatusEnum::class,
        'amount' => 'decimal:2',
        'available_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', BonusTransactionStatusEnum::AVAILABLE);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BonusTransactionStatusEnum::PENDING);
    }

    public function scopeForUser(Builder $query, int|string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
