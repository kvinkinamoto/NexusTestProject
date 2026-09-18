<?php

namespace App\Nexus\Modules\Advertising\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-visitor view counter backing Advertising::hasReachedVisitorCap() — not
 * a Nexus module (no admin CRUD screen of its own), same convention as
 * Order\Models\OrderProduct: plain Eloquent, always accessed through its
 * parent.
 */
class AdImpression extends Model
{
    protected $table = 'ad_impressions';

    protected $fillable = [
        'advertising_id', 'visitor_key', 'views_count',
    ];

    protected $casts = [
        'views_count' => 'integer',
    ];

    public function advertising(): BelongsTo
    {
        return $this->belongsTo(Advertising::class, 'advertising_id');
    }
}
